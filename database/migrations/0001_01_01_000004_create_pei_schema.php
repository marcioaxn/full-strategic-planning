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
            'strategic_planning',
            'action_plan',
            'performance_indicators',
            'risk_management',
            'organization'
        ];

        foreach ($schemas as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS $schema;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover schemas (CASCADE para remover todas as tabelas dentro deles)
        $schemas = [
            'strategic_planning',
            'action_plan',
            'performance_indicators',
            'risk_management',
            'organization'
        ];

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS $schema CASCADE;");
        }
    }
};
