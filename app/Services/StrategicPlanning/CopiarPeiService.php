<?php

namespace App\Services\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * "Salvar como": cria um PEI novo levando tudo o que está preso ao de origem.
 *
 * Cada registro ganha chave nova e toda chave estrangeira interna é remapeada
 * para a cópia (o objetivo copiado aponta para a perspectiva copiada, a entrega
 * para a iniciativa copiada, e assim por diante). O que aponta para FORA do PEI
 * — organização, usuário, tipo de execução, ODS — é mantido.
 *
 * Fica de fora o que é registro de conversa ou de trilha, não de planejamento:
 * comentários, histórico de entregas e auditoria.
 *
 * As linhas são copiadas pelas colunas que existem no banco (sem lista fixa),
 * para que coluna nova não seja esquecida pela cópia. Tudo numa transação:
 * ou o PEI sai inteiro, ou não sai.
 */
final class CopiarPeiService
{
    /** @var array<string, array<string, string>> mapa antigo → novo, por entidade */
    private array $mapas = [];

    /** @var array<string, int> linhas copiadas por tabela, para o relato na tela */
    private array $contagem = [];

    /** @var array<int, string> arquivos gravados, para desfazer se a transação cair */
    private array $arquivosNovos = [];

    public function copiar(PEI $origem, string $descricao, int $anoInicio, int $anoFim): PEI
    {
        $this->mapas = [];
        $this->contagem = [];
        $this->arquivosNovos = [];

        try {
            return DB::transaction(fn () => $this->executar($origem, $descricao, $anoInicio, $anoFim));
        } catch (\Throwable $e) {
            foreach ($this->arquivosNovos as $arquivo) {
                Storage::disk('local')->delete($arquivo);
            }
            throw $e;
        }
    }

    /** @return array<string, int> */
    public function contagem(): array
    {
        return $this->contagem;
    }

    private function executar(PEI $origem, string $descricao, int $anoInicio, int $anoFim): PEI
    {
        $novo = PEI::create([
            'dsc_pei' => $descricao,
            'num_ano_inicio_pei' => $anoInicio,
            'num_ano_fim_pei' => $anoFim,
        ]);
        $this->mapas['pei'] = [$origem->cod_pei => $novo->cod_pei];

        // Identidade, diagnóstico e governança do ciclo
        $this->copiarAncorado('strategic_planning.rel_pei_ods', null, 'cod_pei', 'pei');
        foreach ([
            'strategic_planning.tab_missao_visao_valores' => 'cod_missao_visao_valores',
            'strategic_planning.tab_valores' => 'cod_valor',
            'strategic_planning.tab_tema_norteador' => 'cod_tema_norteador',
            'strategic_planning.tab_grau_satisfacao' => 'cod_grau_satisfacao',
            'strategic_planning.tab_analise_ambiental' => 'cod_analise',
            'strategic_planning.tab_cenarios_prospectivos' => 'cod_cenario',
            'strategic_planning.tab_partes_interessadas' => 'cod_parte',
            'strategic_planning.tab_integracao_instrumentos' => 'cod_integracao',
            'strategic_planning.tab_calendario_eventos_pei' => 'cod_evento',
            'strategic_planning.tab_inaugurar_pei' => 'cod_inaugurar',
        ] as $tabela => $chave) {
            $this->copiarAncorado($tabela, $chave, 'cod_pei', 'pei');
        }

        // Mapa estratégico
        $this->copiarAncorado('strategic_planning.tab_perspectiva', 'cod_perspectiva', 'cod_pei', 'pei', 'perspectiva');
        $this->copiarAncorado('strategic_planning.tab_atividade_cadeia_valor', 'cod_atividade_cadeia_valor', 'cod_pei', 'pei', 'atividade', [
            'cod_perspectiva' => 'perspectiva',
        ]);
        $this->copiarAncorado('strategic_planning.tab_processos_atividade_cadeia_valor', 'cod_processo_atividade_cadeia_valor', 'cod_atividade_cadeia_valor', 'atividade');
        $this->copiarAncorado('strategic_planning.tab_objetivo', 'cod_objetivo', 'cod_perspectiva', 'perspectiva', 'objetivo', [], 'cod_objetivo_pai');
        $this->copiarAncorado('strategic_planning.tab_futuro_almejado_objetivo', 'cod_futuro_almejado', 'cod_objetivo', 'objetivo');
        $this->copiarAncorado('strategic_planning.rel_objetivo_ods', null, 'cod_objetivo', 'objetivo');
        $this->copiarAncorado('strategic_planning.tab_estrategia_tows', 'cod_estrategia', 'cod_pei', 'pei', null, [
            'cod_objetivo_vinculado' => 'objetivo',
        ]);

        // Iniciativas e entregas
        $this->copiarAncorado('action_plan.tab_plano_de_acao', 'cod_plano_de_acao', 'cod_objetivo', 'objetivo', 'plano');
        $this->copiarAncorado('action_plan.rel_plano_organizacao', null, 'cod_plano_de_acao', 'plano');
        // Os gestores designados para a iniciativa acompanham a cópia.
        $this->copiarAncorado('organization.rel_users_tab_organizacoes_tab_perfil_acesso', 'id', 'cod_plano_de_acao', 'plano');
        $this->copiarAncorado('action_plan.tab_entrega_labels', 'cod_label', 'cod_plano_de_acao', 'plano', 'label');
        $this->copiarAncorado('action_plan.tab_licoes_aprendidas', 'cod_licao', 'cod_plano_de_acao', 'plano');
        $this->copiarAncorado('action_plan.tab_plano_comunicacao', 'cod_comunicacao', 'cod_plano_de_acao', 'plano');
        $this->copiarAncorado('action_plan.tab_entregas', 'cod_entrega', 'cod_plano_de_acao', 'plano', 'entrega', [], 'cod_entrega_pai');
        $this->copiarAncorado('action_plan.rel_entrega_labels', null, 'cod_entrega', 'entrega', null, ['cod_label' => 'label']);
        $this->copiarAncorado('action_plan.rel_entrega_users_responsaveis', null, 'cod_entrega', 'entrega');
        $this->copiarAncorado('action_plan.tab_raci', 'cod_raci', 'cod_plano_de_acao', 'plano', null, ['cod_entrega' => 'entrega']);
        $this->copiarAncorado('action_plan.tab_entrega_anexos', 'cod_anexo', 'cod_entrega', 'entrega', null, [], null, 'dsc_caminho');

        // Indicadores: pertencem ao PEI pelo objetivo ou pela iniciativa.
        $this->copiarIndicadores();
        $this->copiarAncorado('performance_indicators.rel_indicador_objetivo_organizacao', null, 'cod_indicador', 'indicador');
        $this->copiarAncorado('performance_indicators.rel_indicador_plano_de_acao', null, 'cod_indicador', 'indicador', null, ['cod_plano_de_acao' => 'plano']);
        $this->copiarAncorado('performance_indicators.tab_linha_base_indicador', 'cod_linha_base', 'cod_indicador', 'indicador');
        $this->copiarAncorado('performance_indicators.tab_meta_por_ano', 'cod_meta_por_ano', 'cod_indicador', 'indicador');
        $this->copiarAncorado('performance_indicators.tab_evolucao_indicador', 'cod_evolucao_indicador', 'cod_indicador', 'indicador', 'evolucao');
        $this->copiarAncorado('strategic_planning.tab_arquivos', 'cod_arquivo', 'cod_evolucao_indicador', 'evolucao', null, [], null, 'dsc_nome_arquivo');

        // Riscos
        $this->copiarAncorado('risk_management.tab_risco', 'cod_risco', 'cod_pei', 'pei', 'risco');
        $this->copiarAncorado('risk_management.tab_risco_objetivo', null, 'cod_risco', 'risco', null, ['cod_objetivo' => 'objetivo']);
        $this->copiarAncorado('risk_management.tab_risco_mitigacao', 'cod_mitigacao', 'cod_risco', 'risco');
        $this->copiarAncorado('risk_management.tab_risco_ocorrencia', 'cod_ocorrencia', 'cod_risco', 'risco');

        // Reuniões de Análise Estratégica
        $this->copiarAncorado('strategic_planning.tab_rae', 'cod_rae', 'cod_pei', 'pei', 'rae');
        $this->copiarAncorado('strategic_planning.tab_rae_encaminhamento', 'cod_encaminhamento', 'cod_rae', 'rae', 'encaminhamento', [
            'cod_plano_vinculado' => 'plano',
        ]);
        $this->copiarAncorado('strategic_planning.tab_rae_causa_raiz', 'cod_causa', 'cod_rae', 'rae', null, [
            'cod_encaminhamento_vinculado' => 'encaminhamento',
        ]);

        return $novo;
    }

    /**
     * Copia as linhas de $tabela cuja coluna $ancora aponta para um registro já
     * copiado da entidade $entidadeAncora.
     *
     * @param  string|null  $chave  PK da tabela (null em tabela de relação, sem PK própria)
     * @param  string|null  $registrarComo  entidade sob a qual guardar o mapa antigo → novo
     * @param  array<string, string>  $remapear  coluna => entidade, para as demais FKs internas
     * @param  string|null  $autorreferencia  coluna que aponta para a própria tabela (pai)
     * @param  string|null  $colunaArquivo  coluna com o caminho de um arquivo no disco público
     */
    private function copiarAncorado(
        string $tabela,
        ?string $chave,
        string $ancora,
        string $entidadeAncora,
        ?string $registrarComo = null,
        array $remapear = [],
        ?string $autorreferencia = null,
        ?string $colunaArquivo = null,
    ): void {
        $ancoras = $this->mapas[$entidadeAncora] ?? [];
        if ($ancoras === []) {
            return;
        }

        $linhas = collect();
        foreach (array_chunk(array_keys($ancoras), 500) as $lote) {
            $consulta = DB::table($tabela)->whereIn($ancora, $lote);
            if ($this->temColuna($tabela, 'deleted_at')) {
                $consulta->whereNull('deleted_at');
            }
            $linhas = $linhas->merge($consulta->get());
        }

        $this->inserir($tabela, $linhas, $chave, $ancora, $entidadeAncora, $registrarComo, $remapear, $autorreferencia, $colunaArquivo);
    }

    private function copiarIndicadores(): void
    {
        $objetivos = array_keys($this->mapas['objetivo'] ?? []);
        $planos = array_keys($this->mapas['plano'] ?? []);
        if ($objetivos === [] && $planos === []) {
            return;
        }

        $linhas = DB::table('performance_indicators.tab_indicador')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($objetivos, $planos) {
                $q->whereIn('cod_objetivo', $objetivos ?: ['00000000-0000-0000-0000-000000000000'])
                    ->orWhereIn('cod_plano_de_acao', $planos ?: ['00000000-0000-0000-0000-000000000000']);
            })
            ->get();

        $this->inserir('performance_indicators.tab_indicador', $linhas, 'cod_indicador', null, null, 'indicador', [
            'cod_objetivo' => 'objetivo',
            'cod_plano_de_acao' => 'plano',
        ]);
    }

    /**
     * @param  Collection<int, object>  $linhas
     * @param  array<string, string>  $remapear
     */
    private function inserir(
        string $tabela,
        $linhas,
        ?string $chave,
        ?string $ancora,
        ?string $entidadeAncora,
        ?string $registrarComo,
        array $remapear,
        ?string $autorreferencia = null,
        ?string $colunaArquivo = null,
    ): void {
        if ($linhas->isEmpty()) {
            return;
        }

        $mapa = [];
        foreach ($linhas as $linha) {
            if ($chave) {
                $mapa[$linha->{$chave}] = (string) Str::uuid();
            }
        }

        $agora = now();
        $novas = [];
        $pais = [];
        foreach ($linhas as $linha) {
            $nova = (array) $linha;

            if ($chave) {
                $nova[$chave] = $mapa[$linha->{$chave}];
            }
            if ($ancora) {
                $nova[$ancora] = $this->mapas[$entidadeAncora][$linha->{$ancora}];
            }
            foreach ($remapear as $coluna => $entidade) {
                if (($nova[$coluna] ?? null) !== null) {
                    // Aponta para algo de outro PEI: o vínculo não acompanha a cópia.
                    $nova[$coluna] = $this->mapas[$entidade][$nova[$coluna]] ?? null;
                    if ($nova[$coluna] === null && $chave === null) {
                        // Linha de relação sem uma das pontas não tem o que ligar.
                        continue 2;
                    }
                }
            }
            if ($autorreferencia && $nova[$autorreferencia] !== null) {
                // O pai pode vir depois do filho no lote: grava sem pai e liga em seguida.
                $pais[$nova[$chave]] = $mapa[$nova[$autorreferencia]] ?? null;
                $nova[$autorreferencia] = null;
            }
            if ($colunaArquivo && ($nova[$colunaArquivo] ?? null)) {
                $nova[$colunaArquivo] = $this->duplicarArquivo($nova[$colunaArquivo]);
            }
            if (array_key_exists('created_at', $nova)) {
                $nova['created_at'] = $agora;
            }
            if (array_key_exists('updated_at', $nova)) {
                $nova['updated_at'] = $agora;
            }

            $novas[] = $nova;
        }

        foreach (array_chunk($novas, 200) as $lote) {
            DB::table($tabela)->insert($lote);
        }

        foreach (array_filter($pais) as $filho => $pai) {
            DB::table($tabela)->where($chave, $filho)->update([$autorreferencia => $pai]);
        }

        if ($registrarComo) {
            $this->mapas[$registrarComo] = ($this->mapas[$registrarComo] ?? []) + $mapa;
        }
        $this->contagem[$tabela] = ($this->contagem[$tabela] ?? 0) + count($novas);
    }

    /** O arquivo é duplicado: apagar o anexo de um PEI não pode levar o do outro. */
    private function duplicarArquivo(string $caminho): string
    {
        // Evidências e anexos vão para o disco privado; os antigos podem estar
        // ainda no público. Lê de onde estiver, grava a cópia sempre no privado.
        $origem = collect(['local', 'public'])->first(fn (string $d) => Storage::disk($d)->exists($caminho));
        if (! $origem) {
            return $caminho;
        }

        $diretorio = trim(dirname($caminho), './\\');
        $destino = ($diretorio !== '' ? $diretorio.'/' : '').Str::uuid().'_'.basename($caminho);
        Storage::disk('local')->put($destino, Storage::disk($origem)->get($caminho));
        $this->arquivosNovos[] = $destino;

        return $destino;
    }

    /** @var array<string, array<int, string>> */
    private array $colunas = [];

    private function temColuna(string $tabela, string $coluna): bool
    {
        if (! isset($this->colunas[$tabela])) {
            [$schema, $nome] = explode('.', $tabela);
            $this->colunas[$tabela] = DB::table('information_schema.columns')
                ->where('table_schema', $schema)
                ->where('table_name', $nome)
                ->pluck('column_name')
                ->all();
        }

        return in_array($coluna, $this->colunas[$tabela], true);
    }
}
