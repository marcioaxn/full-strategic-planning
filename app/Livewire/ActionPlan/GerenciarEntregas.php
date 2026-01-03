<?php

namespace App\Livewire\ActionPlan;

use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Entrega;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class GerenciarEntregas extends Component
{
    use AuthorizesRequests;

    public $plano;
    public $entregas = [];
    public $progresso = 0;

    public bool $showModal = false;
    public $entregaId;

    // Campos do formulário
    public $dsc_entrega;
    public $bln_status = 'Não Iniciado';
    public $dsc_periodo_medicao;
    public $num_nivel_hierarquico_apresentacao = 1;

    public $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Cancelado', 'Suspenso'];

    public function mount($planoId)
    {
        $this->plano = PlanoDeAcao::with('tipoExecucao')->findOrFail($planoId);
        $this->authorize('view', $this->plano);
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $this->entregas = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->ordenadoPorNivel()
            ->get();
        
        $this->progresso = $this->plano->calcularProgressoEntregas();
    }

    public function create()
    {
        $this->authorize('update', $this->plano);
        $this->resetForm();
        
        // Sugerir o próximo nível
        $maxNivel = $this->entregas->max('num_nivel_hierarquico_apresentacao') ?? 0;
        $this->num_nivel_hierarquico_apresentacao = $maxNivel + 1;
        
        $this->showModal = true;
    }

    public function edit($id)
    {
        $entrega = Entrega::findOrFail($id);
        $this->authorize('update', $this->plano);

        $this->entregaId = $id;
        $this->dsc_entrega = $entrega->dsc_entrega;
        $this->bln_status = $entrega->bln_status;
        $this->dsc_periodo_medicao = $entrega->dsc_periodo_medicao;
        $this->num_nivel_hierarquico_apresentacao = $entrega->num_nivel_hierarquico_apresentacao;

        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('update', $this->plano);

        $this->validate([
            'dsc_entrega' => 'required|string|max:500',
            'bln_status' => 'required|in:' . implode(',', $this->statusOptions),
            'dsc_periodo_medicao' => 'nullable|string|max:100',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
        ]);

        Entrega::updateOrCreate(
            ['cod_entrega' => $this->entregaId],
            [
                'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
                'dsc_entrega' => $this->dsc_entrega,
                'bln_status' => $this->bln_status,
                'dsc_periodo_medicao' => $this->dsc_periodo_medicao,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
            ]
        );

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', 'Entrega salva com sucesso!');
    }

    public function delete($id)
    {
        $this->authorize('update', $this->plano);
        Entrega::findOrFail($id)->delete();
        $this->carregarDados();
        session()->flash('status', 'Entrega excluída!');
    }

    public function resetForm()
    {
        $this->entregaId = null;
        $this->dsc_entrega = '';
        $this->bln_status = 'Não Iniciado';
        $this->dsc_periodo_medicao = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
    }

    public function render()
    {
        return view('livewire.plano-acao.gerenciar-entregas');
    }
}
