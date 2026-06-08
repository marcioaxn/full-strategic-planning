<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_partes_interessadas', function (Blueprint $table) {
            $table->uuid('cod_parte')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->string('nom_parte', 150);
            $table->string('dsc_tipo', 20)->default('Externo');
            $table->smallInteger('num_interesse')->default(3);
            $table->smallInteger('num_influencia')->default(3);
            $table->text('txt_estrategia_engajamento')->nullable();
            $table->smallInteger('num_ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "strategic_planning"."tab_partes_interessadas" CASCADE');
    }
};
