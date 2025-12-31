<?php

namespace App\Livewire\Relatorio;

use App\Models\Organization;
use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarRelatorios extends Component
{
    public $organizacaoId;
    public $organizacaoNome;
    public $organizacoes = [];

    // Novos Filtros
    public $anos = [];
    public $anoSelecionado;
    public $periodoSelecionado = 'anual';
    public $perspectivas = [];
    public $perspectivaSelecionada = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao'
    ];

    public function mount()
    {
        $this->organizacoes = Organization::orderBy('nom_organizacao')->get();
        
        // Carregar Anos (baseado nos ciclos PEI ativos/recentes)
        $this->anos = range(date('Y') - 1, date('Y') + 4);
        $this->anoSelecionado = date('Y');

        // Carregar Perspectivas (do PEI ativo)
        $peiAtivo = PEI::ativos()->first();
        if ($peiAtivo) {
            $this->perspectivas = Perspectiva::where('cod_pei', $peiAtivo->cod_pei)
                ->ordenadoPorNivel()
                ->get();
        }

        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
    }

    public function updatedOrganizacaoId($value)
    {
        $this->setOrganizacao($value);
    }

    public function setOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        // Sincronizar com sessão global se desejado, ou manter apenas local para o relatório
    }

    public function getQueryParamsProperty()
    {
        return [
            'ano' => $this->anoSelecionado,
            'periodo' => $this->periodoSelecionado,
            'perspectiva' => $this->perspectivaSelecionada,
            'organizacao_id' => $this->organizacaoId
        ];
    }

    public function render()
    {
        return view('livewire.relatorio.listar-relatorios');
    }
}
