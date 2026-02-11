<?php

namespace App\Models\StrategicPlanning;

use App\Models\ActionPlan\Entrega;
use App\Models\PerformanceIndicators\Indicador;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Perspectiva extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tab_perspectiva';
    protected $primaryKey = 'cod_perspectiva';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dsc_perspectiva',
        'num_nivel_hierarquico_apresentacao',
        'cod_pei',
        'num_peso_indicadores',
        'num_peso_planos',
    ];

    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
        'num_peso_indicadores' => 'integer',
        'num_peso_planos' => 'integer',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Relacionamento: Objetivos
     */
    public function objetivos(): HasMany
    {
        return $this->hasMany(Objetivo::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Atividades da Cadeia de Valor
     */
    public function atividades(): HasMany
    {
        return $this->hasMany(AtividadeCadeiaValor::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Obtém todos os indicadores vinculados direta ou indiretamente a esta perspectiva.
     * (Via Objetivos Estratégicos)
     */
    public function getIndicadoresAttribute(): Collection
    {
        // Indicadores vinculados aos objetivos desta perspectiva
        return Indicador::whereIn('cod_objetivo', $this->objetivos->pluck('cod_objetivo'))
            ->get();
    }

    /**
     * Calcula o desempenho global da perspectiva no ano, considerando os pesos configurados.
     * 
     * @param int|null $ano Ano de referência (default: ano atual)
     * @return float Desempenho de 0 a 100
     */
    public function calcularDesempenho(?int $ano = null): float
    {
        $ano = $ano ?? date('Y');

        // 1. Calcular Desempenho dos Indicadores (Lagging)
        $desempenhoIndicadores = $this->calcularDesempenhoIndicadores($ano);

        // 2. Calcular Desempenho dos Planos de Ação (Leading)
        $desempenhoPlanos = $this->calcularDesempenhoPlanos($ano);

        // 3. Aplicar Ponderação
        $pesoInd = $this->num_peso_indicadores ?? 100;
        $pesoPlanos = $this->num_peso_planos ?? 0;
        
        // Garantir que soma 100
        $totalPesos = $pesoInd + $pesoPlanos;
        if ($totalPesos == 0) return 0;

        return (($desempenhoIndicadores * $pesoInd) + ($desempenhoPlanos * $pesoPlanos)) / $totalPesos;
    }

    /**
     * Calcula a média de atingimento dos indicadores da perspectiva.
     */
    protected function calcularDesempenhoIndicadores(int $ano): float
    {
        $indicadores = $this->indicadores;

        if ($indicadores->isEmpty()) {
            return 0;
        }

        $somaAtingimento = 0;
        $count = 0;

        foreach ($indicadores as $ind) {
            // Calcula atingimento anual do indicador
            $somaAtingimento += $ind->calcularAtingimento($ano);
            $count++;
        }

        return $count > 0 ? ($somaAtingimento / $count) : 0;
    }

    /**
     * Calcula o progresso dos planos de ação vinculados, considerando apenas entregas DO ANO.
     */
    protected function calcularDesempenhoPlanos(int $ano): float
    {
        // Buscar Planos vinculados aos Objetivos desta Perspectiva
        $objetivosIds = $this->objetivos->pluck('cod_objetivo');
        
        // Buscar entregas que pertencem a planos desses objetivos E que ocorrem no ano
        // Regra: Entregas com data_fim_prevista dentro do ano
        $entregasNoAno = Entrega::whereHas('planoDeAcao', function($q) use ($objetivosIds) {
                $q->whereIn('cod_objetivo', $objetivosIds);
            })
            ->whereYear('dte_prazo', $ano) // Filtro Temporal Crucial
            ->where('bln_arquivado', false)
            ->whereNull('deleted_at')
            ->get();

        if ($entregasNoAno->isEmpty()) {
            return 0;
        }

        // Calcular progresso ponderado dessas entregas
        // Reutilizando lógica do Service de Indicadores, mas aplicado ao conjunto
        $somaProgresso = 0;
        $somaPesos = 0;

        foreach ($entregasNoAno as $entrega) {
            // Se entrega cancelada, ignora
            if ($entrega->bln_status === 'Cancelado') continue;

            $statusDecimal = match($entrega->bln_status) {
                'Concluído' => 1.0,
                'Em Andamento' => 0.5,
                'Suspenso' => 0.25,
                default => 0.0,
            };

            $peso = $entrega->num_peso > 0 ? $entrega->num_peso : 1; // Se peso 0, assume 1 para média simples
            
            $somaProgresso += ($peso * $statusDecimal);
            $somaPesos += $peso;
        }

        if ($somaPesos == 0) return 0;

        return ($somaProgresso / $somaPesos) * 100;
    }

    /**
     * Scope: Ordenar por nível hierárquico
     */
    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('num_nivel_hierarquico_apresentacao');
    }
}