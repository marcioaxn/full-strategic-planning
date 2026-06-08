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
        Schema::create('pei.tab_status', function (Blueprint $table) {
            $table->uuid('cod_status')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('dsc_status')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS "pei"."tab_status" CASCADE');
    }
};
