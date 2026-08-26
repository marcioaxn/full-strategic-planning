<?php

namespace Database\Seeders;

use App\Models\PerfilAcesso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cria o único usuário necessário para o primeiro acesso: o Super Administrador.
 *
 * Um Super Administrador funcional exige QUATRO registros, não apenas o usuário:
 *
 *   1. pei.users                                                → a conta em si
 *   2. organization.tab_organizacoes                            → a organização raiz (OrganizacaoRaizSeeder)
 *   3. organization.rel_users_tab_organizacoes                  → vínculo usuário ↔ organização
 *   4. organization.rel_users_tab_organizacoes_tab_perfil_acesso → vínculo usuário ↔ perfil Super Admin
 *
 * O registro 4 é o que determina o privilégio: User::isSuperAdmin() consulta o
 * PERFIL vinculado, não a coluna `adm`. Um usuário criado sem esse vínculo
 * consegue autenticar, mas entra no sistema sem enxergar nenhuma organização e
 * sem permissão em nenhum módulo — foi exatamente essa a falha da seeder
 * anterior, que criava apenas o registro 1.
 *
 * PARA ADAPTAR À SUA INSTITUIÇÃO: altere EMAIL, SENHA e NOME abaixo e rode
 * novamente `php artisan db:seed`.
 */
class SuperAdministradorSeeder extends Seeder
{
    private const TABELA_USERS = 'pei.users';

    private const TABELA_REL_ORG = 'organization.rel_users_tab_organizacoes';

    private const TABELA_REL_PERFIL = 'organization.rel_users_tab_organizacoes_tab_perfil_acesso';

    /**
     * E-mail de acesso.
     *
     * Precisa ser um endereço válido em RFC 5322: o domínio (o que vem depois
     * do "@") aceita apenas letras, dígitos, hífen e ponto. Sublinhados como em
     * "@user_adm.com" produzem um endereço que o validador de e-mail do Laravel
     * rejeita — o que travava a edição do próprio usuário administrador na tela
     * de cadastro.
     */
    public const EMAIL = 'admin@pei.gov.br';

    /**
     * Senha inicial.
     *
     * Atende à política de senha forte do sistema
     * (App\Actions\Fortify\PasswordValidationRules): mínimo de 8 caracteres,
     * com maiúscula, minúscula, número e caractere especial.
     */
    public const SENHA = 'Pei@2026#Admin';

    public const NOME = 'Super Administrador';

    public function run(): void
    {
        $this->exigirPreRequisitos();

        $userId = $this->criarOuAtualizarUsuario();

        $this->vincularOrganizacao($userId);
        $this->vincularPerfilSuperAdmin($userId);

        $this->command?->line('  Super Administrador: '.self::EMAIL.'.');
    }

    /**
     * Falha cedo e com mensagem clara caso as seeders anteriores não tenham rodado.
     */
    private function exigirPreRequisitos(): void
    {
        $temOrganizacao = DB::table('organization.tab_organizacoes')
            ->where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)
            ->exists();

        if (! $temOrganizacao) {
            throw new RuntimeException(
                'Organização raiz não encontrada. Rode "php artisan db:seed" (que executa '
                .'OrganizacaoRaizSeeder antes desta seeder) em vez de chamar esta classe isoladamente.'
            );
        }

        $temPerfil = DB::table('organization.tab_perfil_acesso')
            ->where('cod_perfil', PerfilAcesso::SUPER_ADMIN)
            ->exists();

        if (! $temPerfil) {
            throw new RuntimeException(
                'Perfil "Super Administrador" não encontrado. Rode "php artisan db:seed" (que executa '
                .'PerfilAcessoSeeder antes desta seeder) em vez de chamar esta classe isoladamente.'
            );
        }
    }

    private function criarOuAtualizarUsuario(): string
    {
        $dados = [
            'name' => self::NOME,
            'password' => Hash::make(self::SENHA),
            'ativo' => 1,        // conta habilitada
            'adm' => 1,          // flag legada, mantida em sincronia com o perfil
            'trocarsenha' => 0,  // não força a troca no primeiro login
            'email_verified_at' => now(),
            'updated_at' => now(),
        ];

        $userId = DB::table(self::TABELA_USERS)
            ->where('email', self::EMAIL)
            ->value('id');

        if ($userId !== null) {
            DB::table(self::TABELA_USERS)->where('id', $userId)->update($dados);

            return (string) $userId;
        }

        $userId = (string) Str::uuid();

        DB::table(self::TABELA_USERS)->insert($dados + [
            'id' => $userId,
            'email' => self::EMAIL,
            'created_at' => now(),
        ]);

        return $userId;
    }

    private function vincularOrganizacao(string $userId): void
    {
        $chave = [
            'user_id' => $userId,
            'cod_organizacao' => OrganizacaoRaizSeeder::COD_ORGANIZACAO,
        ];

        $existe = DB::table(self::TABELA_REL_ORG)->where($chave)->exists();

        if ($existe) {
            DB::table(self::TABELA_REL_ORG)->where($chave)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table(self::TABELA_REL_ORG)->insert($chave + [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function vincularPerfilSuperAdmin(string $userId): void
    {
        $chave = [
            'user_id' => $userId,
            'cod_organizacao' => OrganizacaoRaizSeeder::COD_ORGANIZACAO,
            'cod_perfil' => PerfilAcesso::SUPER_ADMIN,
            'cod_plano_de_acao' => null, // perfil global, não amarrado a um plano
        ];

        $existe = DB::table(self::TABELA_REL_PERFIL)->where($chave)->exists();

        if ($existe) {
            DB::table(self::TABELA_REL_PERFIL)->where($chave)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table(self::TABELA_REL_PERFIL)->insert($chave + [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
