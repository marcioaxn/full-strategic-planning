<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Esvazia TODAS as tabelas de dados dos schemas de domínio do sistema.
 *
 * NÃO FAZ PARTE DE `php artisan db:seed`. Saiu do caminho padrão porque atendia
 * a exigência de um cliente específico, já resolvida no último deploy — e deixá-la
 * ali punha todo banco a um comando de distância de perder os dados. Só roda se
 * for chamada de propósito:
 *
 *     php artisan db:seed --class=TruncarBancoSeeder
 *
 * Por que existe: em instalações onde as seeders antigas já rodaram, o banco
 * carrega registros inconsistentes (usuários sem perfil vinculado, organizações
 * duplicadas, dados de demonstração). Repovoar sem limpar antes só empilharia
 * mais inconsistência. Esta seeder garante um ponto de partida determinístico.
 *
 * Garantias:
 * - A lista de tabelas é descoberta em tempo de execução no `information_schema`,
 *   a partir dos schemas declarados em `config('database.connections.pgsql.search_path')`.
 *   Nenhuma tabela nova precisa ser cadastrada aqui manualmente.
 * - A tabela `migrations` é SEMPRE preservada. Apagá-la faria o Laravel acreditar
 *   que nenhuma migration foi executada e tentar recriar todo o esquema.
 * - O TRUNCATE é feito em uma única instrução com CASCADE, então a ordem das
 *   chaves estrangeiras é irrelevante.
 *
 * ATENÇÃO: esta operação é IRREVERSÍVEL. Faça backup antes de executar em um
 * ambiente que já contenha dados reais de planejamento.
 */
class TruncarBancoSeeder extends Seeder
{
    /**
     * Tabelas que nunca podem ser truncadas, em nenhum schema.
     */
    private const TABELAS_PRESERVADAS = [
        'migrations',
    ];

    public function run(): void
    {
        $schemas = $this->schemasDeDominio();
        $tabelas = $this->tabelasDe($schemas);

        if ($tabelas === []) {
            throw new RuntimeException(
                'Nenhuma tabela encontrada nos schemas '.implode(', ', $schemas).'. '
                .'Execute "php artisan migrate" antes de rodar as seeders.'
            );
        }

        $lista = implode(', ', array_map(
            fn (array $t) => sprintf('"%s"."%s"', $t['schema'], $t['tabela']),
            $tabelas
        ));

        DB::statement("TRUNCATE TABLE {$lista} RESTART IDENTITY CASCADE");

        $this->informar(sprintf(
            '  Tabelas esvaziadas: %d (schemas: %s). Tabela "migrations" preservada.',
            count($tabelas),
            implode(', ', $schemas)
        ));
    }

    /**
     * Schemas de domínio configurados para a conexão PostgreSQL do projeto.
     *
     * @return array<int, string>
     */
    private function schemasDeDominio(): array
    {
        $searchPath = config('database.connections.pgsql.search_path', []);
        $schemas = is_array($searchPath) ? $searchPath : preg_split('/[\s,]+/', (string) $searchPath);

        $schemas = array_values(array_filter(
            array_map('trim', $schemas ?: []),
            fn (string $schema) => $schema !== '' && $schema !== '$user'
        ));

        // Barreira de sanidade: identificadores vindos de configuração são
        // interpolados na instrução TRUNCATE, então precisam ser inequívocos.
        foreach ($schemas as $schema) {
            if (! preg_match('/^[a-z_][a-z0-9_]*$/', $schema)) {
                throw new RuntimeException("Nome de schema inválido no search_path: \"{$schema}\".");
            }
        }

        if ($schemas === []) {
            throw new RuntimeException(
                'O search_path da conexão pgsql está vazio. Verifique config/database.php.'
            );
        }

        return $schemas;
    }

    /**
     * Tabelas físicas (não views) existentes nos schemas informados.
     *
     * @param  array<int, string>  $schemas
     * @return array<int, array{schema: string, tabela: string}>
     */
    private function tabelasDe(array $schemas): array
    {
        $placeholders = implode(', ', array_fill(0, count($schemas), '?'));

        $linhas = DB::select(
            "SELECT table_schema, table_name
               FROM information_schema.tables
              WHERE table_type = 'BASE TABLE'
                AND table_schema IN ({$placeholders})
              ORDER BY table_schema, table_name",
            $schemas
        );

        $tabelas = [];

        foreach ($linhas as $linha) {
            if (in_array($linha->table_name, self::TABELAS_PRESERVADAS, true)) {
                continue;
            }

            $tabelas[] = [
                'schema' => $linha->table_schema,
                'tabela' => $linha->table_name,
            ];
        }

        return $tabelas;
    }

    private function informar(string $mensagem): void
    {
        $this->command?->line($mensagem);
    }
}
