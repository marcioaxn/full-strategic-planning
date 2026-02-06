<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Entrega;
use App\Services\IndicadorCalculoService;
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
    public $progressoPonderado = 0;
    public $validacaoPesos = [];

    public bool $showModal = false;
    public $entregaId;

    // Campos do formulário
    public $dsc_entrega;
    public $bln_status = 'Não Iniciado';
    public $dsc_periodo_medicao;
    public $num_nivel_hierarquico_apresentacao = 1;
    public $num_peso = 0;

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
            ->whereNull('cod_entrega_pai')
            ->ordenadoPorNivel()
            ->get();
        
        $this->progresso = $this->plano->calcularProgressoEntregas();
        
        // Calcular progresso ponderado e validação de pesos usando o service
        $service = app(IndicadorCalculoService::class);
        $this->progressoPonderado = $service->calcularProgressoPlano($this->plano);
        $this->validacaoPesos = $service->validarPesosPlano($this->plano);
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
        $this->num_peso = $entrega->num_peso ?? 0;

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
            'num_peso' => 'nullable|numeric|min:0|max:100',
        ]);

        Entrega::updateOrCreate(
            ['cod_entrega' => $this->entregaId],
            [
                'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
                'dsc_entrega' => $this->dsc_entrega,
                'bln_status' => $this->bln_status,
                'dsc_periodo_medicao' => $this->dsc_periodo_medicao,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'num_peso' => $this->num_peso ?? 0,
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
        $this->num_peso = 0;
    }

    /**
     * Redistribui pesos igualitários entre as entregas
     */
    public function redistribuirPesos()
    {
        $this->authorize('update', $this->plano);
        
        $service = app(IndicadorCalculoService::class);
        $count = $service->redistribuirPesosIguais($this->plano);
        
        $this->carregarDados();
        session()->flash('status', "Pesos redistribuídos igualmente entre {$count} entregas.");
    }

    public function render()
    {
        return view('livewire.plano-acao.gerenciar-entregas');
    }
}
