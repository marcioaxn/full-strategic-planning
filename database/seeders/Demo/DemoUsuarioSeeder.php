<?php

namespace Database\Seeders\Demo;

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Cria (ou garante a existência de) um usuário dedicado para apresentações de demo.
 * Nunca recria se já existir — apenas garante que está ativo e com perfil Super Admin.
 */
class DemoUsuarioSeeder extends Seeder
{
    const EMAIL = 'demo@planejamento.gov.br';
    const SENHA  = 'Demo@2025!';

    public function run(): void
    {
        $org = Organization::whereNull('rel_cod_organizacao')->first();

        if (! $org) {
            $this->command->error('Nenhuma organização raiz encontrada. Abortando criação do usuário de demo.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name'        => 'Usuário Demo — Apresentação',
                'password'    => Hash::make(self::SENHA),
                'ativo'       => true,
                'adm'         => true,
                'trocarsenha' => 0,
            ]
        );

        // Vincula à organização raiz com perfil Super Admin (upsert via tabela pivot)
        $jaVinculado = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $user->id)
            ->where('cod_organizacao', $org->cod_organizacao)
            ->where('cod_perfil', PerfilAcesso::SUPER_ADMIN)
            ->whereNull('deleted_at')
            ->exists();

        if (! $jaVinculado) {
            DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
                'user_id'         => $user->id,
                'cod_organizacao' => $org->cod_organizacao,
                'cod_perfil'      => PerfilAcesso::SUPER_ADMIN,
                'cod_plano_de_acao' => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // Vincula também à tabela de organizações do usuário
        $jaNaOrg = DB::table('organization.rel_users_tab_organizacoes')
            ->where('user_id', $user->id)
            ->where('cod_organizacao', $org->cod_organizacao)
            ->whereNull('deleted_at')
            ->exists();

        if (! $jaNaOrg) {
            DB::table('organization.rel_users_tab_organizacoes')->insert([
                'user_id'         => $user->id,
                'cod_organizacao' => $org->cod_organizacao,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->command->info("  Usuário de demo: " . self::EMAIL . " | Senha: " . self::SENHA);
    }
}
