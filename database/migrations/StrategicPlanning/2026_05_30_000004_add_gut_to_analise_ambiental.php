<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('strategic_planning.tab_analise_ambiental', function (Blueprint $table) {
            $table->smallInteger('num_gravidade')->default(3)->after('num_impacto');
            $table->smallInteger('num_urgencia')->default(3)->after('num_gravidade');
            $table->smallInteger('num_tendencia')->default(3)->after('num_urgencia');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE strategic_planning.tab_analise_ambiental DROP COLUMN IF EXISTS num_gravidade');
        DB::statement('ALTER TABLE strategic_planning.tab_analise_ambiental DROP COLUMN IF EXISTS num_urgencia');
        DB::statement('ALTER TABLE strategic_planning.tab_analise_ambiental DROP COLUMN IF EXISTS num_tendencia');
    }
};
