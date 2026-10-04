<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\NotificationService;
use App\Support\UnidadeMedida;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ListarGrausSatisfacao extends Component
{
    /**
     * As faixas são a régua do farol do CICLO inteiro (a tabela não tem
     * organização): uma alteração repinta o desempenho de todas as unidades.
     * Por isso, além da capacidade no módulo, gravar exige poder editar o que
     * é institucional — Super Admin ou Administrador da unidade raiz.
     */
    private function autorizarInstitucional(string $ability): void
    {
        $this->authorize("modulo.{$ability}", 'graus-satisfacao');
        $this->authorize('editar-institucional');
    }

    use WithPagination;

    // Campos do formulario
    #[Locked]
    public $cod_grau_satisfacao;

    public $cod_pei;

    public $num_ano;

    public $dsc_grau_satisfacao = '';

    public $cor = '';

    public $vlr_minimo = '';

    public $vlr_maximo = '';

    // Controle do modal
    public $showModal = false;

    public $showDeleteModal = false;

    #[Locked]
    public $isEditing = false;

    public $grauId = null; // Alterado de deleteId para grauId para consistência

    // Busca
    public $search = '';

    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    // Success Modal Properties
    public bool $showSuccessModal = false;

    public bool $showErrorModal = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public string $createdGrauName = '';

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('modulo.acessar', 'graus-satisfacao');

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);

        // "Editar" no detalhe do grau chega com ?editar={cod}.
        $editar = request()->query('editar');
        if (is_string($editar) && auth()->user()->can('modulo.editar', 'graus-satisfacao')
            && auth()->user()->can('editar-institucional')
            && GrauSatisfacao::whereKey($editar)->exists()) {
            $this->edit($editar);
        }
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->successMessage = '';
        $this->createdGrauName = '';
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessage = '';
    }

    public function pedirAjudaIA()
    {
        $this->autorizarInstitucional('criar');

        if (! $this->aiEnabled) {
            return;
        }

        $aiService = AiServiceFactory::make();
        if (! $aiService) {
            return;
        }

        $this->aiSuggestion = 'Pensando...';

        $prompt = "Sugira uma escala de 4 a 5 Graus de Satisfação padrão para monitoramento estratégico. 
        A escala deve cobrir de 0 a 100%. 
        Para cada nível forneça: Descrição (ex: Crítico, Excelente), Cor (Hexadecimal vibrante), Valor Mínimo e Valor Máximo.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'cor', 'min' e 'max'.";

        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $cor, $min, $max)
    {
        // Sempre lê o PEI atual da sessão (nunca reaproveita um valor já
        // carregado): evita gravar a sugestão vinculada a um PEI antigo caso
        // o usuário troque o Ciclo PEI no menu superior antes de aplicar.
        $this->cod_pei = session('pei_selecionado_id');

        if (! $this->cod_pei) {
            session()->flash('error', 'Selecione um Ciclo PEI antes de aplicar sugestões da IA.');

            return;
        }

        $this->dsc_grau_satisfacao = $nome;
        $this->cor = $cor;
        $this->vlr_minimo = $min;
        $this->vlr_maximo = $max;

        $this->save();

        // Remove da lista
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function ($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            if (empty($this->aiSuggestion)) {
                $this->aiSuggestion = '';
            }
        }
    }

    protected function rules()
    {
        return [
            'cod_pei' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! PEI::where('cod_pei', $value)->exists()) {
                        $fail('O Ciclo PEI selecionado é inválido.');
                    }
                },
            ],
            'dsc_grau_satisfacao' => 'required|string|max:100',
            // Só #rrggbb: a cor vai para atributo style= em várias telas e relatórios.
            'cor' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'vlr_minimo' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! $this->percentualValido(UnidadeMedida::paraFloat($value))) {
                        $fail('Informe o percentual mínimo entre 0 e 999,99. Ex.: 0,00');
                    }
                },
            ],
            'vlr_maximo' => [
                'required',
                function ($attribute, $value, $fail) {
                    $minimo = UnidadeMedida::paraFloat($this->vlr_minimo);
                    $maximo = UnidadeMedida::paraFloat($value);

                    if (! $this->percentualValido($maximo)) {
                        $fail('Informe o percentual máximo entre 0 e 999,99. Ex.: 29,99');

                        return;
                    }

                    if ($minimo !== null && $maximo < $minimo) {
                        $fail('O valor máximo deve ser maior ou igual ao mínimo.');

                        return;
                    }

                    if ($minimo !== null && ($conflito = $this->faixaSobreposta($minimo, $maximo))) {
                        $fail(sprintf(
                            'Esta faixa se sobrepõe a "%s" (%s%% a %s%%). As faixas não podem se cruzar: um mesmo resultado teria duas cores.',
                            $conflito->dsc_grau_satisfacao,
                            number_format((float) $conflito->vlr_minimo, 2, ',', '.'),
                            number_format((float) $conflito->vlr_maximo, 2, ',', '.')
                        ));
                    }
                },
            ],
        ];
    }

    private function percentualValido(?float $valor): bool
    {
        return $valor !== null && $valor >= 0 && $valor <= 999.99;
    }

    /**
     * Outra faixa do mesmo ciclo e do mesmo ano que cruza o intervalo informado.
     *
     * A tela sempre disse que as faixas "devem ser contíguas e exclusivas", mas
     * nada impedia o cruzamento. Encostar no limite (0–50 e 50–75) é permitido;
     * cruzar (0–60 e 50–75) não.
     */
    private function faixaSobreposta(float $minimo, float $maximo): ?GrauSatisfacao
    {
        return GrauSatisfacao::query()
            ->where('cod_pei', $this->cod_pei ?? session('pei_selecionado_id'))
            ->when(
                $this->num_ano,
                fn ($q) => $q->where('num_ano', $this->num_ano),
                fn ($q) => $q->whereNull('num_ano')
            )
            ->when(
                $this->isEditing && $this->cod_grau_satisfacao,
                fn ($q) => $q->where('cod_grau_satisfacao', '!=', $this->cod_grau_satisfacao)
            )
            ->where('vlr_minimo', '<', $maximo)
            ->where('vlr_maximo', '>', $minimo)
            ->orderBy('vlr_minimo')
            ->first();
    }

    protected $messages = [
        'cod_pei.required' => 'O Ciclo PEI é obrigatório.',
        'cod_pei.exists' => 'O Ciclo PEI selecionado é inválido.',
        'dsc_grau_satisfacao.required' => 'A descrição é obrigatória.',
        'cor.required' => 'A cor é obrigatória.',
        'vlr_minimo.required' => 'O valor mínimo é obrigatório.',
        'vlr_minimo.numeric' => 'O valor mínimo deve ser numérico.',
        'vlr_maximo.required' => 'O valor máximo é obrigatório.',
        'vlr_maximo.numeric' => 'O valor máximo deve ser numérico.',
        'vlr_maximo.gte' => 'O valor máximo deve ser maior ou igual ao mínimo.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->autorizarInstitucional('criar');

        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->cod_grau_satisfacao = null;
        $this->cod_pei = session('pei_selecionado_id');
        $this->num_ano = null; // Default: Geral do Ciclo
        $this->dsc_grau_satisfacao = '';
        $this->cor = '';
        $this->vlr_minimo = '';
        $this->vlr_maximo = '';
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function save()
    {
        // Todo método público de componente Livewire é invocável direto pelo
        // navegador: autorizar só no mount() deixaria a escrita aberta para
        // quem tem apenas leitura (Gestor Responsável e Substituto).
        $this->autorizarInstitucional($this->isEditing ? 'editar' : 'criar');

        $this->validate();

        try {
            $data = [
                'dsc_grau_satisfacao' => $this->dsc_grau_satisfacao,
                'cor' => strtolower(trim($this->cor)),
                'vlr_minimo' => UnidadeMedida::paraFloat($this->vlr_minimo),
                'vlr_maximo' => UnidadeMedida::paraFloat($this->vlr_maximo),
                'cod_pei' => $this->cod_pei ?? session('pei_selecionado_id'),
                'num_ano' => $this->num_ano,
            ];

            if ($this->isEditing && $this->cod_grau_satisfacao) {
                $grau = GrauSatisfacao::find($this->cod_grau_satisfacao);
                if ($grau) {
                    $grau->update($data);
                    $this->successMessage = 'As alterações no grau de satisfação foram salvas com sucesso.';
                }
            } else {
                GrauSatisfacao::create($data);
                $this->successMessage = 'O novo grau de satisfação foi registrado e já está disponível para uso no sistema.';
            }

            $this->createdGrauName = $this->dsc_grau_satisfacao;
            $this->closeModal();
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            $this->errorMessage = 'Ocorreu um erro técnico ao processar sua solicitação. Por favor, verifique os dados e tente novamente.';
            $this->showErrorModal = true;
        }
    }

    public function edit($id)
    {
        $this->autorizarInstitucional('editar');

        $grau = GrauSatisfacao::find($id);

        if ($grau) {
            $this->cod_grau_satisfacao = $grau->cod_grau_satisfacao;
            $this->cod_pei = $grau->cod_pei;
            $this->num_ano = $grau->num_ano;
            $this->dsc_grau_satisfacao = $grau->dsc_grau_satisfacao;
            $this->cor = $grau->cor;
            // No formato que o campo mostra e o usuário edita: "29,99".
            $this->vlr_minimo = number_format((float) $grau->vlr_minimo, 2, ',', '.');
            $this->vlr_maximo = number_format((float) $grau->vlr_maximo, 2, ',', '.');
            $this->isEditing = true;
            $this->showModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->autorizarInstitucional('excluir');

        $this->grauId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->autorizarInstitucional('excluir');

        if ($this->grauId) {
            $grau = GrauSatisfacao::find($this->grauId);
            if ($grau) {
                $nome = $grau->dsc_grau_satisfacao;
                $grau->delete();

                $alert = NotificationService::sendMentorAlert(
                    'Grau Removido',
                    "A faixa “{$nome}” foi excluída com sucesso.",
                    'bi-trash',
                    'warning'
                );
                $this->dispatch('mentor-notification', ...$alert);
            }
        }

        $this->showDeleteModal = false;
        $this->grauId = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->grauId = null;
    }

    public function render()
    {
        $peiId = session('pei_selecionado_id');

        $graus = GrauSatisfacao::query()
            ->where(function ($q) use ($peiId) {
                if ($peiId) {
                    $q->where('cod_pei', $peiId)->orWhereNull('cod_pei');
                }
            })
            ->when($this->search, function ($query) {
                $query->where('dsc_grau_satisfacao', 'ilike', '%'.$this->search.'%')
                    ->orWhere('cor', 'ilike', '%'.$this->search.'%');
            })
            ->orderBy('num_ano', 'asc') // Agrupa por ano (maturidade)
            ->orderBy('vlr_minimo', 'asc')
            ->paginate(15);

        return view('livewire.p-e-i.listar-graus-satisfacao', [
            'graus' => $graus,
            'availablePeis' => PEI::orderBy('num_ano_inicio_pei', 'desc')->get(),
            // A régua é do ciclo inteiro: a tela só oferece o botão que o servidor aceita.
            'podeCriar' => Gate::allows('editar-institucional') && Gate::allows('modulo.criar', 'graus-satisfacao'),
            'podeEditar' => Gate::allows('editar-institucional') && Gate::allows('modulo.editar', 'graus-satisfacao'),
            'podeExcluir' => Gate::allows('editar-institucional') && Gate::allows('modulo.excluir', 'graus-satisfacao'),
        ]);
    }
}
