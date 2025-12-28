<?php

namespace Database\Seeders;

use App\Models\PEI\Entrega;
use App\Models\PEI\PlanoDeAcao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntregaSeeder extends Seeder
{
    public function run(): void
    {
        $planos = PlanoDeAcao::with('objetivoEstrategico.perspectiva')->get();

        if ($planos->isEmpty()) {
            $this->command->warn('Nenhum Plano de Ação encontrado. Execute PlanoAcaoSeeder primeiro.');
            return;
        }

        // Limpar entregas existentes
        DB::table('tab_entregas')
            ->whereIn('cod_plano_de_acao', $planos->pluck('cod_plano_de_acao'))
            ->delete();

        $this->command->info('Criando Entregas dos Planos de Ação...');

        $entregas = [];
        $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Atrasado'];

        foreach ($planos as $plano) {
            $numEntregas = rand(3, 7);

            for ($i = 1; $i <= $numEntregas; $i++) {
                // Se o plano está concluído, mais entregas concluídas
                if ($plano->bln_status === 'Concluído') {
                    $status = rand(0, 100) > 10 ? 'Concluído' : 'Em Andamento';
                } elseif ($plano->bln_status === 'Em Andamento') {
                    $statusDist = rand(1, 100);
                    if ($statusDist <= 40) $status = 'Concluído';
                    elseif ($statusDist <= 70) $status = 'Em Andamento';
                    elseif ($statusDist <= 90) $status = 'Não Iniciado';
                    else $status = 'Atrasado';
                } else {
                    $status = 'Não Iniciado';
                }

                $entregas[] = [
                    'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                    'dsc_entrega' => $this->gerarDescricaoEntrega($i),
                    'bln_status' => $status,
                    'dsc_periodo_medicao' => $this->gerarPeriodoMedicao(),
                    'num_nivel_hierarquico_apresentacao' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes
        foreach (array_chunk($entregas, 100) as $chunk) {
            Entrega::insert($chunk);
        }

        $this->command->info('✓ ' . count($entregas) . ' Entregas criadas com sucesso!');
    }

    private function gerarDescricaoEntrega(int $numero): string
    {
        $entregas = [
            'Relatório de diagnóstico institucional completo',
            'Mapeamento de processos críticos finalizado',
            'Matriz de capacitação de servidores elaborada',
            'Sistema de gestão implantado e homologado',
            'Procedimento operacional padrão documentado',
            'Plano de comunicação institucional aprovado',
            'Dashboard de monitoramento em produção',
            'Política institucional publicada',
            'Capacitação de equipes concluída',
            'Infraestrutura tecnológica modernizada',
            'Manual de boas práticas elaborado',
            'Framework de governança implementado',
            'Processo de auditoria interna estruturado',
            'Modelo de gestão de riscos aprovado',
            'Sistema de indicadores em operação',
            'Comitê técnico instituído',
            'Norma regulamentadora publicada',
            'Ferramenta de automação implantada',
            'Estudo de viabilidade concluído',
            'Plano de contingência validado'
        ];

        return $entregas[($numero - 1) % count($entregas)];
    }

    private function gerarPeriodoMedicao(): string
    {
        $periodos = ['Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual', 'Pontual'];
        return $periodos[array_rand($periodos)];
    }
}
