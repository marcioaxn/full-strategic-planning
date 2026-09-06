<?php

namespace App\Policies;

use App\Models\Reports\RelatorioGerado;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Autorização dos relatórios já gerados.
 *
 * Existe porque a listagem filtrar por usuário NÃO protege o download: todo
 * método público de um componente Livewire é invocável direto pelo navegador,
 * sem passar pela listagem. A verificação precisa estar no ato do download.
 */
class RelatorioGeradoPolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('modulo.acessar', 'relatorios');
    }

    /**
     * Só o autor baixa o próprio relatório — ou o Super Admin.
     *
     * O arquivo é um retrato consolidado do PEI no momento em que foi gerado,
     * com os filtros de quem o pediu. Não há como reavaliar o escopo
     * organizacional do conteúdo depois de renderizado, então a regra é a mais
     * restritiva que ainda atende ao uso real: quem gerou, baixa.
     */
    public function download(User $user, RelatorioGerado $relatorio): bool
    {
        if (! Gate::forUser($user)->allows('modulo.exportar', 'relatorios')) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $relatorio->user_id !== null
            && $relatorio->user_id === $user->getKey();
    }

    public function delete(User $user, RelatorioGerado $relatorio): bool
    {
        return $this->download($user, $relatorio);
    }
}
