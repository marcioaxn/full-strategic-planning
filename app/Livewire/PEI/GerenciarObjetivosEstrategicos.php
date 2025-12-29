<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\ObjetivoEstrategico;
use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class GerenciarObjetivosEstrategicos extends Component
{
    use WithPagination;

    public $search = '';
    public $peiAtivo;
    public $organizacaoId;

    // Campos do Modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $objetivoId;
    public $nom_objetivo_estrategico;
    public $cod_organizacao;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
    ];

    public function mount()
    {
        $this->peiAtivo = PEI::ativos()->first();
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
        $this->cod_organizacao = $this->organizacaoId;
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
        $obj = ObjetivoEstrategico::findOrFail($id);
        $this->objetivoId = $id;
        $this->nom_objetivo_estrategico = $obj->nom_objetivo_estrategico;
        $this->cod_organizacao = $obj->cod_organizacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nom_objetivo_estrategico' => 'required|string|min:5|max:1000',
            'cod_organizacao' => 'required|exists:tab_organizacoes,cod_organizacao',
        ]);

        if (!$this->peiAtivo) {
            $this->dispatch('notify', message: 'Não existe um ciclo PEI ativo.', style: 'danger');
            return;
        }

        ObjetivoEstrategico::updateOrCreate(
            ['cod_objetivo_estrategico' => $this->objetivoId],
            [
                'nom_objetivo_estrategico' => $this->nom_objetivo_estrategico,
                'cod_pei' => $this->peiAtivo->cod_pei,
                'cod_organizacao' => $this->cod_organizacao,
            ]
        );

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', message: 'Objetivo Estratégico salvo com sucesso!', style: 'success');
    }

    public function confirmDelete($id)
    {
        $this->objetivoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->objetivoId) {
            ObjetivoEstrategico::findOrFail($this->objetivoId)->delete();
            $this->objetivoId = null;
            $this->showDeleteModal = false;
            $this->dispatch('notify', message: 'Objetivo Estratégico removido.', style: 'success');
        }
    }

    public function resetForm()
    {
        $this->objetivoId = null;
        $this->nom_objetivo_estrategico = '';
        $this->cod_organizacao = $this->organizacaoId;
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function render()
    {
        $query = ObjetivoEstrategico::query()
            ->with(['organizacao', 'pei'])
            ->when($this->search, function($q) {
                $q->where('nom_objetivo_estrategico', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->organizacaoId, function($q) {
                $q->where('cod_organizacao', $this->organizacaoId);
            })
            ->when($this->peiAtivo, function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            });

        return view('livewire.p-e-i.gerenciar-objetivos-estrategicos', [
            'objetivos' => $query->latest()->paginate(10),
            'organizacoes' => Organization::all()
        ]);
    }
}
