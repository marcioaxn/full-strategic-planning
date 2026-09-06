# 03 — Modal "Nova Atividade": largura e o tipo `Valores públicos`

> **Tema:** B — Vocabulários controlados · **Tipo:** Melhoria
> **Impacto:** médio · **Risco de regressão:** médio (o tipo novo muda o agrupamento da tela)
> **Depende de:** [16 — Padrão de seeder idempotente](16-padrao-seeders-idempotentes.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "O(A) Modal [Nova Atividade], para inserir uma nova atividade da Cadeia de Valor precisa ser um
> pouco mais larga. Além disso é necessário agregar mais um novo item para o campo [Tipo]. O novo
> item do Tipo é [Valores públicos]. Da mesma forma que o item anterior [2.] é necessário entrar no
> seeder inicial e um específico para os clientes que já fizeram a instalação [clone]."

São **duas** demandas independentes no mesmo modal. A primeira é de uma linha. A segunda **não é**.

---

## 2. Análise técnica

### 2.1 A largura — trivial e confirmada

`resources/views/livewire/p-e-i/cadeia-de-valor.blade.php:98`

```html
<div class="modal-dialog modal-dialog-centered">
```

Sem classe de tamanho, o Bootstrap 5.3 aplica `max-width: 500px`. É estreito para um modal com
campo de texto longo (`dsc_atividade` é `text`), select de tipo, select de perspectiva e ordem.

Correção: `modal-dialog modal-lg modal-dialog-centered` → `800px`.

> Os outros dois modais do mesmo arquivo (linhas 148 e 183 — "Novo Processo" e o de confirmação)
> **não** foram citados pelo gestor. Alargar os três "porque faz sentido" é justamente o que o
> `CLAUDE.md` chama de irresponsabilidade. Alterar só o pedido; apontar os outros no resumo.

### 2.2 🔴 O tipo novo NÃO é uma linha em seeder — e aqui está o porquê

O gestor pediu "da mesma forma que o item 2", assumindo que o tipo da Cadeia de Valor mora numa
tabela como o tipo de Iniciativa. **Não mora.**

| | Tipo de Iniciativa (demanda 02) | Tipo de Atividade (esta demanda) |
|---|---|---|
| Onde vive | Tabela `action_plan.tab_tipo_execucao` | **Constante PHP** `AtividadeCadeiaValor::TIPOS` |
| Valor | 3 linhas com UUID | `['Finalística', 'Suporte']` |
| Coluna no registro | FK `cod_tipo_execucao` | Texto livre `dsc_tipo` |
| Seeder resolve? | ✅ Sim | ❌ **Não há tabela para semear** |

Evidência:

```php
// app/Models/StrategicPlanning/AtividadeCadeiaValor.php:46
public const TIPOS = ['Finalística', 'Suporte'];
```

E a validação e o agrupamento da tela **repetem a lista, hardcoded, em mais três lugares**:

```php
// app/Livewire/StrategicPlanning/CadeiaDeValor.php
:26   'dsc_tipo' => 'Finalística',                                    // valor padrão do form
:71   $this->formAtividade = [... 'dsc_tipo' => 'Finalística' ...];   // reset do form
:96   'formAtividade.dsc_tipo' => 'required|in:Finalística,Suporte',  // ❌ validação hardcoded
:204  'finalisticas' => $atividades->get('Finalística', collect()),   // ❌ agrupamento hardcoded
:205  'suporte'      => $atividades->get('Suporte', collect()),       // ❌ agrupamento hardcoded
```

**Consequência prática:** acrescentar `'Valores públicos'` só à constante faz a opção aparecer no
combo, o cliente salvar — e **a atividade sumir da tela**, porque `render()` só monta dois grupos
e o terceiro não é lido por ninguém. Erro silencioso, do tipo que aparece semanas depois.

### 2.3 A pergunta que muda o desenho

**"Valores públicos" é um terceiro tipo de atividade, ou é outra coisa?**

Na cadeia de valor clássica, atividades se dividem em **finalísticas** (entregam valor ao
destinatário) e **de suporte** (sustentam as finalísticas). No setor público, "valor público" é
o **resultado** da cadeia — o que ela entrega à sociedade — e costuma aparecer como uma **faixa
ao final do diagrama**, não como uma terceira coluna de atividades ao lado das outras duas.

O modelo da Presidência confirma a distinção: o sumário do Relatório de Gestão 2025 traz
`1.5 Modelo de Negócios` e `1.6 Cadeia de Valor` como seções separadas.

| Leitura | O que significa | Consequência no código |
|---|---|---|
| **L1 — terceiro tipo de atividade** | Vira mais um grupo ao lado de Finalística e Suporte | Barato: um valor a mais + terceiro grupo no render |
| **L2 — faixa de resultado da cadeia** | É outra entidade, exibida ao final do diagrama | Caro: outra tabela, outra seção na tela e no relatório |

**Esta pergunta precisa ser respondida pelo gestor antes de escrever código.** O plano abaixo cobre
L1 (que é o que a letra do pedido diz), e a seção 4 registra o custo de L2.

---

## 3. Soluções avaliadas

### Opção A — Acrescentar à constante e pronto ❌
Faz a opção aparecer e a atividade desaparecer (seção 2.2). Descartada.

### Opção B — Constante + os três pontos hardcoded ⚠️
Funciona. Mantém o vocabulário espalhado por 5 lugares em 2 arquivos, e deixa a próxima adição
com a mesma armadilha. Aceitável só como paliativo.

### Opção C — Tabela de referência + seeder idempotente ✅ **RECOMENDADA**
Cria `strategic_planning.tab_tipo_atividade_cadeia_valor` (UUID, rótulo, ordem de exibição,
`softDeletes`), migra os valores de texto existentes e passa a alimentar combo, validação e
agrupamento a partir dela.

**Por que vale o custo:**
- É o único caminho que cumpre a letra do pedido — "entrar no seeder"; hoje **não há o que semear**
- Elimina os 3 pontos hardcoded, e com eles a armadilha do agrupamento
- Dá ordem de exibição (hoje o agrupamento é fixo no código)
- Alinha com a demanda 02 — um único padrão de vocabulário controlado no produto

**O custo honesto:** é migration + seeder + refatoração de `render()` + migração dos dados de
texto existentes. Não é o trabalho de meia hora que o pedido sugere.

### Opção D — C, entregue em duas versões ✅ **RECOMENDADA para o cronograma**
- **Versão N:** largura do modal (1 linha) + Opção B, para o cliente ter o tipo já
- **Versão N+1:** Opção C, com a refatoração completa

Entrega valor rápido sem fingir que a dívida não existe.

---

## 4. Se a resposta for L2 ("valor público" é resultado, não atividade)

O trabalho é outro e maior. Registrado aqui para não se perder:

- Entidade `ValorPublico` própria (`cod_pei`, `dsc_valor_publico`, `num_ordem`, vínculo opcional a objetivo)
- Faixa de destaque ao final do diagrama da cadeia, visualmente distinta das colunas de atividade
- Seção correspondente no relatório de cadeia de valor (`resources/views/relatorios/cadeia-valor.blade.php`)
- Campo `Tipo` do modal **fica como está**, com dois valores

Esforço estimado: **G** (mais de 4h), contra **M** da L1.

---

## 5. Plano de ação (assumindo L1)

### Versão N — imediato
1. `modal-dialog` → `modal-dialog modal-lg` em `cadeia-de-valor.blade.php:98`. Só esse modal.
2. Conferir em 1280px, 1024px e 768px que o modal não estoura a viewport.
3. Acrescentar `'Valores públicos'` a `AtividadeCadeiaValor::TIPOS`.
4. Trocar a validação hardcoded por derivação da constante:
   ```php
   'formAtividade.dsc_tipo' => ['required', Rule::in(AtividadeCadeiaValor::TIPOS)],
   ```
5. **Corrigir o agrupamento** em `CadeiaDeValor::render()` para iterar sobre `TIPOS`, em vez de
   nomear dois grupos fixos. Sem isso o passo 3 cria o bug da seção 2.2.
6. Ajustar a blade para renderizar N grupos.
7. Teste Livewire: criar atividade com cada um dos 3 tipos e afirmar que **todas** aparecem.

### Versão N+1 — o padrão
8. Migration criando `strategic_planning.tab_tipo_atividade_cadeia_valor`, com schema qualificado
   e coluna `num_ordem`.
9. Migration de dados: para cada `dsc_tipo` distinto já existente, criar a linha correspondente e
   preencher a nova FK. **Sem apagar `dsc_tipo`** nesta versão — coexistência primeiro.
10. `TipoAtividadeCadeiaValorSeeder` sobre `VocabularioControlado` ([16](16-padrao-seeders-idempotentes.md)).
11. Componente e blade passam a ler da tabela.
12. Versão N+2: remover a coluna `dsc_tipo`, depois de confirmado em base de cliente.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R3.0** Decisão | Gestor responde L1 ou L2 | Registrado neste arquivo | — |
| **R3.1** Largura | `modal-lg` no modal de atividade | Modal a 800px; os outros 2 modais intactos | — |
| **R3.2** Agrupamento genérico | `render()` itera sobre `TIPOS` | Teste R3.4 verde | — |
| **R3.3** Tipo novo | `Valores públicos` no combo | Atividade salva **e visível** na tela | R3.2 |
| **R3.4** Teste | Livewire cria e lista os 3 tipos | Verde | R3.2 |
| **R3.5** Tabela de referência | Migration + migração de dados | Nenhuma atividade perde o tipo | R3.4 |
| **R3.6** Seeder | `TipoAtividadeCadeiaValorSeeder` | Testes 16-B05..B09 verdes | [16](16-padrao-seeders-idempotentes.md), R3.5 |
| **R3.7** Leitura da tabela | Combo, validação e agrupamento vindos do banco | Nenhum literal `'Finalística'` fora do seeder | R3.6 |
| **R3.8** Relatório | Cadeia de valor no PDF com os 3 grupos | Confere com a tela | R3.7 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 03-B01 | Perguntar ao gestor: L1 ou L2 | P | — | Resposta registrada |
| 03-B02 | `modal-lg` no modal de atividade | P | — | Inspeção em 3 larguras |
| 03-B03 | Conferir que os outros 2 modais do arquivo não mudaram | P | B02 | `git diff` |
| 03-B04 | Generalizar o agrupamento em `render()` | M | — | B06 |
| 03-B05 | Generalizar a validação com `Rule::in(TIPOS)` | P | — | B06 |
| 03-B06 | Teste Livewire: 3 tipos criados e todos listados | M | B04, B05 | Verde |
| 03-B07 | Acrescentar `Valores públicos` à constante | P | B06 | Tela |
| 03-B08 | Migration da tabela de referência | M | B07 | `migrate` limpo em base nova e antiga |
| 03-B09 | Migration de dados dos `dsc_tipo` existentes | M | B08 | Contagem antes = depois |
| 03-B10 | `TipoAtividadeCadeiaValorSeeder` | M | 16-B02, B08 | Verde |
| 03-B11 | Componente e blade lendo da tabela | M | B10 | Sem literal de tipo no código |
| 03-B12 | Relatório de cadeia de valor com N grupos | M | B11 | PDF confere com a tela |
| 03-B13 | Remover coluna `dsc_tipo` (versão N+2) | M | B12 | Só após confirmação em base de cliente |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **O teste que pega o bug real:** criar atividade do tipo `Valores públicos` e afirmar que ela
   **aparece na listagem**. Um teste que só afirme que o registro foi salvo passa verde com a tela
   quebrada — é exatamente a armadilha que o `CLAUDE.md` descreve na regra nº 5 do topo.
2. **A largura:** abrir o modal em 1280/1024/768px e confirmar que não estoura.
3. **Os outros modais:** `git diff` de `cadeia-de-valor.blade.php` mostrando **uma** linha alterada
   na parte do modal.
4. **As duas populações** (após R3.6): base limpa e dump de cliente, como em [16](16-padrao-seeders-idempotentes.md).
5. **Contagem antes/depois** da migration de dados — nenhuma atividade pode ficar sem tipo.
6. `php artisan view:clear` antes de julgar a tela, e `php artisan test` no escopo.

---

## 9. O que NÃO foi verificado

- **Se "Valores públicos" é L1 ou L2** — a pergunta que mais muda o esforço, e ela é do gestor
- Quantas atividades existem hoje, e como se distribuem entre Finalística e Suporte
- Se a blade de listagem tem outros pontos que assumem exatamente dois grupos (só `render()` foi lido)
- Como o relatório `relatorios/cadeia-valor.blade.php` agrupa — não foi aberto nesta análise
- Se algum cliente já gravou `dsc_tipo` fora dos dois valores previstos (a coluna é texto livre)
