<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarPerspectivas extends Component
{
    public $perspectivas = [];
    public $peiAtivo;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showSuccessModal = false;
    public bool $showErrorModal = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public string $createdPerspectivaName = '';

    public $perspectivaId;
    public $dsc_perspectiva;
    public $num_nivel_hierarquico_apresentacao;
    public $num_peso_indicadores = 100;
    public $num_peso_planos = 0;
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();

        if ($this->peiAtivo) {
            $this->carregarPerspectivas();
        }
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->successMessage = '';
        $this->createdPerspectivaName = '';
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessage = '';
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $identidade = \App\Models\StrategicPlanning\MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)->first();

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Com base na Missão: '" . ($identidade->dsc_missao ?? 'Não definida') . "' e Visão: '" . ($identidade->dsc_visao ?? 'Não definida') . "', sugira as 4 perspectivas do BSC para esta organização. 
        IMPORTANTE: Utilize a lógica DOWN-TOP para a ordem (hierarquia):
        - Ordem 1: A base (ex: Aprendizado e Crescimento)
        - Ordem 2: Processos Internos
        - Ordem 3: Clientes / Usuários
        - Ordem 4: O topo/resultado (ex: Financeira ou Valor Social)
        
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'ordem' e 'descricao' (uma frase curta explicando o foco). 
        Exemplo: [{\"nome\": \"Aprendizado e Crescimento\", \"ordem\": 1, \"descricao\": \"Desenvolvimento de pessoas e sistemas.\"}, ...]";
        
        $response = $aiService->suggest($prompt);
        
        // Tenta decodificar o JSON. Se falhar, limpa para não quebrar a UI
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);
        
        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'A IA gerou um formato inválido. Por favor, tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $ordem)
    {
        $this->dsc_perspectiva = $nome;
        $this->num_nivel_hierarquico_apresentacao = $ordem;
        
        $this->save();
        
        // Remove o item da lista de sugestões após aplicar para não duplicar
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            
            if (empty($this->aiSuggestion)) {
                $this->aiSuggestion = '';
            }
        }
    }

    public function testarNotificacao()
    {
        $this->dispatch('mentor-notification', 
            title: 'Teste de Comunicação',
            message: 'Se você está lendo isso, o sistema de Toasts está <strong>funcional</strong>!',
            icon: 'bi-megaphone-fill',
            type: 'success'
        );
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
            ->with('pei')
            ->ordenadoPorNivel()
            ->get();
    }

    public function create(\App\Services\PeiGuidanceService $service)
    {
        $guidance = $service->analyzeCompleteness($this->peiAtivo->cod_pei);
        
        if ($guidance['status'] === 'warning' && $guidance['current_phase'] === 'identidade') {
             session()->flash('error', $guidance['message']);
             return redirect()->route($guidance['action_route']);
        }

        $this->resetForm();
        $maxNivel = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->max('num_nivel_hierarquico_apresentacao') ?? 0;
        $this->num_nivel_hierarquico_apresentacao = $maxNivel + 1;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $p = Perspectiva::findOrFail($id);
        $this->perspectivaId = $id;
        $this->dsc_perspectiva = $p->dsc_perspectiva;
        $this->num_nivel_hierarquico_apresentacao = $p->num_nivel_hierarquico_apresentacao;
        $this->num_peso_indicadores = $p->num_peso_indicadores ?? 100;
        $this->num_peso_planos = $p->num_peso_planos ?? 0;
        $this->showModal = true;
    }

    public function save()
    {
        $service = app(\App\Services\PeiGuidanceService::class);
        $this->validate([
            'dsc_perspectiva' => 'required|string|max:255',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
            'num_peso_indicadores' => 'required|integer|min:0|max:100',
            'num_peso_planos' => 'required|integer|min:0|max:100',
        ]);
        
        if (($this->num_peso_indicadores + $this->num_peso_planos) != 100) {
            $this->addError('num_peso_indicadores', 'A soma dos pesos deve ser exatamente 100%.');
            return;
        }

        try {
            Perspectiva::updateOrCreate(
                ['cod_perspectiva' => $this->perspectivaId],
                [
                    'dsc_perspectiva' => $this->dsc_perspectiva,
                    'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                    'cod_pei' => $this->peiAtivo->cod_pei,
                    'num_peso_indicadores' => $this->num_peso_indicadores,
                    'num_peso_planos' => $this->num_peso_planos,
                ]
            );

            if ($this->perspectivaId) {
                $this->successMessage = "A perspectiva do Balanced Scorecard foi atualizada com sucesso e já reflete as mudanças no seu mapa estratégico.";
            } else {
                $this->successMessage = "A nova perspectiva foi registrada. Agora você pode prosseguir vinculando objetivos estratégicos a esta dimensão.";
            }

            $this->createdPerspectivaName = $this->dsc_perspectiva;
            $this->showModal = false;
            $this->resetForm();
            $this->carregarPerspectivas();
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            $this->errorMessage = "Não foi possível processar a alteração na perspectiva. Por favor, revise as informações e tente novamente.";
            $this->showErrorModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->perspectivaId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        Perspectiva::findOrFail($this->perspectivaId)->delete();
        $this->showDeleteModal = false;
        $this->perspectivaId = null;
        $this->carregarPerspectivas();
        
        $this->dispatch('mentor-notification', 
            title: 'Perspectiva Removida',
            message: 'O item foi excluído com sucesso do seu planejamento estratégico.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->perspectivaId = null;
        $this->dsc_perspectiva = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->num_peso_indicadores = 100;
        $this->num_peso_planos = 0;
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-perspectivas');
    }
}
