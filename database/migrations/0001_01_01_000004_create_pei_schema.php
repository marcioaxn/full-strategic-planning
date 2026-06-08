<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar schemas para separação de domínios
        $schemas = [
            'pei',
            'strategic_planning',
            'action_plan',
            'performance_indicators',
            'risk_management',
            'organization',
        ];

        foreach ($schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS $schema;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * O schema "pei" é intencionalmente excluído do DROP porque contém a
     * tabela "migrations" que o Laravel precisa para registrar o rollback
     * desta própria migration. Remover "pei" aqui causaria
     * SQLSTATE[42P01] na tentativa de deletar o registro da migration.
     * As tabelas dentro de "pei" já foram removidas pelas down() individuais;
     * o schema vazio pode ser descartado manualmente se necessário.
     */
    public function down(): void
    {
        $schemas = [
            'strategic_planning',
            'action_plan',
            'performance_indicators',
            'risk_management',
            'organization',
        ];

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS {$schema} CASCADE;");
        }
    }
};
