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
        Schema::table('tab_perspectiva', function (Blueprint $table) {
            $table->integer('num_peso_indicadores')->default(100)->after('dsc_perspectiva');
            $table->integer('num_peso_planos')->default(0)->after('num_peso_indicadores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tab_perspectiva', function (Blueprint $table) {
            $table->dropColumn(['num_peso_indicadores', 'num_peso_planos']);
        });
    }
};
