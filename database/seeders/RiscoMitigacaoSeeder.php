<?php

namespace Database\Seeders;

use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\RiskManagement\Risco;
use App\Models\User;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiscoMitigacaoSeeder extends Seeder
{
    public function run(): void
    {
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado.');
            return;
        }

        $riscos = Risco::where('cod_pei', $peiAtivo->cod_pei)->get();
        $usuarios = User::all();

        if ($riscos->isEmpty() || $usuarios->isEmpty()) {
            $this->command->warn('Riscos ou Usuários não encontrados.');
            return;
        }

        // Limpar mitigações existentes
        DB::table('tab_risco_mitigacao')
            ->whereIn('cod_risco', $riscos->pluck('cod_risco'))
            ->delete();

        $this->command->info('Criando Planos de Mitigação de Riscos...');

        $mitigacoes = [];
        $tipos = ['Prevenção', 'Mitigação', 'Transferência', 'Aceitação'];
        $statusOptions = ['A Fazer', 'Em Andamento', 'Concluído'];

        // Riscos críticos têm mais mitigações
        foreach ($riscos as $risco) {
            $numMitigacoes = $risco->num_nivel_risco >= 16 ? rand(2, 4) : rand(1, 2);

            for ($i = 1; $i <= $numMitigacoes; $i++) {
                $dataPrazo = now()->addMonths(rand(1, 12));
                $status = $dataPrazo->isPast() ? $statusOptions[array_rand($statusOptions)] : 'A Fazer';

                $mitigacoes[] = [
                    'cod_risco' => $risco->cod_risco,
                    'txt_descricao' => $this->gerarDescricaoMitigacao($risco->dsc_categoria),
                    'cod_responsavel' => $usuarios->random()->id,
                    'dte_prazo' => $dataPrazo,
                    'dsc_status' => $status,
                    'vlr_custo_estimado' => rand(5000, 500000) / 100,
                    'created_at' => now()->subDays(rand(1, 180)),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes
        foreach (array_chunk($mitigacoes, 100) as $chunk) {
            RiscoMitigacao::insert($chunk);
        }

        $this->command->info('✓ ' . count($mitigacoes) . ' Planos de Mitigação criados com sucesso!');
    }

    private function gerarDescricaoMitigacao(string $categoria): string
    {
        $mitigacoes = [
            'Estratégico' => [
                'Desenvolver plano de contingência estratégico com cenários alternativos',
                'Estabelecer comitê de governança para monitoramento contínuo',
                'Criar indicadores de alerta precoce para tomada de decisão',
            ],
            'Operacional' => [
                'Implementar procedimentos operacionais padrão (POPs)',
                'Automatizar processos críticos para redução de erros',
                'Estabelecer redundância operacional em atividades essenciais',
            ],
            'Financeiro' => [
                'Constituir reserva orçamentária de contingência',
                'Diversificar fontes de recursos financeiros',
                'Implementar controles financeiros mais rigorosos',
            ],
            'Compliance' => [
                'Fortalecer programa de conformidade regulatória',
                'Realizar auditorias internas periódicas',
                'Capacitar equipes em requisitos legais aplicáveis',
            ],
            'Tecnológico' => [
                'Modernizar infraestrutura de TI com soluções redundantes',
                'Implementar política de backup e recuperação de desastres',
                'Estabelecer programa de atualização tecnológica contínua',
            ],
            'Reputacional' => [
                'Desenvolver plano de comunicação de crise',
                'Fortalecer canais de relacionamento com stakeholders',
                'Implementar programa de gestão de imagem institucional',
            ],
        ];

        $lista = $mitigacoes[$categoria] ?? $mitigacoes['Operacional'];
        return $lista[array_rand($lista)];
    }
}
