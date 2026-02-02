<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plan.rel_plano_organizacao', function (Blueprint $table) {
            $table->uuid('cod_plano_de_acao');
            $table->uuid('cod_organizacao');
            
            $table->primary(['cod_plano_de_acao', 'cod_organizacao']);
            
            $table->foreign('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->onDelete('cascade');
                  
            $table->foreign('cod_organizacao')
                  ->references('cod_organizacao')
                  ->on('organization.tab_organizacoes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plan.rel_plano_organizacao');
    }
};