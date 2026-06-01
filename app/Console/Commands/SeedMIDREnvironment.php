<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\MIDRIdentitySeeder;
use Database\Seeders\MIDROrganizationSeeder;
use Database\Seeders\MIDRStrategicSeeder;
use Database\Seeders\MIDRAnalysisSeeder;
use Database\Seeders\MIDRBusinessSeeder;
use Database\Seeders\MIDRRiskSeeder;
use Database\Seeders\MIDRSupportSeeder;

class SeedMIDREnvironment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:seed-midr {--fresh : Reiniciar o banco de dados antes do seeding}';

    /**
     * The console command description.
     */
    protected $description = 'Popula o sistema com dados reais do MIDR 2023-2027 e dados operacionais simulados.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================================');
        $this->info('  ESTRUTURAÇÃO DO AMBIENTE MIDR (PEI 2023-2027)');
        $this->info('========================================================');

        if ($this->option('fresh')) {
            $this->warn('Reiniciando banco de dados (migrate:fresh)...');
            Artisan::call('migrate:fresh', [], $this->output);
        }

        $seeders = [
            MIDROrganizationSeeder::class, // 1. Organizações (Base)
            MIDRSupportSeeder::class,      // 2. Usuários (Necessário para vínculos de responsabilidade)
            MIDRIdentitySeeder::class,     // 3. Ciclo PEI, Missão, Visão, Valores
            MIDRStrategicSeeder::class,    // 4. Perspectivas e Objetivos Reais
            MIDRAnalysisSeeder::class,     // 5. SWOT e PESTEL
            MIDRBusinessSeeder::class,     // 6. Planos, Indicadores e Entregas (Tarefas + KPIs)
            MIDRRiskSeeder::class,         // 7. Matriz de Riscos
        ];

        foreach ($seeders as $seeder) {
            $name = class_basename($seeder);
            $this->comment("\nExecutando: $name...");
            Artisan::call('db:seed', ['--class' => $seeder], $this->output);
        }

        $this->info("\n========================================================");
        $this->info('  ✓ AMBIENTE MIDR CONFIGURADO COM SUCESSO!');
        $this->info('========================================================');

        return 0;
    }
}
