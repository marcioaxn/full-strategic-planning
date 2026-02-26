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
        Schema::create('organization.rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->uuid('cod_organizacao');
            $table->uuid('cod_plano_de_acao')->nullable();
            $table->uuid('cod_perfil');
            
            $table->foreign('user_id', 'fk_uopp_user')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('cod_organizacao', 'fk_uopp_org')->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();
            $table->foreign('cod_plano_de_acao', 'fk_uopp_plano')->references('cod_plano_de_acao')->on('action_plan.tab_plano_de_acao')->cascadeOnDelete();
            $table->foreign('cod_perfil', 'fk_uopp_perfil')->references('cod_perfil')->on('organization.tab_perfil_acesso')->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();

            // Índice composto para evitar duplicatas e melhorar performance
            $table->unique(['user_id', 'cod_organizacao', 'cod_plano_de_acao', 'cod_perfil'], 'rel_uopp_unique');
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
        Schema::dropIfExists('organization.rel_users_tab_organizacoes_tab_perfil_acesso');
    }
};
