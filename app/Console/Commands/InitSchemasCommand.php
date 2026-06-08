<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitSchemasCommand extends Command
{
    protected $signature = 'app:init-schemas
        {--connection= : Conexão a usar (padrão: conexão default configurada)}';

    protected $description = 'Cria os schemas PostgreSQL necessários para o sistema (idempotente — seguro executar múltiplas vezes)';

    public function handle(): int
    {
        $connection = $this->option('connection') ?? config('database.default');

        if (config("database.connections.{$connection}.driver") !== 'pgsql') {
            $this->info("Conexão [{$connection}] não é PostgreSQL — nenhum schema criado.");
            return self::SUCCESS;
        }

        $schemas = config("database.connections.{$connection}.search_path", []);

        if (empty($schemas)) {
            $this->warn("Nenhum schema definido em search_path para a conexão [{$connection}].");
            return self::SUCCESS;
        }

        $this->line('  <comment>Verificando schemas PostgreSQL...</comment>');

        foreach ((array) $schemas as $schema) {
            DB::connection($connection)->statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\"");
            $this->line("  <info>✓</info> {$schema}");
        }

        $this->newLine();
        $this->info('Schemas criados/verificados. Pronto para migrate.');
        return self::SUCCESS;
    }
}
