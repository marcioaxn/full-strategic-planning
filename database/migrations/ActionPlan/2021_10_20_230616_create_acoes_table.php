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
        Schema::create('action_plan.acoes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('table_id')->nullable(false);
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('table')->nullable(false);
            $table->text('acao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices para melhorar performance de consultas
            $table->index(['table', 'table_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acoes');
    }
};
