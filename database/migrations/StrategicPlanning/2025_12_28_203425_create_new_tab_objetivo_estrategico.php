<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('strategic_planning.tab_objetivo_estrategico', function (Blueprint $table) {
            $table->uuid('cod_objetivo_estrategico')->primary();
            $table->text('nom_objetivo_estrategico');
            
            // Relacionamentos
            $table->uuid('cod_pei');
            $table->uuid('cod_organizacao');

            // Timestamps e SoftDeletes
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->onDelete('cascade');

            $table->foreign('cod_organizacao')
                  ->references('cod_organizacao')
                  ->on('organization.tab_organizacoes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_objetivo_estrategico');
    }
};