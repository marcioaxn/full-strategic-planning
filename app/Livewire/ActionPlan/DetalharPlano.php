<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharPlano extends Component
{
    public PlanoDeAcao $plano;

    public function mount($id)
    {
        $this->plano = PlanoDeAcao::with([
            'objetivo.perspectiva',
            'tipoExecucao',
            'organizacao',
            'entregas.responsaveis',
            'indicadores'
        ])->findOrFail($id);
    }

    public function render()
    {
        // Calcula progresso baseado nas entregas
        $progresso = $this->plano->calcularProgressoEntregas();

        // Busca responsáveis únicos de todas as entregas do plano
        $responsaveis = User::join('rel_entrega_users_responsaveis as r', 'users.id', '=', 'r.cod_usuario')
            ->join('tab_entregas as e', 'r.cod_entrega', '=', 'e.cod_entrega')
            ->where('e.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->select('users.*')
            ->selectRaw("'Responsável' as dsc_perfil")
            ->distinct()
            ->get();

        // Busca histórico de auditoria do plano
        $auditoria = $this->plano->audits()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.plano-acao.detalhar-plano', [
            'progresso' => $progresso,
            'responsaveis' => $responsaveis,
            'auditoria' => $auditoria,
        ]);
    }
}
