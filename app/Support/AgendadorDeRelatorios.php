<?php

namespace App\Support;

use App\Models\SystemSetting;
use Carbon\CarbonImmutable;

/**
 * O agendador de relatórios está mesmo rodando neste servidor?
 *
 * 🔴 POR QUE ESTA PERGUNTA PRECISA DE RESPOSTA
 *
 * O envio automático de relatórios depende de uma Tarefa Agendada do sistema
 * operacional chamando `php artisan schedule:run` a cada minuto. Quem instala
 * o produto precisa configurá-la; na prática quase ninguém configura.
 *
 * Sem ela, a tela de agendamento continuava funcionando por fora: o usuário
 * escolhia \"toda segunda-feira, para o gabinete\", clicava em salvar, recebia
 * \"Agendamento criado!\" — e nada era enviado, nunca, sem uma linha de aviso.
 * O sistema prometia um serviço que ele não tinha como prestar.
 *
 * A prova de vida é o pulso que `ProcessScheduledReports` grava a cada
 * execução. Sem pulso recente, a tela diz o que falta configurar em vez de
 * aceitar um agendamento que morreria calado.
 */
class AgendadorDeRelatorios
{
    /** Chave do pulso, gravada pelo comando a cada execução. */
    public const CHAVE_PULSO = 'agendamento_ultimo_processamento';

    /**
     * Margem de tolerância do pulso.
     *
     * O comando roda de hora em hora (bootstrap/app.php). Duas horas dariam
     * um alarme a cada reinício do servidor; 24 horas seriam frouxas demais
     * para o usuário confiar. Seis horas cobrem manutenção curta e ainda
     * denunciam um agendador parado no mesmo dia.
     */
    public const HORAS_DE_TOLERANCIA = 6;

    public static function ativo(): bool
    {
        return static::ultimoPulso() !== null
            && static::ultimoPulso()->diffInHours(CarbonImmutable::now()) <= static::HORAS_DE_TOLERANCIA;
    }

    public static function ultimoPulso(): ?CarbonImmutable
    {
        $valor = SystemSetting::getValue(static::CHAVE_PULSO);

        if (! $valor) {
            return null;
        }

        try {
            return CarbonImmutable::parse($valor);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * O que dizer ao usuário — em português de gente, não em jargão de infra.
     *
     * Três estados, e cada um pede uma frase diferente:
     *   • nunca rodou     → o serviço não foi ligado neste servidor
     *   • rodou e parou   → estava funcionando e parou, com a data
     *   • rodando         → nada a dizer
     */
    public static function aviso(): ?string
    {
        if (static::ativo()) {
            return null;
        }

        $pulso = static::ultimoPulso();

        if ($pulso === null) {
            return 'O envio automático de relatórios ainda não foi ativado neste servidor. '
                .'Enquanto a tarefa agendada não estiver em execução, um agendamento salvo aqui '
                .'não será disparado. Peça à equipe que mantém o servidor para agendar '
                .'"php artisan schedule:run" a cada minuto.';
        }

        return 'O envio automático de relatórios está parado desde '
            .$pulso->format('d/m/Y \à\s H:i').'. '
            .'Agendamentos salvos agora não serão disparados até que a tarefa do servidor volte a rodar.';
    }
}
