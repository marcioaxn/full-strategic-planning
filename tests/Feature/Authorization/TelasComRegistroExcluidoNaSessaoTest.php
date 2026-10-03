<?php

/*
 * Varredura de todas as telas GET sem parâmetro, para cada perfil, com a sessão
 * em dois estados: válida e guardando unidade e ciclo já excluídos — o estado
 * em que ficou a sessão do gestor depois da limpeza dos dados de teste em
 * 03/10/2026, que derrubou /pei com 500. Nenhuma tela pode responder 500.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use Illuminate\Support\Facades\Route;

function rotasGetSemParametro(): array
{
    $rotas = [];
    foreach (Route::getRoutes() as $rota) {
        $uri = $rota->uri();
        if (! in_array('GET', $rota->methods(), true) || str_contains($uri, '{')
            || preg_match('#^(_|livewire|sanctum|api/|up$|storage|logout|impersonate|two-factor|user/confirm|email/verify|refresh-csrf)#', $uri)) {
            continue;
        }
        $rotas[] = '/'.ltrim($uri, '/');
    }

    return $rotas;
}

test('nenhuma tela quebra, em nenhum perfil, com sessão válida ou com registros excluídos', function (string $perfil, bool $sessaoComExcluidos) {
    $ano = (int) date('Y');
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $unidade = Organization::create(['nom_organizacao' => 'Unidade', 'sgl_organizacao' => 'UNI', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $vigente = PEI::create(['dsc_pei' => 'Vigente', 'num_ano_inicio_pei' => $ano - 1, 'num_ano_fim_pei' => $ano + 2]);

    $usuario = User::factory()->create();
    $usuario->perfisAcesso()->attach($perfil, ['cod_organizacao' => $perfil === PerfilAcesso::SUPER_ADMIN ? $raiz->cod_organizacao : $unidade->cod_organizacao]);

    $sessao = [
        'organizacao_selecionada_id' => $unidade->cod_organizacao,
        'pei_selecionado_id' => $vigente->cod_pei,
        'ano_selecionado' => $ano,
    ];

    if ($sessaoComExcluidos) {
        $excluida = Organization::create(['nom_organizacao' => 'Excluída', 'sgl_organizacao' => 'EXC', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
        $ciclo = PEI::create(['dsc_pei' => 'Ciclo excluído', 'num_ano_inicio_pei' => $ano, 'num_ano_fim_pei' => $ano + 3]);
        $excluida->delete();
        $ciclo->delete();
        $sessao = [
            'organizacao_selecionada_id' => $excluida->cod_organizacao,
            'organizacao_selecionada_nom' => 'Excluída',
            'organizacao_selecionada_sgl' => 'EXC',
            'pei_selecionado_id' => $ciclo->cod_pei,
            'pei_selecionado_dsc' => 'Ciclo excluído',
            'pei_selecionado_periodo' => $ano.'-'.($ano + 3),
            'ano_selecionado' => $ano,
        ];
    }

    $quebradas = [];
    foreach (rotasGetSemParametro() as $uri) {
        $resposta = $this->actingAs($usuario)->withSession($sessao)->get($uri);
        if ($resposta->getStatusCode() >= 500) {
            $quebradas[] = $resposta->getStatusCode().' '.$uri.' — '.mb_substr((string) $resposta->exception?->getMessage(), 0, 160);
        }
    }

    expect($quebradas)->toBe([]);
})->with([
    'Super Admin' => PerfilAcesso::SUPER_ADMIN,
    'Administrador da Unidade' => PerfilAcesso::ADMIN_UNIDADE,
    'Gestor Responsável' => PerfilAcesso::GESTOR_RESPONSAVEL,
    'Gestor Substituto' => PerfilAcesso::GESTOR_SUBSTITUTO,
    'Consulta' => PerfilAcesso::CONSULTA,
])->with([
    'sessão válida' => false,
    'sessão com unidade e ciclo excluídos' => true,
]);
