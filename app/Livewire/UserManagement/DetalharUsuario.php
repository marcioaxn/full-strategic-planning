<?php

namespace App\Livewire\UserManagement;

use App\Models\User;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharUsuario extends Component
{
    public $user;
    public $estatisticas = [];
    public $planosResponsavel = [];
    public $entregasResponsavel = []; // Placeholder, pois Entrega não tem user direto na estrutura atual conhecida

    public function mount($id)
    {
        $this->user = User::with(['organizacoes'])->findOrFail($id);
        
        // Buscar planos onde o usuário é Gestor Responsável
        // Usando a tabela pivô rel_users_tab_organizacoes_tab_perfil_acesso
        // PerfilAcesso::GESTOR_RESPONSAVEL (assumindo ID fixo ou buscando)
        
        // Como não tenho a classe PerfilAcesso fácil aqui para pegar constantes, vou assumir IDs ou fazer query genérica
        // Mas para simplificar neste momento, vou listar as organizações e deixar planos como TODO se a query for complexa demais sem os IDs.
        
        // Tentativa de buscar Planos via relação inversa se existisse.
        // Vou usar uma query manual na tabela pivot se conseguir, mas User tem o relacionamento perfisAcesso().
        
        // $this->user->perfisAcesso() retorna os perfis. O pivot tem cod_plano_de_acao.
        // Vamos pegar os planos através disso.
        
        $planosIds = \Illuminate\Support\Facades\DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $id)
            ->whereNotNull('cod_plano_de_acao')
            ->pluck('cod_plano_de_acao');
            
        $this->planosResponsavel = PlanoDeAcao::whereIn('cod_plano_de_acao', $planosIds)->get();

        $this->estatisticas = [
            'qtd_organizacoes' => $this->user->organizacoes->count(),
            'qtd_planos' => $this->planosResponsavel->count(),
            'entregas_concluidas' => 0, // Implementar depois
        ];
    }

    public function render()
    {
        return view('livewire.user-management.detalhar-usuario');
    }
}
