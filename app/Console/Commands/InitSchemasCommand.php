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
        $this->line('  <comment>Verificando gen_random_uuid()...</comment>');
        $this->garantirGenRandomUuid($connection, (array) $schemas);

        $this->newLine();
        $this->info('Schemas criados/verificados. Pronto para migrate.');

        return self::SUCCESS;
    }

    /**
     * Garante que gen_random_uuid() seja resolvível pelo search_path da aplicação.
     *
     * Praticamente toda tabela deste projeto usa gen_random_uuid() como default
     * da chave primária. A função é nativa no PostgreSQL 13+; no 12 vem da
     * extensão pgcrypto — e aí há uma armadilha: "CREATE EXTENSION pgcrypto"
     * sem WITH SCHEMA instala em "public", que NÃO faz parte do search_path
     * deste projeto. A extensão existe, a função existe, e o migrate falha
     * mesmo assim com "function gen_random_uuid() does not exist".
     *
     * Por isso a extensão é instalada no primeiro schema do search_path.
     */
    private function garantirGenRandomUuid(string $connection, array $schemas): void
    {
        $db = DB::connection($connection);

        if ($this->uuidResolvivel($connection)) {
            $this->line('  <info>✓</info> gen_random_uuid() disponível');

            return;
        }

        $destino = $schemas[0] ?? null;

        if ($destino === null) {
            return;
        }

        // 1) Instalar, se ainda não existir em lugar nenhum.
        try {
            $db->statement("CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA \"{$destino}\"");
        } catch (\Throwable) {
            // Sem privilégio; a mensagem final orienta o DBA.
        }

        // 2) Atenção: se a extensão JÁ existia (tipicamente em "public"), o
        // comando acima é no-op silencioso — não lança, e não move nada. Só a
        // reverificação revela isso, e aí o caminho é mover de schema.
        if (! $this->uuidResolvivel($connection)) {
            try {
                $db->statement("ALTER EXTENSION pgcrypto SET SCHEMA \"{$destino}\"");
            } catch (\Throwable) {
                // Tratado abaixo pela verificação final.
            }
        }

        if ($this->uuidResolvivel($connection)) {
            $this->line("  <info>✓</info> gen_random_uuid() via pgcrypto em \"{$destino}\"");

            return;
        }

        $this->line('  <fg=yellow>!</> gen_random_uuid() NÃO está disponível.');
        $this->line('    Toda migration deste projeto depende dela. Peça ao DBA, uma única vez:');
        $this->line("    <comment>CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA \"{$destino}\";</comment>");
        $this->line('    (o WITH SCHEMA é essencial: em "public" a função não é vista pelo search_path)');
    }

    private function uuidResolvivel(string $connection): bool
    {
        try {
            DB::connection($connection)->select('SELECT gen_random_uuid()');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
