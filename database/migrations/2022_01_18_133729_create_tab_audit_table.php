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
        Schema::create('tab_audit', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('acao')->nullable(false);
            $table->text('antes')->nullable(true);
            $table->text('depois')->nullable(true);
            $table->string('table')->nullable(false);
            $table->string('column_name')->nullable(false);
            $table->string('data_type')->nullable(true);
            $table->string('table_id')->nullable(false);
            $table->string('ip')->nullable(false);
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamp('dte_expired_at')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para melhorar performance
            $table->index(['table', 'table_id']);
            $table->index('user_id');
            $table->index('acao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_audit');
    }
};
