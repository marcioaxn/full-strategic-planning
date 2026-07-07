<?php

namespace Tests\Feature\UserManagement;

use App\Livewire\UserManagement\ListarUsuarios;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use App\Notifications\WelcomeSetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ListarUsuariosCadastroSemTruncateTest extends TestCase
{
    /** @var array<int, string> */
    private array $createdUserIds = [];

    /** @var array<int, string> */
    private array $createdEmails = [];

    protected function tearDown(): void
    {
        $this->cleanupCreatedUsers();

        parent::tearDown();
    }

    public function test_administrador_cadastra_usuario_com_vinculo_e_email_sem_truncar_tabelas(): void
    {
        Notification::fake();

        $admin = User::create([
            'name' => 'Administrador Teste Cadastro',
            'email' => 'admin.cadastro.'.Str::uuid().'@example.test',
            'password' => Hash::make('SenhaForte!123'),
            'ativo' => true,
            'adm' => true,
            'trocarsenha' => 0,
        ]);

        $this->trackCreatedUser($admin);

        $organizacao = Organization::query()->first();
        $perfil = PerfilAcesso::query()->first();

        if (! $organizacao || ! $perfil) {
            $this->markTestSkipped('O banco precisa ter ao menos uma organizacao e um perfil de acesso cadastrados.');
        }

        // Fonte de verdade de Super Admin é o perfil vinculado, não a flag legada "adm".
        $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $organizacao->cod_organizacao]);
        $admin->unsetRelation('perfisAcesso');

        $email = 'usuario.cadastro.'.Str::uuid().'@example.test';
        $this->createdEmails[] = $email;

        Livewire::actingAs($admin)
            ->test(ListarUsuarios::class)
            ->set('modoSenhaInicial', 'enviar_link')
            ->set('form.name', 'Usuario Teste Cadastro')
            ->set('form.email', $email)
            ->set('form.ativo', true)
            ->set('form.trocarsenha', 0)
            ->set('form.vinculos', [[
                'org_id' => $organizacao->cod_organizacao,
                'perfil_id' => $perfil->cod_perfil,
                'org_label' => $organizacao->sgl_organizacao,
                'perfil_label' => $perfil->dsc_perfil,
            ]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showTransactionModal', true)
            ->assertSet('transactionStyle', 'success');

        $usuario = User::query()->where('email', $email)->first();

        $this->assertNotNull($usuario);
        $this->trackCreatedUser($usuario);

        $this->assertSame('Usuario Teste Cadastro', $usuario->name);
        $this->assertTrue((bool) $usuario->ativo);
        $this->assertSame(1, (int) $usuario->trocarsenha);

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'email' => $email,
            'trocarsenha' => 1,
        ]);

        $this->assertDatabaseHas('rel_users_tab_organizacoes', [
            'user_id' => $usuario->id,
            'cod_organizacao' => $organizacao->cod_organizacao,
        ]);

        $this->assertDatabaseHas('rel_users_tab_organizacoes_tab_perfil_acesso', [
            'user_id' => $usuario->id,
            'cod_organizacao' => $organizacao->cod_organizacao,
            'cod_perfil' => $perfil->cod_perfil,
        ]);

        Notification::assertSentTo($usuario, WelcomeSetPasswordNotification::class);
    }

    public function test_administrador_cadastra_usuario_com_senha_manual_sem_enviar_email_e_sem_truncar_tabelas(): void
    {
        Notification::fake();

        $admin = User::create([
            'name' => 'Administrador Teste Cadastro Manual',
            'email' => 'admin.cadastro.manual.'.Str::uuid().'@example.test',
            'password' => Hash::make('SenhaForte!123'),
            'ativo' => true,
            'adm' => true,
            'trocarsenha' => 0,
        ]);

        $this->trackCreatedUser($admin);

        $organizacao = Organization::query()->first();
        $perfil = PerfilAcesso::query()->first();

        if (! $organizacao || ! $perfil) {
            $this->markTestSkipped('O banco precisa ter ao menos uma organizacao e um perfil de acesso cadastrados.');
        }

        // Fonte de verdade de Super Admin é o perfil vinculado, não a flag legada "adm".
        $admin->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $organizacao->cod_organizacao]);
        $admin->unsetRelation('perfisAcesso');

        $email = 'usuario.cadastro.manual.'.Str::uuid().'@example.test';
        $senha = 'SenhaManual!123';
        $this->createdEmails[] = $email;

        Livewire::actingAs($admin)
            ->test(ListarUsuarios::class)
            ->set('modoSenhaInicial', 'senha_manual')
            ->set('form.name', 'Usuario Teste Cadastro Manual')
            ->set('form.email', $email)
            ->set('form.password', $senha)
            ->set('form.password_confirmation', $senha)
            ->set('form.ativo', true)
            ->set('form.trocarsenha', 0)
            ->set('form.vinculos', [[
                'org_id' => $organizacao->cod_organizacao,
                'perfil_id' => $perfil->cod_perfil,
                'org_label' => $organizacao->sgl_organizacao,
                'perfil_label' => $perfil->dsc_perfil,
            ]])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showTransactionModal', true)
            ->assertSet('transactionStyle', 'success');

        $usuario = User::query()->where('email', $email)->first();

        $this->assertNotNull($usuario);
        $this->trackCreatedUser($usuario);

        $this->assertSame('Usuario Teste Cadastro Manual', $usuario->name);
        $this->assertTrue(Hash::check($senha, $usuario->password));
        $this->assertSame(0, (int) $usuario->trocarsenha);

        $this->assertDatabaseHas('rel_users_tab_organizacoes', [
            'user_id' => $usuario->id,
            'cod_organizacao' => $organizacao->cod_organizacao,
        ]);

        $this->assertDatabaseHas('rel_users_tab_organizacoes_tab_perfil_acesso', [
            'user_id' => $usuario->id,
            'cod_organizacao' => $organizacao->cod_organizacao,
            'cod_perfil' => $perfil->cod_perfil,
        ]);

        Notification::assertNotSentTo($usuario, WelcomeSetPasswordNotification::class);
    }

    private function trackCreatedUser(User $user): void
    {
        $this->createdUserIds[] = (string) $user->id;
        $this->createdEmails[] = (string) $user->email;
    }

    private function cleanupCreatedUsers(): void
    {
        $ids = array_values(array_unique(array_filter($this->createdUserIds)));
        $emails = array_values(array_unique(array_filter($this->createdEmails)));

        if ($ids === [] && $emails === []) {
            return;
        }

        $idsByEmail = $emails === []
            ? []
            : User::query()->whereIn('email', $emails)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $ids = array_values(array_unique(array_merge($ids, $idsByEmail)));

        if ($ids !== []) {
            DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')->whereIn('user_id', $ids)->delete();
            DB::table('rel_users_tab_organizacoes')->whereIn('user_id', $ids)->delete();
            DB::table('users')->whereIn('id', $ids)->delete();
        }

        if ($emails !== []) {
            DB::table('password_reset_tokens')->whereIn('email', $emails)->delete();
            DB::table('users')->whereIn('email', $emails)->delete();
        }
    }
}
