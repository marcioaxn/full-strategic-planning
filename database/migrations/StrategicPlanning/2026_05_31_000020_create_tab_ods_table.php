<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_ods', function (Blueprint $table) {
            $table->smallInteger('num_ods')->primary();
            $table->string('nom_ods', 120);
            $table->string('nom_ods_abreviado', 60);
            $table->text('dsc_ods')->nullable();
            $table->string('cod_cor', 7)->comment('Cor hexadecimal oficial ONU');
            $table->string('nom_icone', 40)->nullable()->comment('Nome do arquivo em public/img/ods/');
            $table->timestamps();
        });

        DB::statement("COMMENT ON TABLE strategic_planning.tab_ods IS 'Agenda 2030 — 17 Objetivos de Desenvolvimento Sustentável (ODS/ONU)'");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "strategic_planning"."tab_ods" CASCADE');
    }
};
