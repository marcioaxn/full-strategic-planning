<?php

namespace App\Livewire\PlanoAcao;

use App\Models\PEI\PlanoDeAcao;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class DetalharPlano extends Component
{
    use AuthorizesRequests;

    public $plano;
    public $responsaveis = [];
    public $progresso = 0;
    public $auditoria = [];

    public function mount($planoId)
    {
        $this->plano = PlanoDeAcao::with(['objetivoEstrategico.perspectiva', 'tipoExecucao', 'organizacao', 'entregas', 'indicadores'])
            ->findOrFail($planoId);
        
        $this->authorize('view', $this->plano);

        $this->carregarResponsaveis();
        $this->carregarAuditoria();
        $this->progresso = $this->plano->calcularProgressoEntregas();
    }

    public function carregarResponsaveis()
    {
        $this->responsaveis = DB::table('public.rel_users_tab_organizacoes_tab_perfil_acesso as pivot')
            ->join('users', 'users.id', '=', 'pivot.user_id')
            ->join('public.tab_perfil_acesso as perfil', 'perfil.cod_perfil', '=', 'pivot.cod_perfil')
            ->where('pivot.cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->select('users.name', 'perfil.dsc_perfil')
            ->get();
    }

    public function carregarAuditoria()
    {
        // Pega as últimas 10 alterações
        $this->auditoria = $this->plano->audits()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.plano-acao.detalhar-plano');
    }
}