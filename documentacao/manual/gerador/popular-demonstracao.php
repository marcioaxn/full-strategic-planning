<?php

/**
 * Ambiente de demonstração do manual de uso — popula o banco `pei_manual`.
 *
 * Órgão fictício "Agência Federal de Exemplo" (AFE) com três unidades, cinco
 * usuários (um por perfil) e três ciclos PEI, preenchidos pelos Models reais
 * da aplicação: eventos, observers, histórico de entregas e trilha de
 * auditoria (owen-it) disparam como na tela.
 *
 * Idempotente: cada registro é procurado pela sua chave natural antes de ser
 * criado. A fase de "alterações" (que dá conteúdo à auditoria e à aba
 * Atividade do sino) só roda na primeira carga do ciclo vigente.
 *
 * 🔴 Aborta se a conexão padrão não for o banco `pei_manual`.
 * A senha dos usuários vem de $env:SENHA_DEMO (nunca de arquivo).
 *
 * Uso (PowerShell):
 *   $env:DB_DATABASE='pei_manual'; $env:DB_PORT='5434'; $env:SENHA_DEMO='...'
 *   php documentacao/manual/gerador/popular-demonstracao.php
 */

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\EntregaAnexo;
use App\Models\ActionPlan\EntregaComentario;
use App\Models\ActionPlan\EntregaLabel;
use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoComunicacao;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\ActionPlan\Raci;
use App\Models\ActionPlan\TipoExecucao;
use App\Models\Agenda2030\ODS;
use App\Models\Documento;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\PerformanceIndicators\EvolucaoIndicador;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\Reports\RelatorioAgendado;
use App\Models\RiskManagement\Risco;
use App\Models\RiskManagement\RiscoMitigacao;
use App\Models\RiskManagement\RiscoOcorrencia;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\Arquivo;
use App\Models\StrategicPlanning\AtividadeCadeiaValor;
use App\Models\StrategicPlanning\CalendarioEventoPei;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\EstrategiaTows;
use App\Models\StrategicPlanning\FuturoAlmejado;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\IdentidadeEstrategica;
use App\Models\StrategicPlanning\InauguraPei;
use App\Models\StrategicPlanning\IntegracaoInstrumento;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ObjetivoComentario;
use App\Models\StrategicPlanning\ParteInteressada;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor;
use App\Models\StrategicPlanning\Rae;
use App\Models\StrategicPlanning\RaeCausaRaiz;
use App\Models\StrategicPlanning\RaeEncaminhamento;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\Valor;
use App\Models\User;
use App\Services\IndicadorCalculoService;
use App\Services\NotificationService;
use Database\Seeders\OrganizacaoRaizSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

const BANCO_DEMONSTRACAO = 'pei_manual';

chdir(__DIR__.'/../../..');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// ---------------------------------------------------------------------------
// Guardas
// ---------------------------------------------------------------------------
if (DB::connection()->getDatabaseName() !== BANCO_DEMONSTRACAO) {
    fwrite(STDERR, 'ABORTADO: a conexão aponta para "'.DB::connection()->getDatabaseName().'", não para "'.BANCO_DEMONSTRACAO.'".'.PHP_EOL);
    exit(1);
}

$senha = (string) getenv('SENHA_DEMO');
if ($senha === '') {
    fwrite(STDERR, 'ABORTADO: defina $env:SENHA_DEMO com a senha dos usuários de demonstração.'.PHP_EOL);
    exit(1);
}

// A auditoria do owen-it é desligada no console (config/audit.php). Aqui ela
// precisa rodar como na tela, para a trilha e a aba Atividade terem conteúdo.
config(['audit.console' => true]);

$REAL_AGORA = Carbon::now();
$LIMITE_EVOLUCAO = Carbon::create($REAL_AGORA->year, $REAL_AGORA->month, 1); // até o mês anterior ao atual

function linha(string $texto): void
{
    echo $texto.PHP_EOL;
}

/** Fixa o relógio da aplicação (created_at/updated_at e auditoria) num instante do passado recente. */
function momento(Carbon $instante): void
{
    Carbon::setTestNow($instante);
}

function logar(User $usuario, ?string $codOrganizacao = null): void
{
    Auth::guard('web')->login($usuario);
    if ($codOrganizacao) {
        session(['organizacao_selecionada_id' => $codOrganizacao]);
    }
}

/** Casas decimais de cada unidade de medida (mesma regra de App\Support\UnidadeMedida). */
function casasDaUnidade(string $unidade): int
{
    return match ($unidade) {
        'Índice (0-1)', 'Proporção', 'Taxa' => 4,
        'Quantidade (un)', 'Dias', 'Nº de Ocorrências' => 0,
        'Toneladas (t)' => 3,
        default => 2,
    };
}

function pdfDemonstracao(string $titulo, array $paragrafos): string
{
    $corpo = '';
    foreach ($paragrafos as $p) {
        $corpo .= '<p>'.htmlspecialchars($p, ENT_QUOTES, 'UTF-8').'</p>';
    }
    $html = '<html><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;margin:40px}'
        .'h1{font-size:18px;color:#1f3a5f;border-bottom:2px solid #1f3a5f;padding-bottom:6px}'
        .'.rodape{margin-top:40px;font-size:10px;color:#666}</style></head><body>'
        .'<div style="font-size:11px;color:#555">AGÊNCIA FEDERAL DE EXEMPLO — AFE</div>'
        .'<h1>'.htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8').'</h1>'.$corpo
        .'<div class="rodape">Documento fictício do ambiente de demonstração do Sistema PEI.</div></body></html>';

    $dompdf = new Dompdf\Dompdf;
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();

    return $dompdf->output();
}

function usuario(string $email, string $nome, string $senha, bool $superAdmin = false): User
{
    $u = User::where('email', $email)->first() ?? new User(['email' => $email]);
    $u->fill(['name' => $nome, 'ativo' => true, 'trocarsenha' => 0, 'password' => $senha]);
    $u->forceFill(['adm' => $superAdmin ? 1 : 0]);
    $u->save();
    if (! $u->email_verified_at) {
        $u->forceFill(['email_verified_at' => now()])->save();
    }

    return $u;
}

/** Vínculo usuário ↔ unidade ↔ perfil (e, para gestor, a iniciativa), como as telas gravam. */
function vincularPerfil(User $u, string $codOrganizacao, string $codPerfil, ?string $codPlano = null): void
{
    $existe = DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')
        ->where('user_id', $u->id)->where('cod_organizacao', $codOrganizacao)->where('cod_perfil', $codPerfil)
        ->when($codPlano, fn ($q) => $q->where('cod_plano_de_acao', $codPlano), fn ($q) => $q->whereNull('cod_plano_de_acao'))
        ->exists();
    if (! $existe) {
        DB::table('organization.rel_users_tab_organizacoes_tab_perfil_acesso')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $u->id,
            'cod_organizacao' => $codOrganizacao,
            'cod_perfil' => $codPerfil,
            'cod_plano_de_acao' => $codPlano,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    $u->organizacoes()->syncWithoutDetaching([$codOrganizacao]);
}

function aplicarFator(float $previsto, float $fator, string $polaridade): float
{
    return $polaridade === 'Negativa' ? $previsto / $fator : $previsto * $fator;
}

/**
 * Lança a evolução mensal de um indicador.
 *
 * $fatores[ano] = atingimento desejado no ano (1,00 = 100% da meta). O último
 * mês medido de cada ano usa o fator exato, para o farol cair na faixa
 * planejada; os demais oscilam um pouco em torno dele.
 */
function lancarEvolucoes(Indicador $ind, array $metas, array $fatores, Carbon $limite, array $avaliacoes = []): int
{
    $casas = casasDaUnidade($ind->dsc_unidade_medida);
    $acumulado = $ind->bln_acumulado === 'Sim';
    $polaridade = (string) $ind->dsc_polaridade;
    $semente = crc32($ind->nom_indicador) % 7;
    $total = 0;

    foreach ($fatores as $ano => $fator) {
        if ($ano > $limite->year || ($ano === $limite->year && $limite->month === 1)) {
            continue;
        }
        $ultimoMes = $ano < $limite->year ? 12 : $limite->month - 1;
        $previstoMes = round($acumulado ? $metas[$ano] / 12 : $metas[$ano], $casas);
        $cumPrevisto = 0.0;
        $cumRealAnterior = 0.0;

        for ($mes = 1; $mes <= $ultimoMes; $mes++) {
            $amplitude = $acumulado ? 0.015 : 0.035;
            $f = $mes === $ultimoMes ? $fator : $fator + $amplitude * sin($mes * 1.9 + $semente);

            if ($acumulado) {
                $cumPrevisto += $previstoMes;
                $cumReal = round(aplicarFator($cumPrevisto, $f, $polaridade), $casas);
                $realizado = round($cumReal - $cumRealAnterior, $casas);
                $cumRealAnterior = $cumReal;
            } else {
                $realizado = round(aplicarFator($previstoMes, $f, $polaridade), $casas);
            }
            // Percentual e índice têm teto natural (100% e 1).
            if ($ind->dsc_unidade_medida === 'Percentual (%)') {
                $realizado = min(100, $realizado);
            } elseif ($ind->dsc_unidade_medida === 'Índice (0-1)') {
                $realizado = min(1, $realizado);
            }

            $evolucao = EvolucaoIndicador::firstOrCreate(
                ['cod_indicador' => $ind->cod_indicador, 'num_ano' => $ano, 'num_mes' => $mes],
                [
                    'vlr_previsto' => $previstoMes,
                    'vlr_realizado' => max(0, $realizado),
                    'txt_avaliacao' => $avaliacoes[$ano.'-'.$mes] ?? null,
                    'bln_atualizado' => 'Sim',
                ]
            );
            if ($evolucao->wasRecentlyCreated) {
                $total++;
            }
        }
    }

    return $total;
}

/** Indicador + unidades + metas anuais + linha de base, como a tela de Indicadores grava. */
function indicador(array $dados, array $orgs, array $metas, array $linhaBase): Indicador
{
    $ind = Indicador::where('nom_indicador', $dados['nom_indicador'])->first();
    if (! $ind) {
        $dados += [
            'dsc_calculation_type' => 'manual',
            'dsc_periodo_medicao' => 'Mensal',
            'num_peso' => 1,
            'json_smart' => ['especifico' => true, 'mensuravel' => true, 'atingivel' => true, 'relevante' => true, 'temporal' => true],
        ];
        $ind = Indicador::create($dados);
        $ind->organizacoes()->sync($orgs);
    }
    foreach ($metas as $ano => $meta) {
        MetaPorAno::firstOrCreate(['cod_indicador' => $ind->cod_indicador, 'num_ano' => $ano], ['meta' => $meta]);
    }
    foreach ($linhaBase as $ano => $valor) {
        LinhaBaseIndicador::firstOrCreate(['cod_indicador' => $ind->cod_indicador, 'num_ano' => $ano], ['num_linha_base' => $valor]);
    }

    return $ind;
}

function iniciativa(array $dados, array $orgs): PlanoDeAcao
{
    $plano = PlanoDeAcao::where('dsc_plano_de_acao', $dados['dsc_plano_de_acao'])->first();
    if (! $plano) {
        $dados += ['num_nivel_hierarquico_apresentacao' => 3, 'cod_organizacao' => $orgs[0]];
        $plano = PlanoDeAcao::create($dados);
        $plano->organizacoes()->sync($orgs);
    }

    return $plano;
}

function entrega(PlanoDeAcao $plano, array $dados, array $responsaveis = [], ?Entrega $pai = null): Entrega
{
    $e = Entrega::where('cod_plano_de_acao', $plano->cod_plano_de_acao)->where('dsc_entrega', $dados['dsc_entrega'])->first();
    if ($e) {
        return $e;
    }
    $ordem = (int) Entrega::where('cod_plano_de_acao', $plano->cod_plano_de_acao)->max('num_ordem') + 1;
    $e = Entrega::create($dados + [
        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
        'cod_entrega_pai' => $pai?->cod_entrega,
        'dsc_tipo' => 'task',
        'cod_prioridade' => 'media',
        'num_ordem' => $ordem,
        'num_nivel_hierarquico_apresentacao' => $ordem,
        'num_peso' => 0,
        'bln_arquivado' => false,
        'cod_responsavel' => $responsaveis ? $responsaveis[0]->id : null,
    ]);
    if ($responsaveis) {
        $e->responsaveis()->sync(array_map(fn (User $u) => $u->id, $responsaveis));
    }

    return $e;
}

function risco(array $dados, array $objetivos): Risco
{
    $r = Risco::where('cod_pei', $dados['cod_pei'])->where('dsc_titulo', $dados['dsc_titulo'])->first();
    if (! $r) {
        $r = Risco::create($dados);
        $r->objetivos()->sync(array_map(fn (Objetivo $o) => $o->cod_objetivo, $objetivos));
    }

    return $r;
}

function objetivo(Perspectiva $p, string $nome, string $descricao, int $ordem, ?Objetivo $pai = null, array $ods = []): Objetivo
{
    $o = Objetivo::where('cod_perspectiva', $p->cod_perspectiva)->where('nom_objetivo', $nome)->first();
    if (! $o) {
        $o = Objetivo::create([
            'nom_objetivo' => $nome,
            'dsc_objetivo' => $descricao,
            'num_nivel_hierarquico_apresentacao' => $ordem,
            'cod_perspectiva' => $p->cod_perspectiva,
            'cod_objetivo_pai' => $pai?->cod_objetivo,
            'num_nivel_desdobramento' => $pai ? ($pai->num_nivel_desdobramento ?? 1) + 1 : 1,
        ]);
        if ($ods) {
            $o->ods()->sync(collect($ods)->mapWithKeys(fn ($txt, $num) => [$num => ['txt_contribuicao' => $txt]])->all());
        }
    }

    return $o;
}

function documento(array $dados, array $paragrafos, User $autor): Documento
{
    $doc = Documento::where('nom_documento', $dados['nom_documento'])->first();
    if ($doc) {
        return $doc;
    }
    $pdf = pdfDemonstracao($dados['nom_documento'], $paragrafos);
    $caminho = Documento::PASTA.'/'.Str::uuid().'.pdf';
    Storage::disk(Documento::DISCO)->put($caminho, $pdf);

    return Documento::create($dados + [
        'dsc_nome_arquivo' => Str::slug($dados['nom_documento']).'.pdf',
        'dsc_caminho' => $caminho,
        'num_tamanho_bytes' => strlen($pdf),
        'dsc_hash_sha256' => hash('sha256', $pdf),
        'cod_usuario' => $autor->id,
    ]);
}

// ===========================================================================
// 0. Vocabulário de ODS (tab_ods não tem seeder; vem de ods.json ao lado)
// ===========================================================================
$ods = json_decode(file_get_contents(__DIR__.'/ods.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($ods as $linhaOds) {
    ODS::firstOrCreate(['num_ods' => $linhaOds['num_ods']], $linhaOds);
}
linha('ODS: '.ODS::count());

$primeiraCarga = ! PEI::where('dsc_pei', 'PEI 2024-2027')->exists();

// ===========================================================================
// 1. Estrutura organizacional e usuários (relógio: 30 dias atrás)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(30)->setTime(9, 0));

$afe = Organization::find(OrganizacaoRaizSeeder::COD_ORGANIZACAO);
if (! $afe) {
    $afe = Organization::create(['sgl_organizacao' => 'AFE', 'nom_organizacao' => 'Agência Federal de Exemplo']);
}
$afe->fill(['sgl_organizacao' => 'AFE', 'nom_organizacao' => 'Agência Federal de Exemplo', 'rel_cod_organizacao' => $afe->cod_organizacao]);
if ($afe->isDirty()) {
    $afe->save();
}
$spg = Organization::firstOrCreate(['sgl_organizacao' => 'SPG'], ['nom_organizacao' => 'Secretaria de Planejamento e Gestão', 'rel_cod_organizacao' => $afe->cod_organizacao]);
$dti = Organization::firstOrCreate(['sgl_organizacao' => 'DTI'], ['nom_organizacao' => 'Diretoria de Tecnologia da Informação', 'rel_cod_organizacao' => $spg->cod_organizacao]);
$dac = Organization::firstOrCreate(['sgl_organizacao' => 'DAC'], ['nom_organizacao' => 'Diretoria de Atendimento ao Cidadão', 'rel_cod_organizacao' => $afe->cod_organizacao]);

$admin = usuario('admin.sistema@exemplo.gov.br', 'Administração do Sistema', $senha, true);
$carla = usuario('carla.ribeiro@exemplo.gov.br', 'Carla Mendes Ribeiro', $senha);
$rafael = usuario('rafael.lima@exemplo.gov.br', 'Rafael Souza Lima', $senha);
$juliana = usuario('juliana.costa@exemplo.gov.br', 'Juliana Alves Costa', $senha);
$paulo = usuario('paulo.nogueira@exemplo.gov.br', 'Paulo Henrique Nogueira', $senha);

vincularPerfil($admin, $afe->cod_organizacao, PerfilAcesso::SUPER_ADMIN);
vincularPerfil($carla, $spg->cod_organizacao, PerfilAcesso::ADMIN_UNIDADE);
vincularPerfil($paulo, $afe->cod_organizacao, PerfilAcesso::CONSULTA);
linha('Organizações: '.Organization::count().' | Usuários: '.User::count());

// ===========================================================================
// 2. Ciclos PEI
// ===========================================================================
logar($admin, $afe->cod_organizacao);
$peiAnterior = PEI::firstOrCreate(['dsc_pei' => 'PEI 2020-2023'], ['num_ano_inicio_pei' => 2020, 'num_ano_fim_pei' => 2023]);
$pei = PEI::firstOrCreate(['dsc_pei' => 'PEI 2024-2027'], ['num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);
$peiFuturo = PEI::firstOrCreate(['dsc_pei' => 'PEI 2028-2031'], ['num_ano_inicio_pei' => 2028, 'num_ano_fim_pei' => 2031]);

// Régua do farol (todo o ciclo), sem buracos entre as faixas.
$regua = [
    ['Crítico', '#dc3545', 0, 49.99],
    ['Atenção', '#ffc107', 50, 79.99],
    ['Bom', '#198754', 80, 99.99],
    ['Superado', '#0d6efd', 100, 999],
];
foreach ([$pei, $peiAnterior] as $ciclo) {
    foreach ($regua as [$rotulo, $cor, $min, $max]) {
        GrauSatisfacao::firstOrCreate(
            ['cod_pei' => $ciclo->cod_pei, 'dsc_grau_satisfacao' => $rotulo, 'num_ano' => null],
            ['cor' => $cor, 'vlr_minimo' => $min, 'vlr_maximo' => $max]
        );
    }
}

// ===========================================================================
// 3. Inaugurar e Integrar (ciclo vigente)
// ===========================================================================
InauguraPei::firstOrCreate(['cod_pei' => $pei->cod_pei], [
    'txt_equipe' => "Comitê de Governança Estratégica (CGE), presidido pelo Diretor-Presidente da AFE.\nCoordenação técnica: Secretaria de Planejamento e Gestão (SPG).\nPontos focais: Diretoria de Tecnologia da Informação (DTI) e Diretoria de Atendimento ao Cidadão (DAC).\nApoio: Assessoria de Comunicação e Escritório de Projetos.",
    'txt_diretrizes' => "1. Orientar o plano a resultados percebidos pelo cidadão.\n2. Integrar o PEI ao Plano Plurianual e à Lei Orçamentária Anual.\n3. Garantir participação de todas as unidades na construção do mapa estratégico.\n4. Monitorar a execução em Reuniões de Avaliação da Estratégia (RAE) quadrimestrais.\n5. Dar transparência ativa aos resultados.",
    'txt_metodologia' => 'Balanced Scorecard adaptado ao setor público, com análise de ambiente (PESTEL e SWOT), matriz de partes interessadas, cenários prospectivos e oficinas participativas com as unidades.',
    'txt_observacoes' => 'O plano foi aprovado pelo CGE em dezembro de 2023 e publicado por portaria do Diretor-Presidente.',
    'dte_inicio_processo' => '2023-08-01',
    'dte_fim_previsto' => '2023-12-15',
    'bln_aprovado' => true,
]);

$integracoes = [
    ['PPA', 'Plano Plurianual 2024-2027 — Programa 2101 (Gestão e Manutenção do Poder Executivo)', 'Alinhar os objetivos estratégicos às metas do programa e aos atributos do PPA.', 'Mapear objetivos × metas do PPA; revisar anualmente os indicadores comuns.', 'Alta'],
    ['LOA', 'Lei Orçamentária Anual 2026 — ações 2000 e 21C0', 'Garantir dotação para os projetos estratégicos priorizados pelo CGE.', 'Marcar as iniciativas estratégicas no sistema orçamentário; acompanhar a execução bimestral.', 'Alta'],
    ['Plano Setorial', 'Plano Diretor de Tecnologia da Informação e Comunicação (PDTIC) 2024-2026', 'Priorizar no PDTIC as soluções de transformação digital previstas no PEI.', 'Compatibilizar o portfólio de TI com o objetivo de transformação digital.', 'Media'],
    ['Outro', 'Plano de Integridade da AFE', 'Incorporar as ações de integridade ao objetivo de governança e gestão de riscos.', 'Acompanhar as ações do plano nas reuniões de avaliação.', 'Baixa'],
];
foreach ($integracoes as $i => [$tipo, $instrumento, $pontos, $tarefas, $intensidade]) {
    IntegracaoInstrumento::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_instrumento' => mb_substr($instrumento, 0, 100)], [
        'dsc_tipo_instrumento' => $tipo, 'txt_pontos_atencao' => $pontos, 'txt_tarefas' => $tarefas,
        'dsc_intensidade' => $intensidade, 'num_ordem' => $i + 1,
    ]);
}

$odsDoPei = [
    5 => ['Promover a equidade de gênero na ocupação de funções de liderança da AFE.', 'Media'],
    8 => ['Valorizar e capacitar a força de trabalho, com condições dignas e produtivas.', 'Media'],
    9 => ['Modernizar a infraestrutura tecnológica e digitalizar os serviços públicos.', 'Alta'],
    10 => ['Ampliar o acesso aos serviços para populações com menor acesso digital.', 'Alta'],
    12 => ['Adotar critérios de sustentabilidade nas contratações e na logística.', 'Baixa'],
    16 => ['Fortalecer a transparência, a integridade e a efetividade da instituição.', 'Alta'],
    17 => ['Firmar parcerias com estados, municípios e sociedade civil.', 'Media'],
];
if ($pei->ods()->count() === 0) {
    $pei->ods()->sync(collect($odsDoPei)->mapWithKeys(fn ($v, $num) => [$num => ['txt_contribuicao' => $v[0], 'dsc_intensidade' => $v[1]]])->all());
}

$eventos = [
    ['Oficina de lançamento do planejamento', 'Oficina', '2023-08-10', 'Apresentar o cronograma e a metodologia às unidades.', 'Alta administração, dirigentes e pontos focais', true],
    ['Workshop de análise de ambiente (PESTEL e SWOT)', 'Workshop', '2023-09-14', 'Construir a análise de ambiente interno e externo.', 'Equipes técnicas de todas as unidades', true],
    ['Apresentação do mapa estratégico ao CGE', 'Apresentação', '2023-11-28', 'Validar missão, visão, objetivos e indicadores.', 'Comitê de Governança Estratégica', true],
    ['RAE do 1º quadrimestre de 2026', 'Reunião', '2026-05-14', 'Avaliar os resultados de janeiro a abril.', 'CGE e gestores de iniciativas', true],
    ['RAE do 2º quadrimestre de 2026', 'Reunião', '2026-09-17', 'Avaliar os resultados de maio a agosto.', 'CGE e gestores de iniciativas', true],
    ['Capacitação de gestores em indicadores', 'Capacitação', '2026-11-05', 'Nivelar conceitos de meta, polaridade e linha de base.', 'Gestores responsáveis e substitutos', false],
    ['Revisão anual do PEI 2026', 'Reunião', '2026-12-10', 'Revisar metas e iniciativas para 2027.', 'Comitê de Governança Estratégica', false],
];
foreach ($eventos as [$titulo, $tipo, $data, $objetivoEv, $participantes, $realizado]) {
    CalendarioEventoPei::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_titulo' => $titulo], [
        'dsc_tipo_evento' => $tipo, 'dte_evento' => $data, 'dsc_objetivo' => $objetivoEv,
        'dsc_participantes' => $participantes, 'bln_realizado' => $realizado,
    ]);
}

// ===========================================================================
// 4. Identidade, valores e temas norteadores
// ===========================================================================
IdentidadeEstrategica::firstOrCreate(['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], [
    'dsc_negocio' => 'Gestão de serviços públicos e apoio à execução de políticas públicas.',
    'dsc_missao' => 'Prestar serviços públicos de qualidade, simples e acessíveis, apoiando a execução de políticas que gerem valor para a sociedade.',
    'dsc_visao' => 'Ser reconhecida, até 2027, como referência em gestão pública orientada a resultados e em atendimento digital ao cidadão.',
]);
IdentidadeEstrategica::firstOrCreate(['cod_pei' => $peiAnterior->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], [
    'dsc_missao' => 'Executar com eficiência os serviços públicos sob responsabilidade da Agência, com transparência e respeito ao cidadão.',
    'dsc_visao' => 'Ser, até 2023, uma instituição moderna, eficiente e próxima do cidadão.',
]);

$valores = [
    ['Ética e integridade', 'Agir com honestidade, imparcialidade e respeito ao interesse público em todas as decisões.'],
    ['Transparência', 'Dar publicidade aos atos, aos dados e aos resultados, em linguagem simples.'],
    ['Foco no cidadão', 'Orientar processos e serviços às necessidades de quem os utiliza.'],
    ['Inovação', 'Buscar soluções criativas e baseadas em evidências para melhorar os serviços.'],
    ['Valorização das pessoas', 'Reconhecer e desenvolver os servidores como base da entrega de valor público.'],
];
foreach ($valores as [$nome, $desc]) {
    Valor::firstOrCreate(['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao, 'nom_valor' => $nome], ['dsc_valor' => $desc]);
}
foreach (['Transformação digital', 'Excelência no atendimento', 'Governança e integridade', 'Eficiência e sustentabilidade do gasto'] as $tema) {
    TemaNorteador::firstOrCreate(['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao, 'nom_tema_norteador' => $tema]);
}

// ===========================================================================
// 5. Análise de ambiente: PESTEL, SWOT, partes interessadas, cenários
// ===========================================================================
$pestel = [
    'Político' => [['Prioridade de governo para a transformação digital dos serviços públicos', 5, 'Agenda favorável a projetos de digitalização.'], ['Mudanças na alta administração a cada ciclo de governo', 3, 'Pode alterar prioridades e patrocínio dos projetos.']],
    'Econômico' => [['Restrição fiscal e contingenciamento de despesas discricionárias', 5, 'Exige priorização rigorosa do portfólio.'], ['Aumento do custo de contratações de tecnologia', 3, null]],
    'Social' => [['Crescimento da demanda por atendimento digital e remoto', 4, 'Cidadãos esperam resolver serviços sem deslocamento.'], ['Parcela da população com baixa inclusão digital', 4, 'Requer manter canais presenciais e telefônicos.'], ['Envelhecimento da força de trabalho', 3, null]],
    'Tecnológico' => [['Avanço de plataformas de identidade digital e assinatura eletrônica', 5, null], ['Uso de inteligência artificial no atendimento', 4, 'Oportunidade com cuidados de governança de dados.']],
    'Ambiental' => [['Exigência de critérios de sustentabilidade nas contratações', 3, null], ['Metas de redução de consumo de energia e papel', 2, null]],
    'Legal' => [['Lei Geral de Proteção de Dados Pessoais', 5, 'Exige adequação de processos e sistemas.'], ['Lei de Acesso à Informação e prazos de resposta', 4, null], ['Nova lei de licitações e contratos', 3, 'Mudança de procedimentos de contratação.']],
];
$ordem = 0;
foreach ($pestel as $categoria => $itens) {
    foreach ($itens as [$item, $impacto, $obs]) {
        AnaliseAmbiental::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_tipo_analise' => 'PESTEL', 'dsc_item' => $item], [
            'cod_organizacao' => $afe->cod_organizacao, 'dsc_categoria' => $categoria, 'num_impacto' => $impacto,
            'txt_observacao' => $obs, 'num_ordem' => ++$ordem,
        ]);
    }
}
$swot = [
    'Força' => [['Equipe técnica qualificada e comprometida', 5, 4, 3], ['Sistemas corporativos estáveis e integrados', 4, 3, 3], ['Cultura de planejamento consolidada no ciclo anterior', 4, 3, 2]],
    'Fraqueza' => [['Processos de atendimento pouco padronizados entre as unidades', 4, 4, 4], ['Baixa maturidade em gestão de riscos', 4, 3, 3], ['Quadro de pessoal reduzido em áreas críticas', 5, 4, 4], ['Dependência de fornecedores externos de TI', 3, 3, 3]],
    'Oportunidade' => [['Plataformas governamentais de serviços digitais e identidade', 5, 4, 4], ['Parcerias com estados e municípios para atendimento local', 4, 3, 3], ['Linhas de financiamento para modernização da gestão', 3, 2, 3]],
    'Ameaça' => [['Contingenciamento orçamentário', 5, 5, 4], ['Ataques cibernéticos a serviços públicos', 5, 4, 5], ['Perda de servidores por aposentadoria', 4, 3, 4]],
];
$ordem = 0;
foreach ($swot as $categoria => $itens) {
    foreach ($itens as [$item, $g, $u, $t]) {
        AnaliseAmbiental::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_tipo_analise' => 'SWOT', 'dsc_item' => $item], [
            'cod_organizacao' => $afe->cod_organizacao, 'dsc_categoria' => $categoria, 'num_impacto' => $g,
            'num_gravidade' => $g, 'num_urgencia' => $u, 'num_tendencia' => $t, 'num_ordem' => ++$ordem,
        ]);
    }
}
$partes = [
    ['Cidadãos usuários dos serviços', 'Externo', 5, 3, 'Pesquisas de satisfação, ouvidoria e divulgação de resultados em linguagem simples.'],
    ['Alta administração da AFE', 'Interno', 5, 5, 'Participação no CGE e nas Reuniões de Avaliação da Estratégia.'],
    ['Servidores e colaboradores', 'Interno', 4, 3, 'Comunicação interna, oficinas e reconhecimento de resultados.'],
    ['Órgãos de controle interno e externo', 'Externo', 3, 5, 'Relatórios periódicos de gestão e atendimento tempestivo às demandas.'],
    ['Órgão central de planejamento e orçamento', 'Externo', 3, 4, 'Alinhamento com o PPA e a LOA; reuniões técnicas semestrais.'],
    ['Fornecedores de tecnologia', 'Externo', 2, 2, 'Gestão contratual com acordos de nível de serviço.'],
    ['Imprensa e formadores de opinião', 'Externo', 2, 4, 'Plano de comunicação externa e porta-voz definido.'],
];
foreach ($partes as $i => [$nome, $tipo, $interesse, $influencia, $estrategia]) {
    ParteInteressada::firstOrCreate(['cod_pei' => $pei->cod_pei, 'nom_parte' => $nome], [
        'dsc_tipo' => $tipo, 'num_interesse' => $interesse, 'num_influencia' => $influencia,
        'txt_estrategia_engajamento' => $estrategia, 'num_ordem' => $i + 1,
    ]);
}
$cenarios = [
    ['Digitalização acelerada com orçamento recomposto', 'Otimista', 'Recomposição orçamentária a partir de 2026 e adesão ampla às plataformas digitais do governo.', 'Antecipação das metas de digitalização e de satisfação.', 'Ampliar o portfólio de serviços digitais e acelerar a capacitação.', 2, 4],
    ['Avanço gradual com restrição fiscal', 'Tendencial', 'Orçamento estável em termos reais, com contingenciamentos pontuais.', 'Metas alcançadas com priorização e possíveis atrasos em projetos de infraestrutura.', 'Priorizar projetos de maior impacto e buscar parcerias.', 4, 3],
    ['Crise fiscal e perda de pessoal', 'Pessimista', 'Corte de despesas discricionárias e aposentadorias sem reposição.', 'Risco de descontinuidade de projetos e queda na qualidade do atendimento.', 'Plano de contingência com foco nos serviços essenciais e automação.', 2, 5],
];
foreach ($cenarios as $i => [$nome, $tipo, $desc, $impl, $resp, $prob, $imp]) {
    CenarioProspectivo::firstOrCreate(['cod_pei' => $pei->cod_pei, 'nom_cenario' => $nome], [
        'cod_organizacao' => $afe->cod_organizacao, 'dsc_tipo' => $tipo, 'dsc_descricao' => $desc, 'txt_implicacoes' => $impl,
        'txt_resposta_estrategica' => $resp, 'num_probabilidade' => $prob, 'num_impacto' => $imp, 'num_ordem' => $i + 1,
    ]);
}

// ===========================================================================
// 6. Perspectivas, objetivos e futuro almejado
// ===========================================================================
// No BSC o nível 1 é a BASE do mapa e o maior nível é o topo (Resultados).
$perspectivas = [];
foreach ([
    ['Resultados para a Sociedade', 4, 70, 30],
    ['Processos Internos', 3, 60, 40],
    ['Pessoas e Aprendizado', 2, 50, 50],
    ['Orçamento e Logística', 1, 80, 20],
] as [$nome, $nivel, $pInd, $pPlan]) {
    $perspectiva = Perspectiva::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_perspectiva' => $nome], [
        'num_nivel_hierarquico_apresentacao' => $nivel, 'num_peso_indicadores' => $pInd, 'num_peso_planos' => $pPlan,
    ]);
    if ((int) $perspectiva->num_nivel_hierarquico_apresentacao !== $nivel) {
        $perspectiva->update(['num_nivel_hierarquico_apresentacao' => $nivel]);
    }
    $perspectivas[] = $perspectiva;
}
[$pSoc, $pProc, $pPes, $pOrc] = $perspectivas;

$o1 = objetivo($pSoc, 'Ampliar o acesso da população aos serviços públicos', 'Levar os serviços da AFE a mais pessoas, combinando canais digitais e presenciais, com atenção a quem tem menor acesso à internet.', 1, null, [10 => 'Redução das desigualdades de acesso aos serviços.', 16 => 'Instituições eficazes e acessíveis.']);
$o2 = objetivo($pSoc, 'Elevar a satisfação do cidadão com o atendimento', 'Melhorar a experiência do cidadão em todos os canais, com respostas rápidas, claras e resolutivas.', 2, null, [16 => 'Serviços públicos responsivos e de qualidade.']);
$o3 = objetivo($pSoc, 'Fortalecer a transparência e o controle social', 'Ampliar a publicação de dados e resultados e responder às demandas de informação dentro dos prazos legais.', 3, null, [16 => 'Acesso público à informação.', 17 => 'Parcerias com a sociedade civil no controle social.']);
$o4 = objetivo($pProc, 'Transformar digitalmente os serviços e processos', 'Digitalizar serviços e processos de trabalho, integrando-os às plataformas de governo digital.', 1, null, [9 => 'Infraestrutura e inovação tecnológica.']);
$o41 = objetivo($pProc, 'Digitalizar os serviços de maior demanda até 2027', 'Desdobramento do objetivo de transformação digital para os vinte serviços mais procurados.', 2, $o4, [9 => 'Inovação aplicada aos serviços mais demandados.']);
$o5 = objetivo($pProc, 'Aprimorar a governança, a gestão de riscos e a integridade', 'Consolidar instâncias de governança, a gestão de riscos e o programa de integridade.', 3, null, [16 => 'Instituições transparentes e responsáveis.']);
$o6 = objetivo($pProc, 'Reduzir o tempo de resposta às demandas do cidadão', 'Simplificar fluxos e eliminar etapas sem valor para responder mais rápido.', 4);
$o61 = objetivo($pProc, 'Padronizar o fluxo de atendimento nas unidades regionais', 'Desdobramento do objetivo de tempo de resposta para o atendimento presencial.', 5, $o6);
$o7 = objetivo($pPes, 'Desenvolver competências para a gestão orientada a resultados', 'Capacitar lideranças e equipes em planejamento, projetos, indicadores e riscos.', 1, null, [8 => 'Trabalho produtivo e qualificação profissional.']);
$o8 = objetivo($pPes, 'Promover a qualidade de vida e a equidade no trabalho', 'Cuidar da saúde dos servidores e ampliar a equidade nas funções de liderança.', 2, null, [5 => 'Equidade de gênero na liderança.', 8 => 'Trabalho decente.']);
$o9 = objetivo($pOrc, 'Assegurar a eficiência e a qualidade do gasto público', 'Priorizar o orçamento nos resultados estratégicos e gerar economia nas contratações.', 1, null, [12 => 'Uso eficiente dos recursos públicos.']);
$o10 = objetivo($pOrc, 'Ampliar a sustentabilidade das contratações e da logística', 'Incorporar critérios ambientais às compras e reduzir resíduos e consumo de recursos.', 2, null, [12 => 'Consumo e produção responsáveis.']);

foreach ([
    [$o1, 'Cerca de metade dos serviços exige atendimento presencial.', 'Nove em cada dez serviços disponíveis em canal digital, com atendimento presencial assistido.', 'Percentual de serviços ofertados em canal digital', 90, '2027-12-31'],
    [$o2, 'Satisfação medida apenas em pesquisas pontuais, com índice de 0,71.', 'Satisfação acima de 0,85, medida continuamente em todos os canais.', 'Índice de satisfação do cidadão', 0.85, '2027-12-31'],
    [$o4, 'Processos internos majoritariamente em papel e planilhas.', 'Processos críticos automatizados e integrados às plataformas de governo digital.', 'Quantidade de serviços digitalizados', 72, '2027-12-31'],
    [$o9, 'Baixa previsibilidade da execução orçamentária.', 'Orçamento executado conforme o cronograma e economia anual acima de R$ 2 milhões.', 'Valor economizado em contratações', 2000000, '2027-12-31'],
] as [$obj, $atual, $futuro, $refInd, $refMeta, $horizonte]) {
    FuturoAlmejado::firstOrCreate(['cod_objetivo' => $obj->cod_objetivo], [
        'dsc_situacao_atual' => $atual, 'dsc_futuro_almejado' => $futuro, 'dsc_indicador_referencia' => $refInd,
        'vlr_referencia_meta' => $refMeta, 'dte_horizonte' => $horizonte,
    ]);
}

$tows = [
    ['SO', 'Usar a equipe qualificada para aderir rapidamente às plataformas digitais de governo.', 'Força: equipe técnica qualificada. Oportunidade: plataformas governamentais de serviços digitais.', $o4],
    ['WO', 'Aproveitar parcerias locais para padronizar o atendimento nas unidades regionais.', 'Fraqueza: atendimento pouco padronizado. Oportunidade: parcerias com estados e municípios.', $o61],
    ['ST', 'Usar a cultura de planejamento para priorizar o portfólio diante do contingenciamento.', 'Força: cultura de planejamento. Ameaça: contingenciamento orçamentário.', $o9],
    ['WT', 'Reduzir a dependência de fornecedores e reforçar a segurança da informação.', 'Fraqueza: dependência de fornecedores de TI. Ameaça: ataques cibernéticos.', $o5],
];
foreach ($tows as [$tipo, $estrategia, $fund, $obj]) {
    EstrategiaTows::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_estrategia' => $estrategia], [
        'cod_organizacao' => $afe->cod_organizacao, 'dsc_tipo' => $tipo, 'txt_fundamentacao' => $fund, 'cod_objetivo_vinculado' => $obj->cod_objetivo,
    ]);
}

// Cadeia de valor
$cadeia = [
    ['Atendimento e relacionamento com o cidadão', 'Finalística', $pSoc, [['Solicitação do cidadão (presencial, telefone ou internet)', 'Triagem, orientação e resolução do pedido', 'Demanda resolvida e cidadão informado'], ['Manifestação na ouvidoria', 'Análise, encaminhamento à área e resposta', 'Resposta conclusiva dentro do prazo']]],
    ['Prestação de serviços digitais', 'Finalística', $pProc, [['Serviço mapeado com alta demanda', 'Redesenho, automação e publicação no portal', 'Serviço disponível em canal digital']]],
    ['Apoio à formulação e avaliação de políticas públicas', 'Finalística', $pSoc, [['Dados administrativos e pesquisas', 'Análise e elaboração de estudos técnicos', 'Notas técnicas e recomendações à alta administração']]],
    ['Gestão de pessoas', 'Suporte', $pPes, [['Necessidades de capacitação', 'Planejamento e execução do plano de desenvolvimento', 'Servidores capacitados']]],
    ['Gestão de tecnologia da informação', 'Suporte', $pProc, [['Demandas de soluções de TI', 'Priorização, desenvolvimento e sustentação', 'Sistemas disponíveis e seguros']]],
    ['Gestão orçamentária, financeira e de contratações', 'Suporte', $pOrc, [['Necessidade de aquisição', 'Planejamento da contratação e licitação', 'Contrato firmado com economia']]],
    ['Governança, riscos e integridade', 'Suporte', $pProc, []],
    ['Serviços acessíveis e resolutivos', 'Valores públicos', null, []],
    ['Confiança da sociedade na administração pública', 'Valores públicos', null, []],
];
foreach ($cadeia as $i => [$atividade, $tipo, $persp, $processos]) {
    $a = AtividadeCadeiaValor::firstOrCreate(['cod_pei' => $pei->cod_pei, 'dsc_atividade' => $atividade], [
        'dsc_tipo' => $tipo, 'cod_perspectiva' => $persp?->cod_perspectiva, 'num_ordem' => $i + 1,
    ]);
    foreach ($processos as [$entrada, $transf, $saida]) {
        ProcessoAtividadeCadeiaValor::firstOrCreate(['cod_atividade_cadeia_valor' => $a->cod_atividade_cadeia_valor, 'dsc_entrada' => $entrada], [
            'dsc_transformacao' => $transf, 'dsc_saida' => $saida,
        ]);
    }
}
linha('Ciclo vigente: identidade, ambiente e '.Objetivo::whereIn('cod_perspectiva', $pei->perspectivas()->pluck('cod_perspectiva'))->count().' objetivos');

// ===========================================================================
// 7. Ciclo anterior (PEI 2020-2023), encerrado, com alguns dados
// ===========================================================================
$antSoc = Perspectiva::firstOrCreate(['cod_pei' => $peiAnterior->cod_pei, 'dsc_perspectiva' => 'Sociedade'], ['num_nivel_hierarquico_apresentacao' => 1, 'num_peso_indicadores' => 100, 'num_peso_planos' => 0]);
$antProc = Perspectiva::firstOrCreate(['cod_pei' => $peiAnterior->cod_pei, 'dsc_perspectiva' => 'Processos Internos'], ['num_nivel_hierarquico_apresentacao' => 3, 'num_peso_indicadores' => 100, 'num_peso_planos' => 0]);
$antPes = Perspectiva::firstOrCreate(['cod_pei' => $peiAnterior->cod_pei, 'dsc_perspectiva' => 'Aprendizado e Crescimento'], ['num_nivel_hierarquico_apresentacao' => 3, 'num_peso_indicadores' => 100, 'num_peso_planos' => 0]);
$ao1 = objetivo($antSoc, 'Melhorar a qualidade do atendimento presencial', 'Reduzir filas e retrabalho no atendimento presencial.', 1);
$ao2 = objetivo($antProc, 'Informatizar os processos administrativos', 'Substituir processos em papel por processos eletrônicos.', 1);
$ao3 = objetivo($antPes, 'Capacitar os servidores nas competências essenciais', 'Implantar o plano anual de capacitação.', 1);

$antMetas = [2020 => 40, 2021 => 60, 2022 => 80, 2023 => 100];
$ia1 = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $ao2->cod_objetivo, 'nom_indicador' => 'Percentual de processos administrativos eletrônicos', 'dsc_indicador' => 'Proporção de processos administrativos que tramitam em meio eletrônico.', 'dsc_meta' => '100% dos processos eletrônicos até 2023', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => '(processos eletrônicos / total de processos) × 100', 'dsc_fonte' => 'Sistema de processo eletrônico'], [$afe->cod_organizacao], $antMetas, [2019 => 18]);
$ia2 = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $ao1->cod_objetivo, 'nom_indicador' => 'Tempo médio de espera no atendimento presencial', 'dsc_indicador' => 'Tempo médio, em minutos convertidos em horas, entre a chegada e o início do atendimento.', 'dsc_meta' => 'Reduzir a espera para meia hora', 'dsc_unidade_medida' => 'Horas (h)', 'dsc_polaridade' => 'Negativa', 'bln_acumulado' => 'Não', 'dsc_formula' => 'Soma dos tempos de espera / número de atendimentos', 'dsc_fonte' => 'Sistema de gestão de filas'], [$dac->cod_organizacao], [2020 => 1.5, 2021 => 1.0, 2022 => 0.75, 2023 => 0.5], [2019 => 1.8]);
$ia3 = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $ao3->cod_objetivo, 'nom_indicador' => 'Servidores capacitados no ano', 'dsc_indicador' => 'Número de servidores que concluíram ao menos uma ação de capacitação.', 'dsc_meta' => 'Capacitar 360 servidores por ano', 'dsc_unidade_medida' => 'Quantidade (un)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Sim', 'dsc_formula' => 'Contagem de servidores com certificado no ano', 'dsc_fonte' => 'Sistema de gestão de pessoas'], [$spg->cod_organizacao], [2020 => 240, 2021 => 300, 2022 => 360, 2023 => 360], [2019 => 150]);
$evAnt = 0;
$evAnt += lancarEvolucoes($ia1, $antMetas, [2020 => 0.95, 2021 => 0.97, 2022 => 0.92, 2023 => 0.94], $LIMITE_EVOLUCAO);
$evAnt += lancarEvolucoes($ia2, [2020 => 1.5, 2021 => 1.0, 2022 => 0.75, 2023 => 0.5], [2020 => 0.8, 2021 => 0.85, 2022 => 0.9, 2023 => 1.05], $LIMITE_EVOLUCAO);
$evAnt += lancarEvolucoes($ia3, [2020 => 240, 2021 => 300, 2022 => 360, 2023 => 360], [2020 => 0.7, 2021 => 0.9, 2022 => 1.05, 2023 => 1.1], $LIMITE_EVOLUCAO);

$pa1 = iniciativa(['dsc_plano_de_acao' => 'Implantação do processo administrativo eletrônico', 'cod_objetivo' => $ao2->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::PROJETO, 'dte_inicio' => '2020-03-01', 'dte_fim' => '2022-12-16', 'bln_status' => 'Concluído', 'vlr_orcamento_previsto' => 850000, 'txt_detalhamento' => 'Implantação do sistema de processo eletrônico em todas as unidades.'], [$spg->cod_organizacao]);
$pa2 = iniciativa(['dsc_plano_de_acao' => 'Reorganização das filas de atendimento presencial', 'cod_objetivo' => $ao1->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::ACAO, 'dte_inicio' => '2021-02-01', 'dte_fim' => '2023-06-30', 'bln_status' => 'Concluído', 'txt_detalhamento' => 'Agendamento prévio e triagem na recepção.'], [$dac->cod_organizacao]);
foreach ([[$pa1, 'Contratação da solução de processo eletrônico', '2020-09-30'], [$pa1, 'Migração dos processos em andamento', '2022-10-31'], [$pa2, 'Implantação do agendamento prévio', '2022-03-31']] as [$pl, $desc, $prazo]) {
    entrega($pl, ['dsc_entrega' => $desc, 'bln_status' => 'Concluído', 'dte_prazo' => $prazo]);
}
risco(['cod_pei' => $peiAnterior->cod_pei, 'cod_organizacao' => $spg->cod_organizacao, 'dsc_titulo' => 'Resistência das unidades ao processo eletrônico', 'txt_descricao' => 'Unidades poderiam manter processos em papel após a implantação.', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Encerrado', 'num_probabilidade' => 3, 'num_impacto' => 3, 'dsc_estrategia_resposta' => 'Mitigar', 'cod_responsavel_monitoramento' => $carla->id], [$ao2]);
risco(['cod_pei' => $peiAnterior->cod_pei, 'cod_organizacao' => $dac->cod_organizacao, 'dsc_titulo' => 'Indisponibilidade do sistema de filas', 'txt_descricao' => 'Falhas no sistema de senhas poderiam paralisar o atendimento presencial.', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Encerrado', 'num_probabilidade' => 2, 'num_impacto' => 4, 'dsc_estrategia_resposta' => 'Mitigar', 'cod_responsavel_monitoramento' => $juliana->id], [$ao1]);
linha("Ciclo anterior: {$evAnt} evoluções novas");

// ===========================================================================
// 8. Indicadores do ciclo vigente
// ===========================================================================
$anosCiclo = [2024, 2025, 2026, 2027];
$m = fn (array $v) => array_combine($anosCiclo, $v);

$ind = [];
$ind['digital'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o1->cod_objetivo, 'nom_indicador' => 'Percentual de serviços ofertados em canal digital', 'dsc_indicador' => 'Proporção dos serviços do catálogo que podem ser solicitados integralmente pela internet.', 'dsc_meta' => '90% dos serviços em canal digital até 2027', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => '(serviços digitais / total de serviços do catálogo) × 100', 'dsc_fonte' => 'Catálogo de serviços da AFE', 'num_peso' => 3, 'dsc_referencial_comparativo' => 'Média dos órgãos federais de porte semelhante: 72%'], [$afe->cod_organizacao], $m([60, 70, 80, 90]), [2023 => 52]);
$ind['satisfacao'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o2->cod_objetivo, 'nom_indicador' => 'Índice de satisfação do cidadão', 'dsc_indicador' => 'Média das avaliações dos atendimentos, normalizada entre 0 e 1.', 'dsc_meta' => 'Índice de 0,85 até 2027', 'dsc_unidade_medida' => 'Índice (0-1)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => 'Média das notas (1 a 5) convertida para a escala 0 a 1', 'dsc_fonte' => 'Pesquisa de satisfação pós-atendimento', 'num_peso' => 3], [$afe->cod_organizacao, $dac->cod_organizacao], $m([0.75, 0.78, 0.80, 0.85]), [2023 => 0.71]);
$ind['resposta'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o6->cod_objetivo, 'nom_indicador' => 'Tempo médio de resposta às manifestações', 'dsc_indicador' => 'Dias corridos entre o registro da manifestação e a resposta conclusiva.', 'dsc_meta' => 'Responder em até 15 dias até 2027', 'dsc_unidade_medida' => 'Dias', 'dsc_polaridade' => 'Negativa', 'bln_acumulado' => 'Não', 'dsc_formula' => 'Soma dos dias de resposta / número de manifestações respondidas', 'dsc_fonte' => 'Sistema de ouvidoria', 'num_peso' => 2], [$dac->cod_organizacao], $m([30, 25, 20, 15]), [2023 => 35]);
$ind['economia'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o9->cod_objetivo, 'nom_indicador' => 'Valor economizado em contratações', 'dsc_indicador' => 'Diferença entre o valor estimado e o valor contratado nas licitações do ano.', 'dsc_meta' => 'Economizar R$ 2 milhões por ano até 2027', 'dsc_unidade_medida' => 'Monetário (R$)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Sim', 'dsc_formula' => 'Σ (valor estimado − valor contratado)', 'dsc_fonte' => 'Sistema de compras', 'num_peso' => 2], [$spg->cod_organizacao], $m([1200000, 1500000, 1800000, 2000000]), [2023 => 950000]);
$ind['servicos'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o4->cod_objetivo, 'nom_indicador' => 'Quantidade de serviços digitalizados no ano', 'dsc_indicador' => 'Número de serviços redesenhados e publicados em canal digital no ano.', 'dsc_meta' => '72 serviços digitalizados no ciclo', 'dsc_unidade_medida' => 'Quantidade (un)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Sim', 'dsc_formula' => 'Contagem de serviços publicados no portal no ano', 'dsc_fonte' => 'Portal de serviços', 'num_peso' => 2], [$dti->cod_organizacao], $m([12, 24, 24, 12]), [2023 => 8]);
$ind['riscos'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o5->cod_objetivo, 'nom_indicador' => 'Percentual de riscos estratégicos com plano de tratamento', 'dsc_indicador' => 'Riscos de nível alto ou crítico que possuem plano de tratamento aprovado.', 'dsc_meta' => '95% dos riscos relevantes tratados até 2027', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => '(riscos com plano / riscos altos e críticos) × 100', 'dsc_fonte' => 'Módulo de riscos do Sistema PEI'], [$afe->cod_organizacao], $m([70, 80, 90, 95]), [2023 => 40]);
$ind['capacitacao'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o7->cod_objetivo, 'nom_indicador' => 'Horas de capacitação por servidor', 'dsc_indicador' => 'Média de horas de capacitação concluídas por servidor no ano.', 'dsc_meta' => '30 horas por servidor ao ano', 'dsc_unidade_medida' => 'Horas (h)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Sim', 'dsc_formula' => 'Total de horas certificadas / número de servidores', 'dsc_fonte' => 'Sistema de gestão de pessoas'], [$spg->cod_organizacao], $m([24, 24, 30, 30]), [2023 => 16]);
$ind['absenteismo'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o8->cod_objetivo, 'nom_indicador' => 'Taxa de absenteísmo', 'dsc_indicador' => 'Dias de ausência não programada sobre os dias de trabalho previstos.', 'dsc_meta' => 'Taxa de 0,03 até 2027', 'dsc_unidade_medida' => 'Taxa', 'dsc_polaridade' => 'Negativa', 'bln_acumulado' => 'Não', 'dsc_formula' => 'dias de ausência / dias de trabalho previstos', 'dsc_fonte' => 'Sistema de frequência'], [$spg->cod_organizacao], $m([0.045, 0.040, 0.035, 0.030]), [2023 => 0.052]);
$ind['residuos'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o10->cod_objetivo, 'nom_indicador' => 'Resíduos recicláveis destinados à coleta seletiva', 'dsc_indicador' => 'Massa de resíduos recicláveis entregue a cooperativas no ano.', 'dsc_meta' => '14,4 toneladas em 2027', 'dsc_unidade_medida' => 'Toneladas (t)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Sim', 'dsc_formula' => 'Soma das pesagens mensais', 'dsc_fonte' => 'Relatórios das cooperativas'], [$afe->cod_organizacao], $m([9.6, 10.8, 12, 14.4]), [2023 => 7.2]);
$ind['lai'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o3->cod_objetivo, 'nom_indicador' => 'Pedidos de acesso à informação respondidos no prazo', 'dsc_indicador' => 'Pedidos respondidos dentro do prazo legal sobre o total de pedidos recebidos.', 'dsc_meta' => '99% no prazo até 2027', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => '(pedidos no prazo / pedidos recebidos) × 100', 'dsc_fonte' => 'Plataforma de acesso à informação'], [$dac->cod_organizacao], $m([95, 97, 98, 99]), [2023 => 91]);
$ind['reclamacoes'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o61->cod_objetivo, 'nom_indicador' => 'Reclamações sobre o atendimento presencial', 'dsc_indicador' => 'Número mensal de reclamações registradas sobre o atendimento nas unidades regionais.', 'dsc_meta' => 'No máximo 60 reclamações por mês em 2027', 'dsc_unidade_medida' => 'Nº de Ocorrências', 'dsc_polaridade' => 'Negativa', 'bln_acumulado' => 'Não', 'dsc_formula' => 'Contagem de reclamações no mês', 'dsc_fonte' => 'Sistema de ouvidoria'], [$dac->cod_organizacao], $m([120, 100, 80, 60]), [2023 => 135]);
$ind['maturidade'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o41->cod_objetivo, 'nom_indicador' => 'Índice de maturidade em governo digital', 'dsc_indicador' => 'Pontuação obtida na autoavaliação anual de maturidade em governo digital.', 'dsc_meta' => '75 pontos até 2027', 'dsc_unidade_medida' => 'Pontos', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => 'Soma ponderada das dimensões do questionário', 'dsc_fonte' => 'Autoavaliação anual', 'dsc_periodo_medicao' => 'Anual'], [$dti->cod_organizacao], $m([60, 65, 70, 75]), [2023 => 55]);
$ind['desembolso'] = indicador(['dsc_tipo' => 'Objetivo', 'cod_objetivo' => $o9->cod_objetivo, 'nom_indicador' => 'Aderência ao cronograma de desembolso', 'dsc_indicador' => 'Razão entre o valor pago e o valor programado no cronograma financeiro do mês.', 'dsc_meta' => 'Manter a razão próxima de 1,0', 'dsc_unidade_medida' => 'Proporção', 'dsc_polaridade' => 'Estabilidade', 'bln_acumulado' => 'Não', 'dsc_formula' => 'valor pago / valor programado', 'dsc_fonte' => 'Sistema de administração financeira'], [$spg->cod_organizacao], $m([1, 1, 1, 1]), []);
linha('Indicadores de objetivo: '.count($ind));

// ===========================================================================
// 9. Iniciativas (relógio: 21 dias atrás, pela Administradora da SPG)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(21)->setTime(10, 15));
logar($carla, $spg->cod_organizacao);

$p1 = iniciativa(['dsc_plano_de_acao' => 'Implantação do Portal Único de Serviços ao Cidadão', 'cod_objetivo' => $o4->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::PROJETO, 'dte_inicio' => '2024-03-01', 'dte_fim' => '2026-12-15', 'bln_status' => 'Em Andamento', 'vlr_orcamento_previsto' => 2400000, 'cod_ppa' => 'Programa 2101 — Gestão e Manutenção', 'cod_loa' => 'Ação 21C0 — Transformação Digital', 'txt_detalhamento' => 'Reunir em um único portal os serviços da Agência, com login pela identidade digital do cidadão, acompanhamento do pedido e avaliação do atendimento.', 'json_modelo_logico' => ['insumos' => 'Equipe de produto, contrato de desenvolvimento, orçamento de R$ 2,4 milhões.', 'atividades' => 'Redesenho dos serviços, desenvolvimento do portal, integração com a identidade digital e capacitação.', 'resultados' => 'Serviços solicitados e acompanhados pela internet.', 'impacto' => 'Mais acesso e menor custo para o cidadão.', 'pressupostos' => 'Manutenção do orçamento e adesão das unidades.']], [$spg->cod_organizacao, $dti->cod_organizacao]);
$p2 = iniciativa(['dsc_plano_de_acao' => 'Programa de Capacitação em Gestão por Resultados', 'cod_objetivo' => $o7->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::ACAO, 'dte_inicio' => '2024-02-01', 'dte_fim' => '2025-12-19', 'bln_status' => 'Concluído', 'vlr_orcamento_previsto' => 180000, 'txt_detalhamento' => 'Trilhas de capacitação em planejamento, projetos, indicadores e riscos para lideranças e equipes.'], [$spg->cod_organizacao]);
$p5 = iniciativa(['dsc_plano_de_acao' => 'Implantação da Política de Gestão de Riscos e Integridade', 'cod_objetivo' => $o5->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::PROJETO, 'dte_inicio' => '2024-06-03', 'dte_fim' => '2026-12-18', 'bln_status' => 'Em Andamento', 'vlr_orcamento_previsto' => 320000, 'txt_detalhamento' => 'Publicar a política, capacitar as unidades e implantar o registro e o monitoramento dos riscos estratégicos.'], [$spg->cod_organizacao]);
$p6 = iniciativa(['dsc_plano_de_acao' => 'Programa de Eficiência Energética e Logística Sustentável', 'cod_objetivo' => $o10->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::ACAO, 'dte_inicio' => '2025-04-01', 'dte_fim' => '2027-06-30', 'bln_status' => 'Suspenso', 'vlr_orcamento_previsto' => 450000, 'txt_detalhamento' => 'Troca de iluminação, coleta seletiva e critérios ambientais nas compras. Suspenso por contingenciamento em 2026.'], [$spg->cod_organizacao]);
$p8 = iniciativa(['dsc_plano_de_acao' => 'Revisão do modelo de contratações compartilhadas', 'cod_objetivo' => $o9->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::ACAO, 'dte_inicio' => '2024-04-01', 'dte_fim' => '2025-03-31', 'bln_status' => 'Cancelado', 'txt_detalhamento' => 'Cancelado após a adesão da Agência à central de compras do governo, que tornou o modelo próprio desnecessário.'], [$spg->cod_organizacao]);

// Indicador de iniciativa calculado pelas entregas (antes das entregas, para o observer atualizar)
$ind['entregasPortal'] = indicador(['dsc_tipo' => 'Iniciativa', 'cod_plano_de_acao' => $p1->cod_plano_de_acao, 'nom_indicador' => 'Percentual de entregas do Portal Único concluídas', 'dsc_indicador' => 'Calculado automaticamente a partir da situação das entregas da iniciativa.', 'dsc_meta' => '100% das entregas até dezembro de 2026', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_calculation_type' => 'action_plan', 'dsc_fonte' => 'Sistema PEI — entregas da iniciativa'], [$spg->cod_organizacao], [2026 => 100], []);

$labelsP1 = [];
foreach ([['Prioridade da alta gestão', '#ef4444', 'star-fill'], ['Dependência de TI', '#3b82f6', 'cpu'], ['Comunicação', '#22c55e', 'megaphone']] as $i => [$rotulo, $cor, $icone]) {
    $labelsP1[$rotulo] = EntregaLabel::firstOrCreate(['cod_plano_de_acao' => $p1->cod_plano_de_acao, 'dsc_label' => $rotulo], ['dsc_cor' => $cor, 'dsc_icone' => $icone, 'num_ordem' => $i + 1]);
}

// ===========================================================================
// 10. Iniciativas da DTI e da DAC (relógio: 14 dias atrás)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(14)->setTime(11, 0));
$p3 = iniciativa(['dsc_plano_de_acao' => 'Modernização da infraestrutura em nuvem e da segurança da informação', 'cod_objetivo' => $o41->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::PROJETO, 'dte_inicio' => '2025-01-15', 'dte_fim' => '2026-08-31', 'bln_status' => 'Em Andamento', 'vlr_orcamento_previsto' => 1350000, 'cod_loa' => 'Ação 21C0 — Transformação Digital', 'txt_detalhamento' => 'Migrar os sistemas críticos para nuvem de governo e implantar monitoramento de segurança contínuo.'], [$dti->cod_organizacao]);
$p4 = iniciativa(['dsc_plano_de_acao' => 'Padronização do atendimento presencial nas unidades regionais', 'cod_objetivo' => $o61->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::ACAO, 'dte_inicio' => '2025-03-03', 'dte_fim' => '2026-11-30', 'bln_status' => 'Em Andamento', 'vlr_orcamento_previsto' => 260000, 'txt_detalhamento' => 'Protocolo único de atendimento, agendamento obrigatório e painel de filas em todas as unidades regionais.'], [$dac->cod_organizacao]);
$p7 = iniciativa(['dsc_plano_de_acao' => 'Central de Atendimento Omnicanal', 'cod_objetivo' => $o2->cod_objetivo, 'cod_tipo_execucao' => TipoExecucao::PROJETO, 'dte_inicio' => '2026-11-03', 'dte_fim' => '2027-10-29', 'bln_status' => 'Não Iniciado', 'vlr_orcamento_previsto' => 980000, 'txt_detalhamento' => 'Integrar telefone, chat, e-mail e aplicativo de mensagens em uma única central com histórico do cidadão.'], [$dac->cod_organizacao]);
$ind['disponibilidade'] = indicador(['dsc_tipo' => 'Iniciativa', 'cod_plano_de_acao' => $p3->cod_plano_de_acao, 'nom_indicador' => 'Disponibilidade dos sistemas críticos', 'dsc_indicador' => 'Percentual do tempo em que os sistemas críticos ficaram disponíveis no mês.', 'dsc_meta' => '99% de disponibilidade', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'bln_acumulado' => 'Não', 'dsc_formula' => '(horas disponíveis / horas do mês) × 100', 'dsc_fonte' => 'Ferramenta de monitoramento'], [$dti->cod_organizacao], [2025 => 99, 2026 => 99], [2024 => 97.2]);

// Gestores das iniciativas (vínculo de perfil por iniciativa, como a tela de Gestores grava)
foreach ([$p1, $p2, $p3, $p4, $p5, $p7] as $pl) {
    vincularPerfil($rafael, $pl->cod_organizacao, PerfilAcesso::GESTOR_RESPONSAVEL, $pl->cod_plano_de_acao);
}
foreach ([$p1, $p4, $p5] as $pl) {
    vincularPerfil($juliana, $pl->cod_organizacao, PerfilAcesso::GESTOR_SUBSTITUTO, $pl->cod_plano_de_acao);
}

// ===========================================================================
// 11. Evoluções mensais (jan/2024 até o mês anterior ao atual)
// ===========================================================================
$aval = function (string $chave) use ($LIMITE_EVOLUCAO): array {
    $ultimo = $LIMITE_EVOLUCAO->copy()->subMonth();
    $textos = [
        'superado' => 'Resultado acima da meta no período. Manter as práticas que sustentaram o desempenho.',
        'bom' => 'Resultado próximo da meta. Acompanhar a tendência nos próximos meses.',
        'atencao' => 'Resultado abaixo do esperado. Plano de ação em revisão pela unidade responsável.',
        'critico' => 'Resultado crítico. Tema levado à Reunião de Avaliação da Estratégia para encaminhamento.',
    ];

    return [$ultimo->year.'-'.$ultimo->month => $textos[$chave]];
};
$evolucoes = 0;
$evolucoes += lancarEvolucoes($ind['digital'], $m([60, 70, 80, 90]), [2024 => 0.93, 2025 => 0.96, 2026 => 0.89], $LIMITE_EVOLUCAO, $aval('bom'));
$evolucoes += lancarEvolucoes($ind['satisfacao'], $m([0.75, 0.78, 0.80, 0.85]), [2024 => 0.98, 2025 => 1.02, 2026 => 1.05], $LIMITE_EVOLUCAO, $aval('superado'));
$evolucoes += lancarEvolucoes($ind['resposta'], $m([30, 25, 20, 15]), [2024 => 0.88, 2025 => 0.8, 2026 => 0.71], $LIMITE_EVOLUCAO, $aval('atencao'));
$evolucoes += lancarEvolucoes($ind['economia'], $m([1200000, 1500000, 1800000, 2000000]), [2024 => 0.9, 2025 => 0.72, 2026 => 0.4], $LIMITE_EVOLUCAO, $aval('critico'));
$evolucoes += lancarEvolucoes($ind['servicos'], $m([12, 24, 24, 12]), [2024 => 1.1, 2025 => 0.96, 2026 => 0.9], $LIMITE_EVOLUCAO, $aval('bom'));
$evolucoes += lancarEvolucoes($ind['riscos'], $m([70, 80, 90, 95]), [2024 => 0.75, 2025 => 0.7, 2026 => 0.62], $LIMITE_EVOLUCAO, $aval('atencao'));
$evolucoes += lancarEvolucoes($ind['capacitacao'], $m([24, 24, 30, 30]), [2024 => 1.05, 2025 => 1.08, 2026 => 1.12], $LIMITE_EVOLUCAO, $aval('superado'));
$evolucoes += lancarEvolucoes($ind['absenteismo'], $m([0.045, 0.040, 0.035, 0.030]), [2024 => 0.85, 2025 => 0.6, 2026 => 0.44], $LIMITE_EVOLUCAO, $aval('critico'));
$evolucoes += lancarEvolucoes($ind['residuos'], $m([9.6, 10.8, 12, 14.4]), [2024 => 0.92, 2025 => 0.9], $LIMITE_EVOLUCAO); // sem medição em 2026
$evolucoes += lancarEvolucoes($ind['lai'], $m([95, 97, 98, 99]), [2024 => 0.97, 2025 => 0.98, 2026 => 0.97], $LIMITE_EVOLUCAO, $aval('bom'));
$evolucoes += lancarEvolucoes($ind['reclamacoes'], $m([120, 100, 80, 60]), [2024 => 0.9, 2025 => 1.0, 2026 => 1.14], $LIMITE_EVOLUCAO, $aval('superado'));
$evolucoes += lancarEvolucoes($ind['desembolso'], $m([1, 1, 1, 1]), [2024 => 0.9, 2025 => 0.95, 2026 => 0.93], $LIMITE_EVOLUCAO, $aval('bom'));
$evolucoes += lancarEvolucoes($ind['disponibilidade'], [2025 => 99, 2026 => 99], [2025 => 0.985, 2026 => 0.97], $LIMITE_EVOLUCAO, $aval('bom'));
// "Índice de maturidade em governo digital": sem nenhuma medição (aparece como "sem medição").
linha("Evoluções novas do ciclo vigente: {$evolucoes}");

// Evidência anexada a uma evolução
$evSatisfacao = EvolucaoIndicador::where('cod_indicador', $ind['satisfacao']->cod_indicador)->orderByDesc('num_ano')->orderByDesc('num_mes')->first();
if ($evSatisfacao && ! Arquivo::where('cod_evolucao_indicador', $evSatisfacao->cod_evolucao_indicador)->exists()) {
    $pdf = pdfDemonstracao('Relatório da pesquisa de satisfação do cidadão', ['Consolidação das avaliações registradas após os atendimentos do período.', 'Amostra: 4.812 avaliações. Nota média 4,3 de 5 (índice 0,84).']);
    $caminho = 'pei/evidencias/'.Str::uuid().'.pdf';
    Storage::disk('local')->put($caminho, $pdf);
    Arquivo::create(['cod_evolucao_indicador' => $evSatisfacao->cod_evolucao_indicador, 'txt_assunto' => 'relatorio-pesquisa-satisfacao.pdf', 'data' => now()->format('Y-m-d'), 'dsc_nome_arquivo' => $caminho, 'dsc_tipo' => 'pdf']);
}

// ===========================================================================
// 12. Entregas, comentários, RACI, comunicação e lições (pelo Gestor Responsável)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(12)->setTime(14, 30));
logar($rafael, $spg->cod_organizacao);

$cab = entrega($p1, ['dsc_entrega' => 'Fase 1 — Descoberta e desenho dos serviços', 'dsc_tipo' => 'heading', 'bln_status' => 'Concluído']);
$e11 = entrega($p1, ['dsc_entrega' => 'Mapear os 20 serviços de maior demanda', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-06-28', 'cod_prioridade' => 'alta', 'num_peso' => 10], [$rafael]);
$e12 = entrega($p1, ['dsc_entrega' => 'Pesquisa com usuários sobre a jornada de atendimento', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-08-30', 'num_peso' => 10], [$juliana]);
$e13 = entrega($p1, ['dsc_entrega' => 'Contratar a fábrica de software do portal', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-11-29', 'cod_prioridade' => 'urgente', 'num_peso' => 15], [$carla, $rafael]);
$e14 = entrega($p1, ['dsc_entrega' => 'Integrar o portal à identidade digital do cidadão', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-07-31', 'cod_prioridade' => 'alta', 'num_peso' => 20, 'json_propriedades' => ['5w2h' => ['what' => 'Integração do login do portal à identidade digital do governo.', 'why' => 'Dispensar cadastro próprio e aumentar a segurança do acesso.', 'who' => 'DTI, com apoio da fábrica de software.', 'where' => 'Ambiente de homologação e produção do portal.', 'when' => 'Até 31/07/2026.', 'how' => 'Uso do protocolo OpenID Connect e testes com usuários.', 'howmuch' => 'R$ 180.000,00 do contrato vigente.']]], [$rafael]);
$e141 = entrega($p1, ['dsc_entrega' => 'Homologar o fluxo de login com o nível prata', 'bln_status' => 'Concluído', 'dte_prazo' => '2026-05-29'], [$rafael], $e14);
$e142 = entrega($p1, ['dsc_entrega' => 'Publicar o login em produção', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-07-31'], [$rafael], $e14);
$e15 = entrega($p1, ['dsc_entrega' => 'Publicar os 10 primeiros serviços no portal', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-10-30', 'cod_prioridade' => 'alta', 'num_peso' => 20, 'json_propriedades' => ['5w2h' => ['what' => 'Publicação dos dez primeiros serviços redesenhados.', 'why' => 'Entregar valor ao cidadão ainda em 2026.', 'who' => 'Equipe de produto e áreas donas dos serviços.', 'where' => 'Portal Único de Serviços.', 'when' => 'Até 30/10/2026.', 'how' => 'Publicação em ondas de dois serviços por quinzena.', 'howmuch' => 'Sem custo adicional.']]], [$juliana, $rafael]);
$e16 = entrega($p1, ['dsc_entrega' => 'Campanha de divulgação do portal', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2026-11-27', 'cod_prioridade' => 'media', 'num_peso' => 10], [$juliana]);
$e17 = entrega($p1, ['dsc_entrega' => 'Aplicativo móvel do portal', 'bln_status' => 'Suspenso', 'dte_prazo' => '2026-12-15', 'cod_prioridade' => 'baixa', 'num_peso' => 15], [$rafael]);
$e18 = entrega($p1, ['dsc_entrega' => 'Chat com atendente humano no portal', 'bln_status' => 'Cancelado', 'dte_prazo' => '2026-09-30', 'cod_prioridade' => 'baixa'], [$rafael]);

foreach ([[$e11, 'Prioridade da alta gestão'], [$e14, 'Dependência de TI'], [$e14, 'Prioridade da alta gestão'], [$e15, 'Dependência de TI'], [$e16, 'Comunicação']] as [$ent, $rotulo]) {
    $ent->labels()->syncWithoutDetaching([$labelsP1[$rotulo]->cod_label]);
}

entrega($p2, ['dsc_entrega' => 'Trilha de capacitação para lideranças', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-09-30'], [$rafael]);
entrega($p2, ['dsc_entrega' => 'Curso de indicadores e metas para equipes', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-05-30'], [$juliana]);
entrega($p2, ['dsc_entrega' => 'Avaliação de reação e de impacto das turmas', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-12-12'], [$rafael]);

entrega($p3, ['dsc_entrega' => 'Inventário e classificação dos sistemas críticos', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-04-30', 'cod_prioridade' => 'alta'], [$rafael]);
entrega($p3, ['dsc_entrega' => 'Migrar os sistemas críticos para a nuvem de governo', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-06-30', 'cod_prioridade' => 'urgente'], [$rafael]);
entrega($p3, ['dsc_entrega' => 'Implantar o centro de monitoramento de segurança', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2026-08-31', 'cod_prioridade' => 'alta'], [$rafael]);
entrega($p3, ['dsc_entrega' => 'Plano de continuidade de negócios de TI', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-12-11'], [$rafael]);

entrega($p5, ['dsc_entrega' => 'Publicar a Política de Gestão de Riscos', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-09-30'], [$carla]);
entrega($p5, ['dsc_entrega' => 'Capacitar os pontos focais de riscos das unidades', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-03-31'], [$rafael]);
entrega($p5, ['dsc_entrega' => 'Registrar os riscos estratégicos no Sistema PEI', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-08-28', 'cod_prioridade' => 'alta'], [$rafael, $juliana]);
entrega($p5, ['dsc_entrega' => 'Primeiro relatório anual de riscos e integridade', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2026-12-18'], [$juliana]);

entrega($p6, ['dsc_entrega' => 'Substituir a iluminação por lâmpadas LED na sede', 'bln_status' => 'Suspenso', 'dte_prazo' => '2026-06-30'], [$carla]);
entrega($p6, ['dsc_entrega' => 'Implantar a coleta seletiva solidária', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-08-29'], [$carla]);
entrega($p8, ['dsc_entrega' => 'Diagnóstico das contratações compartilhadas', 'bln_status' => 'Concluído', 'dte_prazo' => '2024-07-31'], [$carla]);
entrega($p8, ['dsc_entrega' => 'Minuta do novo modelo de contratações', 'bln_status' => 'Cancelado', 'dte_prazo' => '2025-02-28'], [$carla]);

momento($REAL_AGORA->copy()->subDays(10)->setTime(9, 40));
logar($juliana, $dac->cod_organizacao);
entrega($p4, ['dsc_entrega' => 'Protocolo único de atendimento presencial', 'bln_status' => 'Concluído', 'dte_prazo' => '2025-08-29', 'cod_prioridade' => 'alta'], [$juliana]);
$e42 = entrega($p4, ['dsc_entrega' => 'Agendamento obrigatório em todas as unidades regionais', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-09-15', 'cod_prioridade' => 'urgente', 'json_propriedades' => ['5w2h' => ['what' => 'Agendamento prévio obrigatório para os serviços presenciais.', 'why' => 'Reduzir filas e o tempo de espera.', 'who' => 'Coordenações regionais de atendimento.', 'where' => 'Todas as unidades regionais.', 'when' => 'Até 15/09/2026.', 'how' => 'Agenda no portal e na central telefônica, com cota para demanda espontânea.', 'howmuch' => 'R$ 40.000,00 em adequação de sistemas.']]], [$juliana]);
entrega($p4, ['dsc_entrega' => 'Painel de filas nas recepções', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2026-08-31'], [$juliana]);
entrega($p4, ['dsc_entrega' => 'Treinamento das equipes de recepção', 'bln_status' => 'Em Andamento', 'dte_prazo' => '2026-11-30'], [$juliana, $rafael]);
entrega($p7, ['dsc_entrega' => 'Estudo técnico preliminar da central', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2027-01-29'], [$juliana]);
entrega($p7, ['dsc_entrega' => 'Termo de referência da contratação', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2027-04-30'], [$juliana]);

// Comentários com resposta
$comentarios = [
    [$e14, $juliana, 'A equipe da identidade digital pediu mais duas semanas para liberar o ambiente de produção. Isso afeta o prazo de julho?', $rafael, 'Afeta. Vou propor no CGE a repactuação do prazo para setembro e registrar o risco.'],
    [$e42, $rafael, 'Duas unidades regionais ainda não aderiram ao agendamento. Precisamos de apoio da direção?', $juliana, 'Sim. Já agendei reunião com os coordenadores regionais para a próxima semana.'],
];
foreach ($comentarios as [$ent, $autor, $texto, $quemResponde, $resposta]) {
    if (! EntregaComentario::where('cod_entrega', $ent->cod_entrega)->exists()) {
        logar($autor);
        $c = $ent->comentarios()->create(['cod_usuario' => $autor->id, 'dsc_comentario' => $texto]);
        $ent->registrarHistorico('comment_added');
        logar($quemResponde);
        $ent->comentarios()->create(['cod_usuario' => $quemResponde->id, 'dsc_comentario' => $resposta, 'cod_comentario_pai' => $c->cod_comentario]);
        $ent->registrarHistorico('comment_added');
    }
}

// Anexo em uma entrega
logar($rafael);
if (! EntregaAnexo::where('cod_entrega', $e11->cod_entrega)->exists()) {
    $pdf = pdfDemonstracao('Mapeamento dos serviços de maior demanda', ['Lista dos vinte serviços mais procurados em 2023, com volume anual, canal atual e complexidade.']);
    $caminho = EntregaAnexo::PASTA.'/'.Str::uuid().'.pdf';
    Storage::disk(EntregaAnexo::DISCO)->put($caminho, $pdf);
    EntregaAnexo::create(['cod_entrega' => $e11->cod_entrega, 'cod_usuario' => $rafael->id, 'dsc_nome_arquivo' => 'mapeamento-servicos.pdf', 'dsc_caminho' => $caminho, 'dsc_mime_type' => 'application/pdf', 'num_tamanho_bytes' => strlen($pdf), 'dsc_descricao' => 'Planilha consolidada do mapeamento']);
    $e11->registrarHistorico('attachment_added');
}

// RACI
foreach ([[$p1, $rafael, 'R'], [$p1, $carla, 'A'], [$p1, $juliana, 'C'], [$p1, $paulo, 'I'], [$p4, $juliana, 'R'], [$p4, $rafael, 'A'], [$p4, $paulo, 'I']] as [$pl, $u, $papel]) {
    Raci::firstOrCreate(['cod_plano_de_acao' => $pl->cod_plano_de_acao, 'user_id' => $u->id, 'dsc_papel' => $papel, 'cod_entrega' => null]);
}
Raci::firstOrCreate(['cod_plano_de_acao' => $p1->cod_plano_de_acao, 'user_id' => $rafael->id, 'dsc_papel' => 'R', 'cod_entrega' => $e14->cod_entrega]);

// Plano de comunicação
foreach ([
    [$p1, 'Comitê de Governança Estratégica', 'Andamento das entregas, riscos e decisões necessárias.', 'Reunião presencial', 'Mensal', 'Rafael Souza Lima'],
    [$p1, 'Servidores das áreas donas dos serviços', 'Cronograma de publicação e papel de cada área.', 'E-mail', 'Quinzenal', 'Juliana Alves Costa'],
    [$p1, 'Cidadãos e imprensa', 'Novos serviços disponíveis no portal.', 'Portal/Intranet', 'Sob demanda', 'Assessoria de Comunicação'],
    [$p4, 'Coordenações regionais de atendimento', 'Regras do agendamento obrigatório e metas de espera.', 'Videoconferência', 'Semanal', 'Juliana Alves Costa'],
] as $i => [$pl, $publico, $mensagem, $canal, $freq, $resp]) {
    PlanoComunicacao::firstOrCreate(['cod_plano_de_acao' => $pl->cod_plano_de_acao, 'nom_publico_alvo' => $publico], [
        'dsc_mensagem_chave' => $mensagem, 'dsc_canal' => $canal, 'dsc_frequencia' => $freq, 'nom_responsavel' => $resp, 'num_ordem' => $i + 1,
    ]);
}

// Lições aprendidas
foreach ([
    [$p2, 'Equipe', 'Aprendizado', 'Turmas mistas de lideranças e equipes técnicas aumentaram a aplicação prática do conteúdo.', 'Manter turmas mistas nas próximas trilhas.'],
    [$p2, 'Planejamento', 'Boas Práticas', 'A avaliação de impacto noventa dias após o curso mostrou o uso real das ferramentas.', 'Incluir avaliação de impacto em todas as ações de capacitação.'],
    [$p8, 'Planejamento', 'Problema', 'A iniciativa foi cancelada porque não se verificou antes a existência de solução centralizada no governo.', 'Consultar soluções compartilhadas antes de iniciar projetos de modelo próprio.'],
    [$p3, 'Prazo', 'Problema', 'A migração atrasou por dependência de janelas de manutenção do fornecedor de nuvem.', 'Negociar as janelas de manutenção já no planejamento do projeto.'],
    [$p1, 'Comunicação', 'Melhoria', 'Publicar serviços em ondas pequenas reduziu chamados de suporte.', 'Adotar publicação em ondas para os próximos serviços.'],
] as $i => [$pl, $cat, $tipo, $desc, $rec]) {
    LicaoAprendida::firstOrCreate(['cod_plano_de_acao' => $pl->cod_plano_de_acao, 'txt_descricao' => $desc], ['dsc_categoria' => $cat, 'dsc_tipo' => $tipo, 'txt_recomendacao' => $rec, 'num_ordem' => $i + 1]);
}
linha('Iniciativas: '.PlanoDeAcao::count().' | Entregas: '.Entrega::count());

// ===========================================================================
// 13. Riscos (pela Administradora da SPG e pelos gestores)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(9)->setTime(15, 0));
logar($carla, $spg->cod_organizacao);
$base = ['cod_pei' => $pei->cod_pei];
$r1 = risco($base + ['cod_organizacao' => $afe->cod_organizacao, 'dsc_titulo' => 'Restrição orçamentária para os projetos estratégicos', 'txt_descricao' => 'Contingenciamento das despesas discricionárias pode interromper projetos do portfólio estratégico.', 'dsc_categoria' => 'Estratégico', 'dsc_status' => 'Em Monitoramento', 'num_probabilidade' => 4, 'num_impacto' => 5, 'txt_causas' => 'Cenário fiscal restritivo; baixa previsibilidade de limites de empenho.', 'txt_consequencias' => 'Atraso ou cancelamento de entregas e metas não alcançadas.', 'cod_responsavel_monitoramento' => $carla->id, 'dsc_estrategia_resposta' => 'Mitigar', 'dte_proxima_revisao' => $REAL_AGORA->copy()->addDays(20)->toDateString()], [$o9, $o4]);
$r3 = risco($base + ['cod_organizacao' => $spg->cod_organizacao, 'dsc_titulo' => 'Baixa adesão das unidades à gestão de riscos', 'txt_descricao' => 'Unidades podem não registrar nem monitorar seus riscos no prazo.', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Identificado', 'num_probabilidade' => 3, 'num_impacto' => 4, 'txt_causas' => 'Pouca familiaridade com a metodologia; sobrecarga das equipes.', 'txt_consequencias' => 'Riscos relevantes sem tratamento.', 'cod_responsavel_monitoramento' => $rafael->id, 'dsc_estrategia_resposta' => 'Mitigar', 'dte_proxima_revisao' => $REAL_AGORA->copy()->subDays(15)->toDateString()], [$o5]);
$r5 = risco($base + ['cod_organizacao' => $spg->cod_organizacao, 'dsc_titulo' => 'Descumprimento de prazos legais de transparência', 'txt_descricao' => 'Publicações obrigatórias podem não ocorrer no prazo.', 'dsc_categoria' => 'Legal/Conformidade', 'dsc_status' => 'Monitorado', 'num_probabilidade' => 2, 'num_impacto' => 4, 'txt_causas' => 'Dependência de consolidação manual de dados.', 'txt_consequencias' => 'Apontamentos dos órgãos de controle.', 'cod_responsavel_monitoramento' => $carla->id, 'dsc_estrategia_resposta' => 'Evitar'], [$o3]);
$r8 = risco($base + ['cod_organizacao' => $spg->cod_organizacao, 'dsc_titulo' => 'Rotatividade de servidores em funções-chave', 'txt_descricao' => 'Saída de servidores experientes pode comprometer a continuidade das iniciativas.', 'dsc_categoria' => 'Estratégico', 'dsc_status' => 'Em Monitoramento', 'num_probabilidade' => 3, 'num_impacto' => 2, 'cod_responsavel_monitoramento' => $carla->id, 'dsc_estrategia_resposta' => 'Mitigar'], [$o7]);
$r9 = risco($base + ['cod_organizacao' => $afe->cod_organizacao, 'dsc_titulo' => 'Divulgação de informação incorreta em canais oficiais', 'txt_descricao' => 'Publicação de dado desatualizado nos canais da Agência.', 'dsc_categoria' => 'Reputacional', 'dsc_status' => 'Monitorado', 'num_probabilidade' => 1, 'num_impacto' => 3, 'cod_responsavel_monitoramento' => $carla->id, 'dsc_estrategia_resposta' => 'Aceitar', 'txt_justificativa_estrategia' => 'Probabilidade muito baixa e fluxo de revisão já existente; o custo de controles adicionais supera o benefício.'], [$o3]);

logar($rafael, $dti->cod_organizacao);
$r2 = risco($base + ['cod_organizacao' => $dti->cod_organizacao, 'dsc_titulo' => 'Indisponibilidade prolongada dos sistemas críticos', 'txt_descricao' => 'Falhas de infraestrutura podem deixar os sistemas de atendimento fora do ar por mais de quatro horas.', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Em Monitoramento', 'num_probabilidade' => 4, 'num_impacto' => 4, 'txt_causas' => 'Infraestrutura própria obsoleta; ausência de redundância.', 'txt_consequencias' => 'Interrupção do atendimento e perda de credibilidade.', 'cod_responsavel_monitoramento' => $rafael->id, 'dsc_estrategia_resposta' => 'Mitigar', 'dte_proxima_revisao' => $REAL_AGORA->copy()->subDays(3)->toDateString()], [$o4, $o41]);
$r6 = risco($base + ['cod_organizacao' => $dti->cod_organizacao, 'dsc_titulo' => 'Vazamento de dados pessoais de cidadãos', 'txt_descricao' => 'Acesso indevido a bases com dados pessoais.', 'dsc_categoria' => 'Legal/Conformidade', 'dsc_status' => 'Em Monitoramento', 'num_probabilidade' => 2, 'num_impacto' => 5, 'txt_causas' => 'Controles de acesso insuficientes; ataques cibernéticos.', 'txt_consequencias' => 'Sanções legais e dano à imagem.', 'cod_responsavel_monitoramento' => $rafael->id, 'dsc_estrategia_resposta' => 'Mitigar'], [$o5]);
$r7 = risco($base + ['cod_organizacao' => $dti->cod_organizacao, 'dsc_titulo' => 'Atraso de fornecedores na entrega de soluções contratadas', 'txt_descricao' => 'Fornecedores de TI podem descumprir os prazos dos contratos.', 'dsc_categoria' => 'Financeiro', 'dsc_status' => 'Mitigado', 'num_probabilidade' => 3, 'num_impacto' => 3, 'cod_responsavel_monitoramento' => $rafael->id, 'dsc_estrategia_resposta' => 'Transferir', 'txt_justificativa_estrategia' => 'Cláusulas contratuais de nível de serviço com glosa por atraso.'], [$o4]);

logar($juliana, $dac->cod_organizacao);
$r4 = risco($base + ['cod_organizacao' => $dac->cod_organizacao, 'dsc_titulo' => 'Aumento das reclamações por demora no atendimento', 'txt_descricao' => 'Picos de demanda podem elevar o tempo de espera nas unidades regionais.', 'dsc_categoria' => 'Reputacional', 'dsc_status' => 'Em Monitoramento', 'num_probabilidade' => 3, 'num_impacto' => 4, 'txt_causas' => 'Equipes reduzidas e demanda sazonal.', 'txt_consequencias' => 'Insatisfação do cidadão e exposição na imprensa.', 'cod_responsavel_monitoramento' => $juliana->id, 'dsc_estrategia_resposta' => 'Mitigar'], [$o2, $o61]);
$r10 = risco($base + ['cod_organizacao' => $dac->cod_organizacao, 'dsc_titulo' => 'Instabilidade de internet nas unidades regionais', 'txt_descricao' => 'Quedas de conexão impedem o uso do sistema de agendamento.', 'dsc_categoria' => 'Operacional', 'dsc_status' => 'Encerrado', 'num_probabilidade' => 2, 'num_impacto' => 2, 'cod_responsavel_monitoramento' => $juliana->id, 'dsc_estrategia_resposta' => 'Mitigar'], [$o61]);

$mitigacoes = [
    [$r1, 'Prevenção', 'Priorizar o portfólio com o CGE e reservar dotação para os projetos essenciais.', $carla, -20, 'Concluído', 0],
    [$r1, 'Contingência', 'Plano de faseamento das entregas em caso de novo contingenciamento.', $carla, 30, 'Em Andamento', 0],
    [$r2, 'Prevenção', 'Migrar os sistemas críticos para a nuvem de governo com redundância.', $rafael, -10, 'Em Andamento', 850000],
    [$r2, 'Contingência', 'Procedimento de atendimento manual durante indisponibilidades.', $juliana, 15, 'A Fazer', 0],
    [$r3, 'Prevenção', 'Oficinas práticas de gestão de riscos com cada unidade.', $rafael, 45, 'A Fazer', 12000],
    [$r4, 'Prevenção', 'Agendamento obrigatório e remanejamento de equipes nos picos.', $juliana, -5, 'A Fazer', 0],
    [$r6, 'Prevenção', 'Autenticação em dois fatores e revisão trimestral de acessos.', $rafael, 60, 'Em Andamento', 95000],
    [$r7, 'Prevenção', 'Acordos de nível de serviço com glosa e reuniões quinzenais de acompanhamento.', $rafael, -60, 'Concluído', 0],
];
foreach ($mitigacoes as [$rs, $tipo, $desc, $resp, $dias, $status, $custo]) {
    RiscoMitigacao::firstOrCreate(['cod_risco' => $rs->cod_risco, 'txt_descricao' => $desc], [
        'dsc_tipo' => $tipo, 'cod_responsavel' => $resp->id, 'dte_prazo' => $REAL_AGORA->copy()->addDays($dias)->toDateString(),
        'dsc_status' => $status, 'vlr_custo_estimado' => $custo ?: null,
    ]);
}
foreach ([
    [$r2, '2026-03-12', 'Queda do servidor de banco de dados deixou o agendamento fora do ar por seis horas.', 4, 'Restauração a partir do backup e atendimento manual nas unidades.', 'Acelerar a migração para a nuvem e testar o plano de continuidade.'],
    [$r2, '2026-07-22', 'Falha elétrica no datacenter interrompeu o portal por duas horas.', 3, 'Acionamento do gerador e comunicação aos usuários.', 'Incluir o datacenter no plano de manutenção preventiva.'],
    [$r4, '2026-01-20', 'Fila de mais de três horas na unidade regional norte, com repercussão na imprensa local.', 4, 'Mutirão de atendimento e nota pública.', 'Tornar o agendamento obrigatório nas unidades com maior demanda.'],
] as [$rs, $data, $desc, $imp, $acoes, $licoes]) {
    RiscoOcorrencia::firstOrCreate(['cod_risco' => $rs->cod_risco, 'dte_ocorrencia' => $data], [
        'txt_descricao' => $desc, 'num_impacto_real' => $imp, 'txt_acoes_tomadas' => $acoes, 'txt_licoes_aprendidas' => $licoes,
    ]);
}
linha('Riscos: '.Risco::count());

// ===========================================================================
// 14. RAE, causas raiz e encaminhamentos (pela Administração do Sistema)
// ===========================================================================
momento($REAL_AGORA->copy()->subDays(8)->setTime(16, 0));
logar($admin, $afe->cod_organizacao);
$raes = [
    ['Revisão Anual', '2025-12-31', '2026-02-05', 'Satisfação do cidadão acima da meta; programa de capacitação concluído.', 'Economia em contratações abaixo do previsto; absenteísmo em alta.', 61.5,
        [['Revisão de Meta', 'Revisar a meta de economia em contratações após a adesão à central de compras.', 'Concluído', $carla, -150, null]],
        [['Economia em contratações abaixo da meta', ['Por que a economia caiu?', 'Porque as licitações próprias diminuíram.', 'Por que diminuíram?', 'Porque a Agência aderiu à central de compras.', 'Por que isso reduz a economia medida?'], 'A fórmula do indicador não considera a economia obtida nas compras centralizadas.', 'Medida']]],
    ['RAE', '2026-04-30', '2026-05-14', 'Publicação dos primeiros serviços no portal; tempo de resposta da ouvidoria em queda.', 'Atraso na integração com a identidade digital; riscos de TI sem plano de contingência.', 64.0,
        [['Revisão de Risco', 'Elaborar plano de contingência para indisponibilidade dos sistemas críticos.', 'Em Execução', $rafael, -20, null], ['Nova Iniciativa', 'Estruturar projeto de central de atendimento omnicanal.', 'Concluído', $juliana, -60, null]],
        [['Integração com a identidade digital atrasada', ['Por que atrasou?', 'Porque o ambiente de produção não foi liberado.', 'Por que não foi liberado?', 'Porque faltou o termo de adesão assinado.', 'Por que faltou o termo?'], 'O fluxo de assinatura do termo de adesão não tinha responsável definido.', 'Método']]],
    ['RAE', '2026-08-31', '2026-09-17', 'Satisfação do cidadão superou a meta; reclamações presenciais em queda.', 'Absenteísmo e economia em contratações em nível crítico; migração para a nuvem atrasada.', 66.5,
        [['Revisão de Objetivo', 'Reavaliar as metas do objetivo de qualidade de vida no trabalho com a área de saúde.', 'Pendente', $carla, 25, null], ['Outro', 'Apresentar ao CGE novo cronograma da migração para a nuvem.', 'Pendente', $rafael, -2, 'p3']],
        [['Absenteísmo acima da meta', ['Por que o absenteísmo subiu?', 'Porque aumentaram os afastamentos curtos.', 'Por que aumentaram?', 'Porque cresceram as queixas osteomusculares.', 'Por que cresceram?'], 'Mobiliário inadequado e ausência de programa de ergonomia.', 'Meio Ambiente'], ['Migração para a nuvem atrasada', ['Por que atrasou?', 'Porque as janelas de manutenção foram canceladas.', 'Por que foram canceladas?', 'Porque o fornecedor priorizou outros clientes.'], 'Contrato sem cláusula de janelas de manutenção garantidas.', 'Material']]],
];
foreach ($raes as [$tipo, $ref, $reuniao, $positivos, $problemas, $progresso, $encs, $causas]) {
    $rae = Rae::firstOrCreate(['cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao, 'dte_referencia' => $ref], [
        'dte_reuniao' => $reuniao, 'dsc_tipo_reuniao' => $tipo, 'txt_destaques_positivos' => $positivos,
        'txt_problemas_identificados' => $problemas, 'txt_encaminhamentos' => 'Ver encaminhamentos registrados.',
        'json_participantes' => ['Diretor-Presidente', 'Carla Mendes Ribeiro (SPG)', 'Rafael Souza Lima', 'Juliana Alves Costa', 'Paulo Henrique Nogueira'],
        'num_progresso_geral' => $progresso,
    ]);
    $primeiroEnc = null;
    foreach ($encs as [$tipoEnc, $desc, $status, $resp, $dias, $plano]) {
        $enc = RaeEncaminhamento::firstOrCreate(['cod_rae' => $rae->cod_rae, 'txt_descricao' => $desc], [
            'dsc_tipo' => $tipoEnc, 'dsc_status' => $status, 'cod_responsavel' => $resp->id,
            'dte_prazo' => $REAL_AGORA->copy()->addDays($dias)->toDateString(),
            'cod_plano_vinculado' => $plano === 'p3' ? $p3->cod_plano_de_acao : null,
        ]);
        $primeiroEnc ??= $enc;
    }
    foreach ($causas as $i => [$problema, $porques, $causaRaiz, $categoria]) {
        RaeCausaRaiz::firstOrCreate(['cod_rae' => $rae->cod_rae, 'dsc_problema' => $problema], [
            'json_cinco_porques' => $porques, 'dsc_causa_raiz' => $causaRaiz, 'dsc_categoria_ishikawa' => $categoria,
            'cod_encaminhamento_vinculado' => $i === 0 ? $primeiroEnc?->cod_encaminhamento : null,
        ]);
    }
}

// Comentários em objetivos
if (! ObjetivoComentario::where('cod_objetivo', $o2->cod_objetivo)->exists()) {
    logar($carla);
    $c = ObjetivoComentario::create(['cod_objetivo' => $o2->cod_objetivo, 'user_id' => $carla->id, 'dsc_comentario' => 'Sugiro incluir a central omnicanal como iniciativa deste objetivo a partir de 2027.']);
    logar($admin);
    ObjetivoComentario::create(['cod_objetivo' => $o2->cod_objetivo, 'user_id' => $admin->id, 'dsc_comentario' => 'De acordo. A iniciativa já foi cadastrada e entra no portfólio do próximo ano.', 'cod_comentario_pai' => $c->cod_comentario]);
    logar($rafael);
    ObjetivoComentario::create(['cod_objetivo' => $o4->cod_objetivo, 'user_id' => $rafael->id, 'dsc_comentario' => 'O desdobramento para os serviços de maior demanda facilitou a priorização das entregas do portal.']);
}

// ===========================================================================
// 15. Acervo de documentos e relatórios agendados
// ===========================================================================
logar($admin, $afe->cod_organizacao);
$documentos = [
    [['nom_documento' => 'Portaria de instituição do Comitê de Governança Estratégica', 'dsc_tipo' => 'Portaria', 'num_documento' => '112', 'num_ano_referencia' => 2023, 'dte_documento' => '2023-07-20', 'dsc_origem' => 'Gabinete do Diretor-Presidente', 'txt_descricao' => 'Institui o CGE e define sua composição e competências.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], ['Art. 1º Fica instituído o Comitê de Governança Estratégica da Agência Federal de Exemplo.', 'Art. 2º Compete ao Comitê aprovar e monitorar o Planejamento Estratégico Integrado.']],
    [['nom_documento' => 'Mapa Estratégico 2024-2027', 'dsc_tipo' => 'Mapa Estratégico', 'num_ano_referencia' => 2024, 'dte_documento' => '2023-12-14', 'dsc_origem' => 'Secretaria de Planejamento e Gestão', 'txt_descricao' => 'Versão aprovada do mapa estratégico do ciclo vigente.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], ['Missão, visão, valores, perspectivas e objetivos estratégicos do ciclo 2024-2027.']],
    [['nom_documento' => 'Relatório de Gestão 2025', 'dsc_tipo' => 'Relatório de Gestão', 'num_ano_referencia' => 2025, 'dte_documento' => '2026-03-31', 'dsc_origem' => 'Secretaria de Planejamento e Gestão', 'txt_descricao' => 'Prestação de contas anual com os resultados dos indicadores estratégicos.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], ['Apresenta os resultados alcançados em 2025 e as perspectivas para 2026.']],
    [['nom_documento' => 'Ata da RAE do 2º quadrimestre de 2026', 'dsc_tipo' => 'Ata de Reunião', 'num_ano_referencia' => 2026, 'dte_documento' => '2026-09-17', 'dsc_origem' => 'Comitê de Governança Estratégica', 'txt_descricao' => 'Registro das deliberações da reunião de avaliação da estratégia.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $afe->cod_organizacao], ['Participantes, pauta, resultados apresentados e encaminhamentos aprovados.']],
    [['nom_documento' => 'Nota Técnica sobre a metodologia de indicadores', 'dsc_tipo' => 'Nota Técnica', 'num_documento' => '7', 'num_ano_referencia' => 2024, 'dte_documento' => '2024-02-15', 'dsc_origem' => 'Secretaria de Planejamento e Gestão', 'txt_descricao' => 'Orienta a definição de metas, polaridade, linha de base e periodicidade.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $spg->cod_organizacao], ['Conceitos de meta, polaridade, linha de base e régua de satisfação utilizados no Sistema PEI.']],
    [['nom_documento' => 'Plano Diretor de Tecnologia da Informação e Comunicação 2024-2026', 'dsc_tipo' => 'Plano Diretor de TI (PDTI)', 'num_ano_referencia' => 2024, 'dte_documento' => '2024-04-10', 'dsc_origem' => 'Diretoria de Tecnologia da Informação', 'txt_descricao' => 'Portfólio de soluções de TI alinhado ao objetivo de transformação digital.', 'cod_pei' => $pei->cod_pei, 'cod_organizacao' => $dti->cod_organizacao], ['Diagnóstico, necessidades priorizadas e plano de investimentos de TI.']],
];
foreach ($documentos as [$dados, $paragrafos]) {
    documento($dados, $paragrafos, $admin);
}
foreach ([
    [$admin, 'executivo', 'mensal', $afe->cod_organizacao, 8],
    [$carla, 'indicadores', 'semanal', $spg->cod_organizacao, 3],
] as [$u, $tipo, $freq, $org, $dias]) {
    if (! RelatorioAgendado::where('user_id', $u->id)->where('dsc_tipo_relatorio', $tipo)->exists()) {
        logar($u, $org);
        RelatorioAgendado::create([
            'user_id' => $u->id, 'dsc_tipo_relatorio' => $tipo, 'dsc_frequencia' => $freq,
            'txt_filtros' => ['ano' => (int) $REAL_AGORA->year, 'periodo' => 'anual', 'perspectiva' => null, 'organizacao_id' => $org, 'include_ai' => false, 'cod_pei' => $pei->cod_pei],
            'dte_proxima_execucao' => $REAL_AGORA->copy()->addDays($dias)->setTime(7, 0), 'bln_ativo' => true,
        ]);
    }
}

// ===========================================================================
// 16. Alterações posteriores (auditoria e aba Atividade) — só na primeira carga
// ===========================================================================
if ($primeiraCarga) {
    momento($REAL_AGORA->copy()->subDays(4)->setTime(9, 20));
    logar($carla, $spg->cod_organizacao);
    $ind['economia']->update(['dsc_meta' => 'Economizar R$ 2 milhões por ano até 2027, incluindo as compras centralizadas']);
    $r5->update(['num_probabilidade' => 3, 'txt_causas' => 'Dependência de consolidação manual de dados e equipe reduzida no período de férias.']);
    $p5->update(['txt_detalhamento' => $p5->txt_detalhamento.' Em 2026, prioridade para o registro dos riscos estratégicos no sistema.']);
    $duplicado = Risco::create($base + ['cod_organizacao' => $spg->cod_organizacao, 'dsc_titulo' => 'Rotatividade de servidores em funções-chave (cópia)', 'txt_descricao' => 'Registro feito em duplicidade.', 'dsc_categoria' => 'Estratégico', 'dsc_status' => 'Identificado', 'num_probabilidade' => 3, 'num_impacto' => 2, 'cod_responsavel_monitoramento' => $carla->id, 'dsc_estrategia_resposta' => 'Mitigar']);
    momento($REAL_AGORA->copy()->subDays(4)->setTime(9, 45));
    $duplicado->delete();

    momento($REAL_AGORA->copy()->subDays(3)->setTime(11, 10));
    logar($rafael, $dti->cod_organizacao);
    $p3->update(['bln_status' => 'Atrasado']);
    $ind['disponibilidade']->update(['txt_observacao' => 'Queda em julho causada por falha elétrica no datacenter (ver ocorrência do risco de indisponibilidade).']);
    $e142->update(['bln_status' => 'Concluído']);
    $e15->update(['dte_prazo' => '2026-11-27']);
    $rascunho = entrega($p1, ['dsc_entrega' => 'Revisar textos do portal (duplicada)', 'bln_status' => 'Não Iniciado', 'dte_prazo' => '2026-10-30'], [$rafael]);
    $rascunho->delete();

    momento($REAL_AGORA->copy()->subDays(2)->setTime(15, 35));
    logar($juliana, $dac->cod_organizacao);
    RiscoMitigacao::where('cod_risco', $r4->cod_risco)->first()?->update(['dsc_status' => 'Em Andamento']);
    RiscoOcorrencia::create(['cod_risco' => $r4->cod_risco, 'dte_ocorrencia' => $REAL_AGORA->copy()->subDays(2)->toDateString(), 'txt_descricao' => 'Espera de duas horas na unidade regional sul por falta de servidores.', 'num_impacto_real' => 3, 'txt_acoes_tomadas' => 'Remanejamento de dois servidores da sede.']);
    $e16->update(['bln_status' => 'Em Andamento']);

    momento($REAL_AGORA->copy()->subDays(1)->setTime(10, 5));
    logar($admin, $afe->cod_organizacao);
    $o3->update(['dsc_objetivo' => 'Ampliar a publicação de dados abertos e resultados e responder às demandas de informação dentro dos prazos legais.']);
    Valor::where('cod_pei', $pei->cod_pei)->where('nom_valor', 'Inovação')->first()?->update(['dsc_valor' => 'Buscar soluções criativas, baseadas em evidências e centradas no usuário para melhorar os serviços.']);

    momento($REAL_AGORA->copy()->subHours(3));
    logar($carla, $spg->cod_organizacao);
    $r3->update(['dsc_status' => 'Em Monitoramento']);
    $ind['capacitacao']->update(['num_peso' => 2]);
    linha('Alterações posteriores registradas (auditoria e Atividade).');
}

// Alertas do sino (tendência desfavorável e avisos), em nome da Administração do Sistema
Carbon::setTestNow();
logar($admin, $afe->cod_organizacao);
$servico = app(IndicadorCalculoService::class);
foreach (Indicador::all() as $i) {
    $servico->verificarAlertaTendencia($i);
}
if (! \App\Models\StrategicAlert::where('user_id', $admin->id)->where('title', 'RAE do 2º quadrimestre registrada')->exists()) {
    NotificationService::sendMentorAlert('RAE do 2º quadrimestre registrada', 'Há dois encaminhamentos pendentes com prazo nos próximos dias.', 'bi-calendar-check', 'info');
    NotificationService::sendMentorAlert('Revisão de risco vencida', 'O risco "Indisponibilidade prolongada dos sistemas críticos" está com a revisão vencida.', 'bi-exclamation-triangle', 'warning');
}
Auth::guard('web')->logout();

linha('Concluído. Banco: '.DB::connection()->getDatabaseName());
