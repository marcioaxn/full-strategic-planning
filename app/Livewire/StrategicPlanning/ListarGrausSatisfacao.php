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

    protected $paginationTheme = 'bootstrap';

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
                session()->flash('message', 'Grau de Satisfacao atualizado com sucesso!');
            }
        } else {
            GrauSatisfacao::create($data);
            session()->flash('message', 'Grau de Satisfacao criado com sucesso!');
        }

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
                $grau->delete();
                session()->flash('message', 'Grau de Satisfacao excluido com sucesso!');
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
