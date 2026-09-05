<?php

// Prova que a limpeza do banco é completa e, ao mesmo tempo, segura:
// apaga todo dado de domínio e NÃO apaga o histórico de migrations.

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SuperAdministradorSeeder;
use Database\Seeders\TruncarBancoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Os testes deste arquivo truncam o banco por conta própria, invalidando o
 * estado compartilhado que o SeederTestCase mantém entre os testes do processo.
 */
beforeEach(function () {
    $this->invalidarSeedCompartilhada();
});

if (! function_exists('tabelasDeDominio')) {
    /**
     * Lista as tabelas físicas dos schemas de domínio, exceto `migrations`.
     *
     * @return array<int, string> nomes qualificados, ex.: "pei.users"
     */
    function tabelasDeDominio(): array
    {
        $schemas = config('database.connections.pgsql.search_path');
        $placeholders = implode(', ', array_fill(0, count($schemas), '?'));

        $linhas = DB::select(
            "SELECT table_schema, table_name
               FROM information_schema.tables
              WHERE table_type = 'BASE TABLE'
                AND table_schema IN ({$placeholders})
                AND table_name <> 'migrations'
              ORDER BY table_schema, table_name",
            $schemas
        );

        return array_map(fn ($l) => $l->table_schema.'.'.$l->table_name, $linhas);
    }
}

it('encontra as tabelas dos seis schemas de domínio antes de truncar', function () {
    $tabelas = tabelasDeDominio();

    $this->assertNotEmpty(
        $tabelas,
        "Nenhuma tabela encontrada no banco \"{$this->bancoEmUso}\". Execute \"php artisan migrate\" antes dos testes."
    );

    // As cinco tabelas que sustentam o login precisam existir.
    foreach ([
        'pei.users',
        'organization.tab_organizacoes',
        'organization.tab_perfil_acesso',
        'organization.rel_users_tab_organizacoes',
        'organization.rel_users_tab_organizacoes_tab_perfil_acesso',
    ] as $obrigatoria) {
        $this->assertContains(
            $obrigatoria,
            $tabelas,
            "A tabela \"{$obrigatoria}\", exigida para o login, não existe no banco \"{$this->bancoEmUso}\"."
        );
    }
});

it('apaga registros mesmo quando existem chaves estrangeiras entre eles', function () {
    // Monta uma cadeia com FK: organização ← vínculo → usuário.
    $orgId = (string) Str::uuid();
    $userId = (string) Str::uuid();

    DB::table('organization.tab_organizacoes')->insert([
        'cod_organizacao' => $orgId,
        'sgl_organizacao' => 'TESTE',
        'nom_organizacao' => 'Organização temporária de teste',
        'rel_cod_organizacao' => $orgId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('pei.users')->insert([
        'id' => $userId,
        'name' => 'Usuário temporário de teste',
        'email' => 'temporario.teste@exemplo.gov.br',
        'password' => bcrypt('irrelevante'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('organization.rel_users_tab_organizacoes')->insert([
        'id' => (string) Str::uuid(),
        'user_id' => $userId,
        'cod_organizacao' => $orgId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(DB::table('organization.rel_users_tab_organizacoes')->count())->toBeGreaterThan(0);

    $this->seed(TruncarBancoSeeder::class);

    expect(DB::table('pei.users')->count())->toBe(0)
        ->and(DB::table('organization.tab_organizacoes')->count())->toBe(0)
        ->and(DB::table('organization.rel_users_tab_organizacoes')->count())->toBe(0);
});

it('deixa TODAS as tabelas de domínio com zero registros', function () {
    $this->seed(TruncarBancoSeeder::class);

    $comSobras = [];

    foreach (tabelasDeDominio() as $tabela) {
        $total = DB::table($tabela)->count();

        if ($total > 0) {
            $comSobras[$tabela] = $total;
        }
    }

    $this->assertSame(
        [],
        $comSobras,
        'Tabelas que continuaram com registros após o truncate: '.json_encode($comSobras)
    );
});

it('preserva o histórico de migrations', function () {
    $antes = DB::table('pei.migrations')->count();

    $this->assertGreaterThan(
        0,
        $antes,
        "O banco \"{$this->bancoEmUso}\" não tem migrations registradas. Execute \"php artisan migrate\" antes dos testes."
    );

    $this->seed(TruncarBancoSeeder::class);

    $this->assertSame(
        $antes,
        DB::table('pei.migrations')->count(),
        'A tabela "migrations" foi alterada pelo truncate. Isso faria o Laravel tentar recriar todo o esquema.'
    );
});

it('pode ser executada repetidas vezes sem erro', function () {
    $this->seed(TruncarBancoSeeder::class);
    $this->seed(TruncarBancoSeeder::class);

    expect(DB::table('pei.users')->count())->toBe(0);
});

/**
 * Os testes acima deixam o banco vazio de propósito. Este último recompõe o
 * acesso inicial, de modo que ao fim da suíte o banco fique no mesmo estado que
 * `php artisan db:seed` produz — e não sem nenhum usuário para entrar.
 */
it('restaura o acesso inicial ao final da suíte, deixando o banco pronto para uso', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', SuperAdministradorSeeder::EMAIL)->first();

    $this->assertNotNull($user, 'O administrador não foi recriado ao final da suíte.');
    $this->assertTrue($user->isSuperAdmin());

    $this->post('/login', [
        'email' => SuperAdministradorSeeder::EMAIL,
        'password' => SuperAdministradorSeeder::senhaInicial(),
    ]);

    $this->assertAuthenticated();
});
