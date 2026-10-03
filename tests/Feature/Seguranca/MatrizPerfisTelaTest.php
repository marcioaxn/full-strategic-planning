<?php

/*
 * A tela "Gestão de Perfis de Acesso" mostrava uma matriz escrita à mão que
 * divergia da MATRIZ que de fato autoriza (CapacidadeResolver). Este teste
 * prende a tela à MATRIZ: se alguém mudar uma capacidade, a tela acompanha.
 */

use App\Livewire\Admin\GestaoPerfis;
use App\Models\PerfilAcesso;
use App\Services\Authorization\CapacidadeResolver;

test('a matriz exibida na tela de perfis é a mesma que autoriza o acesso', function () {
    $tela = (new ReflectionClass(GestaoPerfis::class))->newInstanceWithoutConstructor();
    $matrizTela = $tela->getMatrizProperty();

    $matrizReal = (new ReflectionClass(CapacidadeResolver::class))->getConstant('MATRIZ');

    $indice = array_search('Planejamento (ciclo, identidade, análises, objetivos)', $matrizTela['funcionalidades'], true);

    // Gestor Responsável só LÊ o planejamento da unidade (age nas iniciativas
    // dele); a tela tem de dizer "Leitura", não "Edição".
    expect($matrizReal['planejamento-estrategico'][PerfilAcesso::GESTOR_RESPONSAVEL])->not->toContain('criar')
        ->and($matrizReal['planejamento-estrategico'][PerfilAcesso::GESTOR_RESPONSAVEL])->not->toContain('editar')
        ->and($matrizTela['perfis']['Gestor Responsável'][$indice])->toBe('L')
        ->and($matrizTela['perfis']['Admin de Unidade'][$indice])->toBe('T');

    // Consulta aparece na tela e é leitura em tudo que alcança.
    expect($matrizTela['perfis'])->toHaveKey('Consulta');
    foreach ($matrizTela['perfis']['Consulta'] as $nivel) {
        expect($nivel)->toBeIn(['L', '—']);
    }

    $auditoria = array_search('Auditoria', $matrizTela['funcionalidades'], true);
    expect($matrizTela['perfis']['Admin de Unidade'][$auditoria])->toBe('—');
});
