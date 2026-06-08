<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.rel_objetivo_ods', function (Blueprint $table) {
            $table->uuid('cod_objetivo');
            $table->smallInteger('num_ods');
            $table->text('txt_contribuicao')->nullable()->comment('Como o objetivo contribui para este ODS');
            $table->timestamps();

            $table->primary(['cod_objetivo', 'num_ods']);

            $table->foreign('cod_objetivo')
                ->references('cod_objetivo')->on('strategic_planning.tab_objetivo')
                ->cascadeOnDelete();

            $table->foreign('num_ods')
                ->references('num_ods')->on('strategic_planning.tab_ods')
                ->cascadeOnDelete();
        });

        DB::statement("COMMENT ON TABLE strategic_planning.rel_objetivo_ods IS 'Vínculo N:N entre Objetivos Estratégicos e ODS da Agenda 2030'");
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "strategic_planning"."rel_objetivo_ods" CASCADE');
    }
};
