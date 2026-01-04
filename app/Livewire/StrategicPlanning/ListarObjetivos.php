<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarObjetivos extends Component
{
    public $perspectivas = [];
    public $peiAtivo;

    public bool $showModal = false;
    public $objetivoId;
    public $nom_objetivo;
    public $dsc_objetivo;
    public $num_nivel_hierarquico_apresentacao;
    public $cod_perspectiva;
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;
        
        if (!$this->cod_perspectiva) {
             session()->flash('error', 'Selecione uma perspectiva primeiro.');
             return;
        }

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $perspectiva = Perspectiva::find($this->cod_perspectiva);
        $identidade = \App\Models\StrategicPlanning\MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)->first();

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Sugira 3 objetivos estratégicos para a perspectiva '{$perspectiva->dsc_perspectiva}'. 
        Missão: '" . ($identidade->dsc_missao ?? '') . "'. Visão: '" . ($identidade->dsc_visao ?? '') . "'.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'descricao' e 'ordem' (inteiro).";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $descricao, $ordem)
    {
        $this->nom_objetivo = $nome;
        $this->dsc_objetivo = $descricao;
        $this->num_nivel_hierarquico_apresentacao = $ordem;
        
        $this->save();
        
        // Remove o item da lista de sugestões
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            
            if (empty($this->aiSuggestion)) $this->aiSuggestion = '';
        }
    }

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();

        if ($this->peiAtivo) {
            $this->carregarPerspectivas();
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarPerspectivas();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function carregarPerspectivas()
    {
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) {
                $query->ordenadoPorNivel();
            }])
            ->ordenadoPorNivel()
            ->get();
    }

    public function create($perspectivaId = null, \App\Services\PeiGuidanceService $service = null)
    {
        // Resolve service if not passed (Livewire handles dependency injection in methods if requested)
        $service = $service ?? app(\App\Services\PeiGuidanceService::class);
        $guidance = $service->analyzeCompleteness($this->peiAtivo->cod_pei);
        
        // If we are in any phase BEFORE objectives, redirect and alert
        $phasesBefore = ['ciclo', 'identidade', 'perspectivas'];
        if ($guidance['status'] === 'warning' && in_array($guidance['current_phase'], $phasesBefore)) {
             session()->flash('error', $guidance['message']);
             return redirect()->route($guidance['action_route']);
        }

        $this->resetForm();
        $this->cod_perspectiva = $perspectivaId;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $obj = Objetivo::findOrFail($id);
        $this->objetivoId = $id;
        $this->nom_objetivo = $obj->nom_objetivo;
        $this->dsc_objetivo = $obj->dsc_objetivo;
        $this->num_nivel_hierarquico_apresentacao = $obj->num_nivel_hierarquico_apresentacao;
        $this->cod_perspectiva = $obj->cod_perspectiva;
        $this->showModal = true;
    }

    /**
     * Hook do Livewire que dispara quando cod_perspectiva é alterado
     * Melhora UX sugerindo a próxima ordem automaticamente
     */
    public function updatedCodPerspectiva($value)
    {
        if (!$this->objetivoId && $value) {
            $proximaOrdem = Objetivo::where('cod_perspectiva', $value)
                ->max('num_nivel_hierarquico_apresentacao');
            
            $this->num_nivel_hierarquico_apresentacao = ($proximaOrdem ?? 0) + 1;
        }
    }

    public function save()
    {
        $service = app(\App\Services\PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->validate([
            'nom_objetivo' => 'required|string|max:255',
            'dsc_objetivo' => 'nullable|string|max:1000',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
            'cod_perspectiva' => 'required|exists:pei.tab_perspectiva,cod_perspectiva',
        ]);

        Objetivo::updateOrCreate(
            ['cod_objetivo' => $this->objetivoId],
            [
                'nom_objetivo' => $this->nom_objetivo,
                'dsc_objetivo' => $this->dsc_objetivo,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'cod_perspectiva' => $this->cod_perspectiva,
            ]
        );

        $after = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->dispatch('mentor-notification', 
            title: $this->objetivoId ? 'Objetivo Atualizado!' : 'Objetivo Criado!',
            message: $after['message'],
            icon: 'bi-bullseye'
        );

        $this->showModal = false;
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo salvo com sucesso!');
    }

    public function delete($id)
    {
        Objetivo::findOrFail($id)->delete();
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->objetivoId = null;
        $this->nom_objetivo = '';
        $this->dsc_objetivo = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->cod_perspectiva = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-objetivos');
    }
}
