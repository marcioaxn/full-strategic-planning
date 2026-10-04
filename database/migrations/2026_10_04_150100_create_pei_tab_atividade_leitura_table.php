<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Até quando cada pessoa já viu a aba "Atividade" do sino.
 *
 * Uma linha por usuário: o contador de atividade nova conta o que foi gravado
 * na auditoria depois de dte_visto_ate. Tabela própria para não tocar a
 * tabela users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pei.tab_atividade_leitura', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->timestamp('dte_visto_ate')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('pei.users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pei.tab_atividade_leitura');
    }
};
