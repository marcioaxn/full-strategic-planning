<?php

namespace App\Models\StrategicPlanning;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\PerformanceIndicators\Indicador;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Objetivo extends Model implements Auditable
{
    use HasFactory, HasUuids, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'tab_objetivo';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_objetivo';

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
        'nom_objetivo',
        'dsc_objetivo',
        'num_nivel_hierarquico_apresentacao',
        'cod_perspectiva',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_nivel_hierarquico_apresentacao' => 'integer',
    ];

    /**
     * Relacionamento: Perspectiva BSC
     */
    public function perspectiva(): BelongsTo
    {
        return $this->belongsTo(Perspectiva::class, 'cod_perspectiva', 'cod_perspectiva');
    }

    /**
     * Relacionamento: Planos de Ação
     */
    public function planosAcao(): HasMany
    {
        return $this->hasMany(PlanoDeAcao::class, 'cod_objetivo', 'cod_objetivo');
    }

    /**
     * Relacionamento: Indicadores
     */
    public function indicadores(): HasMany
    {
        return $this->hasMany(Indicador::class, 'cod_objetivo', 'cod_objetivo');
    }

    /**
     * Relacionamento: Futuro Almejado
     */
    public function futuroAlmejado(): HasMany
    {
        return $this->hasMany(FuturoAlmejado::class, 'cod_objetivo', 'cod_objetivo');
    }

    /**
     * Relacionamento: Comentários
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(ObjetivoComentario::class, 'cod_objetivo', 'cod_objetivo');
    }

    /**
     * Métodos de Cálculo Agregado
     */

    /**
     * Calcular percentual de atingimento consolidado do objetivo.
     *
     * Considera todos os indicadores vinculados ao objetivo (diretos e via planos de ação),
     * calculando a média ponderada pelo peso de cada indicador.
     *
     * @param int|null $ano Ano para cálculo (padrão: ano atual)
     * @param int|null $mes Mês limite para cálculo
     * @return float Percentual de atingimento consolidado (0-100+)
     */
    public function calcularAtingimentoConsolidado(int $ano = null, int $mes = null): float
    {
        $ano = $ano ?? session('ano_selecionado', now()->year);

        // Determina o mês limite automaticamente se não for passado
        if ($mes === null) {
            $anoAtual = now()->year;
            if ($ano < $anoAtual) {
                $mes = 12;
            } elseif ($ano == $anoAtual) {
                $mes = now()->month;
            } else {
                $mes = 1;
            }
        }

        // Buscar indicadores diretos do objetivo
        $indicadoresDiretos = $this->indicadores()->with(['evolucoes', 'metasPorAno'])->get();

        // Buscar indicadores dos planos de ação vinculados ao objetivo
        $indicadoresPlanos = Indicador::whereHas('planoDeAcao', function ($query) {
            $query->where('cod_objetivo', $this->cod_objetivo);
        })->with(['evolucoes', 'metasPorAno'])->get();

        // Combinar todos os indicadores (removendo duplicatas)
        $todosIndicadores = $indicadoresDiretos->merge($indicadoresPlanos)->unique('cod_indicador');

        if ($todosIndicadores->isEmpty()) {
            return 0;
        }

        // Calcular média ponderada pelo peso
        $somaPesos = 0;
        $somaAtingimentoPonderado = 0;

        foreach ($todosIndicadores as $indicador) {
            // Ignora indicadores informativos no cálculo da média consolidada
            if ($indicador->dsc_polaridade === 'Não Aplicável') {
                continue;
            }

            $peso = $indicador->num_peso ?? 1;
            $atingimento = $indicador->calcularAtingimento($ano, $mes);

            $somaAtingimentoPonderado += $atingimento * $peso;
            $somaPesos += $peso;
        }

        if ($somaPesos == 0) {
            return 0;
        }

        return $somaAtingimentoPonderado / $somaPesos;
    }

    /**
     * Obter cor do farol consolidado do objetivo.
     *
     * @param int|null $ano Ano para cálculo
     * @param int|null $mes Mês limite para cálculo
     * @return string|null Cor hexadecimal do grau de satisfação
     */
    public function getCorFarolConsolidado(int $ano = null, int $mes = null): ?string
    {
        $percentual = $this->calcularAtingimentoConsolidado($ano, $mes);

        $grau = GrauSatisfacao::where('vlr_minimo', '<=', $percentual)
            ->where('vlr_maximo', '>=', $percentual)
            ->first();

        return $grau->cor ?? null;
    }

    /**
     * Obter resumo de desempenho do objetivo.
     *
     * @param int|null $ano Ano para cálculo
     * @return array Resumo com totais e percentuais
     */
    public function getResumoDesempenho(int $ano = null): array
    {
        $ano = $ano ?? session('ano_selecionado', now()->year);

        // Indicadores diretos
        $indicadoresDiretos = $this->indicadores()->with(['evolucoes', 'metasPorAno'])->get();

        // Indicadores via planos
        $indicadoresPlanos = Indicador::whereHas('planoDeAcao', function ($query) {
            $query->where('cod_objetivo', $this->cod_objetivo);
        })->with(['evolucoes', 'metasPorAno'])->get();

        $todosIndicadores = $indicadoresDiretos->merge($indicadoresPlanos)->unique('cod_indicador');

        // Planos de ação
        $planos = $this->planosAcao()->whereYear('dte_inicio', '<=', $ano)
            ->whereYear('dte_fim', '>=', $ano)
            ->get();

        // Contar status dos planos
        $planosConcluidos = $planos->where('bln_status', 'Concluído')->count();
        $planosEmAndamento = $planos->where('bln_status', 'Em Andamento')->count();
        $planosAtrasados = $planos->filter(fn($p) => $p->dte_fim < now() && $p->bln_status !== 'Concluído')->count();

        // Calcular atingimento
        $atingimento = $this->calcularAtingimentoConsolidado($ano);

        return [
            'ano' => $ano,
            'total_indicadores' => $todosIndicadores->count(),
            'total_planos' => $planos->count(),
            'planos_concluidos' => $planosConcluidos,
            'planos_em_andamento' => $planosEmAndamento,
            'planos_atrasados' => $planosAtrasados,
            'percentual_atingimento' => round($atingimento, 1),
            'cor_farol' => $this->getCorFarolConsolidado($ano),
        ];
    }

    /**
     * Scopes
     */

    /**
     * Scope: Ordenar por nível hierárquico
     */
    public function scopeOrdenadoPorNivel($query)
    {
        return $query->orderBy('num_nivel_hierarquico_apresentacao');
    }

    /**
     * Scope: Por perspectiva
     */
    public function scopePorPerspectiva($query, string $codPerspectiva)
    {
        return $query->where('cod_perspectiva', $codPerspectiva);
    }
}
