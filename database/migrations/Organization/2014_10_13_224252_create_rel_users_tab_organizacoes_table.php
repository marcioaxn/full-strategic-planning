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
        Schema::create('organization.rel_users_tab_organizacoes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('organization.tab_organizacoes')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índice composto para evitar duplicatas
            $table->unique(['user_id', 'cod_organizacao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization.rel_users_tab_organizacoes');
    }
};
