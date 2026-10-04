<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Revalida, em TODA requisição Livewire (não só no mount), que o usuário logado
 * continua ativo e alcança a unidade em tela ($organizacaoId).
 *
 * 🔴 Várias telas conferiam a unidade só ao abrir. Quem perdia o vínculo — ou
 * tinha a conta desativada — com a aba aberta continuava recebendo os dados:
 * o Painel atualiza sozinho a cada 30 s (wire:poll), e o /livewire/update não
 * repassava os middlewares da rota.
 *
 * Visitante (área pública de Transparência) não passa por aqui: não há login a
 * revalidar, e o que ele vê já é público por decisão do produto.
 */
trait RevalidaUnidadeNaRequisicao
{
    public function hydrateRevalidaUnidadeNaRequisicao(): void
    {
        $usuario = Auth::user();
        if (! $usuario) {
            return;
        }

        abort_unless($usuario->isAtivo(), 403, 'Sua conta está inativa.');

        $unidade = property_exists($this, 'organizacaoId') ? $this->organizacaoId : null;
        if ($unidade && ! $usuario->podeAcessarOrganizacao($unidade)) {
            abort(403, 'Você não tem mais acesso a esta unidade. Recarregue a página.');
        }
    }
}
