<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
        DB::statement('CREATE SCHEMA IF NOT EXISTS pei;');

        Schema::create('pei.users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('email')->unique();
            $table->smallInteger('ativo')->default(1); // Usuário ativo (1 = sim, 2 = não)
            $table->smallInteger('adm')->default(2); // Administrador (1 = sim, 2 = não)
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->smallInteger('trocarsenha')->default(1); // Deve trocar senha no próximo login (1 = sim, 0 = não)
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->uuid('current_team_id')->nullable(); // Jetstream teams
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('pei.password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.users');
        Schema::dropIfExists('pei.password_reset_tokens');
    }
};
