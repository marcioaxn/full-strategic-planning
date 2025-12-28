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
        Schema::create('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes')->cascadeOnDelete();
            $table->foreignUuid('cod_plano_de_acao')->nullable()->references('cod_plano_de_acao')->on('tab_plano_de_acao')->cascadeOnDelete();
            $table->foreignUuid('cod_perfil')->references('cod_perfil')->on('tab_perfil_acesso')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índice composto para evitar duplicatas e melhorar performance
            $table->unique(['user_id', 'cod_organizacao', 'cod_plano_de_acao', 'cod_perfil'], 'rel_user_org_plano_perfil_unique');
            $table->index('user_id');
            $table->index('cod_organizacao');
            $table->index('cod_plano_de_acao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rel_users_tab_organizacoes_tab_perfil_acesso');
    }
};
