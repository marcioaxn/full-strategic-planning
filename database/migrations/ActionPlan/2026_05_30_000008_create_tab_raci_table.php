<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plan.tab_raci', function (Blueprint $table) {
            $table->uuid('cod_raci')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('action_plan.tab_plano_de_acao')
                  ->cascadeOnDelete();
            $table->foreignUuid('cod_entrega')->nullable()
                  ->references('cod_entrega')
                  ->on('action_plan.tab_entregas')
                  ->nullOnDelete();
            $table->foreignUuid('user_id')
                  ->references('id')
                  ->on('pei.users')
                  ->cascadeOnDelete();
            $table->char('dsc_papel', 1);
            $table->timestamps();

            $table->index('cod_plano_de_acao');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "action_plan"."tab_raci" CASCADE');
    }
};
