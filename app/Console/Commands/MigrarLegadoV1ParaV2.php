<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Migração de dados: Sistema de Planejamento Estratégico v1 (Laravel 8) → v2 (Laravel 12).
 *
 * Executa, no MESMO banco PostgreSQL, a estratégia aprovada:
 *   Fase 0 — Pré-checagem (versão >= 9.4, pgcrypto, backup, estado do banco)
 *   Preview — Mostra os volumes que serão migrados e as decisões aplicadas
 *   Fase 1 — Quarentena   (schema `pei` -> `legacy_pei`; tabelas de `public` -> `legacy_public`)
 *   Fase 2 — Construção   (Artisan migrate cria os 6 schemas e tabelas da v2)
 *   Fase 3 — Transferência (ETL: copia legacy_* -> v2, preservando UUIDs)
 *   Fase 4 — Validação    (contagens origem x destino)
 *   Fase 5 — Descarte     (opcional, após validação OK)
 *
 * ASSISTENTE INTERATIVO: as perguntas feitas ao operador são EXCLUSIVAMENTE
 * operacionais/de ambiente e SEMPRE por seleção (Sim/Não ou escolha de opção) —
 * nunca texto livre. As decisões de negócio ficam fixas (defaults seguros),
 * ajustáveis apenas por flags explícitas (--migrar-auditoria, --status-entrega-padrao).
 *
 * Premissas verificadas: UUID do legado é gerado por código (preservado 1:1);
 * v2 exige PostgreSQL >= 9.4 (jsonb / gen_random_uuid via pgcrypto).
 *
 * Ver: documentacao/migracao-legado-v1-para-v2-mapa-de-para.md
 */
class MigrarLegadoV1ParaV2 extends Command
{
    protected $signature = 'migracao:v1-para-v2
        {--dry-run : Simula e mostra os volumes; não grava nada}
        {--force : Não faz perguntas (assume as opções seguras); para automação/reprodutibilidade}
        {--pular-backup : Pula a confirmação de backup (NÃO recomendado)}
        {--descartar-legado : Ao final, remove os schemas legacy_* (irreversível)}
        {--migrar-auditoria : Inclui audits/tab_audit na migração (padrão: NÃO migra)}
        {--status-entrega-padrao= : Status para entregas legadas não reconhecidas (padrão: "Não Iniciado")}';

    protected $description = 'Migra os dados da v1 (Laravel 8) para a v2 (Laravel 12) no mesmo banco PostgreSQL, preservando UUIDs.';

    /** Versão mínima do PostgreSQL exigida pela v2 (9.4.0 => 90400). */
    private const PG_MIN = 90400;

    private const Q_PEI = 'legacy_pei';      // schema de quarentena do antigo schema "pei"
    private const Q_PUB = 'legacy_public';   // schema de quarentena das tabelas antigas de "public"

    /** Status de entrega válidos na v2 (decisão de negócio — fixos). */
    private const STATUS_ENTREGA_VALIDOS = ['Não Iniciado', 'Em Andamento', 'Concluído', 'Cancelado', 'Suspenso'];

    private array $relatorio = [];

    /**
     * Mapa De→Para (ordem topológica de FK).
     * - origem:   nome(s) candidato(s) da tabela legada (procurada em legacy_pei/legacy_public/pei)
     * - destino:  schema.tabela na v2
     * - rename:   colunas renomeadas [antigo => novo]
     * - defaults: valores para colunas NOT NULL novas, sem origem
     * - transform:nome do método transformador (linha a linha), opcional
     */
    private function mapa(): array
    {
        $mapa = [
            ['origem' => ['tab_organizacoes'], 'destino' => 'organization.tab_organizacoes'],
            ['origem' => ['rel_organizacao'], 'destino' => 'public.rel_organizacao'],
            ['origem' => ['tab_perfil_acesso'], 'destino' => 'organization.tab_perfil_acesso'],
            ['origem' => ['users'], 'destino' => 'public.users'],
            ['origem' => ['rel_users_tab_organizacoes'], 'destino' => 'organization.rel_users_tab_organizacoes'],
            ['origem' => ['rel_users_tab_organizacoes_tab_perfil_acesso'], 'destino' => 'organization.rel_users_tab_organizacoes_tab_perfil_acesso'],

            ['origem' => ['tab_pei'], 'destino' => 'strategic_planning.tab_pei'],
            ['origem' => ['tab_missao_visao_valores'], 'destino' => 'strategic_planning.tab_missao_visao_valores'],
            ['origem' => ['tab_valores', 'valores'], 'destino' => 'strategic_planning.tab_valores'],
            ['origem' => ['tab_nivel_hierarquico'], 'destino' => 'strategic_planning.tab_nivel_hierarquico'],
            ['origem' => ['tab_perspectiva'], 'destino' => 'strategic_planning.tab_perspectiva',
                'defaults' => ['num_peso_indicadores' => 100, 'num_peso_planos' => 0]],
            ['origem' => ['tab_grau_satisfcao'], 'destino' => 'strategic_planning.tab_grau_satisfacao',
                'transform' => 'transformGrauSatisfacao'],

            ['origem' => ['tab_objetivo_estrategico'], 'destino' => 'strategic_planning.tab_objetivo',
                'rename' => [
                    'cod_objetivo_estrategico' => 'cod_objetivo',
                    'nom_objetivo_estrategico' => 'nom_objetivo',
                    'dsc_objetivo_estrategico' => 'dsc_objetivo',
                ]],
            ['origem' => ['tab_futuro_almejado_objetivo_estrategico'], 'destino' => 'strategic_planning.tab_futuro_almejado_objetivo',
                'rename' => ['cod_objetivo_estrategico' => 'cod_objetivo']],

            ['origem' => ['tab_tipo_execucao'], 'destino' => 'action_plan.tab_tipo_execucao'],
            ['origem' => ['tab_plano_de_acao'], 'destino' => 'action_plan.tab_plano_de_acao',
                'rename' => [
                    'cod_objetivo_estrategico' => 'cod_objetivo',
                    'txt_principais_entregas'  => 'txt_detalhamento',
                ]],

            ['origem' => ['tab_indicador'], 'destino' => 'performance_indicators.tab_indicador',
                'rename' => ['cod_objetivo_estrategico' => 'cod_objetivo'],
                'defaults' => ['dsc_polaridade' => 'Positiva']],
            ['origem' => ['tab_meta_por_ano'], 'destino' => 'performance_indicators.tab_meta_por_ano'],
            ['origem' => ['tab_linha_base_indicador'], 'destino' => 'performance_indicators.tab_linha_base_indicador'],
            ['origem' => ['tab_evolucao_indicador'], 'destino' => 'performance_indicators.tab_evolucao_indicador'],
            ['origem' => ['tab_arquivos'], 'destino' => 'performance_indicators.tab_arquivos'],
            ['origem' => ['rel_indicador_objetivo_estrategico_organizacao'], 'destino' => 'performance_indicators.rel_indicador_objetivo_organizacao'],

            ['origem' => ['tab_atividade_cadeia_valor'], 'destino' => 'strategic_planning.tab_atividade_cadeia_valor',
                'defaults' => ['dsc_tipo' => 'Finalística', 'num_ordem' => 0]],
            ['origem' => ['tab_processos_atividade_cadeia_valor'], 'destino' => 'strategic_planning.tab_processos_atividade_cadeia_valor'],

            ['origem' => ['tab_entregas'], 'destino' => 'action_plan.tab_entregas',
                'transform' => 'transformEntrega'],

            ['origem' => ['tab_status'], 'destino' => 'public.tab_status'],
            ['origem' => ['acoes'], 'destino' => 'action_plan.acoes'],
        ];

        // Decisão de negócio (flag): auditoria só entra se explicitamente solicitada.
        if ($this->option('migrar-auditoria')) {
            $mapa[] = ['origem' => ['audits'], 'destino' => 'public.audits'];
            $mapa[] = ['origem' => ['tab_audit'], 'destino' => 'public.tab_audit'];
        }

        return $mapa;
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║   MIGRAÇÃO v1 → v2  ·  Sistema de Planejamento Estratégico      ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        if ($dry) {
            $this->warn('MODO DRY-RUN: nenhuma alteração será gravada. Apenas pré-visualização.');
        }

        // Validação da flag de negócio (evita "problema de comunicação": valor inválido aborta cedo).
        if (! in_array($this->statusPadrao(), self::STATUS_ENTREGA_VALIDOS, true)) {
            $this->error('Valor inválido para --status-entrega-padrao: "'.$this->statusPadrao().'".');
            $this->line('  Use exatamente um destes: '.implode(' | ', self::STATUS_ENTREGA_VALIDOS));
            return self::FAILURE;
        }

        try {
            $this->fase0Precheck($dry);

            // Pré-visualização (volumes + decisões aplicadas) — base do assistente de confirmação.
            $prev = $this->previewContagens();
            $this->exibirDecisoes();

            if ($dry) {
                $this->newLine();
                $this->info('✓ Simulação concluída. Nenhuma alteração foi feita.');
                return self::SUCCESS;
            }

            // ── Gates de confirmação (apenas OPERACIONAIS, sempre por seleção) ──
            if ($prev['naoEncontradas'] > 0) {
                $this->warn("  ⚠ {$prev['naoEncontradas']} tabela(s) de origem não foram encontradas e serão ignoradas.");
                if (! $this->gate('  Deseja prosseguir mesmo assim?', true)) {
                    $this->line('  Operação cancelada pelo operador. Nada foi alterado.');
                    return self::SUCCESS;
                }
            }
            if (! $this->gate("  Prosseguir com a gravação? ({$prev['total']} registros serão transferidos)", true)) {
                $this->line('  Operação cancelada pelo operador. Nada foi alterado.');
                return self::SUCCESS;
            }

            $this->fase1Quarentena();
            $this->fase2Construcao();
            $this->fase3Transferencia(false);
            $this->fase4Validacao();
            $this->etapaDescarte();
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('✗ MIGRAÇÃO INTERROMPIDA: '.$e->getMessage());
            $this->warn('O legado permanece preservado nos schemas '.self::Q_PEI.' / '.self::Q_PUB.' (quando a Fase 1 já tiver rodado).');
            $this->line('Nenhum dado antigo foi destruído. Corrija a causa e reexecute.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✓ Migração concluída.');
        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 0 — Pré-checagem
    // ──────────────────────────────────────────────────────────────────────
    private function fase0Precheck(bool $dry): void
    {
        $this->comment("\n▶ Fase 0 — Pré-checagem");

        // Versão do PostgreSQL
        $verNum = (int) (DB::selectOne('show server_version_num')->server_version_num ?? 0);
        $verTxt = DB::selectOne('show server_version')->server_version ?? '?';
        if ($verNum < self::PG_MIN) {
            throw new \RuntimeException("PostgreSQL $verTxt incompatível. A v2 exige >= 9.4 (jsonb/gen_random_uuid). Atualize o servidor antes de migrar.");
        }
        $this->line("  • PostgreSQL: $verTxt  (compatível)");

        // Disponibilidade de gen_random_uuid (pgcrypto em PG < 13)
        $this->checarPgcrypto($dry);

        // Confirmação de backup (decisão operacional — por seleção Sim/Não)
        if (! $dry && ! $this->option('pular-backup')) {
            if (! $this->gate('  Foi feito um pg_dump COMPLETO do banco antes desta execução?', true)) {
                throw new \RuntimeException('Backup não confirmado. Faça o pg_dump e reexecute.');
            }
        }

        // Estado: o legado existe? a migração já foi feita?
        $temPei = $this->schemaExiste('pei');
        $jaQuarentena = $this->schemaExiste(self::Q_PEI);
        if (! $temPei && ! $jaQuarentena) {
            throw new \RuntimeException('Schema "pei" do legado não encontrado e quarentena inexistente. Este banco contém a v1?');
        }
        if ($jaQuarentena) {
            $this->warn('  • Quarentena já existe ('.self::Q_PEI.'). Em reexecução, a Fase 1 será pulada.');
        }
        $this->line('  • Estado do banco verificado.');
    }

    /** Verifica gen_random_uuid(); se faltar, oferece criar a extensão pgcrypto (seleção Sim/Não). */
    private function checarPgcrypto(bool $dry): void
    {
        try {
            DB::select('select gen_random_uuid()');
            $this->line('  • gen_random_uuid(): disponível.');
            return;
        } catch (Throwable $e) {
            // segue para tratamento
        }

        $this->warn('  • gen_random_uuid() indisponível (PostgreSQL < 13 sem a extensão pgcrypto).');
        if ($dry) {
            $this->line('    [dry-run] Em produção, o assistente ofereceria criar a extensão pgcrypto aqui.');
            return;
        }
        if (! $this->gate('  Criar a extensão pgcrypto agora?', true)) {
            throw new \RuntimeException('Extensão pgcrypto necessária. Crie manualmente com: CREATE EXTENSION pgcrypto;');
        }
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        $this->line('  • Extensão pgcrypto criada.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // PREVIEW — volumes que serão migrados (lê a origem, não exige destino)
    // ──────────────────────────────────────────────────────────────────────
    private function previewContagens(): array
    {
        $this->comment("\n▶ Pré-visualização — registros a migrar");
        $rows = [];
        $naoEncontradas = 0;
        $total = 0;

        foreach ($this->mapa() as $def) {
            $origemFqn = $this->resolverOrigem($def['origem']);
            if ($origemFqn === null) {
                $rows[] = [$def['destino'], '(não encontrada)', '—', 'IGNORADA'];
                $naoEncontradas++;
                continue;
            }
            $n = (int) DB::table($origemFqn)->count();
            $total += $n;
            $rows[] = [$def['destino'], $origemFqn, $n, 'OK'];
        }

        $this->table(['Destino (v2)', 'Origem (v1)', 'Registros', 'Situação'], $rows);
        $this->line("  Total a migrar: <info>{$total}</info> registros.");

        return ['naoEncontradas' => $naoEncontradas, 'total' => $total];
    }

    /** Exibe, de forma transparente, as decisões de negócio aplicadas (não são perguntas). */
    private function exibirDecisoes(): void
    {
        $this->comment("\n▶ Decisões aplicadas nesta migração");
        $this->line('  • UUIDs preservados 1:1 (vínculos entre registros permanecem válidos).');
        $this->line('  • Entregas: campos sem equivalente preservados em json_propriedades.');
        $this->line('  • Status de entrega não reconhecido → "'.$this->statusPadrao().'".');
        $this->line('  • Auditoria (audits/tab_audit): '
            .($this->option('migrar-auditoria')
                ? 'SERÁ migrada (flag --migrar-auditoria).'
                : 'NÃO será migrada (padrão).'));
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 1 — Quarentena (renomeia o legado, preservando tudo)
    // ──────────────────────────────────────────────────────────────────────
    private function fase1Quarentena(): void
    {
        $this->comment("\n▶ Fase 1 — Quarentena do legado");

        if ($this->schemaExiste(self::Q_PEI)) {
            $this->line('  • '.self::Q_PEI.' já existe — pulando.');
            return;
        }

        DB::transaction(function () {
            // 1) schema "pei" inteiro → legacy_pei (todas as tabelas vão junto)
            if ($this->schemaExiste('pei')) {
                DB::statement('ALTER SCHEMA pei RENAME TO '.self::Q_PEI);
                $this->line('  • schema "pei" → '.self::Q_PEI);
            }

            // 2) tabelas de "public" do cliente → legacy_public (libera o public para a v2)
            DB::statement('CREATE SCHEMA IF NOT EXISTS '.self::Q_PUB);
            foreach ($this->tabelasDoSchema('public') as $t) {
                DB::statement("ALTER TABLE public.\"$t\" SET SCHEMA ".self::Q_PUB);
            }
            $this->line('  • tabelas de "public" movidas para '.self::Q_PUB);
        });

        $this->info('  ✓ Legado em quarentena (preservado, reversível).');
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 2 — Construção do schema v2
    // ──────────────────────────────────────────────────────────────────────
    private function fase2Construcao(): void
    {
        $this->comment("\n▶ Fase 2 — Construção do schema v2 (migrate)");
        Artisan::call('migrate', ['--force' => true], $this->getOutput());
        $this->info('  ✓ Schema v2 criado.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 3 — Transferência (ETL genérico, resiliente)
    // ──────────────────────────────────────────────────────────────────────
    private function fase3Transferencia(bool $dry): void
    {
        $this->comment("\n▶ Fase 3 — Transferência de dados");

        foreach ($this->mapa() as $def) {
            $origemFqn = $this->resolverOrigem($def['origem']);
            $destino   = $def['destino'];

            if ($origemFqn === null) {
                $this->relatorio[$destino] = ['origem' => '(não encontrada)', 'lidos' => 0, 'gravados' => 0, 'status' => 'PULADO'];
                $this->line("  • {$destino}: origem não encontrada — pulado.");
                continue;
            }
            if (! $this->tabelaDestinoExiste($destino)) {
                $this->relatorio[$destino] = ['origem' => $origemFqn, 'lidos' => 0, 'gravados' => 0, 'status' => 'DESTINO AUSENTE'];
                $this->warn("  • {$destino}: tabela de destino não existe — pulado.");
                continue;
            }

            $colsDestino = $this->colunas($destino);
            $rename      = $def['rename'] ?? [];
            $defaults    = $def['defaults'] ?? [];
            $transform   = $def['transform'] ?? null;

            $lidos = 0; $gravados = 0;
            $buffer = [];

            DB::table($origemFqn)->orderBy($this->primeiraColuna($origemFqn))->chunk(1000, function ($linhas) use (
                &$lidos, &$gravados, &$buffer, $colsDestino, $rename, $defaults, $transform, $destino, $dry
            ) {
                foreach ($linhas as $linha) {
                    $lidos++;
                    $row = (array) $linha;

                    // 1) renomear colunas
                    foreach ($rename as $de => $para) {
                        if (array_key_exists($de, $row)) {
                            $row[$para] = $row[$de];
                            unset($row[$de]);
                        }
                    }
                    // 2) transformador específico (entregas, grau, etc.)
                    if ($transform) {
                        $row = $this->{$transform}($row);
                    }
                    // 3) manter apenas colunas existentes no destino
                    $row = array_intersect_key($row, array_flip($colsDestino));
                    // 4) defaults para colunas novas ausentes
                    foreach ($defaults as $col => $val) {
                        if (in_array($col, $colsDestino, true) && (! array_key_exists($col, $row) || $row[$col] === null)) {
                            $row[$col] = $val;
                        }
                    }

                    $buffer[] = $row;
                    if (! $dry && count($buffer) >= 500) {
                        DB::table($destino)->insert($buffer);
                        $gravados += count($buffer);
                        $buffer = [];
                    }
                }
            });

            if (! $dry && ! empty($buffer)) {
                DB::table($destino)->insert($buffer);
                $gravados += count($buffer);
            }

            $this->relatorio[$destino] = ['origem' => $origemFqn, 'lidos' => $lidos, 'gravados' => $gravados, 'status' => 'OK'];
            $this->line("  • {$destino}: {$lidos} lidos → {$gravados} gravados");
        }

        // Etapa derivada: pivô plano↔organização (a v2 usa N:N)
        $this->popularRelPlanoOrganizacao($dry);
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 4 — Validação
    // ──────────────────────────────────────────────────────────────────────
    private function fase4Validacao(): void
    {
        $this->comment("\n▶ Fase 4 — Validação (origem × destino)");
        $rows = [];
        $divergencias = 0;
        foreach ($this->relatorio as $destino => $r) {
            $ok = $r['lidos'] === $r['gravados'] ? '✓' : '✗';
            if ($r['lidos'] !== $r['gravados'] && $r['status'] === 'OK') {
                $divergencias++;
            }
            $rows[] = [$destino, $r['origem'], $r['lidos'], $r['gravados'], $r['status'], $ok];
        }
        $this->table(['Destino', 'Origem', 'Lidos', 'Gravados', 'Status', 'OK'], $rows);

        if ($divergencias > 0) {
            $this->warn("  ⚠ {$divergencias} tabela(s) com divergência de contagem. Revise antes de descartar o legado.");
        } else {
            $this->info('  ✓ Contagens conferem.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Decisão operacional final — o que fazer com o legado (seleção)
    // ──────────────────────────────────────────────────────────────────────
    private function etapaDescarte(): void
    {
        // Flag explícita tem prioridade (modo automação).
        if ($this->option('descartar-legado')) {
            if ($this->gate('  Confirmar remoção DEFINITIVA dos schemas legacy_*?', true)) {
                $this->fase5Descarte();
            } else {
                $this->line('  • Descarte cancelado. Legado mantido.');
            }
            return;
        }

        // Sem flag e em modo automação (--force): mantém o legado por segurança.
        if ($this->option('force')) {
            $this->line("\n  • Legado mantido em ".self::Q_PEI.' (use --descartar-legado quando estabilizar).');
            return;
        }

        // Interativo: escolha entre opções fixas (sem texto livre).
        $opcao = $this->choice(
            "\n  O que fazer com o legado agora?",
            ['Manter como rede de segurança (recomendado)', 'Descartar definitivamente agora'],
            0
        );
        if (str_starts_with($opcao, 'Descartar')) {
            $this->fase5Descarte();
        } else {
            $this->line('  • Legado mantido em '.self::Q_PEI.'. Remova depois com --descartar-legado.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // FASE 5 — Descarte (irreversível)
    // ──────────────────────────────────────────────────────────────────────
    private function fase5Descarte(): void
    {
        $this->comment("\n▶ Fase 5 — Descarte do legado");
        DB::statement('DROP SCHEMA IF EXISTS '.self::Q_PEI.' CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS '.self::Q_PUB.' CASCADE');
        $this->info('  ✓ Legado removido.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Transformadores específicos
    // ──────────────────────────────────────────────────────────────────────

    /** Entregas: preserva campos órfãos em json_propriedades; aplica defaults do modelo Notion. */
    private function transformEntrega(array $row): array
    {
        $orfaos = [];
        foreach (['dsc_unidade_medida', 'dsc_item_entregue', 'num_quantidade_prevista'] as $c) {
            if (array_key_exists($c, $row) && $row[$c] !== null && $row[$c] !== '') {
                $orfaos[$c] = $row[$c];
            }
            unset($row[$c]);
        }
        $row['json_propriedades'] = json_encode($orfaos ?: (object) [], JSON_UNESCAPED_UNICODE);

        // status: normaliza para os valores válidos da v2 (default configurável por flag)
        if (! in_array($row['bln_status'] ?? null, self::STATUS_ENTREGA_VALIDOS, true)) {
            $row['bln_status'] = $this->statusPadrao();
        }
        // defaults do novo modelo
        $row['dsc_tipo']        = $row['dsc_tipo']        ?? 'task';
        $row['cod_prioridade']  = $row['cod_prioridade']  ?? 'media';
        $row['num_ordem']       = $row['num_ordem']       ?? 0;
        $row['bln_arquivado']   = $row['bln_arquivado']   ?? false;
        $row['num_nivel_hierarquico_apresentacao'] = $row['num_nivel_hierarquico_apresentacao'] ?? 1;

        return $row;
    }

    /** Grau de satisfação: v2 exige cod_pei e num_ano — associa ao primeiro PEI migrado. */
    private function transformGrauSatisfacao(array $row): array
    {
        static $pei = null;
        if ($pei === null) {
            $pei = DB::table('strategic_planning.tab_pei')->orderBy('num_ano_inicio_pei')->first();
        }
        if ($pei) {
            $row['cod_pei'] = $row['cod_pei'] ?? $pei->cod_pei;
            $row['num_ano'] = $row['num_ano'] ?? $pei->num_ano_inicio_pei;
        }
        return $row;
    }

    /** Popula o pivô N:N action_plan.rel_plano_organizacao a partir do cod_organizacao do plano legado. */
    private function popularRelPlanoOrganizacao(bool $dry): void
    {
        $destino = 'action_plan.rel_plano_organizacao';
        if (! $this->tabelaDestinoExiste($destino)) {
            return;
        }
        $cols = $this->colunas($destino);
        $planos = DB::table('action_plan.tab_plano_de_acao')->whereNotNull('cod_organizacao')->get(['cod_plano_de_acao', 'cod_organizacao']);
        $n = 0; $buffer = [];
        foreach ($planos as $p) {
            $row = ['cod_plano_de_acao' => $p->cod_plano_de_acao, 'cod_organizacao' => $p->cod_organizacao];
            if (in_array('id', $cols, true)) {
                $row['id'] = (string) Str::uuid();
            }
            $buffer[] = array_intersect_key($row, array_flip($cols));
            if (! $dry && count($buffer) >= 500) { DB::table($destino)->insert($buffer); $buffer = []; }
            $n++;
        }
        if (! $dry && ! empty($buffer)) { DB::table($destino)->insert($buffer); }
        $this->line("  • {$destino}: {$n} vínculos gravados");
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    /** Status padrão para entregas não reconhecidas (flag --status-entrega-padrao). */
    private function statusPadrao(): string
    {
        $v = trim((string) $this->option('status-entrega-padrao'));
        return $v !== '' ? $v : 'Não Iniciado';
    }

    /**
     * Gate de confirmação por SELEÇÃO (Sim/Não). Em modo --force, assume a opção segura
     * informada, sem perguntar — garantindo reprodutibilidade na automação.
     */
    private function gate(string $msg, bool $assumeSeForce): bool
    {
        if ($this->option('force')) {
            return $assumeSeForce;
        }
        return $this->confirm($msg, false);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Utilitários (descoberta de schema/tabela/coluna em runtime)
    // ──────────────────────────────────────────────────────────────────────

    private function schemaExiste(string $schema): bool
    {
        return (bool) DB::selectOne(
            'select 1 from information_schema.schemata where schema_name = ?', [$schema]
        );
    }

    private function tabelasDoSchema(string $schema): array
    {
        return collect(DB::select(
            "select table_name from information_schema.tables where table_schema = ? and table_type = 'BASE TABLE'",
            [$schema]
        ))->pluck('table_name')->all();
    }

    /**
     * Procura a tabela legada nos schemas de quarentena e, como fallback, no schema "pei"
     * (estado pré-quarentena, usado na pré-visualização). A ordem garante que, após a Fase 1,
     * a versão em quarentena tenha prioridade sobre qualquer tabela de mesmo nome.
     * Devolve o FQN ("schema.tabela") ou null.
     */
    private function resolverOrigem(array $candidatos): ?string
    {
        foreach ([self::Q_PEI, self::Q_PUB, 'pei'] as $schema) {
            foreach ($candidatos as $tab) {
                $existe = DB::selectOne(
                    'select 1 from information_schema.tables where table_schema = ? and table_name = ?',
                    [$schema, $tab]
                );
                if ($existe) {
                    return "$schema.$tab";
                }
            }
        }
        return null;
    }

    private function tabelaDestinoExiste(string $fqn): bool
    {
        [$schema, $tab] = explode('.', $fqn, 2);
        return (bool) DB::selectOne(
            'select 1 from information_schema.tables where table_schema = ? and table_name = ?',
            [$schema, $tab]
        );
    }

    private function colunas(string $fqn): array
    {
        [$schema, $tab] = explode('.', $fqn, 2);
        return collect(DB::select(
            'select column_name from information_schema.columns where table_schema = ? and table_name = ?',
            [$schema, $tab]
        ))->pluck('column_name')->all();
    }

    private function primeiraColuna(string $fqn): string
    {
        return $this->colunas($fqn)[0] ?? 'created_at';
    }
}
