<?php

namespace Tests\Feature\UserManagement;

use App\Livewire\UserManagement\ListarUsuarios;
use App\Mail\WelcomeUserMail;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
        Mail::fake();

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

        $email = 'usuario.cadastro.'.Str::uuid().'@example.test';
        $this->createdEmails[] = $email;

        Livewire::actingAs($admin)
            ->test(ListarUsuarios::class)
            ->set('gerarSenhaAutomatica', true)
            ->set('enviarEmailBoasVindas', true)
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
            ->assertHasNoErrors();

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

        Mail::assertQueued(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($usuario): bool {
            return $mail->user->is($usuario)
                && strlen($mail->password) >= 12
                && Hash::check($mail->password, $usuario->password);
        });
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
            DB::table('users')->whereIn('email', $emails)->delete();
        }
    }
}
