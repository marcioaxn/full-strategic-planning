<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Arquivo;
use App\Services\IndicadorCalculoService;
use App\Support\UnidadeMedida;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

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
        // Chegam como TEXTO no formato brasileiro (20.000.000.000,00).
        // A regra 'numeric' recusaria — a conversão acontece em salvar().
        'vlr_previsto' => 'nullable|string|max:30',
        'vlr_realizado' => 'nullable|string|max:30',
        'txt_avaliacao' => 'nullable|string|max:2000',
        'bln_atualizado' => 'required|in:Sim,Não',
        // Evidência é servida pelo disco público: só documento e imagem — um
        // .html/.svg ali rodaria script na origem do sistema. Até 10 MB.
        'arquivosTemporarios' => 'nullable|array',
        'arquivosTemporarios.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,png,jpg,jpeg,gif,txt,csv,zip',
    ];

    protected $listeners = [
        'anoSelecionado' => 'atualizarAno',
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

        $this->ano = (int) session('ano_selecionado', now()->year);
        $this->mes = now()->month;

        // O ano de referência tem de estar entre as opções do seletor: senão a
        // tela MOSTRA o primeiro ano da lista e GRAVA no ano da sessão.
        $anos = $this->anosDisponiveis();
        if (! in_array($this->ano, $anos, true)) {
            $this->ano = in_array((int) now()->year, $anos, true) ? (int) now()->year : $anos[0];
        }

        $this->carregarPeriodo();
        $this->carregarHistorico();
    }

    /**
     * Anos lançáveis: os do ciclo PEI a que o indicador pertence (pelo
     * objetivo direto ou pela iniciativa). A lista era fixa em hoje ± 2 —
     * deixava de fora o 1º ano de um ciclo 2023-2027 e não oferecia nenhum
     * ano de um ciclo futuro.
     *
     * @return list<int>
     */
    public function anosDisponiveis(): array
    {
        $pei = $this->indicador->objetivo?->perspectiva?->pei
            ?? $this->indicador->planoDeAcao?->objetivo?->perspectiva?->pei;

        return $pei
            ? range((int) $pei->num_ano_inicio_pei, (int) $pei->num_ano_fim_pei)
            : range((int) now()->year - 2, (int) now()->year + 2);
    }

    public function updatedAno()
    {
        $this->carregarPeriodo();
    }

    public function updatedMes()
    {
        $this->carregarPeriodo();
    }

    public function carregarPeriodo()
    {
        $evolucao = EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->where('num_ano', $this->ano)
            ->where('num_mes', $this->mes)
            ->first();

        if ($evolucao) {
            $unidade = $this->indicador->dsc_unidade_medida ?? null;
            $this->vlr_previsto = UnidadeMedida::formatar($evolucao->vlr_previsto, $unidade, false);
            $this->vlr_realizado = UnidadeMedida::formatar($evolucao->vlr_realizado, $unidade, false);
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
        // Método público = endpoint: a autorização do mount não vale para as chamadas seguintes.
        $this->authorize('update', $this->indicador);

        $this->validate();

        $evolucao = EvolucaoIndicador::updateOrCreate(
            [
                'cod_indicador' => $this->indicador->cod_indicador,
                'num_ano' => $this->ano,
                'num_mes' => $this->mes,
            ],
            [
                // null e zero são coisas diferentes: zero entra na média,
                // "não informado" não deveria. Mantido o ?: 0 do
                // comportamento atual para não mudar cálculo neste passo.
                'vlr_previsto' => UnidadeMedida::paraFloat($this->vlr_previsto) ?: 0,
                'vlr_realizado' => UnidadeMedida::paraFloat($this->vlr_realizado) ?: 0,
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

        app(IndicadorCalculoService::class)->verificarAlertaTendencia($this->indicador);

        session()->flash('status', 'Lançamento realizado com sucesso!');
    }

    public function excluirArquivo($id)
    {
        $this->authorize('update', $this->indicador);

        // Só evidência de evolução DESTE indicador: o id vem do navegador.
        $arquivo = Arquivo::whereIn('cod_evolucao_indicador', EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->select('cod_evolucao_indicador'))
            ->findOrFail($id);
        Storage::disk('public')->delete($arquivo->dsc_nome_arquivo);
        $arquivo->delete();
        $this->carregarPeriodo();
    }

    public function render()
    {
        return view('livewire.indicador.lancar-evolucao');
    }
}
