<?php

namespace Database\Seeders;

use App\Models\Lead;
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
        User::updateOrCreate(
            ['email' => 'user_adm@user_adm.com'],
            [
                'name' => 'Starter Admin',
                'password' => Hash::make('1352@765@1452'),
            ]
        );

        Lead::factory()
            ->count(25)
            ->create();
    }
}
