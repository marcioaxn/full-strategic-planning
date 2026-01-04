<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use App\Models\StrategicPlanning\PEI;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class MissaoVisao extends Component
{
    use AuthorizesRequests;

    public $missao = '';
    public $visao = '';
    public $organizacaoId;
    public $organizacaoNome;
    public $peiAtivo;

    // Propriedades para Valores
    public $valores = [];
    public $novoValorTitulo = '';
    public $novoValorDescricao = '';
    public $editandoValorId = null;

    public bool $isEditing = false;
    public bool $isEditingValores = false;
    public bool $aiEnabled = false;
    public bool $showDeleteModal = false;
    public $valorParaExcluirId;
    public $aiSuggestion = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        if (!$this->organizacaoNome) {
             session()->flash('error', 'Selecione uma organização primeiro.');
             return;
        }

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Sugira Missão, Visão e 5 Valores para a organização: {$this->organizacaoNome}. 
        Responda OBRIGATORIAMENTE em formato JSON puro com os campos: 
        'missao' (string), 'visao' (string) e 'valores' (array de objetos com 'nome' e 'descricao').";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarIdentidade()
    {
        if (!isset($this->aiSuggestion['missao'])) return;

        $this->missao = $this->aiSuggestion['missao'];
        $this->visao = $this->aiSuggestion['visao'];
        
        $this->salvar();
        
        // Mantém apenas os valores na sugestão para não repetir a missão/visão já aplicada
        $this->aiSuggestion['missao_aplicada'] = true;
    }

    public function adicionarValorSugerido($nome, $descricao)
    {
        $service = app(\App\Services\PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->novoValorTitulo = $nome;
        $this->novoValorDescricao = $descricao;
        $this->adicionarValor();

        $after = $service->analyzeCompleteness($this->peiAtivo->cod_pei);
        if ($after['progress'] > $before['progress']) {
            $this->dispatch('mentor-notification', 
                title: 'Princípio Registrado!',
                message: "Valor organizacional adicionado com sucesso. Progresso: <strong>{$after['progress']}%</strong>.",
                icon: 'bi-star-fill'
            );
        }

        // Remove o valor da lista de sugestões
        if (isset($this->aiSuggestion['valores'])) {
            $this->aiSuggestion['valores'] = array_filter($this->aiSuggestion['valores'], function($v) use ($nome) {
                return $v['nome'] !== $nome;
            });
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarDados();
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

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        
        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org->nom_organizacao;
            $this->carregarDados();
        } else {
            $this->resetarDados();
        }
        
        $this->isEditing = false;
        $this->isEditingValores = false;
    }

    public function resetarDados()
    {
        $this->missao = '';
        $this->visao = '';
        $this->organizacaoNome = '';
        $this->valores = [];
    }

    public function carregarDados()
    {
        // Carregar Missão/Visão
        $dados = MissaoVisaoValores::where('cod_organizacao', $this->organizacaoId)
            ->where(function($q) {
                // Tenta buscar pelo PEI ativo, ou o último registro se não houver PEI específico
                if ($this->peiAtivo) {
                    $q->where('cod_pei', $this->peiAtivo->cod_pei);
                }
            })
            ->latest() // Em caso de múltiplos (ex: sem PEI), pega o mais recente
            ->first();

        if ($dados) {
            $this->missao = $dados->dsc_missao;
            $this->visao = $dados->dsc_visao;
        } else {
            $this->missao = '';
            $this->visao = '';
        }

        // Carregar Valores
        if ($this->peiAtivo) {
            $this->valores = Valor::where('cod_organizacao', $this->organizacaoId)
                ->where('cod_pei', $this->peiAtivo->cod_pei)
                ->orderBy('created_at')
                ->get();
        } else {
            $this->valores = [];
        }
    }

    public function habilitarEdicao()
    {
        if (!$this->peiAtivo) {
            session()->flash('error', 'Não há um Ciclo PEI ativo. Não é possível editar a Identidade Estratégica.');
            return;
        }
        $this->isEditing = true;
    }

    public function cancelar()
    {
        $this->carregarDados();
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function salvar()
    {
        if (!$this->peiAtivo) {
            session()->flash('error', 'Não é possível salvar sem um Ciclo PEI ativo.');
            return;
        }

        $service = app(\App\Services\PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->validate([
            'missao' => 'nullable|string|max:5000',
            'visao' => 'nullable|string|max:5000',
        ]);

        MissaoVisaoValores::updateOrCreate(
            [
                'cod_organizacao' => $this->organizacaoId,
                'cod_pei' => $this->peiAtivo->cod_pei
            ],
            [
                'dsc_missao' => $this->missao,
                'dsc_visao' => $this->visao,
            ]
        );

        $after = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $alert = \App\Services\NotificationService::sendMentorAlert(
            'Identidade Estratégica Salva!',
            $after['message'],
            'bi-fingerprint'
        );

        $this->dispatch('mentor-notification', ...$alert);

        $this->isEditing = false;
        session()->flash('status', 'Identidade estratégica atualizada com sucesso!');
    }

    // --- Métodos para Valores ---

    public function adicionarValor()
    {
        if (!$this->peiAtivo) return;

        $service = app(\App\Services\PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->validate([
            'novoValorTitulo' => 'required|string|max:255',
            'novoValorDescricao' => 'nullable|string|max:1000',
        ]);

        Valor::create([
            'cod_organizacao' => $this->organizacaoId,
            'cod_pei' => $this->peiAtivo->cod_pei,
            'nom_valor' => $this->novoValorTitulo,
            'dsc_valor' => $this->novoValorDescricao,
        ]);

        $after = $service->analyzeCompleteness($this->peiAtivo->cod_pei);
        if ($after['progress'] > $before['progress']) {
            $alert = \App\Services\NotificationService::sendMentorAlert(
                'Valor Adicionado!',
                "Princípios fortalecidos. Progresso: <strong>{$after['progress']}%</strong>.",
                'bi-star-fill'
            );
            $this->dispatch('mentor-notification', ...$alert);
        }

        $this->novoValorTitulo = '';
        $this->novoValorDescricao = '';
        $this->carregarDados();
        session()->flash('status', 'Valor adicionado com sucesso!');
    }

    public function confirmDeleteValor($id)
    {
        $this->valorParaExcluirId = $id;
        $this->showDeleteModal = true;
    }

    public function removerValor()
    {
        Valor::find($this->valorParaExcluirId)->delete();
        $this->valorParaExcluirId = null;
        $this->showDeleteModal = false;
        $this->carregarDados();
        
        $this->dispatch('mentor-notification', 
            title: 'Valor Removido',
            message: 'O princípio foi excluído da identidade estratégica.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function editarValor($id)
    {
        $valor = Valor::find($id);
        $this->editandoValorId = $id;
        $this->novoValorTitulo = $valor->nom_valor;
        $this->novoValorDescricao = $valor->dsc_valor;
        $this->isEditingValores = true;
    }

    public function atualizarValor()
    {
        $this->validate([
            'novoValorTitulo' => 'required|string|max:255',
            'novoValorDescricao' => 'nullable|string|max:1000',
        ]);

        $valor = Valor::find($this->editandoValorId);
        $valor->update([
            'nom_valor' => $this->novoValorTitulo,
            'dsc_valor' => $this->novoValorDescricao,
        ]);

        $this->cancelarEdicaoValor();
        $this->carregarDados();
        session()->flash('status', 'Valor atualizado com sucesso!');
    }

    public function cancelarEdicaoValor()
    {
        $this->editandoValorId = null;
        $this->novoValorTitulo = '';
        $this->novoValorDescricao = '';
        $this->isEditingValores = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.p-e-i.missao-visao');
    }
}
