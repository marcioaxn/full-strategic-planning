<?php

namespace App\Services\Seeding;

use Illuminate\Support\Facades\DB;

/**
 * Sincroniza listas fechadas (tipos, categorias, status) entre o código e o banco.
 *
 * POR QUE EXISTE
 * --------------
 * Este produto é multicliente. Havia duas populações e só uma era atendida:
 * o vocabulário era semeado dentro do `up()` de uma migration, e migration já
 * aplicada nunca roda de novo. Cliente que clonava hoje recebia a opção nova;
 * cliente instalado no ano passado nunca recebia, mesmo rodando `migrate`.
 *
 * A resposta NÃO é um seeder por população — isso duplica a lista em dois
 * lugares, e alguém acrescenta um item em um e esquece o outro. A resposta é
 * um seeder idempotente que CONVERGE o banco para o estado desejado, seja ele
 * qual for. O mesmo `php artisan db:seed` serve a instalação nova e a antiga.
 *
 * GARANTIAS
 * ---------
 * - Nunca apaga. Cliente pode ter renomeado "Ação" para "Ação Corretiva";
 *   sincronizar não desfaz customização — só insere o que falta.
 * - Faz SELECT + INSERT/UPDATE explícitos, sem a cláusula de upsert do
 *   PostgreSQL 9.5+ (que `upsert()` e `insertOrIgnore()` do Laravel emitem),
 *   para funcionar também em instalação antiga.
 * - Nunca aposenta item que tenha registro filho apontando para ele: recusa e
 *   relata, para o operador decidir.
 * - Toda query qualifica o schema.
 */
class VocabularioControlado
{
    /** @var array{inseridos:int, atualizados:int, ignorados:int, recusados:array} */
    private array $relatorio = [
        'inseridos' => 0,
        'atualizados' => 0,
        'ignorados' => 0,
        'recusados' => [],
    ];

    /**
     * Garante que cada item exista, com o rótulo esperado.
     *
     * @param  string  $tabela  Nome QUALIFICADO com schema (ex.: 'action_plan.tab_tipo_execucao')
     * @param  string  $colunaChave  Nome da coluna de chave primária
     * @param  array<int, array<string, mixed>>  $itens  Cada item precisa trazer a chave primária
     * @param  array<int, string>  $colunasAtualizaveis  Colunas que podem ser corrigidas em item
     *                                                   já existente. Vazio = só insere, nunca
     *                                                   toca no que o cliente já tem.
     */
    public function sincronizar(
        string $tabela,
        string $colunaChave,
        array $itens,
        array $colunasAtualizaveis = []
    ): self {
        $this->exigirSchemaQualificado($tabela);

        $agora = now();

        foreach ($itens as $item) {
            $chave = $item[$colunaChave] ?? null;

            if ($chave === null) {
                throw new \InvalidArgumentException(
                    "Item sem a chave primária [{$colunaChave}] em [{$tabela}]. ".
                    'O identificador precisa ser fixo em constante, não gerado no seeder: '.
                    'é ele que faz a segunda execução ser inócua.'
                );
            }

            $existente = DB::table($tabela)->where($colunaChave, $chave)->first();

            if ($existente === null) {
                DB::table($tabela)->insert($item + [
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ]);
                $this->relatorio['inseridos']++;

                continue;
            }

            $divergentes = [];

            foreach ($colunasAtualizaveis as $coluna) {
                if (array_key_exists($coluna, $item) && $existente->{$coluna} !== $item[$coluna]) {
                    $divergentes[$coluna] = $item[$coluna];
                }
            }

            if ($divergentes === []) {
                $this->relatorio['ignorados']++;

                continue;
            }

            DB::table($tabela)
                ->where($colunaChave, $chave)
                ->update($divergentes + ['updated_at' => $agora]);

            $this->relatorio['atualizados']++;
        }

        return $this;
    }

    /**
     * Marca itens como excluídos (soft delete), mas SÓ se nada apontar para eles.
     *
     * Aposentar um tipo com 400 registros vinculados quebra a tela do cliente.
     * O serviço recusa e relata — remanejar é decisão do operador, não efeito
     * colateral de um `db:seed`.
     *
     * @param  array<int, string>  $chaves
     * @param  array<int, array{tabela:string, coluna:string}>  $dependencias
     */
    public function aposentar(
        string $tabela,
        string $colunaChave,
        array $chaves,
        array $dependencias = []
    ): self {
        $this->exigirSchemaQualificado($tabela);

        foreach ($chaves as $chave) {
            $registro = DB::table($tabela)->where($colunaChave, $chave)->first();

            if ($registro === null || ($registro->deleted_at ?? null) !== null) {
                $this->relatorio['ignorados']++;

                continue;
            }

            $vinculos = 0;

            foreach ($dependencias as $dep) {
                $this->exigirSchemaQualificado($dep['tabela']);

                $vinculos += DB::table($dep['tabela'])
                    ->where($dep['coluna'], $chave)
                    ->whereNull('deleted_at')
                    ->count();
            }

            if ($vinculos > 0) {
                $this->relatorio['recusados'][] = [
                    'chave' => $chave,
                    'vinculos' => $vinculos,
                    'motivo' => "Existem {$vinculos} registro(s) vinculado(s). ".
                                'Remaneje antes de aposentar.',
                ];

                continue;
            }

            DB::table($tabela)
                ->where($colunaChave, $chave)
                ->update(['deleted_at' => now(), 'updated_at' => now()]);

            $this->relatorio['atualizados']++;
        }

        return $this;
    }

    /** @return array{inseridos:int, atualizados:int, ignorados:int, recusados:array} */
    public function relatorio(): array
    {
        return $this->relatorio;
    }

    public function houveMudanca(): bool
    {
        return $this->relatorio['inseridos'] > 0 || $this->relatorio['atualizados'] > 0;
    }

    /**
     * O search_path começa em "pei": tabela sem schema resolve por sorte, e
     * cala no dia em que existir homônima num schema anterior da lista.
     */
    private function exigirSchemaQualificado(string $tabela): void
    {
        if (! str_contains($tabela, '.')) {
            throw new \InvalidArgumentException(
                "Tabela [{$tabela}] sem schema. Qualifique sempre ".
                "(ex.: 'action_plan.tab_tipo_execucao') — o search_path começa em \"pei\" ".
                'e uma query sem schema cai lá em silêncio.'
            );
        }
    }
}
