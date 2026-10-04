<?php

namespace App\Livewire\StrategicPlanning;

use App\Concerns\RevalidaUnidadeNaRequisicao;
use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\RaeCausaRaiz;
use App\Models\StrategicPlanning\RaeEncaminhamento;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Reports\AcabamentoPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarRae extends Component
{
    use RevalidaUnidadeNaRequisicao;

    public $peiAtivo;

    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    // Modal RAE
    public bool $showModal = false;

    public bool $showDelete = false;

    #[Locked]
    public ?string $raeEditId = null;

    #[Locked]
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

    // IDs definidos só pelo servidor (novo/editar): o navegador não pode trocá-los
    // para operar em registro de outro RAE.
    #[Locked]
    public ?string $encEditId = null;

    #[Locked]
    public ?string $encDeleteId = null;

    #[Locked]
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

    #[Locked]
    public ?string $causaEditId = null;

    #[Locked]
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
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        // Nunca a sessão crua: a seleção só vale se estiver no escopo do usuário.
        $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();
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
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        abort_unless(! $id || ! Auth::check() || Auth::user()->podeAcessarOrganizacao($id), 403);

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
                // A capacidade vale NA unidade da RAE, não na soma dos vínculos.
                && Gate::forUser($user)->allows("modulo.{$ability}", ['planejamento-estrategico', $codOrganizacao]),
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
            // Salvar grava a unidade em tela: editar a RAE de outra unidade a mudaria de dono.
            abort_unless($raeExistente->cod_organizacao === $this->organizacaoId, 403);
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
            // "0" é falso em PHP: com ?: null, quem informava 0% ficava sem barra.
            'num_progresso_geral' => in_array($this->form['num_progresso_geral'], ['', null], true)
                ? null
                : (float) $this->form['num_progresso_geral'],
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
        $this->garantirAcesso(Rae::findOrFail($id)->cod_organizacao, 'excluir');
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
        // O PDF leva o conteúdo integral da RAE (problemas, encaminhamentos,
        // participantes): é exportação de dado restrito, não leitura pública.
        // O id vem do navegador — sem esta checagem, qualquer RAE de qualquer
        // organização seria baixada.
        //
        // A permissão é a de EXPORTAR RELATÓRIOS — a mesma de todo PDF do
        // sistema. Exigia "exportar" em Planejamento, que o Gestor Substituto
        // não tem: o botão aparecia para ele e devolvia 403.
        $rae = Rae::with(['pei', 'organizacao'])->findOrFail($id);
        $user = auth()->user();
        abort_unless(
            $rae->cod_organizacao && $user?->podeAcessarOrganizacao($rae->cod_organizacao)
                && Gate::forUser($user)->allows('modulo.exportar', 'relatorios'),
            403,
            'Você não tem permissão para exportar esta RAE.'
        );

        $pdf = Pdf::loadView('relatorios.rae', [
            'rae' => $rae,
            'data' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'portrait');

        (new AcabamentoPdf('portrait'))->aplicar($pdf, [
            'esquerda' => $rae->organizacao?->nom_organizacao ?? 'Todas as unidades',
            'centro' => 'Revisão e Avaliação da Estratégia',
            'site' => (string) SystemSetting::getValue('orgao_site', ''),
            'emitido_em' => now()->format('d/m/Y'),
        ]);

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
            // Sem prefixo de schema: o ponto em "exists:x.y" é lido pelo Laravel como
            // nome de CONEXÃO. Ver documentacao/melhorias/11-*.md
            'encForm.cod_responsavel' => 'nullable|exists:users,id',
            'encForm.dte_prazo' => 'nullable|date',
            'encForm.dsc_status' => 'required|in:'.implode(',', RaeEncaminhamento::STATUS),
        ], [
            'encForm.dsc_tipo.required' => 'Selecione o tipo de encaminhamento.',
            'encForm.txt_descricao.required' => 'Descreva o encaminhamento.',
        ]);

        // Responsável vem do navegador: só pessoas da unidade da RAE (a mesma lista
        // que a tela oferece). exists:users aceitava qualquer conta do sistema.
        $codResponsavel = $this->encForm['cod_responsavel'] ?: null;
        $orgDaRae = Rae::whereKey($this->encRaeId)->value('cod_organizacao');
        if ($codResponsavel && ! User::whereKey($codResponsavel)
            ->whereHas('organizacoes', fn ($q) => $q->where('tab_organizacoes.cod_organizacao', $orgDaRae))->exists()) {
            $this->addError('encForm.cod_responsavel', 'Escolha um responsável da unidade desta RAE.');

            return;
        }

        $data = [
            'cod_rae' => $this->encRaeId,
            'dsc_tipo' => $this->encForm['dsc_tipo'],
            'txt_descricao' => $this->encForm['txt_descricao'],
            'cod_responsavel' => $this->encForm['cod_responsavel'] ?: null,
            'dte_prazo' => $this->encForm['dte_prazo'] ?: null,
            'dsc_status' => $this->encForm['dsc_status'],
        ];

        // Na edição, o encaminhamento tem de ser do RAE já autorizado acima.
        $this->encEditId
            ? RaeEncaminhamento::where('cod_rae', $this->encRaeId)->findOrFail($this->encEditId)->update($data)
            : RaeEncaminhamento::create($data);

        $this->showEncModal = false;
        $this->encEditId = null;
        $this->dispatch('notify', message: 'Encaminhamento salvo.', style: 'success');
    }

    public function atualizarStatusEnc(string $id, string $status): void
    {
        $enc = RaeEncaminhamento::findOrFail($id);
        $this->garantirAcessoPorRae($enc->cod_rae, 'editar');
        // Vocabulário fechado: o status vem do navegador.
        abort_unless(in_array($status, RaeEncaminhamento::STATUS, true), 422);

        $enc->update(['dsc_status' => $status]);
        $this->dispatch('notify', message: 'Status atualizado.', style: 'success');
    }

    public function confirmarExclusaoEnc(string $id): void
    {
        // Método público: autoriza já na abertura do modal, como confirmarExclusao().
        $this->garantirAcessoPorRae(RaeEncaminhamento::findOrFail($id)->cod_rae, 'excluir');
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
            // Encaminhamento excluído (lógico) é vínculo ausente: o select já
            // mostrava "Nenhum", mas o estado guardava o id e o salvar dava 403.
            'cod_encaminhamento_vinculado' => $causa->cod_encaminhamento_vinculado
                && RaeEncaminhamento::whereKey($causa->cod_encaminhamento_vinculado)->exists()
                ? $causa->cod_encaminhamento_vinculado
                : '',
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

        // O encaminhamento vinculado vem do formulário: só os do mesmo RAE.
        // Excluído (lógico) do mesmo RAE = vínculo ausente, não tentativa indevida.
        $encVinculado = $this->causaForm['cod_encaminhamento_vinculado'] ?: null;
        if ($encVinculado && ! RaeEncaminhamento::where('cod_rae', $this->causaRaeId)->whereKey($encVinculado)->exists()) {
            abort_unless(RaeEncaminhamento::onlyTrashed()->where('cod_rae', $this->causaRaeId)->whereKey($encVinculado)->exists(), 403);
            $encVinculado = null;
        }

        $porques = array_values(array_filter($this->causaForm['json_cinco_porques'], fn ($p) => trim($p) !== ''));

        $data = [
            'cod_rae' => $this->causaRaeId,
            'dsc_problema' => $this->causaForm['dsc_problema'],
            'json_cinco_porques' => $porques,
            'dsc_causa_raiz' => $this->causaForm['dsc_causa_raiz'] ?: null,
            'dsc_categoria_ishikawa' => $this->causaForm['dsc_categoria_ishikawa'] ?: null,
            'cod_encaminhamento_vinculado' => $encVinculado,
        ];

        // Na edição, a análise tem de ser do RAE já autorizado acima.
        $this->causaEditId
            ? RaeCausaRaiz::where('cod_rae', $this->causaRaeId)->findOrFail($this->causaEditId)->update($data)
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

        // Responsável por encaminhamento: pessoas da unidade em tela. Antes a
        // lista trazia o nome de todos os usuários ativos do sistema.
        $usuarios = $this->organizacaoId
            ? User::where('ativo', true)
                ->whereHas('organizacoes', fn ($q) => $q->where('tab_organizacoes.cod_organizacao', $this->organizacaoId))
                ->orderBy('name')->get(['id', 'name'])
            : collect();

        $pode = fn (string $a) => $this->organizacaoId !== null
            && Gate::allows("modulo.{$a}", ['planejamento-estrategico', $this->organizacaoId]);

        return view('livewire.p-e-i.gerenciar-rae', [
            'raes' => $raes,
            'tiposReuniao' => Rae::TIPOS_REUNIAO,
            'tiposEnc' => RaeEncaminhamento::TIPOS,
            'statusEnc' => RaeEncaminhamento::STATUS,
            'usuarios' => $usuarios,
            'categoriasIshikawa' => RaeCausaRaiz::CATEGORIAS_ISHIKAWA,
            // A tela só oferece o botão que o servidor aceita.
            'podeCriar' => $pode('criar'),
            'podeEditar' => $pode('editar'),
            'podeExcluir' => $pode('excluir'),
            'podeExportar' => Gate::allows('modulo.exportar', 'relatorios'),
        ]);
    }
}
