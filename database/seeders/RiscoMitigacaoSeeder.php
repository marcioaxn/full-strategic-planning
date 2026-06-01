<?php

namespace Database\Seeders;

use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoMitigacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando Planos de Mitigação de Riscos...');

        $riscos = Risco::all();
        $users = User::all();

        if ($riscos->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Riscos ou Usuários não encontrados. Pule o seeder de mitigação.');
            return;
        }

        $tiposMitigacao = ['Prevenir', 'Reduzir', 'Transferir', 'Aceitar'];
        $statusMitigacao = ['Planejada', 'Em Andamento', 'Concluída', 'Cancelada'];

        $acoes = [
            'Implementar política de backup e recuperação de desastres',
            'Realizar auditorias internas periódicas',
            'Capacitar equipes em requisitos legais aplicáveis',
            'Modernizar infraestrutura de TI com soluções redundantes',
            'Fortalecer programa de conformidade regulatória',
            'Diversificar fontes de recursos financeiros',
            'Implementar controles financeiros mais rigorosos',
            'Desenvolver plano de contingência estratégico com cenários alternativos',
            'Criar indicadores de alerta precoce para tomada de decisão',
            'Automatizar processos críticos para redução de erros',
            'Estabelecer redundância operacional em atividades essenciais',
            'Constituir reserva orçamentária de contingência',
            'Fortalecer canais de relacionamento com stakeholders',
            'Estabelecer comitê de governança para monitoramento contínuo',
            'Implementar procedimentos operacionais padrão (POPs)'
        ];

        $mitigacoes = [];

        foreach ($riscos as $risco) {
            // Cada risco tem entre 1 e 3 mitigações
            $numMitigacoes = rand(1, 3);
            
            for ($i = 0; $i < $numMitigacoes; $i++) {
                $createdAt = now()->subDays(rand(1, 180));
                
                $mitigacoes[] = [
                    'cod_mitigacao' => strtolower((string) \Illuminate\Support\Str::uuid()),
                    'cod_risco' => $risco->cod_risco,
                    'dsc_tipo_mitigacao' => $tiposMitigacao[array_rand($tiposMitigacao)],
                    'txt_acao_mitigacao' => $acoes[array_rand($acoes)],
                    'cod_responsavel' => $users->random()->id,
                    'dte_prazo' => now()->addDays(rand(30, 365)),
                    'dsc_status' => $statusMitigacao[array_rand($statusMitigacao)],
                    'txt_observacoes' => rand(0, 1) ? 'Observação automática gerada pelo seeder.' : null,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes de 50 para performance
        foreach (array_chunk($mitigacoes, 50) as $chunk) {
            DB::table('risk_management.tab_risco_mitigacao')->insert($chunk);
        }

        $this->command->info('✓ ' . count($mitigacoes) . ' Planos de mitigação criados!');
    }
}
