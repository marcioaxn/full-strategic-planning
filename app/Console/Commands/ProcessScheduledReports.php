<?php

namespace App\Console\Commands;

use App\Models\Reports\RelatorioAgendado;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Reports\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;

class ProcessScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa os relatórios agendados pendentes';

    /**
     * Execute the console command.
     */
    public function handle(ReportGenerationService $reportService)
    {
        $this->info('Iniciando processamento de relatórios agendados...');

        /*
         * 🔴 PULSO DO AGENDADOR.
         *
         * O agendamento de relatórios depende de uma Tarefa Agendada do sistema
         * operacional chamando `schedule:run`. Se o cliente não configurou essa
         * tarefa — e é o caso mais comum — nada nunca dispara, em silêncio: o
         * usuário marca "enviar toda segunda", a tela confirma, e o e-mail não
         * chega nunca.
         *
         * Marcar aqui a hora de cada execução é o que permite à tela dizer a
         * verdade: com pulso recente, o agendamento é oferecido; sem pulso, ela
         * avisa que a tarefa do servidor não está rodando em vez de aceitar um
         * agendamento que morreria calado.
         */
        SystemSetting::setValue('agendamento_ultimo_processamento', now()->toIso8601String());

        $agendamentos = RelatorioAgendado::where('bln_ativo', true)
            ->where('dte_proxima_execucao', '<=', now())
            ->get();

        if ($agendamentos->isEmpty()) {
            $this->info('Nenhum agendamento pendente.');

            return;
        }

        foreach ($agendamentos as $agendamento) {
            $this->info("Processando agendamento ID: {$agendamento->cod_agendamento} (Tipo: {$agendamento->dsc_tipo_relatorio})");

            try {
                $filtros = $agendamento->txt_filtros ?? [];
                $result = null;

                // Extrair filtros comuns
                $organizacaoId = $filtros['organizacao_id'] ?? null;

                if ($motivo = $this->motivoParaNaoExecutar($agendamento, $organizacaoId)) {
                    $agendamento->bln_ativo = false;
                    $agendamento->save();

                    $this->warn("Agendamento {$agendamento->cod_agendamento} desativado: {$motivo}");
                    \Log::warning('Agendamento de relatório desativado', [
                        'cod_agendamento' => $agendamento->cod_agendamento,
                        'user_id' => $agendamento->user_id,
                        'motivo' => $motivo,
                    ]);

                    continue;
                }
                $ano = $filtros['ano'] ?? date('Y');
                $periodo = $filtros['periodo'] ?? 'anual';
                $perspectivaId = ($filtros['perspectiva'] ?? null) ?: null;
                // IA só quando quem agendou ligou "Incluir IA" (antes: ligada por padrão).
                $includeAi = filter_var($filtros['include_ai'] ?? false, FILTER_VALIDATE_BOOL);

                // 🔴 O ciclo gravado no agendamento. Sem ele (agendamentos antigos),
                // o do contexto — no cron, o ciclo vigente.
                $reportService->usarCiclo($filtros['cod_pei'] ?? null);

                switch ($agendamento->dsc_tipo_relatorio) {
                    case 'integrado':
                        $result = $reportService->generateIntegrado($organizacaoId, $ano, $periodo, $includeAi);
                        break;
                    case 'executivo':
                        $result = $reportService->generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId, $includeAi);
                        break;
                    case 'identidade':
                        if (! $organizacaoId) {
                            throw new \Exception('Organização obrigatória para este relatório.');
                        }
                        // O ano escolhido: sem ele, o Mapa saía sempre no ano corrente.
                        $result = $reportService->generateIdentidade($organizacaoId, $ano);
                        break;
                    case 'objetivos':
                        $result = $reportService->generateObjetivos($organizacaoId, $ano, $perspectivaId);
                        break;
                    case 'indicadores':
                        $result = $reportService->generateIndicadores($organizacaoId, $ano, $periodo);
                        break;
                    case 'planos':
                        $result = $reportService->generatePlanos($organizacaoId, $ano);
                        break;
                    case 'riscos':
                        $result = $reportService->generateRiscos($organizacaoId);
                        break;
                    default:
                        // Desativa: reprocessar um tipo que nunca vai existir só
                        // enche o log de hora em hora.
                        $agendamento->bln_ativo = false;
                        $agendamento->save();
                        $this->error("Tipo de relatório desconhecido: {$agendamento->dsc_tipo_relatorio}. Agendamento desativado.");

                        continue 2; // Pula para o próximo agendamento
                }

                if ($result) {
                    // Gravar o arquivo e registrar a geração é uma coisa só, e
                    // vive no modelo: a tela e o agendador gravam igual, no
                    // mesmo disco privado, com a mesma convenção de caminho.
                    $registro = RelatorioGerado::registrar(
                        $result,
                        $agendamento->dsc_tipo_relatorio,
                        $agendamento->user_id,
                        $filtros,
                    );

                    $path = $registro->dsc_caminho_arquivo;

                    $this->info("Relatório gerado com sucesso: $path");

                    // Atualizar Próxima Execução
                    $this->atualizarProximaExecucao($agendamento);
                }

            } catch (\Throwable $e) {
                // \Throwable: um TypeError derrubava o laço inteiro e os demais
                // agendamentos da hora não rodavam.
                $this->error("Erro ao processar agendamento {$agendamento->cod_agendamento}: ".$e->getMessage());
                \Log::error('Erro Report Scheduler: '.$e->getMessage(), ['cod_agendamento' => $agendamento->cod_agendamento]);

                // Empurra para a próxima ocorrência da frequência: sem isto, o
                // agendamento com erro era reprocessado (e logado) de hora em hora.
                try {
                    $this->atualizarProximaExecucao($agendamento);
                } catch (\Throwable $e2) {
                    report($e2);
                }
            } finally {
                $reportService->usarCiclo(null);
            }
        }

        $this->info('Processamento concluído.');
    }

    /**
     * O agendamento roda sem ninguém logado, semanas depois de criado. O acesso
     * é reconferido AGORA, com o usuário como está hoje.
     *
     * 🔴 Só se checava na criação: quem era desativado ou saía da unidade
     * continuava recebendo — e baixando — o relatório dela.
     */
    private function motivoParaNaoExecutar(RelatorioAgendado $agendamento, ?string $organizacaoId): ?string
    {
        $usuario = User::find($agendamento->user_id);

        if (! $usuario) {
            return 'usuário não existe mais';
        }

        if (! $usuario->isAtivo()) {
            return 'usuário inativo';
        }

        if (! $usuario->temPerfilDeAcesso()) {
            return 'usuário sem perfil de acesso';
        }

        // Ciclo gravado e depois excluído: gerar de outro ciclo seria mentir.
        $codPei = $agendamento->txt_filtros['cod_pei'] ?? null;
        if ($codPei && ! PEI::find($codPei)) {
            return 'o ciclo do agendamento não existe mais';
        }

        if ($organizacaoId) {
            $pode = $usuario->podeAcessarOrganizacao($organizacaoId)
                && Gate::forUser($usuario)->allows('modulo.exportar', ['relatorios', $organizacaoId]);

            return $pode ? null : 'usuário sem acesso à organização do relatório';
        }

        // Sem organização = todas as unidades: só o Super Admin.
        return $usuario->isSuperAdmin() ? null : 'relatório de todas as unidades exige Super Administrador';
    }

    private function atualizarProximaExecucao(RelatorioAgendado $agendamento)
    {
        $proxima = Carbon::parse($agendamento->dte_proxima_execucao);

        switch ($agendamento->dsc_frequencia) {
            case 'diario':
                $proxima->addDay();
                break;
            case 'semanal':
                $proxima->addWeek();
                break;
            case 'mensal':
                $proxima->addMonth();
                break;
            default:
                // Se frequência desconhecida, desativa para evitar loop
                $agendamento->bln_ativo = false;
                break;
        }

        $agendamento->dte_proxima_execucao = $proxima;
        $agendamento->save();
    }
}
