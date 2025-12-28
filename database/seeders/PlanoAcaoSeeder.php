<?php

namespace Database\Seeders;

use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\TipoExecucao;
use App\Models\Organization;
use App\Models\PEI\PEI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanoAcaoSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar PEI ativo
        $peiAtivo = PEI::ativos()->first();
        if (!$peiAtivo) {
            $this->command->warn('Nenhum PEI ativo encontrado. Pulando seeder de Planos de Ação.');
            return;
        }

        // Buscar organizações
        $organizacoes = Organization::whereNotNull('cod_organizacao')->get();
        if ($organizacoes->isEmpty()) {
            $this->command->warn('Nenhuma organização encontrada. Pulando seeder de Planos de Ação.');
            return;
        }

        // Buscar objetivos estratégicos do PEI ativo
        $objetivos = ObjetivoEstrategico::whereHas('perspectiva', function($q) use ($peiAtivo) {
            $q->where('cod_pei', $peiAtivo->cod_pei);
        })->get();
        if ($objetivos->isEmpty()) {
            $this->command->warn('Nenhum objetivo encontrado. Pulando seeder de Planos de Ação.');
            return;
        }

        // Buscar tipos de execução
        $tiposExecucao = TipoExecucao::all();
        if ($tiposExecucao->isEmpty()) {
            $this->command->warn('Nenhum tipo de execução encontrado. Pulando seeder de Planos de Ação.');
            return;
        }

        // Limpar planos existentes (apenas do PEI ativo)
        DB::table('tab_plano_de_acao')
            ->whereIn('cod_objetivo_estrategico', $objetivos->pluck('cod_objetivo_estrategico'))
            ->delete();

        $this->command->info('Criando Planos de Ação...');

        $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Suspenso'];
        $planos = [];

        // Criar 3-5 planos por objetivo
        foreach ($objetivos as $objetivo) {
            $numPlanos = rand(3, 5);

            for ($i = 1; $i <= $numPlanos; $i++) {
                $dataInicio = now()->subMonths(rand(1, 12));
                $dataFim = (clone $dataInicio)->addMonths(rand(6, 24));
                $status = $dataFim->isPast() ? 'Concluído' : (rand(0, 100) > 20 ? 'Em Andamento' : 'Não Iniciado');

                $planos[] = [
                    'cod_objetivo_estrategico' => $objetivo->cod_objetivo_estrategico,
                    'cod_tipo_execucao' => $tiposExecucao->random()->cod_tipo_execucao,
                    'cod_organizacao' => $organizacoes->random()->cod_organizacao,
                    'num_nivel_hierarquico_apresentacao' => 1,
                    'dsc_plano_de_acao' => $this->gerarDescricaoPlano($i, $objetivo->dsc_objetivo_estrategico),
                    'dte_inicio' => $dataInicio,
                    'dte_fim' => $dataFim,
                    'vlr_orcamento_previsto' => rand(50000, 5000000) / 100,
                    'bln_status' => $status,
                    'cod_ppa' => rand(1000, 9999),
                    'cod_loa' => 'LOA-' . now()->year . '-' . rand(100, 999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserir em lotes
        foreach (array_chunk($planos, 50) as $chunk) {
            PlanoDeAcao::insert($chunk);
        }

        $this->command->info('✓ ' . count($planos) . ' Planos de Ação criados com sucesso!');
    }

    private function gerarDescricaoPlano(int $numero, string $objetivo): string
    {
        $prefixos = [
            'Implementar',
            'Desenvolver',
            'Executar',
            'Realizar',
            'Estabelecer',
            'Criar',
            'Estruturar',
            'Promover',
            'Fortalecer',
            'Aprimorar'
        ];

        $acoes = [
            'programa de capacitação',
            'sistema de gestão integrado',
            'processo de melhoria contínua',
            'estratégia de comunicação',
            'plano de desenvolvimento',
            'metodologia ágil',
            'política institucional',
            'modelo de governança',
            'framework de monitoramento',
            'procedimento operacional padrão'
        ];

        return $prefixos[array_rand($prefixos)] . ' ' .
               $acoes[array_rand($acoes)] . ' - ' .
               substr($objetivo, 0, 50) . ' (' . $numero . ')';
    }
}
