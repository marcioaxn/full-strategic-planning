<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->jsonb('json_smart')->nullable()->after('dsc_calculation_type');
        });
    }

    public function down(): void
    {
        Schema::table('performance_indicators.tab_indicador', function (Blueprint $table) {
            $table->dropColumn('json_smart');
        });
    }
};
