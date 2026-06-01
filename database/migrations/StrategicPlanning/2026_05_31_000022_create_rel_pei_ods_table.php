<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.rel_pei_ods', function (Blueprint $table) {
            $table->uuid('cod_pei');
            $table->smallInteger('num_ods');
            $table->text('txt_contribuicao')->nullable()->comment('Como o PEI da instituição adere a este ODS');
            $table->string('dsc_intensidade', 10)->default('Media')->comment('Alta | Media | Baixa');
            $table->timestamps();

            $table->primary(['cod_pei', 'num_ods']);

            $table->foreign('cod_pei')
                ->references('cod_pei')->on('strategic_planning.tab_pei')
                ->cascadeOnDelete();

            $table->foreign('num_ods')
                ->references('num_ods')->on('strategic_planning.tab_ods')
                ->cascadeOnDelete();
        });

        DB::statement("COMMENT ON TABLE strategic_planning.rel_pei_ods IS 'Aderência institucional do ciclo PEI à Agenda 2030 (Passo 1 — mapeamento estratégico)'");
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.rel_pei_ods');
    }
};
