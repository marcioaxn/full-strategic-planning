<?php

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrganizacaoRaizSeeder;
use Database\Seeders\PerfilAcessoSeeder;
use Database\Seeders\SuperAdministradorSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Prova, ponta a ponta, que `php artisan db:seed` entrega um Super Administrador
 * capaz de autenticar e navegar no sistema.
 *
 * A seed é executada UMA vez por processo, não a cada teste: ela trunca cerca de
 * noventa tabelas, o que leva alguns segundos no PostgreSQL (um fsync por
 * arquivo de tabela), e todos os testes abaixo apenas LEEM o resultado — nenhum
 * altera o banco. O último teste, de repetição, executa a seed por conta
 * própria e deixa o banco no mesmo estado.
 */
beforeEach(function () {
    $this->semearAcessoInicialUmaVez();
});

/** Recupera o administrador criado pela seed. */
function superAdmin(): ?User
{
    return User::where('email', SuperAdministradorSeeder::EMAIL)->first();
}

// ---------------------------------------------------------------------------
// 1. Perfis de acesso
// ---------------------------------------------------------------------------

it('cadastra exatamente os quatro perfis de acesso do sistema', function () {
    expect(DB::table('organization.tab_perfil_acesso')->count())->toBe(4);
});

it('usa para cada perfil o identificador esperado pelo código de autorização', function () {
    foreach ([
        PerfilAcesso::SUPER_ADMIN => 'Super Administrador',
        PerfilAcesso::ADMIN_UNIDADE => 'Administrador da Unidade',
        PerfilAcesso::GESTOR_RESPONSAVEL => 'Gestor(a) Responsável',
        PerfilAcesso::GESTOR_SUBSTITUTO => 'Gestor(a) Substituto(a)',
    ] as $codPerfil => $descricao) {
        $perfil = DB::table('organization.tab_perfil_acesso')->where('cod_perfil', $codPerfil)->first();

        $this->assertNotNull(
            $perfil,
            "O perfil \"{$descricao}\" ({$codPerfil}) não foi criado. Policies e Gates dependem desse UUID exato."
        );
        expect($perfil->dsc_perfil)->toBe($descricao);
        expect($perfil->deleted_at)->toBeNull();
    }
});

it('mantém os perfis da seeder em sincronia com as constantes do model', function () {
    $daSeeder = collect(PerfilAcessoSeeder::perfis())->pluck('cod_perfil')->sort()->values()->all();

    $doModel = collect([
        PerfilAcesso::SUPER_ADMIN,
        PerfilAcesso::ADMIN_UNIDADE,
        PerfilAcesso::GESTOR_RESPONSAVEL,
        PerfilAcesso::GESTOR_SUBSTITUTO,
    ])->sort()->values()->all();

    expect($daSeeder)->toBe($doModel);
});

// ---------------------------------------------------------------------------
// 2. Organização raiz
// ---------------------------------------------------------------------------

it('cria a organização raiz da hierarquia, uma só vez', function () {
    expect(Organization::where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)->count())->toBe(1);

    $org = Organization::where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)->first();

    expect($org->cod_organizacao)->toBe(OrganizacaoRaizSeeder::COD_ORGANIZACAO)
        ->and($org->sgl_organizacao)->toBe(OrganizacaoRaizSeeder::SIGLA)
        ->and($org->nom_organizacao)->toBe(OrganizacaoRaizSeeder::NOME);

    $this->assertTrue(
        $org->isRaiz(),
        'A organização não é auto-referenciada. Sem isso ela não aparece no seletor de organização nem no Dashboard.'
    );
});

it('devolve a organização raiz pelo scope usado pelo Dashboard', function () {
    expect(Organization::raiz()->count())->toBe(1);
});

// ---------------------------------------------------------------------------
// 3. Usuário Super Administrador
// ---------------------------------------------------------------------------

it('cria o administrador uma única vez, sem duplicar por e-mail', function () {
    expect(User::where('email', SuperAdministradorSeeder::EMAIL)->count())->toBe(1);
});

it('cria o Super Administrador com os atributos de conta corretos', function () {
    $user = superAdmin();

    $this->assertNotNull($user, 'O usuário '.SuperAdministradorSeeder::EMAIL.' não foi criado pela seed.');

    expect($user->name)->toBe(SuperAdministradorSeeder::NOME)
        ->and($user->email)->toBe(SuperAdministradorSeeder::EMAIL);

    $this->assertTrue($user->isAtivo(), 'A conta foi criada inativa e não conseguiria operar o sistema.');
    $this->assertFalse(
        $user->deveTrocarSenha(),
        'A conta nasceu com troca de senha obrigatória, o que desvia o primeiro acesso para /trocar-senha.'
    );
});

it('grava a senha com hash, nunca em texto puro', function () {
    $user = superAdmin();

    expect($user->password)->not->toBe(SuperAdministradorSeeder::senhaInicial());

    $this->assertTrue(
        Hash::check(SuperAdministradorSeeder::senhaInicial(), $user->password),
        'A senha resolvida por SuperAdministradorSeeder::senhaInicial() não confere com o hash gravado no banco.'
    );
});

// ---------------------------------------------------------------------------
// 4. Regressão do defeito relatado: e-mail com domínio inválido
// ---------------------------------------------------------------------------

it('usa um e-mail com domínio válido, sem caractere especial depois do @', function () {
    $email = SuperAdministradorSeeder::EMAIL;
    $dominio = substr($email, strpos($email, '@') + 1);

    $this->assertMatchesRegularExpression(
        '/^[A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?(\.[A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?)+$/',
        $dominio,
        "O domínio \"{$dominio}\" contém caractere não permitido. Domínios aceitam apenas letras, "
        .'dígitos, hífen e ponto — foi um sublinhado em "@user_adm.com" que originou o problema relatado.'
    );
});

it('usa um e-mail aceito pelo validador de e-mail do Laravel', function () {
    $validador = Validator::make(
        ['email' => SuperAdministradorSeeder::EMAIL],
        ['email' => ['required', 'string', 'email', 'max:255']]
    );

    $this->assertTrue(
        $validador->passes(),
        'O e-mail do administrador é rejeitado pela mesma validação usada na tela de cadastro de usuários, '
        .'o que impediria editar ou salvar o próprio administrador. Erros: '.$validador->errors()->first('email')
    );

    $this->assertNotFalse(
        filter_var(SuperAdministradorSeeder::EMAIL, FILTER_VALIDATE_EMAIL),
        'O e-mail do administrador não é válido segundo o filtro nativo do PHP.'
    );
});

it('usa uma senha que atende à política de senha forte do sistema', function () {
    $regras = new class
    {
        use PasswordValidationRules;

        /** @return array<int, mixed> */
        public function regras(): array
        {
            return $this->passwordRules();
        }
    };

    $validador = Validator::make(
        [
            'password' => SuperAdministradorSeeder::senhaInicial(),
            'password_confirmation' => SuperAdministradorSeeder::senhaInicial(),
        ],
        ['password' => $regras->regras()]
    );

    $this->assertTrue(
        $validador->passes(),
        'A senha inicial não passa nas regras de App\Actions\Fortify\PasswordValidationRules, '
        .'então seria rejeitada na tela de troca de senha. Erros: '.$validador->errors()->first('password')
    );
});

// ---------------------------------------------------------------------------
// 5. Vínculos que efetivamente concedem o privilégio
// ---------------------------------------------------------------------------

it('vincula o administrador à organização raiz', function () {
    $user = superAdmin();

    $vinculo = DB::table('organization.rel_users_tab_organizacoes')
        ->where('user_id', $user->id)
        ->where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)
        ->whereNull('deleted_at')
        ->first();

    $this->assertNotNull($vinculo, 'Falta o vínculo em organization.rel_users_tab_organizacoes.');
});

it('vincula o administrador ao perfil Super Administrador', function () {
    $user = superAdmin();

    $vinculo = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
        ->where('user_id', $user->id)
        ->where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)
        ->where('cod_perfil', PerfilAcesso::SUPER_ADMIN)
        ->whereNull('deleted_at')
        ->first();

    $this->assertNotNull(
        $vinculo,
        'Falta o vínculo de perfil. Sem ele User::isSuperAdmin() é falso e o usuário entra sem permissão alguma — '
        .'era esse o defeito da seeder anterior.'
    );

    $this->assertNull(
        $vinculo->cod_plano_de_acao,
        'O perfil de Super Administrador deve ser global, não amarrado a um plano de ação específico.'
    );
});

it('reconhece o usuário como Super Administrador', function () {
    $this->assertTrue(
        superAdmin()->isSuperAdmin(),
        'User::isSuperAdmin() retornou falso — o usuário não teria acesso a nenhum módulo do sistema.'
    );
});

it('dá ao administrador escopo sobre a organização raiz', function () {
    $user = superAdmin();

    expect($user->organizacaoIdsPermitidas())->toContain(OrganizacaoRaizSeeder::COD_ORGANIZACAO);

    $this->assertTrue(
        $user->podeAcessarOrganizacao(OrganizacaoRaizSeeder::COD_ORGANIZACAO),
        'O administrador não pode acessar a própria organização raiz.'
    );
});

// ---------------------------------------------------------------------------
// 6. Login de verdade, pela rota HTTP
// ---------------------------------------------------------------------------

it('autentica pela tela de login com as credenciais criadas pela seed', function () {
    $resposta = $this->post('/login', [
        'email' => SuperAdministradorSeeder::EMAIL,
        'password' => SuperAdministradorSeeder::senhaInicial(),
    ]);

    $this->assertAuthenticated();
    $resposta->assertRedirect(route('dashboard', absolute: false));
});

it('recusa o login com senha incorreta', function () {
    $this->post('/login', [
        'email' => SuperAdministradorSeeder::EMAIL,
        'password' => 'senha-errada',
    ]);

    $this->assertGuest();
});

it('permite abrir o Dashboard logo após o login, sem desvio para troca de senha', function () {
    $this->post('/login', [
        'email' => SuperAdministradorSeeder::EMAIL,
        'password' => SuperAdministradorSeeder::senhaInicial(),
    ]);

    $this->get('/dashboard')->assertOk();
});

// ---------------------------------------------------------------------------
// 7. Repetição
// ---------------------------------------------------------------------------

it('pode ser executada mais de uma vez sem duplicar registros nem falhar', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $userId = superAdmin()->id;

    expect(User::where('email', SuperAdministradorSeeder::EMAIL)->count())->toBe(1)
        ->and(Organization::where('cod_organizacao', OrganizacaoRaizSeeder::COD_ORGANIZACAO)->count())->toBe(1)
        ->and(DB::table('organization.tab_perfil_acesso')->count())->toBe(4)
        ->and(DB::table('organization.rel_users_tab_organizacoes')->where('user_id', $userId)->count())->toBe(1)
        ->and(DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')->where('user_id', $userId)->count())->toBe(1);

    $this->assertTrue(superAdmin()->isSuperAdmin());
});
