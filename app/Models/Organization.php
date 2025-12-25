<?php

namespace App\Models;

use App\Models\PEI\MissaoVisaoValores;
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
