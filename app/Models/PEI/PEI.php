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
    protected $table = 'tab_pei';

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
