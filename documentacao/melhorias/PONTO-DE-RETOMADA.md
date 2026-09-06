# Estado do ciclo — 15 demandas estudadas

**Concluído em:** 05/09/2026 · **Branch:** `chore/harness-permissoes-e-guardas`
**Nada commitado. Nenhum código de aplicação alterado.**

---

## O que existe agora

17 estudos em `documentacao/melhorias/`, 4.700 linhas. Cada um com: análise do problema com
evidência (arquivo:linha), soluções avaliadas com recomendação, plano de ação, roadmap, backlog
com esforço e dependência, "como eu confirmaria que está correto" e "o que NÃO foi verificado".

Comece pelo [00-INDICE-AGREGACAO-POR-TEMAS.md](00-INDICE-AGREGACAO-POR-TEMAS.md) — agrega as 15
demandas em 7 temas, com grafo de dependências e ordem de execução em 6 ondas.

O **16** não estava na lista: é a causa comum das demandas 2 e 3, e resolve as duas.

---

## 🔴 Sete decisões suas — nenhuma é técnica

| # | Pergunta | Onde | Trava |
|---|---|---|---|
| 1 | O tipo **"Iniciativa"** sai da tabela de tipos? Depois da renomeação, "Iniciativa · Tipo: Iniciativa" é ambíguo | [02](02-tipos-de-iniciativa-seeder.md) §2.2 | demanda 2 |
| 2 | **"Valores públicos"** é um terceiro tipo de atividade, ou a faixa de resultado da cadeia? Muda de 2h para 1 dia | [03](03-modal-nova-atividade.md) §2.3 | demanda 3 |
| 3 | Autoriza os **`SELECT` de contagem** em dev e numa base de cliente? | [02](02-tipos-de-iniciativa-seeder.md) §5 | demandas 2, 3, 6, 11 |
| 4 | **Qual a versão mínima de PostgreSQL** que o produto suporta? A regra "9.3" veio do mesmo bloco da premissa que corrigimos | [16](16-padrao-seeders-idempotentes.md) §9 | desenho do seeder |
| 5 | O **Admin de Unidade** passa a definir os Graus de Satisfação? Exige mexer no `CapacidadeResolver` (arquivo global) | [06](06-grau-de-satisfacao-antes-do-indicador.md) §5 | demandas 6 e 8 |
| 6 | **O que pode ser publicado sem login?** Tabela linha a linha | [07](07-mapa-estrategico-publico.md) §4 | demanda 7 |
| 7 | Aceita que o **Relatório de Gestão gere ~40 das 198 páginas** do modelo? | [15](15-relatorio-de-gestao-modelo-presidencia.md) §3 | demanda 15 |

---

## O que descobri e ninguém tinha pedido

| Achado | Onde |
|---|---|
| 🔴 O sistema manda o cliente configurar Graus de Satisfação numa tela que devolve **403** para todo perfil não-Super-Admin | [06](06-grau-de-satisfacao-antes-do-indicador.md) §2 |
| 🔴 **13 de 17** consultas ao Grau de Satisfação ignoram o PEI — o farol pode acender com a régua de outro ciclo | [06](06-grau-de-satisfacao-antes-do-indicador.md) §3 |
| 🔴 **26 de 51** Models não qualificam o schema | [12](12-visao-holistica-achados-transversais.md) §3 |
| 🔴 O mesmo bug do erro `[pei]` existe num segundo lugar, ainda não relatado (`GerenciarRae.php:311`) | [11](11-erro-conexao-pei-e-modal-de-risco.md) §2.4 |
| 🔴 O **Histórico de Relatórios está vazio por construção**: só o agendador grava, e o agendador não roda | [10](10-historico-de-relatorios-e-textos-humanizados.md) §2 |
| 🔴 A landing **já publica** o mapa, o atingimento e a contagem de riscos críticos, sem chave para desligar | [07](07-mapa-estrategico-publico.md) §2 · [13](13-auditoria-ui-ux-navegada.md) §3.3 |
| 🟠 **15 de 23** `catch` na camada de tela descartam a exceção sem log | [12](12-visao-holistica-achados-transversais.md) §5 |
| 🟠 **10 de 58** componentes e **1 de 10** services citados em teste. `IndicadorCalculoService` — o farol — não tem nenhum | [12](12-visao-holistica-achados-transversais.md) §6 |
| 🟡 O ODS no objetivo (demanda 5) **já está implementado** — o problema é o cliente não achar | [05](05-vinculo-ods-no-objetivo.md) §1 |

🔒 **Um achado de segurança** foi registrado em `.claude/seguranca-em-aberto.md` (item 6), **fora
do versionamento** — o repositório é público. A demanda [10](10-historico-de-relatorios-e-textos-humanizados.md)
o referencia sem descrevê-lo.

---

## Alterado fora de `documentacao/melhorias/`

| Arquivo | O quê |
|---|---|
| `CLAUDE.md` | Corrigida a premissa "produção não roda migration" — veio de outro projeto. Agora: migration e seeder são o caminho oficial, executados pelo cliente. Três seções reescritas |
| `.claude/seguranca-em-aberto.md` | Item 6 acrescentado (ignorado pelo git) |

---

## Ordem recomendada

| Onda | Demandas | Por quê |
|---|---|---|
| **0** | [11](11-erro-conexao-pei-e-modal-de-risco.md), [14](14-claude-md-fora-do-repositorio.md), 🔒 item 6 | Bug que impede salvar risco; contexto interno em repositório público; falha de acesso a arquivo |
| **1** | [16](16-padrao-seeders-idempotentes.md), [02](02-tipos-de-iniciativa-seeder.md), [03](03-modal-nova-atividade.md) | O padrão precisa existir antes de qualquer dado novo |
| **2** | [01](01-renomear-plano-de-acao-para-iniciativas.md) | Trava 09 e 15; quanto mais tarde, mais arquivos |
| **3** | [06](06-grau-de-satisfacao-antes-do-indicador.md), [04](04-ordem-das-perspectivas-no-bsc.md), [08](08-quem-gerencia-o-que-papeis-e-responsaveis.md), [05](05-vinculo-ods-no-objetivo.md), [13](13-auditoria-ui-ux-navegada.md) | Baixo risco, alto retorno percebido |
| **4** | [09](09-reconstrucao-ui-dos-relatorios.md), [10](10-historico-de-relatorios-e-textos-humanizados.md) | Design system antes do conteúdo |
| **5** | [15](15-relatorio-de-gestao-modelo-presidencia.md) | Maior visibilidade; depende de 01 e 09 |
| **6** | [12](12-visao-holistica-achados-transversais.md) | Fecha o ciclo — e o passo 1 dele (banco de teste na porta 5434) destrava tudo |

---

## A limitação que atravessa tudo

**A suíte de testes não roda nesta máquina** — o banco de teste (porta 5434) está fora do ar.
Todo diagnóstico deste ciclo é leitura estática de código, mais a auditoria de UI que foi
executada no Chrome de verdade.

Levantar esse banco é o item de maior alavancagem de todo o conjunto: sem ele, nenhuma das travas
propostas nos 17 documentos pode ser escrita.

---

## Ferramentas preparadas

Em `…/scratchpad/` (efêmero — refazer é rápido):

| Para | Ferramenta |
|---|---|
| Ler o PDF modelo | `pdftotext -layout` (poppler, já no mingw64) — 198 páginas |
| Ver o PDF modelo | `pdf-to-img` (Node) — páginas em PNG |
| Medir cor do modelo | `pngjs` — amostragem de pixel |
| Auditar UI | `puppeteer-core` dirigindo o **Chrome instalado**, 4 resoluções, capturas e métricas |

O script de auditoria de UI merece ser versionado ([13](13-auditoria-ui-ux-navegada.md) 13-B19) —
ele transforma "observe pontos de melhoria" em número comparável antes e depois.
