# 01 — Renomear "Plano de Ação" para "Iniciativas"

> **Tema:** A — Vocabulário e identidade do produto
> **Tipo:** Melhoria · **Impacto:** alto (toda a superfície visível) · **Risco de regressão:** alto se malfeita
> **Bloqueia:** [09](09-reconstrucao-ui-dos-relatorios.md), [15](15-relatorio-de-gestao-modelo-presidencia.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "O item [Plano de Ação] precisa ser renomeado para [Iniciativas]. Isso é apenas nas blades,
> relatórios e onde estiver referenciado como [Plano de Ação]."

A frase **"apenas nas blades, relatórios e onde estiver referenciado"** é a instrução mais
importante deste documento. Ela delimita o escopo em **rótulo**, não em estrutura.

---

## 2. Por que a mudança está certa — evidência externa

Não é preferência de gosto. O modelo de Relatório de Gestão 2025 da Presidência da República
(`documentacao/relatorios/RelatriodeGesto2025PReVPR31mar.pdf`, 198 páginas) usa **"Iniciativas"**
como termo oficial em todas as tabelas de resultado:

| Página do PDF | Elemento | Cabeçalho literal |
|---|---|---|
| 28 | Tabela 2.2.1 | `Identificador · Objetivo Estratégico · Descrição (resumida) · Principais Iniciativas` |
| 34–37 | Tabela 2.2.2 | `Objetivo · Iniciativas · Resultados` |
| 27 | Texto 2.1 | "…a identificação de **iniciativas** de interesse nas unidades…" |
| 27 | Texto 2.1 | "Em 2024 houve o monitoramento das **iniciativas** do PEI/PR…" |

O maior cliente do sistema já escreve "Iniciativas" no seu documento oficial. O sistema escreve
"Plano de Ação". A demanda 15 pede um relatório **idêntico ao modelo** — sem esta renomeação, o
relatório gerado divergiria do modelo já na primeira tabela.

---

## 3. Análise técnica

### 3.1 A superfície real

`grep -rli "plano de aç|plano de ac|planos de aç"` em `resources/views`, `app` e `routes`
devolve **54 arquivos**, distribuídos assim:

| Camada | Arquivos | Tratamento |
|---|---:|---|
| `resources/views/livewire/**` | 22 | ✅ Renomear (rótulo visível) |
| `resources/views/relatorios/**` | 4 | ✅ Renomear (rótulo visível) |
| `resources/views/layouts`, `auth`, `emails`, `partials` | 5 | ✅ Renomear (rótulo visível) |
| `app/Livewire/**` | 7 | ⚠️ Só mensagens `dispatch('notify', ...)` e textos de erro |
| `app/Models/**`, `app/Services/**`, `app/Exports/**` | 9 | ⚠️ Só PHPDoc, comentário e string de export |
| `app/Console/Commands/**` | 3 | ⚠️ Só `$description` e saída de console |
| `routes/web.php` | 1 | ❌ **NÃO TOCAR** — é comentário de seção; a rota é `planos.index` |

### 3.2 O que é PROIBIDO renomear

Esta é a lista que impede o dano. Nenhum destes itens é "referência ao termo" — todos são
**identificadores de sistema**, e trocá-los quebra rota, query, migration e histórico de auditoria.

| Item | Valor atual | Motivo de não tocar |
|---|---|---|
| Tabela | `action_plan.tab_plano_de_acao` | Renomear tabela é DDL de risco em N instalações de cliente, e quebra toda FK e todo `$table` de Model |
| Schema | `action_plan` | Está no `search_path` de `config/database.php` |
| Coluna FK | `cod_plano_de_acao` | Referenciada por indicadores, entregas, RACI, riscos |
| Model | `App\Models\ActionPlan\PlanoDeAcao` | 9 Models `Auditable` gravam o nome da classe em `pei.tab_audit` |
| Rotas | `planos.index`, `planos.detalhes`, `planos.entregas`, `planos.responsaveis` | URLs em uso, e `route()` espalhado nas blades |
| Módulo na MATRIZ | `'planos'` no `CapacidadeResolver` | Renomear = acesso negado silencioso para todo perfil não-Super-Admin |
| Policy | `PlanoDeAcaoPolicy` | Resolução por convenção de nome do Laravel |
| Namespace | `App\Livewire\ActionPlan\*` | Referenciado em `routes/web.php` |

> 🔴 **Se um dia a estrutura for renomeada, será um projeto próprio**, com migration de renomeação
> testada contra base de cliente real, reescrita da MATRIZ e migração de `pei.tab_audit`.
> **Não é este trabalho.**

### 3.3 As variações do termo que o `grep` precisa cobrir

O termo aparece em pelo menos oito grafias. Uma varredura ingênua deixa metade para trás:

```
Plano de Ação      Planos de Ação      plano de ação      planos de ação
Plano de Ação ▸    PLANO DE AÇÃO       Plano de ação      Plano de Acao (sem cedilha/acento)
```

E há um caso que **não se resolve por substituição textual**: `Plano` sozinho, como em
"Selecione um Plano", "Detalhes do Plano", "Gestor Responsável do Plano". Esses exigem leitura
da frase, um a um.

### 3.4 O que muda de gênero

"Plano" é masculino; "Iniciativa" é feminino. Toda concordância vizinha muda:

| Antes | Depois |
|---|---|
| "**O** Plano de Ação **foi criado**" | "**A** Iniciativa **foi criada**" |
| "**Novo** Plano de Ação" | "**Nova** Iniciativa" |
| "**Nenhum** plano cadastrado" | "**Nenhuma** iniciativa cadastrada" |
| "**este** plano" | "**esta** iniciativa" |
| "**do** plano" | "**da** iniciativa" |

Substituição automática de "Plano de Ação" → "Iniciativa" produz **"O Iniciativa foi criado"** em
dezenas de telas. Este é o principal risco desta demanda.

---

## 4. Soluções avaliadas

### Opção A — `sed` global em todos os arquivos ❌
Rápido, e produz erro de concordância em massa, além de tocar identificador de sistema. **Viola
diretamente o mandamento nº 4 do CLAUDE.md** ("pedido em massa não suspende a cirurgia"), que
nasceu exatamente de um caso assim.

### Opção B — Arquivo de tradução (`lang/pt_BR/dominio.php`) e `__('dominio.iniciativa')` ⚠️
Correto em tese, centraliza o vocabulário e permite trocar de novo sem varredura. Mas exige tocar
os 54 arquivos de qualquer forma para inserir a chamada, **e** as blades passariam a depender de
um arquivo de idioma que hoje não existe no projeto. Ganho real só apareceria numa terceira
renomeação. Custo alto, benefício adiado.

### Opção C — Renomeação manual arquivo a arquivo, com revisão de concordância ✅ **RECOMENDADA**
54 arquivos, lidos e alterados individualmente, com `git diff` revisto por lote. É o que o
mandamento nº 4 exige. É mais lento — e é o único caminho que não quebra frase.

### Opção D — C + constante única para os rótulos de relatório
Complemento à C: os títulos de relatório e cabeçalhos de tabela (que a demanda 15 vai reutilizar)
ficam numa constante em `App\Services\Reports\`, para que 09 e 15 leiam do mesmo lugar.
**Recomendada como parte da onda 4, não desta.**

---

## 5. Plano de ação

### Fase 0 — Inventário congelado (antes de tocar em qualquer arquivo)
1. Gerar `documentacao/melhorias/anexos/01-inventario-plano-de-acao.tsv` com
   `arquivo · linha · trecho · classificação`, onde classificação ∈
   `{RÓTULO, MENSAGEM, COMENTÁRIO, IDENTIFICADOR-NÃO-TOCAR}`.
2. O gestor revisa a coluna de classificação. **Só depois** começa a edição.

### Fase 1 — Blades de tela (22 arquivos)
3. Ordem: `livewire/plano-acao/*` → `livewire/entregas/*` → `livewire/p-e-i/*` → demais.
4. Regra por arquivo: ler o parágrafo inteiro, não a linha; ajustar artigo, adjetivo e particípio.
5. Ao final de cada pasta: `git diff --stat` e leitura completa do diff daquela pasta.

### Fase 2 — Blades de relatório (4 arquivos)
6. `relatorios/planos.blade.php`, `integrado.blade.php`, `executivo.blade.php`, `objetivos.blade.php`.
7. Aqui o rótulo de coluna passa a ser exatamente **"Iniciativas"**, alinhado ao modelo da PR.

### Fase 3 — Mensagens de PHP (7 arquivos Livewire)
8. Apenas strings de `dispatch('notify', ...)`, `$this->successMessage`, `$this->errorMessage`
   e mensagens de validação customizadas.
9. **Nenhuma** assinatura de método, nome de propriedade ou chave de array muda.

### Fase 4 — Comentários e PHPDoc (12 arquivos)
10. Comentário desalinhado do vocabulário confunde o próximo leitor. Baixo risco, faz-se por último.

### Fase 5 — Menu e navegação
11. `resources/views/navigation-menu.blade.php` — **arquivo de infraestrutura global**
    (CLAUDE.md). Alterar **só** o texto do item de menu, e informar o gestor no resumo.

### Fase 6 — Verificação
12. `php artisan view:clear && php artisan config:clear`
13. `grep -rni "plano de aç" resources/ app/` — o que sobrar precisa ter justificativa escrita.
14. `grep -rniE "(o|um|novo|este|do|ao) (a )?iniciativa" resources/` — caça a erro de concordância.
15. Teste de fumaça manual nas 6 telas do módulo.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R1.0** Inventário | TSV com 54 arquivos classificados | Gestor aprova a coluna de classificação | — |
| **R1.1** Telas | 22 blades renomeadas | Nenhuma ocorrência de "Plano de Ação" nas telas do módulo; zero erro de concordância no diff | R1.0 |
| **R1.2** Relatórios | 4 blades renomeadas | Cabeçalho "Iniciativas" idêntico ao modelo da PR (p. 28) | R1.1 |
| **R1.3** Mensagens | 7 componentes Livewire | Toda mensagem de sucesso/erro no feminino correto | R1.1 |
| **R1.4** Comentários | 12 arquivos PHP | `grep` limpo em `app/` para o termo antigo, exceto identificadores | R1.3 |
| **R1.5** Navegação | 1 blade global | Gestor avisado explicitamente da alteração em arquivo global | R1.1 |
| **R1.6** Teste de regressão | Teste Pest que cita o rótulo | Suíte verde | R1.5 |

**Marco de saída:** nenhuma tela, relatório ou mensagem exibe "Plano de Ação"; nenhuma rota,
tabela, coluna ou classe mudou de nome; `git diff --name-only` contém apenas os 54 arquivos
inventariados.

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 01-B01 | Script `grep` que gera o TSV do inventário com as 8 grafias | P | — | O TSV tem ≥54 linhas de arquivo distintas |
| 01-B02 | Classificar as ocorrências e submeter ao gestor | M | B01 | Aprovação registrada |
| 01-B03 | Renomear `livewire/plano-acao/*` (5 arquivos) | M | B02 | Diff lido linha a linha |
| 01-B04 | Renomear `livewire/entregas/*` (4 arquivos) | P | B02 | idem |
| 01-B05 | Renomear `livewire/p-e-i/*` (7 arquivos) | M | B02 | idem |
| 01-B06 | Renomear demais blades de tela (6 arquivos) | P | B02 | idem |
| 01-B07 | Renomear `resources/views/relatorios/*` (4 arquivos) | M | B03–B06 | Cabeçalho confere com p. 28 do modelo |
| 01-B08 | Renomear mensagens em `app/Livewire/**` (7 arquivos) | M | B02 | Nenhuma assinatura alterada |
| 01-B09 | Renomear PHPDoc e comentários (12 arquivos) | P | B08 | `grep` em `app/` limpo |
| 01-B10 | Item de menu em `navigation-menu.blade.php` | P | B03 | Alerta ao gestor no resumo (arquivo global) |
| 01-B11 | **Guarda:** teste Pest afirmando que `/planos` renderiza "Iniciativas" e não "Plano de Ação" | M | B03 | Teste falha se alguém reverter |
| 01-B12 | **Guarda:** teste afirmando que a rota `planos.index` e o módulo `'planos'` da MATRIZ continuam existindo | P | B02 | Teste falha se alguém renomear identificador |
| 01-B13 | Constante única de rótulos de relatório (preparo para 09 e 15) | M | B07 | 09 e 15 leem da constante |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. `grep -rni "plano de aç\|plano de ac" resources/ app/` — a saída precisa conter **apenas**
   ocorrências que eu justifique por escrito.
2. `git diff --name-only` comparado ao TSV do inventário — **nenhum arquivo fora da lista**.
3. `git diff` completo relido procurando (a) identificador removido, (b) erro de concordância.
4. `php artisan route:list | grep planos` — as 4 rotas continuam lá.
5. `php artisan test --filter=Plano` e os testes de `tests/Feature/Authorization/`.
6. Abrir as 6 telas do módulo com `view:clear` feito antes.

**Se a suíte não puder rodar** (banco de teste na porta 5434 está fora do ar), isso é declarado ao
gestor como verificação faltante — não é maquiado de "pronto".

---

## 9. O que NÃO foi verificado nesta análise

- **Quantas ocorrências** existem dentro de cada um dos 54 arquivos (só a lista de arquivos foi contada)
- Se há o termo em **dado do banco** (nome de relatório salvo, template, `system_settings`) —
  exige leitura no banco com autorização
- Se há o termo em **JS** (`resources/js/`) — a varredura cobriu `views`, `app` e `routes`
- Se há o termo em **documentação de artefato** (`documentacao/artefatos/`) — fora do escopo pedido
