<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use App\Services\IndicadorCalculoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

// Sem #[Layout] fixo: o layout é escolhido no render(), porque esta tela
// também é servida ao visitante pelo Mapa Estratégico público.
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
            'indicadores',
        ])->findOrFail($id);
    }

    public function render()
    {
        // Calcula progresso baseado nas entregas (Regra Unificada via Service)
        $service = app(IndicadorCalculoService::class);
        $progresso = $service->calcularProgressoPlano($this->plano);

        // Gestores da iniciativa (tela "Gestores e Responsáveis") primeiro;
        // depois quem responde por alguma entrega e ainda não apareceu.
        $gestores = User::join('organization.rel_users_tab_organizacoes_tab_perfil_acesso as pivot', 'users.id', '=', 'pivot.user_id')
            ->join('organization.tab_perfil_acesso as perfil', 'perfil.cod_perfil', '=', 'pivot.cod_perfil')
            ->where('pivot.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->orderBy('perfil.dsc_perfil')
            ->get(['users.id', 'users.name', 'perfil.dsc_perfil']);

        $responsaveisEntregas = User::join('action_plan.rel_entrega_users_responsaveis as r', 'users.id', '=', 'r.cod_usuario')
            ->join('action_plan.tab_entregas as e', 'r.cod_entrega', '=', 'e.cod_entrega')
            ->where('e.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->whereNull('e.deleted_at')
            ->select('users.id', 'users.name')
            ->selectRaw("'Responsável por entrega' as dsc_perfil")
            ->distinct()
            ->get();

        $responsaveis = $gestores
            ->concat($responsaveisEntregas->whereNotIn('id', $gestores->pluck('id')))
            ->values();

        // Busca histórico de auditoria do plano
        $auditoria = $this->plano->audits()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        // Plano de comunicação e lições aprendidas (com try/catch até migrations aplicadas)
        try {
            $comunicacoes = PlanoComunicacao::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
                ->orderBy('num_ordem')->get();
            $licoes = LicaoAprendida::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
                ->orderBy('dsc_tipo')->get();
        } catch (\Exception) {
            $comunicacoes = collect();
            $licoes = collect();
        }

        // Layout dinâmico: o visitante chega aqui pelo Mapa Estratégico público
        // e não tem menu autenticado. Mesmo critério do MapaEstrategico.
        return view('livewire.plano-acao.detalhar-plano', [
            'progresso' => $progresso,
            'responsaveis' => $responsaveis,
            'auditoria' => $auditoria,
            'comunicacoes' => $comunicacoes,
            'licoes' => $licoes,
        ])
            ->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
