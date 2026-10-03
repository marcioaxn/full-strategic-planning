<?php

use App\Models\User;
use App\Support\VersaoAplicacao;
use Illuminate\Support\Facades\File;

/**
 * Rodapé de versão: o número, o commit e a data do último deploy, lidos do
 * .git que a Infra atualiza com `git pull` — em toda tela, com ou sem login.
 */
function gitFalso(array $arquivos): string
{
    $raiz = sys_get_temp_dir().DIRECTORY_SEPARATOR.'rodape-versao-'.uniqid();

    foreach ($arquivos as $caminho => $conteudo) {
        File::ensureDirectoryExists(dirname($raiz.'/.git/'.$caminho));
        file_put_contents($raiz.'/.git/'.$caminho, $conteudo);
    }

    return $raiz;
}

afterEach(fn () => VersaoAplicacao::esquecer());

test('lê o commit da branch e a data do último pull no reflog', function () {
    config(['versao.numero' => '2.0.0', 'app.timezone' => 'America/Sao_Paulo']);
    $hash = str_repeat('a1b2c3d4e5', 4);
    // 1791059101 = 03/10/2026 17:25 em Brasília (UTC-3).
    $raiz = gitFalso([
        'HEAD' => "ref: refs/heads/main\n",
        'refs/heads/main' => $hash."\n",
        'logs/HEAD' => str_repeat('0', 40).' '.$hash." Infra <infra@orgao> 1790000000 -0300\tclone\n"
            .str_repeat('1', 40).' '.$hash." Infra <infra@orgao> 1791059101 -0300\tpull origin main: Fast-forward\n",
    ]);

    $v = VersaoAplicacao::atual($raiz);

    expect($v['commit'])->toBe('a1b2c3d')
        ->and($v['branch'])->toBe('main')
        ->and(VersaoAplicacao::paraExibicao($raiz))->toBe('v2.0.0 · a1b2c3d · último deploy 03/10/2026 17:25');
});

test('encontra o commit em packed-refs quando o arquivo da ref não existe', function () {
    $hash = str_repeat('f', 40);
    $raiz = gitFalso([
        'HEAD' => "ref: refs/heads/main\n",
        'packed-refs' => "# pack-refs with: peeled\n{$hash} refs/heads/main\n",
    ]);

    expect(VersaoAplicacao::atual($raiz)['commit'])->toBe('fffffff');
});

test('sem .git o rodapé diz que o deploy não foi identificado, sem inventar dado', function () {
    config(['versao.numero' => '2.0.0']);
    $raiz = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sem-git-'.uniqid();

    expect(VersaoAplicacao::paraExibicao($raiz))->toBe('v2.0.0 · deploy não identificado');
});

test('o rodapé aparece sem login, na tela de login e logado', function () {
    $texto = VersaoAplicacao::paraExibicao();

    $this->get(route('login'))->assertOk()->assertSee($texto);
    $this->get('/')->assertOk()->assertSee($texto);

    $this->actingAs(User::factory()->create())
        ->get('/acesso-pendente')->assertOk()->assertSee($texto);
});
