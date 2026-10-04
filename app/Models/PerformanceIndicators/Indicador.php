<?php

namespace App\Models\PerformanceIndicators;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Services\IndicadorCalculoService;
use App\Support\CalculoPolaridade;
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
    protected $table = 'performance_indicators.tab_indicador';

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
     * Tipos de Cálculo disponíveis
     */
    const CALCULATION_TYPES = [
        'manual' => 'Medição Manual',
        'action_plan' => 'Baseado em Iniciativa',
    ];

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
        'dsc_calculation_type',
        'json_smart',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'num_peso' => 'integer',
        'json_smart' => 'array',
    ];

    public function isSmartValido(): bool
    {
        $smart = $this->json_smart ?? [];

        return count(array_filter($smart)) === 5;
    }

    /**
     * Relacionamento: Iniciativa (opcional)
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
     * Iniciativas vinculadas via pivô (ROAD-005)
     */
    public function planosDeAcaoVinculados(): BelongsToMany
    {
        return $this->belongsToMany(
            PlanoDeAcao::class,
            'performance_indicators.rel_indicador_plano_de_acao',
            'cod_indicador',
            'cod_plano_de_acao',
            'cod_indicador',
            'cod_plano_de_acao'
        )->withPivot('txt_justificativa', 'created_at');
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
     * @param  int|null  $ano  Ano para cálculo (padrão: ano atual)
     * @param  int|null  $mes  Mês limite para cálculo (padrão: mês atual ou 12 se ano passado)
     * @return float Percentual de atingimento (0-100+)
     */
    public function calcularAtingimento(?int $ano = null, ?int $mes = null): float
    {
        // "Sem medição" continua valendo 0 para quem soma e tira média (as
        // telas que precisam distinguir usam atingimentoMedido()).
        return $this->atingimentoMedido($ano, $mes) ?? 0.0;
    }

    /**
     * O atingimento, ou NULL quando não há o que medir no período.
     *
     * 🔴 Realizado em branco era gravado como 0 — e 0 em polaridade negativa
     * dá 100%: o mês que ninguém mediu acendia verde. Agora o branco é NULL e
     * o mês sem Realizado não conta; sem nenhum mês medido (ou sem previsto
     * nem meta para comparar), a resposta é "sem medição", não 0% nem 100%.
     */
    public function atingimentoMedido(?int $ano = null, ?int $mes = null): ?float
    {
        // Se for cálculo automático baseado em iniciativa, usar o service
        if ($this->dsc_calculation_type === 'action_plan' && $this->cod_plano_de_acao) {
            $service = app(IndicadorCalculoService::class);

            return $service->calcularProgressoPlano($this->planoDeAcao);
        }

        // Pega o ano da sessão se não for passado
        $ano = (int) ($ano ?? session('ano_selecionado', now()->year));

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

        // Só os meses MEDIDOS do ano até o mês especificado: mês com Realizado
        // em branco (NULL) não é "zero realizado".
        $evolucoes = $this->evolucoes()
            ->where('num_ano', $ano)
            ->where('num_mes', '<=', $mes)
            ->whereNotNull('vlr_realizado')
            ->orderBy('num_mes')
            ->get();

        if ($evolucoes->isEmpty()) {
            return null;
        }

        if ($this->bln_acumulado === 'Sim') {
            // Indicador ACUMULADO: soma todos os valores do período
            $totalPrevisto = (float) $evolucoes->sum('vlr_previsto');
            $totalRealizado = (float) $evolucoes->sum('vlr_realizado');
        } else {
            // Indicador NÃO ACUMULADO: usa o último valor medido
            $ultimaEvolucao = $evolucoes->last();
            $totalPrevisto = (float) ($ultimaEvolucao->vlr_previsto ?? 0);
            $totalRealizado = (float) $ultimaEvolucao->vlr_realizado;
        }

        // Sem previsto (em branco, ou 0 dos lançamentos antigos que gravavam o
        // branco como zero): compara com a meta anual.
        if ($totalPrevisto == 0) {
            $totalPrevisto = $this->previstoPelaMeta($ano, $evolucoes->count());
        }

        if (! $totalPrevisto) {
            return null;
        }

        // Calcular percentual baseado no tipo de polaridade
        return $this->calcularPercentualPorTipo($totalRealizado, $totalPrevisto);
    }

    /**
     * O previsto que a meta anual implica, quando o lançamento não o traz.
     *
     * - ACUMULADO: a meta é a soma do ano — proporcional aos meses medidos
     *   (meta/12 × meses).
     * - NÃO ACUMULADO: o valor é pontual e a meta anual é o próprio alvo.
     *   Dividir por 12 (como era) fazia 85 contra meta 90 virar 1.133%.
     */
    public function previstoPelaMeta(int $ano, int $mesesMedidos = 1): ?float
    {
        $meta = $this->metasPorAno()->where('num_ano', $ano)->first();

        if (! $meta || $meta->meta === null || (float) $meta->meta == 0) {
            return null;
        }

        return $this->bln_acumulado === 'Sim'
            ? ((float) $meta->meta / 12) * $mesesMedidos
            : (float) $meta->meta;
    }

    /**
     * O ciclo PEI do indicador: pelo objetivo direto ou, no indicador de
     * iniciativa, pelo objetivo da iniciativa. Sem o segundo caminho o
     * indicador de iniciativa ficava sempre sem régua (farol cinza).
     */
    public function codPeiDoCiclo(): ?string
    {
        return $this->objetivo?->perspectiva?->cod_pei
            ?? $this->planoDeAcao?->objetivo?->perspectiva?->cod_pei;
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
     * @param  float  $realizado  Valor realizado
     * @param  float  $previsto  Valor previsto
     * @return float Percentual calculado
     */
    protected function calcularPercentualPorTipo(float $realizado, float $previsto): float
    {
        /*
         * A conta vive em App\Support\CalculoPolaridade — num lugar só.
         *
         * Estava duplicada aqui e em EvolucaoIndicador, com os MESMOS três
         * erros nas duas cópias:
         *
         *  1. Meta ZERO devolvia 0% em qualquer polaridade. Em polaridade
         *     negativa, meta zero é a mais ambiciosa que existe — zero
         *     acidentes, zero fraudes. Quem alcançava zero recebia 0% de
         *     atingimento: o número dizia fracasso total onde houve êxito.
         *  2. Estabilidade usava a fórmula positiva: estourar o alvo em 50%
         *     marcava 150%, premiando o desvio que a polaridade existe para
         *     evitar.
         *  3. O rótulo longo ("Negativa (Quanto menor, melhor)") caía no
         *     default e era calculado como POSITIVA, sem erro e sem aviso.
         */
        return CalculoPolaridade::atingimento($realizado, $previsto, $this->dsc_polaridade);
    }

    /**
     * Obter cor do farol de desempenho
     */
    public function getCorFarol(?int $ano = null): string
    {
        // O mesmo ano do atingimento: sem isto o percentual era do ano de
        // referência e a régua de qualquer ano (a de ano específico vencia).
        $ano = (int) ($ano ?? session('ano_selecionado', now()->year));

        $percentual = $this->atingimentoMedido($ano);

        // Sem medição ou informativo: não há o que julgar — cinza, nunca a cor
        // da pior faixa.
        if ($percentual === null || CalculoPolaridade::ehInformativo($this->dsc_polaridade)) {
            return GrauSatisfacao::COR_SEM_REGUA;
        }

        // A régua é a do ciclo a que este indicador pertence, via
        // objetivo → perspectiva → PEI. A consulta anterior não filtrava por
        // ciclo: com dois PEIs de faixas diferentes, o farol acendia com o
        // critério do ciclo errado. Verde, e mentira.
        //
        // 🔴 Usa corDe(), não faixaDe(): um indicador a 125% da meta não casa
        // com faixa nenhuma (a última costuma terminar em 100) e devolvia
        // null — que cada tela traduzia num cinza ou num vermelho diferente.
        // corDe() trata as pontas e devolve o cinza neutro quando não há régua
        // configurada, que é a única resposta honesta nesse caso.
        return GrauSatisfacao::corDe((float) $percentual, $this->codPeiDoCiclo(), $ano);
    }

    public function tendenciaAtual(int $meses = 3): array
    {
        return app(IndicadorCalculoService::class)
            ->calcularTendencia($this->cod_indicador, $meses);
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
     * Scope: Indicadores de iniciativa
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
