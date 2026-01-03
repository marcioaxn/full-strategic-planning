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
        // Strategic Planning Domain (PEI, BSC, SWOT, etc.)
        DB::statement('CREATE SCHEMA IF NOT EXISTS strategic_planning');

        // Action Plan Domain (Projects, Deliverables, Tasks)
        DB::statement('CREATE SCHEMA IF NOT EXISTS action_plan');

        // Performance Indicators Domain (KPIs, Goals, Results)
        DB::statement('CREATE SCHEMA IF NOT EXISTS performance_indicators');

        // Risk Management Domain (Risks, Matrix, Mitigation)
        DB::statement('CREATE SCHEMA IF NOT EXISTS risk_management');

        // Organization Domain (Multi-tenancy, Structures, Access Profiles - except Users/Auth which stay in public)
        DB::statement('CREATE SCHEMA IF NOT EXISTS organization');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping schemas in reverse dependency order (if strict)
        // Using CASCADE to drop tables inside them
        DB::statement('DROP SCHEMA IF EXISTS risk_management CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS performance_indicators CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS action_plan CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS strategic_planning CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS organization CASCADE');
    }
};
