<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\GrauSatisfacao;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarGrausSatisfacao extends Component
{
    use WithPagination;

    // Campos do formulario
    public $cod_grau_satisfcao;
    public $dsc_grau_satisfcao = '';
    public $cor = '';
    public $vlr_minimo = '';
    public $vlr_maximo = '';

    // Controle do modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteId = null;

    // Busca
    public $search = '';
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Sugira uma escala de 4 a 5 Graus de Satisfação padrão para monitoramento estratégico. 
        A escala deve cobrir de 0 a 100%. 
        Para cada nível forneça: Descrição (ex: Crítico, Excelente), Cor (Hexadecimal vibrante), Valor Mínimo e Valor Máximo.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'cor', 'min' e 'max'.";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $cor, $min, $max)
    {
        $this->dsc_grau_satisfcao = $nome;
        $this->cor = $cor;
        $this->vlr_minimo = $min;
        $this->vlr_maximo = $max;
        
        $this->save();
        
        // Remove da lista
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            if (empty($this->aiSuggestion)) $this->aiSuggestion = '';
        }
    }

    protected function rules()
    {
        return [
            'dsc_grau_satisfcao' => 'required|string|max:100',
            'cor' => 'required|string|max:50',
            'vlr_minimo' => 'required|numeric|min:0|max:999.99',
            'vlr_maximo' => 'required|numeric|min:0|max:999.99|gte:vlr_minimo',
        ];
    }

    protected $messages = [
        'dsc_grau_satisfcao.required' => 'A descricao e obrigatoria.',
        'cor.required' => 'A cor e obrigatoria.',
        'vlr_minimo.required' => 'O valor minimo e obrigatorio.',
        'vlr_minimo.numeric' => 'O valor minimo deve ser numerico.',
        'vlr_maximo.required' => 'O valor maximo e obrigatorio.',
        'vlr_maximo.numeric' => 'O valor maximo deve ser numerico.',
        'vlr_maximo.gte' => 'O valor maximo deve ser maior ou igual ao minimo.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->cod_grau_satisfcao = null;
        $this->dsc_grau_satisfcao = '';
        $this->cor = '';
        $this->vlr_minimo = '';
        $this->vlr_maximo = '';
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'dsc_grau_satisfcao' => $this->dsc_grau_satisfcao,
            'cor' => strtolower(trim($this->cor)),
            'vlr_minimo' => $this->vlr_minimo,
            'vlr_maximo' => $this->vlr_maximo,
        ];

        if ($this->isEditing && $this->cod_grau_satisfcao) {
            $grau = GrauSatisfacao::find($this->cod_grau_satisfcao);
            if ($grau) {
                $grau->update($data);
                $title = 'Grau de Satisfação Atualizado!';
            }
        } else {
            GrauSatisfacao::create($data);
            $title = 'Grau de Satisfação Criado!';
        }

        $alert = \App\Services\NotificationService::sendMentorAlert(
            $title,
            "A faixa <strong>{$this->dsc_grau_satisfcao}</strong> ({$this->vlr_minimo}% - {$this->vlr_maximo}%) foi salva.",
            'bi-palette'
        );
        $this->dispatch('mentor-notification', ...$alert);

        $this->closeModal();
    }

    public function edit($id)
    {
        $grau = GrauSatisfacao::find($id);

        if ($grau) {
            $this->cod_grau_satisfcao = $grau->cod_grau_satisfcao;
            $this->dsc_grau_satisfcao = $grau->dsc_grau_satisfcao;
            $this->cor = $grau->cor;
            $this->vlr_minimo = $grau->vlr_minimo;
            $this->vlr_maximo = $grau->vlr_maximo;
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            $grau = GrauSatisfacao::find($this->deleteId);
            if ($grau) {
                $nome = $grau->dsc_grau_satisfcao;
                $grau->delete();
                
                $alert = \App\Services\NotificationService::sendMentorAlert(
                    'Grau Removido',
                    "A faixa <strong>{$nome}</strong> foi excluída.",
                    'bi-trash',
                    'warning'
                );
                $this->dispatch('mentor-notification', ...$alert);
            }
        }

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $graus = GrauSatisfacao::query()
            ->when($this->search, function($query) {
                $query->where('dsc_grau_satisfcao', 'ilike', '%' . $this->search . '%')
                      ->orWhere('cor', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('vlr_minimo')
            ->paginate(10);

        return view('livewire.p-e-i.listar-graus-satisfacao', [
            'graus' => $graus
        ]);
    }
}
