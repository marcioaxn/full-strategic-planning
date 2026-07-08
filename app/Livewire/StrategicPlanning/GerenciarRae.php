<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\RaeCausaRaiz;
use App\Models\StrategicPlanning\RaeEncaminhamento;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarRae extends Component
{
    public $peiAtivo;

    public $organizacaoId;

    public $organizacaoNome;

    // Modal RAE
    public bool $showModal = false;

    public bool $showDelete = false;

    public ?string $raeEditId = null;

    public ?string $raeDeleteId = null;

    public array $form = [
        'dte_referencia' => '',
        'dte_reuniao' => '',
        'dsc_tipo_reuniao' => 'RAE',
        'txt_destaques_positivos' => '',
        'txt_problemas_identificados' => '',
        'txt_encaminhamentos' => '',
        'participantes_raw' => '',
        'num_progresso_geral' => '',
    ];

    // Modal Encaminhamento
    public bool $showEncModal = false;

    public bool $showEncDelete = false;

    public ?string $encEditId = null;

    public ?string $encDeleteId = null;

    public ?string $encRaeId = null;

    public array $encForm = [
        'dsc_tipo' => 'Outro',
        'txt_descricao' => '',
        'cod_responsavel' => '',
        'dte_prazo' => '',
        'dsc_status' => 'Pendente',
    ];

    // Controla quais RAEs têm painel de encaminhamentos expandido
    public array $encExpanded = [];

    // Causa Raiz (5 Porquês / Ishikawa)
    public bool $showCausaModal = false;

    public ?string $causaEditId = null;

    public ?string $causaRaeId = null;

    public array $causaForm = [
        'dsc_problema' => '',
        'json_cinco_porques' => ['', '', '', '', ''],
        'dsc_causa_raiz' => '',
        'dsc_categoria_ishikawa' => '',
        'cod_encaminhamento_vinculado' => '',
    ];

    public array $causaExpanded = [];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount(): void
    {
        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
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
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
    }

    /**
     * Garante que a organização informada está dentro do escopo real do
     * usuário autenticado e que ele tem a capacidade RBAC exigida no módulo
     * "planejamento-estrategico". Nunca confia apenas em organizacaoId vindo
     * da sessão/estado do componente — sempre revalida contra o usuário.
     */
    private function garantirAcesso(?string $codOrganizacao, string $ability): void
    {
        $user = auth()->user();

        abort_unless(
            $codOrganizacao && $user?->podeAcessarOrganizacao($codOrganizacao)
                && Gate::forUser($user)->allows("modulo.{$ability}", 'planejamento-estrategico'),
            403,
            'Você não tem permissão para operar nesta organização.'
        );
    }

    // ── RAE ──────────────────────────────────────────────────────────────────

    public function novaRae(): void
    {
        $this->garantirAcesso($this->organizacaoId, 'criar');

        $this->raeEditId = null;
        $this->form = [
            'dte_referencia' => now()->format('Y-m-d'),
            'dte_reuniao' => '',
            'dsc_tipo_reuniao' => 'RAE',
            'txt_destaques_positivos' => '',
            'txt_problemas_identificados' => '',
            'txt_encaminhamentos' => '',
            'participantes_raw' => '',
            'num_progresso_geral' => '',
        ];
        $this->showModal = true;
    }

    public function editarRae(string $id): void
    {
        $rae = Rae::findOrFail($id);
        $this->garantirAcesso($rae->cod_organizacao, 'editar');

        $this->raeEditId = $id;
        $this->form = [
            'dte_referencia' => $rae->dte_referencia?->format('Y-m-d') ?? '',
            'dte_reuniao' => $rae->dte_reuniao?->format('Y-m-d') ?? '',
            'dsc_tipo_reuniao' => $rae->dsc_tipo_reuniao,
            'txt_destaques_positivos' => $rae->txt_destaques_positivos ?? '',
            'txt_problemas_identificados' => $rae->txt_problemas_identificados ?? '',
            'txt_encaminhamentos' => $rae->txt_encaminhamentos ?? '',
            'participantes_raw' => implode(', ', $rae->json_participantes ?? []),
            'num_progresso_geral' => $rae->num_progresso_geral ?? '',
        ];
        $this->showModal = true;
    }

    public function salvarRae(): void
    {
        $this->garantirAcesso($this->organizacaoId, $this->raeEditId ? 'editar' : 'criar');

        if ($this->raeEditId) {
            $raeExistente = Rae::findOrFail($this->raeEditId);
            $this->garantirAcesso($raeExistente->cod_organizacao, 'editar');
        }

        $this->validate([
            'form.dte_referencia' => 'required|date',
            'form.dte_reuniao' => 'nullable|date',
            'form.dsc_tipo_reuniao' => 'required|string',
            'form.num_progresso_geral' => 'nullable|numeric|min:0|max:100',
        ], [
            'form.dte_referencia.required' => 'Informe o período de referência.',
        ]);

        $participantes = array_filter(array_map('trim', explode(',', $this->form['participantes_raw'])));

        $data = [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dte_referencia' => $this->form['dte_referencia'],
            'dte_reuniao' => $this->form['dte_reuniao'] ?: null,
            'dsc_tipo_reuniao' => $this->form['dsc_tipo_reuniao'],
            'txt_destaques_positivos' => $this->form['txt_destaques_positivos'] ?: null,
            'txt_problemas_identificados' => $this->form['txt_problemas_identificados'] ?: null,
            'txt_encaminhamentos' => $this->form['txt_encaminhamentos'] ?: null,
            'json_participantes' => $participantes ?: null,
            'num_progresso_geral' => $this->form['num_progresso_geral'] ?: null,
        ];

        $this->raeEditId
            ? Rae::findOrFail($this->raeEditId)->update($data)
            : Rae::create($data);

        $this->showModal = false;
        $this->raeEditId = null;
        $this->dispatch('notify', message: 'RAE salva com sucesso.', style: 'success');
    }

    public function confirmarExclusao(string $id): void
    {
        $this->raeDeleteId = $id;
        $this->showDelete = true;
    }

    public function excluir(): void
    {
        $rae = Rae::findOrFail($this->raeDeleteId);
        $this->garantirAcesso($rae->cod_organizacao, 'excluir');

        $rae->delete();
        $this->showDelete = false;
        $this->raeDeleteId = null;
        $this->dispatch('notify', message: 'RAE removida.', style: 'warning');
    }

    public function gerarPdf(string $id): mixed
    {
        $rae = Rae::with(['pei', 'organizacao'])->findOrFail($id);
        $this->garantirAcesso($rae->cod_organizacao, 'acessar');

        $pdf = Pdf::loadView('relatorios.rae', [
            'rae' => $rae,
            'data' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'RAE_'.$rae->dte_referencia->format('Y_m').'.pdf'
        );
    }

    /**
     * Mesma validação de garantirAcesso(), mas a partir do cod_rae — usada
     * pelos Encaminhamentos e Causas Raiz, que pertencem a um RAE e portanto
     * herdam a organização dele.
     */
    private function garantirAcessoPorRae(string $codRae, string $ability): void
    {
        $rae = Rae::findOrFail($codRae);
        $this->garantirAcesso($rae->cod_organizacao, $ability);
    }

    // ── ENCAMINHAMENTOS ───────────────────────────────────────────────────────

    public function toggleEncaminhamentos(string $raeId): void
    {
        if (in_array($raeId, $this->encExpanded)) {
            $this->encExpanded = array_values(array_filter($this->encExpanded, fn ($id) => $id !== $raeId));
        } else {
            $this->encExpanded[] = $raeId;
        }
    }

    public function novoEncaminhamento(string $raeId): void
    {
        $this->garantirAcessoPorRae($raeId, 'criar');

        $this->encEditId = null;
        $this->encRaeId = $raeId;
        $this->encForm = [
            'dsc_tipo' => 'Outro',
            'txt_descricao' => '',
            'cod_responsavel' => '',
            'dte_prazo' => '',
            'dsc_status' => 'Pendente',
        ];

        if (! in_array($raeId, $this->encExpanded)) {
            $this->encExpanded[] = $raeId;
        }

        $this->showEncModal = true;
    }

    public function editarEncaminhamento(string $id): void
    {
        $enc = RaeEncaminhamento::findOrFail($id);
        $this->garantirAcessoPorRae($enc->cod_rae, 'editar');

        $this->encEditId = $id;
        $this->encRaeId = $enc->cod_rae;
        $this->encForm = [
            'dsc_tipo' => $enc->dsc_tipo,
            'txt_descricao' => $enc->txt_descricao,
            'cod_responsavel' => $enc->cod_responsavel ?? '',
            'dte_prazo' => $enc->dte_prazo?->format('Y-m-d') ?? '',
            'dsc_status' => $enc->dsc_status,
        ];
        $this->showEncModal = true;
    }

    public function salvarEncaminhamento(): void
    {
        $this->garantirAcessoPorRae($this->encRaeId, $this->encEditId ? 'editar' : 'criar');

        $this->validate([
            'encForm.dsc_tipo' => 'required|in:'.implode(',', RaeEncaminhamento::TIPOS),
            'encForm.txt_descricao' => 'required|string|max:2000',
            'encForm.cod_responsavel' => 'nullable|exists:pei.users,id',
            'encForm.dte_prazo' => 'nullable|date',
            'encForm.dsc_status' => 'required|in:'.implode(',', RaeEncaminhamento::STATUS),
        ], [
            'encForm.dsc_tipo.required' => 'Selecione o tipo de encaminhamento.',
            'encForm.txt_descricao.required' => 'Descreva o encaminhamento.',
        ]);

        $data = [
            'cod_rae' => $this->encRaeId,
            'dsc_tipo' => $this->encForm['dsc_tipo'],
            'txt_descricao' => $this->encForm['txt_descricao'],
            'cod_responsavel' => $this->encForm['cod_responsavel'] ?: null,
            'dte_prazo' => $this->encForm['dte_prazo'] ?: null,
            'dsc_status' => $this->encForm['dsc_status'],
        ];

        $this->encEditId
            ? RaeEncaminhamento::findOrFail($this->encEditId)->update($data)
            : RaeEncaminhamento::create($data);

        $this->showEncModal = false;
        $this->encEditId = null;
        $this->dispatch('notify', message: 'Encaminhamento salvo.', style: 'success');
    }

    public function atualizarStatusEnc(string $id, string $status): void
    {
        $enc = RaeEncaminhamento::findOrFail($id);
        $this->garantirAcessoPorRae($enc->cod_rae, 'editar');

        $enc->update(['dsc_status' => $status]);
        $this->dispatch('notify', message: 'Status atualizado.', style: 'success');
    }

    public function confirmarExclusaoEnc(string $id): void
    {
        $this->encDeleteId = $id;
        $this->showEncDelete = true;
    }

    public function excluirEncaminhamento(): void
    {
        $enc = RaeEncaminhamento::findOrFail($this->encDeleteId);
        $this->garantirAcessoPorRae($enc->cod_rae, 'excluir');

        $enc->delete();
        $this->showEncDelete = false;
        $this->encDeleteId = null;
        $this->dispatch('notify', message: 'Encaminhamento removido.', style: 'warning');
    }

    // ── CAUSA RAIZ (5 Porquês / Ishikawa) ────────────────────────────────────

    public function toggleCausas(string $raeId): void
    {
        if (in_array($raeId, $this->causaExpanded)) {
            $this->causaExpanded = array_values(array_filter($this->causaExpanded, fn ($id) => $id !== $raeId));
        } else {
            $this->causaExpanded[] = $raeId;
        }
    }

    public function novaCausa(string $raeId): void
    {
        $this->garantirAcessoPorRae($raeId, 'criar');

        $this->causaEditId = null;
        $this->causaRaeId = $raeId;
        $this->causaForm = [
            'dsc_problema' => '',
            'json_cinco_porques' => ['', '', '', '', ''],
            'dsc_causa_raiz' => '',
            'dsc_categoria_ishikawa' => '',
            'cod_encaminhamento_vinculado' => '',
        ];
        if (! in_array($raeId, $this->causaExpanded)) {
            $this->causaExpanded[] = $raeId;
        }
        $this->showCausaModal = true;
    }

    public function editarCausa(string $id): void
    {
        $causa = RaeCausaRaiz::findOrFail($id);
        $this->garantirAcessoPorRae($causa->cod_rae, 'editar');

        $this->causaEditId = $id;
        $this->causaRaeId = $causa->cod_rae;
        $porques = $causa->json_cinco_porques ?? [];
        while (count($porques) < 5) {
            $porques[] = '';
        }
        $this->causaForm = [
            'dsc_problema' => $causa->dsc_problema,
            'json_cinco_porques' => array_slice($porques, 0, 5),
            'dsc_causa_raiz' => $causa->dsc_causa_raiz ?? '',
            'dsc_categoria_ishikawa' => $causa->dsc_categoria_ishikawa ?? '',
            'cod_encaminhamento_vinculado' => $causa->cod_encaminhamento_vinculado ?? '',
        ];
        $this->showCausaModal = true;
    }

    public function salvarCausa(): void
    {
        $this->garantirAcessoPorRae($this->causaRaeId, $this->causaEditId ? 'editar' : 'criar');

        $this->validate([
            'causaForm.dsc_problema' => 'required|string|max:1000',
            'causaForm.dsc_causa_raiz' => 'nullable|string|max:1000',
            'causaForm.dsc_categoria_ishikawa' => 'nullable|in:'.implode(',', RaeCausaRaiz::CATEGORIAS_ISHIKAWA),
        ], ['causaForm.dsc_problema.required' => 'Descreva o problema observado.']);

        $porques = array_values(array_filter($this->causaForm['json_cinco_porques'], fn ($p) => trim($p) !== ''));

        $data = [
            'cod_rae' => $this->causaRaeId,
            'dsc_problema' => $this->causaForm['dsc_problema'],
            'json_cinco_porques' => $porques,
            'dsc_causa_raiz' => $this->causaForm['dsc_causa_raiz'] ?: null,
            'dsc_categoria_ishikawa' => $this->causaForm['dsc_categoria_ishikawa'] ?: null,
            'cod_encaminhamento_vinculado' => $this->causaForm['cod_encaminhamento_vinculado'] ?: null,
        ];

        $this->causaEditId
            ? RaeCausaRaiz::findOrFail($this->causaEditId)->update($data)
            : RaeCausaRaiz::create($data);

        $this->showCausaModal = false;
        $this->causaEditId = null;
        $this->dispatch('notify', message: 'Análise de causa raiz salva.', style: 'success');
    }

    public function excluirCausa(string $id): void
    {
        $causa = RaeCausaRaiz::findOrFail($id);
        $this->garantirAcessoPorRae($causa->cod_rae, 'excluir');

        $causa->delete();
        $this->dispatch('notify', message: 'Análise removida.', style: 'warning');
    }

    // ── RENDER ────────────────────────────────────────────────────────────────

    public function render()
    {
        $raes = ($this->peiAtivo && $this->organizacaoId)
            ? Rae::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->with(['encaminhamentos.responsavel', 'causasRaiz.encaminhamento'])
                ->orderByDesc('dte_referencia')
                ->get()
            : collect();

        $usuarios = User::where('ativo', true)->orderBy('name')->get(['id', 'name']);

        return view('livewire.p-e-i.gerenciar-rae', [
            'raes' => $raes,
            'tiposReuniao' => Rae::TIPOS_REUNIAO,
            'tiposEnc' => RaeEncaminhamento::TIPOS,
            'statusEnc' => RaeEncaminhamento::STATUS,
            'usuarios' => $usuarios,
            'categoriasIshikawa' => RaeCausaRaiz::CATEGORIAS_ISHIKAWA,
        ]);
    }
}
