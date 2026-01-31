<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class GerenciarTemasNorteadores extends Component
{
    use WithPagination;

    public $search = '';
    public $peiAtivo;
    public $organizacaoId;

    // Campos do Modal
    public $showModal = false;
    public bool $showDeleteModal = false;
    public $temaId;
    public $nom_tema_norteador;
    public $cod_organizacao;
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->cod_organizacao = $this->organizacaoId;
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $org = Organization::find($this->organizacaoId);
        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Sugerir 3 Temas Norteadores (Objetivos Estratégicos de alto nível) para a organização: '{$org->nom_organizacao}'. 
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com o campo 'nome'.";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome)
    {
        $this->nom_tema_norteador = $nome;
        $this->save();
        
        // Remove da lista
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, fn($item) => $item['nome'] !== $nome);
            if (empty($this->aiSuggestion)) $this->aiSuggestion = '';
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->resetPage();
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
        $this->cod_organizacao = $id;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $obj = TemaNorteador::findOrFail($id);
        $this->temaId = $id;
        $this->nom_tema_norteador = $obj->nom_tema_norteador;
        $this->cod_organizacao = $obj->cod_organizacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nom_tema_norteador' => 'required|string|min:5|max:1000',
            'cod_organizacao' => 'required|exists:tab_organizacoes,cod_organizacao',
        ]);

        if (!$this->peiAtivo) {
            session()->flash('error', 'Não existe um ciclo PEI ativo.');
            return;
        }

        TemaNorteador::updateOrCreate(
            ['cod_tema_norteador' => $this->temaId],
            [
                'nom_tema_norteador' => $this->nom_tema_norteador,
                'cod_pei' => $this->peiAtivo->cod_pei,
                'cod_organizacao' => $this->cod_organizacao,
            ]
        );

        $alert = \App\Services\NotificationService::sendMentorAlert(
            $this->temaId ? 'Tema Norteador Atualizado!' : 'Tema Norteador Criado!',
            'O tema norteador foi registrado com sucesso.',
            'bi-shield-check'
        );
        $this->dispatch('mentor-notification', ...$alert);

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->temaId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->temaId) {
            TemaNorteador::findOrFail($this->temaId)->delete();
            $this->temaId = null;
            $this->showDeleteModal = false;
            
            $alert = \App\Services\NotificationService::sendMentorAlert(
                'Tema Norteador Removido',
                'O item foi excluído do planejamento institucional.',
                'bi-trash',
                'warning'
            );
            $this->dispatch('mentor-notification', ...$alert);
        }
    }

    public function resetForm()
    {
        $this->temaId = null;
        $this->nom_tema_norteador = '';
        $this->cod_organizacao = $this->organizacaoId;
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function render()
    {
        $query = TemaNorteador::query()
            ->with(['organizacao', 'pei'])
            ->when($this->search, function($q) {
                $q->where('nom_tema_norteador', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->organizacaoId, function($q) {
                $q->where('cod_organizacao', $this->organizacaoId);
            })
            ->when($this->peiAtivo, function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            });

        return view('livewire.p-e-i.gerenciar-temas-norteadores', [
            'temas' => $query->latest()->paginate(10),
            'organizacoes' => Organization::all()
        ]);
    }
}