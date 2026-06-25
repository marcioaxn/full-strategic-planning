# Dicionario de Dados PostgreSQL - Sistema de Planejamento Estrategico

Data: 2026-05-23. Fonte primaria: catalogo real do PostgreSQL consultado em modo somente leitura.

## Criterios de confianca

- `Verificado no banco`: informacao extraida de `information_schema`, `pg_indexes`, tabela `migrations` ou contagem real de linhas.
- `Verificado no codigo`: informacao cruzada com Models Eloquent e migrations locais.
- `Inferido`: finalidade funcional deduzida por nome, schema, relacoes e uso no codigo; validar antes de mudanca estrutural.

## Sumario do banco

| Schema | Tabelas | Dominio predominante |
|---|---:|---|
| `action_plan` | 11 | Planos de acao e entregas |
| `organization` | 5 | Organizacao e acesso institucional |
| `performance_indicators` | 5 | Indicadores de desempenho |
| `public` | 18 | Infraestrutura, usuarios, auditoria, relatorios e suporte |
| `risk_management` | 4 | Gestao de riscos |
| `strategic_planning` | 13 | Planejamento estrategico PEI/BSC |

- Tabelas reais: 56.
- Migrations aplicadas: 68.
- Arquivos de migration no disco: 68.
- Arquivos de migration nao aplicados: nenhum.
- Migrations aplicadas sem arquivo local: nenhuma.

## Mapa funcional das tabelas

| Tabela | Modulo | Linhas | Model Eloquent | Finalidade |
|---|---|---:|---|---|
| `action_plan.acoes` | Planos de acao e entregas | 0 | `App\Models\ActionPlan\Acao` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `action_plan.rel_entrega_labels` | Planos de acao e entregas | 1 | Nao identificado | Pivot entre entregas e labels. |
| `action_plan.rel_entrega_users_responsaveis` | Planos de acao e entregas | 85 | Nao identificado | Pivot de responsaveis por entrega. |
| `action_plan.rel_plano_organizacao` | Planos de acao e entregas | 21 | Nao identificado | Pivot de multivinculacao entre planos de acao e organizacoes. |
| `action_plan.tab_entrega_anexos` | Planos de acao e entregas | 0 | `App\Models\ActionPlan\EntregaAnexo` | Anexos enviados em entregas. |
| `action_plan.tab_entrega_comentarios` | Planos de acao e entregas | 0 | `App\Models\ActionPlan\EntregaComentario` | Comentarios em entregas, inclusive respostas por comentario pai. |
| `action_plan.tab_entrega_historico` | Planos de acao e entregas | 85 | `App\Models\ActionPlan\EntregaHistorico` | Historico de alteracoes e eventos de entregas. |
| `action_plan.tab_entrega_labels` | Planos de acao e entregas | 1 | `App\Models\ActionPlan\EntregaLabel` | Labels por plano para classificar entregas. |
| `action_plan.tab_entregas` | Planos de acao e entregas | 84 | `App\Models\ActionPlan\Entrega` | Entregas/tarefas de planos de acao, com status, prazo, pesos, hierarquia e soft delete. |
| `action_plan.tab_plano_de_acao` | Planos de acao e entregas | 21 | `App\Models\ActionPlan\PlanoDeAcao` | Planos de acao/iniciativas/projetos vinculados a objetivos e organizacoes. |
| `action_plan.tab_tipo_execucao` | Planos de acao e entregas | 4 | `App\Models\ActionPlan\TipoExecucao` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `organization.rel_organizacao` | Organizacao e acesso institucional | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `organization.rel_users_tab_organizacoes` | Organizacao e acesso institucional | 5 | Nao identificado | Pivot de associacao entre usuarios e organizacoes. |
| `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | Organizacao e acesso institucional | 0 | Nao identificado | Pivot de perfis por usuario, organizacao e opcionalmente plano de acao. |
| `organization.tab_organizacoes` | Organizacao e acesso institucional | 6 | `App\Models\Organization` | Cadastro hierarquico de organizacoes/unidades institucionais. |
| `organization.tab_perfil_acesso` | Organizacao e acesso institucional | 4 | `App\Models\PerfilAcesso` | Catalogo de perfis de acesso usados pelas policies e pivots de usuario. |
| `performance_indicators.rel_indicador_objetivo_organizacao` | Indicadores de desempenho | 17 | Nao identificado | Pivot de indicadores/objetivos/organizacoes. |
| `performance_indicators.tab_evolucao_indicador` | Indicadores de desempenho | 51 | `App\Models\PerformanceIndicators\EvolucaoIndicador` | Lancamentos periodicos de realizado/previsto dos indicadores. |
| `performance_indicators.tab_indicador` | Indicadores de desempenho | 17 | `App\Models\PerformanceIndicators\Indicador` | Indicadores/KPIs vinculados a objetivos ou planos. |
| `performance_indicators.tab_linha_base_indicador` | Indicadores de desempenho | 17 | `App\Models\PerformanceIndicators\LinhaBaseIndicador` | Linhas de base anuais de indicadores. |
| `performance_indicators.tab_meta_por_ano` | Indicadores de desempenho | 17 | `App\Models\PerformanceIndicators\MetaPorAno` | Metas anuais de indicadores. |
| `pei.audits` | Auditoria | 1 | Nao identificado | Tabela do pacote owen-it/laravel-auditing. |
| `pei.cache` | Infraestrutura Laravel/autenticacao | 14 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.cache_locks` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.failed_jobs` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.job_batches` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.jobs` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.migrations` | Infraestrutura Laravel/autenticacao | 67 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.password_reset_tokens` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.personal_access_tokens` | Infraestrutura Laravel/autenticacao | 0 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.sessions` | Infraestrutura Laravel/autenticacao | 2 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.strategic_alerts` | Suporte funcional | 1 | `App\Models\StrategicAlert` | Alertas estrategicos persistentes exibidos ao usuario. |
| `pei.system_settings` | Suporte funcional | 6 | `App\Models\SystemSetting` | Configuracoes sistemicas, incluindo provedores de IA. |
| `strategic_planning.tab_analise_ambiental` | Suporte funcional | 90 | `App\Models\StrategicPlanning\AnaliseAmbiental` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.tab_audit` | Auditoria | 1 | `App\Models\TabAudit` | Tabela de auditoria/logs propria ou legada. |
| `pei.tab_relatorios_agendados` | Relatorios | 0 | `App\Models\Reports\RelatorioAgendado` | Agendamentos de geracao de relatorios. |
| `pei.tab_relatorios_gerados` | Relatorios | 0 | `App\Models\Reports\RelatorioGerado` | Historico de relatorios gerados. |
| `pei.tab_status` | Suporte funcional | 6 | `App\Models\TabStatus` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `pei.users` | Infraestrutura Laravel/autenticacao | 5 | `App\Models\User` | Usuarios autenticados do sistema, com flags de administrador, ativo, troca de senha e preferencias. |
| `risk_management.tab_risco` | Gestao de riscos | 30 | `App\Models\RiskManagement\Risco` | Riscos identificados por PEI/organizacao. |
| `risk_management.tab_risco_mitigacao` | Gestao de riscos | 30 | `App\Models\RiskManagement\RiscoMitigacao` | Medidas/planos de mitigacao de riscos. |
| `risk_management.tab_risco_objetivo` | Gestao de riscos | 40 | `App\Models\RiskManagement\RiscoObjetivo` | Pivot entre riscos e objetivos estrategicos. |
| `risk_management.tab_risco_ocorrencia` | Gestao de riscos | 0 | `App\Models\RiskManagement\RiscoOcorrencia` | Ocorrencias materializadas de riscos. |
| `strategic_planning.tab_arquivos` | Planejamento estrategico PEI/BSC | 0 | `App\Models\StrategicPlanning\Arquivo` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `strategic_planning.tab_atividade_cadeia_valor` | Planejamento estrategico PEI/BSC | 0 | `App\Models\StrategicPlanning\AtividadeCadeiaValor` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `strategic_planning.tab_futuro_almejado_objetivo` | Planejamento estrategico PEI/BSC | 0 | `App\Models\StrategicPlanning\FuturoAlmejado` | Futuro almejado associado a objetivos estrategicos. |
| `strategic_planning.tab_grau_satisfacao` | Planejamento estrategico PEI/BSC | 4 | `App\Models\StrategicPlanning\GrauSatisfacao` | Faixas percentuais e cores para classificacao de desempenho. |
| `strategic_planning.tab_missao_visao_valores` | Planejamento estrategico PEI/BSC | 1 | `App\Models\StrategicPlanning\MissaoVisaoValores` | Identidade estrategica por PEI/organizacao: missao e visao. |
| `strategic_planning.tab_nivel_hierarquico` | Planejamento estrategico PEI/BSC | 100 | Nao identificado | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `strategic_planning.tab_objetivo` | Planejamento estrategico PEI/BSC | 17 | `App\Models\StrategicPlanning\Objetivo` | Objetivos estrategicos vinculados a perspectivas. |
| `strategic_planning.tab_objetivo_comentarios` | Planejamento estrategico PEI/BSC | 0 | `App\Models\StrategicPlanning\ObjetivoComentario` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `strategic_planning.tab_pei` | Planejamento estrategico PEI/BSC | 1 | `App\Models\StrategicPlanning\PEI` | Ciclos de Planejamento Estrategico Institucional. |
| `strategic_planning.tab_perspectiva` | Planejamento estrategico PEI/BSC | 4 | `App\Models\StrategicPlanning\Perspectiva` | Perspectivas BSC do PEI, com pesos de indicadores e planos. |
| `strategic_planning.tab_processos_atividade_cadeia_valor` | Planejamento estrategico PEI/BSC | 0 | `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor` | Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais. |
| `strategic_planning.tab_tema_norteador` | Planejamento estrategico PEI/BSC | 4 | `App\Models\StrategicPlanning\TemaNorteador` | Temas norteadores por PEI/organizacao. |
| `strategic_planning.tab_valores` | Planejamento estrategico PEI/BSC | 5 | `App\Models\StrategicPlanning\Valor` | Valores institucionais por PEI/organizacao. |

## Relacionamentos por Foreign Key

| Origem | Coluna | Destino | Constraint | ON UPDATE | ON DELETE |
|---|---|---|---|---|---|
| `action_plan.acoes` | `user_id` | `pei.users.id` | `action_plan_acoes_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_entrega_labels` | `cod_entrega` | `action_plan.tab_entregas.cod_entrega` | `action_plan_rel_entrega_labels_cod_entrega_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_entrega_labels` | `cod_label` | `action_plan.tab_entrega_labels.cod_label` | `action_plan_rel_entrega_labels_cod_label_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_entrega_users_responsaveis` | `cod_entrega` | `action_plan.tab_entregas.cod_entrega` | `action_plan_rel_entrega_users_responsaveis_cod_entrega_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_entrega_users_responsaveis` | `cod_usuario` | `pei.users.id` | `action_plan_rel_entrega_users_responsaveis_cod_usuario_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_plano_organizacao` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `action_plan_rel_plano_organizacao_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.rel_plano_organizacao` | `cod_plano_de_acao` | `action_plan.tab_plano_de_acao.cod_plano_de_acao` | `action_plan_rel_plano_organizacao_cod_plano_de_acao_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entrega_anexos` | `cod_entrega` | `action_plan.tab_entregas.cod_entrega` | `action_plan_tab_entrega_anexos_cod_entrega_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entrega_comentarios` | `cod_comentario_pai` | `action_plan.tab_entrega_comentarios.cod_comentario` | `action_plan_tab_entrega_comentarios_cod_comentario_pai_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entrega_comentarios` | `cod_entrega` | `action_plan.tab_entregas.cod_entrega` | `action_plan_tab_entrega_comentarios_cod_entrega_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entrega_historico` | `cod_entrega` | `action_plan.tab_entregas.cod_entrega` | `action_plan_tab_entrega_historico_cod_entrega_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entrega_labels` | `cod_plano_de_acao` | `action_plan.tab_plano_de_acao.cod_plano_de_acao` | `action_plan_tab_entrega_labels_cod_plano_de_acao_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entregas` | `cod_plano_de_acao` | `action_plan.tab_plano_de_acao.cod_plano_de_acao` | `action_plan_tab_entregas_cod_plano_de_acao_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_entregas` | `cod_entrega_pai` | `action_plan.tab_entregas.cod_entrega` | `fk_entregas_entrega_pai` | `NO ACTION` | `SET NULL` |
| `action_plan.tab_entregas` | `cod_responsavel` | `pei.users.id` | `fk_entregas_responsavel` | `NO ACTION` | `SET NULL` |
| `action_plan.tab_plano_de_acao` | `cod_objetivo` | `strategic_planning.tab_objetivo.cod_objetivo` | `action_plan_tab_plano_de_acao_cod_objetivo_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_plano_de_acao` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `action_plan_tab_plano_de_acao_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `action_plan.tab_plano_de_acao` | `cod_tipo_execucao` | `action_plan.tab_tipo_execucao.cod_tipo_execucao` | `action_plan_tab_plano_de_acao_cod_tipo_execucao_foreign` | `NO ACTION` | `CASCADE` |
| `organization.rel_organizacao` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `organization_rel_organizacao_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `organization.rel_organizacao` | `rel_cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `organization_rel_organizacao_rel_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `organization_rel_users_tab_organizacoes_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes` | `user_id` | `pei.users.id` | `organization_rel_users_tab_organizacoes_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `fk_uopp_org` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | `cod_perfil` | `organization.tab_perfil_acesso.cod_perfil` | `fk_uopp_perfil` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | `cod_plano_de_acao` | `action_plan.tab_plano_de_acao.cod_plano_de_acao` | `fk_uopp_plano` | `NO ACTION` | `CASCADE` |
| `organization.rel_users_tab_organizacoes_tab_perfil_acesso` | `user_id` | `pei.users.id` | `fk_uopp_user` | `NO ACTION` | `CASCADE` |
| `performance_indicators.rel_indicador_objetivo_organizacao` | `cod_indicador` | `performance_indicators.tab_indicador.cod_indicador` | `fk_rioo_indicador` | `NO ACTION` | `CASCADE` |
| `performance_indicators.rel_indicador_objetivo_organizacao` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `fk_rioo_org` | `NO ACTION` | `CASCADE` |
| `performance_indicators.tab_evolucao_indicador` | `cod_indicador` | `performance_indicators.tab_indicador.cod_indicador` | `performance_indicators_tab_evolucao_indicador_cod_indicador_for` | `NO ACTION` | `CASCADE` |
| `performance_indicators.tab_indicador` | `cod_objetivo` | `strategic_planning.tab_objetivo.cod_objetivo` | `performance_indicators_tab_indicador_cod_objetivo_foreign` | `NO ACTION` | `CASCADE` |
| `performance_indicators.tab_indicador` | `cod_plano_de_acao` | `action_plan.tab_plano_de_acao.cod_plano_de_acao` | `performance_indicators_tab_indicador_cod_plano_de_acao_foreign` | `NO ACTION` | `CASCADE` |
| `performance_indicators.tab_linha_base_indicador` | `cod_indicador` | `performance_indicators.tab_indicador.cod_indicador` | `performance_indicators_tab_linha_base_indicador_cod_indicador_f` | `CASCADE` | `CASCADE` |
| `performance_indicators.tab_meta_por_ano` | `cod_indicador` | `performance_indicators.tab_indicador.cod_indicador` | `performance_indicators_tab_meta_por_ano_cod_indicador_foreign` | `NO ACTION` | `CASCADE` |
| `pei.strategic_alerts` | `user_id` | `pei.users.id` | `strategic_alerts_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_analise_ambiental` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `tab_analise_ambiental_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_analise_ambiental` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `tab_analise_ambiental_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `pei.tab_audit` | `user_id` | `pei.users.id` | `tab_audit_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `pei.tab_relatorios_agendados` | `user_id` | `pei.users.id` | `tab_relatorios_agendados_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `pei.tab_relatorios_gerados` | `user_id` | `pei.users.id` | `tab_relatorios_gerados_user_id_foreign` | `NO ACTION` | `SET NULL` |
| `risk_management.tab_risco` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `risk_management_tab_risco_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `risk_management.tab_risco` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `risk_management_tab_risco_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `risk_management.tab_risco` | `cod_responsavel_monitoramento` | `pei.users.id` | `risk_management_tab_risco_cod_responsavel_monitoramento_foreign` | `NO ACTION` | `SET NULL` |
| `risk_management.tab_risco_mitigacao` | `cod_responsavel` | `pei.users.id` | `risk_management_tab_risco_mitigacao_cod_responsavel_foreign` | `NO ACTION` | `SET NULL` |
| `risk_management.tab_risco_mitigacao` | `cod_risco` | `risk_management.tab_risco.cod_risco` | `risk_management_tab_risco_mitigacao_cod_risco_foreign` | `NO ACTION` | `CASCADE` |
| `risk_management.tab_risco_objetivo` | `cod_objetivo` | `strategic_planning.tab_objetivo.cod_objetivo` | `risk_management_tab_risco_objetivo_cod_objetivo_foreign` | `NO ACTION` | `CASCADE` |
| `risk_management.tab_risco_objetivo` | `cod_risco` | `risk_management.tab_risco.cod_risco` | `risk_management_tab_risco_objetivo_cod_risco_foreign` | `NO ACTION` | `CASCADE` |
| `risk_management.tab_risco_ocorrencia` | `cod_risco` | `risk_management.tab_risco.cod_risco` | `risk_management_tab_risco_ocorrencia_cod_risco_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_arquivos` | `cod_evolucao_indicador` | `performance_indicators.tab_evolucao_indicador.cod_evolucao_indicador` | `strategic_planning_tab_arquivos_cod_evolucao_indicador_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_atividade_cadeia_valor` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_atividade_cadeia_valor_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_atividade_cadeia_valor` | `cod_perspectiva` | `strategic_planning.tab_perspectiva.cod_perspectiva` | `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_f` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_futuro_almejado_objetivo` | `cod_objetivo` | `strategic_planning.tab_objetivo.cod_objetivo` | `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_grau_satisfacao` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_grau_satisfacao_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_missao_visao_valores` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `strategic_planning_tab_missao_visao_valores_cod_organizacao_for` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_missao_visao_valores` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_missao_visao_valores_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_objetivo` | `cod_perspectiva` | `strategic_planning.tab_perspectiva.cod_perspectiva` | `strategic_planning_tab_objetivo_estrategico_cod_perspectiva_for` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_objetivo_comentarios` | `cod_objetivo` | `strategic_planning.tab_objetivo.cod_objetivo` | `strategic_planning_tab_objetivo_comentarios_cod_objetivo_foreig` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_objetivo_comentarios` | `user_id` | `pei.users.id` | `strategic_planning_tab_objetivo_comentarios_user_id_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_perspectiva` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_perspectiva_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_processos_atividade_cadeia_valor` | `cod_atividade_cadeia_valor` | `strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor` | `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_tema_norteador` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `strategic_planning_tab_objetivo_estrategico_cod_organizacao_for` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_tema_norteador` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_objetivo_estrategico_cod_pei_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_valores` | `cod_organizacao` | `organization.tab_organizacoes.cod_organizacao` | `strategic_planning_tab_valores_cod_organizacao_foreign` | `NO ACTION` | `CASCADE` |
| `strategic_planning.tab_valores` | `cod_pei` | `strategic_planning.tab_pei.cod_pei` | `strategic_planning_tab_valores_cod_pei_foreign` | `NO ACTION` | `CASCADE` |

## Tabelas pivot e associativas identificadas

- `action_plan.rel_entrega_labels`: Pivot entre entregas e labels..
- `action_plan.rel_entrega_users_responsaveis`: Pivot de responsaveis por entrega..
- `action_plan.rel_plano_organizacao`: Pivot de multivinculacao entre planos de acao e organizacoes.
- `organization.rel_organizacao`: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais..
- `organization.rel_users_tab_organizacoes`: Pivot de associacao entre usuarios e organizacoes..
- `organization.rel_users_tab_organizacoes_tab_perfil_acesso`: Pivot de perfis por usuario, organizacao e opcionalmente plano de acao.
- `performance_indicators.rel_indicador_objetivo_organizacao`: Pivot de indicadores/objetivos/organizacoes..

## Dicionario detalhado por tabela

### `action_plan.acoes`

- Modulo: Planos de acao e entregas.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\ActionPlan\Acao` em `app/Models/ActionPlan/Acao.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `table_id` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id |
| 4 | `table` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `acao` | `text / text` | `NO` | `` |  |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_acoes_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `acoes_pkey` coluna `id`

#### Indices

- `acoes_pkey`: `CREATE UNIQUE INDEX acoes_pkey ON action_plan.acoes USING btree (id)`
- `action_plan_acoes_table_table_id_index`: `CREATE INDEX action_plan_acoes_table_table_id_index ON action_plan.acoes USING btree ("table", table_id)`
- `action_plan_acoes_user_id_index`: `CREATE INDEX action_plan_acoes_user_id_index ON action_plan.acoes USING btree (user_id)`

### `action_plan.rel_entrega_labels`

- Modulo: Planos de acao e entregas.
- Finalidade: Pivot entre entregas e labels.
- Linhas no momento da consulta: 1.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_entrega` | `uuid / uuid` | `NO` | `` | PK; FK para action_plan.tab_entregas.cod_entrega |
| 2 | `cod_label` | `uuid / uuid` | `NO` | `` | PK; FK para action_plan.tab_entrega_labels.cod_label |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_labels_cod_label_foreign` coluna `cod_label` -> action_plan.tab_entrega_labels.cod_label
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_entrega`
- `PRIMARY KEY` `rel_entrega_labels_pkey` coluna `cod_label`

#### Indices

- `rel_entrega_labels_pkey`: `CREATE UNIQUE INDEX rel_entrega_labels_pkey ON action_plan.rel_entrega_labels USING btree (cod_entrega, cod_label)`

### `action_plan.rel_entrega_users_responsaveis`

- Modulo: Planos de acao e entregas.
- Finalidade: Pivot de responsaveis por entrega.
- Linhas no momento da consulta: 85.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_entrega` | `uuid / uuid` | `NO` | `` | PK; FK para action_plan.tab_entregas.cod_entrega |
| 2 | `cod_usuario` | `uuid / uuid` | `NO` | `` | PK; FK para pei.users.id |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 4 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `action_plan_rel_entrega_users_responsaveis_cod_usuario_foreign` coluna `cod_usuario` -> pei.users.id
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_entrega`
- `PRIMARY KEY` `rel_entrega_users_responsaveis_pkey` coluna `cod_usuario`

#### Indices

- `rel_entrega_users_responsaveis_pkey`: `CREATE UNIQUE INDEX rel_entrega_users_responsaveis_pkey ON action_plan.rel_entrega_users_responsaveis USING btree (cod_entrega, cod_usuario)`

### `action_plan.rel_plano_organizacao`

- Modulo: Planos de acao e entregas.
- Finalidade: Pivot de multivinculacao entre planos de acao e organizacoes.
- Linhas no momento da consulta: 21.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_plano_de_acao` | `uuid / uuid` | `NO` | `` | PK; FK para action_plan.tab_plano_de_acao.cod_plano_de_acao |
| 2 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | PK; FK para organization.tab_organizacoes.cod_organizacao |

#### Constraints

- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `action_plan_rel_plano_organizacao_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_plano_de_acao`
- `PRIMARY KEY` `rel_plano_organizacao_pkey` coluna `cod_organizacao`

#### Indices

- `rel_plano_organizacao_pkey`: `CREATE UNIQUE INDEX rel_plano_organizacao_pkey ON action_plan.rel_plano_organizacao USING btree (cod_plano_de_acao, cod_organizacao)`

### `action_plan.tab_entrega_anexos`

- Modulo: Planos de acao e entregas.
- Finalidade: Anexos enviados em entregas.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\ActionPlan\EntregaAnexo` em `app/Models/ActionPlan/EntregaAnexo.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_anexo` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_entrega` | `uuid / uuid` | `NO` | `` | FK para action_plan.tab_entregas.cod_entrega |
| 3 | `cod_usuario` | `uuid / uuid` | `NO` | `` |  |
| 4 | `dsc_nome_arquivo` | `character varying / varchar(255)` | `NO` | `` |  |
| 5 | `dsc_caminho` | `character varying / varchar(500)` | `NO` | `` |  |
| 6 | `dsc_mime_type` | `character varying / varchar(100)` | `NO` | `` |  |
| 7 | `num_tamanho_bytes` | `bigint / int8(64,0)` | `NO` | `` |  |
| 8 | `dsc_descricao` | `character varying / varchar(500)` | `YES` | `` |  |
| 9 | `dsc_thumbnail` | `text / text` | `YES` | `` |  |
| 10 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 11 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 12 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_entrega_anexos_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_anexos_pkey` coluna `cod_anexo`

#### Indices

- `idx_anexos_entrega`: `CREATE INDEX idx_anexos_entrega ON action_plan.tab_entrega_anexos USING btree (cod_entrega)`
- `idx_anexos_mime`: `CREATE INDEX idx_anexos_mime ON action_plan.tab_entrega_anexos USING btree (dsc_mime_type)`
- `idx_anexos_usuario`: `CREATE INDEX idx_anexos_usuario ON action_plan.tab_entrega_anexos USING btree (cod_usuario)`
- `tab_entrega_anexos_pkey`: `CREATE UNIQUE INDEX tab_entrega_anexos_pkey ON action_plan.tab_entrega_anexos USING btree (cod_anexo)`

### `action_plan.tab_entrega_comentarios`

- Modulo: Planos de acao e entregas.
- Finalidade: Comentarios em entregas, inclusive respostas por comentario pai.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\ActionPlan\EntregaComentario` em `app/Models/ActionPlan/EntregaComentario.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_comentario` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_entrega` | `uuid / uuid` | `NO` | `` | FK para action_plan.tab_entregas.cod_entrega |
| 3 | `cod_usuario` | `uuid / uuid` | `NO` | `` |  |
| 4 | `dsc_comentario` | `text / text` | `NO` | `` |  |
| 5 | `json_mencoes` | `jsonb / jsonb` | `YES` | `` |  |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `cod_comentario_pai` | `uuid / uuid` | `YES` | `` | FK para action_plan.tab_entrega_comentarios.cod_comentario |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_comentario_pai_foreign` coluna `cod_comentario_pai` -> action_plan.tab_entrega_comentarios.cod_comentario
- `FOREIGN KEY` `action_plan_tab_entrega_comentarios_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_comentarios_pkey` coluna `cod_comentario`

#### Indices

- `idx_comentarios_data`: `CREATE INDEX idx_comentarios_data ON action_plan.tab_entrega_comentarios USING btree (created_at)`
- `idx_comentarios_entrega`: `CREATE INDEX idx_comentarios_entrega ON action_plan.tab_entrega_comentarios USING btree (cod_entrega)`
- `idx_comentarios_pai`: `CREATE INDEX idx_comentarios_pai ON action_plan.tab_entrega_comentarios USING btree (cod_comentario_pai)`
- `idx_comentarios_usuario`: `CREATE INDEX idx_comentarios_usuario ON action_plan.tab_entrega_comentarios USING btree (cod_usuario)`
- `tab_entrega_comentarios_pkey`: `CREATE UNIQUE INDEX tab_entrega_comentarios_pkey ON action_plan.tab_entrega_comentarios USING btree (cod_comentario)`

### `action_plan.tab_entrega_historico`

- Modulo: Planos de acao e entregas.
- Finalidade: Historico de alteracoes e eventos de entregas.
- Linhas no momento da consulta: 85.
- Models relacionados: `App\Models\ActionPlan\EntregaHistorico` em `app/Models/ActionPlan/EntregaHistorico.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_historico` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_entrega` | `uuid / uuid` | `NO` | `` | FK para action_plan.tab_entregas.cod_entrega |
| 3 | `cod_usuario` | `uuid / uuid` | `YES` | `` |  |
| 4 | `dsc_acao` | `character varying / varchar(50)` | `NO` | `` |  |
| 5 | `dsc_campo` | `character varying / varchar(100)` | `YES` | `` |  |
| 6 | `json_valor_antigo` | `jsonb / jsonb` | `YES` | `` |  |
| 7 | `json_valor_novo` | `jsonb / jsonb` | `YES` | `` |  |
| 8 | `dsc_descricao` | `text / text` | `YES` | `` |  |
| 9 | `created_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_entrega_historico_cod_entrega_foreign` coluna `cod_entrega` -> action_plan.tab_entregas.cod_entrega
- `PRIMARY KEY` `tab_entrega_historico_pkey` coluna `cod_historico`

#### Indices

- `idx_historico_acao`: `CREATE INDEX idx_historico_acao ON action_plan.tab_entrega_historico USING btree (dsc_acao)`
- `idx_historico_data`: `CREATE INDEX idx_historico_data ON action_plan.tab_entrega_historico USING btree (created_at)`
- `idx_historico_entrega`: `CREATE INDEX idx_historico_entrega ON action_plan.tab_entrega_historico USING btree (cod_entrega)`
- `idx_historico_usuario`: `CREATE INDEX idx_historico_usuario ON action_plan.tab_entrega_historico USING btree (cod_usuario)`
- `tab_entrega_historico_pkey`: `CREATE UNIQUE INDEX tab_entrega_historico_pkey ON action_plan.tab_entrega_historico USING btree (cod_historico)`

### `action_plan.tab_entrega_labels`

- Modulo: Planos de acao e entregas.
- Finalidade: Labels por plano para classificar entregas.
- Linhas no momento da consulta: 1.
- Models relacionados: `App\Models\ActionPlan\EntregaLabel` em `app/Models/ActionPlan/EntregaLabel.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_label` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_plano_de_acao` | `uuid / uuid` | `NO` | `` | FK para action_plan.tab_plano_de_acao.cod_plano_de_acao |
| 3 | `dsc_label` | `character varying / varchar(100)` | `NO` | `` |  |
| 4 | `dsc_cor` | `character varying / varchar(7)` | `NO` | `'#6366f1'::character varying` |  |
| 5 | `dsc_icone` | `character varying / varchar(50)` | `YES` | `` |  |
| 6 | `num_ordem` | `integer / int4(32,0)` | `NO` | `0` |  |
| 7 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_entrega_labels_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `tab_entrega_labels_pkey` coluna `cod_label`

#### Indices

- `idx_labels_ordem`: `CREATE INDEX idx_labels_ordem ON action_plan.tab_entrega_labels USING btree (num_ordem)`
- `idx_labels_plano`: `CREATE INDEX idx_labels_plano ON action_plan.tab_entrega_labels USING btree (cod_plano_de_acao)`
- `tab_entrega_labels_pkey`: `CREATE UNIQUE INDEX tab_entrega_labels_pkey ON action_plan.tab_entrega_labels USING btree (cod_label)`

### `action_plan.tab_entregas`

- Modulo: Planos de acao e entregas.
- Finalidade: Entregas/tarefas de planos de acao, com status, prazo, pesos, hierarquia e soft delete.
- Linhas no momento da consulta: 84.
- Models relacionados: `App\Models\ActionPlan\Entrega` em `app/Models/ActionPlan/Entrega.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_entrega` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` | FK para action_plan.tab_plano_de_acao.cod_plano_de_acao |
| 3 | `dsc_entrega` | `text / text` | `NO` | `` |  |
| 4 | `bln_status` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `dsc_periodo_medicao` | `character varying / varchar(191)` | `YES` | `` | Tornado nullable em 2026-06-25 (migration 2026_06_25_032649). Campo vestigial para entregas; registros com '' convertidos para NULL. |
| 6 | `num_nivel_hierarquico_apresentacao` | `smallint / int2(16,0)` | `NO` | `` |  |
| 7 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `cod_entrega_pai` | `uuid / uuid` | `YES` | `` | FK para action_plan.tab_entregas.cod_entrega |
| 11 | `dsc_tipo` | `character varying / varchar(50)` | `NO` | `'task'::character varying` |  |
| 12 | `json_propriedades` | `jsonb / jsonb` | `YES` | `` |  |
| 13 | `dte_prazo` | `date / date` | `YES` | `` |  |
| 14 | `cod_responsavel` | `uuid / uuid` | `YES` | `` | FK para pei.users.id |
| 15 | `cod_prioridade` | `character varying / varchar(20)` | `NO` | `'media'::character varying` |  |
| 16 | `num_ordem` | `integer / int4(32,0)` | `NO` | `0` |  |
| 17 | `bln_arquivado` | `boolean / bool` | `NO` | `false` |  |
| 18 | `num_peso` | `numeric / numeric(8,2)` | `NO` | `'0'::numeric` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_entregas_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `FOREIGN KEY` `fk_entregas_entrega_pai` coluna `cod_entrega_pai` -> action_plan.tab_entregas.cod_entrega
- `FOREIGN KEY` `fk_entregas_responsavel` coluna `cod_responsavel` -> pei.users.id
- `PRIMARY KEY` `tab_entregas_pkey` coluna `cod_entrega`

#### Indices

- `action_plan_tab_entregas_cod_plano_de_acao_index`: `CREATE INDEX action_plan_tab_entregas_cod_plano_de_acao_index ON action_plan.tab_entregas USING btree (cod_plano_de_acao)`
- `idx_entregas_arquivado`: `CREATE INDEX idx_entregas_arquivado ON action_plan.tab_entregas USING btree (bln_arquivado)`
- `idx_entregas_entrega_pai`: `CREATE INDEX idx_entregas_entrega_pai ON action_plan.tab_entregas USING btree (cod_entrega_pai)`
- `idx_entregas_ordem`: `CREATE INDEX idx_entregas_ordem ON action_plan.tab_entregas USING btree (num_ordem)`
- `idx_entregas_peso`: `CREATE INDEX idx_entregas_peso ON action_plan.tab_entregas USING btree (num_peso)`
- `idx_entregas_prazo`: `CREATE INDEX idx_entregas_prazo ON action_plan.tab_entregas USING btree (dte_prazo)`
- `idx_entregas_prioridade`: `CREATE INDEX idx_entregas_prioridade ON action_plan.tab_entregas USING btree (cod_prioridade)`
- `idx_entregas_responsavel`: `CREATE INDEX idx_entregas_responsavel ON action_plan.tab_entregas USING btree (cod_responsavel)`
- `idx_entregas_tipo`: `CREATE INDEX idx_entregas_tipo ON action_plan.tab_entregas USING btree (dsc_tipo)`
- `tab_entregas_pkey`: `CREATE UNIQUE INDEX tab_entregas_pkey ON action_plan.tab_entregas USING btree (cod_entrega)`

### `action_plan.tab_plano_de_acao`

- Modulo: Planos de acao e entregas.
- Finalidade: Planos de acao/iniciativas/projetos vinculados a objetivos e organizacoes.
- Linhas no momento da consulta: 21.
- Models relacionados: `App\Models\ActionPlan\PlanoDeAcao` em `app/Models/ActionPlan/PlanoDeAcao.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_plano_de_acao` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_objetivo` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_objetivo.cod_objetivo |
| 3 | `cod_tipo_execucao` | `uuid / uuid` | `NO` | `` | FK para action_plan.tab_tipo_execucao.cod_tipo_execucao |
| 4 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 5 | `num_nivel_hierarquico_apresentacao` | `smallint / int2(16,0)` | `NO` | `` |  |
| 6 | `dsc_plano_de_acao` | `text / text` | `NO` | `` |  |
| 7 | `dte_inicio` | `date / date` | `NO` | `` |  |
| 8 | `dte_fim` | `date / date` | `NO` | `` |  |
| 9 | `vlr_orcamento_previsto` | `numeric / numeric(15,2)` | `YES` | `` |  |
| 10 | `bln_status` | `character varying / varchar(191)` | `NO` | `` |  |
| 11 | `cod_ppa` | `character varying / varchar(191)` | `YES` | `` |  |
| 12 | `cod_loa` | `character varying / varchar(191)` | `YES` | `` |  |
| 13 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 14 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 15 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 16 | `txt_detalhamento` | `text / text` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_objetivo_foreign` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `action_plan_tab_plano_de_acao_cod_tipo_execucao_foreign` coluna `cod_tipo_execucao` -> action_plan.tab_tipo_execucao.cod_tipo_execucao
- `PRIMARY KEY` `tab_plano_de_acao_pkey` coluna `cod_plano_de_acao`

#### Indices

- `action_plan_tab_plano_de_acao_bln_status_index`: `CREATE INDEX action_plan_tab_plano_de_acao_bln_status_index ON action_plan.tab_plano_de_acao USING btree (bln_status)`
- `action_plan_tab_plano_de_acao_cod_objetivo_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_objetivo_index ON action_plan.tab_plano_de_acao USING btree (cod_objetivo)`
- `action_plan_tab_plano_de_acao_cod_organizacao_index`: `CREATE INDEX action_plan_tab_plano_de_acao_cod_organizacao_index ON action_plan.tab_plano_de_acao USING btree (cod_organizacao)`
- `tab_plano_de_acao_pkey`: `CREATE UNIQUE INDEX tab_plano_de_acao_pkey ON action_plan.tab_plano_de_acao USING btree (cod_plano_de_acao)`

### `action_plan.tab_tipo_execucao`

- Modulo: Planos de acao e entregas.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 4.
- Models relacionados: `App\Models\ActionPlan\TipoExecucao` em `app/Models/ActionPlan/TipoExecucao.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_tipo_execucao` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_tipo_execucao` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 4 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_tipo_execucao_pkey` coluna `cod_tipo_execucao`

#### Indices

- `tab_tipo_execucao_pkey`: `CREATE UNIQUE INDEX tab_tipo_execucao_pkey ON action_plan.tab_tipo_execucao USING btree (cod_tipo_execucao)`

### `organization.rel_organizacao`

- Modulo: Organizacao e acesso institucional.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao; UNIQUE |
| 3 | `rel_cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao; UNIQUE |
| 4 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `organization_rel_organizacao_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_organizacao_rel_cod_organizacao_foreign` coluna `rel_cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `PRIMARY KEY` `rel_organizacao_pkey` coluna `id`
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `cod_organizacao`
- `UNIQUE` `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca` coluna `rel_cod_organizacao`

#### Indices

- `organization_rel_organizacao_cod_organizacao_rel_cod_organizaca`: `CREATE UNIQUE INDEX organization_rel_organizacao_cod_organizacao_rel_cod_organizaca ON organization.rel_organizacao USING btree (cod_organizacao, rel_cod_organizacao)`
- `rel_organizacao_pkey`: `CREATE UNIQUE INDEX rel_organizacao_pkey ON organization.rel_organizacao USING btree (id)`

### `organization.rel_users_tab_organizacoes`

- Modulo: Organizacao e acesso institucional.
- Finalidade: Pivot de associacao entre usuarios e organizacoes.
- Linhas no momento da consulta: 5.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id; UNIQUE |
| 3 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao; UNIQUE |
| 4 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `organization_rel_users_tab_organizacoes_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `rel_users_tab_organizacoes_pkey` coluna `id`
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `user_id`
- `UNIQUE` `organization_rel_users_tab_organizacoes_user_id_cod_organizacao` coluna `cod_organizacao`

#### Indices

- `organization_rel_users_tab_organizacoes_user_id_cod_organizacao`: `CREATE UNIQUE INDEX organization_rel_users_tab_organizacoes_user_id_cod_organizacao ON organization.rel_users_tab_organizacoes USING btree (user_id, cod_organizacao)`
- `rel_users_tab_organizacoes_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_pkey ON organization.rel_users_tab_organizacoes USING btree (id)`

### `organization.rel_users_tab_organizacoes_tab_perfil_acesso`

- Modulo: Organizacao e acesso institucional.
- Finalidade: Pivot de perfis por usuario, organizacao e opcionalmente plano de acao.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id; UNIQUE |
| 3 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao; UNIQUE |
| 4 | `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` | FK para action_plan.tab_plano_de_acao.cod_plano_de_acao; UNIQUE |
| 5 | `cod_perfil` | `uuid / uuid` | `NO` | `` | FK para organization.tab_perfil_acesso.cod_perfil; UNIQUE |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `fk_uopp_org` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `fk_uopp_perfil` coluna `cod_perfil` -> organization.tab_perfil_acesso.cod_perfil
- `FOREIGN KEY` `fk_uopp_plano` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `FOREIGN KEY` `fk_uopp_user` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `rel_users_tab_organizacoes_tab_perfil_acesso_pkey` coluna `id`
- `UNIQUE` `rel_uopp_unique` coluna `user_id`
- `UNIQUE` `rel_uopp_unique` coluna `cod_organizacao`
- `UNIQUE` `rel_uopp_unique` coluna `cod_plano_de_acao`
- `UNIQUE` `rel_uopp_unique` coluna `cod_perfil`

#### Indices

- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_o ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_organizacao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_cod_p ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (cod_plano_de_acao)`
- `organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_`: `CREATE INDEX organization_rel_users_tab_organizacoes_tab_perfil_acesso_user_ ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id)`
- `rel_uopp_unique`: `CREATE UNIQUE INDEX rel_uopp_unique ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (user_id, cod_organizacao, cod_plano_de_acao, cod_perfil)`
- `rel_users_tab_organizacoes_tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX rel_users_tab_organizacoes_tab_perfil_acesso_pkey ON organization.rel_users_tab_organizacoes_tab_perfil_acesso USING btree (id)`

### `organization.tab_organizacoes`

- Modulo: Organizacao e acesso institucional.
- Finalidade: Cadastro hierarquico de organizacoes/unidades institucionais.
- Linhas no momento da consulta: 6.
- Models relacionados: `App\Models\Organization` em `app/Models/Organization.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_organizacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `sgl_organizacao` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `nom_organizacao` | `text / text` | `NO` | `` |  |
| 4 | `rel_cod_organizacao` | `uuid / uuid` | `YES` | `` |  |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_organizacoes_pkey` coluna `cod_organizacao`

#### Indices

- `tab_organizacoes_pkey`: `CREATE UNIQUE INDEX tab_organizacoes_pkey ON organization.tab_organizacoes USING btree (cod_organizacao)`

### `organization.tab_perfil_acesso`

- Modulo: Organizacao e acesso institucional.
- Finalidade: Catalogo de perfis de acesso usados pelas policies e pivots de usuario.
- Linhas no momento da consulta: 4.
- Models relacionados: `App\Models\PerfilAcesso` em `app/Models/PerfilAcesso.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_perfil` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_perfil` | `text / text` | `NO` | `` |  |
| 3 | `dsc_permissao` | `text / text` | `NO` | `` |  |
| 4 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_perfil_acesso_pkey` coluna `cod_perfil`

#### Indices

- `tab_perfil_acesso_pkey`: `CREATE UNIQUE INDEX tab_perfil_acesso_pkey ON organization.tab_perfil_acesso USING btree (cod_perfil)`

### `performance_indicators.rel_indicador_objetivo_organizacao`

- Modulo: Indicadores de desempenho.
- Finalidade: Pivot de indicadores/objetivos/organizacoes.
- Linhas no momento da consulta: 17.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_indicador` | `uuid / uuid` | `NO` | `` | PK; FK para performance_indicators.tab_indicador.cod_indicador |
| 2 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | PK; FK para organization.tab_organizacoes.cod_organizacao |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 4 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `fk_rioo_indicador` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `FOREIGN KEY` `fk_rioo_org` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_indicador`
- `PRIMARY KEY` `rel_indicador_objetivo_estrategico_organizacao_pkey` coluna `cod_organizacao`

#### Indices

- `rel_indicador_objetivo_estrategico_organizacao_pkey`: `CREATE UNIQUE INDEX rel_indicador_objetivo_estrategico_organizacao_pkey ON performance_indicators.rel_indicador_objetivo_organizacao USING btree (cod_indicador, cod_organizacao)`

### `performance_indicators.tab_evolucao_indicador`

- Modulo: Indicadores de desempenho.
- Finalidade: Lancamentos periodicos de realizado/previsto dos indicadores.
- Linhas no momento da consulta: 51.
- Models relacionados: `App\Models\PerformanceIndicators\EvolucaoIndicador` em `app/Models/PerformanceIndicators/EvolucaoIndicador.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_evolucao_indicador` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_indicador` | `uuid / uuid` | `NO` | `` | FK para performance_indicators.tab_indicador.cod_indicador |
| 3 | `num_ano` | `smallint / int2(16,0)` | `NO` | `` |  |
| 4 | `num_mes` | `smallint / int2(16,0)` | `NO` | `` |  |
| 5 | `vlr_previsto` | `numeric / numeric(15,2)` | `YES` | `` |  |
| 6 | `vlr_realizado` | `numeric / numeric(15,2)` | `YES` | `` |  |
| 7 | `txt_avaliacao` | `text / text` | `YES` | `` |  |
| 8 | `bln_atualizado` | `character varying / varchar(191)` | `YES` | `` |  |
| 9 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 11 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `performance_indicators_tab_evolucao_indicador_cod_indicador_for` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_evolucao_indicador_pkey` coluna `cod_evolucao_indicador`

#### Indices

- `performance_indicators_tab_evolucao_indicador_cod_indicador_num`: `CREATE INDEX performance_indicators_tab_evolucao_indicador_cod_indicador_num ON performance_indicators.tab_evolucao_indicador USING btree (cod_indicador, num_ano, num_mes)`
- `tab_evolucao_indicador_pkey`: `CREATE UNIQUE INDEX tab_evolucao_indicador_pkey ON performance_indicators.tab_evolucao_indicador USING btree (cod_evolucao_indicador)`

### `performance_indicators.tab_indicador`

- Modulo: Indicadores de desempenho.
- Finalidade: Indicadores/KPIs vinculados a objetivos ou planos.
- Linhas no momento da consulta: 17.
- Models relacionados: `App\Models\PerformanceIndicators\Indicador` em `app/Models/PerformanceIndicators/Indicador.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_indicador` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_plano_de_acao` | `uuid / uuid` | `YES` | `` | FK para action_plan.tab_plano_de_acao.cod_plano_de_acao |
| 3 | `cod_objetivo` | `uuid / uuid` | `YES` | `` | FK para strategic_planning.tab_objetivo.cod_objetivo |
| 4 | `dsc_tipo` | `text / text` | `NO` | `` |  |
| 5 | `nom_indicador` | `text / text` | `NO` | `` |  |
| 6 | `dsc_indicador` | `text / text` | `NO` | `` |  |
| 7 | `txt_observacao` | `text / text` | `YES` | `` |  |
| 8 | `dsc_meta` | `text / text` | `YES` | `` |  |
| 9 | `dsc_atributos` | `text / text` | `YES` | `` |  |
| 10 | `dsc_referencial_comparativo` | `text / text` | `YES` | `` |  |
| 11 | `dsc_unidade_medida` | `text / text` | `NO` | `` |  |
| 12 | `num_peso` | `smallint / int2(16,0)` | `YES` | `` |  |
| 13 | `bln_acumulado` | `character varying / varchar(191)` | `NO` | `` |  |
| 14 | `dsc_formula` | `text / text` | `YES` | `` |  |
| 15 | `dsc_fonte` | `character varying / varchar(191)` | `YES` | `` |  |
| 16 | `dsc_periodo_medicao` | `character varying / varchar(191)` | `NO` | `` |  |
| 17 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 18 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 19 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 20 | `dsc_polaridade` | `character varying / varchar(191)` | `YES` | `` |  |
| 21 | `dsc_calculation_type` | `character varying / varchar(20)` | `NO` | `'manual'::character varying` |  |

#### Constraints

- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_objetivo_foreign` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `performance_indicators_tab_indicador_cod_plano_de_acao_foreign` coluna `cod_plano_de_acao` -> action_plan.tab_plano_de_acao.cod_plano_de_acao
- `PRIMARY KEY` `tab_indicador_pkey` coluna `cod_indicador`

#### Indices

- `idx_indicador_calculation_type`: `CREATE INDEX idx_indicador_calculation_type ON performance_indicators.tab_indicador USING btree (dsc_calculation_type)`
- `performance_indicators_tab_indicador_cod_objetivo_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_objetivo_index ON performance_indicators.tab_indicador USING btree (cod_objetivo)`
- `performance_indicators_tab_indicador_cod_plano_de_acao_index`: `CREATE INDEX performance_indicators_tab_indicador_cod_plano_de_acao_index ON performance_indicators.tab_indicador USING btree (cod_plano_de_acao)`
- `tab_indicador_pkey`: `CREATE UNIQUE INDEX tab_indicador_pkey ON performance_indicators.tab_indicador USING btree (cod_indicador)`

### `performance_indicators.tab_linha_base_indicador`

- Modulo: Indicadores de desempenho.
- Finalidade: Linhas de base anuais de indicadores.
- Linhas no momento da consulta: 17.
- Models relacionados: `App\Models\PerformanceIndicators\LinhaBaseIndicador` em `app/Models/PerformanceIndicators/LinhaBaseIndicador.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_linha_base` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_indicador` | `uuid / uuid` | `NO` | `` | FK para performance_indicators.tab_indicador.cod_indicador |
| 3 | `num_linha_base` | `numeric / numeric(15,2)` | `NO` | `` |  |
| 4 | `num_ano` | `smallint / int2(16,0)` | `NO` | `` |  |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `performance_indicators_tab_linha_base_indicador_cod_indicador_f` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_linha_base_indicador_pkey` coluna `cod_linha_base`

#### Indices

- `performance_indicators_tab_linha_base_indicador_cod_indicador_n`: `CREATE INDEX performance_indicators_tab_linha_base_indicador_cod_indicador_n ON performance_indicators.tab_linha_base_indicador USING btree (cod_indicador, num_ano)`
- `tab_linha_base_indicador_pkey`: `CREATE UNIQUE INDEX tab_linha_base_indicador_pkey ON performance_indicators.tab_linha_base_indicador USING btree (cod_linha_base)`

### `performance_indicators.tab_meta_por_ano`

- Modulo: Indicadores de desempenho.
- Finalidade: Metas anuais de indicadores.
- Linhas no momento da consulta: 17.
- Models relacionados: `App\Models\PerformanceIndicators\MetaPorAno` em `app/Models/PerformanceIndicators/MetaPorAno.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_meta_por_ano` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_indicador` | `uuid / uuid` | `NO` | `` | FK para performance_indicators.tab_indicador.cod_indicador |
| 3 | `num_ano` | `smallint / int2(16,0)` | `NO` | `` |  |
| 4 | `meta` | `numeric / numeric(15,2)` | `YES` | `` |  |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `performance_indicators_tab_meta_por_ano_cod_indicador_foreign` coluna `cod_indicador` -> performance_indicators.tab_indicador.cod_indicador
- `PRIMARY KEY` `tab_meta_por_ano_pkey` coluna `cod_meta_por_ano`

#### Indices

- `performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i`: `CREATE INDEX performance_indicators_tab_meta_por_ano_cod_indicador_num_ano_i ON performance_indicators.tab_meta_por_ano USING btree (cod_indicador, num_ano)`
- `tab_meta_por_ano_pkey`: `CREATE UNIQUE INDEX tab_meta_por_ano_pkey ON performance_indicators.tab_meta_por_ano USING btree (cod_meta_por_ano)`

### `pei.audits`

- Modulo: Auditoria.
- Finalidade: Tabela do pacote owen-it/laravel-auditing.
- Linhas no momento da consulta: 1.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint / int8(64,0)` | `NO` | `nextval('audits_id_seq'::regclass)` | PK; sequencial |
| 2 | `user_type` | `character varying / varchar(191)` | `YES` | `` |  |
| 3 | `user_id` | `uuid / uuid` | `YES` | `` |  |
| 4 | `event` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `auditable_type` | `character varying / varchar(191)` | `NO` | `` |  |
| 6 | `auditable_id` | `uuid / uuid` | `NO` | `` |  |
| 7 | `old_values` | `text / text` | `YES` | `` |  |
| 8 | `new_values` | `text / text` | `YES` | `` |  |
| 9 | `url` | `text / text` | `YES` | `` |  |
| 10 | `ip_address` | `inet / inet` | `YES` | `` |  |
| 11 | `user_agent` | `character varying / varchar(1023)` | `YES` | `` |  |
| 12 | `tags` | `character varying / varchar(191)` | `YES` | `` |  |
| 13 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 14 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `audits_pkey` coluna `id`

#### Indices

- `audits_auditable_id_auditable_type_index`: `CREATE INDEX audits_auditable_id_auditable_type_index ON pei.audits USING btree (auditable_id, auditable_type)`
- `audits_pkey`: `CREATE UNIQUE INDEX audits_pkey ON pei.audits USING btree (id)`
- `audits_user_id_user_type_index`: `CREATE INDEX audits_user_id_user_type_index ON pei.audits USING btree (user_id, user_type)`

### `pei.cache`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 14.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `key` | `character varying / varchar(191)` | `NO` | `` | PK |
| 2 | `value` | `text / text` | `NO` | `` |  |
| 3 | `expiration` | `integer / int4(32,0)` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `cache_pkey` coluna `key`

#### Indices

- `cache_pkey`: `CREATE UNIQUE INDEX cache_pkey ON pei.cache USING btree (key)`

### `pei.cache_locks`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `key` | `character varying / varchar(191)` | `NO` | `` | PK |
| 2 | `owner` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `expiration` | `integer / int4(32,0)` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `cache_locks_pkey` coluna `key`

#### Indices

- `cache_locks_pkey`: `CREATE UNIQUE INDEX cache_locks_pkey ON pei.cache_locks USING btree (key)`

### `pei.failed_jobs`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint / int8(64,0)` | `NO` | `nextval('failed_jobs_id_seq'::regclass)` | PK; sequencial |
| 2 | `uuid` | `character varying / varchar(191)` | `NO` | `` | UNIQUE |
| 3 | `connection` | `text / text` | `NO` | `` |  |
| 4 | `queue` | `text / text` | `NO` | `` |  |
| 5 | `payload` | `text / text` | `NO` | `` |  |
| 6 | `exception` | `text / text` | `NO` | `` |  |
| 7 | `failed_at` | `timestamp without time zone / timestamp` | `NO` | `CURRENT_TIMESTAMP` |  |

#### Constraints

- `PRIMARY KEY` `failed_jobs_pkey` coluna `id`
- `UNIQUE` `failed_jobs_uuid_unique` coluna `uuid`

#### Indices

- `failed_jobs_pkey`: `CREATE UNIQUE INDEX failed_jobs_pkey ON pei.failed_jobs USING btree (id)`
- `failed_jobs_uuid_unique`: `CREATE UNIQUE INDEX failed_jobs_uuid_unique ON pei.failed_jobs USING btree (uuid)`

### `pei.job_batches`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `character varying / varchar(191)` | `NO` | `` | PK |
| 2 | `name` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `total_jobs` | `integer / int4(32,0)` | `NO` | `` |  |
| 4 | `pending_jobs` | `integer / int4(32,0)` | `NO` | `` |  |
| 5 | `failed_jobs` | `integer / int4(32,0)` | `NO` | `` |  |
| 6 | `failed_job_ids` | `text / text` | `NO` | `` |  |
| 7 | `options` | `text / text` | `YES` | `` |  |
| 8 | `cancelled_at` | `integer / int4(32,0)` | `YES` | `` |  |
| 9 | `created_at` | `integer / int4(32,0)` | `NO` | `` |  |
| 10 | `finished_at` | `integer / int4(32,0)` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `job_batches_pkey` coluna `id`

#### Indices

- `job_batches_pkey`: `CREATE UNIQUE INDEX job_batches_pkey ON pei.job_batches USING btree (id)`

### `pei.jobs`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint / int8(64,0)` | `NO` | `nextval('jobs_id_seq'::regclass)` | PK; sequencial |
| 2 | `queue` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `payload` | `text / text` | `NO` | `` |  |
| 4 | `attempts` | `smallint / int2(16,0)` | `NO` | `` |  |
| 5 | `reserved_at` | `integer / int4(32,0)` | `YES` | `` |  |
| 6 | `available_at` | `integer / int4(32,0)` | `NO` | `` |  |
| 7 | `created_at` | `integer / int4(32,0)` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `jobs_pkey` coluna `id`

#### Indices

- `jobs_pkey`: `CREATE UNIQUE INDEX jobs_pkey ON pei.jobs USING btree (id)`
- `jobs_queue_index`: `CREATE INDEX jobs_queue_index ON pei.jobs USING btree (queue)`

### `pei.migrations`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 67.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `integer / int4(32,0)` | `NO` | `nextval('migrations_id_seq'::regclass)` | PK; sequencial |
| 2 | `migration` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `batch` | `integer / int4(32,0)` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `migrations_pkey` coluna `id`

#### Indices

- `migrations_pkey`: `CREATE UNIQUE INDEX migrations_pkey ON pei.migrations USING btree (id)`

### `pei.password_reset_tokens`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `email` | `character varying / varchar(191)` | `NO` | `` | PK |
| 2 | `token` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `password_reset_tokens_pkey` coluna `email`

#### Indices

- `password_reset_tokens_pkey`: `CREATE UNIQUE INDEX password_reset_tokens_pkey ON pei.password_reset_tokens USING btree (email)`

### `pei.personal_access_tokens`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `tokenable_type` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `tokenable_id` | `uuid / uuid` | `NO` | `` |  |
| 4 | `name` | `text / text` | `NO` | `` |  |
| 5 | `token` | `character varying / varchar(64)` | `NO` | `` | UNIQUE |
| 6 | `abilities` | `text / text` | `YES` | `` |  |
| 7 | `last_used_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `expires_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `personal_access_tokens_pkey` coluna `id`
- `UNIQUE` `personal_access_tokens_token_unique` coluna `token`

#### Indices

- `personal_access_tokens_expires_at_index`: `CREATE INDEX personal_access_tokens_expires_at_index ON pei.personal_access_tokens USING btree (expires_at)`
- `personal_access_tokens_pkey`: `CREATE UNIQUE INDEX personal_access_tokens_pkey ON pei.personal_access_tokens USING btree (id)`
- `personal_access_tokens_token_unique`: `CREATE UNIQUE INDEX personal_access_tokens_token_unique ON pei.personal_access_tokens USING btree (token)`
- `personal_access_tokens_tokenable_type_tokenable_id_index`: `CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON pei.personal_access_tokens USING btree (tokenable_type, tokenable_id)`

### `pei.sessions`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 2.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `character varying / varchar(191)` | `NO` | `` | PK |
| 2 | `user_id` | `uuid / uuid` | `YES` | `` |  |
| 3 | `ip_address` | `character varying / varchar(45)` | `YES` | `` |  |
| 4 | `user_agent` | `text / text` | `YES` | `` |  |
| 5 | `payload` | `text / text` | `NO` | `` |  |
| 6 | `last_activity` | `integer / int4(32,0)` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `sessions_pkey` coluna `id`

#### Indices

- `sessions_last_activity_index`: `CREATE INDEX sessions_last_activity_index ON pei.sessions USING btree (last_activity)`
- `sessions_pkey`: `CREATE UNIQUE INDEX sessions_pkey ON pei.sessions USING btree (id)`
- `sessions_user_id_index`: `CREATE INDEX sessions_user_id_index ON pei.sessions USING btree (user_id)`

### `pei.strategic_alerts`

- Modulo: Suporte funcional.
- Finalidade: Alertas estrategicos persistentes exibidos ao usuario.
- Linhas no momento da consulta: 1.
- Models relacionados: `App\Models\StrategicAlert` em `app/Models/StrategicAlert.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id |
| 3 | `cod_organizacao` | `uuid / uuid` | `YES` | `` |  |
| 4 | `title` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `message` | `text / text` | `NO` | `` |  |
| 6 | `icon` | `character varying / varchar(191)` | `NO` | `'bi-info-circle'::character varying` |  |
| 7 | `type` | `character varying / varchar(191)` | `NO` | `'info'::character varying` |  |
| 8 | `read_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_alerts_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `strategic_alerts_pkey` coluna `id`

#### Indices

- `strategic_alerts_pkey`: `CREATE UNIQUE INDEX strategic_alerts_pkey ON pei.strategic_alerts USING btree (id)`

### `pei.system_settings`

- Modulo: Suporte funcional.
- Finalidade: Configuracoes sistemicas, incluindo provedores de IA.
- Linhas no momento da consulta: 6.
- Models relacionados: `App\Models\SystemSetting` em `app/Models/SystemSetting.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `bigint / int8(64,0)` | `NO` | `nextval('system_settings_id_seq'::regclass)` | PK; sequencial |
| 2 | `key` | `character varying / varchar(191)` | `NO` | `` | UNIQUE |
| 3 | `value` | `text / text` | `YES` | `` |  |
| 4 | `type` | `character varying / varchar(191)` | `NO` | `'string'::character varying` |  |
| 5 | `is_encrypted` | `boolean / bool` | `NO` | `false` |  |
| 6 | `description` | `character varying / varchar(191)` | `YES` | `` |  |
| 7 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `system_settings_pkey` coluna `id`
- `UNIQUE` `system_settings_key_unique` coluna `key`

#### Indices

- `system_settings_key_unique`: `CREATE UNIQUE INDEX system_settings_key_unique ON pei.system_settings USING btree (key)`
- `system_settings_pkey`: `CREATE UNIQUE INDEX system_settings_pkey ON pei.system_settings USING btree (id)`

### `strategic_planning.tab_analise_ambiental`

- Modulo: Suporte funcional.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 90.
- Models relacionados: `App\Models\StrategicPlanning\AnaliseAmbiental` em `app/Models/StrategicPlanning/AnaliseAmbiental.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_analise` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 3 | `cod_organizacao` | `uuid / uuid` | `YES` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 4 | `dsc_tipo_analise` | `character varying / varchar(10)` | `NO` | `` |  |
| 5 | `dsc_categoria` | `character varying / varchar(20)` | `NO` | `` |  |
| 6 | `dsc_item` | `character varying / varchar(500)` | `NO` | `` |  |
| 7 | `num_impacto` | `integer / int4(32,0)` | `NO` | `3` |  |
| 8 | `txt_observacao` | `text / text` | `YES` | `` |  |
| 9 | `num_ordem` | `integer / int4(32,0)` | `NO` | `0` |  |
| 10 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 11 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 12 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `tab_analise_ambiental_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `tab_analise_ambiental_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_analise_ambiental_pkey` coluna `cod_analise`

#### Indices

- `tab_analise_ambiental_cod_organizacao_index`: `CREATE INDEX tab_analise_ambiental_cod_organizacao_index ON strategic_planning.tab_analise_ambiental USING btree (cod_organizacao)`
- `tab_analise_ambiental_cod_pei_index`: `CREATE INDEX tab_analise_ambiental_cod_pei_index ON strategic_planning.tab_analise_ambiental USING btree (cod_pei)`
- `tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index`: `CREATE INDEX tab_analise_ambiental_dsc_tipo_analise_dsc_categoria_index ON strategic_planning.tab_analise_ambiental USING btree (dsc_tipo_analise, dsc_categoria)`
- `tab_analise_ambiental_pkey`: `CREATE UNIQUE INDEX tab_analise_ambiental_pkey ON strategic_planning.tab_analise_ambiental USING btree (cod_analise)`

### `pei.tab_audit`

- Modulo: Auditoria.
- Finalidade: Tabela de auditoria/logs propria ou legada.
- Linhas no momento da consulta: 1.
- Models relacionados: `App\Models\TabAudit` em `app/Models/TabAudit.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `acao` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `antes` | `text / text` | `YES` | `` |  |
| 4 | `depois` | `text / text` | `YES` | `` |  |
| 5 | `table` | `character varying / varchar(191)` | `NO` | `` |  |
| 6 | `column_name` | `character varying / varchar(191)` | `NO` | `` |  |
| 7 | `data_type` | `character varying / varchar(191)` | `YES` | `` |  |
| 8 | `table_id` | `character varying / varchar(191)` | `NO` | `` |  |
| 9 | `ip` | `character varying / varchar(191)` | `NO` | `` |  |
| 10 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id |
| 11 | `dte_expired_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 12 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 13 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 14 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `tab_audit_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_audit_pkey` coluna `id`

#### Indices

- `tab_audit_acao_index`: `CREATE INDEX tab_audit_acao_index ON pei.tab_audit USING btree (acao)`
- `tab_audit_pkey`: `CREATE UNIQUE INDEX tab_audit_pkey ON pei.tab_audit USING btree (id)`
- `tab_audit_table_table_id_index`: `CREATE INDEX tab_audit_table_table_id_index ON pei.tab_audit USING btree ("table", table_id)`
- `tab_audit_user_id_index`: `CREATE INDEX tab_audit_user_id_index ON pei.tab_audit USING btree (user_id)`

### `pei.tab_relatorios_agendados`

- Modulo: Relatorios.
- Finalidade: Agendamentos de geracao de relatorios.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\Reports\RelatorioAgendado` em `app/Models/Reports/RelatorioAgendado.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_agendamento` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id |
| 3 | `dsc_tipo_relatorio` | `character varying / varchar(191)` | `NO` | `` |  |
| 4 | `dsc_frequencia` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `txt_filtros` | `jsonb / jsonb` | `YES` | `` |  |
| 6 | `dte_proxima_execucao` | `timestamp without time zone / timestamp` | `NO` | `` |  |
| 7 | `bln_ativo` | `boolean / bool` | `NO` | `true` |  |
| 8 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `tab_relatorios_agendados_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_relatorios_agendados_pkey` coluna `cod_agendamento`

#### Indices

- `tab_relatorios_agendados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_agendados_pkey ON pei.tab_relatorios_agendados USING btree (cod_agendamento)`

### `pei.tab_relatorios_gerados`

- Modulo: Relatorios.
- Finalidade: Historico de relatorios gerados.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\Reports\RelatorioGerado` em `app/Models/Reports/RelatorioGerado.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_relatorio_gerado` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `user_id` | `uuid / uuid` | `YES` | `` | FK para pei.users.id |
| 3 | `dsc_tipo_relatorio` | `character varying / varchar(191)` | `NO` | `` |  |
| 4 | `dsc_caminho_arquivo` | `character varying / varchar(191)` | `NO` | `` |  |
| 5 | `dsc_formato` | `character varying / varchar(191)` | `NO` | `` |  |
| 6 | `txt_filtros_aplicados` | `jsonb / jsonb` | `YES` | `` |  |
| 7 | `num_tamanho_bytes` | `integer / int4(32,0)` | `YES` | `` |  |
| 8 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `tab_relatorios_gerados_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_relatorios_gerados_pkey` coluna `cod_relatorio_gerado`

#### Indices

- `tab_relatorios_gerados_pkey`: `CREATE UNIQUE INDEX tab_relatorios_gerados_pkey ON pei.tab_relatorios_gerados USING btree (cod_relatorio_gerado)`

### `pei.tab_status`

- Modulo: Suporte funcional.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 6.
- Models relacionados: `App\Models\TabStatus` em `app/Models/TabStatus.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_status` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_status` | `text / text` | `NO` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_status_pkey` coluna `cod_status`

#### Indices

- `tab_status_pkey`: `CREATE UNIQUE INDEX tab_status_pkey ON pei.tab_status USING btree (cod_status)`

### `pei.users`

- Modulo: Infraestrutura Laravel/autenticacao.
- Finalidade: Usuarios autenticados do sistema, com flags de administrador, ativo, troca de senha e preferencias.
- Linhas no momento da consulta: 5.
- Models relacionados: `App\Models\User` em `app/Models/User.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `id` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `name` | `character varying / varchar(191)` | `NO` | `` |  |
| 3 | `email` | `character varying / varchar(191)` | `NO` | `` | UNIQUE |
| 4 | `ativo` | `smallint / int2(16,0)` | `NO` | `'1'::smallint` |  |
| 5 | `adm` | `smallint / int2(16,0)` | `NO` | `'2'::smallint` |  |
| 6 | `email_verified_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `password` | `character varying / varchar(191)` | `NO` | `` |  |
| 8 | `trocarsenha` | `smallint / int2(16,0)` | `NO` | `'1'::smallint` |  |
| 9 | `two_factor_secret` | `text / text` | `YES` | `` |  |
| 10 | `two_factor_recovery_codes` | `text / text` | `YES` | `` |  |
| 11 | `two_factor_confirmed_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 12 | `remember_token` | `character varying / varchar(100)` | `YES` | `` |  |
| 13 | `current_team_id` | `uuid / uuid` | `YES` | `` |  |
| 14 | `profile_photo_path` | `character varying / varchar(2048)` | `YES` | `` |  |
| 15 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 16 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 17 | `theme_color` | `character varying / varchar(191)` | `NO` | `'primary'::character varying` |  |

#### Constraints

- `PRIMARY KEY` `users_pkey` coluna `id`
- `UNIQUE` `users_email_unique` coluna `email`

#### Indices

- `users_email_unique`: `CREATE UNIQUE INDEX users_email_unique ON pei.users USING btree (email)`
- `users_pkey`: `CREATE UNIQUE INDEX users_pkey ON pei.users USING btree (id)`

### `risk_management.tab_risco`

- Modulo: Gestao de riscos.
- Finalidade: Riscos identificados por PEI/organizacao.
- Linhas no momento da consulta: 30.
- Models relacionados: `App\Models\RiskManagement\Risco` em `app/Models/RiskManagement/Risco.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_risco` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 3 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 4 | `num_codigo_risco` | `integer / int4(32,0)` | `NO` | `` |  |
| 5 | `dsc_titulo` | `character varying / varchar(255)` | `NO` | `` |  |
| 6 | `txt_descricao` | `text / text` | `NO` | `` |  |
| 7 | `dsc_categoria` | `character varying / varchar(50)` | `NO` | `` |  |
| 8 | `dsc_status` | `character varying / varchar(50)` | `NO` | `` |  |
| 9 | `num_probabilidade` | `smallint / int2(16,0)` | `NO` | `` |  |
| 10 | `num_impacto` | `smallint / int2(16,0)` | `NO` | `` |  |
| 11 | `num_nivel_risco` | `smallint / int2(16,0)` | `NO` | `` |  |
| 12 | `txt_causas` | `text / text` | `YES` | `` |  |
| 13 | `txt_consequencias` | `text / text` | `YES` | `` |  |
| 14 | `cod_responsavel_monitoramento` | `uuid / uuid` | `YES` | `` | FK para pei.users.id |
| 15 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 16 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 17 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `CHECK` `chk_impacto` coluna `num_impacto`
- `CHECK` `chk_nivel_risco` coluna `num_nivel_risco`
- `CHECK` `chk_probabilidade` coluna `num_probabilidade`
- `FOREIGN KEY` `risk_management_tab_risco_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `risk_management_tab_risco_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `FOREIGN KEY` `risk_management_tab_risco_cod_responsavel_monitoramento_foreign` coluna `cod_responsavel_monitoramento` -> pei.users.id
- `PRIMARY KEY` `tab_risco_pkey` coluna `cod_risco`

#### Indices

- `risk_management_tab_risco_cod_organizacao_index`: `CREATE INDEX risk_management_tab_risco_cod_organizacao_index ON risk_management.tab_risco USING btree (cod_organizacao)`
- `risk_management_tab_risco_cod_pei_index`: `CREATE INDEX risk_management_tab_risco_cod_pei_index ON risk_management.tab_risco USING btree (cod_pei)`
- `risk_management_tab_risco_cod_pei_num_codigo_risco_index`: `CREATE INDEX risk_management_tab_risco_cod_pei_num_codigo_risco_index ON risk_management.tab_risco USING btree (cod_pei, num_codigo_risco)`
- `risk_management_tab_risco_dsc_categoria_index`: `CREATE INDEX risk_management_tab_risco_dsc_categoria_index ON risk_management.tab_risco USING btree (dsc_categoria)`
- `risk_management_tab_risco_dsc_status_index`: `CREATE INDEX risk_management_tab_risco_dsc_status_index ON risk_management.tab_risco USING btree (dsc_status)`
- `risk_management_tab_risco_num_nivel_risco_index`: `CREATE INDEX risk_management_tab_risco_num_nivel_risco_index ON risk_management.tab_risco USING btree (num_nivel_risco)`
- `tab_risco_pkey`: `CREATE UNIQUE INDEX tab_risco_pkey ON risk_management.tab_risco USING btree (cod_risco)`

### `risk_management.tab_risco_mitigacao`

- Modulo: Gestao de riscos.
- Finalidade: Medidas/planos de mitigacao de riscos.
- Linhas no momento da consulta: 30.
- Models relacionados: `App\Models\RiskManagement\RiscoMitigacao` em `app/Models/RiskManagement/RiscoMitigacao.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_mitigacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_risco` | `uuid / uuid` | `NO` | `` | FK para risk_management.tab_risco.cod_risco |
| 3 | `dsc_tipo_mitigacao` | `character varying / varchar(50)` | `NO` | `` |  |
| 4 | `txt_acao_mitigacao` | `text / text` | `NO` | `` |  |
| 5 | `cod_responsavel` | `uuid / uuid` | `YES` | `` | FK para pei.users.id |
| 6 | `dte_prazo` | `date / date` | `YES` | `` |  |
| 7 | `dsc_status` | `character varying / varchar(50)` | `NO` | `` |  |
| 8 | `txt_observacoes` | `text / text` | `YES` | `` |  |
| 9 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 11 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_responsavel_foreign` coluna `cod_responsavel` -> pei.users.id
- `FOREIGN KEY` `risk_management_tab_risco_mitigacao_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_mitigacao_pkey` coluna `cod_mitigacao`

#### Indices

- `risk_management_tab_risco_mitigacao_cod_responsavel_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_responsavel_index ON risk_management.tab_risco_mitigacao USING btree (cod_responsavel)`
- `risk_management_tab_risco_mitigacao_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_cod_risco_index ON risk_management.tab_risco_mitigacao USING btree (cod_risco)`
- `risk_management_tab_risco_mitigacao_dsc_status_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_status_index ON risk_management.tab_risco_mitigacao USING btree (dsc_status)`
- `risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index`: `CREATE INDEX risk_management_tab_risco_mitigacao_dsc_tipo_mitigacao_index ON risk_management.tab_risco_mitigacao USING btree (dsc_tipo_mitigacao)`
- `tab_risco_mitigacao_pkey`: `CREATE UNIQUE INDEX tab_risco_mitigacao_pkey ON risk_management.tab_risco_mitigacao USING btree (cod_mitigacao)`

### `risk_management.tab_risco_objetivo`

- Modulo: Gestao de riscos.
- Finalidade: Pivot entre riscos e objetivos estrategicos.
- Linhas no momento da consulta: 40.
- Models relacionados: `App\Models\RiskManagement\RiscoObjetivo` em `app/Models/RiskManagement/RiscoObjetivo.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_risco` | `uuid / uuid` | `NO` | `` | PK; FK para risk_management.tab_risco.cod_risco |
| 2 | `cod_objetivo` | `uuid / uuid` | `NO` | `` | PK; FK para strategic_planning.tab_objetivo.cod_objetivo |
| 3 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 4 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_objetivo_foreign` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `risk_management_tab_risco_objetivo_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_risco`
- `PRIMARY KEY` `tab_risco_objetivo_pkey` coluna `cod_objetivo`

#### Indices

- `tab_risco_objetivo_pkey`: `CREATE UNIQUE INDEX tab_risco_objetivo_pkey ON risk_management.tab_risco_objetivo USING btree (cod_risco, cod_objetivo)`

### `risk_management.tab_risco_ocorrencia`

- Modulo: Gestao de riscos.
- Finalidade: Ocorrencias materializadas de riscos.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\RiskManagement\RiscoOcorrencia` em `app/Models/RiskManagement/RiscoOcorrencia.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_ocorrencia` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_risco` | `uuid / uuid` | `NO` | `` | FK para risk_management.tab_risco.cod_risco |
| 3 | `dte_ocorrencia` | `date / date` | `NO` | `` |  |
| 4 | `txt_descricao_ocorrencia` | `text / text` | `NO` | `` |  |
| 5 | `vlr_impacto_financeiro` | `numeric / numeric(15,2)` | `YES` | `` |  |
| 6 | `txt_acoes_tomadas` | `text / text` | `YES` | `` |  |
| 7 | `txt_licoes_aprendidas` | `text / text` | `YES` | `` |  |
| 8 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 10 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `risk_management_tab_risco_ocorrencia_cod_risco_foreign` coluna `cod_risco` -> risk_management.tab_risco.cod_risco
- `PRIMARY KEY` `tab_risco_ocorrencia_pkey` coluna `cod_ocorrencia`

#### Indices

- `risk_management_tab_risco_ocorrencia_cod_risco_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_cod_risco_index ON risk_management.tab_risco_ocorrencia USING btree (cod_risco)`
- `risk_management_tab_risco_ocorrencia_dte_ocorrencia_index`: `CREATE INDEX risk_management_tab_risco_ocorrencia_dte_ocorrencia_index ON risk_management.tab_risco_ocorrencia USING btree (dte_ocorrencia)`
- `tab_risco_ocorrencia_pkey`: `CREATE UNIQUE INDEX tab_risco_ocorrencia_pkey ON risk_management.tab_risco_ocorrencia USING btree (cod_ocorrencia)`

### `strategic_planning.tab_arquivos`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\StrategicPlanning\Arquivo` em `app/Models/StrategicPlanning/Arquivo.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_arquivo` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_evolucao_indicador` | `uuid / uuid` | `NO` | `` | FK para performance_indicators.tab_evolucao_indicador.cod_evolucao_indicador |
| 3 | `txt_assunto` | `text / text` | `NO` | `` |  |
| 4 | `data` | `text / text` | `NO` | `` |  |
| 5 | `dsc_nome_arquivo` | `text / text` | `NO` | `` |  |
| 6 | `dsc_tipo` | `character varying / varchar(191)` | `NO` | `` |  |
| 7 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_arquivos_cod_evolucao_indicador_foreign` coluna `cod_evolucao_indicador` -> performance_indicators.tab_evolucao_indicador.cod_evolucao_indicador
- `PRIMARY KEY` `tab_arquivos_pkey` coluna `cod_arquivo`

#### Indices

- `strategic_planning_tab_arquivos_cod_evolucao_indicador_index`: `CREATE INDEX strategic_planning_tab_arquivos_cod_evolucao_indicador_index ON strategic_planning.tab_arquivos USING btree (cod_evolucao_indicador)`
- `tab_arquivos_pkey`: `CREATE UNIQUE INDEX tab_arquivos_pkey ON strategic_planning.tab_arquivos USING btree (cod_arquivo)`

### `strategic_planning.tab_atividade_cadeia_valor`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\StrategicPlanning\AtividadeCadeiaValor` em `app/Models/StrategicPlanning/AtividadeCadeiaValor.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 3 | `cod_perspectiva` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_perspectiva.cod_perspectiva |
| 4 | `dsc_atividade` | `text / text` | `NO` | `` |  |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `FOREIGN KEY` `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_f` coluna `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_atividade_cadeia_valor_pkey` coluna `cod_atividade_cadeia_valor`

#### Indices

- `strategic_planning_tab_atividade_cadeia_valor_cod_pei_index`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_pei_index ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_pei)`
- `strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i`: `CREATE INDEX strategic_planning_tab_atividade_cadeia_valor_cod_perspectiva_i ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_perspectiva)`
- `tab_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_atividade_cadeia_valor_pkey ON strategic_planning.tab_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`

### `strategic_planning.tab_futuro_almejado_objetivo`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Futuro almejado associado a objetivos estrategicos.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\StrategicPlanning\FuturoAlmejado` em `app/Models/StrategicPlanning/FuturoAlmejado.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_futuro_almejado` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_futuro_almejado` | `text / text` | `NO` | `` |  |
| 3 | `cod_objetivo` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_objetivo.cod_objetivo |
| 4 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 5 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `PRIMARY KEY` `tab_futuro_almejado_objetivo_estrategico_pkey` coluna `cod_futuro_almejado`

#### Indices

- `strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod`: `CREATE INDEX strategic_planning_tab_futuro_almejado_objetivo_estrategico_cod ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_objetivo)`
- `tab_futuro_almejado_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_futuro_almejado_objetivo_estrategico_pkey ON strategic_planning.tab_futuro_almejado_objetivo USING btree (cod_futuro_almejado)`

### `strategic_planning.tab_grau_satisfacao`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Faixas percentuais e cores para classificacao de desempenho.
- Linhas no momento da consulta: 4.
- Models relacionados: `App\Models\StrategicPlanning\GrauSatisfacao` em `app/Models/StrategicPlanning/GrauSatisfacao.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_grau_satisfacao` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_grau_satisfacao` | `text / text` | `NO` | `` |  |
| 3 | `cor` | `character varying / varchar(191)` | `NO` | `` |  |
| 4 | `vlr_minimo` | `numeric / numeric(15,2)` | `NO` | `` |  |
| 5 | `vlr_maximo` | `numeric / numeric(15,2)` | `NO` | `` |  |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 9 | `cod_pei` | `uuid / uuid` | `YES` | `` | FK para strategic_planning.tab_pei.cod_pei. Nullable no banco por design (escala global). OBRIGATORIO na camada de aplicacao: `ListarGrausSatisfacao` valida `required\|exists` desde 2026-06-25. |
| 10 | `num_ano` | `integer / int4(32,0)` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_grau_satisfacao_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_grau_satisfcao_pkey` coluna `cod_grau_satisfacao`

#### Indices

- `idx_grau_satisfacao_pei_ano`: `CREATE INDEX idx_grau_satisfacao_pei_ano ON strategic_planning.tab_grau_satisfacao USING btree (cod_pei, num_ano)`
- `tab_grau_satisfcao_pkey`: `CREATE UNIQUE INDEX tab_grau_satisfcao_pkey ON strategic_planning.tab_grau_satisfacao USING btree (cod_grau_satisfacao)`

### `strategic_planning.tab_missao_visao_valores`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Identidade estrategica por PEI/organizacao: missao e visao.
- Linhas no momento da consulta: 1.
- Models relacionados: `App\Models\StrategicPlanning\MissaoVisaoValores` em `app/Models/StrategicPlanning/MissaoVisaoValores.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_missao_visao_valores` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_missao` | `text / text` | `NO` | `` |  |
| 3 | `dsc_visao` | `text / text` | `NO` | `` |  |
| 4 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 5 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_organizacao_for` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `strategic_planning_tab_missao_visao_valores_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_missao_visao_valores_pkey` coluna `cod_missao_visao_valores`

#### Indices

- `tab_missao_visao_valores_pkey`: `CREATE UNIQUE INDEX tab_missao_visao_valores_pkey ON strategic_planning.tab_missao_visao_valores USING btree (cod_missao_visao_valores)`

### `strategic_planning.tab_nivel_hierarquico`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 100.
- Models relacionados: nao identificado por declaracao explicita de `$table`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `num_nivel_hierarquico_apresentacao` | `smallint / int2(16,0)` | `NO` | `` | PK |
| 2 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 3 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 4 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_nivel_hierarquico_pkey` coluna `num_nivel_hierarquico_apresentacao`

#### Indices

- `tab_nivel_hierarquico_pkey`: `CREATE UNIQUE INDEX tab_nivel_hierarquico_pkey ON strategic_planning.tab_nivel_hierarquico USING btree (num_nivel_hierarquico_apresentacao)`

### `strategic_planning.tab_objetivo`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Objetivos estrategicos vinculados a perspectivas.
- Linhas no momento da consulta: 17.
- Models relacionados: `App\Models\StrategicPlanning\Objetivo` em `app/Models/StrategicPlanning/Objetivo.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_objetivo` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `nom_objetivo` | `text / text` | `NO` | `` |  |
| 3 | `dsc_objetivo` | `text / text` | `NO` | `` |  |
| 4 | `num_nivel_hierarquico_apresentacao` | `smallint / int2(16,0)` | `NO` | `` |  |
| 5 | `cod_perspectiva` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_perspectiva.cod_perspectiva |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_perspectiva_for` coluna `cod_perspectiva` -> strategic_planning.tab_perspectiva.cod_perspectiva
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey` coluna `cod_objetivo`

#### Indices

- `tab_objetivo_estrategico_pkey`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey ON strategic_planning.tab_objetivo USING btree (cod_objetivo)`

### `strategic_planning.tab_objetivo_comentarios`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\StrategicPlanning\ObjetivoComentario` em `app/Models/StrategicPlanning/ObjetivoComentario.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_comentario` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `cod_objetivo` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_objetivo.cod_objetivo |
| 3 | `user_id` | `uuid / uuid` | `NO` | `` | FK para pei.users.id |
| 4 | `dsc_comentario` | `text / text` | `NO` | `` |  |
| 5 | `cod_comentario_pai` | `uuid / uuid` | `YES` | `` |  |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_cod_objetivo_foreig` coluna `cod_objetivo` -> strategic_planning.tab_objetivo.cod_objetivo
- `FOREIGN KEY` `strategic_planning_tab_objetivo_comentarios_user_id_foreign` coluna `user_id` -> pei.users.id
- `PRIMARY KEY` `tab_objetivo_comentarios_pkey` coluna `cod_comentario`

#### Indices

- `tab_objetivo_comentarios_pkey`: `CREATE UNIQUE INDEX tab_objetivo_comentarios_pkey ON strategic_planning.tab_objetivo_comentarios USING btree (cod_comentario)`

### `strategic_planning.tab_pei`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Ciclos de Planejamento Estrategico Institucional.
- Linhas no momento da consulta: 1.
- Models relacionados: `App\Models\StrategicPlanning\PEI` em `app/Models/StrategicPlanning/PEI.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_pei` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_pei` | `text / text` | `NO` | `` |  |
| 3 | `num_ano_inicio_pei` | `smallint / int2(16,0)` | `NO` | `` |  |
| 4 | `num_ano_fim_pei` | `smallint / int2(16,0)` | `NO` | `` |  |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `PRIMARY KEY` `tab_pei_pkey` coluna `cod_pei`

#### Indices

- `tab_pei_pkey`: `CREATE UNIQUE INDEX tab_pei_pkey ON strategic_planning.tab_pei USING btree (cod_pei)`

### `strategic_planning.tab_perspectiva`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Perspectivas BSC do PEI, com pesos de indicadores e planos.
- Linhas no momento da consulta: 4.
- Models relacionados: `App\Models\StrategicPlanning\Perspectiva` em `app/Models/StrategicPlanning/Perspectiva.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_perspectiva` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `dsc_perspectiva` | `text / text` | `NO` | `` |  |
| 3 | `num_nivel_hierarquico_apresentacao` | `smallint / int2(16,0)` | `NO` | `` |  |
| 4 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `num_peso_indicadores` | `integer / int4(32,0)` | `NO` | `100` |  |
| 9 | `num_peso_planos` | `integer / int4(32,0)` | `NO` | `0` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_perspectiva_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_perspectiva_pkey` coluna `cod_perspectiva`

#### Indices

- `tab_perspectiva_pkey`: `CREATE UNIQUE INDEX tab_perspectiva_pkey ON strategic_planning.tab_perspectiva USING btree (cod_perspectiva)`

### `strategic_planning.tab_processos_atividade_cadeia_valor`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Finalidade inferida pelo nome, schema, model e relacionamentos; validar com regra de negocio antes de alteracoes estruturais.
- Linhas no momento da consulta: 0.
- Models relacionados: `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor` em `app/Models/StrategicPlanning/ProcessoAtividadeCadeiaValor.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_processo_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `cod_atividade_cadeia_valor` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor |
| 3 | `dsc_entrada` | `text / text` | `NO` | `` |  |
| 4 | `dsc_transformacao` | `text / text` | `NO` | `` |  |
| 5 | `dsc_saida` | `text / text` | `NO` | `` |  |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati` coluna `cod_atividade_cadeia_valor` -> strategic_planning.tab_atividade_cadeia_valor.cod_atividade_cadeia_valor
- `PRIMARY KEY` `tab_processos_atividade_cadeia_valor_pkey` coluna `cod_processo_atividade_cadeia_valor`

#### Indices

- `strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati`: `CREATE INDEX strategic_planning_tab_processos_atividade_cadeia_valor_cod_ati ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_atividade_cadeia_valor)`
- `tab_processos_atividade_cadeia_valor_pkey`: `CREATE UNIQUE INDEX tab_processos_atividade_cadeia_valor_pkey ON strategic_planning.tab_processos_atividade_cadeia_valor USING btree (cod_processo_atividade_cadeia_valor)`

### `strategic_planning.tab_tema_norteador`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Temas norteadores por PEI/organizacao.
- Linhas no momento da consulta: 4.
- Models relacionados: `App\Models\StrategicPlanning\TemaNorteador` em `app/Models/StrategicPlanning/TemaNorteador.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_tema_norteador` | `uuid / uuid` | `NO` | `` | PK |
| 2 | `nom_tema_norteador` | `text / text` | `NO` | `` |  |
| 3 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 4 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 5 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 6 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_organizacao_for` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `strategic_planning_tab_objetivo_estrategico_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_objetivo_estrategico_pkey1` coluna `cod_tema_norteador`

#### Indices

- `tab_objetivo_estrategico_pkey1`: `CREATE UNIQUE INDEX tab_objetivo_estrategico_pkey1 ON strategic_planning.tab_tema_norteador USING btree (cod_tema_norteador)`

### `strategic_planning.tab_valores`

- Modulo: Planejamento estrategico PEI/BSC.
- Finalidade: Valores institucionais por PEI/organizacao.
- Linhas no momento da consulta: 5.
- Models relacionados: `App\Models\StrategicPlanning\Valor` em `app/Models/StrategicPlanning/Valor.php`.

#### Colunas

| Ordem | Coluna | Tipo fisico | Nulo | Default | Observacao |
|---:|---|---|---|---|---|
| 1 | `cod_valor` | `uuid / uuid` | `NO` | `gen_random_uuid()` | PK; UUID/default |
| 2 | `nom_valor` | `text / text` | `NO` | `` |  |
| 3 | `dsc_valor` | `text / text` | `NO` | `` |  |
| 4 | `cod_pei` | `uuid / uuid` | `NO` | `` | FK para strategic_planning.tab_pei.cod_pei |
| 5 | `cod_organizacao` | `uuid / uuid` | `NO` | `` | FK para organization.tab_organizacoes.cod_organizacao |
| 6 | `created_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 7 | `updated_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |
| 8 | `deleted_at` | `timestamp without time zone / timestamp` | `YES` | `` |  |

#### Constraints

- `FOREIGN KEY` `strategic_planning_tab_valores_cod_organizacao_foreign` coluna `cod_organizacao` -> organization.tab_organizacoes.cod_organizacao
- `FOREIGN KEY` `strategic_planning_tab_valores_cod_pei_foreign` coluna `cod_pei` -> strategic_planning.tab_pei.cod_pei
- `PRIMARY KEY` `tab_valores_pkey` coluna `cod_valor`

#### Indices

- `tab_valores_pkey`: `CREATE UNIQUE INDEX tab_valores_pkey ON strategic_planning.tab_valores USING btree (cod_valor)`

## Cruzamento Models x Banco

| Model | Tabela declarada | PK declarada | Existe no banco/search_path | Observacao |
|---|---|---|---|---|
| `App\Models\ActionPlan\Acao` | `acoes` | `id` | sim, via tabela action_plan.acoes |  |
| `App\Models\ActionPlan\Entrega` | `tab_entregas` | `cod_entrega` | sim, via tabela action_plan.tab_entregas |  |
| `App\Models\ActionPlan\EntregaAnexo` | `tab_entrega_anexos` | `cod_anexo` | sim, via tabela action_plan.tab_entrega_anexos |  |
| `App\Models\ActionPlan\EntregaComentario` | `tab_entrega_comentarios` | `cod_comentario` | sim, via tabela action_plan.tab_entrega_comentarios |  |
| `App\Models\ActionPlan\EntregaHistorico` | `tab_entrega_historico` | `cod_historico` | sim, via tabela action_plan.tab_entrega_historico |  |
| `App\Models\ActionPlan\EntregaLabel` | `tab_entrega_labels` | `cod_label` | sim, via tabela action_plan.tab_entrega_labels |  |
| `App\Models\ActionPlan\PlanoDeAcao` | `tab_plano_de_acao` | `cod_plano_de_acao` | sim, via tabela action_plan.tab_plano_de_acao | Auditable |
| `App\Models\ActionPlan\TipoExecucao` | `tab_tipo_execucao` | `cod_tipo_execucao` | sim, via tabela action_plan.tab_tipo_execucao |  |
| `App\Models\Organization` | `tab_organizacoes` | `cod_organizacao` | sim, via tabela organization.tab_organizacoes |  |
| `App\Models\PerfilAcesso` | `tab_perfil_acesso` | `cod_perfil` | sim, via tabela organization.tab_perfil_acesso |  |
| `App\Models\PerformanceIndicators\EvolucaoIndicador` | `tab_evolucao_indicador` | `cod_evolucao_indicador` | sim, via tabela performance_indicators.tab_evolucao_indicador |  |
| `App\Models\PerformanceIndicators\Indicador` | `tab_indicador` | `cod_indicador` | sim, via tabela performance_indicators.tab_indicador | Auditable |
| `App\Models\PerformanceIndicators\LinhaBaseIndicador` | `tab_linha_base_indicador` | `cod_linha_base` | sim, via tabela performance_indicators.tab_linha_base_indicador |  |
| `App\Models\PerformanceIndicators\MetaPorAno` | `tab_meta_por_ano` | `cod_meta_por_ano` | sim, via tabela performance_indicators.tab_meta_por_ano |  |
| `App\Models\Reports\RelatorioAgendado` | `tab_relatorios_agendados` | `cod_agendamento` | sim, via tabela pei.tab_relatorios_agendados |  |
| `App\Models\Reports\RelatorioGerado` | `tab_relatorios_gerados` | `cod_relatorio_gerado` | sim, via tabela pei.tab_relatorios_gerados |  |
| `App\Models\RiskManagement\Risco` | `tab_risco` | `cod_risco` | sim, via tabela risk_management.tab_risco | Auditable |
| `App\Models\RiskManagement\RiscoMitigacao` | `tab_risco_mitigacao` | `cod_mitigacao` | sim, via tabela risk_management.tab_risco_mitigacao | Auditable |
| `App\Models\RiskManagement\RiscoObjetivo` | `tab_risco_objetivo` | `cod_risco_objetivo` | sim, via tabela risk_management.tab_risco_objetivo |  |
| `App\Models\RiskManagement\RiscoOcorrencia` | `tab_risco_ocorrencia` | `cod_ocorrencia` | sim, via tabela risk_management.tab_risco_ocorrencia | Auditable |
| `App\Models\StrategicAlert` | `strategic_alerts` | `convencao` | sim, via tabela pei.strategic_alerts |  |
| `App\Models\StrategicPlanning\AnaliseAmbiental` | `tab_analise_ambiental` | `cod_analise` | sim, via tabela strategic_planning.tab_analise_ambiental |  |
| `App\Models\StrategicPlanning\Arquivo` | `tab_arquivos` | `cod_arquivo` | sim, via tabela strategic_planning.tab_arquivos |  |
| `App\Models\StrategicPlanning\AtividadeCadeiaValor` | `tab_atividade_cadeia_valor` | `cod_atividade_cadeia_valor` | sim, via tabela strategic_planning.tab_atividade_cadeia_valor |  |
| `App\Models\StrategicPlanning\FuturoAlmejado` | `tab_futuro_almejado_objetivo` | `cod_futuro_almejado` | sim, via tabela strategic_planning.tab_futuro_almejado_objetivo |  |
| `App\Models\StrategicPlanning\GrauSatisfacao` | `tab_grau_satisfacao` | `cod_grau_satisfacao` | sim, via tabela strategic_planning.tab_grau_satisfacao |  |
| `App\Models\StrategicPlanning\MissaoVisaoValores` | `tab_missao_visao_valores` | `cod_missao_visao_valores` | sim, via tabela strategic_planning.tab_missao_visao_valores | Auditable |
| `App\Models\StrategicPlanning\Objetivo` | `tab_objetivo` | `cod_objetivo` | sim, via tabela strategic_planning.tab_objetivo | Auditable |
| `App\Models\StrategicPlanning\ObjetivoComentario` | `tab_objetivo_comentarios` | `cod_comentario` | sim, via tabela strategic_planning.tab_objetivo_comentarios |  |
| `App\Models\StrategicPlanning\PEI` | `tab_pei` | `cod_pei` | sim, via tabela strategic_planning.tab_pei |  |
| `App\Models\StrategicPlanning\Perspectiva` | `tab_perspectiva` | `cod_perspectiva` | sim, via tabela strategic_planning.tab_perspectiva |  |
| `App\Models\StrategicPlanning\ProcessoAtividadeCadeiaValor` | `tab_processos_atividade_cadeia_valor` | `cod_processo_atividade_cadeia_valor` | sim, via tabela strategic_planning.tab_processos_atividade_cadeia_valor |  |
| `App\Models\StrategicPlanning\TemaNorteador` | `strategic_planning.tab_tema_norteador` | `cod_tema_norteador` | sim, nome qualificado | Auditable |
| `App\Models\StrategicPlanning\Valor` | `tab_valores` | `cod_valor` | sim, via tabela strategic_planning.tab_valores | Auditable |
| `App\Models\SystemSetting` | `system_settings` | `convencao` | sim, via tabela pei.system_settings |  |
| `App\Models\TabAudit` | `tab_audit` | `id` | sim, via tabela pei.tab_audit |  |
| `App\Models\TabStatus` | `tab_status` | `cod_status` | sim, via tabela pei.tab_status |  |
| `App\Models\User` | `users` | `id` | sim, via tabela pei.users |  |

## Observacoes de upgrade e governanca de dados

- O banco usa schemas funcionais; alterar `search_path` ou qualificar tabelas nos models exige testes completos de persistencia.
- Existem tabelas pivot importantes para seguranca e escopo: usuarios-organizacoes, usuarios-perfis, planos-organizacoes, entregas-responsaveis, entregas-labels, riscos-objetivos e indicadores-organizacoes/objetivos.
- Campos legados aparecem em pontos de compatibilidade, por exemplo `cod_organizacao` em planos e `cod_responsavel` em entregas, mesmo havendo pivots modernos.
- Tabelas de auditoria e historico devem ser preservadas em qualquer upgrade; elas sustentam rastreabilidade.
- Antes de qualquer DDL, gerar dump estrutural e validar PK/FK/indices reais contra este dicionario.
