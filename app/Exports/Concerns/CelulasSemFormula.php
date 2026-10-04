<?php

namespace App\Exports\Concerns;

use App\Support\TextoSeguro;

/**
 * Toda célula exportada passa por TextoSeguro::celula(): texto do usuário que
 * começa com = + - @ sai como texto, nunca como fórmula viva no Excel.
 *
 * A classe que usa esta trait escreve a linha em linha(); o map() exigido pelo
 * WithMapping é este.
 */
trait CelulasSemFormula
{
    /** @return array<int, mixed> */
    abstract protected function linha($registro): array;

    /** @return array<int, mixed> */
    public function map($registro): array
    {
        return array_map([TextoSeguro::class, 'celula'], $this->linha($registro));
    }
}
