# 04 — A ordem das perspectivas mente para o cliente

> **Tema:** C — Honestidade metodológica da interface · **Tipo:** Correção
> **Impacto:** médio-alto (é a tela que define a leitura do mapa inteiro) · **Risco de regressão:** baixo
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "No(a) Modal [Nova Perspectiva] em `/pei/perspectivas` é necessário adequar a parte
> [Visualização no Mapa:] pois confunde o cliente uma vez que no BSC as perspectivas são dispostas
> de baixo para cima. O mesmo ajuste (…) precisa ser aplicada na tabela [Perspectivas Cadastradas].
> O mesmo ajuste precisa ser aplicado em `/objetivos`."

---

## 2. O problema, dito na forma exata

Não é um problema de gosto visual. **Três lugares do sistema representam a mesma ordem de três
maneiras diferentes, e uma delas é uma imagem que nunca muda.** O cliente compara os três e conclui
que o sistema está errado — ou pior, entende o BSC ao contrário.

### 2.1 As três representações, com evidência

| Onde | Código | Comportamento |
|---|---|---|
| **Mapa Estratégico** (`/pei/mapa`) | `MapaEstrategico.php:130` → `orderBy('num_nivel_hierarquico_apresentacao', 'desc')` | Maior número **em cima**. Ordem 1 fica na base. ✅ Correto no BSC |
| **Tabela "Perspectivas Cadastradas"** | `ListarPerspectivas.php:149` → `ordenadoPorNivel()` = `orderBy(...)` **ASC** | Ordem 1 aparece na **primeira linha**, no topo da tabela. ❌ Inverso do mapa |
| **Preview "Visualização no Mapa"** (modal) | `listar-perspectivas.blade.php:549-559` | **Imagem fixa**: sempre "Topo do Mapa" ⟶ *sua perspectiva* ⟶ "Base do Mapa" |

### 2.2 O preview é estático — este é o defeito central

```html
<p class="small text-muted mb-2">Visualização no Mapa:</p>
<div class="d-flex flex-column gap-1 align-items-center">
    <div class="... bg-primary ..." style="opacity: 0.3;">Topo do Mapa</div>
    <i class="bi bi-arrow-down text-muted"></i>
    <div class="... gradient-theme ...">{{ $dsc_perspectiva ?: 'Sua Perspectiva' }}</div>
    <i class="bi bi-arrow-down text-muted"></i>
    <div class="... bg-secondary ..." style="opacity: 0.3;">Base do Mapa</div>
</div>
```

**Não há uma única referência a `$num_nivel_hierarquico_apresentacao` neste bloco.** Digite ordem
1, 3 ou 9: o desenho é idêntico, sempre com a perspectiva no meio. O rótulo promete
"Visualização no Mapa" e entrega um enfeite.

Isso é literalmente o que o mandamento nº 6c do `CLAUDE.md` proíbe:

> *"Um botão que diz 'Abrir link' mas dispara o download de um arquivo mente para quem clica."*

### 2.3 A legenda diz o certo, e a tabela logo abaixo contradiz

`listar-perspectivas.blade.php:544`:

> `1 = Nível mais baixo (base do mapa)`

A frase está **correta**. Mas na tabela "Perspectivas Cadastradas", a perspectiva de ordem 1
aparece na **primeira linha**. O cliente lê de cima para baixo: `Aprendizado (1)`,
`Processos (2)`, `Financeira (3)` — e conclui que Aprendizado está no topo do mapa. No mapa, está
na base.

### 2.4 O mesmo em `/objetivos`

`ListarObjetivos.php:411-412` ordena `cod_perspectiva`, depois `num_nivel_hierarquico_apresentacao`
— **ascendente**, e o primeiro critério é o UUID da perspectiva, que não tem significado nenhum
para o cliente. O agrupamento por perspectiva na tela de objetivos não segue nem a ordem do mapa,
nem a ordem da tabela de perspectivas: segue a ordem alfabética de um UUID.

---

## 3. Por que o BSC é de baixo para cima (a explicação que falta na tela)

O Balanced Scorecard tem uma **cadeia de causa e efeito ascendente**. Cada camada é o meio para a
de cima:

```
   ┌──────────────────────────────────────┐   ordem 4  (topo)
   │ Financeira / Resultados p/ Sociedade │   ← o FIM
   └──────────────────▲───────────────────┘
   ┌──────────────────┴───────────────────┐   ordem 3
   │ Clientes / Destinatários             │
   └──────────────────▲───────────────────┘
   ┌──────────────────┴───────────────────┐   ordem 2
   │ Processos Internos                   │
   └──────────────────▲───────────────────┘
   ┌──────────────────┴───────────────────┐   ordem 1  (base)
   │ Aprendizado e Crescimento            │   ← o MEIO
   └──────────────────────────────────────┘
```

Lê-se: *investimos em pessoas e conhecimento (1) **para** executar melhor os processos (2)
**para** entregar valor ao destinatário (3) **para** alcançar o resultado institucional (4).*

O número não é "prioridade" nem "importância" — é **posição na cadeia causal**. Nenhuma tela diz
isso hoje.

> No setor público a camada financeira frequentemente cede o topo para **Sociedade / Resultados
> para o Cidadão**. O sistema não deve impor os quatro nomes clássicos — deve deixar claro que
> **maior número = mais perto do resultado final**.

---

## 4. Soluções avaliadas

### Opção A — Inverter a ordenação da tabela para `DESC` ⚠️
Uma linha, e alinha tabela com mapa. Mas tabela ordenada decrescente por um número é
contraintuitivo em si, e não resolve o preview estático nem explica o porquê. **Insuficiente
sozinha.**

### Opção B — Preview dinâmico + tabela alinhada + legenda explicativa ✅ **RECOMENDADA**
Ataca as três representações de uma vez:
1. O preview passa a desenhar **a pilha real**, com todas as perspectivas do PEI e a que está
   sendo editada em destaque na posição correta.
2. A tabela ordena `DESC` e ganha uma indicação visual de que a primeira linha é o topo do mapa.
3. Uma frase explica a cadeia causal, uma vez, onde o cliente decide o número.

### Opção C — Trocar o campo numérico por arrastar-e-soltar 🔵 **Evolução futura**
O cliente reordena a pilha arrastando; o sistema calcula os números. Elimina a classe inteira de
problema — o cliente nunca mais digita "ordem". Custo maior (Alpine + persistência de reordenação
em lote + recálculo de todas as perspectivas do PEI). **Backlog, não esta entrega.**

### Opção D — Trocar o rótulo "Ordem" por "Nível na cadeia de valor" ✅ **complemento barato**
A palavra "Ordem" sugere sequência de leitura. "Nível" sugere altura. Custo: 3 rótulos.

---

## 5. O desenho recomendado

### 5.1 Preview dinâmico (substitui o bloco estático)

```
  Visualização no Mapa Estratégico
  ┌────────────────────────────────────┐  ← topo: resultado final
  │  4  Financeira                     │
  ├────────────────────────────────────┤
  │  3  Clientes                       │
  ├────────────────────────────────────┤
  │ ►2  Sua Perspectiva          ◄     │  ← destacada, na posição real
  ├────────────────────────────────────┤
  │  1  Aprendizado e Crescimento      │
  └────────────────────────────────────┘  ← base: onde a estratégia começa
        ▲ cada nível sustenta o de cima
```

Requisitos:
- Renderiza **todas** as perspectivas do PEI, ordenadas `DESC`, com a editada em destaque.
- Reage a `wire:model.live` do campo de ordem: mudar o número **move o bloco**.
- Se o número colide com uma perspectiva existente, avisa antes de salvar.

### 5.2 Tabela "Perspectivas Cadastradas"

- Ordenação `DESC` (topo do mapa na primeira linha).
- Cabeçalho `Ordem` → **`Nível`**.
- Marcador de extremidade: `▲ topo do mapa` na primeira linha, `▼ base do mapa` na última.

### 5.3 Tela `/objetivos`

- Trocar `orderBy('cod_perspectiva')` por ordenação pela perspectiva **`DESC`**, para que o
  agrupamento na tela siga a mesma leitura do mapa.
- ⚠️ Verificar se algum outro ponto depende da ordem atual antes de trocar.

### 5.4 Texto humanizado (uma vez, no modal)

> **Como o mapa é lido:** as perspectivas se empilham de baixo para cima. A de **nível 1** fica na
> **base** — é onde a estratégia começa. A de nível mais alto fica no **topo** — é o resultado que
> a organização entrega. Cada nível sustenta o de cima.

Mesma explicação, resumida, como `title`/tooltip no cabeçalho `Nível` da tabela.

---

## 6. Plano de ação

1. Levantar **todos** os pontos que ordenam perspectiva:
   `grep -rn "num_nivel_hierarquico_apresentacao\|ordenadoPorNivel" app/ resources/`
   Cada um recebe decisão explícita: ASC, DESC ou indiferente. **Nenhum fica sem decisão.**
2. Substituir o bloco do preview por um dinâmico.
3. Ordenação `DESC` + rótulo `Nível` + marcadores na tabela.
4. Texto humanizado no modal e tooltip na tabela.
5. Ajustar `/objetivos` — só depois de 1 confirmar que nada depende da ordem atual.
6. Conferir o relatório de objetivos (`resources/views/relatorios/objetivos.blade.php`): se ele
   lista por perspectiva, precisa da mesma ordem. **Divergência entre tela e PDF é exatamente o
   que destrói a confiança do CEO** (`CLAUDE.md`, seção do padrão do CEO, item 2).
7. `php artisan view:clear` e conferência visual das três telas + PDF.

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R4.0** Inventário | Lista de todo ponto que ordena perspectiva, com decisão | Nenhum ponto sem decisão escrita | — |
| **R4.1** Preview dinâmico | Bloco reage ao número digitado | Ordem 1 desenha na base; ordem N no topo | R4.0 |
| **R4.2** Tabela | `DESC` + `Nível` + marcadores de extremidade | Primeira linha da tabela = primeira linha do mapa | R4.0 |
| **R4.3** Texto | Explicação da cadeia causal no modal e tooltip | Cliente entende sem perguntar | R4.1 |
| **R4.4** `/objetivos` | Agrupamento na ordem do mapa | Mesma sequência da tela de perspectivas | R4.0 |
| **R4.5** Relatórios | PDF na mesma ordem | Comparação lado a lado tela × PDF | R4.4 |
| **R4.6** Teste | Asserção sobre a ordem renderizada | Verde | R4.2 |
| **R4.7** Arrastar-e-soltar | Opção C | — | Backlog futuro |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 04-B01 | Inventariar pontos de ordenação de perspectiva | P | — | Lista com decisão por ponto |
| 04-B02 | Preview dinâmico no modal de perspectiva | M | B01 | Inspeção com 3 ordens diferentes |
| 04-B03 | Aviso de colisão de ordem no preview | M | B02 | Duas perspectivas com ordem 2 → aviso |
| 04-B04 | Tabela `DESC` + rótulo `Nível` | P | B01 | Tela |
| 04-B05 | Marcadores `▲ topo` / `▼ base` na tabela | P | B04 | Tela |
| 04-B06 | Texto humanizado no modal | P | B02 | Revisão do gestor |
| 04-B07 | Tooltip no cabeçalho da tabela | P | B04 | Tela |
| 04-B08 | Ordenação de `/objetivos` | M | B01 | Sequência igual à de perspectivas |
| 04-B09 | Ordenação nos relatórios que listam por perspectiva | M | B08 | PDF confere com a tela |
| 04-B10 | Teste: tela de perspectivas renderiza maior nível primeiro | M | B04 | Verde |
| 04-B11 | **Teste de coerência entre módulos:** mapa, lista e relatório devolvem a mesma sequência | M | B09 | Verde — é a trava contra divergência |
| 04-B12 | Reordenação por arrastar-e-soltar | G | B10 | Futuro |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **A prova visual que fecha o pedido:** print da tabela, do preview e do mapa lado a lado. A
   sequência de nomes tem de ser **idêntica nos três**. É a única verificação que responde à
   queixa original ("confunde o cliente").
2. **Preview reage:** digitar 1, depois 3, depois 9 no campo, e ver o bloco mudar de posição. Se
   não muda, o defeito continua — só ficou mais bonito.
3. **04-B11**, o teste que pega regressão: um teste automatizado afirmando que
   `MapaEstrategico`, `ListarPerspectivas` e o relatório devolvem a mesma sequência. É a
   verificação que responde à pergunta do `CLAUDE.md` — *"que verificação automática teria pego
   isto?"*
4. `php artisan view:clear` **antes** de julgar qualquer tela.
5. Nenhum arquivo fora de perspectivas/objetivos/relatórios no `git diff --name-only`.

---

## 10. O que NÃO foi verificado

- **Se algum cliente já cadastrou perspectivas com ordem repetida ou com buracos** (1, 2, 5) — o
  preview dinâmico precisa se comportar bem nesses casos, e não foi medido
- Como o relatório `objetivos.blade.php` e o `integrado.blade.php` ordenam — não foram abertos
- Se `DetalharPerspectiva` e `DetalharObjetivo` exibem o número de ordem em algum lugar
- Se o dashboard executivo lista perspectivas em alguma ordem própria
- Se há dependência da ordem ASC em cálculo de peso ou consolidação — o inventário 04-B01 existe
  exatamente para responder isso **antes** de inverter qualquer coisa
