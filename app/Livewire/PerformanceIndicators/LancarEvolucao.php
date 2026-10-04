<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\StrategicPlanning\Arquivo;
use App\Services\IndicadorCalculoService;
use App\Support\UnidadeMedida;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    /**
     * Booleano na tela, "Sim"/"Não" no banco.
     *
     * 🔴 O switch usava true-value="Sim" false-value="Não", que o wire:model do
     * Livewire 4 ignora: enviava true/false, a regra in:Sim,Não recusava e,
     * sem @error na tela, nada era gravado nem dito. E o mês gravado com "Não"
     * abria com o switch LIGADO (!!'Não' é verdadeiro).
     */
    public bool $bln_atualizado = true;

    // Upload de Arquivos
    public $arquivosTemporarios = [];

    public $arquivosExistentes = [];

    protected $rules = [
        // Chegam como TEXTO no formato brasileiro (20.000.000.000,00).
        // A regra 'numeric' recusaria — a conversão acontece em salvar().
        'vlr_previsto' => 'nullable|string|max:30',
        'vlr_realizado' => 'nullable|string|max:30',
        'txt_avaliacao' => 'nullable|string|max:2000',
        'bln_atualizado' => 'boolean',
        // Só documento e imagem (um .html/.svg rodaria script se aberto na
        // origem do sistema). Até 10 MB. Fica no disco privado.
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
        $this->barrarCalculoAutomatico();

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
     * Indicador de cálculo automático (pela iniciativa) não recebe lançamento
     * manual: o atingimento vem do progresso das entregas, e qualquer mudança
     * numa entrega sobrescreve o mês corrente (EntregaObserver). O valor
     * digitado não entrava no cálculo e sumia sem aviso.
     */
    private function barrarCalculoAutomatico(): void
    {
        // Mesmo critério de Indicador::atingimentoMedido(): sem iniciativa, o
        // "automático" não tem de onde tirar valor e é medido pelos lançamentos.
        abort_if(
            $this->indicador->dsc_calculation_type === 'action_plan' && $this->indicador->cod_plano_de_acao,
            403,
            'Este indicador é calculado automaticamente pelo progresso das entregas da iniciativa e não recebe lançamento manual.'
        );
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
            $this->bln_atualizado = $evolucao->bln_atualizado === 'Sim';
            $this->arquivosExistentes = $evolucao->arquivos;
        } else {
            $this->vlr_previsto = '';
            $this->vlr_realizado = '';
            $this->txt_avaliacao = '';
            $this->bln_atualizado = true;
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
        $this->barrarCalculoAutomatico();

        $this->validate();

        // Texto em formato brasileiro: aqui se confere se é número, se cabe na
        // coluna (acima disso o banco recusava e a tela caía em 500) e se a
        // unidade admite fração.
        $unidade = $this->indicador->dsc_unidade_medida;
        $erros = array_filter([
            'vlr_previsto' => UnidadeMedida::erroDeValor($this->vlr_previsto, $unidade),
            'vlr_realizado' => UnidadeMedida::erroDeValor($this->vlr_realizado, $unidade),
        ]);
        if ($erros) {
            throw ValidationException::withMessages($erros);
        }

        // Ano e mês vêm do navegador: o seletor limita ao ciclo, o servidor também.
        // Sem isto, mês 13 ou ano fora do ciclo eram gravados, e texto virava erro 500.
        $this->validate([
            'ano' => ['required', 'integer', Rule::in($this->anosDisponiveis())],
            'mes' => 'required|integer|between:1,12',
        ], [
            'ano.in' => 'O ano precisa estar dentro do ciclo do PEI.',
            'mes.between' => 'Mês inválido.',
        ]);

        $evolucao = EvolucaoIndicador::updateOrCreate(
            [
                'cod_indicador' => $this->indicador->cod_indicador,
                'num_ano' => $this->ano,
                'num_mes' => $this->mes,
            ],
            [
                // Em branco é NULL ("não medido"), nunca zero: o zero do
                // Realizado em polaridade negativa dava 100% e farol verde a um
                // mês que ninguém mediu.
                'vlr_previsto' => UnidadeMedida::paraFloat($this->vlr_previsto),
                'vlr_realizado' => UnidadeMedida::paraFloat($this->vlr_realizado),
                'txt_avaliacao' => $this->txt_avaliacao,
                'bln_atualizado' => $this->bln_atualizado ? 'Sim' : 'Não',
            ]
        );

        // Processar Uploads
        foreach ($this->arquivosTemporarios as $arquivo) {
            // Disco privado: a evidência não tem link público (antes ia para o disco
            // público e era baixável por URL direta, sem login).
            $path = $arquivo->store('pei/evidencias', 'local');

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
        $this->barrarCalculoAutomatico();

        // Só evidência de evolução DESTE indicador: o id vem do navegador.
        $arquivo = Arquivo::whereIn('cod_evolucao_indicador', EvolucaoIndicador::where('cod_indicador', $this->indicador->cod_indicador)
            ->select('cod_evolucao_indicador'))
            ->findOrFail($id);
        foreach (['local', 'public'] as $disco) {
            Storage::disk($disco)->delete($arquivo->dsc_nome_arquivo);
        }
        $arquivo->delete();
        $this->carregarPeriodo();
    }

    public function render()
    {
        return view('livewire.indicador.lancar-evolucao');
    }
}
