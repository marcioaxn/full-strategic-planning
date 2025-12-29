<?php

namespace Database\Seeders;

use App\Models\RiscoOcorrencia;
use App\Models\Risco;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoOcorrenciaSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $riscos = Risco::where('cod_pei', $peiAtivo->cod_pei)->get();

        if ($riscos->isEmpty()) {
            $this->command->warn('Nenhum risco encontrado.');
            return;
        }

        // Limpar ocorrências existentes
        DB::table('tab_risco_ocorrencia')
            ->whereIn('cod_risco', $riscos->pluck('cod_risco'))
            ->delete();

        $this->command->info('Criando Ocorrências de Riscos...');

        $ocorrencias = [];

        // 30% dos riscos tiveram ocorrências
        $riscosComOcorrencia = $riscos->random(min($riscos->count(), (int)($riscos->count() * 0.3)));

        foreach ($riscosComOcorrencia as $risco) {
            $numOcorrencias = rand(1, 3);

            for ($i = 1; $i <= $numOcorrencias; $i++) {
                $ocorrencias[] = [
                    'cod_risco' => $risco->cod_risco,
                    'dte_ocorrencia' => now()->subDays(rand(1, 365)),
                    'txt_descricao_ocorrencia' => $this->gerarDescricaoOcorrencia(),
                    'vlr_impacto_financeiro' => rand(1000, 100000),
                    'txt_acoes_tomadas' => $this->gerarAcoes(),
                    'txt_licoes_aprendidas' => $this->gerarLicoes(),
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes
        foreach (array_chunk($ocorrencias, 100) as $chunk) {
            RiscoOcorrencia::insert($chunk);
        }

        $this->command->info('✓ ' . count($ocorrencias) . ' Ocorrências de Riscos criadas com sucesso!');
    }

    private function gerarDescricaoOcorrencia(): string
    {
        $descricoes = [
            'Materialização parcial do risco identificado, com impacto controlado nas operações. Situação monitorada pela equipe responsável.',
            'Evento de risco ocorrido conforme cenário previsto na análise. Ações imediatas foram implementadas para contenção.',
            'Ocorrência inesperada do risco, requerendo ativação do plano de contingência e mobilização de recursos adicionais.',
            'Manifestação do risco em menor intensidade que a projetada, permitindo resposta efetiva com recursos disponíveis.',
            'Concretização do risco com impacto significativo, demandando intervenção de múltiplas áreas e revisão de processos.',
        ];

        return $descricoes[array_rand($descricoes)];
    }

    private function gerarAcoes(): string
    {
        $acoes = [
            'Ativação do comitê de crise; Comunicação imediata às partes interessadas; Implementação de medidas corretivas emergenciais; Reavaliação do plano de mitigação',
            'Mobilização de equipe técnica especializada; Ajustes nos processos afetados; Realocação de recursos; Monitoramento intensificado',
            'Execução do plano de contingência previamente estabelecido; Comunicação transparente com stakeholders; Documentação detalhada do evento; Análise de causa raiz',
            'Contenção imediata do impacto; Acionamento de fornecedores alternativos; Revisão de controles internos; Implementação de melhorias preventivas',
        ];

        return $acoes[array_rand($acoes)];
    }

    private function gerarLicoes(): string
    {
        $licoes = [
            'Importância de manter planos de contingência atualizados e testados periodicamente; Necessidade de comunicação efetiva e tempestiva; Valor do monitoramento contínuo de indicadores de alerta',
            'Investimento em redundância de sistemas críticos justificado; Treinamento regular de equipes é fundamental; Documentação de processos facilita resposta rápida',
            'Diversificação de fornecedores reduz vulnerabilidades; Cultura de gestão de riscos deve ser fortalecida; Revisão periódica da análise de riscos é essencial',
            'Colaboração entre áreas acelera resposta a crises; Automação de processos críticos aumenta resiliência; Registro de lições aprendidas agrega valor organizacional',
        ];

        return $licoes[array_rand($licoes)];
    }
}
