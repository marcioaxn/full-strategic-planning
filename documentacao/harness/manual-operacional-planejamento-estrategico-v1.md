# Manual Operacional — Sistema de Planejamento Estratégico Integrado (PEI)

---

**Versão:** 4.0  
**Data:** Junho de 2026  
**Órgão Responsável:** Ministério da Integração e do Desenvolvimento Regional (MIDR)  
**Sistema:** Sistema de Planejamento Estratégico Integrado (PEI)  
**Classificação:** Uso Interno

---

> **Sobre as figuras:** Os blocos `📸 [Figura N — …]` indicam **onde inserir a captura de tela** correspondente e **como obtê-la**. Substitua `[endereço do sistema]` pelo endereço interno da sua organização (ex.: `http://servidor-interno/fs-v1/public`).

---

## Sumário

1. [Introdução e Conceitos Essenciais](#1-introdução-e-conceitos-essenciais)
2. [Acesso ao Sistema](#2-acesso-ao-sistema)
3. [Visão Geral da Interface](#3-visão-geral-da-interface)
4. [Dashboard](#4-dashboard)
5. [Módulo 01 — Inaugurar e Integrar](#5-módulo-01--inaugurar-e-integrar)
6. [Módulo 02 — Planejar](#6-módulo-02--planejar)
7. [Módulo 03 — Monitorar e Avaliar](#7-módulo-03--monitorar-e-avaliar)
8. [Meu Espaço](#8-meu-espaço)
9. [Referências Metodológicas](#9-referências-metodológicas)
10. [Administração do Sistema](#10-administração-do-sistema)
11. [Perfis de Usuário e Permissões](#11-perfis-de-usuário-e-permissões)
12. [Mensagens de Erro e Alertas Comuns](#12-mensagens-de-erro-e-alertas-comuns)
13. [Glossário](#13-glossário)

---

## 1. Introdução e Conceitos Essenciais

### 1.1 O que é o Sistema PEI?

O **Sistema de Planejamento Estratégico Integrado (PEI)** é uma solução digital que apoia a estruturação, o monitoramento e a avaliação do planejamento estratégico das organizações do MIDR. Foi construído com base no **Guia Prático de Elaboração e Implementação do PEI — GPPEI (MGI/2025)** e na metodologia **BSC (Balanced Scorecard)**.

O sistema guia a organização por três módulos em sequência:

| Módulo | Propósito |
|---|---|
| **01 — Inaugurar e Integrar** | Planejar o processo, integrá-lo a instrumentos de governo (PPA, LOA), declarar aderência à Agenda 2030/ODS e organizar o calendário de eventos |
| **02 — Planejar** | Definir identidade (missão/visão/valores), analisar o ambiente (SWOT/PESTEL), construir perspectivas, objetivos, indicadores, planos e entregas, e visualizar o mapa estratégico |
| **03 — Monitorar e Avaliar** | Acompanhar riscos, revisar a estratégia periodicamente (RAE) e gerar relatórios executivos |

---

### 1.2 Conceitos Fundamentais de Planejamento Estratégico

> Esta seção traz os conceitos mínimos para que qualquer usuário compreenda o que está fazendo no sistema, mesmo sem formação prévia em gestão estratégica.

#### O que é Planejamento Estratégico?

É o processo pelo qual uma organização define **onde quer chegar** (visão), **por que existe** (missão) e **como vai chegar lá** (objetivos, indicadores e planos). Sem um planejamento estruturado, as ações do dia a dia podem ser eficientes individualmente mas não convergir para um resultado maior.

#### O que é BSC (Balanced Scorecard)?

O BSC é uma metodologia de gestão criada para traduzir a estratégia em objetivos mensuráveis, organizados em **perspectivas** (dimensões de análise). Em vez de avaliar a organização apenas por um ângulo (ex.: financeiro), o BSC equilibra múltiplas dimensões ao mesmo tempo.

No setor público, as perspectivas típicas são:

- **Cidadão e Sociedade** — qual valor entregamos para a população?
- **Processos Internos** — como precisamos funcionar para entregarmos esse valor?
- **Aprendizado e Crescimento** — que capacidades e pessoas precisamos desenvolver?
- **Recursos e Finanças** — como gerenciamos os recursos para viabilizar tudo?

#### A lógica do PEI no sistema (do maior ao menor)

Entender essa hierarquia é fundamental para navegar corretamente:

```
Ciclo PEI (ex.: 2024-2028)
  └─ Perspectivas BSC (dimensões de análise)
       └─ Objetivos Estratégicos (metas de médio/longo prazo)
            ├─ Indicadores (KPIs) — medem se o objetivo está sendo atingido
            └─ Planos de Ação — iniciativas concretas para atingir o objetivo
                 └─ Entregas — tarefas e produtos que compõem cada plano
```

**Exemplo prático:**
- Ciclo PEI 2024-2028
  - Perspectiva: *Cidadão e Sociedade*
    - Objetivo: *Ampliar o acesso a habitação de qualidade*
      - Indicador: *% de famílias atendidas pelo programa X*
      - Plano de Ação: *Construção de unidades habitacionais — Região Nordeste*
        - Entrega: *Estudo de demanda concluído*
        - Entrega: *Projetos executivos aprovados*
        - Entrega: *300 unidades entregues até dez/2025*

#### O que é Missão e Visão?

- **Missão:** descreve o **propósito** da organização — *por que ela existe* e *para quem*. Ex.: "Formular e implementar políticas de integração nacional e desenvolvimento regional."
- **Visão:** descreve o **estado futuro desejado** — *onde a organização quer estar* ao fim do ciclo. Ex.: "Ser reconhecida como agente central do desenvolvimento equilibrado do Brasil até 2028."

#### O que é SWOT e PESTEL?

- **SWOT:** análise do ambiente *interno* (Forças e Fraquezas próprias) e *externo* (Oportunidades e Ameaças do contexto). Serve para direcionar onde concentrar esforços.
- **PESTEL:** análise específica do ambiente *externo* em seis dimensões (Política, Econômica, Social, Tecnológica, Ambiental, Legal). Aprofunda as oportunidades e ameaças do SWOT.

#### O que é um Indicador (KPI)?

Um indicador é uma **medição objetiva** que responde à pergunta: "Estamos progredindo em direção ao objetivo?" Sem indicadores, não há como saber se o planejamento está funcionando.

Bons indicadores seguem o critério **SMART:** Específico, Mensurável, Atingível, Relevante e Temporal.

Exemplos: "% de entregas concluídas no prazo", "número de beneficiários atendidos", "R$ captados".

#### O que é um Farol?

O **farol** é a cor que o sistema atribui a um indicador ou objetivo com base no **% de atingimento da meta**:

| Cor | Significado |
|---|---|
| 🟢 **Verde** | Atingimento dentro ou acima da meta |
| 🟡 **Amarelo** | Atingimento parcial (atenção) |
| 🔴 **Vermelho** | Atingimento abaixo do mínimo (ação necessária) |
| ⚪ **Cinza** | Sem dados lançados ainda |

As faixas exatas são definidas pelo administrador em **Graus de Satisfação** (ex.: 0–70% = Vermelho; 71–89% = Amarelo; 90–100% = Verde).

#### O que é RAE?

**Revisão e Avaliação da Estratégia** é a reunião periódica (ordinária ou extraordinária) em que a alta direção e os gestores avaliam os resultados, identificam problemas, tomam decisões de ajuste e registram os encaminhamentos. No sistema, cada RAE é documentada com ata estruturada e gera um PDF.

---

## 2. Acesso ao Sistema

### 2.1 Endereço de Acesso

Acesse pelo navegador (recomenda-se Chrome ou Edge atualizados) o endereço fornecido pela sua organização:

```
[endereço do sistema]/
```

> **Atenção:** requer conexão com a rede interna. Acesso externo pode exigir VPN.

### 2.2 Tela Pública (Landing Page)

Quando há um ciclo PEI ativo, o acesso *sem autenticação* exibe o **Mapa Estratégico público** (transparência ativa). Qualquer cidadão pode visualizar os objetivos e o desempenho da organização, sem necessidade de login.

> 📸 **[Figura 1 — Tela Pública (Landing Page)]**
> *Como capturar: em janela anônima, acesse `[endereço do sistema]/` com um PEI ativo configurado.*

### 2.3 Tela de Login

> 📸 **[Figura 2 — Tela de Login]**
> *Como capturar: acesse `[endereço do sistema]/login` sem sessão ativa.*

**Passo 1:** No campo **E-mail**, informe seu e-mail institucional.  
**Passo 2:** No campo **Senha**, informe sua senha. *(A senha diferencia maiúsculas de minúsculas.)*  
**Passo 3:** Clique em **Entrar**.

Se o **2FA (Autenticação em Dois Fatores)** estiver habilitado para seu perfil, o sistema solicitará um código de 6 dígitos gerado pelo seu aplicativo autenticador (Google Authenticator, Authy etc.) após informar a senha.

> 📸 **[Figura 3 — Solicitação de Código 2FA]**
> *Como capturar: faça login com conta que tenha 2FA ativo; a tela `[endereço do sistema]/two-factor-challenge` será exibida automaticamente.*

### 2.4 Troca de Senha Obrigatória

Quando o administrador cria um novo usuário com senha temporária ou marca a conta para troca obrigatória, o sistema **redireciona automaticamente** para a tela de troca de senha em qualquer tentativa de acesso, bloqueando o uso das demais funcionalidades até que a troca seja feita.

> 📸 **[Figura 4 — Troca de Senha Obrigatória]**
> *Como capturar: faça login com usuário marcado para trocar senha; o sistema redireciona para `[endereço do sistema]/trocar-senha`.*

**Regras da nova senha (validadas em tempo real pela tela):**
- Mínimo de 8 caracteres
- Ao menos uma letra maiúscula
- Ao menos um número
- Ao menos um caractere especial (ex.: `@`, `!`, `#`)

**Passo 1:** Informe a **Senha Atual** (a temporária recebida do administrador).  
**Passo 2:** Informe a **Nova Senha**. Um indicador visual mostra em tempo real quais regras já foram atendidas.  
**Passo 3:** Repita a nova senha em **Confirmar Nova Senha**.  
**Passo 4:** Clique em **Alterar Senha**. A sessão é encerrada e você deve refazer o login com a nova senha.

### 2.5 Perfil do Usuário

Cada usuário gerencia seus próprios dados de cadastro.

**Como acessar:** clique no seu nome no canto superior direito e selecione **Perfil**, ou acesse diretamente `[endereço do sistema]/user/profile`.

> 📸 **[Figura 5 — Perfil do Usuário]**
> *Como capturar: autenticado, acesse `[endereço do sistema]/user/profile`.*

| Ação | Descrição |
|---|---|
| Alterar nome | Atualiza o nome de exibição em todo o sistema |
| Alterar e-mail | Novo endereço (pode exigir confirmação por link no e-mail) |
| Alterar senha | Senha atual + nova senha (mesmas regras do item 2.4) |
| Foto de perfil | Upload de imagem (aparece no cabeçalho e nos comentários) |
| 2FA | Ativa/desativa autenticação em dois fatores via aplicativo |
| Cor do tema | Personaliza a cor de destaque da interface |

Clique em **Salvar** ao final de cada seção individualmente.

---

## 3. Visão Geral da Interface

### 3.1 Seletores Globais (Contexto da Sessão)

No **cabeçalho** da tela ficam três seletores que definem o contexto de exibição de dados em todo o sistema. Eles funcionam como um "filtro global": tudo que você vê nas demais telas é relativo às escolhas feitas aqui.

> 📸 **[Figura 6 — Cabeçalho com Seletores Globais]**
> *Como capturar: em qualquer tela autenticada, expanda os três seletores e capture a faixa do cabeçalho.*

| Seletor | Função |
|---|---|
| **Organização** | Define a unidade cujos dados são exibidos. Ex.: "SEDEC", "DNIT" |
| **PEI** | Define o ciclo de planejamento ativo (ex.: "PEI MIDR 2024-2028") |
| **Ano** | Define o ano de referência para metas e evoluções de indicadores |

> ⚠️ **Importante:** **sempre mantenha Organização e PEI selecionados** ao iniciar o uso. Mudar qualquer seletor recarrega imediatamente os dados da tela atual. Quando nenhum PEI ou organização está selecionado, várias telas exibem dados vazios.

> 🔒 **Restrição do seletor de Organização:** a lista do seletor mostra apenas as organizações às quais você tem vínculo — e o sistema também garante, ao processar a troca, que só é possível assumir uma organização à qual você realmente pertence. Não é possível, por nenhum meio, operar em nome de uma organização à qual você não está vinculado. A única exceção é o **Super Administrador**, que pode selecionar e operar em qualquer organização do sistema, sem restrição.

### 3.2 Menu de Navegação (Sidebar)

O menu lateral organiza as funcionalidades em grupos temáticos. Clique no ícone de hambúrguer (☰) para recolher/expandir.

> 📸 **[Figura 7 — Sidebar de Navegação Expandida]**
> *Como capturar: logado como Super Administrador (que vê todos os grupos), expanda todos os grupos do menu na tela do Dashboard.*

| Grupo | Itens disponíveis |
|---|---|
| *(topo)* | **Dashboard** |
| 🚀 **Inaugurar e Integrar** | Inaugurar e Integrar · Ciclos PEI · Cadeia de Valor |
| 🧭 **Planejar** | Identidade Estratégica · Valores Institucionais · Temas Norteadores · Análise SWOT · Análise PESTEL · Perspectivas BSC · Objetivos BSC · Agenda 2030 (ODS) · Indicadores · Planos de Ação · Gerenciar Entregas · Mapa Estratégico |
| 📊 **Monitorar e Avaliar** | Gestão de Riscos · RAE — Revisão da Estratégia · Relatórios · Histórico de Relatórios |
| 👤 **Meu Espaço** | Minhas Entregas · Lições Aprendidas |
| 📚 **Referências** | Guia GPPEI · Guia de Projetos |
| ⚙️ **Administração** *(somente Super Administrador)* | Organizações · Usuários · Perfis de Acesso · Graus de Satisfação · Configurações · Auditoria |

> **Nota:** os itens do grupo **Administração** são visíveis e acessíveis **somente** pelo perfil Super Administrador. Usuários com outros perfis não enxergam esse grupo no menu.

---

## 4. Dashboard

O Dashboard é a **visão executiva centralizada** do planejamento. Exibe em um só lugar o que está indo bem, o que requer atenção e onde estão as entregas atrasadas. É a primeira tela após o login.

**Como acessar:** menu lateral → **Dashboard**, ou `[endereço do sistema]/dashboard`.

> 📸 **[Figura 8 — Dashboard — Parte Superior (Módulos + Agenda 2030 + Mentor)]**
> *Como capturar: com Organização e PEI selecionados, acesse o Dashboard; capture do topo até o Mentor Estratégico.*

> 📸 **[Figura 9 — Dashboard — Cards de Métricas e Gráficos]**
> *Como capturar: role a página até os 4 cards de KPI e os três gráficos.*

> 📸 **[Figura 10 — Dashboard — Performance BSC e Indicadores em Alerta]**
> *Como capturar: role até o gráfico "Performance por Perspectiva" e o painel "Indicadores em Alerta".*

> 📸 **[Figura 11 — Dashboard — RAE e Alertas de Prazo]**
> *Como capturar: role até o resumo da última RAE e a seção de alertas de prazo (aparece somente se houver entregas vencidas ou a vencer em 7 dias).*

**Elementos da tela e o que significam:**

| Elemento | O que é | Para que serve |
|---|---|---|
| **Portal de Módulos GPPEI** | 3 cards (01/02/03) com atalhos rápidos | Navegar direto para o início de cada módulo |
| **Widget Agenda 2030** | Cobertura X de 17 ODS + botão "Abrir Painel" | Ver rapidamente quantos ODS o PEI atinge |
| **Mentor Estratégico** | Trilha de 5 passos (Identidade → Mapa → Objetivos → Indicadores → Planos) com % de conclusão | Guia visual do progresso do ciclo PEI; recolhe ao atingir 100% |
| **4 Cards de Métricas** | Execução no Exercício · Saúde da Estratégia · Riscos Críticos · Indicadores Monitorados | Pulso rápido do estado atual |
| **Curva de Evolução** | Gráfico de linha com média mensal de atingimento das metas | Ver tendência ao longo do ano |
| **Status dos Planos** | Gráfico de rosca com contagem por status | Ver quantos planos estão em execução, concluídos etc. |
| **Riscos por Severidade** | Gráfico de rosca com contagem por nível | Ver distribuição dos riscos (Baixo/Médio/Alto/Crítico) |
| **Performance por Perspectiva** | Barras horizontais com % de atingimento por perspectiva BSC | Identificar qual perspectiva está mais atrasada |
| **Indicadores em Alerta** | Os 3 KPIs com menor atingimento | Priorizar onde agir |
| **RAE** | Resumo da última Revisão da Estratégia | Acompanhar as decisões da última reunião de revisão |
| **Alertas de Prazos** | Lista de entregas vencidas ou com prazo nos próximos 7 dias | Evitar atrasos |

**Atualização automática:** o Dashboard recarrega os dados a cada 30 segundos quando a tela está aberta, sem necessidade de atualizar o navegador.

**Análise por Inteligência Artificial:** quando a IA está habilitada pelo administrador (seção 10.5), o botão **Gerar Análise AI** no cabeçalho produz um resumo executivo com base nos indicadores, planos e riscos do contexto selecionado.

> 📸 **[Figura 12 — Dashboard — Insight Estratégico gerado pela IA]**
> *Como capturar: com IA habilitada e API Key válida configurada, clique em "Gerar Análise AI" e capture o painel dourado com o texto gerado.*

---

## 5. Módulo 01 — Inaugurar e Integrar

> **O que é este módulo?** É o ponto de partida formal do ciclo PEI. Aqui a organização registra *como* vai conduzir o planejamento, com quem, vinculado a quais instrumentos de governo, com quais compromissos com a Agenda 2030 e seguindo qual calendário de eventos. Sem este módulo preenchido, o Mentor Estratégico no Dashboard indicará progresso zero.

### 5.1 Inaugurar e Integrar

Tela principal do Módulo 01, organizada em quatro abas.

**Como acessar:** menu lateral → **Inaugurar e Integrar**, ou `[endereço do sistema]/pei/inaugurar`.  
**Pré-requisito:** ter um ciclo PEI criado e selecionado no seletor global.

> 📸 **[Figura 13 — Tela Inaugurar e Integrar — Visão Geral das 4 Abas]**
> *Como capturar: acesse `[endereço do sistema]/pei/inaugurar` com PEI ativo selecionado.*

---

#### 5.1.1 Aba: Planejar o Processo

**O que é:** aqui se documenta a *organização interna* do próprio processo de planejamento — quem faz parte da equipe, quais são as orientações da alta direção, qual metodologia será adotada e em qual prazo.

> 📸 **[Figura 14 — Aba "Planejar o Processo" (antes de preencher)]**
> *Como capturar: clique na aba com o PEI ainda sem dados nela.*

**Passo 1:** Clique em **Preencher** (primeira vez) ou **Editar** (para atualizar). O modal abre.

> 📸 **[Figura 15 — Modal "Planejar o Planejamento"]**
> *Como capturar: clique em "Preencher".*

**Passo 2:** Preencha os campos:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Equipe de Planejamento | Sim | Nomes, cargos e responsabilidades dos membros |
| Diretrizes da Alta Direção | Não | Orientações estratégicas recebidas da liderança |
| Metodologia a Adotar | Não | Ex.: "GPPEI/MGI 2025, BSC" |
| Data de Início | Não | Quando o processo começou |
| Data Fim Prevista | Não | Prazo previsto para conclusão do planejamento |
| Aprovado pela Alta Direção | Não | Marque quando houver aprovação formal |
| Observações | Não | Notas adicionais |

**Passo 3:** Clique em **Salvar**. A aba passa a exibir os dados em modo de leitura (cards).

> 📸 **[Figura 16 — Aba "Planejar o Processo" (após preencher)]**
> *Como capturar: após salvar, capture a aba em modo leitura com o indicador de aprovação visível.*

---

#### 5.1.2 Aba: Integração com Instrumentos

**O que é:** registra como o PEI se conecta a outros instrumentos de planejamento do governo (PPA — Plano Plurianual; LOA — Lei Orçamentária Anual; contratos de gestão etc.). Isso garante que a estratégia esteja alinhada com as obrigações legais e orçamentárias.

> 📸 **[Figura 17 — Aba "Integração com Instrumentos" (com registros)]**
> *Como capturar: clique na aba com ao menos um instrumento cadastrado.*

**Passo 1:** Clique em **Adicionar**.

> 📸 **[Figura 18 — Modal "Nova Integração com Instrumento"]**
> *Como capturar: clique em "Adicionar".*

**Passo 2:** Preencha os campos:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Instrumento | Sim | Nome do instrumento (ex.: "PPA 2024-2027") |
| Tipo | Não | Categoria (ex.: Orçamentário, Normativo) |
| Intensidade | Não | Alta / Média / Baixa |
| Pontos de Atenção | Não | Observações sobre a integração |
| Tarefas de Integração | Não | O que precisa ser feito para garantir o alinhamento |

**Passo 3:** Clique em **Salvar**. Na tabela resultante, use ✏️ para editar e 🗑️ para excluir.

---

#### 5.1.3 Aba: Agenda 2030 (ODS)

**O que é:** a Agenda 2030 da ONU tem 17 **Objetivos de Desenvolvimento Sustentável (ODS)** que orientam o desenvolvimento global. Aqui a organização declara quais ODS o seu PEI contribui para atingir, com qual intensidade e de que forma. Isso é uma exigência de transparência e alinhamento para órgãos federais.

> 📸 **[Figura 19 — Aba "Agenda 2030 (ODS)" com ODS selecionados]**
> *Como capturar: selecione ao menos 3 ODS (ficam realçados em verde) e capture antes de salvar, com a seção de detalhamento visível.*

**Passo 1:** Na grade dos 17 ODS, clique sobre os ícones que o PEI contribui. O ícone muda para verde quando selecionado.  
**Passo 2:** Para cada ODS selecionado, preencha (opcionalmente) **Contribuição** (como o PEI contribui para aquele ODS) e **Intensidade** (Alta/Média/Baixa).  
**Passo 3:** Clique em **Salvar Aderência**.

> **Nota:** a vinculação de ODS *diretamente a objetivos estratégicos específicos* é feita ao criar/editar os objetivos (seção 6.7). Aqui declara-se a aderência *do PEI como um todo*.

---

#### 5.1.4 Aba: Calendário de Eventos

**O que é:** um calendário estruturado das reuniões e eventos relacionados ao ciclo PEI (workshops de planejamento, RAEs, apresentações à alta direção etc.). Serve como referência histórica do processo e para organizar a agenda.

> 📸 **[Figura 20 — Aba "Calendário de Eventos" com eventos listados]**
> *Como capturar: clique na aba com ao menos dois eventos, um deles marcado como realizado.*

**Passo 1:** Clique em **Novo Evento**.

> 📸 **[Figura 21 — Modal "Novo Evento do Calendário"]**
> *Como capturar: clique em "Novo Evento".*

**Passo 2:** Preencha os campos:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Título | Sim | Nome do evento (ex.: "1ª RAE 2025") |
| Data | Sim | Data de realização |
| Tipo de Evento | Não | Ex.: Reunião de Planejamento, RAE, Workshop |
| Objetivo | Não | O que se pretende com o evento |
| Participantes | Não | Quem participará |
| Evento já realizado | Não | Marque para sinalizar que ocorreu |

**Passo 3:** Clique em **Salvar**.

---

### 5.2 Ciclos PEI

**O que é:** o Ciclo PEI é o **contêiner principal** de todo o planejamento estratégico. Define o período de vigência (ex.: 2024–2028) e ancora perspectivas, objetivos, indicadores e planos. Toda organização precisa ter ao menos um ciclo PEI criado antes de usar qualquer outra funcionalidade de planejamento.

**Como acessar:** menu lateral → **Ciclos PEI**, ou `[endereço do sistema]/pei/ciclos`.

> 📸 **[Figura 22 — Listagem de Ciclos PEI]**
> *Como capturar: acesse `[endereço do sistema]/pei/ciclos` com ao menos dois PEIs em status diferentes.*

A tabela exibe: **Descrição**, **Período** (com duração calculada em anos), **Qtd. de Perspectivas**, **Status** (Vigente / Futuro / Encerrado) e botões de ação. Há busca por descrição e filtro de status.

#### 5.2.1 Criar um Novo Ciclo PEI

**Passo 1:** Clique em **Novo PEI**.

> 📸 **[Figura 23 — Modal "Novo Ciclo PEI"]**
> *Como capturar: clique em "Novo PEI" e preencha os campos para mostrar o cálculo de duração.*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição do PEI | Sim | Nome identificador (ex.: "PEI MIDR 2024-2028") |
| Ano de Início | Sim | Ex.: 2024 |
| Ano de Término | Sim | Ex.: 2028 — a duração (5 anos) é calculada automaticamente |

**Passo 3:** Clique em **Salvar Ciclo PEI**.

#### 5.2.2 Detalhar, Editar e Excluir

- **👁️ Detalhar:** abre a página de detalhe do PEI, com resumo de perspectivas e dados vinculados.
- **✏️ Editar:** abre o modal para alterar descrição e anos.
- **🗑️ Excluir:** exibe um aviso com o impacto da exclusão (perspectivas, objetivos, indicadores e planos que serão removidos).

> 📸 **[Figura 24 — Modal de Confirmação de Exclusão de PEI (com aviso de impacto)]**
> *Como capturar: clique no 🗑️ de um PEI com dados vinculados; capture o aviso antes de confirmar.*

> ⚠️ **A exclusão de um Ciclo PEI é irreversível e remove em cascata todos os dados vinculados (perspectivas, objetivos, indicadores e planos). Faça isso somente se tiver certeza.**

---

### 5.3 Cadeia de Valor

**O que é:** a Cadeia de Valor mapeia as principais atividades da organização que geram valor público, organizadas em **atividades finalísticas** (entregam diretamente ao cidadão) e **atividades de suporte** (sustentam as finalísticas internamente). Cada atividade é detalhada por processos no modelo Entrada → Transformação → Saída.

**Como acessar:** menu lateral → **Cadeia de Valor**, ou `[endereço do sistema]/pei/cadeia-valor`.

> 📸 **[Figura 25 — Cadeia de Valor — Visão Geral (dois blocos)]**
> *Como capturar: acesse `[endereço do sistema]/pei/cadeia-valor` com atividades cadastradas nos dois blocos (Finalísticas e Suporte).*

A tela exibe dois blocos lado a lado. Cada card de atividade mostra: nome, perspectiva BSC vinculada (se houver) e a lista de processos com a descrição resumida da transformação. Botões no cabeçalho: **Nova Atividade** e **PDF** (exporta a cadeia para PDF).

#### 5.3.1 Cadastrar uma Atividade

**Passo 1:** Clique em **Nova Atividade**.

> 📸 **[Figura 26 — Modal "Nova Atividade da Cadeia de Valor"]**
> *Como capturar: clique em "Nova Atividade".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição | Sim | Nome da atividade (ex.: "Concessão de Financiamento Habitacional") |
| Tipo | Sim | Finalística (entrega ao cidadão) ou Suporte (apoia internamente) |
| Perspectiva BSC | Não | Perspectiva estratégica relacionada a esta atividade |
| Ordem | Não | Posição de exibição dentro do bloco |

**Passo 3:** Clique em **Salvar**.

> **Nota:** a Perspectiva BSC é opcional. A atividade pode ser salva sem ela.

#### 5.3.2 Cadastrar Processos de uma Atividade

Cada atividade pode ter um ou mais processos que detalham como ela funciona.

**Passo 1:** No card da atividade, clique em **+ Processo**.

> 📸 **[Figura 27 — Modal "Novo Processo (Entrada → Transformação → Saída)"]**
> *Como capturar: no card de uma atividade, clique em "+ Processo".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Entradas | Não | Recursos, informações ou insumos que entram no processo |
| Transformação/Processo | Sim | O que é feito (a atividade em si) |
| Saídas | Não | Produto, serviço ou resultado gerado |

**Passo 3:** Clique em **Salvar**.

Para **editar** um processo existente, clique no ✏️ ao lado dele — o modal reabre com os três campos preenchidos para alteração.

> 📸 **[Figura 28 — Modal de Edição de Processo]**
> *Como capturar: clique no ✏️ de um processo existente para abrir em modo edição.*

---

## 6. Módulo 02 — Planejar

> **O que é este módulo?** É onde a estratégia é de fato construída. A sequência recomendada dentro deste módulo é: primeiro a identidade (missão/visão), depois os valores, depois a análise de ambiente (SWOT/PESTEL), depois as perspectivas, depois os objetivos (com ODS), depois os indicadores, depois os planos e entregas. O Mapa Estratégico é o resultado visual de todo esse trabalho.

### 6.1 Identidade Estratégica

**O que é:** define a **Missão** (propósito da organização) e a **Visão** (estado futuro almejado) para o ciclo PEI. São as âncoras de toda a estratégia — cada objetivo, indicador e plano deve contribuir para realizar a visão.

**Como acessar:** menu lateral → **Identidade Estratégica**, ou `[endereço do sistema]/pei`.

> 📸 **[Figura 29 — Identidade Estratégica — Modo Leitura (Missão e Visão preenchidas)]**
> *Como capturar: acesse `[endereço do sistema]/pei` com Missão e Visão preenchidas; capture o card com o botão "Exportar PDF" visível.*

**Passo 1:** Clique em **Editar Identidade**.

> 📸 **[Figura 30 — Identidade Estratégica — Modo Edição (campos abertos)]**
> *Como capturar: clique em "Editar Identidade" para mostrar os campos de texto editáveis.*

**Passo 2:** Preencha ou atualize os campos **Missão** e **Visão**.  
**Passo 3:** Clique em **Salvar Identidade** (ou **Cancelar** para descartar as alterações).

O card **Valores Organizacionais** ao lado exibe os valores cadastrados e tem o botão **Gerenciar →**, que leva diretamente à tela de Valores (seção 6.2). Há também o botão **Exportar PDF** para gerar um documento com a identidade.

---

### 6.2 Valores Institucionais

**O que é:** os valores são os princípios e crenças que orientam o comportamento da organização no dia a dia e na tomada de decisões. São componentes da identidade estratégica e aparecem no Mapa Estratégico.

**Como acessar:** menu lateral → **Valores Institucionais**, ou `[endereço do sistema]/pei/valores`.  
**Pré-requisito:** PEI ativo e Organização selecionada.

> 📸 **[Figura 31 — Listagem de Valores Institucionais (cards)]**
> *Como capturar: acesse `[endereço do sistema]/pei/valores` com ao menos 3 valores cadastrados.*

**Passo 1:** Clique em **Novo Valor**.

> 📸 **[Figura 32 — Modal "Novo Valor Institucional"]**
> *Como capturar: clique em "Novo Valor".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome do Valor | Sim | Ex.: "Integridade", "Inovação", "Transparência" |
| Descrição | Não | Como este valor se manifesta na prática |

**Passo 3:** Clique em **Salvar**. Para editar ou excluir um valor, use o menu (⋮) no canto de cada card.

---

### 6.3 Temas Norteadores

**O que é:** os temas norteadores são eixos estratégicos transversais que estão acima das perspectivas BSC. Enquanto perspectivas organizam *em qual dimensão* a organização atua, os temas norteadores expressam *quais grandes frentes temáticas* devem permear toda a estratégia (ex.: "Transição Digital", "Sustentabilidade", "Equidade").

**Como acessar:** menu lateral → **Temas Norteadores**, ou `[endereço do sistema]/temas-norteadores`.  
**Pré-requisito:** PEI ativo e Organização selecionada.

> 📸 **[Figura 33 — Listagem de Temas Norteadores]**
> *Como capturar: acesse `[endereço do sistema]/temas-norteadores` com ao menos dois temas cadastrados.*

Quando a IA está habilitada (seção 10.5), o botão **Sugerir Temas com IA** gera sugestões com base no contexto da organização.

> 📸 **[Figura 34 — Sugestões de Temas geradas pela IA]**
> *Como capturar: clique em "Sugerir Temas com IA" e capture o painel com as sugestões.*

**Passo 1:** Clique em **Novo Tema**.

> 📸 **[Figura 35 — Modal "Novo Tema Norteador"]**
> *Como capturar: clique em "Novo Tema".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição do Tema | Sim | Nome/descrição do eixo temático |
| Unidade Organizacional | Sim | A que unidade este tema pertence |

**Passo 3:** Clique em **Salvar Tema**. Cada tema pode ser editado (✏️) ou excluído (🗑️) pela tabela.

---

### 6.4 Análise SWOT

**O que é:** a SWOT mapeia quatro categorias que influenciam a estratégia da organização:
- **S — Forças (Strengths):** o que a organização faz bem *internamente* (recursos, capacidades, reputação).
- **W — Fraquezas (Weaknesses):** limitações *internas* que prejudicam o desempenho.
- **O — Oportunidades (Opportunities):** fatores *externos favoráveis* que a organização pode aproveitar.
- **T — Ameaças (Threats):** fatores *externos desfavoráveis* que podem prejudicar.

O sistema inclui a priorização **GUT** (Gravidade × Urgência × Tendência) para ordenar os itens por criticidade, e o gerenciamento de **Partes Interessadas** (stakeholders) e **Cenários Prospectivos**.

**Como acessar:** menu lateral → **Análise SWOT**, ou `[endereço do sistema]/pei/swot`.

> 📸 **[Figura 36 — Análise SWOT — Modo Edição (4 quadrantes com itens)]**
> *Como capturar: acesse `[endereço do sistema]/pei/swot` com ao menos um item em cada quadrante.*

Botões no cabeçalho: **Sugerir com IA**, **Imprimir** (PDF) e alternar **Modo Apresentação / Modo Edição**.

> 📸 **[Figura 37 — Análise SWOT — Modo Apresentação (visual ampliado)]**
> *Como capturar: clique em "Modo Apresentação".*

#### 6.4.1 Cadastrar um Item SWOT

**Passo 1:** No quadrante desejado (Força/Fraqueza/Oportunidade/Ameaça), clique em **+ Novo Item**.

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição | Sim | Descreva a força, fraqueza, oportunidade ou ameaça |
| Impacto | Sim | Nota de 1 (baixo) a 5 (muito alto) |
| Gravidade (G) | Não | Quão grave é o problema/oportunidade se não tratado (1–5) |
| Urgência (U) | Não | Com que urgência precisa ser tratado (1–5) |
| Tendência (T) | Não | A tendência é piorar ou melhorar se não agir (1–5) |

**Passo 3:** Clique em **Salvar**. O **Score GUT** (G × U × T, máximo 125) é calculado automaticamente e ordena os itens do mais crítico ao menos crítico.

#### 6.4.2 Aba: Partes Interessadas

> 📸 **[Figura 38 — SWOT — Aba Partes Interessadas]**
> *Como capturar: na tela de SWOT, clique na aba "Partes Interessadas".*

**Partes Interessadas (stakeholders)** são pessoas, grupos ou organizações que têm interesse ou são afetados pelo planejamento estratégico.

**Cadastrar:** clique em **Nova Parte** e preencha: **Nome**, **Tipo** (interno/externo), **Nível de Interesse** (1–5), **Nível de Influência** (1–5) e **Estratégia de Engajamento**. Clique em **Salvar**.

#### 6.4.3 Aba: Cenários Prospectivos

> 📸 **[Figura 39 — SWOT — Aba Cenários Prospectivos]**
> *Como capturar: clique na aba "Cenários".*

**Cenários Prospectivos** são narrativas de como o futuro pode se apresentar para a organização, usadas para testar a robustez da estratégia. São de três tipos: **Otimista** (tudo corre bem), **Tendencial** (segue a tendência atual) e **Pessimista** (tudo corre mal).

**Cadastrar:** clique em **Novo Cenário** e preencha: **Tipo**, **Descrição**, **Implicações para a organização**, **Resposta Estratégica**, **Probabilidade** e **Impacto**. Clique em **Salvar**.

---

### 6.5 Análise PESTEL

**O que é:** a PESTEL aprofunda a análise do ambiente **externo** (as oportunidades e ameaças do SWOT) em seis dimensões específicas. Cada letra representa uma dimensão:

| Letra | Dimensão | O que analisa |
|---|---|---|
| **P** | Política | Estabilidade política, legislação, políticas de governo |
| **E** | Econômica | Crescimento econômico, juros, câmbio, orçamento |
| **S** | Social | Mudanças demográficas, comportamento social, desigualdade |
| **T** | Tecnológica | Inovação, digitalização, automação |
| **E** | Ambiental (Environmental) | Mudanças climáticas, regulação ambiental, recursos naturais |
| **L** | Legal | Legislação, regulações, normas, LGPD |

**Como acessar:** menu lateral → **Análise PESTEL**, ou `[endereço do sistema]/pei/pestel`.

> 📸 **[Figura 40 — Análise PESTEL — Seis Dimensões com Fatores Cadastrados]**
> *Como capturar: acesse `[endereço do sistema]/pei/pestel` com fatores em pelo menos três dimensões.*

Botões no cabeçalho: **Sugerir com IA** e **Imprimir** (PDF).

**Passo 1:** Na dimensão desejada, clique em **+ Novo Item**.  
**Passo 2:** Preencha **Descrição** *(obrigatório)*, **Impacto** (1 a 5) e **Observações** (opcional).  
**Passo 3:** Clique em **Salvar**. Para editar ou excluir, use os ícones ✏️ e 🗑️ ao lado de cada item.

---

### 6.6 Perspectivas BSC

**O que é:** as perspectivas são as **dimensões de análise** do BSC. Elas estruturam os objetivos estratégicos em grupos lógicos e interdependentes. Cada objetivo pertence a uma perspectiva. O atingimento de uma perspectiva é calculado pela média ponderada dos indicadores e planos dos objetivos nela contidos.

> **Atenção:** é preciso criar as perspectivas *antes* de cadastrar objetivos. Pelo menos 2–4 perspectivas são recomendadas.

**Como acessar:** menu lateral → **Perspectivas BSC**, ou `[endereço do sistema]/pei/perspectivas`.  
**Pré-requisito:** ciclo PEI ativo selecionado.

> 📸 **[Figura 41 — Listagem de Perspectivas BSC]**
> *Como capturar: acesse `[endereço do sistema]/pei/perspectivas` com ao menos 4 perspectivas cadastradas.*

**Passo 1:** Clique em **Nova Perspectiva**.

> 📸 **[Figura 42 — Modal "Nova Perspectiva BSC"]**
> *Como capturar: clique em "Nova Perspectiva" e preencha os pesos para visualizar a soma.*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome da Perspectiva | Sim | Ex.: "Cidadão e Sociedade", "Processos Internos" |
| Descrição | Não | Em que foco estratégico esta perspectiva se concentra |
| Cor | Não | Cor de identificação visual no Mapa Estratégico |
| Peso dos Indicadores | Sim | % de contribuição dos indicadores no atingimento total |
| Peso dos Planos | Sim | % de contribuição dos planos no atingimento total |
| Nível/Ordem | Sim | Posição de exibição no Mapa Estratégico |

> **Regra dos Pesos:** Peso de Indicadores + Peso de Planos deve ser igual a **100%**. Ex.: 60% indicadores + 40% planos = 100%.

**Passo 3:** Clique em **Salvar**.

Para editar ou excluir uma perspectiva, clique nos ícones ✏️ e 🗑️ na listagem. A exclusão remove também todos os objetivos e dados vinculados à perspectiva.

---

### 6.7 Objetivos BSC

**O que é:** os objetivos estratégicos são as **metas de médio e longo prazo** da organização, traduzindo a visão em algo mensurável. Cada objetivo pertence a uma perspectiva e será medido por indicadores e operacionalizado por planos de ação.

**Como acessar:** menu lateral → **Objetivos BSC**, ou `[endereço do sistema]/objetivos`.  
**Pré-requisito:** ao menos uma perspectiva BSC cadastrada.

> 📸 **[Figura 43 — Listagem de Objetivos BSC agrupados por Perspectiva]**
> *Como capturar: acesse `[endereço do sistema]/objetivos` com objetivos cadastrados e indicadores com evolução (farois coloridos visíveis).*

#### 6.7.1 Criar um Objetivo

**Passo 1:** Clique em **Novo Objetivo**.

> 📸 **[Figura 44 — Modal "Novo Objetivo BSC"]**
> *Como capturar: clique em "Novo Objetivo".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome do Objetivo | Sim | Ex.: "Ampliar a cobertura de atendimento habitacional" |
| Descrição | Não | Detalhamento do que se quer alcançar |
| Perspectiva | Sim | Em qual perspectiva BSC este objetivo se encaixa |
| Nível/Ordem | Sim | Posição na listagem e no Mapa Estratégico |
| ODS Vinculados | Não | Quais ODS este objetivo contribui para atingir |

**Passo 3:** Clique em **Salvar**. Para editar (✏️) ou excluir (🗑️), use os ícones na listagem.

#### 6.7.2 Página de Detalhe do Objetivo

Clique sobre o nome de qualquer objetivo para abrir sua página de detalhe completo.

> 📸 **[Figura 45 — Detalhe do Objetivo BSC (cards de estatísticas + indicadores + planos)]**
> *Como capturar: na listagem, clique sobre um objetivo que já tenha indicadores e planos vinculados.*

A página de detalhe mostra:

| Seção | O que exibe |
|---|---|
| 4 cards de métricas | % de Atingimento geral · Qtd. de KPIs · Qtd. de Planos · Qtd. de Riscos vinculados |
| Futuro Almejado | Narrativa descritiva do estado futuro + botão **Gerenciar** |
| Indicadores vinculados | Tabela com farol, meta, realizado e % de atingimento de cada KPI |
| Planos vinculados | Tabela com status, prazo e % de execução de cada plano |
| Comentários | Seção colaborativa para registro de observações da equipe |

#### 6.7.3 Gerenciar o Futuro Almejado

**O que é:** o Futuro Almejado é uma narrativa em texto que descreve o estado concreto que a organização quer alcançar ao cumprir aquele objetivo. É diferente do objetivo (que é a meta) — é a *descrição vívida* do como será quando o objetivo estiver atingido.

**Passo 1:** No detalhe do objetivo, clique em **Gerenciar** ao lado de "Futuro Almejado".

> 📸 **[Figura 46 — Futuro Almejado — Listagem de Itens]**
> *Como capturar: clique em "Gerenciar" com ao menos dois itens cadastrados.*

**Passo 2:** Clique em **Adicionar Item**.

> 📸 **[Figura 47 — Modal "Novo Futuro Almejado"]**
> *Como capturar: clique em "Adicionar Item".*

**Passo 3:** Escreva a **Descrição** do estado futuro desejado e clique em **Salvar**. O botão **Voltar ao Objetivo** retorna à página de detalhe.

#### 6.7.4 Comentários no Objetivo

Na página de detalhe do objetivo, role até **Colaboração e Comentários**.

**Passo 1:** Digite o comentário no campo de texto (suporta `@menção` de outros usuários).  
**Passo 2:** Clique em **Comentar**.

> 📸 **[Figura 48 — Detalhe do Objetivo — Seção de Comentários]**
> *Como capturar: role até "Colaboração e Comentários" com ao menos um comentário postado.*

Para excluir um comentário próprio, clique no ícone de lixeira ao lado dele.

---

### 6.8 Agenda 2030 (ODS) — Painel de Monitoramento

**O que é:** painel que consolida a contribuição de todos os objetivos estratégicos aos 17 Objetivos de Desenvolvimento Sustentável da ONU. Diferente da aba na seção 5.1.3 (que declara a aderência institucional do PEI como um todo), este painel mostra *quais objetivos específicos contribuem para cada ODS*.

**Como acessar:** menu lateral → **Agenda 2030 (ODS)**, ou `[endereço do sistema]/agenda2030`.

> 📸 **[Figura 49 — Painel Agenda 2030 — KPIs e Grade dos 17 ODS]**
> *Como capturar: acesse `[endereço do sistema]/agenda2030` com ao menos 3 objetivos vinculados a ODS diferentes.*

A tela exibe 3 cards de resumo:
- **Cobertura da Agenda:** X de 17 ODS cobertos (ex.: 11/17)
- **Objetivos Vinculados:** quantos objetivos estratégicos têm pelo menos 1 ODS associado
- **ODS Não Cobertos:** os ODS ainda sem vínculo

E a grade visual dos 17 ODS: coloridos = cobertos por algum objetivo; cinza = sem vínculo.

> 📸 **[Figura 50 — Painel ODS — Objetivos vinculados a um ODS específico]**
> *Como capturar: clique sobre o ícone de um ODS coberto (colorido) para expandir a lista de objetivos vinculados.*

> **Como vincular ODS a objetivos:** ao criar ou editar um objetivo (seção 6.7.1), há o campo **ODS Vinculados** onde você seleciona os ODS que aquele objetivo contribui. O painel da Agenda 2030 reflete esses vínculos automaticamente.

---

### 6.9 Indicadores de Desempenho (KPIs)

**O que é:** indicadores são as **métricas que provam** se os objetivos estão sendo alcançados. Cada indicador tem uma meta anual e recebe medições periódicas (evoluções) que geram o farol de desempenho. Sem indicadores medidos, o Dashboard e o Mapa Estratégico ficam sem dados.

**Como acessar:** menu lateral → **Indicadores**, ou `[endereço do sistema]/indicadores`.  
**Pré-requisito:** ao menos um objetivo BSC cadastrado.

> 📸 **[Figura 51 — Listagem de Indicadores (com farois coloridos)]**
> *Como capturar: acesse `[endereço do sistema]/indicadores` com indicadores que já tenham metas e evoluções lançadas.*

A listagem tem: **busca** por nome, **filtro** por objetivo e por tipo de vínculo. Botões: **Exportar PDF**, **Exportar Excel** e **Novo Indicador**.

#### 6.9.1 Criar um Indicador

**Passo 1:** Clique em **Novo Indicador**.

> 📸 **[Figura 52 — Modal "Novo Indicador" — Aba Principal]**
> *Como capturar: clique em "Novo Indicador".*

**Passo 2 — Aba Principal:** preencha os campos básicos:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome do Indicador | Sim | Nome mensurável e objetivo (ex.: "% de municípios atendidos") |
| Descrição | Sim | O que este indicador mede exatamente |
| Objetivo Vinculado | Sim | A qual objetivo estratégico este KPI pertence |
| Unidade de Medida | Sim | Ex.: %, R$, nº, dias, toneladas |
| Polaridade | Sim | **Positiva:** quanto maior, melhor. **Negativa:** quanto menor, melhor. **Não Aplicável** |
| Período de Medição | Sim | Mensal / Bimestral / Trimestral / Semestral / Anual |
| Tipo de Cálculo | Sim | **Manual:** o gestor lança os valores. **Automático:** calculado pelo % de entregas concluídas nos planos vinculados |
| Peso | Não | Contribuição percentual deste KPI no atingimento da perspectiva |
| Acumulado? | Não | Se os valores se acumulam ao longo do ano |
| Fórmula de Cálculo | Não | Como o valor é calculado (para Manual) |
| Fonte de Dados | Não | De onde os dados vêm |
| Atributos Adicionais | Não | Informações complementares |

**Passo 3 — Aba Metas Anuais:** informe a meta prevista para cada ano do PEI e, opcionalmente, o valor da linha de base (situação de partida antes do PEI).

> 📸 **[Figura 53 — Modal Indicador — Aba Metas Anuais]**
> *Como capturar: no modal, abra a aba "Metas Anuais" com ao menos dois anos preenchidos.*

**Passo 4 — Aba SMART:** documente as cinco dimensões para validar a qualidade do indicador.

> 📸 **[Figura 54 — Modal Indicador — Aba SMART]**
> *Como capturar: no modal, abra a aba "SMART" e marque as cinco dimensões.*

**Passo 5:** Clique em **Salvar**.

#### 6.9.2 Registrar a Evolução (Resultado Medido)

**O que é:** a evolução é o **lançamento do resultado real** do indicador em um período. É aqui que o sistema aprende se o objetivo está sendo atingido ou não, e atualiza os farois e o Dashboard.

**Como acessar:** na listagem, clique no ícone 📈 ao lado do indicador, ou acesse diretamente `[endereço do sistema]/indicadores/{id}/evolucao`.

> 📸 **[Figura 55 — Tela de Lançamento de Evolução]**
> *Como capturar: clique no 📈 de um indicador com meta definida.*

**Passo 1:** Selecione o **Mês** e o **Ano** de referência.  
**Passo 2:** Informe o **Valor Previsto** (meta do período) e o **Valor Realizado** (resultado efetivo medido).  
**Passo 3:** Preencha a **Análise do Desempenho** — explique por que o resultado ficou acima, dentro ou abaixo da meta.  
**Passo 4:** Opcionalmente, faça upload de **evidências** (documentos que comprovam o resultado).  
**Passo 5:** Clique em **Salvar**.

> **Indicadores Automáticos:** quando o Tipo de Cálculo for "Automático", o sistema calcula o valor realizadoautomaticamente com base no percentual de entregas concluídas nos planos vinculados. Não é necessário lançamento manual para estes.

#### 6.9.3 Página de Detalhe do Indicador

Clique no nome de um indicador na listagem para ver seu histórico completo.

> 📸 **[Figura 56 — Detalhe do Indicador — Gráfico de Evolução]**
> *Como capturar: acesse o detalhe de um indicador com ao menos 3 meses de evolução lançados.*

A página mostra: cards com atingimento atual e meta do ano, gráfico de linha com a evolução histórica, tabela de todas as medições e o botão de acesso à tela de lançamento.

---

### 6.10 Planos de Ação

**O que é:** um plano de ação é uma **iniciativa ou projeto concreto** que a organização vai executar para atingir um objetivo estratégico. É no plano que se define o *que será feito, por quem, em quanto tempo e com qual orçamento*. Cada plano tem um portfólio de entregas (as tarefas e produtos) que compõem sua execução.

**Como acessar:** menu lateral → **Planos de Ação**, ou `[endereço do sistema]/planos`.  
**Pré-requisito:** ao menos um objetivo BSC cadastrado.

> 📸 **[Figura 57 — Listagem de Planos de Ação]**
> *Como capturar: acesse `[endereço do sistema]/planos` com planos em status variados (Em Planejamento, Em Execução, Concluído).*

A listagem tem busca por nome, filtros de status e organizaçã, e botões **Exportar PDF**, **Exportar Excel** e **Novo Plano**.

#### 6.10.1 Criar um Plano de Ação

**Passo 1:** Clique em **Novo Plano**.

> 📸 **[Figura 58 — Modal "Novo Plano de Ação"]**
> *Como capturar: clique em "Novo Plano".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome do Plano | Sim | Nome da iniciativa ou projeto |
| Objetivo Vinculado | Sim | A qual objetivo BSC este plano contribui |
| Tipo de Execução | Sim | Projeto / Programa / Processo |
| Organização | Sim | Unidade responsável pela execução |
| Data de Início | Sim | Data de início prevista |
| Data de Término | Sim | Data de conclusão prevista (deve estar dentro do ciclo PEI) |
| Status | Sim | Em Planejamento / Em Execução / Concluído / Suspenso / Cancelado |
| Orçamento Previsto | Não | Valor total em R$ |
| Código PPA / LOA | Não | Referências ao instrumento orçamentário |
| Detalhamento | Não | Escopo, justificativa e objetivo específico do plano |

**Passo 3:** Clique em **Salvar**.

#### 6.10.2 Ficha Técnica do Plano

Clique no nome de qualquer plano na listagem para abrir sua Ficha Técnica completa.

> 📸 **[Figura 59 — Ficha Técnica do Plano (informações + barra de execução + botões)]**
> *Como capturar: clique sobre o nome de um plano com entregas cadastradas; a barra de execução fica colorida.*

A Ficha Técnica exibe:
- Todas as informações gerais do plano
- Uma **barra de progresso** calculada automaticamente pelo percentual de entregas concluídas (ponderada pelos pesos das entregas, quando definidos)
- Botão **Entregas** — abre o quadro de gerenciamento de entregas (seção 6.11)
- Botão **Gestores** — abre a tela de atribuição de responsáveis e RACI (seção 6.10.3)

#### 6.10.3 Gestores e Responsáveis (RACI e Comunicação)

**O que é:** esta tela reúne três estruturas de governança do plano: responsáveis formais (gestor responsável e substituto), matriz RACI e plano de comunicação.

**Como acessar:** na Ficha Técnica do plano, clique em **Gestores**, ou acesse `[endereço do sistema]/planos/{id}/responsaveis`.

> 📸 **[Figura 60 — Gestores e Responsáveis — Tabela de Atribuições]**
> *Como capturar: acesse com ao menos um Gestor Responsável e um Substituto atribuídos.*

**Atribuir um Gestor:**  
Selecione o **Usuário** e o **Perfil de Gestão** (Gestor Responsável ou Gestor Substituto) → clique em **Atribuir ao Plano**.

> **Importante:** somente usuários com o perfil Gestor Responsável ou Gestor Substituto atribuídos a este plano terão permissão para editar suas entregas e dados.

**Matriz RACI:**  
RACI define papéis em cada entrega ou no plano como um todo:
- **R (Responsável):** quem executa
- **A (Aprovador):** quem aprova e responde pelo resultado
- **C (Consultado):** quem deve ser consultado antes de agir
- **I (Informado):** quem deve ser informado sobre o progresso

> 📸 **[Figura 61 — Matriz RACI — Cards por Papel]**
> *Como capturar: role até a seção RACI com ao menos um registro em cada papel.*

Clique em **Atribuir Papel**, preencha **Usuário**, **Papel** (R/A/C/I), **Entrega** (escolha uma entrega específica ou "Plano inteiro" para papel geral) → **Salvar**.

> 📸 **[Figura 62 — Modal "Atribuir Papel RACI"]**
> *Como capturar: clique em "Atribuir Papel".*

**Plano de Comunicação:**  
Define como, quando e para quem as informações sobre o plano serão comunicadas.

> 📸 **[Figura 63 — Plano de Comunicação — Tabela de Itens]**
> *Como capturar: role até "Plano de Comunicação" com ao menos dois itens cadastrados.*

Clique em **Adicionar** e preencha: **Público-Alvo**, **Mensagem-Chave**, **Canal** (ex.: e-mail, reunião, relatório), **Frequência** e **Responsável** → **Salvar**. Há botão **PDF** para gerar o plano de comunicação em documento.

---

### 6.11 Gerenciar Entregas

**O que é:** as entregas são os **produtos, tarefas e resultados concretos** que compõem a execução de um plano de ação. É o nível mais operacional do planejamento — aqui estão o dia a dia, os prazos e as responsabilidades individuais. O sistema oferece quatro modos de visualização para diferentes perfis de uso.

**Como acessar:**
- Direto de um plano: na Ficha Técnica, clique em **Entregas** → `[endereço do sistema]/planos/{id}/entregas`
- Visão geral de todos os planos: menu lateral → **Gerenciar Entregas** → `[endereço do sistema]/entregas`

> 📸 **[Figura 64 — Quadro de Entregas — Modo Kanban (colunas por status)]**
> *Como capturar: acesse o board de um plano com entregas distribuídas em ao menos 3 colunas diferentes.*

> 📸 **[Figura 65 — Quadro de Entregas — Modo Lista (tabela densa)]**
> *Como capturar: alterne para o modo Lista (ícone de linhas no cabeçalho).*

> 📸 **[Figura 66 — Quadro de Entregas — Modo Linha do Tempo (Gantt)]**
> *Como capturar: alterne para Linha do Tempo; certifique-se que as entregas têm prazo definido.*

> 📸 **[Figura 67 — Quadro de Entregas — Modo Calendário]**
> *Como capturar: alterne para o modo Calendário.*

#### 6.11.1 Criar uma Entrega

**Passo 1:** Clique em **Nova Entrega** (ou no botão **+** na parte inferior de qualquer coluna do Kanban).

> 📸 **[Figura 68 — Modal "Nova Entrega"]**
> *Como capturar: clique em "Nova Entrega" no modo de lista ou Kanban.*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição | Sim | O que será entregue (ex.: "Relatório de diagnóstico aprovado") |
| Status | Sim | Não Iniciado / Em Andamento / Concluído / Cancelado / Suspenso |
| Prioridade | Sim | Baixa / Média / Alta / Urgente |
| Prazo | Não | Data limite para entrega |
| Responsável | Não | Usuário responsável pela entrega |
| Peso | Não | Contribuição percentual desta entrega no progresso total do plano |
| Tipo | Não | Tarefa (padrão) / Cabeçalho (agrupa entregas) / Texto / Divisor / Checklist |

**Passo 3:** Clique em **Salvar**.

> **Sobre os pesos:** se nenhuma entrega tiver peso definido, o sistema calcula o progresso do plano de forma igualitária (todas as entregas valem o mesmo). Se você definir pesos, a soma não precisa ser exatamente 100% — o sistema normaliza automaticamente.

#### 6.11.2 Editar uma Entrega (Detalhes)

Clique sobre o título de qualquer entrega para abrir seu painel de detalhes com abas.

> 📸 **[Figura 69 — Detalhes da Entrega — Aba Comentários (com @menção)]**
> *Como capturar: clique em uma entrega, abra a aba "Comentários" e poste um comentário.*

| Aba | O que oferece |
|---|---|
| **Detalhes** | Editar todos os campos da entrega |
| **Comentários** | Discussão colaborativa; suporta `@menção` de usuários |
| **Anexos** | Upload e download de arquivos de evidência |
| **Histórico** | Registro cronológico de todas as alterações feitas nesta entrega |
| **Labels** | Etiquetas coloridas para classificação visual |
| **Sub-entregas** | Checklist de itens menores dentro desta entrega |

> 📸 **[Figura 70 — Detalhes da Entrega — Aba Anexos]**
> *Como capturar: na entrega aberta, clique na aba "Anexos" e faça upload de um arquivo.*

**Arquivar e Lixeira:**
- **Arquivar:** oculta a entrega do board sem excluir. Use para entregas concluídas que não quer mais ver no dia a dia. Podem ser restauradas com o botão **Ver Arquivadas**.
- **Lixeira:** exclusão lógica (a entrega vai para a lixeira, ainda recuperável). Use **Ver Lixeira** para restaurar. **Exclusão permanente** via botão na lixeira é irreversível.

> ⚠️ **A exclusão permanente de uma entrega não pode ser desfeita.**

#### 6.11.3 Criar Entrega Rápida (Kanban)

No modo Kanban, cada coluna tem um campo de texto na parte inferior. Digite a descrição e pressione **Enter** para criar uma entrega diretamente naquela coluna (status) sem abrir o modal completo.

---

### 6.12 Mapa Estratégico

**O que é:** o Mapa Estratégico é a **visualização gráfica BSC** do planejamento — mostra todas as perspectivas com seus objetivos e o farol de desempenho atual de cada um. É o "painel de controle visual" da estratégia, usado em reuniões de alta direção para ter uma visão rápida de onde a organização está.

**Como acessar:** menu lateral → **Mapa Estratégico**, ou `[endereço do sistema]/pei/mapa`.  
**Pré-requisito:** perspectivas, objetivos e ao menos um indicador com evolução lançada.

> 📸 **[Figura 71 — Mapa Estratégico — Modo Estrita (organização corrente)]**
> *Como capturar: acesse `[endereço do sistema]/pei/mapa` com perspectivas, objetivos e indicadores com evolução.*

Cada objetivo aparece como um card colorido pelo farol:

| Cor | Significado |
|---|---|
| 🟢 Verde | Atingimento dentro ou acima da meta |
| 🟡 Amarelo | Atingimento parcial |
| 🔴 Vermelho | Atingimento abaixo do mínimo |
| ⚪ Cinza | Sem dados (nenhum indicador com evolução lançada) |

**Modos de visualização:**
- **Estrita:** exibe somente os dados da organização selecionada.
- **Consolidada:** consolida (*roll-up*) os dados de todas as unidades subordinadas à organização selecionada.

> 📸 **[Figura 72 — Mapa Estratégico — Modo Consolidado com unidades subordinadas]**
> *Como capturar: alterne para o modo Consolidado em uma organização com subordinadas; expanda a lista lateral de unidades.*

**Memória de Cálculo:** clique sobre qualquer objetivo no mapa para abrir o painel que detalha exatamente como o atingimento foi calculado (quais indicadores contribuíram, com qual peso, e o resultado de cada um).

> 📸 **[Figura 73 — Mapa Estratégico — Memória de Cálculo de um Objetivo]**
> *Como capturar: clique sobre um objetivo colorido (não cinza) no mapa.*

> **Transparência pública:** o Mapa Estratégico também é exibido na página pública do sistema (acesso sem login), permitindo que qualquer cidadão acompanhe o desempenho estratégico da organização.

---

## 7. Módulo 03 — Monitorar e Avaliar

> **O que é este módulo?** É onde a execução é acompanhada: riscos são gerenciados, a estratégia é revisada formalmente e os relatórios são gerados. Este módulo é de uso contínuo ao longo de todo o ciclo PEI.

### 7.1 Gestão de Riscos

**O que é:** risco é um **evento incerto** que, se ocorrer, pode afetar negativamente os objetivos do PEI. Gerenciar riscos significa identificá-los antes que aconteçam, avaliar sua probabilidade e impacto, e preparar ações para mitigá-los. O sistema calcula automaticamente o nível de criticidade de cada risco.

**Como acessar:** menu lateral → **Gestão de Riscos**, ou `[endereço do sistema]/riscos`.

> 📸 **[Figura 74 — Listagem de Riscos (com farois de nível)]**
> *Como capturar: acesse `[endereço do sistema]/riscos` com riscos de diferentes níveis (Baixo/Médio/Alto/Crítico).*

Botões no cabeçalho: **Ver Matriz** (abre a matriz visual 5×5) e **Identificar Risco**.

#### 7.1.1 Identificar e Registrar um Risco

**Passo 1:** Clique em **Identificar Risco**.

> 📸 **[Figura 75 — Modal "Identificar Risco"]**
> *Como capturar: clique em "Identificar Risco".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Título | Sim | Nome curto e objetivo do risco |
| Descrição | Sim | Como o risco pode se manifestar |
| Categoria | Sim | Operacional / Legal / Financeiro / Reputacional / Estratégico / Outros |
| Probabilidade | Sim | Nota de 1 (muito baixa) a 5 (muito alta) |
| Impacto | Sim | Nota de 1 (insignificante) a 5 (catastrófico) |
| Responsável pelo Monitoramento | Sim | Usuário que acompanhará este risco |
| Causas | Não | O que pode provocar o risco |
| Consequências | Não | O que acontece se o risco se materializar |
| Status | Sim | Identificado / Em Monitoramento / Mitigado / Encerrado |
| Objetivos Vinculados | Não | Quais objetivos estratégicos este risco ameaça |

**Passo 3:** Clique em **Salvar**. O **Nível de Risco** (Probabilidade × Impacto) é calculado automaticamente:

| Resultado | Nível |
|---|---|
| 1–4 | 🟢 Baixo |
| 5–9 | 🟡 Médio |
| 10–15 | 🟠 Alto |
| 16–25 | 🔴 Crítico |

#### 7.1.2 Planos de Mitigação

**O que é:** ações planejadas para reduzir a probabilidade ou o impacto de um risco.

**Como acessar:** na listagem de riscos, clique no botão **Mitigações** ao lado do risco, ou `[endereço do sistema]/riscos/{id}/mitigacao`.

> 📸 **[Figura 76 — Planos de Mitigação — Listagem]**
> *Como capturar: acesse a tela com ao menos uma mitigação cadastrada.*

**Passo 1:** Clique em **Novo Plano**.

> 📸 **[Figura 77 — Modal "Novo Plano de Mitigação"]**
> *Como capturar: clique em "Novo Plano".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Tipo | Sim | **Preventiva** (evita o risco) / **Corretiva** (minimiza o dano após ocorrer) / **Contingência** (plano B se o risco se materializar) |
| Ação | Sim | O que será feito |
| Responsável | Sim | Quem executará a ação |
| Prazo | Não | Até quando a ação deve ser concluída |
| Status | Sim | A Fazer / Em Andamento / Concluído |
| Observações | Não | Detalhes adicionais |

**Passo 3:** Clique em **Salvar**.

#### 7.1.3 Registrar Ocorrências

**O que é:** quando um risco se **materializa** (acontece de fato), registra-se uma ocorrência com o impacto real e as ações que foram tomadas. Serve como memória histórica e aprendizado.

**Como acessar:** na listagem de riscos, clique em **Ocorrências**, ou `[endereço do sistema]/riscos/{id}/ocorrencias`.

> 📸 **[Figura 78 — Histórico de Materialização — Cards de Ocorrências]**
> *Como capturar: registre ao menos duas ocorrências e capture a tela.*

**Passo 1:** Clique em **Registrar Nova**.

> 📸 **[Figura 79 — Modal "Registrar Nova Ocorrência"]**
> *Como capturar: clique em "Registrar Nova".*

**Passo 2:** Preencha: **Data da Ocorrência**, **Descrição** (o que aconteceu), **Impacto Real** (o que foi afetado), **Ações Tomadas** e **Lições Aprendidas** → **Salvar**.

#### 7.1.4 Matriz Visual de Riscos

**Como acessar:** na tela de Riscos, clique em **Ver Matriz**, ou `[endereço do sistema]/riscos/matriz`.

> 📸 **[Figura 80 — Matriz Visual de Riscos 5×5]**
> *Como capturar: tenha riscos em combinações variadas de probabilidade × impacto para preencher várias células.*

A matriz é uma grade 5×5 (Probabilidade × Impacto) com zonas coloridas. Cada célula exibe os riscos daquele cruzamento. A visualização permite identificar rapidamente onde estão concentrados os riscos mais críticos.

---

### 7.2 RAE — Revisão e Avaliação da Estratégia

**O que é:** a RAE é a **reunião formal periódica** (ordinária — semestral/anual — ou extraordinária — quando necessário) em que líderes e gestores avaliam o desempenho estratégico, decidem ajustes e registram encaminhamentos. No sistema, cada RAE gera uma ata estruturada e um relatório em PDF.

**Como acessar:** menu lateral → **RAE — Revisão da Estratégia**, ou `[endereço do sistema]/monitoramento/rae`.

> 📸 **[Figura 81 — RAE — Listagem de Revisões]**
> *Como capturar: acesse `[endereço do sistema]/monitoramento/rae` com ao menos duas RAEs registradas.*

**Passo 1:** Clique em **Nova RAE**.

> 📸 **[Figura 82 — Modal "Nova RAE"]**
> *Como capturar: clique em "Nova RAE".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Data de Referência | Sim | Período avaliado (ex.: "Semestre 1/2025") |
| Data da Reunião | Não | Quando a reunião aconteceu |
| Tipo de Reunião | Sim | Ordinária / Extraordinária |
| Participantes | Não | Lista de quem participou |
| Destaques Positivos | Não | O que está indo bem |
| Problemas Identificados | Não | O que precisa de atenção ou decisão |
| Encaminhamentos | Não | Decisões e ações deliberadas na reunião |
| Progresso Geral (%) | Não | Avaliação geral do progresso do PEI (0–100%) |

**Passo 3:** Clique em **Salvar**. Cada RAE salva tem um botão **PDF** para gerar a ata em documento.

> 🔒 **Quem pode registrar/editar/excluir uma RAE:** somente o **Administrador da Unidade** responsável pela organização à qual a RAE pertence (ou o **Super Administrador**, sem restrição). Qualquer usuário autenticado pode **visualizar** as atas de RAE, encaminhamentos e análises de causa raiz, mesmo de outras organizações — a consulta é sempre livre; apenas criar, editar ou excluir é restrito.

---

### 7.3 Relatórios

**O que é:** central de geração de documentos executivos em PDF e planilhas Excel. Os relatórios são gerados dinamicamente com base no contexto atual (Organização, PEI e Ano selecionados).

**Como acessar:** menu lateral → **Relatórios**, ou `[endereço do sistema]/relatorios`.

> 📸 **[Figura 83 — Central de Relatórios — Catálogo de Cards]**
> *Como capturar: acesse `[endereço do sistema]/relatorios` com Organização e PEI selecionados; certifique-se que há dados.*

**Filtros no topo:** **Ano**, **Período** (Anual / 1º Semestre / 2º Semestre / 1º Trimestre etc.), **Perspectiva** (para filtrar por perspectiva BSC) e **Incluir IA** (quando a IA está habilitada, adiciona análise de linguagem natural ao documento).

**Botão AI Strategic Minute:** gera uma ata executiva estratégica de curto prazo com base nos dados atuais.

**Catálogo de relatórios disponíveis:**

| Relatório | Formato | O que contém |
|---|---|---|
| Dossiê Estratégico Integrado | PDF | Consolidação completa de todos os módulos |
| Relatório Executivo | PDF | Resumo de indicadores e planos para alta direção |
| Mapa Estratégico | PDF | Visualização BSC com farois |
| Objetivos Táticos (BSC) | PDF / Excel | Lista de objetivos com atingimento |
| Indicadores (KPIs) | PDF / Excel | Tabela detalhada de todos os KPIs com metas e realizados |
| Planos de Ação | PDF / Excel | Portfólio de planos com status e progresso |
| Gestão de Riscos | PDF / Excel | Mapa de riscos com mitigações |
| Identidade Estratégica | PDF | Missão, Visão e Valores |
| Plano de Comunicação | PDF | Consolidado de todos os planos de comunicação |

**Para baixar:** clique no menu (⋮) do card desejado e escolha **Baixar PDF** ou **Baixar Excel**.

> 📸 **[Figura 84 — Dropdown de Download de Relatório (opções PDF e Excel)]**
> *Como capturar: clique no ícone ⋮ de um card de relatório e capture o dropdown com as opções.*

---

### 7.4 Histórico de Relatórios

**Como acessar:** menu lateral → **Histórico de Relatórios**, ou `[endereço do sistema]/relatorios/historico`.

> 📸 **[Figura 85 — Histórico de Relatórios — Tabela com Registros]**
> *Como capturar: gere ao menos 3 relatórios de tipos diferentes e acesse o histórico.*

Tabela com **Data e Hora de Geração**, **Tipo de Relatório**, **Formato** (PDF/Excel), **Tamanho (KB)** e botão **Download** para cada relatório gerado.

---

## 8. Meu Espaço

> **O que é esta seção?** É a área pessoal de cada usuário — mostra apenas o que é relevante para *ele*, independentemente da organização ou PEI selecionado no contexto global.

### 8.1 Minhas Entregas

**O que é:** visão pessoal e consolidada de **todas as entregas atribuídas ao usuário logado**, agrupadas por plano de ação. Ideal para o dia a dia operacional: saber o que está pendente, o que está atrasado e atualizar o status sem precisar navegar por cada plano individualmente.

**Como acessar:** menu lateral → **Minhas Entregas**, ou `[endereço do sistema]/minhas-entregas`.

> 📸 **[Figura 86 — Minhas Entregas — KPIs e Listagem por Plano]**
> *Como capturar: logado com um usuário que tenha entregas atribuídas, com ao menos uma atrasada (em vermelho).*

A tela exibe quatro KPIs:
- **Pendentes:** entregas Não Iniciadas atribuídas a mim
- **Em Andamento:** entregas em execução
- **Atrasadas:** entregas com prazo vencido e não concluídas (destaque em vermelho)
- **Planos:** quantos planos diferentes me têm como responsável

Abaixo dos KPIs, as entregas são agrupadas pelo plano ao qual pertencem. **Alterar o status de uma entrega aqui** recalcula automaticamente o progresso do plano correspondente.

### 8.2 Lições Aprendidas

**O que é:** registro estruturado de **conhecimentos adquiridos durante a execução dos planos** — o que funcionou, o que não funcionou, o que pode ser melhorado. Serve para alimentar os próximos ciclos de planejamento com experiência real.

**Como acessar:** menu lateral → **Lições Aprendidas**, ou `[endereço do sistema]/licoes-aprendidas`.

> 📸 **[Figura 87 — Lições Aprendidas — Agrupadas por Tipo]**
> *Como capturar: acesse com ao menos 2 lições de tipos diferentes (ex.: Problema e Boa Prática).*

**Passo 1:** Clique em **Nova Lição**.

> 📸 **[Figura 88 — Modal "Nova Lição Aprendida"]**
> *Como capturar: clique em "Nova Lição".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Plano de Ação | Sim | A qual plano esta lição se refere |
| Categoria | Não | Tema da lição (ex.: Gestão de Prazo, Comunicação) |
| Tipo | Não | Aprendizado / Problema / Melhoria / Boas Práticas |
| Descrição | Sim | O que aconteceu e o que foi aprendido |
| Recomendação | Não | O que fazer (ou não fazer) da próxima vez |

**Passo 3:** Clique em **Salvar**. Use o filtro **Plano** no topo para ver as lições de um plano específico.

---

## 9. Referências Metodológicas

No grupo **Referências** do menu ficam os documentos metodológicos que embasam o sistema:

| Item | O que contém | Como acessar |
|---|---|---|
| **Guia GPPEI** | Guia Prático do PEI (MGI/2025) em formato PDF com visualizador embutido | Menu → "Guia GPPEI" ou `[endereço do sistema]/guia-gppei` |
| **Guia de Projetos** | Guia de Gestão de Projetos (abre em nova aba) | Menu → "Guia de Projetos" ou `[endereço do sistema]/documentos/projetos` |

> 📸 **[Figura 89 — Visualizador do Guia GPPEI embutido na tela]**
> *Como capturar: menu → "Guia GPPEI"; capture a tela com a capa do PDF renderizada no visualizador.*

Em diversas telas do sistema há links **"Ver no Guia GPPEI"** e **"Ver no Guia de Projetos"** que levam diretamente à página do guia correspondente ao que você está fazendo.

---

## 10. Administração do Sistema

> **Quem acessa:** esta seção é **exclusiva do perfil Super Administrador**. Usuários com outros perfis não enxergam o grupo Administração no menu e recebem erro 403 se tentarem acessar diretamente as URLs.

### 10.1 Organizações

**O que é:** define a estrutura hierárquica das unidades organizacionais (ex.: órgão central → secretaria → departamento). Esta hierarquia sustenta o filtro de dados por organização, o *roll-up* (consolidação) no Mapa Estratégico e no Dashboard, e a vinculação de usuários, planos e indicadores a unidades específicas.

**Como acessar:** menu lateral (Administração) → **Organizações**, ou `[endereço do sistema]/organizacoes`.

> 📸 **[Figura 90 — Listagem de Unidades Organizacionais (com hierarquia)]**
> *Como capturar: acesse com organizações em pelo menos dois níveis hierárquicos.*

#### 10.1.1 Cadastrar uma Organização

**Passo 1:** Clique em **Nova Organização**.

> 📸 **[Figura 91 — Modal "Nova Organização"]**
> *Como capturar: clique em "Nova Organização".*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Sigla | Sim | Ex.: "SEDEC", "DNIT" |
| Nome | Sim | Nome completo da unidade |
| Organização Superior | Não | Unidade à qual esta está subordinada (deixe em branco para nível raiz) |

**Passo 3:** Clique em **Salvar**.

**Detalhar:** clique no nome de uma organização para ver suas subordinadas, usuários vinculados e dados do planejamento.

> 📸 **[Figura 92 — Detalhe de uma Organização (subordinadas e dados)]**
> *Como capturar: clique sobre uma organização que tenha subordinadas.*

### 10.2 Usuários

**O que é:** gerencia todos os usuários do sistema — criação, edição, vínculos (organização + perfil), ativação/inativação e impersonação para suporte.

**Como acessar:** menu lateral (Administração) → **Usuários**, ou `[endereço do sistema]/usuarios`.

> 📸 **[Figura 93 — Listagem de Usuários (com filtros de organização e status)]**
> *Como capturar: acesse `[endereço do sistema]/usuarios` com usuários de diferentes perfis e status.*

**Filtros:** busca por nome ou e-mail, filtro por **Organização** (dropdown — filtra os usuários vinculados àquela organização), filtro por **Status** (Ativo/Inativo).

#### 10.2.1 Cadastrar um Novo Usuário

**Passo 1:** Clique em **Novo Usuário**.

> 📸 **[Figura 94 — Modal "Novo Usuário" — Dados + Vínculo]**
> *Como capturar: clique em "Novo Usuário" e adicione ao menos um vínculo antes de capturar.*

**Passo 2 — Dados do usuário:** preencha os campos:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Nome | Sim | Nome completo |
| E-mail | Sim | E-mail institucional (deve ser único no sistema) |
| Usuário Ativo | Sim | Ativo = pode fazer login; Inativo = bloqueado |
| Exigir Troca de Senha | Não | Marque para forçar troca no primeiro acesso |

**Passo 3 — Senha inicial:** escolha uma das duas opções:
- **Enviar link de redefinição por e-mail** *(padrão):* o sistema envia um e-mail com link para o usuário cadastrar a própria senha.
- **Definir senha manualmente:** o administrador digita a senha inicial diretamente (os campos de senha aparecem ao selecionar esta opção).

> 📸 **[Figura 95 — Modal Usuário — Opção "Definir Senha Manualmente"]**
> *Como capturar: selecione "Definir senha manualmente" para mostrar os campos de senha.*

**Passo 4 — Vínculos obrigatórios (Organização + Perfil):**

> ⚠️ **Todo usuário precisa de pelo menos um vínculo.** Um vínculo é a combinação de uma Organização com um Perfil de Acesso. Sem vínculo, o usuário não tem acesso a dados de nenhuma organização.

1. Na seção **Vínculos**, selecione uma **Organização** no primeiro campo.
2. Selecione o **Perfil de Acesso** no segundo campo (ver seção 11 para a descrição de cada perfil).
3. Clique em **Adicionar vínculo**. O vínculo aparece na tabela abaixo.
4. Repita para adicionar vínculos a outras organizações ou perfis, se necessário.
5. Para remover um vínculo, clique no ícone 🗑️ na linha correspondente.

> 📸 **[Figura 96 — Modal Usuário — Seção de Vínculos com ao menos um vínculo adicionado]**
> *Como capturar: adicione ao menos um vínculo (org + perfil) e capture a tabela de vínculos preenchida.*

**Passo 5:** Clique em **Salvar Usuário**.

> **Dica:** se você selecionou organização e perfil mas esqueceu de clicar em "Adicionar vínculo" antes de salvar, o sistema incorpora o vínculo pendente automaticamente ao salvar.

#### 10.2.2 Editar um Usuário

Clique no ícone ✏️ ao lado do usuário. O mesmo modal abre preenchido. Altere os campos necessários e clique em **Salvar Usuário**.

**Ativar/Inativar:** clique no ícone ✅ (ativo) ou 🚫 (inativo) na listagem. Usuário inativo não consegue fazer login, mas todos os seus dados históricos são preservados.

**Detalhar:** clique no ícone 👁️ ou no nome do usuário para ver a página de detalhe com estatísticas de atividade (planos, entregas, logs de auditoria).

> 📸 **[Figura 97 — Detalhe de um Usuário (estatísticas e atividade)]**
> *Como capturar: clique no nome de um usuário ativo com histórico de atividades.*

#### 10.2.3 Impersonação (Assumir a Identidade de um Usuário)

**O que é:** permite que o Super Administrador acesse o sistema temporariamente *como se fosse* outro usuário, para diagnóstico de problemas de acesso ou suporte. Toda ação realizada durante a impersonação é registrada na Auditoria com identificação do administrador real.

> 📸 **[Figura 98 — Banner de Impersonação Ativo (faixa laranja no topo)]**
> *Como capturar: na listagem, clique no ícone de impersonação (👤→) de um usuário ativo; capture a tela com o banner laranja "Você está como [nome]" no topo.*

**Para iniciar:** na listagem de usuários, clique no ícone de impersonação (👤→) ao lado do usuário desejado. O sistema recarrega como aquele usuário.

**Para encerrar:** clique no botão **Sair da Impersonação** no banner laranja no topo da tela. O sistema retorna ao Super Administrador.

> ⚠️ **Restrições:** não é possível impersonar a si mesmo, nem iniciar uma impersonação dentro de outra impersonação já ativa.

---

### 10.3 Perfis de Acesso

**Como acessar:** menu lateral (Administração) → **Perfis de Acesso**, ou `[endereço do sistema]/admin/perfis`.

> **Importante:** esta é uma tela de **consulta** — os perfis são pré-definidos pelo sistema e **não podem ser criados, editados ou excluídos pela interface**. Para atribuir ou alterar o perfil de um usuário, use a tela de **Usuários** (seção 10.2).

> 📸 **[Figura 99 — Perfis de Acesso — Cards dos 4 Perfis do Sistema]**
> *Como capturar: acesse `[endereço do sistema]/admin/perfis`.*

> 📸 **[Figura 100 — Perfis de Acesso — Matriz de Permissões por Funcionalidade]**
> *Como capturar: role até a tabela "Matriz de Permissões por Funcionalidade".*

---

### 10.4 Graus de Satisfação

**O que é:** define as faixas percentuais que determinam a cor do **farol** dos indicadores. O farol coloriza automaticamente indicadores, objetivos e o Mapa Estratégico conforme o % de atingimento da meta. A configuração padrão é: 0–70% Vermelho, 71–89% Amarelo, 90–100% Verde — mas pode ser personalizada para o contexto de cada PEI.

**Como acessar:** menu lateral (Administração) → **Graus de Satisfação**, ou `[endereço do sistema]/graus-satisfacao`.

> 📸 **[Figura 101 — Listagem de Graus de Satisfação (com preview de cor)]**
> *Como capturar: acesse `[endereço do sistema]/graus-satisfacao` com ao menos 3 graus configurados.*

Quando a IA está habilitada, o botão **Sugerir Escala de Satisfação com IA** propõe automaticamente uma escala baseada em boas práticas.

> 📸 **[Figura 102 — Graus de Satisfação — Sugestão pela IA]**
> *Como capturar: clique em "Sugerir Escala de Satisfação com IA".*

#### 10.4.1 Cadastrar um Grau de Satisfação

**Passo 1:** Clique em **Novo Grau**.

> 📸 **[Figura 103 — Modal "Novo Grau de Satisfação" (com preview de cor ao vivo)]**
> *Como capturar: clique em "Novo Grau"; preencha Descrição, Cor e percentuais para ativar o preview.*

**Passo 2:** Preencha:

| Campo | Obrigatório | O que informar |
|---|---|---|
| Descrição do Grau | Sim | Nome descritivo (ex.: "Crítico", "Atenção", "Adequado", "Excelente") |
| Cor Representativa | Sim | Escolha no color picker; o preview mostra como o farol ficará |
| Ciclo PEI | Não | "Escala Global" (aplica a todos os PEIs) ou um ciclo específico |
| Ano Específico | Não | "Todo o Ciclo" ou um ano específico |
| Percentual Mínimo | Sim | Limite inferior da faixa (ex.: 0) |
| Percentual Máximo | Sim | Limite superior da faixa (ex.: 70) |

**Passo 3:** Clique em **Salvar Grau**.

> **Regra:** as faixas não devem se sobrepor e devem ser contíguas de 0% a 100% para cobrir todo o espectro.

Para ver o detalhe de um grau (quantos indicadores estão naquela faixa atualmente), clique no ícone 👁️.

---

### 10.5 Configurações do Sistema

**Como acessar:** menu lateral (Administração) → **Configurações**, ou `[endereço do sistema]/configuracoes`.

> 📸 **[Figura 104 — Configurações — Seção de Inteligência Artificial]**
> *Como capturar: acesse `[endereço do sistema]/configuracoes` com a IA habilitada e a chave configurada.*

A área de configuração disponível é a de **Inteligência Artificial (IA)**:

| Configuração | O que faz |
|---|---|
| **Habilitar IA** | Liga ou desliga todos os recursos de IA em todo o sistema |
| **Provedor** | Google Gemini ou OpenAI (GPT-4) |
| **Chave da API** | Cole a chave obtida na plataforma do provedor escolhido. Use o botão 👁️ para mostrar/ocultar. Uma chave já salva é exibida como `********` |
| **Testar Conexão** | Verifica se a chave é válida e se há comunicação com o provedor |

**Para configurar a IA:**
1. Ative a chave **Habilitar IA**.
2. Selecione o **Provedor**.
3. Cole a **Chave da API**.
4. Clique em **Testar Conexão** para validar.
5. Clique em **Salvar Configurações**.

> 📸 **[Figura 105 — Configurações — Resultado do Teste de Conexão (sucesso)]**
> *Como capturar: com a chave preenchida, clique em "Testar Conexão" e capture o indicador de sucesso.*

> **Impacto quando a IA está ativa:** os botões "Sugerir com IA", "Gerar Análise AI" e "AI Strategic Minute" ficam disponíveis em várias telas. Quando a IA está desabilitada, esses botões somem automaticamente.

---

### 10.6 Auditoria

**O que é:** registro automático e imutável de todas as operações de criação, alteração e exclusão realizadas no sistema. Essencial para rastreabilidade, governança e conformidade.

**Como acessar:** menu lateral (Administração) → **Auditoria**, ou `[endereço do sistema]/auditoria`.

> 📸 **[Figura 106 — Auditoria — Listagem com Filtros e Badges de Evento]**
> *Como capturar: acesse `[endereço do sistema]/auditoria` após realizar operações variadas.*

**Filtros disponíveis:** Usuário · Evento (Criação / Alteração / Exclusão / Restauração) · Módulo/Tabela · Período (De/Até). Botão **Exportar CSV** para exportar todos os registros filtrados.

A tabela exibe para cada registro: **Data/Hora**, **Usuário**, **Módulo**, **Evento** (badge colorido), **Endereço IP** e botão **Detalhes**.

> 📸 **[Figura 107 — Auditoria — Modal de Detalhes de uma Alteração]**
> *Como capturar: clique em "Detalhes" de um registro de Alteração; capture a tabela com Atributo / Valor Anterior / Valor Novo.*

**O modal de detalhes mostra:**
- Data/Hora exata, Usuário, Endereço IP e User Agent
- Tabela de campos alterados: **Atributo** | **Valor Anterior** | **Valor Novo**
- URL e rota da requisição que originou a mudança

---

## 11. Perfis de Usuário e Permissões

O sistema tem **quatro perfis de acesso** pré-definidos. Cada usuário pode ter múltiplos vínculos (organização + perfil), inclusive com perfis diferentes em organizações diferentes.

| Perfil | Escopo de atuação | O que pode fazer |
|---|---|---|
| **Super Administrador** | Todo o sistema, sem restrição | Acesso irrestrito para editar/excluir qualquer dado de qualquer organização, incluindo o grupo Administração (Organizações, Usuários, Perfis, Graus, Configurações, Auditoria). É a única exceção às regras de responsabilidade organizacional abaixo |
| **Administrador de Unidade** | Organização à qual está vinculado | Criar, editar e excluir dados estratégicos e operacionais **da sua unidade** (Planos de Ação, Entregas, Indicadores, Riscos, RAE, Futuro Almejado, Missão/Visão, Lições Aprendidas etc.); gerenciar usuários e planos da unidade; não acessa o grupo Administração do sistema |
| **Gestor Responsável** | Planos de ação aos quais está vinculado | Criar e editar indicadores e planos de ação; gerenciar entregas dos planos vinculados; registrar evoluções |
| **Gestor Substituto** | Planos de ação aos quais está vinculado | Mesmas permissões do Gestor Responsável nos planos vinculados |

### 11.1 A regra de responsabilidade organizacional (quem pode editar o quê)

Uma regra simples vale para **todos os módulos operacionais** do sistema — Planos de Ação, Entregas, Indicadores, Riscos, RAE (Revisão da Estratégia), Futuro Almejado, Missão/Visão institucional e Lições Aprendidas:

> **Só pode CRIAR, EDITAR, LANÇAR EVOLUÇÃO ou EXCLUIR** um registro quem for:
> - o **Administrador da Unidade** responsável pela organização daquele registro; **ou**
> - o **Gestor Responsável ou Substituto** especificamente vinculado àquele Plano de Ação; **ou**
> - (no caso de Riscos) o **responsável pelo monitoramento** designado no próprio risco; **ou**
> - o **Super Administrador**, que não tem restrição alguma.

> ✅ **Visualizar é sempre livre.** Consultar informações — abrir o Mapa Estratégico, ver o detalhe de um objetivo, indicador, perspectiva ou plano, mesmo de **outra organização** — é permitido a qualquer usuário autenticado do sistema, independentemente de vínculo ou perfil. A restrição de organização/perfil se aplica **apenas** na hora de editar ou salvar algo — nunca para apenas visualizar.

> 🔧 Essa regra foi recentemente reforçada nos módulos de **Indicadores, RAE, Futuro Almejado, Missão/Visão institucional e Lições Aprendidas**, onde havia uma inconsistência que fazia a checagem não considerar corretamente a organização real do registro. Hoje o comportamento é uniforme em todos os módulos.

### Resumo das Permissões por Funcionalidade

| Funcionalidade | Super Admin | Admin de Unidade | Gestor Responsável | Gestor Substituto |
|---|---|---|---|---|
| Criar/Editar Ciclo PEI | ✅ | ✅ | ❌ | ❌ |
| Criar/Editar Perspectivas | ✅ | ✅ | ❌ | ❌ |
| Criar/Editar Objetivos | ✅ | ✅ | ❌ | ❌ |
| Criar/Editar Indicadores | ✅ | ✅ | ✅ | ❌ |
| **Excluir** Indicadores | ✅ | ✅ | ❌ | ❌ |
| Registrar Evolução de KPI | ✅ | ✅ | ✅ | ✅ |
| Criar/Editar Planos de Ação | ✅ | ✅ | ✅ | ❌ |
| **Excluir** Planos de Ação | ✅ | ✅ | ❌ | ❌ |
| Gerenciar Entregas (criar/editar/excluir) | ✅ | ✅ | ✅* | ✅* |
| Identificar e Editar Riscos | ✅ | ✅ | ✅ | ✅ |
| **Excluir** Riscos | ✅ | ✅ | ❌ | ❌ |
| Criar/Editar/Excluir RAE (atas, encaminhamentos, causa raiz) | ✅ | ✅ | ❌ | ❌ |
| Visualizar Dashboard e Relatórios | ✅ | ✅ | ✅ | ✅ |
| Administração (Usuários, Perfis, Config.) | ✅ | ❌ | ❌ | ❌ |

*\*Gestores só podem gerenciar entregas dos planos aos quais estão vinculados como Responsável ou Substituto.*

> **Visualização:** todos os usuários autenticados podem visualizar dados estratégicos (Dashboard, Mapa, listagens, RAE, indicadores, planos) de **qualquer** organização do sistema, independente de vínculo. A restrição de organização só entra em jogo para ações de escrita (criar/editar/lançar/excluir).

> **Seletor de Organização:** ao trocar a organização ativa no cabeçalho, você só consegue selecionar organizações às quais realmente pertence — essa validação é garantida tanto na lista exibida quanto no processamento da troca. Exceção: o Super Administrador pode selecionar qualquer organização do sistema.

---

## 12. Mensagens de Erro e Alertas Comuns

| Mensagem exibida | Causa | O que fazer |
|---|---|---|
| *"Você não tem permissão para acessar este recurso"* ou *"Acesso negado"* | Perfil sem autorização para aquela funcionalidade | Solicite ao Super Administrador que ajuste seu perfil ou vínculo |
| *"Sua sessão expirou. Por favor, faça login novamente."* | Inatividade prolongada ou token expirado | Faça login novamente; dados não salvos serão perdidos |
| *"Não é possível excluir: existem registros vinculados"* | O registro tem dependências no banco de dados | Remova primeiro os registros dependentes (ex.: remova as entregas antes de excluir o plano) |
| *"Erro de validação: [campo] é obrigatório"* | Campo obrigatório não preenchido | Preencha todos os campos marcados com * ou indicados como obrigatórios |
| *"Você precisa alterar sua senha antes de continuar"* | Conta marcada com troca de senha obrigatória | Siga o processo da seção 2.4 |
| *"A soma dos pesos deve ser 100%"* | Peso de Indicadores + Peso de Planos ≠ 100% na Perspectiva | Ajuste os dois campos para somarem exatamente 100% |
| *"A data de fim deve ser posterior à data de início"* | Datas invertidas | Corrija a ordem das datas |
| *"Nenhum ciclo PEI ativo selecionado"* | Seletor global sem PEI selecionado | Selecione um PEI no seletor do cabeçalho |
| *"Selecione uma organização"* | Seletor global sem organização | Selecione uma organização no seletor do cabeçalho |
| *"O usuário precisa de pelo menos um vínculo"* | Tentativa de salvar usuário sem organização + perfil atribuídos | Na aba de vínculos do modal, selecione organização + perfil e clique em "Adicionar vínculo" antes de salvar |
| *"E-mail já cadastrado no sistema"* | E-mail duplicado | Use um e-mail diferente ou edite o usuário existente |
| Página em branco ou dados vazios no Dashboard | Nenhum PEI ou organização selecionado | Verifique os seletores globais no cabeçalho |

---

## 13. Glossário

| Termo | Definição |
|---|---|
| **2FA** | Autenticação em Dois Fatores — segunda camada de segurança além da senha, usando um código gerado por aplicativo |
| **Agenda 2030** | Plano global da ONU com 17 ODS para um futuro sustentável até 2030 |
| **BSC** | Balanced Scorecard — metodologia que organiza objetivos estratégicos em perspectivas interdependentes para uma visão equilibrada do desempenho |
| **Cadeia de Valor** | Mapeamento das atividades finalísticas e de suporte que geram valor para o cidadão |
| **Cenário Prospectivo** | Narrativa de como o futuro pode se apresentar (Otimista, Tendencial ou Pessimista), para testar a robustez da estratégia |
| **Ciclo PEI** | Período de vigência do planejamento estratégico (ex.: 2024-2028), contêiner de toda a estratégia |
| **Dashboard** | Painel de indicadores e métricas em tempo real |
| **Dossiê Estratégico** | Relatório que consolida todos os módulos do PEI em um único documento |
| **Entrega** | Produto, tarefa ou resultado concreto dentro de um Plano de Ação |
| **Farol** | Sinalização visual por cor (verde/amarelo/vermelho/cinza) que indica o nível de atingimento da meta |
| **Futuro Almejado** | Narrativa descritiva do estado concreto que a organização quer alcançar ao cumprir um objetivo |
| **GPPEI** | Guia Prático de Elaboração e Implementação do PEI — MGI/2025 |
| **Grau de Satisfação** | Faixa percentual configurável que define a cor do farol |
| **GUT** | Técnica de priorização por Gravidade × Urgência × Tendência |
| **Identidade Estratégica** | Conjunto formado por Missão, Visão e Valores da organização |
| **Impersonação** | Funcionalidade que permite ao Super Administrador acessar o sistema temporariamente como outro usuário |
| **KPI** | Key Performance Indicator — indicador-chave de desempenho; métrica quantificável de progresso |
| **Lição Aprendida** | Conhecimento adquirido durante a execução de um plano, útil para ciclos futuros |
| **Memória de Cálculo** | Detalhamento passo a passo de como o atingimento percentual de um objetivo foi calculado |
| **Mentor Estratégico** | Trilha de progresso das 5 fases do PEI exibida no Dashboard, que recolhe ao atingir 100% |
| **Missão** | Propósito e razão de existir da organização — *por que* ela existe |
| **ODS** | Objetivos de Desenvolvimento Sustentável — os 17 objetivos da Agenda 2030 da ONU |
| **PEI** | Planejamento Estratégico Integrado |
| **PESTEL** | Análise do ambiente externo em seis dimensões: Política, Econômica, Social, Tecnológica, Ambiental (Environmental), Legal |
| **Perspectiva BSC** | Dimensão de análise que agrupa objetivos relacionados (ex.: Cidadão e Sociedade, Processos Internos) |
| **Plano de Ação** | Iniciativa ou projeto vinculado a um objetivo, com escopo, prazo, orçamento e portfólio de entregas |
| **RACI** | Matriz de papéis: Responsável (executa), Aprovador (responde), Consultado (orienta), Informado (recebe) |
| **RAE** | Revisão e Avaliação da Estratégia — reunião periódica formal de análise do desempenho estratégico |
| **Roll-up** | Consolidação automática dos dados das unidades subordinadas para a visão da organização-mãe |
| **SMART** | Critério de qualidade de indicadores: Específico, Mensurável, Atingível, Relevante, Temporal |
| **Stakeholder** | Parte interessada — pessoa, grupo ou organização que afeta ou é afetada pela estratégia |
| **SWOT** | Análise das Forças (Strengths), Fraquezas (Weaknesses), Oportunidades e Ameaças da organização |
| **Tema Norteador** | Eixo estratégico transversal que perpassa todas as perspectivas (ex.: "Digitalização", "Sustentabilidade") |
| **Vínculo** | Combinação de Organização + Perfil de Acesso que define o que um usuário pode ver e fazer no sistema |
| **Visão** | Estado futuro almejado pela organização ao final do ciclo PEI — *onde* quer chegar |
| **5W2H** | Estrutura de planejamento: What (o quê), Who (quem), When (quando), Where (onde), Why (por quê), How (como), How Much (quanto custa) |

---

*Manual Operacional — Sistema de Planejamento Estratégico Integrado (PEI)*  
*Versão 4.0 — Junho de 2026 — MIDR*
