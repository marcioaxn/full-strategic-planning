<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\Organization;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class AnalisePESTEL extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public $peiAtivo;

    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    // Dados agrupados por categoria PESTEL
    public $politicos = [];

    public $economicos = [];

    public $sociais = [];

    public $tecnologicos = [];

    public $ambientais = [];

    public $legais = [];

    // Modal
    public bool $showModal = false;

    #[Locked]
    public $itemId;

    public $dsc_categoria;

    public $dsc_item = '';

    public $num_impacto = 3;

    public $txt_observacao = '';

    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        // Organização da sessão só vale se estiver no escopo do usuário.
        $this->atualizarOrganizacao(Auth::user()->organizacaoSelecionadaId());
    }

    /**
     * A capacidade vale na unidade EM OPERAÇÃO — a desta tela, que é a dos
     * registros. Antes, o Gate somava os perfis de todas as unidades do
     * usuário: Administrador na A e Gestor Substituto na B excluía itens da B.
     */
    private function autorizarNaUnidade(string $ability): void
    {
        abort_unless($this->organizacaoId !== null, 403);
        $this->authorize("modulo.{$ability}", ['planejamento-estrategico', $this->organizacaoId]);
    }

    public function pedirAjudaIA()
    {
        $this->autorizarNaUnidade('criar');

        if (! $this->aiEnabled) {
            return;
        }

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

            $this->aiSuggestion = 'Pensando...';

            $prompt = "Sugira 2 fatores para cada dimensão da análise PESTEL (Político, Econômico, Social, Tecnológico, Ambiental, Legal) para a organização: {$this->organizacaoNome}.
            Responda OBRIGATORIAMENTE em formato JSON puro com as chaves 'politico', 'economico', 'social', 'tecnologico', 'ambiental', 'legal', cada uma contendo um array de strings.";

            $response = $aiService->suggest($prompt);
            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Resposta em formato inválido.');
            }
        } catch (\Throwable $e) {
            \Log::error('Erro IA PESTEL: '.$e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões.');
        }
    }

    public function adicionarSugerido($categoria, $item)
    {
        $this->autorizarNaUnidade('criar');
        abort_unless($this->peiAtivo !== null, 403);

        AnaliseAmbiental::create([
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_PESTEL,
            'dsc_categoria' => $categoria,
            'dsc_item' => $item,
            'num_impacto' => 3,
        ]);

        $this->carregarDados();

        // Remover da sugestão
        $map = [
            'Político' => 'politico',
            'Econômico' => 'economico',
            'Social' => 'social',
            'Tecnológico' => 'tecnologico',
            'Ambiental' => 'ambiental',
            'Legal' => 'legal',
        ];
        $key = $map[$categoria] ?? null;

        if ($key && isset($this->aiSuggestion[$key])) {
            $this->aiSuggestion[$key] = array_filter($this->aiSuggestion[$key], fn ($i) => $item !== $i);
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarDados();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (! $this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente. Sem esta
        // checagem, o escopo #[Locked] era trocado por qualquer organização.
        abort_unless(! $id || Auth::user()?->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        $this->carregarDados();
    }

    public function carregarDados()
    {
        if (! $this->peiAtivo) {
            return;
        }

        $query = AnaliseAmbiental::pestel()
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->ordenado();

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        } else {
            // Sem unidade, nunca "todas": o escopo do usuário (Super Admin vê tudo).
            Auth::user()->aplicarEscopoOrganizacional($query);
        }

        $itens = $query->get();

        $this->politicos = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_POLITICO)->values()->toArray();
        $this->economicos = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_ECONOMICO)->values()->toArray();
        $this->sociais = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_SOCIAL)->values()->toArray();
        $this->tecnologicos = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_TECNOLOGICO)->values()->toArray();
        $this->ambientais = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_AMBIENTAL)->values()->toArray();
        $this->legais = $itens->where('dsc_categoria', AnaliseAmbiental::PESTEL_LEGAL)->values()->toArray();
    }

    public function create($categoria)
    {
        $this->autorizarNaUnidade('criar');
        $this->resetForm();
        $this->dsc_categoria = $categoria;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->autorizarNaUnidade('editar');

        $item = AnaliseAmbiental::findOrFail($id);
        abort_unless($item->cod_organizacao === $this->organizacaoId, 403);
        $this->itemId = $id;
        $this->dsc_categoria = $item->dsc_categoria;
        $this->dsc_item = $item->dsc_item;
        $this->num_impacto = $item->num_impacto;
        $this->txt_observacao = $item->txt_observacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->autorizarNaUnidade($this->itemId ? 'editar' : 'criar');

        if (! $this->peiAtivo) {
            session()->flash('error', 'Selecione um Ciclo PEI antes de salvar.');

            return;
        }

        $this->validate([
            'dsc_item' => 'required|string|max:500',
            'num_impacto' => 'required|integer|min:1|max:5',
            'txt_observacao' => 'nullable|string|max:1000',
        ]);

        $data = [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_PESTEL,
            'dsc_categoria' => $this->dsc_categoria,
            'dsc_item' => $this->dsc_item,
            'num_impacto' => $this->num_impacto,
            'txt_observacao' => $this->txt_observacao,
        ];

        if ($this->itemId) {
            $item = AnaliseAmbiental::findOrFail($this->itemId);
            abort_unless($item->cod_organizacao === $this->organizacaoId, 403);
            $item->update($data);
            $message = 'Item atualizado com sucesso!';
        } else {
            AnaliseAmbiental::create($data);
            $message = 'Item adicionado com sucesso!';
        }

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', $message);
    }

    public function delete($id)
    {
        $this->autorizarNaUnidade('excluir');

        $item = AnaliseAmbiental::findOrFail($id);
        abort_unless($item->cod_organizacao === $this->organizacaoId, 403);
        $item->delete();
        $this->carregarDados();
        session()->flash('status', 'Item removido com sucesso!');
    }

    public function resetForm()
    {
        $this->itemId = null;
        $this->dsc_categoria = '';
        $this->dsc_item = '';
        $this->num_impacto = 3;
        $this->txt_observacao = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.analise-p-e-s-t-e-l', [
            'categorias' => AnaliseAmbiental::categoriasPESTEL(),
            // A tela só oferece o botão que o servidor aceita.
            ...$this->permissoesNaUnidade(),
        ]);
    }

    /** @return array{podeCriar: bool, podeEditar: bool, podeExcluir: bool} */
    private function permissoesNaUnidade(): array
    {
        $pode = fn (string $a) => $this->organizacaoId !== null
            && Gate::allows("modulo.{$a}", ['planejamento-estrategico', $this->organizacaoId]);

        return ['podeCriar' => $pode('criar'), 'podeEditar' => $pode('editar'), 'podeExcluir' => $pode('excluir')];
    }
}
