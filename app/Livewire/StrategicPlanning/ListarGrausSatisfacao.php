<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarGrausSatisfacao extends Component
{
    use WithPagination;

    // Campos do formulario
    public $cod_grau_satisfacao;
    public $cod_pei;
    public $num_ano;
    public $dsc_grau_satisfacao = '';
    public $cor = '';
    public $vlr_minimo = '';
    public $vlr_maximo = '';

    // Controle do modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $grauId = null; // Alterado de deleteId para grauId para consistência

    // Busca
    public $search = '';
    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    // Success Modal Properties
    public bool $showSuccessModal = false;
    public $createdGrauName = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->createdGrauName = '';
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
        $this->dsc_grau_satisfacao = $nome;
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
            'dsc_grau_satisfacao' => 'required|string|max:100',
            'cor' => 'required|string|max:50',
            'vlr_minimo' => 'required|numeric|min:0|max:999.99',
            'vlr_maximo' => 'required|numeric|min:0|max:999.99|gte:vlr_minimo',
        ];
    }

    protected $messages = [
        'dsc_grau_satisfacao.required' => 'A descricao e obrigatoria.',
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
        $this->cod_grau_satisfacao = null;
        $this->cod_pei = session('pei_selecionado_id');
        $this->num_ano = null; // Default: Geral do Ciclo
        $this->dsc_grau_satisfacao = '';
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
            'dsc_grau_satisfacao' => $this->dsc_grau_satisfacao,
            'cor' => strtolower(trim($this->cor)),
            'vlr_minimo' => $this->vlr_minimo,
            'vlr_maximo' => $this->vlr_maximo,
            'cod_pei' => $this->cod_pei ?? session('pei_selecionado_id'),
            'num_ano' => $this->num_ano,
        ];

        if ($this->isEditing && $this->cod_grau_satisfacao) {
            $grau = GrauSatisfacao::find($this->cod_grau_satisfacao);
            if ($grau) {
                $grau->update($data);
                $title = 'Grau de Satisfação Atualizado!';
            }
        } else {
            GrauSatisfacao::create($data);
            $title = 'Grau de Satisfação Criado!';
        }

        $this->createdGrauName = $this->dsc_grau_satisfacao;
        $this->closeModal();
        $this->showSuccessModal = true;
    }

    public function edit($id)
    {
        $grau = GrauSatisfacao::find($id);

        if ($grau) {
            $this->cod_grau_satisfacao = $grau->cod_grau_satisfacao;
            $this->cod_pei = $grau->cod_pei;
            $this->num_ano = $grau->num_ano;
            $this->dsc_grau_satisfacao = $grau->dsc_grau_satisfacao;
            $this->cor = $grau->cor;
            $this->vlr_minimo = $grau->vlr_minimo;
            $this->vlr_maximo = $grau->vlr_maximo;
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->grauId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->grauId) {
            $grau = GrauSatisfacao::find($this->grauId);
            if ($grau) {
                $nome = $grau->dsc_grau_satisfacao;
                $grau->delete();
                
                $alert = \App\Services\NotificationService::sendMentorAlert(
                    'Grau Removido',
                    "A faixa <strong>{$nome}</strong> foi excluída com sucesso.",
                    'bi-trash',
                    'warning'
                );
                $this->dispatch('mentor-notification', ...$alert);
            }
        }

        $this->showDeleteModal = false;
        $this->grauId = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->grauId = null;
    }

    public function render()
    {
        $peiId = session('pei_selecionado_id');

        $graus = GrauSatisfacao::query()
            ->where(function($q) use ($peiId) {
                if ($peiId) $q->where('cod_pei', $peiId)->orWhereNull('cod_pei');
            })
            ->when($this->search, function($query) {
                $query->where('dsc_grau_satisfacao', 'ilike', '%' . $this->search . '%')
                      ->orWhere('cor', 'ilike', '%' . $this->search . '%');
            })
            ->orderBy('num_ano', 'asc') // Agrupa por ano (maturidade)
            ->orderBy('vlr_minimo', 'asc')
            ->paginate(15);

        return view('livewire.p-e-i.listar-graus-satisfacao', [
            'graus' => $graus,
            'availablePeis' => PEI::orderBy('num_ano_inicio_pei', 'desc')->get()
        ]);
    }
}
