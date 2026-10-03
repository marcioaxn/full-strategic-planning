<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Services\IndicadorCalculoService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarEntregas extends Component
{
    use AuthorizesRequests;

    public $plano;

    public $entregas = [];

    public $progresso = 0;

    public $progressoPonderado = 0;

    public $validacaoPesos = [];

    public bool $showModal = false;

    // Só o servidor define (edit); o navegador não pode apontar para entrega de outro plano.
    #[Locked]
    public $entregaId;

    // Campos do formulário
    public $dsc_entrega;

    public $bln_status = 'Não Iniciado';

    public $dsc_periodo_medicao;

    public $dte_prazo;

    public $num_nivel_hierarquico_apresentacao = 1;

    public $num_peso = 0;

    public $statusOptions = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Cancelado', 'Suspenso'];

    public function mount($planoId)
    {
        $this->plano = PlanoDeAcao::with('tipoExecucao')->findOrFail($planoId);
        $this->authorize('view', $this->plano);
        $this->carregarDados();
    }

    public function carregarDados()
    {
        $this->entregas = Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)
            ->whereNull('cod_entrega_pai')
            ->ordenadoPorNivel()
            ->get();

        $this->progresso = $this->plano->calcularProgressoEntregas();

        // Calcular progresso ponderado e validação de pesos usando o service
        $service = app(IndicadorCalculoService::class);
        $this->progressoPonderado = $service->calcularProgressoPlano($this->plano);
        $this->validacaoPesos = $service->validarPesosPlano($this->plano);
    }

    public function create()
    {
        // Entrega segue a iniciativa: capacidade na unidade dela e, para o
        // Gestor, titularidade (EntregaPolicy).
        $this->authorize('create', [Entrega::class, $this->plano]);
        $this->resetForm();

        // Sugerir o próximo nível
        $maxNivel = $this->entregas->max('num_nivel_hierarquico_apresentacao') ?? 0;
        $this->num_nivel_hierarquico_apresentacao = $maxNivel + 1;

        // Sugerir prazo final do plano
        $this->dte_prazo = $this->plano->dte_fim?->format('Y-m-d');

        $this->showModal = true;
    }

    public function edit($id)
    {
        $entrega = $this->entregaDoPlano($id);
        $this->authorize('update', $entrega);

        $this->entregaId = $id;
        $this->dsc_entrega = $entrega->dsc_entrega;
        $this->bln_status = $entrega->bln_status;
        $this->dsc_periodo_medicao = $entrega->dsc_periodo_medicao;
        $this->dte_prazo = $entrega->dte_prazo?->format('Y-m-d');
        $this->num_nivel_hierarquico_apresentacao = $entrega->num_nivel_hierarquico_apresentacao;
        $this->num_peso = $entrega->num_peso ?? 0;

        $this->showModal = true;
    }

    public function save()
    {
        if ($this->entregaId) {
            $this->authorize('update', $this->entregaDoPlano($this->entregaId));
        } else {
            $this->authorize('create', [Entrega::class, $this->plano]);
        }

        $valData = Carbon::parse($this->dte_prazo);
        $planoInicio = Carbon::parse($this->plano->dte_inicio);
        $planoFim = Carbon::parse($this->plano->dte_fim);

        $this->validate([
            'dsc_entrega' => 'required|string|max:500',
            'bln_status' => 'required|in:'.implode(',', $this->statusOptions),
            'dsc_periodo_medicao' => 'nullable|string|max:100',
            'dte_prazo' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($planoInicio, $planoFim) {
                    $dt = Carbon::parse($value);
                    if ($dt->lt($planoInicio)) {
                        $fail("O prazo não pode ser anterior ao início do plano ({$planoInicio->format('d/m/Y')}).");
                    }
                    if ($dt->gt($planoFim)) {
                        $fail("O prazo não pode exceder o fim do plano ({$planoFim->format('d/m/Y')}).");
                    }
                },
            ],
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
            'num_peso' => 'nullable|numeric|min:0|max:100',
        ]);

        // Edição: a entrega tem de ser deste plano — senão o updateOrCreate abaixo
        // a "moveria" para cá, trocando o cod_plano_de_acao.
        if ($this->entregaId) {
            $this->entregaDoPlano($this->entregaId);
        }

        Entrega::updateOrCreate(
            ['cod_entrega' => $this->entregaId],
            [
                'cod_plano_de_acao' => $this->plano->cod_plano_de_acao,
                'dsc_entrega' => $this->dsc_entrega,
                'bln_status' => $this->bln_status,
                'dsc_periodo_medicao' => $this->dsc_periodo_medicao,
                'dte_prazo' => $this->dte_prazo,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'num_peso' => $this->num_peso ?? 0,
            ]
        );

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', 'Entrega salva com sucesso!');
    }

    public function delete($id)
    {
        // Excluir é capacidade própria: o Gestor Substituto edita, mas não exclui.
        $entrega = $this->entregaDoPlano($id);
        $this->authorize('delete', $entrega);
        $entrega->delete();
        $this->carregarDados();
        session()->flash('status', 'Entrega excluída!');
    }

    /**
     * Entrega pelo id vindo do navegador, restrita ao plano da tela.
     */
    private function entregaDoPlano($id): Entrega
    {
        return Entrega::where('cod_plano_de_acao', $this->plano->cod_plano_de_acao)->findOrFail($id);
    }

    public function resetForm()
    {
        $this->entregaId = null;
        $this->dsc_entrega = '';
        $this->bln_status = 'Não Iniciado';
        $this->dsc_periodo_medicao = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->num_peso = 0;
    }

    /**
     * Redistribui pesos igualitários entre as entregas
     */
    public function redistribuirPesos()
    {
        $this->authorize('update', $this->plano);

        $service = app(IndicadorCalculoService::class);
        $count = $service->redistribuirPesosIguais($this->plano);

        $this->carregarDados();
        session()->flash('status', "Pesos redistribuídos igualmente entre {$count} entregas.");
    }

    public function render()
    {
        return view('livewire.plano-acao.gerenciar-entregas');
    }
}
