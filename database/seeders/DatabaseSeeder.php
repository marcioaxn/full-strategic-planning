<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Usuário Administrador
        User::updateOrCreate(
            ['email' => 'user_adm@user_adm.com'],
            [
                'name' => 'Starter Admin',
                'password' => Hash::make('1352@765@1452'),
            ]
        );

        // 2. Popular dados de referência da Agenda 2030 (17 ODS oficiais)
        $this->call(OdsSeeder::class);

        // 3. Criar Estrutura Estratégica Base (PEI, Perspectivas, Objetivos, etc)
        $this->call(BaseStrategicSeeder::class);

        // 4. Popular Dados de Negócio (Planos, Entregas, Indicadores, Riscos)
        $this->call(PEIDataSeeder::class);
    }
}
