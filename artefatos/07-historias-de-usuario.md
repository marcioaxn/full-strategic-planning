# Histórias de Usuário

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Épico 1 — Portal de Módulos e Boas-Vindas

**H00** — Como **visitante não autenticado**, quero ver uma landing page moderna, elegante e humanizada ao acessar o sistema, que apresente o sistema de forma clara, mostre seus módulos e funcionalidades principais, e me convide a fazer login — sem exibir o Mapa Estratégico (que requer contexto de organização e PEI ativo) nem redirecionar diretamente para o login.

**Critérios de aceite:**
- A rota `/` (pública, sem autenticação) exibe a landing page, não o Mapa Estratégico
- A landing page usa o layout `public.blade.php` com navbar pública
- A página apresenta: nome do sistema, tagline, os 3 módulos GPPEI em cards numerados (01/02/03), lista de funcionalidades principais, e botão de acesso/login em destaque
- Design moderno, responsivo, com identidade visual alinhada ao GPPEI (paleta azul marinho e laranja)
- Usuários já autenticados que acessam `/` são redirecionados automaticamente para o dashboard
- A página não exige nenhuma query ao banco de dados (puramente estática/visual)

**H01** — Como **qualquer usuário autenticado**, quero ver uma página inicial que mostre todos os módulos do sistema com o status atual de cada um (concluído / em andamento / pendente), para que eu saiba por onde começar e o que ainda precisa ser feito.

**Critérios de aceite:**
- A página inicial exibe os 3 módulos GPPEI (Inaugurar, Planejar, Monitorar) como seções com ícones distintos
- Cada módulo tem um indicador visual de progresso (baseado no `PeiGuidanceService`)
- Existe um botão "Acessar módulo" que leva à primeira tela incompleta do módulo
- A página exibe nome do PEI vigente e organização selecionada

**H02** — Como **Administrador Geral**, quero poder assumir temporariamente a identidade de outro usuário cadastrado, para diagnosticar incidentes e verificar permissões.

**Critérios de aceite:**
- Apenas o Administrador Geral (flag `adm=true`) tem acesso à função de impersonate
- Toda ação realizada em modo impersonate fica registrada no `tab_audit` com flag de identificação
- O cabeçalho do sistema exibe banner de aviso quando em modo impersonate
- Existe botão "Encerrar impersonação" visível em destaque

---

## Épico 2 — Módulo 01: Inaugurar e Integrar

**H03** — Como **Gestor Estratégico**, quero registrar as diretrizes da Alta Direção ao iniciar um novo ciclo PEI, para que fique documentada a demanda e as expectativas que orientarão todo o processo.

**Critérios de aceite:**
- Existe uma tela de "Planejar o Planejamento" acessível a partir do ciclo PEI
- Os campos incluem: equipe de planejamento, diretrizes da alta direção, cronograma previsto, metodologias a adotar, observações
- O preenchimento desta tela gera a fase "Inaugurar" como concluída no `PeiGuidanceService`
- Link para a página 10 do GPPEI (Módulo 01, Passo 01) visível na tela

**H04** — Como **Gestor Estratégico**, quero registrar a integração do PEI com os instrumentos de governo (PPA, LOA, ODS, Planos Setoriais), para garantir alinhamento metodológico.

**Critérios de aceite:**
- Existe uma tela de "Integração com Instrumentos" associada ao ciclo PEI
- Para cada instrumento (PPA, LOA, Agenda 2030 ODS, Planos Setoriais) o gestor pode registrar: pontos de atenção, tarefas de integração, intensidade da interface (Alta/Média/Baixa)
- Os registros ficam versionados por ciclo PEI
- Link para a página 14 do GPPEI visível na tela

---

## Épico 3 — Módulo 02: Planejar — Cadeia de Valor

**H05** — Como **Gestor Estratégico**, quero criar e visualizar a Cadeia de Valor da organização em formato de diagrama visual interativo, para que todos possam compreender os macroprocessos e as atividades finalísticas.

**Critérios de aceite:**
- Existe uma tela dedicada à Cadeia de Valor (`/pei/cadeia-valor`)
- O diagrama distingue visualmente: Atividades Finalísticas e Atividades de Suporte (conforme modelo Porter/GPPEI)
- É possível adicionar, editar e remover atividades e processos
- A tela tem link para a página 24 do GPPEI
- O diagrama é exportável como imagem ou PDF

---

## Épico 4 — Módulo 02: Planejar — Análise Ambiental

**H06** — Como **Gestor Estratégico**, quero executar a Análise SWOT de forma guiada, com campos para cada quadrante (Forças, Fraquezas, Oportunidades, Ameaças), para que o diagnóstico organizacional fique documentado e vinculado ao ciclo PEI.

**Critérios de aceite:**
- A tela de Análise SWOT (`/pei/swot`) exibe os 4 quadrantes com campos de texto
- Cada item pode ser classificado por prioridade (Matriz GUT: Gravidade / Urgência / Tendência)
- A tela tem link para a página 66 do GPPEI
- Os dados ficam vinculados ao `cod_pei` e `cod_organizacao`
- É possível exportar o SWOT em PDF

**H07** — Como **Gestor Estratégico**, quero executar a Análise PESTEL com campos para cada dimensão (Política, Econômica, Social, Tecnológica, Ecológica, Legal), para mapear os fatores externos que impactam a organização.

**Critérios de aceite:**
- A tela de Análise PESTEL (`/pei/pestel`) exibe as 6 dimensões com campos de texto e múltiplos itens
- A tela tem link para a página 70 do GPPEI
- Os dados ficam vinculados ao `cod_pei`

**H08** — Como **Gestor Estratégico**, quero registrar as Partes Interessadas (Stakeholders) do processo de planejamento com sua influência e interesse, para planejar o engajamento adequado.

**Critérios de aceite:**
- Existe componente de Análise de Partes Interessadas na tela de Análise Ambiental
- Para cada parte interessada: nome, tipo (interno/externo), nível de interesse (1-5), nível de influência (1-5), estratégia de engajamento
- Link para a página 89 do GPPEI

---

## Épico 5 — Módulo 02: Planejar — Referencial Estratégico

**H09** — Como **Gestor Estratégico**, quero que a tela de Missão, Visão e Valores siga a estrutura visual do GPPEI (com campo dedicado para Temas Norteadores), e que o preenchimento gere um preview do Referencial Estratégico completo.

**Critérios de aceite:**
- A tela de Missão/Visão inclui campos: Missão, Visão, Valores, Temas Norteadores, Futuro Almejado
- Existe um preview/modal "Referencial Estratégico Completo" que exibe todos os campos formatados
- Link para a página 29 do GPPEI

**H10** — Como **Gestor Estratégico**, quero poder classificar as metas dos indicadores como SMART (Específica, Mensurável, Atingível, Relevante, Temporal), para garantir qualidade metodológica das métricas.

**Critérios de aceite:**
- Na tela de cadastro/edição de indicador existe um checklist de validação SMART
- O sistema exibe um badge "Meta SMART Validada" quando todos os critérios forem marcados
- Link para a página 77 do GPPEI

---

## Épico 6 — Módulo 02: Planejar — Carteira de Projetos

**H11** — Como **Gestor Estratégico**, quero criar um Plano de Ação utilizando o template de Modelo Lógico (Insumos → Atividades → Resultados → Impactos), para estruturar iniciativas com coerência metodológica.

**Critérios de aceite:**
- Na tela de criação/edição de Plano de Ação existe uma aba "Modelo Lógico"
- Os campos incluem: insumos necessários, atividades previstas, resultados esperados, impacto esperado, pressupostos/riscos
- Link para a página 86 do GPPEI

**H12** — Como **Gestor Estratégico**, quero descrever cada entrega usando o framework 5W2H (What / Why / Who / Where / When / How / How much), para planejar atividades com clareza.

**Critérios de aceite:**
- Na tela de cadastro/edição de Entrega existe uma aba ou expansão "5W2H"
- Os campos são: O quê (What), Por quê (Why), Quem (Who), Onde (Where), Quando (When), Como (How), Quanto custa (How much)
- Link para a página 116 do GPPEI

**H13** — Como **Gestor Estratégico**, quero definir a Matriz de Responsabilidades (RACI) para um Plano de Ação, para deixar claro quem é Responsável, Aprovador, Consultado e Informado em cada entrega.

**Critérios de aceite:**
- Existe componente RACI na tela de detalhe do Plano de Ação
- Para cada entrega o gestor pode atribuir papéis: R (Responsible), A (Accountable), C (Consulted), I (Informed)
- Link para a página 120 do GPPEI

**H14** — Como **Gestor Operacional**, quero visualizar e atualizar facilmente o status das minhas entregas a partir de um painel pessoal, sem precisar navegar por múltiplos planos para encontrá-las.

**Critérios de aceite:**
- Existe uma tela "Minhas Entregas" acessível pelo menu principal
- Filtra automaticamente entregas onde o usuário logado é responsável
- Exibe prazo, status, plano de ação vinculado e link de acesso rápido

---

## Épico 7 — UX Crítica: Indicadores

**H15** — Como **Gestor Estratégico**, quero ver um botão "Lançar Evolução" visível na listagem de indicadores e no detalhe de cada indicador, para saber claramente onde registrar o valor realizado do período.

**Critérios de aceite:**
- Na listagem de indicadores existe um botão/ícone "Lançar Evolução" em cada linha
- No detalhe do indicador existe seção de destaque "Evolução" com botão de lançamento
- O modal de lançamento exibe: período (mês/ano), valor previsto (pré-preenchido da MetaPorAno), campo para valor realizado e avaliação textual
- Após lançar, a linha do indicador exibe o status de atualização

---

## Épico 8 — Módulo 03: Monitorar e Avaliar

**H16** — Como **Gestor Estratégico**, quero registrar as Revisões e Avaliações da Estratégia (RAE) periodicamente, com resumo do monitoramento e encaminhamentos gerenciais, para documentar o ciclo de gestão.

**Critérios de aceite:**
- Existe o módulo RAE em `/monitoramento/rae`
- Campos: período de referência, status dos objetivos (por percentual), destaques positivos, problemas identificados, encaminhamentos, data da reunião, participantes
- É possível gerar um relatório PDF da RAE
- Link para a página 138 do GPPEI

**H17** — Como **Alta Direção**, quero ver um dashboard executivo com o status consolidado de cada perspectiva BSC (percentual de atingimento), os top-3 indicadores críticos e o progresso geral do PEI em uma única tela.

**Critérios de aceite:**
- O dashboard (`/dashboard`) exibe cards por perspectiva com percentual de atingimento e cor de status
- Exibe lista dos 3 indicadores com pior desempenho e dos 3 planos com maior atraso
- Exibe linha do tempo do ciclo PEI atual com fases concluídas

---

## Épico 9 — Integração com PDF / Links de Referência

**H18** — Como **qualquer usuário**, quero acessar a seção correspondente do GPPEI diretamente da tela em que estou trabalhando, para consultar a orientação metodológica sem sair do sistema.

**Critérios de aceite:**
- Cada tela principal possui um ícone/botão "Ver no GPPEI" (ex.: ícone de livro)
- Ao clicar, abre o PDF na página correspondente ao módulo (via `#page=N` no fragmento de URL para PDF inline, ou navegação até a página via viewer embutido)
- O PDF fica armazenado em `public/documentos/gppei.pdf` e acessível via viewer no próprio sistema

**H19** — Como **Gestor Estratégico**, quero que o sistema tenha um viewer PDF embutido para o GPPEI, com navegação por seção, para que a equipe possa consultar o guia sem precisar abrir outro programa.

**Critérios de aceite:**
- Existe rota `/documentos/gppei` que abre um viewer PDF no navegador
- O viewer suporta navegação por página e busca de texto
- Existe um menu lateral no viewer com os 3 módulos e a caixa de ferramentas como âncoras rápidas

---

## Épico 10 — Gestão de Perfis de Acesso

**H20** — Como **Administrador Geral**, quero uma tela de gestão de perfis que exiba claramente o que cada perfil pode ou não fazer no sistema, e permita atribuir perfis a usuários.

**Critérios de aceite:**
- Existe tela `/admin/perfis` com tabela de perfis × funcionalidades
- Cada célula indica: Leitura / Criação / Edição / Exclusão / Sem acesso
- O Administrador Geral pode criar novos perfis e atribuí-los a usuários por organização
- A atribuição fica registrada em `organization.rel_users_tab_organizacoes_tab_perfil_acesso`
