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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->boolean('is_encrypted')->default(false);
            $table->string('description')->nullable(); // Para ajudar na UI
            $table->timestamps();
        });

        // Inserir configurações padrão iniciais
        DB::table('system_settings')->insert([
            [
                'key' => 'ai_enabled',
                'value' => '1', // True
                'type' => 'boolean',
                'is_encrypted' => false,
                'description' => 'Habilitar recursos de Inteligência Artificial no sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_provider',
                'value' => 'gemini',
                'type' => 'string',
                'is_encrypted' => false,
                'description' => 'Provedor de IA padrão (gemini, openai)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_api_key',
                'value' => null, // Será preenchido via UI ou migração posterior se desejado
                'type' => 'string',
                'is_encrypted' => true,
                'description' => 'Chave de API do provedor de IA selecionado',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};