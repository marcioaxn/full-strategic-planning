<?php

namespace App\Models\PerformanceIndicators;

use App\Models\Organization;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\GrauSatisfacao;
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
    protected $table = 'tab_indicador';

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
     * Constantes de Mercado para Unidades de Medida
     */
    const UNIDADES_MEDIDA = [
        'Percentual (%)',
        'Monetário (R$)',
        'Índice (0-1)',
        'Quantidade (un)',
        'Horas (h)',
        'Dias',
        'Proporção',
        'Taxa',
        'Nº de Ocorrências',
        'Kilômetros (km)',
        'Metros (m)',
        'Toneladas (t)',
        'Pontos',
    ];

    /**
     * Opções de Polaridade
     */
    const POLARIDADES = [
        'Positiva' => 'Positiva (Quanto maior, melhor)',
        'Negativa' => 'Negativa (Quanto menor, melhor)',
        'Estabilidade' => 'Estabilidade (Quanto mais próximo do alvo, melhor)',
        'Não Aplicável' => 'Não Aplicável (Informativo)',
    ];

    /**
     * Atributos mass assignable
     */
    protected $fillable = [
        'cod_plano_de_acao',
        'cod_objetivo',
        'dsc_tipo',
        'nom_indicador',
        'dsc_indicador',
        'txt_observacao',
        'dsc_meta',
        'dsc_atributos',
        'dsc_referencial_comparativo',
        'dsc_unidade_medida',
        'dsc_polaridade',
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
    public function objetivo(): BelongsTo
    {
        return $this->belongsTo(Objetivo::class, 'cod_objetivo', 'cod_objetivo');
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
            'performance_indicators.rel_indicador_objetivo_organizacao',
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
     * Calcular percentual de atingimento baseado nas evoluções do ano.
     *
     * Lógica:
     * - Para indicadores ACUMULADOS (bln_acumulado = 'Sim'): soma todos os valores do período
     * - Para indicadores NÃO ACUMULADOS: usa o último valor disponível
     *
     * Tipos de polaridade (dsc_tipo):
     * - '+' (quanto maior, melhor): (realizado / previsto) × 100
     * - '-' (quanto menor, melhor): (previsto / realizado) × 100 (invertido)
     * - '=' (manter estável): mesmo cálculo do '+'
     *
     * @param int|null $ano Ano para cálculo (padrão: ano atual)
     * @param int|null $mes Mês limite para cálculo (padrão: mês atual ou 12 se ano passado)
     * @return float Percentual de atingimento (0-100+)
     */
    public function calcularAtingimento(int $ano = null, int $mes = null): float
    {
        // Pega o ano da sessão se não for passado
        $ano = $ano ?? session('ano_selecionado', now()->year);
        
        // Determina o mês limite de forma inteligente
        if ($mes === null) {
            $anoAtual = now()->year;
            if ($ano < $anoAtual) {
                // Para anos que já passaram, considera o ano cheio (até Dezembro)
                $mes = 12;
            } elseif ($ano == $anoAtual) {
                // Para o ano atual, considera o acumulado até o mês vigente (YTD)
                $mes = now()->month;
            } else {
                // Para anos futuros, pode considerar 0 ou o primeiro mês se houver dados
                $mes = 1;
            }
        }

        // Buscar evoluções do ano até o mês especificado
        $evolucoes = $this->evolucoes()
            ->where('num_ano', $ano)
            ->where('num_mes', '<=', $mes)
            ->orderBy('num_mes')
            ->get();

        if ($evolucoes->isEmpty()) {
            return 0;
        }

        // Calcular valores baseado no tipo de acumulação
        $totalPrevisto = 0;
        $totalRealizado = 0;

        if ($this->bln_acumulado === 'Sim') {
            // Indicador ACUMULADO: soma todos os valores do período
            $totalPrevisto = $evolucoes->sum('vlr_previsto');
            $totalRealizado = $evolucoes->sum('vlr_realizado');
        } else {
            // Indicador NÃO ACUMULADO: usa o último valor disponível
            $ultimaEvolucao = $evolucoes->last();
            if ($ultimaEvolucao) {
                $totalPrevisto = $ultimaEvolucao->vlr_previsto ?? 0;
                $totalRealizado = $ultimaEvolucao->vlr_realizado ?? 0;
            }
        }

        // Se não houver valor previsto, tentar usar meta anual como fallback
        if ($totalPrevisto == 0) {
            $meta = $this->metasPorAno()->where('num_ano', $ano)->first();
            if (!$meta || $meta->meta == 0) {
                return 0;
            }

            if ($this->bln_acumulado === 'Sim') {
                // Meta anual proporcional aos meses com evolução
                $mesesComEvolucao = $evolucoes->count();
                $totalPrevisto = ($meta->meta / 12) * $mesesComEvolucao;
            } else {
                // Meta mensal (meta anual / 12)
                $totalPrevisto = $meta->meta / 12;
            }
        }

        if ($totalPrevisto == 0) {
            return 0;
        }

        // Calcular percentual baseado no tipo de polaridade
        return $this->calcularPercentualPorTipo($totalRealizado, $totalPrevisto);
    }

    /**
     * Calcular percentual baseado no tipo de polaridade do indicador.
     *
     * NOTA: Atualmente todos os indicadores usam polaridade '+' (quanto maior, melhor).
     * O campo dsc_tipo armazena a CATEGORIA do indicador (Efetividade, Eficiência, etc.),
     * não a polaridade. Para implementar polaridade diferente, seria necessário:
     * 1. Adicionar campo dsc_polaridade (+, -, =) na tabela
     * 2. Ajustar o match abaixo para usar esse campo
     *
     * Tipos de polaridade suportados (para implementação futura):
     * - '+' (quanto maior, melhor): (realizado / previsto) × 100
     * - '-' (quanto menor, melhor): (previsto / realizado) × 100 (invertido)
     * - '=' (manter estável): mesmo cálculo do '+'
     *
     * @param float $realizado Valor realizado
     * @param float $previsto Valor previsto
     * @return float Percentual calculado
     */
    protected function calcularPercentualPorTipo(float $realizado, float $previsto): float
    {
        if ($previsto == 0) {
            return 0;
        }

        $polaridade = $this->dsc_polaridade ?? 'Positiva';

        return match ($polaridade) {
            'Negativa' => $realizado > 0 ? ($previsto / $realizado) * 100 : 100,
            'Não Aplicável' => 0,
            'Positiva', 'Estabilidade' => ($realizado / $previsto) * 100,
            default => ($realizado / $previsto) * 100,
        };
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
        return $query->whereNotNull('cod_objetivo');
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