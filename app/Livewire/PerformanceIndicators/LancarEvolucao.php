<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\StrategicPlanning\Arquivo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
class LancarEvolucao extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public $indicador;
    public $evolucoes = [];
    
    // Filtros/Período atual
    public $ano;
    public $mes;

    // Form Evolução
    public $vlr_previsto;
    public $vlr_realizado;
    public $txt_avaliacao;
    public $bln_atualizado = 'Sim';
    
    // Upload de Arquivos
    public $arquivosTemporarios = [];
    public $arquivosExistentes = [];

    protected $rules = [
        'vlr_previsto' => 'nullable|numeric',
        'vlr_realizado' => 'nullable|numeric',
        'txt_avaliacao' => 'nullable|string|max:2000',
        'bln_atualizado' => 'required|in:Sim,Não',
    ];

    protected $listeners = [
        'anoSelecionado' => 'atualizarAno'
    ];

    public function atualizarAno($ano)
    {
        $this->ano = $ano;
        $this->carregarPeriodo();
        $this->carregarHistorico();
    }

    public function mount($indicadorId)
    {
        $this->indicador = Indicador::findOrFail($indicadorId);
        $this->authorize('update', $this->indicador);

        $this->ano = session('ano_selecionado', now()->year);
        $this->mes = now()->month;

        $this->carregarPeriodo();
        $this->carregarHistorico();
    }

    public function updatedAno() { $this->carregarPeriodo(); }
    public function updatedMes() { $this->carregarPeriodo(); }

    public function carregarPeriodo()
    {
        $evolucao = EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->where('num_ano', $this->ano)
            ->where('num_mes', $this->mes)
            ->first();

        if ($evolucao) {
            $this->vlr_previsto = $evolucao->vlr_previsto;
            $this->vlr_realizado = $evolucao->vlr_realizado;
            $this->txt_avaliacao = $evolucao->txt_avaliacao;
            $this->bln_atualizado = $evolucao->bln_atualizado;
            $this->arquivosExistentes = $evolucao->arquivos;
        } else {
            $this->vlr_previsto = '';
            $this->vlr_realizado = '';
            $this->txt_avaliacao = '';
            $this->bln_atualizado = 'Sim';
            $this->arquivosExistentes = [];
        }
        
        $this->arquivosTemporarios = [];
    }

    public function carregarHistorico()
    {
        $this->evolucoes = EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->orderBy('num_ano', 'desc')
            ->orderBy('num_mes', 'desc')
            ->take(12)
            ->get();
    }

    public function salvar()
    {
        $this->validate();

        $evolucao = EvolucaoIndicador::updateOrCreate(
            [
                'cod_indicador' => $this->indicador->cod_indicador,
                'num_ano' => $this->ano,
                'num_mes' => $this->mes
            ],
            [
                'vlr_previsto' => $this->vlr_previsto ?: 0,
                'vlr_realizado' => $this->vlr_realizado ?: 0,
                'txt_avaliacao' => $this->txt_avaliacao,
                'bln_atualizado' => $this->bln_atualizado,
            ]
        );

        // Processar Uploads
        foreach ($this->arquivosTemporarios as $arquivo) {
            $path = $arquivo->store('pei/evidencias', 'public');
            
            Arquivo::create([
                'cod_evolucao_indicador' => $evolucao->cod_evolucao_indicador,
                'txt_assunto' => $arquivo->getClientOriginalName(),
                'data' => now()->format('Y-m-d'),
                'dsc_nome_arquivo' => $path,
                'dsc_tipo' => $arquivo->getClientOriginalExtension(),
            ]);
        }

        $this->carregarPeriodo();
        $this->carregarHistorico();
        session()->flash('status', 'Lançamento realizado com sucesso!');
    }

    public function excluirArquivo($id)
    {
        $arquivo = Arquivo::findOrFail($id);
        Storage::disk('public')->delete($arquivo->dsc_nome_arquivo);
        $arquivo->delete();
        $this->carregarPeriodo();
    }

    public function render()
    {
        return view('livewire.indicador.lancar-evolucao');
    }
}
