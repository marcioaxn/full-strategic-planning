# COMPONENTES LIVEWIRE
## Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data:** 23/12/2025

---

## ÍNDICE

1. [Convenções e Estrutura](#1-convenções-e-estrutura)
2. [Componentes de Layout](#2-componentes-de-layout)
3. [Componentes de Autenticação](#3-componentes-de-autenticação)
4. [Componentes de Gestão Organizacional](#4-componentes-de-gestão-organizacional)
5. [Componentes de Identidade Estratégica](#5-componentes-de-identidade-estratégica)
6. [Componentes de BSC](#6-componentes-de-bsc)
7. [Componentes de Planos de Ação](#7-componentes-de-planos-de-ação)
8. [Componentes de Indicadores](#8-componentes-de-indicadores)
9. [Componentes de Dashboards](#9-componentes-de-dashboards)
10. [Componentes Reutilizáveis (UI)](#10-componentes-reutilizáveis-ui)
11. [Traits e Mixins](#11-traits-e-mixins)

---

## 1. CONVENÇÕES E ESTRUTURA

### 1.1 Estrutura de Pastas

```
app/
└── Http/
    └── Livewire/
        ├── Dashboard.php
        ├── Auth/
        │   ├── Login.php
        │   ├── ForgotPassword.php
        │   └── ResetPassword.php
        ├── Organizacao/
        │   ├── ListarOrganizacoes.php
        │   ├── FormOrganizacao.php
        │   └── SeletorOrganizacao.php
        ├── Usuario/
        │   ├── ListarUsuarios.php
        │   ├── FormUsuario.php
        │   └── PerfilUsuario.php
        ├── PEI/
        │   ├── Listarphp
        │   └── Formphp
        ├── Identidade/
        │   ├── MissaoVisao.php
        │   ├── ListarValores.php
        │   └── FormValor.php
        ├── BSC/
        │   ├── ListarPerspectivas.php
        │   ├── ListarObjetivos.php
        │   ├── FormObjetivo.php
        │   └── DetalheObjetivo.php
        ├── PlanoAcao/
        │   ├── ListarPlanos.php
        │   ├── FormPlano.php
        │   ├── DetalhePlano.php
        │   ├── GerenciarEntregas.php
        │   └── AtribuirResponsavel.php
        ├── Indicador/
        │   ├── ListarIndicadores.php
        │   ├── FormIndicador.php
        │   ├── DetalheIndicador.php
        │   ├── LancarEvolucao.php
        │   ├── GerenciarMetas.php
        │   └── AnexarArquivo.php
        ├── Dashboard/
        │   ├── DashboardPrincipal.php
        │   ├── DashboardObjetivos.php
        │   ├── DashboardIndicadores.php
        │   └── MapaEstrategico.php
        ├── Relatorio/
        │   ├── RelatorioIdentidade.php
        │   ├── RelatorioObjetivos.php
        │   └── RelatorioIndicadores.php
        └── Shared/
            ├── Datatable.php
            ├── Modal.php
            ├── SeletorPeriodo.php
            ├── GraficoLinha.php
            └── CardKPI.php

resources/
└── views/
    └── livewire/
        ├── dashboard.blade.php
        ├── auth/
        ├── organizacao/
        ├── usuario/
        ├── pei/
        ├── identidade/
        ├── bsc/
        ├── plano-acao/
        ├── indicador/
        ├── dashboard/
        ├── relatorio/
        └── shared/
```

### 1.2 Nomenclatura de Componentes

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| **Lista/Index** | Listar + Entidade | `ListarOrganizacoes` |
| **Formulário CRUD** | Form + Entidade | `FormUsuario` |
| **Detalhes** | Detalhe + Entidade | `DetalheIndicador` |
| **Ação específica** | Verbo + Objeto | `LancarEvolucao`, `AnexarArquivo` |
| **Seletores** | Seletor + Objeto | `SeletorOrganizacao` |
| **Modais** | Modal + Ação | `ModalConfirmacao` |

### 1.3 Propriedades Comuns

Todos os componentes devem seguir estes padrões:

```php
namespace App\Http\Livewire\ExemploNamespace;

use Livewire\Component;
use Livewire\WithPagination;

class ExemploComponente extends Component
{
    use WithPagination;

    // Propriedades públicas (bindáveis na view)
    public $propriedadePublica;

    // Propriedades protegidas (internas)
    protected $propriedadeProtegida;

    // Regras de validação
    protected $rules = [
        'propriedadePublica' => 'required|min:3',
    ];

    // Mensagens de validação customizadas
    protected $messages = [
        'propriedadePublica.required' => 'Este campo é obrigatório.',
    ];

    // Listeners de eventos
    protected $listeners = ['evento' => 'metodoHandler'];

    // Método de renderização
    public function render()
    {
        return view('livewire.exemplo-namespace.exemplo-componente');
    }
}
```

---

## 2. COMPONENTES DE LAYOUT

### 2.1 AppLayout

**Descrição:** Layout principal da aplicação (já vem com Jetstream).

**Localização:** `resources/views/layouts/app.blade.php`

**Funcionalidades:**
- Menu superior com logo, seletor de organização, perfil do usuário
- Menu lateral (sidebar) com navegação
- Breadcrumbs
- Notificações (toasts)
- Footer

---

## 3. COMPONENTES DE AUTENTICAÇÃO

### 3.1 Login

**Componente:** `App\Http\Livewire\Auth\Login`
**View:** `resources/views/livewire/auth/login.blade.php`

**Propriedades:**
```php
public string $email = '';
public string $password = '';
public bool $remember = false;
```

**Métodos:**
```php
public function login(): void
{
    $this->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        // Verificar se usuário deve trocar senha
        if (Auth::user()->deveTrocarSenha()) {
            return redirect()->route('trocar-senha');
        }

        return redirect()->intended(route('dashboard'));
    }

    $this->addError('email', 'Credenciais inválidas.');
}
```

---

## 4. COMPONENTES DE GESTÃO ORGANIZACIONAL

### 4.1 ListarOrganizacoes

**Componente:** `App\Http\Livewire\Organizacao\ListarOrganizacoes`
**View:** `resources/views/livewire/organizacao/listar-organizacoes.blade.php`

**Propriedades:**
```php
public string $busca = '';
public bool $mostrarExcluidas = false;
```

**Métodos:**
```php
public function render()
{
    $organizacoes = Organization::query()
        ->when($this->busca, fn($q) => $q->where('nom_organizacao', 'ilike', "%{$this->busca}%")
            ->orWhere('sgl_organizacao', 'ilike', "%{$this->busca}%"))
        ->when($this->mostrarExcluidas, fn($q) => $q->onlyTrashed())
        ->orderBy('nom_organizacao')
        ->get();

    return view('livewire.organizacao.listar-organizacoes', compact('organizacoes'));
}

public function excluir(string $codOrganizacao): void
{
    $this->authorize('delete', Organization::class);

    $org = Organization::find($codOrganizacao);
    $org->delete();

    $this->dispatch('organizacao-excluida');
    $this->dispatch('toast', ['message' => 'Organização excluída com sucesso!', 'type' => 'success']);
}

public function restaurar(string $codOrganizacao): void
{
    Organization::withTrashed()->find($codOrganizacao)->restore();

    $this->dispatch('toast', ['message' => 'Organização restaurada!', 'type' => 'success']);
}
```

**View (exemplo de estrutura):**
```blade
<div>
    <div class="mb-3">
        <input wire:model.live.debounce.500ms="busca" type="text" class="form-control" placeholder="Buscar organização...">
    </div>

    <div class="form-check mb-3">
        <input wire:model.live="mostrarExcluidas" class="form-check-input" type="checkbox" id="mostrarExcluidas">
        <label class="form-check-label" for="mostrarExcluidas">Mostrar excluídas</label>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Sigla</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($organizacoes as $org)
                <tr>
                    <td>{{ $org->sgl_organizacao }}</td>
                    <td>{{ $org->nom_organizacao }}</td>
                    <td>
                        <button wire:click="excluir('{{ $org->cod_organizacao }}')" class="btn btn-sm btn-danger">Excluir</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

---

### 4.2 FormOrganizacao

**Componente:** `App\Http\Livewire\Organizacao\FormOrganizacao`
**View:** `resources/views/livewire/organizacao/form-organizacao.blade.php`

**Propriedades:**
```php
public ?string $codOrganizacao = null; // null = criando, preenchido = editando
public string $sglOrganizacao = '';
public string $nomOrganizacao = '';
public ?string $relCodOrganizacao = null;
```

**Regras de Validação:**
```php
protected function rules(): array
{
    return [
        'sglOrganizacao' => 'required|max:20|unique:tab_organizacoes,sgl_organizacao,' . $this->codOrganizacao . ',cod_organizacao',
        'nomOrganizacao' => 'required|max:255',
        'relCodOrganizacao' => 'required|exists:tab_organizacoes,cod_organizacao',
    ];
}
```

**Métodos:**
```php
public function mount(?string $codOrganizacao = null): void
{
    if ($codOrganizacao) {
        $org = Organization::findOrFail($codOrganizacao);
        $this->fill([
            'codOrganizacao' => $org->cod_organizacao,
            'sglOrganizacao' => $org->sgl_organizacao,
            'nomOrganizacao' => $org->nom_organizacao,
            'relCodOrganizacao' => $org->rel_cod_organizacao,
        ]);
    }
}

public function salvar(): void
{
    $this->validate();

    $org = Organization::updateOrCreate(
        ['cod_organizacao' => $this->codOrganizacao],
        [
            'sgl_organizacao' => $this->sglOrganizacao,
            'nom_organizacao' => $this->nomOrganizacao,
            'rel_cod_organizacao' => $this->relCodOrganizacao,
        ]
    );

    $this->dispatch('toast', ['message' => 'Organização salva com sucesso!', 'type' => 'success']);
    return redirect()->route('organizacoes.index');
}
```

---

## 5. COMPONENTES DE IDENTIDADE ESTRATÉGICA

### 5.1 MissaoVisao

**Componente:** `App\Http\Livewire\Identidade\MissaoVisao`
**View:** `resources/views/livewire/identidade/missao-visao.blade.php`

**Propriedades:**
```php
public string $codOrganizacao;
public string $codPei;
public string $dscMissao = '';
public string $dscVisao = '';
public bool $editMode = false;
```

**Métodos:**
```php
public function mount(string $codOrganizacao, string $codPei): void
{
    $this->codOrganizacao = $codOrganizacao;
    $this->codPei = $codPei;

    $identidade = MissaoVisaoValores::where('cod_organizacao', $codOrganizacao)
        ->where('cod_pei', $codPei)
        ->first();

    if ($identidade) {
        $this->dscMissao = $identidade->dsc_missao;
        $this->dscVisao = $identidade->dsc_visao;
    }
}

public function toggleEditMode(): void
{
    $this->editMode = !$this->editMode;
}

public function salvar(): void
{
    $this->validate([
        'dscMissao' => 'required|max:2000',
        'dscVisao' => 'required|max:2000',
    ]);

    MissaoVisaoValores::updateOrCreate(
        [
            'cod_organizacao' => $this->codOrganizacao,
            'cod_pei' => $this->codPei,
        ],
        [
            'dsc_missao' => $this->dscMissao,
            'dsc_visao' => $this->dscVisao,
        ]
    );

    $this->editMode = false;
    $this->dispatch('toast', ['message' => 'Missão e Visão atualizadas!', 'type' => 'success']);
}
```

---

## 6. COMPONENTES DE BSC

### 6.1 ListarObjetivos

**Componente:** `App\Http\Livewire\BSC\ListarObjetivos`
**View:** `resources/views/livewire/bsc/listar-objetivos.blade.php`

**Propriedades:**
```php
public string $codPei;
public ?string $codPerspectivaSelecionada = null;
public string $busca = '';
```

**Métodos:**
```php
public function render()
{
    $perspectivas = Perspectiva::where('cod_pei', $this->codPei)
        ->orderBy('num_nivel_hierarquico_apresentacao')
        ->get();

    $objetivos = ObjetivoEstrategico::query()
        ->when($this->codPerspectivaSelecionada, fn($q) => $q->where('cod_perspectiva', $this->codPerspectivaSelecionada))
        ->when($this->busca, fn($q) => $q->where('nom_objetivo_estrategico', 'ilike', "%{$this->busca}%"))
        ->with('indicadores')
        ->orderBy('num_nivel_hierarquico_apresentacao')
        ->get();

    return view('livewire.bsc.listar-objetivos', compact('perspectivas', 'objetivos'));
}

public function selecionarPerspectiva(string $codPerspectiva): void
{
    $this->codPerspectivaSelecionada = $codPerspectiva;
}
```

---

## 7. COMPONENTES DE PLANOS DE AÇÃO

### 7.1 ListarPlanos

**Componente:** `App\Http\Livewire\PlanoAcao\ListarPlanos`
**View:** `resources/views/livewire/plano-acao/listar-planos.blade.php`

**Propriedades:**
```php
use Livewire\WithPagination;

public ?string $filtroTipo = null;
public ?string $filtroStatus = null;
public ?string $filtroOrganizacao = null;
public string $busca = '';
```

**Métodos:**
```php
public function render()
{
    $planos = PlanoDeAcao::query()
        ->with(['objetivoEstrategico', 'tipoExecucao', 'organizacao'])
        ->when($this->filtroTipo, fn($q) => $q->where('cod_tipo_execucao', $this->filtroTipo))
        ->when($this->filtroStatus, fn($q) => $q->where('bln_status', $this->filtroStatus))
        ->when($this->filtroOrganizacao, fn($q) => $q->where('cod_organizacao', $this->filtroOrganizacao))
        ->when($this->busca, fn($q) => $q->where('dsc_plano_de_acao', 'ilike', "%{$this->busca}%"))
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('livewire.plano-acao.listar-planos', compact('planos'));
}

public function limparFiltros(): void
{
    $this->reset(['filtroTipo', 'filtroStatus', 'filtroOrganizacao', 'busca']);
}
```

---

### 7.2 GerenciarEntregas

**Componente:** `App\Http\Livewire\PlanoAcao\GerenciarEntregas`
**View:** `resources/views/livewire/plano-acao/gerenciar-entregas.blade.php`

**Propriedades:**
```php
public string $codPlanoDeAcao;
public array $entregas = [];
public bool $modalAberto = false;
public string $dscEntrega = '';
public string $blnStatus = '';
public string $dscPeriodoMedicao = '';
```

**Métodos:**
```php
public function mount(string $codPlanoDeAcao): void
{
    $this->codPlanoDeAcao = $codPlanoDeAcao;
    $this->carregarEntregas();
}

public function carregarEntregas(): void
{
    $this->entregas = Entrega::where('cod_plano_de_acao', $this->codPlanoDeAcao)
        ->orderBy('num_nivel_hierarquico_apresentacao')
        ->get()
        ->toArray();
}

public function abrirModal(): void
{
    $this->reset(['dscEntrega', 'blnStatus', 'dscPeriodoMedicao']);
    $this->modalAberto = true;
}

public function salvarEntrega(): void
{
    $this->validate([
        'dscEntrega' => 'required|max:1000',
        'blnStatus' => 'required',
        'dscPeriodoMedicao' => 'required',
    ]);

    $ultimoNivel = Entrega::where('cod_plano_de_acao', $this->codPlanoDeAcao)
        ->max('num_nivel_hierarquico_apresentacao') ?? 0;

    Entrega::create([
        'cod_plano_de_acao' => $this->codPlanoDeAcao,
        'dsc_entrega' => $this->dscEntrega,
        'bln_status' => $this->blnStatus,
        'dsc_periodo_medicao' => $this->dscPeriodoMedicao,
        'num_nivel_hierarquico_apresentacao' => $ultimoNivel + 1,
    ]);

    $this->carregarEntregas();
    $this->modalAberto = false;
    $this->dispatch('toast', ['message' => 'Entrega adicionada!', 'type' => 'success']);
}

public function excluirEntrega(string $codEntrega): void
{
    Entrega::find($codEntrega)->delete();
    $this->carregarEntregas();
    $this->dispatch('toast', ['message' => 'Entrega excluída!', 'type' => 'success']);
}
```

---

## 8. COMPONENTES DE INDICADORES

### 8.1 LancarEvolucao

**Componente:** `App\Http\Livewire\Indicador\LancarEvolucao`
**View:** `resources/views/livewire/indicador/lancar-evolucao.blade.php`

**Propriedades:**
```php
public string $codIndicador;
public int $ano;
public int $mes;
public ?float $vlrPrevisto = null;
public ?float $vlrRealizado = null;
public string $txtAvaliacao = '';
public bool $blnAtualizado = false;
public ?string $codEvolucaoExistente = null;
```

**Métodos:**
```php
public function mount(string $codIndicador, int $ano, int $mes): void
{
    $this->codIndicador = $codIndicador;
    $this->ano = $ano;
    $this->mes = $mes;

    // Carregar evolução existente se houver
    $evolucao = EvolucaoIndicador::where('cod_indicador', $codIndicador)
        ->where('num_ano', $ano)
        ->where('num_mes', $mes)
        ->first();

    if ($evolucao) {
        $this->fill([
            'codEvolucaoExistente' => $evolucao->cod_evolucao_indicador,
            'vlrPrevisto' => $evolucao->vlr_previsto,
            'vlrRealizado' => $evolucao->vlr_realizado,
            'txtAvaliacao' => $evolucao->txt_avaliacao,
            'blnAtualizado' => $evolucao->bln_atualizado === 'Sim',
        ]);
    }
}

public function salvar(): void
{
    $this->validate([
        'vlrPrevisto' => 'nullable|numeric',
        'vlrRealizado' => 'nullable|numeric',
        'txtAvaliacao' => 'nullable|max:2000',
    ]);

    EvolucaoIndicador::updateOrCreate(
        [
            'cod_indicador' => $this->codIndicador,
            'num_ano' => $this->ano,
            'num_mes' => $this->mes,
        ],
        [
            'vlr_previsto' => $this->vlrPrevisto,
            'vlr_realizado' => $this->vlrRealizado,
            'txt_avaliacao' => $this->txtAvaliacao,
            'bln_atualizado' => $this->blnAtualizado ? 'Sim' : 'Não',
        ]
    );

    $this->dispatch('toast', ['message' => 'Evolução lançada com sucesso!', 'type' => 'success']);
}
```

---

## 9. COMPONENTES DE GESTÃO DE RISCOS

### 9.1 RiscoIndex

**Componente:** `App\Http\Livewire\Risco\RiscoIndex`
**View:** `resources/views/livewire/risco/risco-index.blade.php`

**Descrição:** Listagem de riscos estratégicos com filtros e ações.

**Propriedades:**
```php
public string $codPei;
public string $codOrganizacao;
public string $filtroCategoria = '';
public string $filtroNivel = '';
public string $filtroStatus = '';
public string $busca = '';
public int $porPagina = 20;
```

**Métodos:**
```php
public function mount(string $codPei, string $codOrganizacao): void
{
    $this->codPei = $codPei;
    $this->codOrganizacao = $codOrganizacao;
}

public function getRiscosProperty()
{
    return Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->when($this->filtroCategoria, fn($q) => $q->where('dsc_categoria', $this->filtroCategoria))
        ->when($this->filtroNivel, function($q) {
            [$min, $max] = explode('-', $this->filtroNivel);
            return $q->porNivel($min, $max);
        })
        ->when($this->filtroStatus, fn($q) => $q->where('dsc_status', $this->filtroStatus))
        ->when($this->busca, fn($q) => $q->where(function($query) {
            $query->where('dsc_titulo', 'ilike', "%{$this->busca}%")
                  ->orWhere('txt_descricao', 'ilike', "%{$this->busca}%");
        }))
        ->with(['responsavel', 'objetivosEstrategicos', 'mitigacoes', 'ocorrencias'])
        ->orderBy('num_nivel_risco', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate($this->porPagina);
}

public function excluir(string $codRisco): void
{
    $risco = Risco::findOrFail($codRisco);

    if ($risco->temOcorrencia()) {
        $this->dispatch('toast', ['message' => 'Não é possível excluir risco com ocorrências registradas.', 'type' => 'error']);
        return;
    }

    $risco->delete();
    $this->dispatch('toast', ['message' => 'Risco excluído com sucesso!', 'type' => 'success']);
}
```

---

### 9.2 RiscoForm

**Componente:** `App\Http\Livewire\Risco\RiscoForm`
**View:** `resources/views/livewire/risco/risco-form.blade.php`

**Descrição:** Formulário de criação/edição de risco.

**Propriedades:**
```php
public ?string $codRisco = null;
public string $codPei;
public string $codOrganizacao;
public string $dscTitulo = '';
public string $txtDescricao = '';
public string $dscCategoria = '';
public string $dscStatus = 'Identificado';
public int $numProbabilidade = 3;
public int $numImpacto = 3;
public string $txtCausas = '';
public string $txtConsequencias = '';
public ?string $codResponsavelMonitoramento = null;
public array $objetivosSelecionados = [];
```

**Validação:**
```php
protected function rules(): array
{
    return [
        'dscTitulo' => ['required', 'string', 'max:255'],
        'txtDescricao' => ['required', 'string'],
        'dscCategoria' => ['required', 'string'],
        'dscStatus' => ['required', 'string'],
        'numProbabilidade' => ['required', 'integer', 'min:1', 'max:5'],
        'numImpacto' => ['required', 'integer', 'min:1', 'max:5'],
        'txtCausas' => ['nullable', 'string'],
        'txtConsequencias' => ['nullable', 'string'],
        'codResponsavelMonitoramento' => ['required', 'uuid', 'exists:users,id'],
        'objetivosSelecionados' => ['nullable', 'array'],
    ];
}
```

**Métodos:**
```php
public function mount(?string $codRisco = null): void
{
    if ($codRisco) {
        $risco = Risco::with('objetivosEstrategicos')->findOrFail($codRisco);
        $this->codRisco = $risco->cod_risco;
        $this->fill($risco->toArray());
        $this->objetivosSelecionados = $risco->objetivosEstrategicos->pluck('cod_objetivo_estrategico')->toArray();
    }
}

public function salvar(): void
{
    $this->validate();

    $dados = [
        'cod_pei' => $this->codPei,
        'cod_organizacao' => $this->codOrganizacao,
        'dsc_titulo' => $this->dscTitulo,
        'txt_descricao' => $this->txtDescricao,
        'dsc_categoria' => $this->dscCategoria,
        'dsc_status' => $this->dscStatus,
        'num_probabilidade' => $this->numProbabilidade,
        'num_impacto' => $this->numImpacto,
        'txt_causas' => $this->txtCausas,
        'txt_consequencias' => $this->txtConsequencias,
        'cod_responsavel_monitoramento' => $this->codResponsavelMonitoramento,
    ];

    $risco = $this->codRisco
        ? Risco::findOrFail($this->codRisco)
        : new Risco();

    $risco->fill($dados);
    $risco->save();

    // Sincronizar objetivos estratégicos
    $risco->objetivosEstrategicos()->sync($this->objetivosSelecionados);

    // Se crítico, notificar admins
    if ($risco->isCritico()) {
        // Disparar notificação
    }

    $this->dispatch('toast', ['message' => 'Risco salvo com sucesso!', 'type' => 'success']);
    return redirect()->route('riscos.show', $risco->cod_risco);
}

public function updatedNumProbabilidade(): void
{
    $this->calcularNivelRisco();
}

public function updatedNumImpacto(): void
{
    $this->calcularNivelRisco();
}

private function calcularNivelRisco(): int
{
    return $this->numProbabilidade * $this->numImpacto;
}
```

---

### 9.3 RiscoShow

**Componente:** `App\Http\Livewire\Risco\RiscoShow`
**View:** `resources/views/livewire/risco/risco-show.blade.php`

**Descrição:** Visualização detalhada de um risco.

**Propriedades:**
```php
public string $codRisco;
public ?Risco $risco = null;
public bool $modalMitigacaoAberto = false;
public bool $modalOcorrenciaAberto = false;
```

**Métodos:**
```php
public function mount(string $codRisco): void
{
    $this->codRisco = $codRisco;
    $this->carregarRisco();
}

public function carregarRisco(): void
{
    $this->risco = Risco::with([
        'pei',
        'organizacao',
        'responsavel',
        'objetivosEstrategicos',
        'mitigacoes.responsavel',
        'ocorrencias'
    ])->findOrFail($this->codRisco);
}

public function abrirModalMitigacao(): void
{
    $this->modalMitigacaoAberto = true;
}

public function abrirModalOcorrencia(): void
{
    $this->modalOcorrenciaAberto = true;
}
```

---

### 9.4 MitigacaoForm

**Componente:** `App\Http\Livewire\Risco\MitigacaoForm`
**View:** `resources/views/livewire/risco/mitigacao-form.blade.php`

**Descrição:** Formulário de plano de mitigação.

**Propriedades:**
```php
public string $codRisco;
public ?string $codMitigacao = null;
public string $dscTipo = 'Prevenir';
public string $txtDescricao = '';
public ?string $codResponsavel = null;
public ?string $dtePrazo = null;
public string $dscStatus = 'A Fazer';
public ?float $vlrCustoEstimado = null;
```

**Validação:**
```php
protected function rules(): array
{
    return [
        'dscTipo' => ['required', 'string', 'in:Prevenir,Reduzir,Transferir,Aceitar'],
        'txtDescricao' => ['required', 'string'],
        'codResponsavel' => ['required', 'uuid', 'exists:users,id'],
        'dtePrazo' => ['required', 'date', 'after:today'],
        'dscStatus' => ['required', 'string', 'in:A Fazer,Em Andamento,Concluído'],
        'vlrCustoEstimado' => ['nullable', 'numeric', 'min:0'],
    ];
}
```

**Métodos:**
```php
public function salvar(): void
{
    $this->validate();

    $mitigacao = $this->codMitigacao
        ? RiscoMitigacao::findOrFail($this->codMitigacao)
        : new RiscoMitigacao();

    $mitigacao->fill([
        'cod_risco' => $this->codRisco,
        'dsc_tipo' => $this->dscTipo,
        'txt_descricao' => $this->txtDescricao,
        'cod_responsavel' => $this->codResponsavel,
        'dte_prazo' => $this->dtePrazo,
        'dsc_status' => $this->dscStatus,
        'vlr_custo_estimado' => $this->vlrCustoEstimado,
    ]);

    $mitigacao->save();

    $this->dispatch('toast', ['message' => 'Plano de mitigação salvo!', 'type' => 'success']);
    $this->dispatch('mitigacaoSalva');
}
```

---

### 9.5 OcorrenciaForm

**Componente:** `App\Http\Livewire\Risco\OcorrenciaForm`
**View:** `resources/views/livewire/risco/ocorrencia-form.blade.php`

**Descrição:** Formulário de registro de ocorrência.

**Propriedades:**
```php
public string $codRisco;
public string $dteOcorrencia;
public string $txtDescricao = '';
public int $numImpactoReal = 3;
public string $txtAcoesTomadas = '';
public string $txtLicoesAprendidas = '';
```

**Validação:**
```php
protected function rules(): array
{
    return [
        'dteOcorrencia' => ['required', 'date', 'before_or_equal:today'],
        'txtDescricao' => ['required', 'string'],
        'numImpactoReal' => ['required', 'integer', 'min:1', 'max:5'],
        'txtAcoesTomadas' => ['required', 'string'],
        'txtLicoesAprendidas' => ['nullable', 'string'],
    ];
}
```

**Métodos:**
```php
public function salvar(): void
{
    $this->validate();

    $ocorrencia = new RiscoOcorrencia();
    $ocorrencia->fill([
        'cod_risco' => $this->codRisco,
        'dte_ocorrencia' => $this->dteOcorrencia,
        'txt_descricao' => $this->txtDescricao,
        'num_impacto_real' => $this->numImpactoReal,
        'txt_acoes_tomadas' => $this->txtAcoesTomadas,
        'txt_licoes_aprendidas' => $this->txtLicoesAprendidas,
    ]);

    $ocorrencia->save();

    // Atualizar status do risco para "Materializado"
    $risco = Risco::findOrFail($this->codRisco);
    $risco->dsc_status = 'Materializado';
    $risco->save();

    // Notificar responsáveis

    $this->dispatch('toast', ['message' => 'Ocorrência registrada!', 'type' => 'success']);
    $this->dispatch('ocorrenciaRegistrada');
}
```

---

### 9.6 MatrizRiscos

**Componente:** `App\Http\Livewire\Risco\MatrizRiscos`
**View:** `resources/views/livewire/risco/matriz-riscos.blade.php`

**Descrição:** Visualização da matriz Probabilidade x Impacto.

**Propriedades:**
```php
public string $codPei;
public string $codOrganizacao;
public array $matrizDados = [];
```

**Métodos:**
```php
public function mount(string $codPei, string $codOrganizacao): void
{
    $this->codPei = $codPei;
    $this->codOrganizacao = $codOrganizacao;
    $this->carregarMatriz();
}

public function carregarMatriz(): void
{
    $riscos = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->ativos()
        ->get();

    // Organizar riscos na matriz 5x5
    $this->matrizDados = [];
    for ($probabilidade = 5; $probabilidade >= 1; $probabilidade--) {
        for ($impacto = 1; $impacto <= 5; $impacto++) {
            $riscosNaCelula = $riscos->filter(function($risco) use ($probabilidade, $impacto) {
                return $risco->num_probabilidade === $probabilidade
                    && $risco->num_impacto === $impacto;
            });

            $this->matrizDados[$probabilidade][$impacto] = $riscosNaCelula;
        }
    }
}
```

---

### 9.7 DashboardRiscos

**Componente:** `App\Http\Livewire\Risco\DashboardRiscos`
**View:** `resources/views/livewire/risco/dashboard-riscos.blade.php`

**Descrição:** Dashboard executivo de riscos.

**Propriedades:**
```php
public string $codPei;
public string $codOrganizacao;
public array $kpis = [];
public array $dadosGraficoPizza = [];
public array $dadosGraficoBarras = [];
public array $dadosGraficoLinha = [];
public array $riscosCriticos = [];
public array $alertas = [];
```

**Métodos:**
```php
public function mount(string $codPei, string $codOrganizacao): void
{
    $this->codPei = $codPei;
    $this->codOrganizacao = $codOrganizacao;
    $this->carregarDados();
}

public function carregarDados(): void
{
    // KPIs
    $totalRiscos = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->ativos()
        ->count();

    $riscosCriticos = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->criticos()
        ->ativos()
        ->count();

    $riscosMateriializados = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->where('dsc_status', 'Materializado')
        ->whereHas('ocorrencias', fn($q) => $q->recentes(30))
        ->count();

    $taxaMitigacao = $totalRiscos > 0
        ? (Risco::where('cod_pei', $this->codPei)
            ->where('cod_organizacao', $this->codOrganizacao)
            ->ativos()
            ->whereHas('mitigacoes')
            ->count() / $totalRiscos) * 100
        : 0;

    $this->kpis = [
        'totalRiscos' => $totalRiscos,
        'riscosCriticos' => $riscosCriticos,
        'riscosMateriializados' => $riscosMateriializados,
        'taxaMitigacao' => round($taxaMitigacao, 1),
    ];

    // Gráfico Pizza - Distribuição por categoria
    $distribuicaoCategoria = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->ativos()
        ->selectRaw('dsc_categoria, COUNT(*) as total')
        ->groupBy('dsc_categoria')
        ->get();

    $this->dadosGraficoPizza = [
        'labels' => $distribuicaoCategoria->pluck('dsc_categoria')->toArray(),
        'datasets' => [[
            'data' => $distribuicaoCategoria->pluck('total')->toArray(),
            'backgroundColor' => ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe'],
        ]],
    ];

    // Top 10 riscos críticos
    $this->riscosCriticos = Risco::where('cod_pei', $this->codPei)
        ->where('cod_organizacao', $this->codOrganizacao)
        ->ativos()
        ->orderBy('num_nivel_risco', 'desc')
        ->limit(10)
        ->get();

    // Alertas
    $this->alertas = [
        'riscosSemMitigacao' => Risco::where('cod_pei', $this->codPei)
            ->where('cod_organizacao', $this->codOrganizacao)
            ->criticos()
            ->doesntHave('mitigacoes')
            ->count(),
        'mitigacoesAtrasadas' => RiscoMitigacao::whereHas('risco', fn($q) =>
            $q->where('cod_pei', $this->codPei)
              ->where('cod_organizacao', $this->codOrganizacao)
        )->atrasados()->count(),
    ];
}
```

---

## 10. COMPONENTES DE DASHBOARDS

### 10.1 DashboardPrincipal

**Componente:** `App\Http\Livewire\Dashboard\DashboardPrincipal`
**View:** `resources/views/livewire/dashboard/dashboard-principal.blade.php`

**Propriedades:**
```php
public string $codOrganizacao;
public string $codPei;
public array $kpis = [];
public array $dadosGraficoRadar = [];
```

**Métodos:**
```php
public function mount(string $codOrganizacao, string $codPei): void
{
    $this->codOrganizacao = $codOrganizacao;
    $this->codPei = $codPei;
    $this->carregarDados();
}

public function carregarDados(): void
{
    // KPIs principais
    $this->kpis = [
        'totalObjetivos' => ObjetivoEstrategico::whereHas('perspectiva', fn($q) => $q->where('cod_pei', $this->codPei))->count(),
        'totalPlanos' => PlanoDeAcao::where('cod_organizacao', $this->codOrganizacao)->count(),
        'totalIndicadores' => Indicador::whereHas('objetivoEstrategico.perspectiva', fn($q) => $q->where('cod_pei', $this->codPei))->count(),
        'percentualMedio' => 75.5, // Calcular baseado em indicadores
    ];

    // Dados para gráfico radar (% por perspectiva)
    $this->dadosGraficoRadar = Perspectiva::where('cod_pei', $this->codPei)
        ->get()
        ->map(function($perspectiva) {
            return [
                'label' => $perspectiva->dsc_perspectiva,
                'value' => 80, // Calcular atingimento médio dos objetivos da perspectiva
            ];
        })
        ->toArray();
}
```

---

## 11. COMPONENTES REUTILIZÁVEIS (UI)

### 11.1 Datatable

**Componente:** `App\Http\Livewire\Shared\Datatable`
**View:** `resources/views/livewire/shared/datatable.blade.php`

**Descrição:** Componente genérico de tabela com filtros, busca e paginação.

**Propriedades:**
```php
public array $colunas = []; // ['field' => 'nome', 'label' => 'Nome', 'sortable' => true]
public string $modelClass;
public string $busca = '';
public string $ordenarPor = 'created_at';
public string $direcao = 'desc';
```

---

### 11.2 Modal

**Componente:** `App\Http\Livewire\Shared\Modal`
**View:** `resources/views/livewire/shared/modal.blade.php`

**Descrição:** Modal genérico reutilizável.

**Propriedades:**
```php
public bool $aberto = false;
public string $titulo = '';
public string $tamanho = 'md'; // sm, md, lg, xl
```

---

## 12. TRAITS E MIXINS

### 12.1 WithNotification

**Localização:** `app/Http/Livewire/Traits/WithNotification.php`

```php
<?php

namespace App\Http\Livewire\Traits;

trait WithNotification
{
    public function notifySuccess(string $message): void
    {
        $this->dispatch('toast', ['message' => $message, 'type' => 'success']);
    }

    public function notifyError(string $message): void
    {
        $this->dispatch('toast', ['message' => $message, 'type' => 'error']);
    }

    public function notifyWarning(string $message): void
    {
        $this->dispatch('toast', ['message' => $message, 'type' => 'warning']);
    }

    public function notifyInfo(string $message): void
    {
        $this->dispatch('toast', ['message' => $message, 'type' => 'info']);
    }
}
```

**Uso:**
```php
use App\Http\Livewire\Traits\WithNotification;

class MeuComponente extends Component
{
    use WithNotification;

    public function salvar(): void
    {
        // ...
        $this->notifySuccess('Registro salvo com sucesso!');
    }
}
```

---

**Próximo Documento:** 06-MIGRATIONS-NOVAS.md, 07-ESTRUTURA-PASTAS.md, 08-ROADMAP-IMPLEMENTACAO.md
