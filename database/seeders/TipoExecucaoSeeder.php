<?php

namespace Database\Seeders;

use App\Models\ActionPlan\TipoExecucao;
use App\Services\Seeding\VocabularioControlado;
use Illuminate\Database\Seeder;

/**
 * Tipos de execução de uma Iniciativa (antigo "Plano de Ação").
 *
 * Substitui a semeadura que vivia dentro do `up()` da migration
 * 2021_11_14_221355_create_pei_tab_tipo_execucao_table.php. Aquela migration
 * continua como está — ela descreve o que era verdade no dia em que rodou — mas
 * nunca mais roda, e por isso cliente já instalado jamais recebia tipo novo.
 *
 * Este seeder é idempotente e atende as duas populações com o mesmo comando:
 *
 *     php artisan db:seed --class=TipoExecucaoSeeder
 *
 * - Instalação nova (tabela vazia): insere Ação e Projeto.
 * - Instalação antiga (tem os três antigos): insere o que faltar, e tenta
 *   aposentar "Iniciativa" — recusando se houver iniciativa vinculada a ele.
 * - Segunda execução: não faz nada.
 *
 * POR QUE "INICIATIVA" SAI DA LISTA
 * ---------------------------------
 * O módulo passou a se chamar "Iniciativas". Um TIPO chamado "Iniciativa"
 * dentro do módulo "Iniciativas" produz na tela "Iniciativa · Tipo: Iniciativa",
 * que não distingue nada. O gestor definiu os tipos como Ação e Projeto.
 *
 * A aposentadoria é por soft delete, nunca DELETE: registros antigos da
 * auditoria referenciam o UUID e precisam continuar resolvíveis.
 */
class TipoExecucaoSeeder extends Seeder
{
    private const TABELA = 'action_plan.tab_tipo_execucao';

    public function run(): void
    {
        $vocabulario = new VocabularioControlado;

        $vocabulario->sincronizar(
            self::TABELA,
            'cod_tipo_execucao',
            [
                [
                    'cod_tipo_execucao' => TipoExecucao::ACAO,
                    'dsc_tipo_execucao' => 'Ação',
                ],
                [
                    'cod_tipo_execucao' => TipoExecucao::PROJETO,
                    'dsc_tipo_execucao' => 'Projeto',
                ],
            ],
            // Lista vazia de propósito: se o cliente renomeou "Ação" para
            // "Ação Corretiva", isso é decisão dele e o seeder não desfaz.
            colunasAtualizaveis: []
        );

        $vocabulario->aposentar(
            self::TABELA,
            'cod_tipo_execucao',
            [TipoExecucao::INICIATIVA],
            dependencias: [
                ['tabela' => 'action_plan.tab_plano_de_acao', 'coluna' => 'cod_tipo_execucao'],
            ]
        );

        $this->relatar($vocabulario->relatorio());
    }

    private function relatar(array $relatorio): void
    {
        $this->command?->info(sprintf(
            'Tipos de Iniciativa: %d inserido(s), %d atualizado(s), %d sem mudança.',
            $relatorio['inseridos'],
            $relatorio['atualizados'],
            $relatorio['ignorados']
        ));

        foreach ($relatorio['recusados'] as $recusa) {
            $rotulo = TipoExecucao::withTrashed()
                ->find($recusa['chave'])?->dsc_tipo_execucao ?? $recusa['chave'];

            $this->command?->warn(
                "  ! O tipo \"{$rotulo}\" NÃO foi aposentado: {$recusa['motivo']}"
            );
            $this->command?->line(
                '    Para remanejar: <comment>php artisan iniciativas:remanejar-tipo '.
                '--de="'.$rotulo.'" --para="Ação"</comment>'
            );
        }

        // Sanidade: o combo do formulário fica vazio se nada sobrar ativo.
        $ativos = TipoExecucao::count();

        if ($ativos === 0) {
            $this->command?->error(
                'Nenhum tipo de execução ativo. O formulário de Iniciativa ficará sem opções.'
            );
        }
    }
}
