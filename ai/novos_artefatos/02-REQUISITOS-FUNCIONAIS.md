# REQUISITOS FUNCIONAIS
## Sistema de Planejamento Estratégico

**Versão:** 1.0
**Data:** 23/12/2025

---

## ÍNDICE

1. [RF-01: Autenticação e Controle de Acesso](#rf-01-autenticação-e-controle-de-acesso)
2. [RF-02: Gestão de Organizações](#rf-02-gestão-de-organizações)
3. [RF-03: Gestão de Usuários](#rf-03-gestão-de-usuários)
4. [RF-04: Gestão de Ciclos de Planejamento (PEI)](#rf-04-gestão-de-ciclos-de-planejamento-pei)
5. [RF-05: Identidade Estratégica](#rf-05-identidade-estratégica)
6. [RF-06: Perspectivas BSC](#rf-06-perspectivas-bsc)
7. [RF-07: Objetivos Estratégicos](#rf-07-objetivos-estratégicos)
8. [RF-08: Planos de Ação](#rf-08-planos-de-ação)
9. [RF-09: Indicadores (KPIs)](#rf-09-indicadores-kpis)
10. [RF-10: Cadeia de Valor](#rf-10-cadeia-de-valor)
11. [RF-11: Dashboards](#rf-11-dashboards)
12. [RF-12: Relatórios](#rf-12-relatórios)
13. [RF-13: Auditoria](#rf-13-auditoria)

---

## RF-01: Autenticação e Controle de Acesso

### RF-01.1: Login de Usuário

**Descrição:** Permitir que usuários autentiquem no sistema.

**Critérios de Aceitação:**
- Campo de email (validação de formato)
- Campo de senha (mascarado)
- Botão "Entrar"
- Link "Esqueci minha senha"
- Checkbox "Lembrar-me" (sessão de 30 dias)
- Mensagem de erro clara se credenciais inválidas
- Redirecionamento para dashboard após login bem-sucedido
- Bloqueio após 5 tentativas falhas (15 minutos)

**Tabela:** `users`

**Regras de Negócio:**
- RN-001: Usuário com campo `ativo = 0` não pode fazer login
- RN-002: Sessão expira após 12 horas de inatividade
- RN-003: Se `trocarsenha = 1`, redirecionar para tela de mudança obrigatória

### RF-01.2: Redefinir Senha

**Descrição:** Permitir reset de senha via email.

**Critérios de Aceitação:**
- Tela "Esqueci minha senha" com campo de email
- Envio de email com link único (token)
- Token válido por 2 horas
- Tela de nova senha com confirmação
- Validação de força de senha (mínimo 8 caracteres, 1 maiúscula, 1 número, 1 especial)
- Invalidar token após uso

**Tabela:** `password_resets`

### RF-01.3: Trocar Senha (Primeiro Acesso)

**Descrição:** Forçar troca de senha no primeiro acesso ou após reset por admin.

**Critérios de Aceitação:**
- Tela obrigatória (não pode pular)
- Campo senha atual
- Campo nova senha
- Campo confirmação
- Validação de força de senha
- Impedir uso de senhas anteriores (últimas 5)
- Após troca, setar `trocarsenha = 0`

**Tabela:** `users` (campo `trocarsenha`)

### RF-01.4: Logout

**Descrição:** Encerrar sessão do usuário.

**Critérios de Aceitação:**
- Botão "Sair" no menu superior
- Invalidar sessão
- Redirecionar para tela de login
- Limpar cookies se "Lembrar-me" estava ativo

**Tabela:** `sessions`

### RF-01.5: Verificação de Permissões

**Descrição:** Sistema deve verificar permissões em cada ação.

**Critérios de Aceitação:**
- Middleware de autorização em todas as rotas protegidas
- Verificar perfil do usuário (`tab_perfil_acesso`)
- Verificar organização permitida (`rel_users_tab_organizacoes`)
- Verificar permissão específica em plano de ação (`rel_users_tab_organizacoes_tab_perfil_acesso`)
- Exibir apenas menus e botões permitidos ao perfil

**Tabelas:** `tab_perfil_acesso`, `rel_users_tab_organizacoes`, `rel_users_tab_organizacoes_tab_perfil_acesso`

**Perfis:**
- **Super Administrador** (`c00b9ebc-7014-4d37-97dc-7875e55fff2a`): Acesso total
- **Administrador da Unidade** (`c00b9ebc-7014-4d37-97dc-7875e55fff3b`): Acesso à sua unidade e subordinadas
- **Gestor Responsável** (`c00b9ebc-7014-4d37-97dc-7875e55fff4c`): Acesso aos planos que gerencia
- **Gestor Substituto** (`c00b9ebc-7014-4d37-97dc-7875e55fff5d`): Acesso aos planos que substitui

---

## RF-02: Gestão de Organizações

### RF-02.1: Listar Organizações

**Descrição:** Exibir lista de organizações em estrutura hierárquica.

**Critérios de Aceitação:**
- Visualização em árvore (TreeView)
- Mostrar sigla e nome
- Indicar nível hierárquico (ícone ou indentação)
- Permitir expandir/colapsar níveis
- Busca por nome ou sigla
- Ordenação alfabética
- Super Admin vê todas, outros usuários veem apenas suas organizações

**Tabela:** `tab_organizacoes`

**Campos exibidos:**
- `sgl_organizacao` (sigla)
- `nom_organizacao` (nome)
- Nível hierárquico (calculado via `rel_cod_organizacao`)

### RF-02.2: Criar Organização

**Descrição:** Permitir criação de nova organização.

**Critérios de Aceitação:**
- Formulário com campos: Sigla, Nome, Organização Pai
- Sigla: máximo 20 caracteres, obrigatório, único
- Nome: máximo 255 caracteres, obrigatório
- Organização Pai: dropdown de organizações existentes, obrigatório
- Validação de campos
- Ao salvar, gerar UUID automático
- Registrar em auditoria

**Tabela:** `tab_organizacoes`

**Permissão:** Apenas Super Administrador

### RF-02.3: Editar Organização

**Descrição:** Permitir edição de organização existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Sigla, Nome, Organização Pai
- Validação de campos
- Impedir que organização seja pai de si mesma (loop)
- Impedir que organização seja filha de uma de suas descendentes (loop)
- Registrar em auditoria

**Tabela:** `tab_organizacoes`

**Permissão:** Apenas Super Administrador

### RF-02.4: Excluir Organização (Soft Delete)

**Descrição:** Permitir exclusão lógica de organização.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui organizações filhas (não permitir se houver)
- Verificar se possui planos de ação vinculados (alertar, mas permitir)
- Setar `deleted_at` (soft delete)
- Registrar em auditoria

**Tabela:** `tab_organizacoes` (campo `deleted_at`)

**Permissão:** Apenas Super Administrador

### RF-02.5: Restaurar Organização

**Descrição:** Permitir restauração de organização excluída.

**Critérios de Aceitação:**
- Listar organizações excluídas (filtro)
- Botão "Restaurar"
- Limpar `deleted_at`
- Registrar em auditoria

**Tabela:** `tab_organizacoes`

**Permissão:** Apenas Super Administrador

---

## RF-03: Gestão de Usuários

### RF-03.1: Listar Usuários

**Descrição:** Exibir lista de usuários do sistema.

**Critérios de Aceitação:**
- Tabela com: Nome, Email, Organizações, Perfis, Status (Ativo/Inativo)
- Filtro por status
- Filtro por organização
- Filtro por perfil
- Busca por nome ou email
- Paginação (20 por página)
- Super Admin vê todos, Admin Unidade vê apenas da sua unidade

**Tabela:** `users`

**Campos exibidos:**
- `name`
- `email`
- `ativo` (badge verde/vermelho)
- Organizações (badges)
- Perfis (badges)

### RF-03.2: Criar Usuário

**Descrição:** Permitir criação de novo usuário.

**Critérios de Aceitação:**
- Formulário com: Nome, Email, Organizações, Perfis
- Nome: obrigatório, máximo 255 caracteres
- Email: obrigatório, formato válido, único
- Senha padrão gerada automaticamente (`trocarsenha = 1`)
- Checkbox "Ativo" (padrão: marcado)
- Checkbox "Super Administrador" (apenas Super Admin pode marcar)
- Multi-select de organizações (ao menos 1)
- Multi-select de perfis por organização
- Ao salvar, enviar email de boas-vindas com senha temporária
- Registrar em auditoria

**Tabelas:** `users`, `rel_users_tab_organizacoes`, `rel_users_tab_organizacoes_tab_perfil_acesso`

**Permissão:** Super Admin (global), Admin Unidade (apenas sua unidade)

### RF-03.3: Editar Usuário

**Descrição:** Permitir edição de usuário existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Nome, Email, Organizações, Perfis, Status
- Não permitir alterar própria flag de Super Admin
- Validação de campos
- Registrar em auditoria

**Tabelas:** `users`, `rel_users_tab_organizacoes`, `rel_users_tab_organizacoes_tab_perfil_acesso`

**Permissão:** Super Admin (global), Admin Unidade (apenas sua unidade)

### RF-03.4: Resetar Senha de Usuário

**Descrição:** Admin pode resetar senha de outro usuário.

**Critérios de Aceitação:**
- Botão "Resetar Senha" na lista de usuários
- Confirmação antes de resetar
- Gerar nova senha temporária
- Setar `trocarsenha = 1`
- Enviar email com nova senha
- Registrar em auditoria

**Tabela:** `users`

**Permissão:** Super Admin (todos), Admin Unidade (sua unidade)

### RF-03.5: Inativar/Ativar Usuário

**Descrição:** Permitir ativar ou inativar usuário.

**Critérios de Aceitação:**
- Toggle "Ativo/Inativo"
- Confirmação antes de inativar
- Setar `ativo = 0` ou `ativo = 1`
- Usuário inativo não consegue fazer login
- Sessões ativas do usuário são invalidadas ao inativar
- Registrar em auditoria

**Tabela:** `users` (campo `ativo`)

**Permissão:** Super Admin (todos), Admin Unidade (sua unidade)

---

## RF-04: Gestão de Ciclos de Planejamento (PEI)

### RF-04.1: Listar Ciclos de PEI

**Descrição:** Exibir lista de ciclos de planejamento estratégico.

**Critérios de Aceitação:**
- Tabela com: Descrição, Ano Início, Ano Fim, Status
- Ordenação por ano início (decrescente)
- Indicar ciclo ativo (badge)
- Busca por descrição ou ano
- Filtro por status (Ativo/Encerrado)

**Tabela:** `pei.tab_pei`

**Campos exibidos:**
- `dsc_pei`
- `num_ano_inicio_pei`
- `num_ano_fim_pei`
- Status calculado (Ativo se ano atual está no range)

### RF-04.2: Criar Ciclo de PEI

**Descrição:** Permitir criação de novo ciclo de planejamento.

**Critérios de Aceitação:**
- Formulário com: Descrição, Ano Início, Ano Fim
- Descrição: obrigatório, máximo 500 caracteres
- Ano Início: obrigatório, numérico, 4 dígitos
- Ano Fim: obrigatório, numérico, 4 dígitos, maior que Ano Início
- Validação: não permitir overlap com outros ciclos ativos
- Ao salvar, gerar UUID automático
- Criar automaticamente 4 perspectivas BSC padrão
- Registrar em auditoria

**Tabela:** `pei.tab_pei`

**Permissão:** Apenas Super Administrador

### RF-04.3: Editar Ciclo de PEI

**Descrição:** Permitir edição de ciclo existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Descrição, Ano Início, Ano Fim
- Validação de campos
- Alertar se alterar datas de ciclo com dados já cadastrados
- Registrar em auditoria

**Tabela:** `pei.tab_pei`

**Permissão:** Apenas Super Administrador

### RF-04.4: Excluir Ciclo de PEI (Soft Delete)

**Descrição:** Permitir exclusão lógica de ciclo.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui dados vinculados (alertar gravidade)
- Soft delete em cascata: perspectivas, objetivos, planos, indicadores
- Setar `deleted_at`
- Registrar em auditoria

**Tabela:** `pei.tab_pei`

**Permissão:** Apenas Super Administrador

---

## RF-05: Identidade Estratégica

### RF-05.1: Visualizar Identidade Estratégica

**Descrição:** Exibir Missão, Visão e Valores da organização.

**Critérios de Aceitação:**
- Cards separados para: Missão, Visão, Valores
- Mostrar texto completo
- Mostrar data de criação/atualização
- Mostrar quem criou/atualizou
- Permitir expandir/recolher textos longos
- Filtro por organização (dropdown)
- Filtro por ciclo PEI

**Tabelas:** `pei.tab_missao_visao_valores`, `pei.tab_valores`

**Campos exibidos:**
- Missão: `dsc_missao`
- Visão: `dsc_visao`
- Valores: lista de `nom_valor` e `dsc_valor`

### RF-05.2: Criar/Editar Missão e Visão

**Descrição:** Permitir edição da Missão e Visão.

**Critérios de Aceitação:**
- Formulário com: Missão (textarea), Visão (textarea)
- Missão: obrigatório, máximo 2000 caracteres
- Visão: obrigatório, máximo 2000 caracteres
- Seleção de organização (dropdown)
- Seleção de ciclo PEI (dropdown)
- Preview antes de salvar
- Se já existe registro, atualizar (não criar novo)
- Versionamento via auditoria
- Registrar em auditoria

**Tabela:** `pei.tab_missao_visao_valores`

**Permissão:** Super Admin (todas), Admin Unidade (sua unidade)

### RF-05.3: Criar Valor

**Descrição:** Adicionar novo valor organizacional.

**Critérios de Aceitação:**
- Formulário com: Nome do Valor, Descrição
- Nome: obrigatório, máximo 100 caracteres
- Descrição: obrigatório, máximo 1000 caracteres
- Seleção de organização
- Seleção de ciclo PEI
- Ao salvar, gerar UUID
- Registrar em auditoria

**Tabela:** `pei.tab_valores`

**Permissão:** Super Admin (todas), Admin Unidade (sua unidade)

### RF-05.4: Editar Valor

**Descrição:** Editar valor existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Nome, Descrição
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_valores`

**Permissão:** Super Admin (todas), Admin Unidade (sua unidade)

### RF-05.5: Excluir Valor (Soft Delete)

**Descrição:** Remover valor.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Soft delete
- Registrar em auditoria

**Tabela:** `pei.tab_valores`

**Permissão:** Super Admin (todas), Admin Unidade (sua unidade)

---

## RF-06: Perspectivas BSC

### RF-06.1: Listar Perspectivas

**Descrição:** Exibir perspectivas do Balanced Scorecard.

**Critérios de Aceitação:**
- Lista ordenada por `num_nivel_hierarquico_apresentacao`
- Mostrar: Nome, Nível, Número de Objetivos
- Filtro por ciclo PEI
- As 4 perspectivas padrão: Financeira, Clientes, Processos Internos, Aprendizado e Crescimento

**Tabela:** `pei.tab_perspectiva`

**Campos exibidos:**
- `dsc_perspectiva`
- `num_nivel_hierarquico_apresentacao`
- Count de objetivos (calculado)

### RF-06.2: Criar Perspectiva

**Descrição:** Adicionar nova perspectiva (caso necessário).

**Critérios de Aceitação:**
- Formulário com: Nome, Nível Hierárquico
- Nome: obrigatório, máximo 255 caracteres
- Nível: número de 1 a 100, obrigatório
- Seleção de ciclo PEI
- Validação: não permitir nível duplicado no mesmo PEI
- Registrar em auditoria

**Tabela:** `pei.tab_perspectiva`

**Permissão:** Apenas Super Administrador

### RF-06.3: Editar Perspectiva

**Descrição:** Editar perspectiva existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Nome, Nível
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_perspectiva`

**Permissão:** Apenas Super Administrador

### RF-06.4: Reordenar Perspectivas

**Descrição:** Alterar ordem de exibição.

**Critérios de Aceitação:**
- Drag-and-drop para reordenar
- Atualizar `num_nivel_hierarquico_apresentacao` automaticamente
- Salvar ao soltar
- Registrar em auditoria

**Tabela:** `pei.tab_perspectiva`

**Permissão:** Apenas Super Administrador

---

## RF-07: Objetivos Estratégicos

### RF-07.1: Listar Objetivos por Perspectiva

**Descrição:** Exibir objetivos agrupados por perspectiva BSC.

**Critérios de Aceitação:**
- Visualização em abas (4 abas, uma por perspectiva)
- Em cada aba: lista de objetivos
- Mostrar: Nome, Descrição (resumida), Status, % Atingimento
- Ordenação por `num_nivel_hierarquico_apresentacao`
- Filtro por ciclo PEI
- Filtro por organização
- Card de cada objetivo exibe KPIs vinculados (quantidade)
- Indicador visual de performance (verde/amarelo/vermelho)

**Tabela:** `pei.tab_objetivo_estrategico`

**Campos exibidos:**
- `nom_objetivo_estrategico`
- `dsc_objetivo_estrategico` (truncada)
- Status calculado (baseado nos planos de ação)
- % Atingimento calculado (média dos indicadores)

### RF-07.2: Visualizar Detalhes de Objetivo

**Descrição:** Ver informações completas de um objetivo.

**Critérios de Aceitação:**
- Modal ou página dedicada
- Exibir: Nome, Descrição completa, Perspectiva, Nível Hierárquico
- Exibir lista de Futuros Almejados
- Exibir lista de Planos de Ação vinculados
- Exibir lista de Indicadores (KPIs)
- Gráfico de evolução dos KPIs
- Timeline de alterações (auditoria)

**Tabelas:** `pei.tab_objetivo_estrategico`, `pei.tab_futuro_almejado_objetivo_estrategico`, `pei.tab_plano_de_acao`, `pei.tab_indicador`

### RF-07.3: Criar Objetivo Estratégico

**Descrição:** Adicionar novo objetivo.

**Critérios de Aceitação:**
- Formulário com: Nome, Descrição, Perspectiva, Nível Hierárquico
- Nome: obrigatório, máximo 500 caracteres
- Descrição: opcional, máximo 2000 caracteres
- Perspectiva: dropdown, obrigatório
- Nível Hierárquico: número 1-100, obrigatório
- Ao salvar, gerar UUID
- Registrar em auditoria

**Tabela:** `pei.tab_objetivo_estrategico`

**Permissão:** Super Admin, Admin Unidade

### RF-07.4: Editar Objetivo Estratégico

**Descrição:** Editar objetivo existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Nome, Descrição, Perspectiva, Nível
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_objetivo_estrategico`

**Permissão:** Super Admin, Admin Unidade

### RF-07.5: Excluir Objetivo (Soft Delete)

**Descrição:** Remover objetivo.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui planos de ação ou indicadores (alertar)
- Soft delete
- Registrar em auditoria

**Tabela:** `pei.tab_objetivo_estrategico`

**Permissão:** Super Admin, Admin Unidade

### RF-07.6: Gerenciar Futuro Almejado

**Descrição:** Adicionar/Editar/Remover futuros almejados de um objetivo.

**Critérios de Aceitação:**
- Lista de futuros almejados por objetivo
- Botão "Adicionar Futuro Almejado"
- Modal com textarea (máximo 1000 caracteres)
- Permitir editar ou excluir
- Registrar em auditoria

**Tabela:** `pei.tab_futuro_almejado_objetivo_estrategico`

**Permissão:** Super Admin, Admin Unidade

---

## RF-08: Planos de Ação

### RF-08.1: Listar Planos de Ação

**Descrição:** Exibir planos de ação (Ações, Iniciativas, Projetos).

**Critérios de Aceitação:**
- Tabela com: Tipo, Descrição, Objetivo, Organização, Data Início/Fim, Status
- Filtro por tipo de execução (Ação/Iniciativa/Projeto)
- Filtro por objetivo estratégico
- Filtro por organização
- Filtro por status
- Filtro por período (data início/fim)
- Ordenação por diversos campos
- Paginação (20 por página)
- Badge colorido por status
- Indicador de atraso (se data fim < hoje e status != concluído)

**Tabela:** `pei.tab_plano_de_acao`

**Campos exibidos:**
- `cod_tipo_execucao` (Ação/Iniciativa/Projeto)
- `dsc_plano_de_acao` (truncado)
- Objetivo Estratégico (nome)
- Organização (sigla)
- `dte_inicio` e `dte_fim`
- `bln_status`
- `vlr_orcamento_previsto`

### RF-08.2: Visualizar Detalhes de Plano de Ação

**Descrição:** Ver informações completas do plano.

**Critérios de Aceitação:**
- Modal ou página dedicada
- Exibir: Descrição, Tipo, Objetivo, Organização, Datas, Orçamento, Status
- Exibir responsáveis (Gestor Responsável e Substituto)
- Exibir lista de Entregas
- Exibir lista de Indicadores
- Exibir códigos PPA e LOA (se houver)
- Timeline de alterações (auditoria)

**Tabelas:** `pei.tab_plano_de_acao`, `pei.tab_entregas`, `pei.tab_indicador`, `rel_users_tab_organizacoes_tab_perfil_acesso`

### RF-08.3: Criar Plano de Ação

**Descrição:** Adicionar novo plano.

**Critérios de Aceitação:**
- Formulário com: Descrição, Tipo, Objetivo, Organização, Datas, Orçamento, Status
- Descrição: obrigatório, máximo 2000 caracteres
- Tipo de Execução: dropdown (Ação/Iniciativa/Projeto), obrigatório
- Objetivo Estratégico: dropdown, obrigatório
- Organização: dropdown, obrigatório
- Nível Hierárquico: número 1-100
- Data Início: obrigatório
- Data Fim: obrigatório, maior que Data Início
- Orçamento: opcional, decimal
- Status: dropdown, obrigatório
- Códigos PPA e LOA: opcionais, varchar
- Ao salvar, gerar UUID
- Permitir adicionar responsáveis (multi-select de usuários)
- Registrar em auditoria

**Tabela:** `pei.tab_plano_de_acao`

**Permissão:** Super Admin, Admin Unidade

### RF-08.4: Editar Plano de Ação

**Descrição:** Editar plano existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar todos os campos
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_plano_de_acao`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-08.5: Excluir Plano de Ação (Soft Delete)

**Descrição:** Remover plano.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui indicadores ou entregas (alertar)
- Soft delete
- Registrar em auditoria

**Tabela:** `pei.tab_plano_de_acao`

**Permissão:** Super Admin, Admin Unidade

### RF-08.6: Gerenciar Entregas de Plano de Ação

**Descrição:** Adicionar/Editar/Remover entregas.

**Critérios de Aceitação:**
- Lista de entregas por plano
- Botão "Adicionar Entrega"
- Formulário: Descrição, Status, Período de Medição, Nível Hierárquico
- Descrição: obrigatório, máximo 1000 caracteres
- Status: dropdown, obrigatório
- Período de Medição: dropdown (Mensal/Bimestral/Trimestral/Semestral/Anual)
- Permitir editar ou excluir entrega
- Ordenação por nível hierárquico
- Drag-and-drop para reordenar
- Registrar em auditoria

**Tabela:** `pei.tab_entregas`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-08.7: Gerenciar Responsáveis

**Descrição:** Atribuir Gestor Responsável e Substituto(s).

**Critérios de Aceitação:**
- Formulário de atribuição de responsáveis
- Selecionar usuário (dropdown)
- Selecionar perfil (Gestor Responsável ou Substituto)
- Selecionar organização
- Permitir múltiplos substitutos
- Não permitir duplicatas
- Registrar em auditoria

**Tabela:** `rel_users_tab_organizacoes_tab_perfil_acesso`

**Permissão:** Super Admin, Admin Unidade

---

## RF-09: Indicadores (KPIs)

### RF-09.1: Listar Indicadores

**Descrição:** Exibir lista de indicadores.

**Critérios de Aceitação:**
- Tabela com: Nome, Tipo, Unidade, Objetivo/Plano vinculado, Status
- Filtro por tipo (Objetivo Estratégico / Plano de Ação)
- Filtro por objetivo estratégico
- Filtro por plano de ação
- Filtro por organização
- Busca por nome
- Badge de status (Em dia / Atrasado / Sem dados)
- Indicador de farol (verde/amarelo/vermelho baseado na última medição)
- Paginação

**Tabela:** `pei.tab_indicador`

**Campos exibidos:**
- `nom_indicador`
- `dsc_tipo`
- `dsc_unidade_medida`
- Vinculação (Objetivo ou Plano de Ação)
- Status calculado

### RF-09.2: Visualizar Detalhes de Indicador

**Descrição:** Ver informações completas do indicador.

**Critérios de Aceitação:**
- Modal ou página dedicada
- Exibir: Nome, Descrição, Tipo, Meta, Atributos, Unidade, Peso
- Exibir fórmula de cálculo
- Exibir fonte de dados
- Exibir período de medição
- Exibir se é acumulado
- Exibir linha de base
- Exibir metas por ano
- Gráfico de evolução (previsto vs. realizado)
- Tabela de evolução mensal
- Lista de arquivos anexados
- Timeline de alterações

**Tabelas:** `pei.tab_indicador`, `pei.tab_linha_base_indicador`, `pei.tab_meta_por_ano`, `pei.tab_evolucao_indicador`, `pei.tab_arquivos`

### RF-09.3: Criar Indicador

**Descrição:** Adicionar novo indicador.

**Critérios de Aceitação:**
- Formulário com múltiplos campos:
  - Nome: obrigatório, máximo 500 caracteres
  - Descrição: obrigatório, máximo 2000 caracteres
  - Tipo: dropdown (Objetivo Estratégico / Plano de Ação)
  - Vinculação: dropdown de objetivos OU planos (dependendo do tipo)
  - Unidade de Medida: obrigatório, varchar (ex: %, R$, qtd)
  - Meta: textarea, opcional
  - Atributos: textarea, opcional
  - Referencial Comparativo: textarea, opcional
  - Fórmula: textarea, opcional
  - Fonte: varchar, opcional
  - Período de Medição: dropdown (Mensal/Bimestral/Trimestral/Semestral/Anual), obrigatório
  - Peso: número 1-100, opcional
  - Acumulado: checkbox (sim/não)
  - Observação: textarea, opcional
- Validação: Indicador deve estar vinculado a Objetivo OU Plano (não ambos, não nenhum)
- Ao salvar, gerar UUID
- Registrar em auditoria

**Tabela:** `pei.tab_indicador`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.4: Editar Indicador

**Descrição:** Editar indicador existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar todos os campos
- Validação de campos
- Alertar se alterar tipo de vinculação (pode afetar relatórios)
- Registrar em auditoria

**Tabela:** `pei.tab_indicador`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.5: Excluir Indicador (Soft Delete)

**Descrição:** Remover indicador.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui histórico de evolução (alertar)
- Soft delete em cascata (evolução, linha base, metas)
- Registrar em auditoria

**Tabela:** `pei.tab_indicador`

**Permissão:** Super Admin, Admin Unidade

### RF-09.6: Definir Linha de Base

**Descrição:** Cadastrar valor inicial do indicador.

**Critérios de Aceitação:**
- Formulário: Valor, Ano
- Valor: decimal, obrigatório
- Ano: numérico 4 dígitos, obrigatório
- Permitir editar se já existir
- Registrar em auditoria

**Tabela:** `pei.tab_linha_base_indicador`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.7: Definir Metas Anuais

**Descrição:** Cadastrar metas por ano.

**Critérios de Aceitação:**
- Formulário: Ano, Meta
- Ano: numérico 4 dígitos, obrigatório
- Meta: decimal, obrigatório
- Permitir cadastrar múltiplos anos de uma vez
- Validação: não permitir ano duplicado
- Registrar em auditoria

**Tabela:** `pei.tab_meta_por_ano`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.8: Lançar Evolução Mensal

**Descrição:** Registrar valores previstos e realizados por mês.

**Critérios de Aceitação:**
- Formulário: Ano, Mês, Valor Previsto, Valor Realizado, Avaliação
- Ano: numérico 4 dígitos, obrigatório
- Mês: dropdown (1-12), obrigatório
- Valor Previsto: decimal, opcional
- Valor Realizado: decimal, opcional
- Avaliação: textarea (comentário), opcional
- Status Atualizado: checkbox (marcar quando dados estão completos)
- Validação: não permitir ano/mês duplicado
- Calcular automaticamente desvio (realizado - previsto)
- Calcular percentual de atingimento vs. meta
- Registrar em auditoria

**Tabela:** `pei.tab_evolucao_indicador`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.9: Anexar Arquivo de Evidência

**Descrição:** Fazer upload de arquivos comprobatórios.

**Critérios de Aceitação:**
- Selecionar evolução mensal (Ano/Mês)
- Upload de arquivo (PDF, Excel, Word, Imagem)
- Tamanho máximo: 10 MB
- Campos: Assunto, Data
- Assunto: obrigatório, varchar
- Data: obrigatório, date
- Armazenar arquivo em storage/app/pei/evidencias/
- Registrar nome original e tipo
- Permitir download
- Permitir excluir arquivo
- Registrar em auditoria

**Tabela:** `pei.tab_arquivos`

**Permissão:** Super Admin, Admin Unidade, Gestor Responsável, Gestor Substituto

### RF-09.10: Visualizar Farol de Desempenho

**Descrição:** Exibir indicador visual de performance (semáforo).

**Critérios de Aceitação:**
- Calcular percentual de atingimento: (Realizado / Meta) * 100
- Buscar faixa correspondente em `pei.tab_grau_satisfacao`
- Exibir cor correspondente (verde/amarelo/vermelho)
- Exibir descrição do grau de satisfação
- Mostrar em cards, dashboards e relatórios

**Tabela:** `pei.tab_grau_satisfacao`

**Regra:**
- Se percentual entre `vlr_minimo` e `vlr_maximo`: exibir `cor` e `dsc_grau_satisfacao`

---

## RF-10: Cadeia de Valor

### RF-10.1: Visualizar Cadeia de Valor

**Descrição:** Exibir cadeia de valor por perspectiva.

**Critérios de Aceitação:**
- Visualização gráfica por perspectiva
- Cada perspectiva mostra suas atividades
- Cada atividade pode ser expandida para mostrar processos
- Processos exibidos em formato: Entrada → Transformação → Saída
- Filtro por ciclo PEI
- Filtro por perspectiva

**Tabelas:** `pei.tab_atividade_cadeia_valor`, `pei.tab_processos_atividade_cadeia_valor`

### RF-10.2: Criar Atividade da Cadeia de Valor

**Descrição:** Adicionar nova atividade.

**Critérios de Aceitação:**
- Formulário com: Descrição, Perspectiva, PEI
- Descrição: obrigatório, máximo 1000 caracteres
- Perspectiva: dropdown, obrigatório
- PEI: dropdown, obrigatório
- Ao salvar, gerar UUID
- Registrar em auditoria

**Tabela:** `pei.tab_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

### RF-10.3: Editar Atividade

**Descrição:** Editar atividade existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar: Descrição, Perspectiva
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

### RF-10.4: Excluir Atividade (Soft Delete)

**Descrição:** Remover atividade.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Verificar se possui processos (alertar)
- Soft delete em cascata (processos)
- Registrar em auditoria

**Tabela:** `pei.tab_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

### RF-10.5: Criar Processo de Atividade

**Descrição:** Adicionar processo a uma atividade.

**Critérios de Aceitação:**
- Formulário com: Entrada, Transformação, Saída
- Entrada: obrigatório, textarea
- Transformação: obrigatório, textarea
- Saída: obrigatório, textarea
- Vinculação à atividade
- Ao salvar, gerar UUID
- Registrar em auditoria

**Tabela:** `pei.tab_processos_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

### RF-10.6: Editar Processo

**Descrição:** Editar processo existente.

**Critérios de Aceitação:**
- Formulário pré-preenchido
- Permitir alterar todos os campos
- Validação de campos
- Registrar em auditoria

**Tabela:** `pei.tab_processos_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

### RF-10.7: Excluir Processo (Soft Delete)

**Descrição:** Remover processo.

**Critérios de Aceitação:**
- Confirmação antes de excluir
- Soft delete
- Registrar em auditoria

**Tabela:** `pei.tab_processos_atividade_cadeia_valor`

**Permissão:** Super Admin, Admin Unidade

---

## RF-11: Dashboards

### RF-11.1: Dashboard Principal (Home)

**Descrição:** Painel executivo com visão consolidada.

**Critérios de Aceitação:**
- Seletor de organização (dropdown) - padrão: organização principal do usuário
- Seletor de ciclo PEI (dropdown) - padrão: ciclo ativo
- Cards com KPIs principais:
  - Total de Objetivos Estratégicos
  - Total de Planos de Ação (com breakdown por tipo)
  - Total de Indicadores
  - % Médio de Atingimento Geral
- Gráfico de % Atingimento por Perspectiva (radar/spider)
- Gráfico de evolução mensal dos indicadores críticos
- Lista de "Alertas" (planos atrasados, indicadores sem lançamento, desvios críticos)
- Últimas atualizações (timeline)

**Tabelas:** Múltiplas (agregação)

### RF-11.2: Dashboard de Objetivos Estratégicos

**Descrição:** Visão detalhada dos objetivos.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Perspectiva
- Cards de objetivos com:
  - Nome
  - % Atingimento
  - Número de planos vinculados
  - Número de indicadores
  - Status visual (farol)
- Gráfico de distribuição de objetivos por perspectiva
- Gráfico de % atingimento por objetivo (bar chart horizontal)
- Opção de drill-down (clicar em objetivo para ver detalhes)

**Tabelas:** `pei.tab_objetivo_estrategico`, `pei.tab_indicador`, `pei.tab_plano_de_acao`

### RF-11.3: Dashboard de Indicadores

**Descrição:** Painel de monitoramento de KPIs.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Objetivo, Plano, Período
- Tabela de indicadores com colunas:
  - Nome
  - Última medição (Realizado vs. Previsto)
  - Desvio
  - % Atingimento vs. Meta
  - Farol de desempenho
  - Status de atualização
- Gráficos:
  - Evolução temporal (line chart) - seleção de múltiplos indicadores
  - Comparativo Previsto vs. Realizado
  - Distribuição de faróis (quantos verdes/amarelos/vermelhos)
- Alertas de indicadores sem lançamento (últimos 2 meses)

**Tabelas:** `pei.tab_indicador`, `pei.tab_evolucao_indicador`, `pei.tab_meta_por_ano`

### RF-11.4: Dashboard de Planos de Ação

**Descrição:** Visão de acompanhamento dos planos.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Objetivo, Tipo, Status
- Cards com totais:
  - Ações / Iniciativas / Projetos
  - No prazo / Atrasados / Concluídos
- Gráfico de Gantt simplificado (timeline)
- Lista de planos com status visual
- Alertas de planos próximos ao vencimento
- Gráfico de orçamento (previsto vs. realizado)

**Tabelas:** `pei.tab_plano_de_acao`

### RF-11.5: Mapa Estratégico Interativo

**Descrição:** Visualização gráfica do BSC.

**Critérios de Aceitação:**
- Visualização das 4 perspectivas (Financeira → Clientes → Processos → Aprendizado)
- Cada perspectiva mostra seus objetivos como blocos/cards
- Linhas conectando objetivos (relações de causa-efeito)
- Cor dos blocos baseada em % atingimento (verde/amarelo/vermelho)
- Hover mostra detalhes do objetivo
- Click abre modal com detalhes completos
- Filtro por organização e PEI
- Opção de exportar como imagem

**Tabelas:** `pei.tab_perspectiva`, `pei.tab_objetivo_estrategico`

---

## RF-12: Relatórios

### RF-12.1: Relatório de Identidade Estratégica

**Descrição:** Documento com Missão, Visão, Valores.

**Critérios de Aceitação:**
- Filtros: Organização, PEI
- Formato: PDF
- Conteúdo:
  - Cabeçalho com logo e nome da organização
  - Missão (texto completo)
  - Visão (texto completo)
  - Valores (lista)
  - Rodapé com data de geração
- Botão "Exportar PDF"

**Tabelas:** `pei.tab_missao_visao_valores`, `pei.tab_valores`

### RF-12.2: Relatório de Objetivos Estratégicos

**Descrição:** Listagem completa de objetivos.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Perspectiva
- Formato: PDF e Excel
- Conteúdo:
  - Tabela com: Perspectiva, Objetivo, Descrição, KPIs, % Atingimento, Status
  - Agrupamento por perspectiva
  - Totalizadores
- Botões "Exportar PDF" e "Exportar Excel"

**Tabelas:** `pei.tab_objetivo_estrategico`, `pei.tab_indicador`

### RF-12.3: Relatório de Planos de Ação

**Descrição:** Listagem de planos.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Objetivo, Tipo, Status, Período
- Formato: PDF e Excel
- Conteúdo:
  - Tabela com: Tipo, Descrição, Objetivo, Organização, Responsável, Data Início, Data Fim, Orçamento, Status
  - Agrupamento por tipo ou objetivo (opção)
  - Totalizadores de orçamento
  - Indicadores de atraso
- Botões "Exportar PDF" e "Exportar Excel"

**Tabelas:** `pei.tab_plano_de_acao`, `rel_users_tab_organizacoes_tab_perfil_acesso`

### RF-12.4: Relatório de Indicadores

**Descrição:** Detalhamento de KPIs.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Objetivo, Plano, Período
- Formato: PDF e Excel
- Conteúdo:
  - Tabela com: Nome, Tipo, Unidade, Meta, Linha Base, Realizado, % Atingimento, Farol
  - Gráficos de evolução temporal
  - Comparativo Previsto vs. Realizado
  - Análise de desvios
- Botões "Exportar PDF" e "Exportar Excel"

**Tabelas:** `pei.tab_indicador`, `pei.tab_evolucao_indicador`, `pei.tab_meta_por_ano`

### RF-12.5: Relatório Executivo Consolidado

**Descrição:** Relatório gerencial completo.

**Critérios de Aceitação:**
- Filtros: Organização, PEI, Período
- Formato: PDF
- Conteúdo:
  - Sumário executivo
  - Identidade estratégica
  - Mapa estratégico (imagem)
  - Objetivos por perspectiva
  - Principais indicadores (TOP 10)
  - Planos de ação em andamento
  - Alertas e desvios
  - Análise de performance
  - Recomendações (se houver)
- Botão "Exportar PDF"

**Tabelas:** Múltiplas

### RF-12.6: Relatório de Evolução de Indicador (Individual)

**Descrição:** Detalhamento de um indicador específico.

**Critérios de Aceitação:**
- Selecionar indicador
- Período (ano)
- Formato: PDF
- Conteúdo:
  - Ficha técnica do indicador (nome, fórmula, unidade, meta, etc.)
  - Tabela mensal: Previsto, Realizado, Desvio, % Atingimento
  - Gráfico de linha (evolução temporal)
  - Gráfico de barras (previsto vs. realizado)
  - Comentários/Avaliações mensais
  - Arquivos anexados (links)
- Botão "Exportar PDF"

**Tabelas:** `pei.tab_indicador`, `pei.tab_evolucao_indicador`, `pei.tab_arquivos`

---

## RF-13: Auditoria

### RF-13.1: Visualizar Logs de Auditoria

**Descrição:** Consultar histórico de alterações.

**Critérios de Aceitação:**
- Filtros:
  - Usuário (dropdown)
  - Tabela (dropdown)
  - Ação (Criação/Edição/Exclusão)
  - Período (data início/fim)
  - IP
- Tabela com colunas:
  - Data/Hora
  - Usuário
  - Ação
  - Tabela
  - Registro ID
  - IP
  - Detalhes (botão)
- Ordenação por data (decrescente)
- Paginação (50 por página)
- Botão "Exportar Excel"

**Tabelas:** `tab_audit`, `audits`

### RF-13.2: Visualizar Detalhes de Auditoria

**Descrição:** Ver alterações específicas (antes/depois).

**Critérios de Aceitação:**
- Modal ou página dedicada
- Exibir:
  - Data/Hora completa
  - Usuário (nome e email)
  - IP
  - User-Agent (navegador)
  - Tabela e coluna alterada
  - Valor anterior (destacado em vermelho)
  - Valor novo (destacado em verde)
  - Diff visual (se possível)

**Tabelas:** `tab_audit`, `audits`

### RF-13.3: Visualizar Timeline de Alterações de um Registro

**Descrição:** Histórico completo de um registro específico.

**Critérios de Aceitação:**
- Selecionar tabela e ID do registro
- Timeline visual (vertical)
- Cada item da timeline mostra:
  - Data/Hora
  - Usuário
  - Ação
  - Campos alterados
  - Link para ver detalhes
- Ordenação cronológica (mais recente primeiro)
- Filtro por período

**Tabelas:** `tab_audit`, `audits`

---

## RF-14: GESTÃO DE RISCOS

**Descrição:** Módulo para identificação, avaliação, monitoramento e mitigação de riscos estratégicos que podem impactar o alcance dos objetivos estratégicos da organização.

---

### RF-14.1: Listar Riscos Estratégicos

**Descrição:** Exibir todos os riscos identificados, com filtros e busca.

**Critérios de Aceitação:**
- Tabela responsiva com colunas: Código, Título, Categoria, Probabilidade, Impacto, Nível (Baixo/Médio/Alto/Crítico), Status, Ações
- Filtros: PEI, Organização, Categoria, Nível de Risco, Status
- Busca por título ou descrição
- Paginação (20 por página)
- Indicador visual de nível de risco (cores: verde/amarelo/laranja/vermelho)
- Botão "Novo Risco" (permissão necessária)

**Regras de Negócio:**
- Super Admin vê todos os riscos
- Admin Unidade vê riscos da sua organização
- Gestor vê riscos vinculados aos seus objetivos/planos
- Nível de Risco = Probabilidade x Impacto
  - Baixo: 1-4
  - Médio: 5-9
  - Alto: 10-15
  - Crítico: 16-25

**Tabelas:** `pei.tab_risco`

---

### RF-14.2: Visualizar Detalhes de Risco

**Descrição:** Exibir todas as informações de um risco específico.

**Critérios de Aceitação:**
- Card com dados completos do risco
- Seções: Identificação, Avaliação, Mitigação, Histórico
- Mostrar matriz de risco visual (Probabilidade x Impacto)
- Timeline de ocorrências
- Planos de mitigação vinculados
- Objetivos estratégicos vinculados
- Responsáveis pelo risco
- Botões de ação: Editar, Registrar Ocorrência, Nova Mitigação

**Tabelas:** `pei.tab_risco`, `pei.tab_risco_objetivo`, `pei.tab_risco_ocorrencia`, `pei.tab_risco_mitigacao`

---

### RF-14.3: Criar Risco Estratégico

**Descrição:** Cadastrar novo risco identificado.

**Critérios de Aceitação:**
- Formulário com campos:
  - **Identificação:**
    - Título do Risco (obrigatório)
    - Descrição detalhada (obrigatório)
    - Categoria (dropdown: Operacional, Financeiro, Reputacional, Legal, Tecnológico, Estratégico, Ambiental)
    - Status (dropdown: Identificado, Em Análise, Monitorado, Mitigado, Materializado, Encerrado)
  - **Avaliação:**
    - Probabilidade (1-5): Muito Baixa, Baixa, Média, Alta, Muito Alta
    - Impacto (1-5): Muito Baixo, Baixo, Médio, Alto, Muito Alto
    - Nível de Risco (calculado automaticamente: Probabilidade x Impacto)
  - **Vínculos:**
    - Organização responsável (obrigatório)
    - Objetivos Estratégicos relacionados (opcional, múltipla seleção)
    - Responsável pelo monitoramento (usuário)
  - **Contexto:**
    - Causas potenciais
    - Consequências esperadas
- Validações: Título único por PEI e Organização
- Preview da matriz de risco

**Regras de Negócio:**
- Ao criar, status inicial = "Identificado"
- Nível de Risco atualiza automaticamente ao mudar Probabilidade ou Impacto
- Se Nível ≥ 16 (Crítico), notificar Super Admin e Admin Unidade

**Tabelas:** `pei.tab_risco`, `pei.tab_risco_objetivo`

---

### RF-14.4: Editar Risco Estratégico

**Descrição:** Atualizar informações de risco existente.

**Critérios de Aceitação:**
- Mesmo formulário de criação, pré-preenchido
- Histórico de alterações de Probabilidade e Impacto
- Ao alterar Probabilidade/Impacto, registrar no histórico com data e usuário
- Confirmação ao mudar status para "Materializado"

**Regras de Negócio:**
- Apenas Super Admin, Admin Unidade e Responsável pelo risco podem editar
- Mudança para status "Materializado" requer registro de ocorrência
- Auditoria completa de todas as alterações

**Tabelas:** `pei.tab_risco`, `pei.tab_risco_objetivo`

---

### RF-14.5: Excluir Risco (Soft Delete)

**Descrição:** Desativar risco (exclusão lógica).

**Critérios de Aceitação:**
- Confirmação com modal: "Tem certeza que deseja excluir este risco?"
- Soft delete (campo `deleted_at`)
- Riscos excluídos não aparecem nas listagens
- Super Admin pode restaurar

**Regras de Negócio:**
- Não permitir exclusão se houver ocorrências registradas
- Apenas Super Admin e Admin Unidade podem excluir

**Tabelas:** `pei.tab_risco`

---

### RF-14.6: Criar Plano de Mitigação

**Descrição:** Definir ações para reduzir probabilidade ou impacto do risco.

**Critérios de Aceitação:**
- Formulário vinculado a um risco:
  - Tipo (dropdown: Prevenir, Reduzir, Transferir, Aceitar)
  - Descrição da ação (obrigatório)
  - Responsável (usuário)
  - Prazo (data)
  - Status (A Fazer, Em Andamento, Concluído)
  - Custo estimado (opcional)
- Múltiplos planos por risco
- Checklist de tarefas

**Regras de Negócio:**
- Riscos críticos (nível ≥ 16) devem ter ao menos 1 plano de mitigação
- Plano concluído não pode ser editado (apenas visualizado)

**Tabelas:** `pei.tab_risco_mitigacao`

---

### RF-14.7: Registrar Ocorrência de Risco

**Descrição:** Registrar quando um risco se materializa.

**Critérios de Aceitação:**
- Formulário:
  - Data da ocorrência (obrigatório)
  - Descrição do evento (obrigatório)
  - Impacto real observado (1-5)
  - Ações tomadas
  - Lições aprendidas
  - Anexos (opcional)
- Ao registrar, alterar status do risco para "Materializado"
- Notificar responsáveis

**Regras de Negócio:**
- Ocorrência gera alerta para Admin Unidade e responsável
- Ocorrência deve ser vinculada a um risco existente
- Histórico completo de ocorrências

**Tabelas:** `pei.tab_risco_ocorrencia`, `pei.tab_arquivos`

---

### RF-14.8: Visualizar Matriz de Riscos

**Descrição:** Visualização gráfica da matriz Probabilidade x Impacto.

**Critérios de Aceitação:**
- Grid 5x5 (Probabilidade x Impacto)
- Riscos posicionados na matriz como círculos/badges
- Cores por nível: Verde (Baixo), Amarelo (Médio), Laranja (Alto), Vermelho (Crítico)
- Hover mostra detalhes do risco
- Click abre detalhes completos
- Filtros: PEI, Organização, Categoria, Status

**Regras de Negócio:**
- Matriz atualiza em tempo real (Livewire)
- Apenas riscos com status ativo (não "Encerrado")

**Tabelas:** `pei.tab_risco`

---

### RF-14.9: Dashboard de Riscos

**Descrição:** Painel executivo com visão geral dos riscos.

**Critérios de Aceitação:**
- **Cards de métricas:**
  - Total de riscos ativos
  - Riscos críticos (nível ≥ 16)
  - Riscos materializados no período
  - Taxa de mitigação (% riscos com plano)
- **Gráficos (Chart.js):**
  - Distribuição por categoria (pizza)
  - Distribuição por nível (barras)
  - Evolução temporal (linha)
  - Top 10 riscos críticos (tabela)
- **Alertas:**
  - Riscos sem plano de mitigação
  - Planos de mitigação atrasados
  - Riscos materializados recentes (últimos 30 dias)
- Filtros: Período, PEI, Organização

**Tabelas:** `pei.tab_risco`, `pei.tab_risco_mitigacao`, `pei.tab_risco_ocorrencia`

---

### RF-14.10: Relatório de Riscos

**Descrição:** Relatório consolidado em PDF/Excel.

**Critérios de Aceitação:**
- Opções de filtro: PEI, Organização, Categoria, Nível, Período
- **Conteúdo do relatório:**
  - Sumário executivo
  - Matriz de riscos visual
  - Lista detalhada de riscos
  - Planos de mitigação
  - Histórico de ocorrências
  - Gráficos e estatísticas
- Formatos: PDF (DomPDF) e Excel (Maatwebsite Excel)
- Geração assíncrona para grandes volumes

**Regras de Negócio:**
- Relatório respeita permissões de visualização
- Incluir filtros aplicados no cabeçalho
- Logo da organização

**Tabelas:** `pei.tab_risco`, `pei.tab_risco_mitigacao`, `pei.tab_risco_ocorrencia`

---

## ESTRUTURA DE TABELAS PARA GESTÃO DE RISCOS

### Tabela: `pei.tab_risco`

```sql
cod_risco UUID PRIMARY KEY
cod_pei UUID FK → pei.tab_pei
cod_organizacao UUID FK → tab_organizacoes
num_codigo_risco INT (auto-incremento por PEI)
dsc_titulo VARCHAR(255)
txt_descricao TEXT
dsc_categoria VARCHAR(50) -- Operacional, Financeiro, etc.
dsc_status VARCHAR(50) -- Identificado, Em Análise, Monitorado, etc.
num_probabilidade INT (1-5)
num_impacto INT (1-5)
num_nivel_risco INT (calculado: probabilidade * impacto)
txt_causas TEXT
txt_consequencias TEXT
cod_responsavel_monitoramento UUID FK → users
created_at TIMESTAMP
updated_at TIMESTAMP
deleted_at TIMESTAMP
```

### Tabela: `pei.tab_risco_objetivo`

```sql
cod_risco_objetivo UUID PRIMARY KEY
cod_risco UUID FK → pei.tab_risco
cod_objetivo_estrategico UUID FK → pei.tab_objetivo_estrategico
created_at TIMESTAMP
```

### Tabela: `pei.tab_risco_mitigacao`

```sql
cod_mitigacao UUID PRIMARY KEY
cod_risco UUID FK → pei.tab_risco
dsc_tipo VARCHAR(50) -- Prevenir, Reduzir, Transferir, Aceitar
txt_descricao TEXT
cod_responsavel UUID FK → users
dte_prazo DATE
dsc_status VARCHAR(50) -- A Fazer, Em Andamento, Concluído
vlr_custo_estimado DECIMAL(15,2)
created_at TIMESTAMP
updated_at TIMESTAMP
```

### Tabela: `pei.tab_risco_ocorrencia`

```sql
cod_ocorrencia UUID PRIMARY KEY
cod_risco UUID FK → pei.tab_risco
dte_ocorrencia DATE
txt_descricao TEXT
num_impacto_real INT (1-5)
txt_acoes_tomadas TEXT
txt_licoes_aprendidas TEXT
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

## RESUMO DE PERMISSÕES

| Funcionalidade | Super Admin | Admin Unidade | Gestor Responsável | Gestor Substituto |
|----------------|------------|---------------|-------------------|------------------|
| Login/Logout | ✅ | ✅ | ✅ | ✅ |
| Gestão de Organizações | ✅ (todas) | ❌ | ❌ | ❌ |
| Gestão de Usuários | ✅ (todos) | ✅ (sua unidade) | ❌ | ❌ |
| Gestão de PEI | ✅ | ❌ | ❌ | ❌ |
| Identidade Estratégica | ✅ | ✅ (sua unidade) | ❌ | ❌ |
| Perspectivas BSC | ✅ | ❌ | ❌ | ❌ |
| Objetivos Estratégicos | ✅ | ✅ (sua unidade) | ❌ | ❌ |
| Planos de Ação | ✅ | ✅ (sua unidade) | ✅ (seus planos) | ✅ (planos que substitui) |
| Indicadores | ✅ | ✅ (sua unidade) | ✅ (seus planos) | ✅ (planos que substitui) |
| Lançamento de Evolução | ✅ | ✅ | ✅ | ✅ |
| Cadeia de Valor | ✅ | ✅ (sua unidade) | ❌ | ❌ |
| Dashboards | ✅ (todos) | ✅ (sua unidade) | ✅ (seus planos) | ✅ (planos que substitui) |
| Relatórios | ✅ (todos) | ✅ (sua unidade) | ✅ (seus planos) | ✅ (planos que substitui) |
| Auditoria | ✅ (tudo) | ✅ (sua unidade) | ✅ (seus planos) | ✅ (planos que substitui) |
| Gestão de Riscos | ✅ (todos) | ✅ (sua unidade) | ✅ (riscos vinculados) | ✅ (riscos vinculados) |

---

## PRÓXIMOS PASSOS

Este documento de requisitos funcionais será complementado por:
- **03-REQUISITOS-NAO-FUNCIONAIS.md** - Performance, segurança, usabilidade
- **04-MODELOS-ELOQUENT.md** - Estrutura de Models
- **05-COMPONENTES-LIVEWIRE.md** - Componentes reutilizáveis
- **06-MIGRATIONS-NOVAS.md** - Novas tabelas (comentários, notificações)
- **07-ESTRUTURA-PASTAS.md** - Organização do código
- **08-ROADMAP-IMPLEMENTACAO.md** - Planejamento de desenvolvimento
