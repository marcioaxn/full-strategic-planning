<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder Master para Popular Dados Completos do Sistema PEI
 *
 * Este seeder orquestra a criação de dados realistas para simular
 * uma grande organização com planejamento estratégico completo.
 *
 * IMPORTANTE:
 * - NÃO dropa dados de Organizações, Usuários, PEI, Perspectivas e Objetivos existentes
 * - Apenas remove dados das tabelas sendo populadas para evitar duplicação
 * - Execute APÓS ter configurado: Organizações, Usuários, PEI, Perspectivas e Objetivos
 *
 * Uso:
 *   php artisan db:seed --class=PEIDataSeeder
 */
class PEIDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  SEEDER COMPLETO DO SISTEMA PEI/BSC');
        $this->command->info('========================================');
        $this->command->info('');

        // Ordem de execução é CRÍTICA devido aos relacionamentos
        $seeders = [
            // 1. Planos de Ação (dependem de Objetivos)
            PlanoAcaoSeeder::class,

            // 2. Entregas (dependem de Planos de Ação)
            EntregaSeeder::class,

            // 3. Indicadores (dependem de Objetivos e Planos)
            IndicadorSeeder::class,

            // 4. Linha de Base dos Indicadores
            LinhaBaseIndicadorSeeder::class,

            // 5. Metas Anuais dos Indicadores
            MetaPorAnoSeeder::class,

            // 6. Evolução Mensal dos Indicadores
            EvolucaoIndicadorSeeder::class,

            // 7. Riscos
            RiscoSeeder::class,

            // 8. Relacionamento Risco-Objetivo (N:N)
            RiscoObjetivoSeeder::class,

            // 9. Planos de Mitigação de Riscos
            RiscoMitigacaoSeeder::class,

            // 10. Ocorrências de Riscos
            RiscoOcorrenciaSeeder::class,

            // 11. Análises SWOT e PESTEL
            AnaliseAmbientalSeeder::class,
        ];

        $totalSeeders = count($seeders);
        $current = 0;

        foreach ($seeders as $seederClass) {
            $current++;
            $this->command->info('');
            $this->command->info("[$current/$totalSeeders] Executando: " . class_basename($seederClass));
            $this->command->info('----------------------------------------');

            $this->call($seederClass);
        }

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  ✓ SEEDING CONCLUÍDO COM SUCESSO!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Base de dados populada com:');
        $this->command->info('  • Planos de Ação (3-5 por objetivo)');
        $this->command->info('  • Entregas (3-7 por plano)');
        $this->command->info('  • Indicadores (2-3 por objetivo + alguns de planos)');
        $this->command->info('  • Linhas de Base, Metas e Evoluções Mensais');
        $this->command->info('  • Riscos (20-30) com Mitigações e Ocorrências');
        $this->command->info('  • Análises SWOT e PESTEL completas');
        $this->command->info('');
        $this->command->info('Pronto para uso! Acesse o Mapa Estratégico e Dashboards.');
        $this->command->info('');
    }
}
