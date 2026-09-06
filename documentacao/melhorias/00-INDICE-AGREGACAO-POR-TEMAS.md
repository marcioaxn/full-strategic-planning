# Índice e Agregação por Temas — Ciclo de Correções e Melhorias

**Data do levantamento:** 05/09/2026 · **Branch:** `chore/harness-permissoes-e-guardas`
**Origem:** 15 demandas registradas pelo gestor, sem ordem de prioridade.

Este documento agrega as 15 demandas em **7 grandes temas**, declara as dependências entre elas e
aponta o arquivo de estudo individual de cada uma.

> ⚠️ **Nenhuma linha de código foi alterada nesta etapa.** O que existe aqui é estudo, plano de
> ação, roadmap e backlog. A execução começa quando o gestor aprovar a ordem.

---

## 🔴 Bloqueios e decisões que precisam do gestor ANTES de executar

| # | Bloqueio | Onde | Por quê |
|---|---|---|---|
| 1 | **O tipo `Iniciativa` já existe no banco** (`action_plan.tab_tipo_execucao`), e a demanda 2 diz que os tipos são apenas `Ação` e `Projeto`. Com a renomeação da demanda 1 ("Plano de Ação" → "Iniciativas"), um *tipo* chamado "Iniciativa" dentro de um *módulo* chamado "Iniciativas" é ambíguo. | [02](02-tipos-de-iniciativa-seeder.md) | Decidir: aposentar o tipo, ou manter os três. Há linhas em produção possivelmente vinculadas. |
| 2 | **A contagem de linhas por tipo em produção não foi medida.** | [02](02-tipos-de-iniciativa-seeder.md) | CLAUDE.md, regra nº 4: número não se escolhe, mede-se. Sem isso não há SQL de produção seguro. |
| 3 | **Abrir o Mapa Estratégico ao público é decisão institucional, não técnica.** | [07](07-mapa-estrategico-publico.md) | Expõe objetivos, indicadores e desempenho de um órgão de Estado a qualquer visitante. |
| 4 | **Dado de referência hoje é semeado dentro de migration**, e migration já aplicada não roda de novo. | [02](02-tipos-de-iniciativa-seeder.md), [03](03-modal-nova-atividade.md), [16](16-padrao-seeders-idempotentes.md) | É por isso que o cliente já instalado não recebe opção nova. O padrão precisa mudar antes de acrescentar qualquer tipo. |
| 5 | **`CLAUDE.md` está rastreado pelo git e o repositório é público.** | [14](14-claude-md-fora-do-repositorio.md) | Remover do índice não apaga o histórico. Precisa de decisão sobre reescrita de histórico. |

---

## Os 7 temas

### Tema A — Vocabulário e identidade do produto
O sistema fala uma língua; a Presidência da República fala outra. O modelo de Relatório de Gestão
2025 usa **"Iniciativas"** em todas as tabelas de resultado (verificado, páginas 28 e 34–37 do PDF).
O sistema chama a mesma coisa de "Plano de Ação". E batizou o relatório principal de "Dossiê" —
palavra que, em Brasília, carrega conotação de investigação, não de prestação de contas.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 1 | [01-renomear-plano-de-acao-para-iniciativas.md](01-renomear-plano-de-acao-para-iniciativas.md) | 54 arquivos com o termo; renomear **só o rótulo**, nunca tabela/rota/classe |
| 9 (parte) | [09-reconstrucao-ui-dos-relatorios.md](09-reconstrucao-ui-dos-relatorios.md) | Renomear "Dossiê Estratégico Integrado" |

### Tema B — Vocabulários controlados e como eles chegam ao cliente
Duas demandas pedem opção nova em campo de lista fechada. As duas esbarram no mesmo problema
estrutural: **este projeto semeia vocabulário controlado dentro do `up()` da migration**. Migration
já aplicada não roda de novo — então cliente novo recebe a opção e **cliente já instalado nunca
recebe**, mesmo rodando `migrate`. É exatamente o cenário que o gestor descreveu.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 2 | [02-tipos-de-iniciativa-seeder.md](02-tipos-de-iniciativa-seeder.md) | Tipos `Ação` / `Projeto` + seeder idempotente que atende instalação nova e existente |
| 3 | [03-modal-nova-atividade.md](03-modal-nova-atividade.md) | Tipo `Valores públicos` + largura do modal |
| — | [16-padrao-seeders-idempotentes.md](16-padrao-seeders-idempotentes.md) | **Derivado:** o padrão que resolve 2 e 3 de uma vez e vale para os próximos |

### Tema C — Honestidade metodológica da interface
Rótulo ambíguo é defeito (CLAUDE.md, mandamento nº 6c). Três demandas apontam telas que
**induzem o cliente a uma leitura errada** do próprio método BSC.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 4 | [04-ordem-das-perspectivas-no-bsc.md](04-ordem-das-perspectivas-no-bsc.md) | Preview do mapa é estático e não reflete a ordem digitada |
| 6 | [06-grau-de-satisfacao-antes-do-indicador.md](06-grau-de-satisfacao-antes-do-indicador.md) | O semáforo não é explicado, e o cliente **não tem permissão** de preenchê-lo |
| 10 | [10-historico-de-relatorios-e-textos-humanizados.md](10-historico-de-relatorios-e-textos-humanizados.md) | Histórico sem explicação e sem fila configurada |

### Tema D — Fluxo de preenchimento do ciclo
Onde o cliente trava, desiste ou preenche em duplicidade.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 5 | [05-vinculo-ods-no-objetivo.md](05-vinculo-ods-no-objetivo.md) | ⚠️ **Já implementado.** Estudo reposiciona a demanda |
| 11 | [11-erro-conexao-pei-e-modal-de-risco.md](11-erro-conexao-pei-e-modal-de-risco.md) | Bug confirmado + reordenação do modal |

### Tema E — Acesso, transparência e governança de papéis
Quem vê, quem preenche, quem responde.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 7 | [07-mapa-estrategico-publico.md](07-mapa-estrategico-publico.md) | Mapa navegável sem autenticação, leitura apenas |
| 8 | [08-quem-gerencia-o-que-papeis-e-responsaveis.md](08-quem-gerencia-o-que-papeis-e-responsaveis.md) | Tornar visível na tela quem pode lançar evolução |

### Tema F — Relatórios como produto
O maior bloco de trabalho, e o de maior exposição: é o artefato que chega ao ministro.

| Demanda | Arquivo | Escopo |
|---|---|---|
| 9 | [09-reconstrucao-ui-dos-relatorios.md](09-reconstrucao-ui-dos-relatorios.md) | Design system de relatório; supressão de seção vazia; renomear "Dossiê" |
| 10 | [10-historico-de-relatorios-e-textos-humanizados.md](10-historico-de-relatorios-e-textos-humanizados.md) | Histórico e agendamento |
| 15 | [15-relatorio-de-gestao-modelo-presidencia.md](15-relatorio-de-gestao-modelo-presidencia.md) | Relatório de Gestão em DOCX e PDF, réplica do modelo + versão autoral |

### Tema G — Qualidade transversal e higiene do repositório

| Demanda | Arquivo | Escopo |
|---|---|---|
| 12 | [12-visao-holistica-achados-transversais.md](12-visao-holistica-achados-transversais.md) | Achados que apareceram no levantamento e ninguém pediu |
| 13 | [13-auditoria-ui-ux-navegada.md](13-auditoria-ui-ux-navegada.md) | Auditoria de UI/UX pelo navegador |
| 14 | [14-claude-md-fora-do-repositorio.md](14-claude-md-fora-do-repositorio.md) | `CLAUDE.md` fora do remoto |

---

## Grafo de dependências

```
        ┌─────────────────────────────────────────────────┐
        │ 14  CLAUDE.md fora do repo   (independente)     │
        │ 11  Bug conexão [pei]        (independente)     │  ← executar primeiro
        └─────────────────────────────────────────────────┘
                              │
        ┌─────────────────────▼───────────────────────────┐
        │ 16  Padrão de seeder idempotente                │
        └──────────┬──────────────────────┬───────────────┘
                   │                      │
            ┌──────▼──────┐        ┌──────▼──────┐
            │ 02 Tipos    │        │ 03 Valores  │
            │ de Iniciat. │        │  públicos   │
            └──────┬──────┘        └─────────────┘
                   │
        ┌──────────▼──────────────────────────────────────┐
        │ 01  Renomear → Iniciativas                      │  ← trava 09 e 15
        └──────────┬──────────────────────────────────────┘
                   │
        ┌──────────▼──────────┐   ┌────────────────────┐
        │ 09 UI dos relatórios│──▶│ 15 Relat. de Gestão│
        └──────────┬──────────┘   └────────────────────┘
                   │
             ┌─────▼─────┐
             │ 10 Histór.│
             └───────────┘

  Trilha paralela (não bloqueia nada):
     04 ordem BSC · 05 ODS · 06 grau satisfação · 08 papéis · 12 · 13
     07 mapa público  ← depende de decisão institucional, não de código
```

---

## Ordem de execução recomendada

| Onda | Demandas | Justificativa |
|---|---|---|
| **0 — Sangramento** | 11, 14 | Bug em produção que impede salvar risco; segredo de contexto em repositório público |
| **1 — Fundação** | 16, 02, 03 | O padrão de seeder precisa existir antes de qualquer dado novo |
| **2 — Vocabulário** | 01 | Trava 09 e 15; quanto mais tarde, mais arquivos para renomear |
| **3 — Honestidade de tela** | 04, 06, 08, 05 | Baixo risco, alto retorno percebido; independentes entre si |
| **4 — Relatórios** | 09, 10 | Bloco grande; exige o design system antes do conteúdo |
| **5 — Relatório de Gestão** | 15 | Depende de 01 e 09; é o entregável de maior visibilidade |
| **6 — Varredura** | 12, 13 | Fecham o ciclo com o que o levantamento revelou |
| **Fora de onda** | 07 | Entra quando o gestor decidir a exposição pública |

---

## Como este levantamento foi feito (e o que não foi verificado)

> 🔴 **Correção de premissa, 05/09/2026.** A primeira versão deste levantamento foi escrita sobre
> a regra do `CLAUDE.md` que dizia que produção não roda `migrate`. **Essa regra veio de outro
> projeto e não vale aqui.** Este produto é multicliente, e cada cliente executa `migrate` e
> `db:seed` no próprio terminal de produção. O `CLAUDE.md` foi corrigido no mesmo turno, e todos
> os estudos abaixo assumem migration + seeder idempotente como caminho oficial de entrega.

**Verificado, com comando:**
- 54 arquivos contêm "Plano de Ação" (`grep -rli` em `resources/views`, `app`, `routes`)
- `exists:pei.users,id` em `app/Livewire/RiskManagement/ListarRiscos.php:224` — causa do erro da demanda 11
- `action_plan.tab_tipo_execucao` é semeada **dentro da migration** `2021_11_14_221355`, com três tipos
- `AtividadeCadeiaValor::TIPOS = ['Finalística', 'Suporte']`, validação e agrupamento hardcoded
- `'graus-satisfacao' => []` na `MATRIZ` do `CapacidadeResolver` — só Super Admin
- `CLAUDE.md` aparece em `git ls-files`
- `app/Jobs/` não existe; nada no projeto implementa `ShouldQueue` além de `WelcomeUserMail`
- O PDF modelo tem 198 páginas, sumário em 5 capítulos + anexo; estrutura, cores e grid extraídos
- Não há `phpoffice/phpword` no `composer.json` — DOCX ainda não é possível

**Não verificado, e por quê:**
- **Contagem de linhas em qualquer tabela** — exige escrita/leitura no banco com autorização explícita
- **Comportamento em tela** — a suíte não roda nesta máquina (banco de teste na porta 5434 fora do ar)
- **Fidelidade visual do relatório atual** — não houve navegação no Chrome nesta etapa (ver [13](13-auditoria-ui-ux-navegada.md))
