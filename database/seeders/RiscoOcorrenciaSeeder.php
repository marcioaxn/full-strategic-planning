<?php

namespace Database\Seeders;

use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoOcorrencia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoOcorrenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando Ocorrências de Riscos...');

        $riscos = Risco::all();

        if ($riscos->isEmpty()) {
            $this->command->warn('Riscos não encontrados. Pule o seeder de ocorrência.');
            return;
        }

        $descricoes = [
            'Ocorrência de falha no servidor principal afetando disponibilidade de dados.',
            'Atraso na entrega de recursos orçamentários por questões burocráticas.',
            'Mudança imprevista na legislação regulatória do setor.',
            'Identificação de vulnerabilidade de segurança crítica no sistema legado.',
            'Aumento imprevisto de custos operacionais por flutuação de mercado.',
            'Indisponibilidade de pessoal chave por licença médica não planejada.',
            'Falha em processo de backup automatizado detectada em auditoria.',
            'Instabilidade na rede local prejudicando atividades remotas.',
            'Erro em cálculo de indicadores por inconsistência em dados de entrada.',
            'Identificação de duplicidade em registros de planejamento estratégico.'
        ];

        $acoes = [
            'Acionamento imediato da equipe de suporte técnico para restauração.',
            'Abertura de chamado prioritário junto ao órgão competente.',
            'Revisão dos fluxos de trabalho e adequação à nova realidade.',
            'Execução de plano de contingência para mitigação de impactos.',
            'Realização de reunião emergencial para realocação de recursos.',
            'Implementação de correção emergencial de segurança (patch).',
            'Comunicação aos stakeholders sobre atraso em prazos estimados.',
            'Reforço de procedimentos de monitoramento e controle.',
            'Adoção de solução temporária baseada em processos manuais.',
            'Treinamento focado para equipes afetadas pela ocorrência.'
        ];

        $licoes = [
            'Necessidade de redundância em infraestrutura crítica.',
            'Melhorar comunicação entre áreas técnicas e administrativas.',
            'Revisar periodicamente as matrizes de risco e controles.',
            'Documentar procedimentos de resposta a incidentes críticos.',
            'Manter backups testados e disponíveis fora da infraestrutura local.',
            'Investir em automação de monitoramento de disponibilidade.',
            'Capacitar mais de uma pessoa para processos chave (Job Rotation).',
            'Estabelecer alertas de desvio de custo em tempo real.',
            'Manter repositório de lições aprendidas atualizado e acessível.',
            'Revisar políticas de conformidade com maior frequência.'
        ];

        $ocorrencias = [];

        foreach ($riscos as $risco) {
            // Apenas 30% dos riscos terão ocorrências
            if (rand(1, 100) > 30) continue;

            // Cada risco afetado tem entre 1 e 3 ocorrências
            $numOcorrencias = rand(1, 3);
            
            for ($i = 0; $i < $numOcorrencias; $i++) {
                $dteOcorrencia = now()->subDays(rand(1, 180));
                
                $ocorrencias[] = [
                    'cod_ocorrencia' => strtolower((string) \Illuminate\Support\Str::uuid()),
                    'cod_risco' => $risco->cod_risco,
                    'dte_ocorrencia' => $dteOcorrencia,
                    'txt_descricao_ocorrencia' => $descricoes[array_rand($descricoes)],
                    'vlr_impacto_financeiro' => rand(0, 1) ? rand(500, 50000) : 0,
                    'txt_acoes_tomadas' => $acoes[array_rand($acoes)],
                    'txt_licoes_aprendidas' => $licoes[array_rand($licoes)],
                    'created_at' => $dteOcorrencia,
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes de 50 para performance
        foreach (array_chunk($ocorrencias, 50) as $chunk) {
            DB::table('risk_management.tab_risco_ocorrencia')->insert($chunk);
        }

        $this->command->info('✓ ' . count($ocorrencias) . ' Ocorrências de riscos criadas!');
    }
}
