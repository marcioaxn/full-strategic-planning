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
     * Relacionamento: Objetivo Estratégico (opcional)
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
     * Scope: Indicadores de objetivo estratégico
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
