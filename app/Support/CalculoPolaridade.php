<?php

namespace App\Support;

/**
 * Quanto do resultado foi atingido, segundo a POLARIDADE do indicador.
 *
 * 🔴 POR QUE ISTO EXISTE
 * A mesma conta estava escrita em DOIS lugares (Indicador e EvolucaoIndicador),
 * e os dois traziam os mesmos três erros:
 *
 *  1. META ZERO devolvia 0% para qualquer polaridade. Em polaridade NEGATIVA,
 *     meta zero é a meta mais ambiciosa que existe — zero acidente, zero
 *     fraude, zero reincidência. Quem alcançava zero acidentes recebia 0% de
 *     atingimento: o número dizia fracasso total onde houve êxito total.
 *
 *  2. ESTABILIDADE usava a fórmula da polaridade positiva. Num indicador em que
 *     o bom é ficar PRÓXIMO do alvo, estourar o alvo em 50% marcava 150% — ou
 *     seja, premiava exatamente o desvio que se quer evitar.
 *
 *  3. A conta duplicada divergia sozinha com o tempo: a tela lia de uma classe
 *     e o relatório da outra.
 *
 * Este é o número que o CEO lê. Ele não pode mentir.
 */
class CalculoPolaridade
{
    public const POSITIVA = 'Positiva';

    public const NEGATIVA = 'Negativa';

    public const ESTABILIDADE = 'Estabilidade';

    public const NAO_APLICAVEL = 'Não Aplicável';

    /**
     * Percentual de atingimento (0 ou mais; pode passar de 100).
     *
     * @param  string|null  $polaridade  Aceita a chave curta ou o rótulo longo
     */
    public static function atingimento(
        float|int|null $realizado,
        float|int|null $previsto,
        ?string $polaridade
    ): float {
        $realizado = (float) ($realizado ?? 0);
        $previsto = (float) ($previsto ?? 0);

        return match (self::normalizar($polaridade)) {
            // Informativo: não entra em média nenhuma.
            self::NAO_APLICAVEL => 0.0,

            self::NEGATIVA => self::negativa($realizado, $previsto),
            self::ESTABILIDADE => self::estabilidade($realizado, $previsto),
            default => self::positiva($realizado, $previsto),
        };
    }

    /**
     * Aceita 'Negativa' e 'Negativa (Quanto menor, melhor)'.
     *
     * O formulário grava a chave curta, mas importação de CSV e dado migrado do
     * legado trazem o rótulo longo. Comparar só com a chave curta fazia o
     * rótulo longo cair no `default` — e um indicador de polaridade NEGATIVA
     * era calculado como POSITIVA, em silêncio.
     */
    public static function normalizar(?string $polaridade): string
    {
        $texto = trim((string) $polaridade);

        if ($texto === '') {
            return self::POSITIVA;
        }

        foreach ([self::NAO_APLICAVEL, self::NEGATIVA, self::ESTABILIDADE, self::POSITIVA] as $chave) {
            if (str_starts_with($texto, $chave)) {
                return $chave;
            }
        }

        return self::POSITIVA;
    }

    public static function ehInformativo(?string $polaridade): bool
    {
        return self::normalizar($polaridade) === self::NAO_APLICAVEL;
    }

    /** Quanto maior, melhor: realizado sobre previsto. */
    private static function positiva(float $realizado, float $previsto): float
    {
        if ($previsto == 0.0) {
            // Sem meta declarada não há como medir atingimento. Zero aqui
            // significa "não mensurável", e o indicador aparece sem farol.
            return 0.0;
        }

        return ($realizado / $previsto) * 100;
    }

    /**
     * Quanto MENOR, melhor: previsto sobre realizado.
     *
     * Meta de 10 dias, realizado 8 → 125%. Realizado 20 → 50%.
     */
    private static function negativa(float $realizado, float $previsto): float
    {
        // Meta ZERO é a meta mais ambiciosa desta polaridade.
        if ($previsto == 0.0) {
            // Alcançou zero: cumpriu integralmente.
            // Não alcançou: quanto mais longe de zero, pior — e sem meta
            // positiva não há denominador, então o resultado é 0%.
            return $realizado == 0.0 ? 100.0 : 0.0;
        }

        if ($realizado <= 0.0) {
            // Zerou um indicador cuja meta era reduzir: êxito pleno.
            return 100.0;
        }

        return ($previsto / $realizado) * 100;
    }

    /**
     * Quanto mais PRÓXIMO do alvo, melhor.
     *
     * Desviar para cima é tão ruim quanto desviar para baixo: 100% menos o
     * desvio relativo, com piso em zero. Alvo 100 → realizado 100 dá 100%;
     * 150 ou 50 dão 50%.
     */
    private static function estabilidade(float $realizado, float $previsto): float
    {
        if ($previsto == 0.0) {
            return $realizado == 0.0 ? 100.0 : 0.0;
        }

        $desvio = abs($realizado - $previsto) / abs($previsto);

        return max(0.0, (1 - $desvio) * 100);
    }
}
