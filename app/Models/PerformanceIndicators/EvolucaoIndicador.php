<?php

namespace App\Models\PerformanceIndicators;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvolucaoIndicador extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_evolucao_indicador';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_evolucao_indicador';

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
        'cod_indicador',
        'num_ano',
        'num_mes',
        'vlr_previsto',
        'vlr_realizado',
        'txt_avaliacao',
        'bln_atualizado',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_ano' => 'integer',
        'num_mes' => 'integer',
        'vlr_previsto' => 'decimal:2',
        'vlr_realizado' => 'decimal:2',
    ];

    /**
     * Relacionamento: Indicador
     */
    public function indicador(): BelongsTo
    {
        return $this->belongsTo(Indicador::class, 'cod_indicador', 'cod_indicador');
    }

    /**
     * Relacionamento: Arquivos anexados
     */
    public function arquivos(): HasMany
    {
        return $this->hasMany(Arquivo::class, 'cod_evolucao_indicador', 'cod_evolucao_indicador');
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Calcular percentual de atingimento considerando a polaridade
     */
    public function calcularAtingimento(): float
    {
        if (!$this->vlr_previsto || $this->vlr_previsto == 0) {
            return 0;
        }

        $polaridade = $this->indicador->dsc_polaridade ?? 'Positiva';

        return match ($polaridade) {
            'Negativa' => $this->vlr_realizado > 0 ? ($this->vlr_previsto / $this->vlr_realizado) * 100 : 100,
            'Não Aplicável' => 0,
            'Positiva', 'Estabilidade' => ($this->vlr_realizado / $this->vlr_previsto) * 100,
            default => ($this->vlr_realizado / $this->vlr_previsto) * 100,
        };
    }

    /**
     * Obter nome do mês
     */
    public function getNomeMes(): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
            4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
            10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $meses[$this->num_mes] ?? '';
    }

    /**
     * Scopes
     */

    /**
     * Scope: Por ano
     */
    public function scopePorAno($query, int $ano)
    {
        return $query->where('num_ano', $ano);
    }

    /**
     * Scope: Por período (ano e mês)
     */
    public function scopePorPeriodo($query, int $ano, int $mes)
    {
        return $query->where('num_ano', $ano)->where('num_mes', $mes);
    }

    /**
     * Scope: Atualizadas
     */
    public function scopeAtualizadas($query)
    {
        return $query->where('bln_atualizado', 'Sim');
    }

    /**
     * Scope: Não atualizadas
     */
    public function scopeNaoAtualizadas($query)
    {
        return $query->where('bln_atualizado', '!=', 'Sim');
    }
}
