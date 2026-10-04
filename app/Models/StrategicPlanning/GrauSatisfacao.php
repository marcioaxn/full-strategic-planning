<?php

namespace App\Models\StrategicPlanning;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class GrauSatisfacao extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Tabela do banco de dados
     */
    protected $table = 'strategic_planning.tab_grau_satisfacao';

    /**
     * Chave primária
     */
    protected $primaryKey = 'cod_grau_satisfacao';

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
        'cod_pei',
        'num_ano',
        'dsc_grau_satisfacao',
        'cor',
        'vlr_minimo',
        'vlr_maximo',
    ];

    /**
     * Relacionamento: PEI
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(PEI::class, 'cod_pei', 'cod_pei');
    }

    /**
     * Casts
     */
    protected $casts = [
        'vlr_minimo' => 'decimal:2',
        'vlr_maximo' => 'decimal:2',
    ];

    /**
     * As faixas de UM ciclo PEI, na ordem em que se leem.
     *
     * 🔴 Use SEMPRE este escopo. Cada ciclo tem a própria régua — é normal que
     * ela aperte de um ciclo para o outro. Consultar a tabela sem filtrar por
     * `cod_pei` faz o `first()` devolver a faixa do ciclo errado, e o farol do
     * indicador acende com o critério de outro PEI. A tela renderiza normalmente:
     * fica verde, e é mentira.
     *
     * 13 dos 17 pontos que consultavam esta tabela não filtravam. Ver
     * documentacao/melhorias/06-grau-de-satisfacao-antes-do-indicador.md
     */
    public function scopeDoPei($query, ?string $codPei, ?int $ano = null)
    {
        $query->where('cod_pei', $codPei)->orderBy('vlr_minimo');

        // num_ano nulo = faixa válida para todo o ciclo. Havendo faixa
        // específica do ano, ela tem precedência sobre a geral.
        if ($ano !== null) {
            $query->where(function ($q) use ($ano) {
                $q->where('num_ano', $ano)->orWhereNull('num_ano');
            });
        }

        return $query;
    }

    /**
     * A faixa em que um percentual cai, dentro de um ciclo.
     *
     * Concentra aqui a regra que estava repetida em seis lugares, cada um com
     * uma variação sutil de comparação.
     */
    public static function faixaDe(float $percentual, ?string $codPei, ?int $ano = null): ?self
    {
        return static::reguaEfetiva($codPei, $ano)
            ->first(fn (self $f) => (float) $f->vlr_minimo <= $percentual && (float) $f->vlr_maximo >= $percentual);
    }

    /**
     * As faixas que de fato julgam um valor no ano.
     *
     * 🔴 A régua do ano SUBSTITUI a geral do ciclo, não se mistura com ela.
     * Misturadas e ordenadas por mínimo, a faixa geral (mínimo 0) vinha antes
     * e pintava 80% de "Neutro" num ano cuja régua dizia "Bom".
     * Ano sem régua própria usa só as faixas gerais (nenhuma = sem farol).
     * Sem ano informado: as gerais; ciclo sem faixa geral usa o que houver.
     *
     * @return Collection<int, self>
     */
    public static function reguaEfetiva(?string $codPei, ?int $ano): Collection
    {
        if ($codPei === null) {
            return collect();
        }

        $faixas = static::doPei($codPei)->get();

        if ($ano !== null && ($doAno = $faixas->where('num_ano', $ano))->isNotEmpty()) {
            return $doAno->values();
        }

        $gerais = $faixas->whereNull('num_ano')->values();

        // Com ano informado, a régua de OUTRO ano nunca julga: sem régua do ano
        // nem geral, não há farol (cinza). Sem ano (chamadas antigas), vale o
        // que houver no ciclo.
        if ($ano !== null || $gerais->isNotEmpty()) {
            return $gerais;
        }

        return $faixas->values();
    }

    /** Cinza neutro: "não há régua para julgar isto", não "isto está ruim". */
    public const COR_SEM_REGUA = '#6b7280';

    /**
     * A cor do farol de um percentual.
     *
     * 🔴 DUAS MENTIRAS QUE ESTA FUNÇÃO EXISTE PARA IMPEDIR
     *
     * 1. SEM RÉGUA, TUDO VERMELHO. Quem não casava com faixa nenhuma recebia
     *    `#dc3545`. Organização que ainda não configurou os graus de satisfação
     *    via a tela inteira pintada de crítico — um juízo que ela nunca emitiu.
     *    Sem régua não há farol: cinza, e a tela diz por quê.
     *
     * 2. ACIMA DA MELHOR FAIXA, VERMELHO. Um indicador a 125% da meta caía fora
     *    da última faixa (que costuma terminar em 100) e era pintado de crítico.
     *    Superar a meta virava alarme. Fora das pontas, o valor recebe a cor da
     *    faixa mais próxima: abaixo da primeira, a cor da primeira; acima da
     *    última, a cor da última.
     *
     * O gestor decide onde estão os cortes. O sistema não inventa nenhum.
     */
    public static function corDe(float $percentual, ?string $codPei, ?int $ano = null): string
    {
        if ($faixa = static::faixaDe($percentual, $codPei, $ano)) {
            return $faixa->cor;
        }

        return static::faixaMaisProxima($percentual, $codPei, $ano)?->cor ?? static::COR_SEM_REGUA;
    }

    /** O rótulo da faixa ("Crítico", "No alvo"), com a mesma regra de borda. */
    public static function rotuloDe(float $percentual, ?string $codPei, ?int $ano = null): ?string
    {
        $faixa = static::faixaDe($percentual, $codPei, $ano)
            ?? static::faixaMaisProxima($percentual, $codPei, $ano);

        return $faixa?->dsc_grau_satisfacao;
    }

    /** O ciclo tem régua configurada? Sem ela, não se pinta farol nenhum. */
    public static function temRegua(?string $codPei, ?int $ano = null): bool
    {
        return static::reguaEfetiva($codPei, $ano)->isNotEmpty();
    }

    /**
     * A faixa para um valor que não caiu dentro de nenhuma.
     *
     * Abaixo da primeira: a primeira. No buraco entre duas faixas (70,5 entre
     * 0–70 e 71–89): a de BAIXO — o sistema nunca promove um valor à cor
     * melhor por falta de corte. Acima da última: a última (superar a meta não
     * vira alarme).
     *
     * Devolve null quando não há faixa alguma — e é esse null que distingue
     * "não há régua" de "está fora da régua".
     */
    private static function faixaMaisProxima(float $percentual, ?string $codPei, ?int $ano = null): ?self
    {
        $faixas = static::reguaEfetiva($codPei, $ano);

        if ($faixas->isEmpty()) {
            return null;
        }

        if ($percentual < (float) $faixas->first()->vlr_minimo) {
            return $faixas->first();
        }

        return $faixas->filter(fn (self $f) => (float) $f->vlr_maximo < $percentual)
            ->sortBy(fn (self $f) => (float) $f->vlr_maximo)
            ->last() ?? $faixas->last();
    }

    /**
     * Métodos auxiliares
     */

    /**
     * Obter grau de satisfação por percentual seguindo as regras de maturidade:
     * 1. Busca por PEI e Ano específico.
     * 2. Fallback: Busca por PEI (geral do ciclo).
     * 3. Fallback: Busca Global (cod_pei nulo).
     */
    public static function porPercentual(float $percentual, ?string $peiId = null, ?int $ano = null): ?self
    {
        // Se não passar PEI/Ano, tenta pegar da sessão
        $peiId = $peiId ?? session('pei_selecionado_id');
        $ano = $ano ?? session('ano_selecionado');

        // Tentar Nível 1: Específico por Ano dentro do PEI (Maturidade)
        if ($peiId && $ano) {
            $grau = static::where('cod_pei', $peiId)
                ->where('num_ano', $ano)
                ->where('vlr_minimo', '<=', $percentual)
                ->where('vlr_maximo', '>=', $percentual)
                ->first();
            if ($grau) {
                return $grau;
            }
        }

        // Tentar Nível 2: Padrão do PEI (Geral do Ciclo)
        if ($peiId) {
            $grau = static::where('cod_pei', $peiId)
                ->whereNull('num_ano')
                ->where('vlr_minimo', '<=', $percentual)
                ->where('vlr_maximo', '>=', $percentual)
                ->first();
            if ($grau) {
                return $grau;
            }
        }

        // Nível 3: Fallback Global (Legado ou Padrão de Sistema)
        return static::whereNull('cod_pei')
            ->whereNull('num_ano')
            ->where('vlr_minimo', '<=', $percentual)
            ->where('vlr_maximo', '>=', $percentual)
            ->first();
    }

    /**
     * Scopes
     */

    /**
     * Scope: Ordenar por valor mínimo
     */
    public function scopeOrdenadoPorValor($query)
    {
        return $query->orderBy('vlr_minimo');
    }
}
