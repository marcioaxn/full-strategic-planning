<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class CadeiaDeValor extends Component
{
    public $peiAtivo;

    // Formulário atividade
    public bool $showModalAtividade = false;
    public ?string $atividadeEditId  = null;

    public array $formAtividade = [
        'dsc_atividade'   => '',
        'dsc_tipo'        => 'Finalística',
        'cod_perspectiva' => '',
        'num_ordem'       => 0,
    ];

    // Formulário processo
    public bool $showModalProcesso = false;
    public ?string $processoEditId  = null;
    public ?string $processoAtivId  = null;

    public array $formProcesso = [
        'dsc_entrada'       => '',
        'dsc_transformacao' => '',
        'dsc_saida'         => '',
    ];

    // Feedback
    public bool $showDeleteModal  = false;
    public string $deleteTarget   = '';
    public string $deleteId       = '';

    protected $listeners = ['peiSelecionado' => 'atualizarPEI'];

    public function mount(): void
    {
        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
        $this->reset(['showModalAtividade', 'showModalProcesso', 'showDeleteModal']);
    }

    // ── Atividades ───────────────────────────────────────────────────────────

    public function novaAtividade(): void
    {
        $this->atividadeEditId = null;
        $this->formAtividade   = ['dsc_atividade' => '', 'dsc_tipo' => 'Finalística', 'cod_perspectiva' => '', 'num_ordem' => 0];
        $this->showModalAtividade = true;
    }

    public function editarAtividade(string $id): void
    {
        $a = AtividadeCadeiaValor::findOrFail($id);
        $this->atividadeEditId = $id;
        $this->formAtividade   = [
            'dsc_atividade'   => $a->dsc_atividade,
            'dsc_tipo'        => $a->dsc_tipo ?? 'Finalística',
            'cod_perspectiva' => $a->cod_perspectiva ?? '',
            'num_ordem'       => $a->num_ordem ?? 0,
        ];
        $this->showModalAtividade = true;
    }

    public function salvarAtividade(): void
    {
        $this->validate([
            'formAtividade.dsc_atividade' => 'required|string|max:500',
            'formAtividade.dsc_tipo'      => 'required|in:Finalística,Suporte',
        ], ['formAtividade.dsc_atividade.required' => 'Informe a descrição da atividade.']);

        if (!$this->peiAtivo) {
            $this->dispatch('notify', message: 'Nenhum ciclo PEI selecionado.', style: 'danger');
            return;
        }
        $data = array_merge($this->formAtividade, ['cod_pei' => $this->peiAtivo->cod_pei]);
        if (empty($data['cod_perspectiva'])) {
            $data['cod_perspectiva'] = null;
        }

        $this->atividadeEditId
            ? AtividadeCadeiaValor::findOrFail($this->atividadeEditId)->update($data)
            : AtividadeCadeiaValor::create($data);

        $this->showModalAtividade = false;
        $this->atividadeEditId    = null;
        $this->dispatch('notify', message: 'Atividade salva!', style: 'success');
    }

    public function confirmarExcluirAtividade(string $id): void
    {
        $this->deleteTarget = 'atividade';
        $this->deleteId     = $id;
        $this->showDeleteModal = true;
    }

    // ── Processos ────────────────────────────────────────────────────────────

    public function novoProcesso(string $atividadeId): void
    {
        $this->processoAtivId  = $atividadeId;
        $this->processoEditId  = null;
        $this->formProcesso    = ['dsc_entrada' => '', 'dsc_transformacao' => '', 'dsc_saida' => ''];
        $this->showModalProcesso = true;
    }

    public function editarProcesso(string $id): void
    {
        $p = ProcessoAtividadeCadeiaValor::findOrFail($id);
        $this->processoAtivId  = $p->cod_atividade_cadeia_valor;
        $this->processoEditId  = $id;
        $this->formProcesso    = [
            'dsc_entrada'       => $p->dsc_entrada ?? '',
            'dsc_transformacao' => $p->dsc_transformacao ?? '',
            'dsc_saida'         => $p->dsc_saida ?? '',
        ];
        $this->showModalProcesso = true;
    }

    public function salvarProcesso(): void
    {
        $this->validate([
            'formProcesso.dsc_transformacao' => 'required|string|max:500',
        ], ['formProcesso.dsc_transformacao.required' => 'Informe a transformação/processo.']);

        $data = array_merge($this->formProcesso, ['cod_atividade_cadeia_valor' => $this->processoAtivId]);

        $this->processoEditId
            ? ProcessoAtividadeCadeiaValor::findOrFail($this->processoEditId)->update($data)
            : ProcessoAtividadeCadeiaValor::create($data);

        $this->showModalProcesso = false;
        $this->dispatch('notify', message: 'Processo salvo!', style: 'success');
    }

    public function confirmarExcluirProcesso(string $id): void
    {
        $this->deleteTarget = 'processo';
        $this->deleteId     = $id;
        $this->showDeleteModal = true;
    }

    // ── Exclusão ─────────────────────────────────────────────────────────────

    public function executarExclusao(): void
    {
        match ($this->deleteTarget) {
            'atividade' => AtividadeCadeiaValor::findOrFail($this->deleteId)->delete(),
            'processo'  => ProcessoAtividadeCadeiaValor::findOrFail($this->deleteId)->delete(),
            default     => null,
        };
        $this->showDeleteModal = false;
        $this->dispatch('notify', message: 'Registro excluído.', style: 'warning');
    }

    public function gerarPdf()
    {
        abort_unless($this->peiAtivo, 404);

        $atividades = AtividadeCadeiaValor::with('processos', 'perspectiva')
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('dsc_tipo')->orderBy('num_ordem')
            ->get()
            ->groupBy('dsc_tipo');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('relatorios.cadeia-valor', [
            'pei'          => $this->peiAtivo,
            'finalisticas' => $atividades->get('Finalística', collect()),
            'suporte'      => $atividades->get('Suporte', collect()),
            'data'         => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Cadeia_de_Valor_' . now()->format('Y_m_d') . '.pdf'
        );
    }

    public function render()
    {
        $perspectivas = $this->peiAtivo
            ? Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)->orderBy('num_nivel_hierarquico_apresentacao')->get()
            : collect();

        $atividades = $this->peiAtivo
            ? AtividadeCadeiaValor::with('processos', 'perspectiva')
                ->where('cod_pei', $this->peiAtivo->cod_pei)
                ->orderBy('dsc_tipo')->orderBy('num_ordem')
                ->get()
                ->groupBy('dsc_tipo')
            : collect();

        return view('livewire.p-e-i.cadeia-de-valor', [
            'atividades'   => $atividades,
            'perspectivas' => $perspectivas,
            'tipos'        => AtividadeCadeiaValor::TIPOS,
        ]);
    }
}
