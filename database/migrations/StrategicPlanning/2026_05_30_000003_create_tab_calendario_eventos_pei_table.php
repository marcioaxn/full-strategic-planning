<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_calendario_eventos_pei', function (Blueprint $table) {
            $table->uuid('cod_evento')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->cascadeOnDelete();
            $table->string('dsc_titulo', 200);
            $table->text('dsc_objetivo')->nullable();
            $table->date('dte_evento');
            $table->text('dsc_participantes')->nullable();
            $table->string('dsc_tipo_evento', 50)->default('Reunião');
            $table->boolean('bln_realizado')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('cod_pei');
            $table->index('dte_evento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_calendario_eventos_pei');
    }
};
