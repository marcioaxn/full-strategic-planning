<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor;
use App\Models\SystemSetting;
use App\Services\Reports\AcabamentoPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class CadeiaDeValor extends Component
{
    /**
     * Este registro não tem organização: vale para a INSTITUIÇÃO inteira
     * (todas as unidades). Por isso, além da capacidade no módulo, gravar
     * exige poder editar o que é institucional — Super Admin ou Administrador
     * da unidade raiz. Antes, o Administrador de qualquer unidade folha (ou
     * um Gestor) alterava o que vale para todas.
     */
    private function autorizarInstitucional(string $ability): void
    {
        $this->authorize("modulo.{$ability}", 'planejamento-estrategico');
        $this->authorize('editar-institucional');
    }

    public $peiAtivo;

    // Formulário atividade
    public bool $showModalAtividade = false;

    #[Locked]
    public ?string $atividadeEditId = null;

    public array $formAtividade = [
        'dsc_atividade' => '',
        'dsc_tipo' => 'Finalística',
        'cod_perspectiva' => '',
        'num_ordem' => 0,
    ];

    // Formulário processo
    public bool $showModalProcesso = false;

    #[Locked]
    public ?string $processoEditId = null;

    #[Locked]
    public ?string $processoAtivId = null;

    public array $formProcesso = [
        'dsc_entrada' => '',
        'dsc_transformacao' => '',
        'dsc_saida' => '',
    ];

    // Feedback
    public bool $showDeleteModal = false;

    #[Locked]
    public string $deleteTarget = '';

    #[Locked]
    public string $deleteId = '';

    protected $listeners = ['peiSelecionado' => 'atualizarPEI'];

    public function mount(): void
    {
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
        $this->reset(['showModalAtividade', 'showModalProcesso', 'showDeleteModal']);
    }

    /**
     * O id vem do navegador: a atividade tem de ser do ciclo em tela. Antes,
     * editava-se ou excluía-se a atividade de qualquer ciclo pelo id.
     */
    private function atividadeDoCiclo(string $id): AtividadeCadeiaValor
    {
        $a = AtividadeCadeiaValor::findOrFail($id);
        abort_unless($this->peiAtivo && $a->cod_pei === $this->peiAtivo->cod_pei, 403);

        return $a;
    }

    private function processoDoCiclo(string $id): ProcessoAtividadeCadeiaValor
    {
        $p = ProcessoAtividadeCadeiaValor::findOrFail($id);
        $this->atividadeDoCiclo($p->cod_atividade_cadeia_valor);

        return $p;
    }

    // ── Atividades ───────────────────────────────────────────────────────────

    public function novaAtividade(): void
    {
        $this->autorizarInstitucional('criar');

        $this->atividadeEditId = null;
        $this->formAtividade = ['dsc_atividade' => '', 'dsc_tipo' => 'Finalística', 'cod_perspectiva' => '', 'num_ordem' => 0];
        $this->showModalAtividade = true;
    }

    public function editarAtividade(string $id): void
    {
        $this->autorizarInstitucional('editar');

        $a = $this->atividadeDoCiclo($id);
        $this->atividadeEditId = $id;
        $this->formAtividade = [
            'dsc_atividade' => $a->dsc_atividade,
            'dsc_tipo' => $a->dsc_tipo ?? 'Finalística',
            'cod_perspectiva' => $a->cod_perspectiva ?? '',
            'num_ordem' => $a->num_ordem ?? 0,
        ];
        $this->showModalAtividade = true;
    }

    public function salvarAtividade(): void
    {
        $this->autorizarInstitucional($this->atividadeEditId ? 'editar' : 'criar');

        $this->validate([
            'formAtividade.dsc_atividade' => 'required|string|max:500',
            'formAtividade.dsc_tipo' => ['required', Rule::in(AtividadeCadeiaValor::TIPOS)],
        ], ['formAtividade.dsc_atividade.required' => 'Informe a descrição da atividade.']);

        if (! $this->peiAtivo) {
            $this->dispatch('notify', message: 'Nenhum ciclo PEI selecionado.', style: 'danger');

            return;
        }
        $data = array_merge($this->formAtividade, ['cod_pei' => $this->peiAtivo->cod_pei]);
        if (empty($data['cod_perspectiva'])) {
            $data['cod_perspectiva'] = null;
        }

        $this->atividadeEditId
            ? $this->atividadeDoCiclo($this->atividadeEditId)->update($data)
            : AtividadeCadeiaValor::create($data);

        $this->showModalAtividade = false;
        $this->atividadeEditId = null;
        $this->dispatch('notify', message: 'Atividade salva!', style: 'success');
    }

    public function confirmarExcluirAtividade(string $id): void
    {
        $this->autorizarInstitucional('excluir');
        $this->atividadeDoCiclo($id);
        $this->deleteTarget = 'atividade';
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // ── Processos ────────────────────────────────────────────────────────────

    public function novoProcesso(string $atividadeId): void
    {
        $this->autorizarInstitucional('criar');
        $this->atividadeDoCiclo($atividadeId);

        $this->processoAtivId = $atividadeId;
        $this->processoEditId = null;
        $this->formProcesso = ['dsc_entrada' => '', 'dsc_transformacao' => '', 'dsc_saida' => ''];
        $this->showModalProcesso = true;
    }

    public function editarProcesso(string $id): void
    {
        $this->autorizarInstitucional('editar');

        $p = $this->processoDoCiclo($id);
        $this->processoAtivId = $p->cod_atividade_cadeia_valor;
        $this->processoEditId = $id;
        $this->formProcesso = [
            'dsc_entrada' => $p->dsc_entrada ?? '',
            'dsc_transformacao' => $p->dsc_transformacao ?? '',
            'dsc_saida' => $p->dsc_saida ?? '',
        ];
        $this->showModalProcesso = true;
    }

    public function salvarProcesso(): void
    {
        $this->autorizarInstitucional($this->processoEditId ? 'editar' : 'criar');

        $this->validate([
            'formProcesso.dsc_transformacao' => 'required|string|max:500',
        ], ['formProcesso.dsc_transformacao.required' => 'Informe a transformação/processo.']);

        $this->atividadeDoCiclo((string) $this->processoAtivId);
        $data = array_merge($this->formProcesso, ['cod_atividade_cadeia_valor' => $this->processoAtivId]);

        $this->processoEditId
            ? $this->processoDoCiclo($this->processoEditId)->update($data)
            : ProcessoAtividadeCadeiaValor::create($data);

        $this->showModalProcesso = false;
        $this->dispatch('notify', message: 'Processo salvo!', style: 'success');
    }

    public function confirmarExcluirProcesso(string $id): void
    {
        $this->autorizarInstitucional('excluir');
        $this->processoDoCiclo($id);
        $this->deleteTarget = 'processo';
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // ── Exclusão ─────────────────────────────────────────────────────────────

    public function executarExclusao(): void
    {
        $this->autorizarInstitucional('excluir');

        match ($this->deleteTarget) {
            'atividade' => $this->atividadeDoCiclo($this->deleteId)->delete(),
            'processo' => $this->processoDoCiclo($this->deleteId)->delete(),
            default => null,
        };
        $this->showDeleteModal = false;
        $this->dispatch('notify', message: 'Registro excluído.', style: 'warning');
    }

    public function gerarPdf()
    {
        // Exportar é capacidade própria, a mesma de todo PDF do sistema: o
        // método é público e invocável direto pelo navegador.
        $this->authorize('modulo.exportar', 'relatorios');
        abort_unless($this->peiAtivo, 404);

        $atividades = AtividadeCadeiaValor::with('processos', 'perspectiva')
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('dsc_tipo')->orderBy('num_ordem')
            ->get()
            ->groupBy('dsc_tipo');

        $pdf = Pdf::loadView('relatorios.cadeia-valor', [
            'pei' => $this->peiAtivo,
            'grupos' => $this->agruparPorTipo($atividades),
            'data' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');

        // Mesmo acabamento dos outros dez relatórios: um renderizador só.
        (new AcabamentoPdf('landscape'))->aplicar($pdf, [
            'esquerda' => $this->peiAtivo?->dsc_pei ?? 'Planejamento Estratégico Integrado',
            'centro' => 'Cadeia de Valor',
            'site' => (string) SystemSetting::getValue('orgao_site', ''),
            'emitido_em' => now()->format('d/m/Y'),
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'Cadeia_de_Valor_'.now()->format('Y_m_d').'.pdf'
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
            'atividades' => $atividades,
            'grupos' => $this->agruparPorTipo($atividades),
            'perspectivas' => $perspectivas,
            'tipos' => AtividadeCadeiaValor::TIPOS,
            // A cadeia de valor é da instituição: a tela só oferece o botão que o servidor aceita.
            'podeCriar' => Gate::allows('modulo.criar', 'planejamento-estrategico') && Gate::allows('editar-institucional'),
            'podeEditar' => Gate::allows('modulo.editar', 'planejamento-estrategico') && Gate::allows('editar-institucional'),
            'podeExcluir' => Gate::allows('modulo.excluir', 'planejamento-estrategico') && Gate::allows('editar-institucional'),
            'podeExportar' => Gate::allows('modulo.exportar', 'relatorios'),
        ]);
    }

    /**
     * Monta um grupo por tipo declarado em AtividadeCadeiaValor::TIPOS, na
     * ordem da constante, já com a apresentação de cada um.
     *
     * Substitui o par de grupos fixos ('Finalística' e 'Suporte') que existia
     * aqui, no PDF e na Blade. Com os grupos fixos, um tipo novo fazia a
     * atividade ser gravada e nunca exibida — erro silencioso.
     *
     * Inclui também tipo encontrado no banco que não esteja mais na constante:
     * dado antigo de cliente não pode desaparecer da tela sem aviso.
     *
     * @param  Collection  $atividadesAgrupadas
     * @return array<int, array{tipo:string, itens:Collection, cor:string, icone:string, titulo:string, ajuda:string}>
     */
    private function agruparPorTipo($atividadesAgrupadas): array
    {
        $apresentacao = AtividadeCadeiaValor::apresentacaoDosTipos();

        $tipos = array_values(array_unique(array_merge(
            AtividadeCadeiaValor::TIPOS,
            $atividadesAgrupadas->keys()->filter()->all()
        )));

        return array_map(fn (string $tipo) => [
            'tipo' => $tipo,
            'itens' => $atividadesAgrupadas->get($tipo, collect()),
            'cor' => $apresentacao[$tipo]['cor'] ?? 'dark',
            'icone' => $apresentacao[$tipo]['icone'] ?? 'bi-diagram-3',
            'titulo' => $apresentacao[$tipo]['titulo'] ?? $tipo,
            'ajuda' => $apresentacao[$tipo]['ajuda'] ?? '',
        ], $tipos);
    }
}
