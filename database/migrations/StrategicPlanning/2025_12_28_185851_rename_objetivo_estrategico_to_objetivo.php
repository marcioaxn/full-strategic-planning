<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function tableExists(string $schema, string $table): bool
    {
        return (bool) DB::select("
            SELECT EXISTS (
                SELECT FROM information_schema.tables
                WHERE table_schema = ? AND table_name = ?
            )
        ", [$schema, $table])[0]->exists;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->tableExists('strategic_planning', 'tab_objetivo_estrategico')) {
            DB::statement('ALTER TABLE strategic_planning.tab_objetivo_estrategico RENAME TO tab_objetivo');
        }

        if ($this->tableExists('strategic_planning', 'tab_futuro_almejado_objetivo_estrategico')) {
            DB::statement('ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo_estrategico RENAME TO tab_futuro_almejado_objetivo');
        }

        if ($this->tableExists('performance_indicators', 'rel_indicador_objetivo_estrategico_organizacao')) {
            DB::statement('ALTER TABLE performance_indicators.rel_indicador_objetivo_estrategico_organizacao RENAME TO rel_indicador_objetivo_organizacao');
        }
    }

    /**
     * Reverse the migrations.
     *
     * Verifica a existência de cada tabela antes de renomear para garantir
     * idempotência independentemente do schema em que a tabela se encontra
     * (pode diferir entre banco novo e banco migrado da v1).
     */
    public function down(): void
    {
        if ($this->tableExists('performance_indicators', 'rel_indicador_objetivo_organizacao')) {
            DB::statement('ALTER TABLE performance_indicators.rel_indicador_objetivo_organizacao RENAME TO rel_indicador_objetivo_estrategico_organizacao');
        }

        if ($this->tableExists('strategic_planning', 'tab_futuro_almejado_objetivo')) {
            DB::statement('ALTER TABLE strategic_planning.tab_futuro_almejado_objetivo RENAME TO tab_futuro_almejado_objetivo_estrategico');
        }

        if ($this->tableExists('strategic_planning', 'tab_objetivo')) {
            DB::statement('ALTER TABLE strategic_planning.tab_objetivo RENAME TO tab_objetivo_estrategico');
        }
    }
};
