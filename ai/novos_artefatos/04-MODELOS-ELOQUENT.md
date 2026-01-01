# MODELOS ELOQUENT E RELACIONAMENTOS
## Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data:** 23/12/2025

---

## ÍNDICE

1. [Convenções e Padrões](#1-convenções-e-padrões)
2. [Models do Schema PUBLIC](#2-models-do-schema-public)
3. [Models do Schema PEI](#3-models-do-schema-pei)
4. [Traits Compartilhados](#4-traits-compartilhados)
5. [Observers e Events](#5-observers-e-events)
6. [Scopes Úteis](#6-scopes-úteis)
7. [Matriz de Relacionamentos](#7-matriz-de-relacionamentos)

---

## 1. CONVENÇÕES E PADRÕES

### 1.1 Estrutura de Pastas

```
app/
├── Models/
│   ├── User.php
│   ├── Organization.php
│   ├── PerfilAcesso.php
│   ├── Acao.php
│   ├── Audit.php
│   ├── TabAudit.php
│   ├── TabStatus.php
│   └── PEI/
│       ├── PEI.php
│       ├── MissaoVisaoValores.php
│       ├── Valor.php
│       ├── FuturoAlmejadoObjetivoEstrategico.php
│       ├── Perspectiva.php
│       ├── ObjetivoEstrategico.php
│       ├── TipoExecucao.php
│       ├── PlanoDeAcao.php
│       ├── Entrega.php
│       ├── Indicador.php
│       ├── EvolucaoIndicador.php
│       ├── LinhaBaseIndicador.php
│       ├── MetaPorAno.php
│       ├── GrauSatisfacao.php
│       ├── Arquivo.php
│       ├── AtividadeCadeiaValor.php
│       └── ProcessoAtividadeCadeiaValor.php
```

### 1.2 Padrões de Nomenclatura

| Elemento | Padrão | Exemplo |
|----------|--------|---------|
| Nome da Classe | PascalCase | `PlanoDeAcao` |
| Nome da Tabela | snake_case | `pei.tab_plano_de_acao` |
| Chave Primária | `cod_*` ou `id` | `cod_plano_de_acao` |
| Chaves Estrangeiras | `cod_*` ou `*_id` | `cod_organizacao`, `user_id` |
| Relacionamentos | camelCase | `planosAcao()`, `organizacao()` |
| Scopes | camelCase | `scopeAtivos()` |
| Mutators/Accessors | camelCase | `getNomCompletoAttribute()` |

### 1.3 Traits Comuns

Todos os models que usam UUID e Soft Deletes devem incluir:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExemploModel extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;
}
```

---

## 2. MODELS DO SCHEMA PUBLIC

### 2.1 User.php

**Localização:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasUuids;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'users';

    /**
     * Chave primária
     */
    protected $primaryKey = 'id';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'ativo',
        'adm',
        'trocarsenha',
    ];

    /**
     * Atributos que devem ser hidden
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Atributos que devem ser cast
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ativo' => 'boolean',
        'adm' => 'boolean',
        'trocarsenha' => 'boolean',
    ];

    /**
     * Relacionamento: Organizações que o usuário pertence
     */
    public function organizacoes(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'rel_users_tab_organizacoes',
            'user_id',
            'cod_organizacao',
            'id',
            'cod_organizacao'
        );
    }

    /**
     * Relacionamento: Perfis de acesso do usuário
     */
    public function perfisAcesso(): BelongsToMany
    {
        return $this->belongsToMany(
            PerfilAcesso::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'user_id',
            'cod_perfil',
            'id',
            'cod_perfil'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Ações (logs simples)
     */
    public function acoes(): HasMany
    {
        return $this->hasMany(Acao::class, 'user_id', 'id');
    }

    /**
     * Relacionamento: Auditorias
     */
    public function audits(): HasMany
    {
        return $this->hasMany(TabAudit::class, 'user_id', 'id');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se usuário é Super Administrador
     */
    public function isSuperAdmin(): bool
    {
        return $this->adm === true;
    }

    /**
     * Verifica se usuário está ativo
     */
    public function isAtivo(): bool
    {
        return $this->ativo === true;
    }

    /**
     * Verifica se usuário precisa trocar senha
     */
    public function deveTrocarSenha(): bool
    {
        return $this->trocarsenha === true;
    }

    /**
     * Verifica se usuário tem permissão em uma organização
     */
    public function temPermissaoOrganizacao(Organization $org): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->organizacoes()->where('cod_organizacao', $org->cod_organizacao)->exists();
    }

    /**
     * Obter perfis do usuário em uma organização específica
     */
    public function perfisNaOrganizacao(Organization $org)
    {
        return $this->perfisAcesso()
            ->wherePivot('cod_organizacao', $org->cod_organizacao)
            ->get();
    }

    /**
     * Verifica se usuário é gestor responsável de um plano
     */
    public function isGestorResponsavel(string $codPlanoDeAcao): bool
    {
        return $this->perfisAcesso()
            ->where('cod_perfil', PerfilAcesso::GESTOR_RESPONSAVEL)
            ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)
            ->exists();
    }

    /**
     * Verifica se usuário é gestor substituto de um plano
     */
    public function isGestorSubstituto(string $codPlanoDeAcao): bool
    {
        return $this->perfisAcesso()
            ->where('cod_perfil', PerfilAcesso::GESTOR_SUBSTITUTO)
            ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)
            ->exists();
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas usuários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope: Apenas administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->where('adm', true);
    }
}
```

---

### 2.2 Organization.php

**Localização:** `app/Models/Organization.php`

```php
<?php

namespace App\Models;

use App\Models\PEI\MissaoVisaoValores;
use App\Models\PEI\PEI;
use App\Models\PEI\PlanoDeAcao;
use App\Models\PEI\Valor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_organizacoes';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_organizacao';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'sgl_organizacao',
        'nom_organizacao',
        'rel_cod_organizacao',
    ];

    /**
     * Relacionamento: Organização pai (hierarquia)
     */
    public function pai(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Organizações filhas
     */
    public function filhas(): HasMany
    {
        return $this->hasMany(Organization::class, 'rel_cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Usuários da organização
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes',
            'cod_organizacao',
            'user_id',
            'cod_organizacao',
            'id'
        );
    }

    /**
     * Relacionamento: Planos de Ação
     */
    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Identidade Estratégica (Missão/Visão)
     */
    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(MissaoVisaoValores::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Valores
     */
    public function valores(): HasMany
    {
        return $this->hasMany(Valor::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Obter toda a hierarquia (esta organização + filhas recursivamente)
     */
    public function obterHierarquia()
    {
        return collect([$this])->merge(
            $this->filhas()->with('filhas')->get()->flatMap(fn($f) => $f->obterHierarquia())
        );
    }

    /**
     * Verifica se é organização raiz (auto-referenciada)
     */
    public function isRaiz(): bool
    {
        return $this->cod_organizacao === $this->rel_cod_organizacao;
    }

    /**
     * Obter nível hierárquico (0 = raiz, 1 = filha direta, etc.)
     */
    public function getNivelHierarquico(int $nivel = 0): int
    {
        if ($this->isRaiz()) {
            return $nivel;
        }

        if ($this->pai) {
            return $this->pai->getNivelHierarquico($nivel + 1);
        }

        return $nivel;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas organizações raiz
     */
    public function scopeRaiz($query)
    {
        return $query->whereColumn('cod_organizacao', 'rel_cod_organizacao');
    }

    /**
     * Scope: Organizações filhas de uma específica
     */
    public function scopeFilhasDe($query, string $codOrganizacaoPai)
    {
        return $query->where('rel_cod_organizacao', $codOrganizacaoPai)
                     ->where('cod_organizacao', '!=', $codOrganizacaoPai);
    }
}
```

---

### 2.3 PerfilAcesso.php

**Localização:** `app/Models/PerfilAcesso.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerfilAcesso extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_perfil_acesso';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_perfil';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'dsc_perfil',
        'dsc_permissao',
    ];

    /**
     * Constantes de perfis pré-definidos
     */
    const SUPER_ADMIN = 'c00b9ebc-7014-4d37-97dc-7875e55fff2a';
    const ADMIN_UNIDADE = 'c00b9ebc-7014-4d37-97dc-7875e55fff3b';
    const GESTOR_RESPONSAVEL = 'c00b9ebc-7014-4d37-97dc-7875e55fff4c';
    const GESTOR_SUBSTITUTO = 'c00b9ebc-7014-4d37-97dc-7875e55fff5d';

    /**
     * Relacionamento: Usuários com este perfil
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rel_users_tab_organizacoes_tab_perfil_acesso',
            'cod_perfil',
            'user_id',
            'cod_perfil',
            'id'
        )->withPivot('cod_organizacao', 'cod_plano_de_acao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se é perfil de Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->cod_perfil === self::SUPER_ADMIN;
    }

    /**
     * Verifica se é perfil de Admin de Unidade
     */
    public function isAdminUnidade(): bool
    {
        return $this->cod_perfil === self::ADMIN_UNIDADE;
    }

    /**
     * Verifica se é perfil de gestor (responsável ou substituto)
     */
    public function isGestor(): bool
    {
        return in_array($this->cod_perfil, [self::GESTOR_RESPONSAVEL, self::GESTOR_SUBSTITUTO]);
    }
}
```

---

## 3. MODELS DO SCHEMA PEI

### 3.1 PEI.php

**Localização:** `app/Models/PEI/PEI.php`

```php
<?php

namespace App\Models\PEI;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PEI extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_pei';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_pei';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'dsc_pei',
        'num_ano_inicio_pei',
        'num_ano_fim_pei',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_ano_inicio_pei' => 'integer',
        'num_ano_fim_pei' => 'integer',
    ];

    /**
     * Relacionamento: Perspectivas BSC
     */
    public function perspectivas(): HasMany
    {
        return $this->hasMany(Perspectiva::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Identidade Estratégica
     */
    public function identidadeEstrategica(): HasMany
    {
        return $this->hasMany(MissaoVisaoValores::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Valores
     */
    public function valores(): HasMany
    {
        return $this->hasMany(Valor::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Atividades da Cadeia de Valor
     */
    public function atividadesCadeiaValor(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se PEI está ativo (ano atual entre início e fim)
     */
    public function isAtivo(): bool
    {
        $anoAtual = now()->year;
        return $anoAtual >= $this->num_ano_inicio_pei && $anoAtual <= $this->num_ano_fim_pei;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Apenas PEIs ativos
     */
    public function scopeAtivos($query)
    {
        $anoAtual = now()->year;
        return $query->where('num_ano_inicio_pei', '<=', $anoAtual)
                     ->where('num_ano_fim_pei', '>=', $anoAtual);
    }

    /**
     * Scope: PEIs futuros
     */
    public function scopeFuturos($query)
    {
        $anoAtual = now()->year;
        return $query->where('num_ano_inicio_pei', '>', $anoAtual);
    }

    /**
     * Scope: PEIs passados
     */
    public function scopePassados($query)
    {
        $anoAtual = now()->year;
        return $query->where('num_ano_fim_pei', '<', $anoAtual);
    }
}
```

---

### 3.2 PlanoDeAcao.php

**Localização:** `app/Models/PEI/PlanoDeAcao.php`

```php
<?php

namespace App\Models\PEI;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PlanoDeAcao extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_plano_de_acao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_plano_de_acao';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_objetivo_estrategico',
        'cod_tipo_execucao',
        'cod_organizacao',
        'num_nivel_hierarquico_apresentacao',
        'dsc_plano_de_acao',
        'dte_inicio',
        'dte_fim',
        'vlr_orcamento_previsto',
        'bln_status',
        'cod_ppa',
        'cod_loa',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'dte_inicio' => 'date',
        'dte_fim' => 'date',
        'vlr_orcamento_previsto' => 'decimal:2',
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: Objetivo
     */
    public function objetivoEstrategico(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }

    /**
     * Relacionamento: Tipo de Execução (Ação/Iniciativa/Projeto)
     */
    public function tipoExecucao(): BelongsTo
    {
        return $this->belongsTo(TipoExecucao::class, 'cod_tipo_execucao', 'cod_tipo_execucao');
    }

    /**
     * Relacionamento: Organização
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    /**
     * Relacionamento: Entregas
     */
    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Indicadores
     */
    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Verifica se plano está atrasado
     */
    public function isAtrasado(): bool
    {
        return now()->greaterThan($this->dte_fim) && $this->bln_status !== 'Concluído';
    }

    /**
     * Calcula percentual de progresso (baseado em entregas)
     */
    public function calcularProgressoEntregas(): float
    {
        $totalEntregas = $this->entregas()->count();
        if ($totalEntregas === 0) {
            return 0;
        }

        $entregasConcluidas = $this->entregas()->where('bln_status', 'Concluído')->count();
        return ($entregasConcluidas / $totalEntregas) * 100;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por tipo de execução
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->whereHas('tipoExecucao', function($q) use ($tipo) {
            $q->where('dsc_tipo_execucao', $tipo);
        });
    }

    /**
     * Scope: Por status
     */
    public function scopePorStatus($query, string $status)
    {
        return $query->where('bln_status', $status);
    }

    /**
     * Scope: Planos atrasados
     */
    public function scopeAtrasados($query)
    {
        return $query->where('dte_fim', '<', now())
                     ->where('bln_status', '!=', 'Concluído');
    }

    /**
     * Scope: Planos em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('dte_inicio', '<=', now())
                     ->where('dte_fim', '>=', now())
                     ->where('bln_status', '!=', 'Concluído');
    }
}
```

---

### 3.3 Indicador.php

**Localização:** `app/Models/PEI/Indicador.php`

```php
<?php

namespace App\Models\PEI;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Indicador extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'pei.tab_indicador';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_indicador';

    /**
     * Tipo da chave primária
     */
    protected $keyType = 'string';

    /**
     * Chave primária não é auto-incremental
     */
    public $incrementing = false;

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_plano_de_acao',
        'cod_objetivo_estrategico',
        'dsc_tipo',
        'nom_indicador',
        'dsc_indicador',
        'txt_observacao',
        'dsc_meta',
        'dsc_atributos',
        'dsc_referencial_comparativo',
        'dsc_unidade_medida',
        'num_peso',
        'bln_acumulado',
        'dsc_formula',
        'dsc_fonte',
        'dsc_periodo_medicao',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_peso' => 'integer',
    ];

    /**
     * Relacionamento: Plano de Ação (opcional)
     */
    public function planoDeAcao(): BelongsTo
    {
        return $this->belongsTo(PlanoDeAcao::class, 'cod_plano_de_acao', 'cod_plano_de_acao');
    }

    /**
     * Relacionamento: Objetivo (opcional)
     */
    public function objetivoEstrategico(): BelongsTo
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }

    /**
     * Relacionamento: Evoluções mensais
     */
    public function evolucoes(): HasMany
    {
        return $this->hasMany(EvolucaoIndicador::class, 'cod_indicador', 'cod_indicador');
    }

    /**
     * Relacionamento: Linha de base
     */
    public function linhaBase(): HasMany
    {
        return $this->hasMany(LinhaBaseIndicador::class, 'cod_indicador', 'cod_indicador');
    }

    /**
     * Relacionamento: Metas por ano
     */
    public function metasPorAno(): HasMany
    {
        return $this->hasMany(MetaPorAno::class, 'cod_indicador', 'cod_indicador');
    }

    /**
     * Relacionamento: Organizações (muitos-para-muitos)
     */
    public function organizacoes(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'pei.rel_indicador_objetivo_estrategico_organizacao',
            'cod_indicador',
            'cod_organizacao',
            'cod_indicador',
            'cod_organizacao'
        );
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Obter última evolução registrada
     */
    public function getUltimaEvolucao()
    {
        return $this->evolucoes()->orderBy('num_ano', 'desc')->orderBy('num_mes', 'desc')->first();
    }

    /**
     * Calcular percentual de atingimento (última medição vs. meta anual)
     */
    public function calcularAtingimento(int $ano = null): float
    {
        $ano = $ano ?? now()->year;

        $meta = $this->metasPorAno()->where('num_ano', $ano)->first();
        if (!$meta || $meta->meta == 0) {
            return 0;
        }

        $ultimaEvolucao = $this->evolucoes()
            ->where('num_ano', $ano)
            ->orderBy('num_mes', 'desc')
            ->first();

        if (!$ultimaEvolucao || $ultimaEvolucao->vlr_realizado === null) {
            return 0;
        }

        return ($ultimaEvolucao->vlr_realizado / $meta->meta) * 100;
    }

    /**
     * Obter cor do farol de desempenho
     */
    public function getCorFarol(int $ano = null): ?string
    {
        $percentual = $this->calcularAtingimento($ano);

        $grau = GrauSatisfacao::where('vlr_minimo', '<=', $percentual)
            ->where('vlr_maximo', '>=', $percentual)
            ->first();

        return $grau->cor ?? null;
    }

    /**
     * Scopes
     */

    /**
     * Scope: Indicadores de objetivo
     */
    public function scopeDeObjetivo($query)
    {
        return $query->whereNotNull('cod_objetivo_estrategico');
    }

    /**
     * Scope: Indicadores de plano de ação
     */
    public function scopeDePlano($query)
    {
        return $query->whereNotNull('cod_plano_de_acao');
    }

    /**
     * Scope: Por período de medição
     */
    public function scopePorPeriodo($query, string $periodo)
    {
        return $query->where('dsc_periodo_medicao', $periodo);
    }
}
```

---

## 4. TRAITS COMPARTILHADOS

### 4.1 Auditable Trait (já incluso no pacote owen-it/laravel-auditing)

Adicionar trait `\OwenIt\Auditing\Auditable` aos models que precisam de auditoria completa.

**Models que devem implementar Auditable:**
- `PlanoDeAcao`
- `Indicador`
- `ObjetivoEstrategico`
- `MissaoVisaoValores`
- `Valor`

---

## 5. OBSERVERS E EVENTS

### 5.1 PlanoDeAcaoObserver

**Localização:** `app/Observers/PlanoDeAcaoObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\PEI\PlanoDeAcao;

class PlanoDeAcaoObserver
{
    /**
     * Handle the PlanoDeAcao "creating" event.
     */
    public function creating(PlanoDeAcao $plano): void
    {
        // Definir nível hierárquico padrão se não informado
        if ($plano->num_nivel_hierarquico_apresentacao === null) {
            $ultimoNivel = PlanoDeAcao::where('cod_objetivo_estrategico', $plano->cod_objetivo_estrategico)
                ->max('num_nivel_hierarquico_apresentacao');
            $plano->num_nivel_hierarquico_apresentacao = ($ultimoNivel ?? 0) + 1;
        }
    }

    /**
     * Handle the PlanoDeAcao "updating" event.
     */
    public function updating(PlanoDeAcao $plano): void
    {
        // Lógica adicional ao atualizar (ex: notificações)
    }

    /**
     * Handle the PlanoDeAcao "deleting" event.
     */
    public function deleting(PlanoDeAcao $plano): void
    {
        // Soft delete em cascata de entregas e indicadores (se necessário)
    }
}
```

**Registrar Observer em:** `app/Providers/EventServiceProvider.php`

```php
use App\Models\PEI\PlanoDeAcao;
use App\Observers\PlanoDeAcaoObserver;

public function boot(): void
{
    PlanoDeAcao::observe(PlanoDeAcaoObserver::class);
}
```

---

## 6. SCOPES ÚTEIS

### 6.1 Global Scope: OrganizacaoScope

**Descrição:** Automaticamente filtrar registros pela organização do usuário logado (exceto Super Admin).

**Localização:** `app/Models/Scopes/OrganizacaoScope.php`

```php
<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizacaoScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user || $user->isSuperAdmin()) {
            return; // Super Admin vê tudo
        }

        $organizacoesPermitidas = $user->organizacoes()->pluck('cod_organizacao');

        $builder->whereIn('cod_organizacao', $organizacoesPermitidas);
    }
}
```

**Aplicar em Models que têm `cod_organizacao`:**

```php
use App\Models\Scopes\OrganizacaoScope;

protected static function booted()
{
    static::addGlobalScope(new OrganizacaoScope);
}
```

---

## 6. MODELS DE GESTÃO DE RISCOS

### 6.1 Model: Risco

**Arquivo:** `app/Models/Risco.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Risco extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_risco';
    protected $primaryKey = 'cod_risco';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_pei',
        'cod_organizacao',
        'num_codigo_risco',
        'dsc_titulo',
        'txt_descricao',
        'dsc_categoria',
        'dsc_status',
        'num_probabilidade',
        'num_impacto',
        'num_nivel_risco',
        'txt_causas',
        'txt_consequencias',
        'cod_responsavel_monitoramento',
    ];

    protected $casts = [
        'num_codigo_risco' => 'integer',
        'num_probabilidade' => 'integer',
        'num_impacto' => 'integer',
        'num_nivel_risco' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function pei()
    {
        return $this->belongsTo(Pei::class, 'cod_pei', 'cod_pei');
    }

    public function organizacao()
    {
        return $this->belongsTo(Organization::class, 'cod_organizacao', 'cod_organizacao');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'cod_responsavel_monitoramento', 'id');
    }

    public function objetivosEstrategicos()
    {
        return $this->belongsToMany(
            ObjetivoEstrategico::class,
            'pei.tab_risco_objetivo',
            'cod_risco',
            'cod_objetivo_estrategico'
        )->withTimestamps();
    }

    public function mitigacoes()
    {
        return $this->hasMany(RiscoMitigacao::class, 'cod_risco', 'cod_risco');
    }

    public function ocorrencias()
    {
        return $this->hasMany(RiscoOcorrencia::class, 'cod_risco', 'cod_risco');
    }

    // === SCOPES ===

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('dsc_status', ['Encerrado']);
    }

    public function scopeCriticos($query)
    {
        return $query->where('num_nivel_risco', '>=', 16);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('dsc_categoria', $categoria);
    }

    public function scopePorNivel($query, $nivelMin, $nivelMax = null)
    {
        $q = $query->where('num_nivel_risco', '>=', $nivelMin);

        if ($nivelMax) {
            $q->where('num_nivel_risco', '<=', $nivelMax);
        }

        return $q;
    }

    // === MÉTODOS AUXILIARES ===

    public function calcularNivelRisco()
    {
        $this->num_nivel_risco = $this->num_probabilidade * $this->num_impacto;
        return $this->num_nivel_risco;
    }

    public function getNivelRiscoLabel()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return 'Crítico';
        if ($nivel >= 10) return 'Alto';
        if ($nivel >= 5) return 'Médio';
        return 'Baixo';
    }

    public function getNivelRiscoCor()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return '#dc2626'; // Vermelho
        if ($nivel >= 10) return '#f97316'; // Laranja
        if ($nivel >= 5) return '#eab308';  // Amarelo
        return '#65a30d'; // Verde
    }

    public function getNivelRiscoBadgeClass()
    {
        $nivel = $this->num_nivel_risco;

        if ($nivel >= 16) return 'bg-danger';
        if ($nivel >= 10) return 'bg-warning';
        if ($nivel >= 5) return 'bg-info';
        return 'bg-success';
    }

    public function isCritico()
    {
        return $this->num_nivel_risco >= 16;
    }

    public function temPlanoMitigacao()
    {
        return $this->mitigacoes()->count() > 0;
    }

    public function temOcorrencia()
    {
        return $this->ocorrencias()->count() > 0;
    }

    public function getProbabilidadeLabel()
    {
        return match($this->num_probabilidade) {
            1 => 'Muito Baixa',
            2 => 'Baixa',
            3 => 'Média',
            4 => 'Alta',
            5 => 'Muito Alta',
            default => 'Não definida'
        };
    }

    public function getImpactoLabel()
    {
        return match($this->num_impacto) {
            1 => 'Muito Baixo',
            2 => 'Baixo',
            3 => 'Médio',
            4 => 'Alto',
            5 => 'Muito Alto',
            default => 'Não definido'
        };
    }

    // === BOOT ===

    protected static function boot()
    {
        parent::boot();

        // Ao criar/atualizar, calcular nível de risco automaticamente
        static::saving(function ($risco) {
            if ($risco->num_probabilidade && $risco->num_impacto) {
                $risco->calcularNivelRisco();
            }

            // Auto-incrementar código do risco
            if (!$risco->num_codigo_risco) {
                $ultimoCodigo = static::where('cod_pei', $risco->cod_pei)
                    ->max('num_codigo_risco') ?? 0;
                $risco->num_codigo_risco = $ultimoCodigo + 1;
            }
        });
    }
}
```

---

### 6.2 Model: RiscoObjetivo

**Arquivo:** `app/Models/RiscoObjetivo.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RiscoObjetivo extends Model
{
    use HasUuids;

    protected $table = 'pei.tab_risco_objetivo';
    protected $primaryKey = 'cod_risco_objetivo';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'cod_objetivo_estrategico',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    public function objetivoEstrategico()
    {
        return $this->belongsTo(ObjetivoEstrategico::class, 'cod_objetivo_estrategico', 'cod_objetivo_estrategico');
    }
}
```

---

### 6.3 Model: RiscoMitigacao

**Arquivo:** `app/Models/RiscoMitigacao.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RiscoMitigacao extends Model implements Auditable
{
    use HasFactory, HasUuids, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_risco_mitigacao';
    protected $primaryKey = 'cod_mitigacao';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'dsc_tipo',
        'txt_descricao',
        'cod_responsavel',
        'dte_prazo',
        'dsc_status',
        'vlr_custo_estimado',
    ];

    protected $casts = [
        'dte_prazo' => 'date',
        'vlr_custo_estimado' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'cod_responsavel', 'id');
    }

    // === SCOPES ===

    public function scopeAtrasados($query)
    {
        return $query->where('dte_prazo', '<', now())
                     ->where('dsc_status', '!=', 'Concluído');
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('dsc_status', $status);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('dsc_tipo', $tipo);
    }

    // === MÉTODOS AUXILIARES ===

    public function isAtrasado()
    {
        return $this->dte_prazo < now() && $this->dsc_status !== 'Concluído';
    }

    public function isConcluido()
    {
        return $this->dsc_status === 'Concluído';
    }

    public function getDiasRestantes()
    {
        if ($this->isConcluido()) {
            return 0;
        }

        return now()->diffInDays($this->dte_prazo, false);
    }

    public function getStatusBadgeClass()
    {
        return match($this->dsc_status) {
            'Concluído' => 'bg-success',
            'Em Andamento' => 'bg-primary',
            'A Fazer' => 'bg-secondary',
            default => 'bg-light'
        };
    }
}
```

---

### 6.4 Model: RiscoOcorrencia

**Arquivo:** `app/Models/RiscoOcorrencia.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class RiscoOcorrencia extends Model implements Auditable
{
    use HasFactory, HasUuids, \OwenIt\Auditing\Auditable;

    protected $table = 'pei.tab_risco_ocorrencia';
    protected $primaryKey = 'cod_ocorrencia';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'cod_risco',
        'dte_ocorrencia',
        'txt_descricao',
        'num_impacto_real',
        'txt_acoes_tomadas',
        'txt_licoes_aprendidas',
    ];

    protected $casts = [
        'dte_ocorrencia' => 'date',
        'num_impacto_real' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // === RELACIONAMENTOS ===

    public function risco()
    {
        return $this->belongsTo(Risco::class, 'cod_risco', 'cod_risco');
    }

    // === SCOPES ===

    public function scopeRecentes($query, $dias = 30)
    {
        return $query->where('dte_ocorrencia', '>=', now()->subDays($dias));
    }

    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('dte_ocorrencia', [$dataInicio, $dataFim]);
    }

    // === MÉTODOS AUXILIARES ===

    public function getImpactoRealLabel()
    {
        return match($this->num_impacto_real) {
            1 => 'Muito Baixo',
            2 => 'Baixo',
            3 => 'Médio',
            4 => 'Alto',
            5 => 'Muito Alto',
            default => 'Não avaliado'
        };
    }

    public function getImpactoRealCor()
    {
        return match($this->num_impacto_real) {
            5 => '#dc2626', // Vermelho
            4 => '#f97316', // Laranja
            3 => '#eab308', // Amarelo
            2 => '#65a30d', // Verde claro
            1 => '#22c55e', // Verde
            default => '#94a3b8' // Cinza
        };
    }

    public function isRecente($dias = 7)
    {
        return $this->dte_ocorrencia >= now()->subDays($dias);
    }
}
```

---

## 7. MATRIZ DE RELACIONAMENTOS

| Model | Relaciona com | Tipo | Método |
|-------|---------------|------|--------|
| **User** | Organization | BelongsToMany | `organizacoes()` |
| **User** | PerfilAcesso | BelongsToMany | `perfisAcesso()` |
| **User** | Acao | HasMany | `acoes()` |
| **User** | TabAudit | HasMany | `audits()` |
| **Organization** | Organization (pai) | BelongsTo | `pai()` |
| **Organization** | Organization (filhas) | HasMany | `filhas()` |
| **Organization** | User | BelongsToMany | `usuarios()` |
| **Organization** | PlanoDeAcao | HasMany | `planosAcao()` |
| **Organization** | MissaoVisaoValores | HasMany | `identidadeEstrategica()` |
| **Organization** | Valor | HasMany | `valores()` |
| **PEI** | Perspectiva | HasMany | `perspectivas()` |
| **PEI** | MissaoVisaoValores | HasMany | `identidadeEstrategica()` |
| **PEI** | Valor | HasMany | `valores()` |
| **PEI** | AtividadeCadeiaValor | HasMany | `atividadesCadeiaValor()` |
| **Perspectiva** | PEI | BelongsTo | `pei()` |
| **Perspectiva** | ObjetivoEstrategico | HasMany | `objetivos()` |
| **Perspectiva** | AtividadeCadeiaValor | HasMany | `atividades()` |
| **ObjetivoEstrategico** | Perspectiva | BelongsTo | `perspectiva()` |
| **ObjetivoEstrategico** | PlanoDeAcao | HasMany | `planosAcao()` |
| **ObjetivoEstrategico** | Indicador | HasMany | `indicadores()` |
| **ObjetivoEstrategico** | FuturoAlmejadoObjetivoEstrategico | HasMany | `futuroAlmejado()` |
| **PlanoDeAcao** | ObjetivoEstrategico | BelongsTo | `objetivoEstrategico()` |
| **PlanoDeAcao** | TipoExecucao | BelongsTo | `tipoExecucao()` |
| **PlanoDeAcao** | Organization | BelongsTo | `organizacao()` |
| **PlanoDeAcao** | Entrega | HasMany | `entregas()` |
| **PlanoDeAcao** | Indicador | HasMany | `indicadores()` |
| **Indicador** | PlanoDeAcao | BelongsTo | `planoDeAcao()` |
| **Indicador** | ObjetivoEstrategico | BelongsTo | `objetivoEstrategico()` |
| **Indicador** | EvolucaoIndicador | HasMany | `evolucoes()` |
| **Indicador** | LinhaBaseIndicador | HasMany | `linhaBase()` |
| **Indicador** | MetaPorAno | HasMany | `metasPorAno()` |
| **Indicador** | Organization | BelongsToMany | `organizacoes()` |
| **EvolucaoIndicador** | Indicador | BelongsTo | `indicador()` |
| **EvolucaoIndicador** | Arquivo | HasMany | `arquivos()` |
| **AtividadeCadeiaValor** | PEI | BelongsTo | `pei()` |
| **AtividadeCadeiaValor** | Perspectiva | BelongsTo | `perspectiva()` |
| **AtividadeCadeiaValor** | ProcessoAtividadeCadeiaValor | HasMany | `processos()` |

---

**Próximo Documento:** 05-COMPONENTES-LIVEWIRE.md
