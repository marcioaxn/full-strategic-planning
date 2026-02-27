<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('strategic_planning.tab_valores', function (Blueprint $table) {
            $table->uuid('cod_valor')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nom_valor')->nullable(false);
            $table->text('dsc_valor')->nullable(false);
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('strategic_planning.tab_pei')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_valores');
    }
};
