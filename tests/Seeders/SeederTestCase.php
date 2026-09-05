<?php

namespace Tests\Seeders;

use Database\Seeders\DatabaseSeeder;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Caso de teste da suíte "Seeders".
 *
 * Diferente das demais suítes do projeto, esta roda contra o BANCO CONFIGURADO
 * NO .env — o mesmo banco que a aplicação usa. É proposital: o objetivo destes
 * testes é provar que `php artisan db:seed` funciona na instalação real, e não
 * em um banco de laboratório que pode nem existir no servidor.
 *
 * Consequências que quem for executar a suíte precisa conhecer:
 *
 * - A seed de acesso inicial NÃO apaga dados: desde a remoção da truncagem do
 *   caminho padrão do `db:seed`, as três etapas apenas criam o que falta ou
 *   atualizam o que já existe. Rodar esta suíte em um banco com dados reais não
 *   destrói nada — no máximo regrava os 4 perfis, a organização raiz e a conta
 *   do administrador.
 * - A exceção é TruncarBancoSeederTest, que exercita a seeder destrutiva de
 *   propósito. Esse arquivo só roda quando SEED_TEST_ALLOW_TRUNCATE=true.
 * - A suíte NÃO usa RefreshDatabase: nenhuma migration é executada e nada é
 *   revertido no final. O estado que sobra no banco é o resultado da seed,
 *   que é exatamente o estado desejado de instalação.
 * - Em .env com APP_ENV=production a suíte é PULADA, a menos que a variável
 *   SEED_TEST_ALLOW_PRODUCTION=true seja informada explicitamente.
 */
abstract class SeederTestCase extends TestCase
{
    /**
     * Nome do banco efetivamente usado por este teste (para mensagens de erro).
     */
    protected string $bancoEmUso = '';

    /**
     * A seed de acesso inicial já rodou neste processo de teste?
     *
     * Precisa ser propriedade estática de classe, e não uma variável `static`
     * dentro do closure de `beforeEach`: o Pest religa o closure a cada teste,
     * o que recria o objeto Closure e zera as variáveis estáticas dele.
     */
    private static bool $seedJaExecutada = false;

    /**
     * Executa a seed de acesso inicial apenas na primeira chamada do processo.
     *
     * A seed trunca cerca de noventa tabelas e o PostgreSQL grava em disco cada
     * arquivo truncado, o que custa dezenas de segundos. Como os testes que a
     * consomem apenas LEEM o resultado, executá-la uma vez é suficiente e evita
     * multiplicar esse custo por teste.
     */
    protected function semearAcessoInicialUmaVez(): void
    {
        if (self::$seedJaExecutada) {
            return;
        }

        $this->seed(DatabaseSeeder::class);
        self::$seedJaExecutada = true;
    }

    /**
     * Marca a seed como não executada — usado pelos testes que truncam o banco
     * por conta própria e, com isso, invalidam o estado compartilhado.
     */
    protected function invalidarSeedCompartilhada(): void
    {
        self::$seedJaExecutada = false;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $env = $this->lerEnvDoProjeto();

        $this->exigirAutorizacaoEmProducao($env);
        $this->apontarConexaoParaBancoDoProjeto($env);
    }

    /**
     * Lê o arquivo .env da raiz do projeto SEM alterar o ambiente do processo.
     *
     * O phpunit.xml sobrescreve DB_DATABASE para o banco de laboratório das
     * demais suítes; por isso a configuração real precisa vir do arquivo.
     *
     * @return array<string, string>
     */
    private function lerEnvDoProjeto(): array
    {
        $caminho = base_path('.env');

        if (! is_file($caminho)) {
            $this->markTestSkipped(
                'Arquivo .env não encontrado em '.$caminho.'. '
                .'Copie o .env.example, configure a conexão do banco e rode novamente.'
            );
        }

        return Dotenv::createArrayBacked(base_path())->safeLoad();
    }

    /**
     * @param  array<string, string>  $env
     */
    private function exigirAutorizacaoEmProducao(array $env): void
    {
        if (($env['APP_ENV'] ?? 'local') !== 'production') {
            return;
        }

        $autorizado = $_SERVER['SEED_TEST_ALLOW_PRODUCTION']
            ?? $_ENV['SEED_TEST_ALLOW_PRODUCTION']
            ?? getenv('SEED_TEST_ALLOW_PRODUCTION');

        if (filter_var($autorizado, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $this->markTestSkipped(
            'O .env está com APP_ENV=production e esta suíte APAGA todos os dados do banco. '
            .'Faça backup e, se realmente quiser executá-la, rode: '
            .'SEED_TEST_ALLOW_PRODUCTION=true php artisan test --testsuite=Seeders '
            .'(no Windows PowerShell: $env:SEED_TEST_ALLOW_PRODUCTION=\'true\'; php artisan test --testsuite=Seeders).'
        );
    }

    /**
     * @param  array<string, string>  $env
     */
    private function apontarConexaoParaBancoDoProjeto(array $env): void
    {
        $banco = $env['DB_DATABASE'] ?? null;

        if (! $banco) {
            $this->markTestSkipped('DB_DATABASE não está definido no .env do projeto.');
        }

        $this->bancoEmUso = $banco;

        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => $env['DB_HOST'] ?? '127.0.0.1',
            'database.connections.pgsql.port' => $env['DB_PORT'] ?? '5432',
            'database.connections.pgsql.database' => $banco,
            'database.connections.pgsql.username' => $env['DB_USERNAME'] ?? 'postgres',
            'database.connections.pgsql.password' => $env['DB_PASSWORD'] ?? '',
        ]);

        // Descarta qualquer conexão já aberta com a configuração do phpunit.xml.
        DB::purge('pgsql');
    }
}
