<?php

namespace App\Livewire\Documentos;

use App\Models\Documento;
use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Acervo de documentos em PDF. Cada método público é um endpoint e autoriza
 * por dentro (DocumentoPolicy); a lista só traz o que o usuário pode ver.
 */
#[Layout('layouts.app')]
class ListarDocumentos extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    /** Teto do arquivo, em KB — o mesmo do upload_max_filesize do PHP (20M). */
    public const TAMANHO_MAXIMO_KB = 20480;

    // Filtros
    public string $search = '';

    public string $filtroTipo = '';

    public string $filtroPei = '';

    public string $filtroAno = '';

    // Modais
    public bool $showModal = false;

    public bool $showDeleteModal = false;

    #[Locked]
    public ?string $documentoId = null;

    public string $arquivoAtual = '';

    // Formulário
    public $arquivo;

    public string $nom_documento = '';

    public string $dsc_tipo = '';

    public string $num_documento = '';

    public $num_ano_referencia = '';

    public string $dte_documento = '';

    public string $dsc_origem = '';

    public string $txt_descricao = '';

    public string $dsc_link = '';

    public string $cod_pei = '';

    public string $cod_organizacao = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'filtroPei' => ['except' => ''],
        'filtroAno' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Documento::class);
    }

    public function updating($propriedade): void
    {
        if (in_array($propriedade, ['search', 'filtroTipo', 'filtroPei', 'filtroAno'], true)) {
            $this->resetPage();
        }
    }

    public function limparFiltros(): void
    {
        $this->reset('search', 'filtroTipo', 'filtroPei', 'filtroAno');
        $this->resetPage();
    }

    public function create(): void
    {
        abort_unless($this->unidadesParaEnvio() !== [] || auth()->user()->podeEditarInstitucional(), 403);

        $this->limparFormulario();
        $this->cod_pei = (string) (session('pei_selecionado_id') ?? '');
        $this->cod_organizacao = auth()->user()->isSuperAdmin() ? '' : (string) (array_key_first($this->unidadesParaEnvio()) ?? '');
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $documento = $this->documento($id);
        $this->authorize('update', $documento);

        $this->limparFormulario();
        $this->documentoId = $documento->cod_documento;
        $this->arquivoAtual = $documento->dsc_nome_arquivo;
        $this->nom_documento = $documento->nom_documento;
        $this->dsc_tipo = $documento->dsc_tipo;
        $this->num_documento = (string) $documento->num_documento;
        $this->num_ano_referencia = (string) ($documento->num_ano_referencia ?? '');
        $this->dte_documento = $documento->dte_documento?->format('Y-m-d') ?? '';
        $this->dsc_origem = (string) $documento->dsc_origem;
        $this->txt_descricao = (string) $documento->txt_descricao;
        $this->dsc_link = (string) $documento->dsc_link;
        $this->cod_pei = (string) $documento->cod_pei;
        $this->cod_organizacao = (string) $documento->cod_organizacao;
        $this->showModal = true;
    }

    public function save(): void
    {
        $documento = $this->documentoId ? Documento::findOrFail($this->documentoId) : null;
        $organizacao = $this->cod_organizacao !== '' ? $this->cod_organizacao : null;

        if ($documento) {
            $this->authorize('update', $documento);
        }
        // Criar, ou mudar o documento de unidade, exige poder gravar na unidade de destino.
        if (! $documento || $documento->cod_organizacao !== $organizacao) {
            if (! Gate::allows('create', [Documento::class, $organizacao])) {
                $this->addError('cod_organizacao', $organizacao
                    ? 'Você não pode enviar documentos para esta unidade.'
                    : 'Documento institucional (sem unidade) só pode ser enviado pelo Super Administrador ou pelo Administrador da unidade raiz.');

                return;
            }
        }

        $this->nom_documento = trim($this->nom_documento);
        $this->validate([
            'arquivo' => [$documento ? 'nullable' : 'required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:'.self::TAMANHO_MAXIMO_KB],
            'nom_documento' => 'required|string|max:255',
            'dsc_tipo' => ['required', 'string', Rule::in(Documento::tiposPlanos())],
            'num_documento' => 'nullable|string|max:60',
            'num_ano_referencia' => 'nullable|integer|min:1900|max:2100',
            'dte_documento' => 'nullable|date',
            'dsc_origem' => 'nullable|string|max:255',
            'txt_descricao' => 'nullable|string|max:5000',
            'dsc_link' => 'nullable|url:http,https|max:500',
            'cod_pei' => 'nullable|uuid',
            'cod_organizacao' => 'nullable|uuid',
        ], [
            'arquivo.required' => 'Selecione o arquivo PDF.',
            'arquivo.mimes' => 'O arquivo precisa ser um PDF.',
            'arquivo.mimetypes' => 'O arquivo precisa ser um PDF.',
            'arquivo.max' => 'O PDF pode ter no máximo 20 MB.',
            'nom_documento.required' => 'Informe o nome do documento.',
            'dsc_tipo.required' => 'Selecione o tipo de documento.',
            'dsc_tipo.in' => 'Selecione um tipo da lista.',
            'dsc_link.url' => 'Informe um endereço completo, começando por http:// ou https://.',
            'num_ano_referencia.integer' => 'Informe o ano com quatro dígitos.',
        ]);

        if ($this->cod_pei !== '' && ! PEI::whereKey($this->cod_pei)->exists()) {
            $this->addError('cod_pei', 'Selecione um PEI da lista.');

            return;
        }
        if ($organizacao && ! Organization::whereKey($organizacao)->exists()) {
            $this->addError('cod_organizacao', 'Selecione uma unidade da lista.');

            return;
        }

        $dados = [
            'nom_documento' => $this->nom_documento,
            'dsc_tipo' => $this->dsc_tipo,
            'num_documento' => $this->num_documento !== '' ? trim($this->num_documento) : null,
            'num_ano_referencia' => $this->num_ano_referencia !== '' ? (int) $this->num_ano_referencia : null,
            'dte_documento' => $this->dte_documento !== '' ? $this->dte_documento : null,
            'dsc_origem' => $this->dsc_origem !== '' ? trim($this->dsc_origem) : null,
            'txt_descricao' => $this->txt_descricao !== '' ? trim($this->txt_descricao) : null,
            'dsc_link' => $this->dsc_link !== '' ? trim($this->dsc_link) : null,
            'cod_pei' => $this->cod_pei !== '' ? $this->cod_pei : null,
            'cod_organizacao' => $organizacao,
        ];

        $caminhoAntigo = null;
        if ($this->arquivo) {
            $real = $this->arquivo->getRealPath();
            // A extensão e o tipo informado pelo navegador podem mentir; o começo do arquivo, não.
            if (! is_string($real) || file_get_contents($real, false, null, 0, 5) !== '%PDF-') {
                $this->addError('arquivo', 'O arquivo enviado não é um PDF válido.');

                return;
            }

            $nomeOriginal = Str::limit(preg_replace('/[^\pL\pN ._()-]/u', '_', $this->arquivo->getClientOriginalName()), 200, '');
            $dados += [
                'dsc_nome_arquivo' => str_ends_with(mb_strtolower($nomeOriginal), '.pdf') ? $nomeOriginal : $nomeOriginal.'.pdf',
                'num_tamanho_bytes' => $this->arquivo->getSize(),
                'dsc_hash_sha256' => hash_file('sha256', $real),
                'dsc_caminho' => $this->arquivo->storeAs(Documento::PASTA, Str::uuid().'.pdf', Documento::DISCO),
            ];
            $caminhoAntigo = $documento?->dsc_caminho;
        }

        try {
            if ($documento) {
                $documento->update($dados);
                $mensagem = 'Documento atualizado.';
            } else {
                Documento::create($dados + ['cod_usuario' => auth()->id()]);
                $mensagem = 'Documento enviado para o acervo.';
            }
        } catch (\Throwable $e) {
            report($e);
            if (isset($dados['dsc_caminho'])) {
                Storage::disk(Documento::DISCO)->delete($dados['dsc_caminho']);
            }
            $this->addError('arquivo', 'Não foi possível gravar o documento. Tente novamente; se persistir, acione o suporte.');

            return;
        }

        if ($caminhoAntigo) {
            Storage::disk(Documento::DISCO)->delete($caminhoAntigo);
        }

        $this->showModal = false;
        $this->limparFormulario();
        session()->flash('status', $mensagem);
    }

    public function confirmDelete(string $id): void
    {
        $documento = $this->documento($id);
        $this->authorize('delete', $documento);

        $this->documentoId = $documento->cod_documento;
        $this->nom_documento = $documento->nom_documento;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $documento = Documento::findOrFail($this->documentoId);
        $this->authorize('delete', $documento);

        // Exclusão lógica: o arquivo fica no disco e o registro na auditoria.
        $documento->delete();

        $this->showDeleteModal = false;
        $this->limparFormulario();
        session()->flash('status', 'Documento excluído do acervo.');
    }

    /** Id que não é UUID vira 404, não erro de banco. */
    private function documento(string $id): Documento
    {
        abort_unless(Str::isUuid($id), 404);

        return Documento::findOrFail($id);
    }

    public function fecharModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->limparFormulario();
    }

    private function limparFormulario(): void
    {
        $this->reset('arquivo', 'documentoId', 'arquivoAtual', 'nom_documento', 'dsc_tipo', 'num_documento',
            'num_ano_referencia', 'dte_documento', 'dsc_origem', 'txt_descricao', 'dsc_link', 'cod_pei', 'cod_organizacao');
        $this->resetValidation();
    }

    /**
     * Unidades em que o usuário pode enviar documento.
     *
     * @return array<string, string> cod_organizacao => "SIGLA — Nome"
     */
    private function unidadesParaEnvio(): array
    {
        $user = auth()->user();
        $consulta = Organization::query()->orderBy('nom_organizacao');
        if (! $user->isSuperAdmin()) {
            $consulta->whereIn('cod_organizacao', $user->organizacaoIdsPermitidas());
        }

        return $consulta->get()
            ->filter(fn (Organization $org) => Gate::allows('create', [Documento::class, $org->cod_organizacao]))
            ->mapWithKeys(fn (Organization $org) => [$org->cod_organizacao => trim(($org->sgl_organizacao ? $org->sgl_organizacao.' — ' : '').$org->nom_organizacao)])
            ->all();
    }

    public function render()
    {
        $user = auth()->user();

        $consulta = Documento::query()
            ->visiveisPara($user)
            ->with(['pei:cod_pei,dsc_pei', 'organizacao:cod_organizacao,sgl_organizacao,nom_organizacao']);

        if ($this->search !== '') {
            $termo = '%'.$this->search.'%';
            $consulta->where(function ($q) use ($termo) {
                $q->where('nom_documento', 'ilike', $termo)
                    ->orWhere('num_documento', 'ilike', $termo)
                    ->orWhere('dsc_origem', 'ilike', $termo)
                    ->orWhere('txt_descricao', 'ilike', $termo);
            });
        }
        if ($this->filtroTipo !== '') {
            $consulta->where('dsc_tipo', $this->filtroTipo);
        }
        if ($this->filtroPei !== '' && Str::isUuid($this->filtroPei)) {
            $consulta->where('cod_pei', $this->filtroPei);
        }
        if ($this->filtroAno !== '' && ctype_digit($this->filtroAno)) {
            $consulta->where('num_ano_referencia', (int) $this->filtroAno);
        }

        $unidades = $this->unidadesParaEnvio();
        $podeInstitucional = $user->isSuperAdmin() || $user->podeEditarInstitucional();

        return view('livewire.documentos.listar-documentos', [
            'documentos' => $consulta->orderByDesc('created_at')->paginate(15),
            'peis' => PEI::orderByDesc('num_ano_inicio_pei')->get(['cod_pei', 'dsc_pei']),
            'unidadesParaEnvio' => $unidades,
            'podeInstitucional' => $podeInstitucional,
            'podeEnviar' => $unidades !== [] || $podeInstitucional,
            'anosComDocumento' => Documento::query()->visiveisPara($user)->whereNotNull('num_ano_referencia')
                ->distinct()->orderByDesc('num_ano_referencia')->pluck('num_ano_referencia'),
        ]);
    }
}
