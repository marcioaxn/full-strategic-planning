<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Rae;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class GerenciarRae extends Component
{
    public $peiAtivo;
    public $organizacaoId;
    public $organizacaoNome;

    public bool $showModal    = false;
    public bool $showDelete   = false;
    public ?string $raeEditId = null;
    public ?string $raeDeleteId = null;

    public array $form = [
        'dte_referencia'             => '',
        'dte_reuniao'                => '',
        'dsc_tipo_reuniao'           => 'RAE',
        'txt_destaques_positivos'    => '',
        'txt_problemas_identificados'=> '',
        'txt_encaminhamentos'        => '',
        'participantes_raw'          => '',
        'num_progresso_geral'        => '',
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado'         => 'atualizarPEI',
    ];

    public function mount(): void
    {
        $this->peiAtivo       = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        $this->organizacaoId  = Session::get('organizacao_selecionada_id');
        $this->organizacaoNome = $this->organizacaoId
            ? Organization::find($this->organizacaoId)?->nom_organizacao
            : null;
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
    }

    public function atualizarOrganizacao($id): void
    {
        $this->organizacaoId   = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
    }

    public function novaRae(): void
    {
        $this->raeEditId = null;
        $this->form = [
            'dte_referencia'              => now()->format('Y-m-d'),
            'dte_reuniao'                 => '',
            'dsc_tipo_reuniao'            => 'RAE',
            'txt_destaques_positivos'     => '',
            'txt_problemas_identificados' => '',
            'txt_encaminhamentos'         => '',
            'participantes_raw'           => '',
            'num_progresso_geral'         => '',
        ];
        $this->showModal = true;
    }

    public function editarRae(string $id): void
    {
        $rae = Rae::findOrFail($id);
        $this->raeEditId = $id;
        $this->form = [
            'dte_referencia'              => $rae->dte_referencia?->format('Y-m-d') ?? '',
            'dte_reuniao'                 => $rae->dte_reuniao?->format('Y-m-d') ?? '',
            'dsc_tipo_reuniao'            => $rae->dsc_tipo_reuniao,
            'txt_destaques_positivos'     => $rae->txt_destaques_positivos ?? '',
            'txt_problemas_identificados' => $rae->txt_problemas_identificados ?? '',
            'txt_encaminhamentos'         => $rae->txt_encaminhamentos ?? '',
            'participantes_raw'           => implode(', ', $rae->json_participantes ?? []),
            'num_progresso_geral'         => $rae->num_progresso_geral ?? '',
        ];
        $this->showModal = true;
    }

    public function salvarRae(): void
    {
        $this->validate([
            'form.dte_referencia'  => 'required|date',
            'form.dte_reuniao'     => 'nullable|date',
            'form.dsc_tipo_reuniao'=> 'required|string',
            'form.num_progresso_geral' => 'nullable|numeric|min:0|max:100',
        ], [
            'form.dte_referencia.required' => 'Informe o período de referência.',
        ]);

        $participantes = array_filter(array_map('trim', explode(',', $this->form['participantes_raw'])));

        $data = [
            'cod_pei'                    => $this->peiAtivo->cod_pei,
            'cod_organizacao'            => $this->organizacaoId,
            'dte_referencia'             => $this->form['dte_referencia'],
            'dte_reuniao'                => $this->form['dte_reuniao'] ?: null,
            'dsc_tipo_reuniao'           => $this->form['dsc_tipo_reuniao'],
            'txt_destaques_positivos'    => $this->form['txt_destaques_positivos'] ?: null,
            'txt_problemas_identificados'=> $this->form['txt_problemas_identificados'] ?: null,
            'txt_encaminhamentos'        => $this->form['txt_encaminhamentos'] ?: null,
            'json_participantes'         => $participantes ?: null,
            'num_progresso_geral'        => $this->form['num_progresso_geral'] ?: null,
        ];

        $this->raeEditId
            ? Rae::findOrFail($this->raeEditId)->update($data)
            : Rae::create($data);

        $this->showModal  = false;
        $this->raeEditId  = null;
        $this->dispatch('notify', message: 'RAE salva com sucesso.', style: 'success');
    }

    public function confirmarExclusao(string $id): void
    {
        $this->raeDeleteId = $id;
        $this->showDelete  = true;
    }

    public function excluir(): void
    {
        Rae::findOrFail($this->raeDeleteId)->delete();
        $this->showDelete    = false;
        $this->raeDeleteId   = null;
        $this->dispatch('notify', message: 'RAE removida.', style: 'warning');
    }

    public function gerarPdf(string $id): mixed
    {
        $rae = Rae::with(['pei', 'organizacao'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('relatorios.rae', [
            'rae'  => $rae,
            'data' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'RAE_' . $rae->dte_referencia->format('Y_m') . '.pdf'
        );
    }

    public function render()
    {
        $raes = ($this->peiAtivo && $this->organizacaoId)
            ? Rae::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->orderByDesc('dte_referencia')
                ->get()
            : collect();

        return view('livewire.p-e-i.gerenciar-rae', [
            'raes'         => $raes,
            'tiposReuniao' => Rae::TIPOS_REUNIAO,
        ]);
    }
}
