<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\SystemSetting;
use App\Models\StrategicAlert;
use App\Models\TabAudit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MIDRSupportSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organization::all();
        
        // 1. Usuários por Perfil (Secretários e Técnicos)
        $perfis = [
            ['name' => 'Usuário Administrador', 'email' => 'user_adm@mdr.gov.br', 'org' => 'MIDR'],
            ['name' => 'Secretário Nacional SEDEC', 'email' => 'sedec@mdr.gov.br', 'org' => 'SEDEC'],
            ['name' => 'Secretário Nacional SNSH', 'email' => 'snsh@mdr.gov.br', 'org' => 'SNSH'],
            ['name' => 'Técnico SDR Regional', 'email' => 'sdr@mdr.gov.br', 'org' => 'SDR'],
            ['name' => 'Gestor Financeiro SNFI', 'email' => 'snfi@mdr.gov.br', 'org' => 'SNFI'],
        ];

        foreach ($perfis as $p) {
            $org = $orgs->where('sgl_organizacao', $p['org'])->first();
            $password = ($p['email'] === 'user_adm@mdr.gov.br') ? 'Nbsdjp@1352464' : '12345678';
            
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'trocarsenha' => 0,
                ]
            );

            // Relacionamento com Organização
            DB::table('organization.rel_users_tab_organizacoes')->updateOrInsert(
                ['user_id' => $user->id, 'cod_organizacao' => $org->cod_organizacao ?? $orgs->random()->cod_organizacao]
            );
        }

        // 2. Configurações do Sistema
        $settings = [
            ['key' => 'system_name', 'val' => 'Plano Estratégico Integrado - MIDR'],
            ['key' => 'cycle_current', 'val' => '2023-2027'],
            ['key' => 'alert_threshold', 'val' => '75'], // 75%
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(['key' => $s['key']], ['value' => $s['val']]);
        }

        // 3. Alertas Estratégicos
        StrategicAlert::create([
            'user_id' => User::first()->id,
            'title' => 'Indicador em Atraso: Transposição PISF',
            'message' => 'O percentual de execução do Eixo Norte está abaixo da meta para o mês atual.',
            'type' => 'danger',
            'icon' => 'bi-exclamation-triangle',
            'read_at' => null,
        ]);

        // 4. Auditoria (Simulação)
        TabAudit::create([
            'user_id' => User::first()->id,
            'acao' => 'Aprovou Plano de Ação: Operação Carro-Pipa 2024',
            'antes' => 'Status: Pendente',
            'depois' => 'Status: Aprovado',
            'table' => 'action_plan.tab_plano_de_acao',
            'column_name' => 'bln_status',
            'table_id' => (string) \Illuminate\Support\Str::uuid(),
            'ip' => '127.0.0.1',
        ]);
    }
}
