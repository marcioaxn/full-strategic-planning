<?php

namespace App\Console\Commands;

use App\Models\Reports\RelatorioAgendado;
use App\Models\Reports\RelatorioGerado;
use App\Models\SystemSetting;
use App\Services\Reports\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                $ano = $filtros['ano'] ?? date('Y');
                $periodo = $filtros['periodo'] ?? 'anual';
                $perspectivaId = $filtros['perspectiva'] ?? null;
                $includeAi = $filtros['include_ai'] ?? true; // Padrão true se não existir

                switch ($agendamento->dsc_tipo_relatorio) {
                    case 'integrado':
                        $result = $reportService->generateIntegrado($organizacaoId, $ano, $periodo, $includeAi);
                        break;
                    case 'executivo':
                        $result = $reportService->generateExecutivo($organizacaoId, $ano, $periodo, $perspectivaId);
                        break;
                    case 'identidade':
                        if (! $organizacaoId) {
                            throw new \Exception('Organização obrigatória para este relatório.');
                        }
                        $result = $reportService->generateIdentidade($organizacaoId);
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
                        $this->error("Tipo de relatório desconhecido: {$agendamento->dsc_tipo_relatorio}");

                        continue 2; // Pula para o próximo agendamento
                }

                if ($result) {
                    // Salvar Arquivo
                    $directory = 'relatorios/'.date('Y/m');
                    if (! Storage::disk('relatorios')->exists($directory)) {
                        Storage::disk('relatorios')->makeDirectory($directory);
                    }

                    $filename = Str::slug(pathinfo($result['filename'], PATHINFO_FILENAME)).'_'.uniqid().'.pdf';
                    $path = $directory.'/'.$filename;

                    Storage::disk('relatorios')->put($path, $result['content']);

                    // Registrar Histórico
                    RelatorioGerado::create([
                        'user_id' => $agendamento->user_id,
                        'dsc_tipo_relatorio' => $agendamento->dsc_tipo_relatorio,
                        'dsc_caminho_arquivo' => $path,
                        'dsc_formato' => 'pdf',
                        'txt_filtros_aplicados' => $filtros,
                        'num_tamanho_bytes' => strlen($result['content']),
                    ]);

                    $this->info("Relatório gerado com sucesso: $path");

                    // Atualizar Próxima Execução
                    $this->atualizarProximaExecucao($agendamento);
                }

            } catch (\Exception $e) {
                $this->error("Erro ao processar agendamento {$agendamento->cod_agendamento}: ".$e->getMessage());
                \Log::error('Erro Report Scheduler: '.$e->getMessage());
            }
        }

        $this->info('Processamento concluído.');
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
