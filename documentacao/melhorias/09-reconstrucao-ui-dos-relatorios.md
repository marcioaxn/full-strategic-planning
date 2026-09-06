# 09 — Reconstrução da UI dos relatórios

> **Tema:** F — Relatórios como produto · **Tipo:** Melhoria estruturante
> **Impacto:** 🔴 **alto** — é o artefato que chega ao ministro · **Risco de regressão:** médio
> **Depende de:** [01](01-renomear-plano-de-acao-para-iniciativas.md) · **Bloqueia:** [15](15-relatorio-de-gestao-modelo-presidencia.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "Todos os relatórios em `/relatorios` precisam ser reconstruídos em relação a UI. Nossos clientes
> são Especiais e precisam de produtos especiais. Nesse momento não há padronização no layout dos
> relatórios. Todos os detalhes precisam de uma nova análise como margens horizontais ou verticais e
> sempre optar pela simetria entre linha superior e inferior. Cores, textos humanizados, rodapé,
> cabeçalho. Se por exemplo o relatório vai mostrar a parte de Temas Norteadores, mas o cliente
> ainda não preencheu, não é necessário mostrar no relatório (…). Em Brasília/DF o nome Dossiê não
> significa algo bom e você colocou o nome de Dossiê para o primeiro relatório? É importante
> renomear esse relatório para algo melhor."

---

## 2. Diagnóstico honesto: a padronização existe, e está pela metade

O gestor disse "não há padronização". A leitura do código mostra algo mais específico e mais fácil
de corrigir: **há um sistema de design, ele é bom, e três relatórios ficaram fora dele — e um
quarto o inclui e o sobrescreve.**

### 2.1 O que já existe e funciona

`resources/views/relatorios/partials/estilos.blade.php` (119 linhas) — sistema de design compartilhado:

| Componente | Estado |
|---|---|
| Paleta declarada (`#1B408E` primary, `#1a3a5c` navy, `#e07b39` accent, `#2e8b57` success) | ✅ |
| Cabeçalho fixo repetido em todas as páginas (`.rpt-header`) | ✅ |
| Rodapé fixo com `counter(page)` / `counter(pages)` | ✅ |
| Faixa de filtros aplicados (`.rpt-filtros`) | ✅ |
| Cards de KPI com variantes semânticas | ✅ |
| Títulos de seção (`.secao-titulo`) | ✅ |
| Partials `cabecalho` e `rodape` parametrizados | ✅ |

Isso é uma base sólida. **A reconstrução não começa do zero — começa terminando o que foi começado.**

### 2.2 Os cinco defeitos, com linha e arquivo

#### Defeito 1 — três relatórios fora do sistema

| Arquivo | `@page` próprio | Consequência |
|---|---|---|
| `cadeia-valor.blade.php:7` | `margin: 1cm; size: a4 landscape` | Sem cabeçalho, sem rodapé, sem numeração |
| `rae.blade.php:7` | `margin: 1.5cm; size: a4 portrait` | Idem |
| `identidade_bkp_20260116.blade.php:7` | `margin: 1cm; size: a4 landscape` | **Arquivo de backup em pasta de produção** |

#### Defeito 2 — `identidade.blade.php` inclui o sistema e o sobrescreve

```blade
6:  @include('relatorios.partials.estilos')     ← @page { margin: 110px 35px 70px 35px; }
7:  <style>
9:      @page { size: a4 landscape; margin: 95px 28px 52px 28px; }   ← vence
```

O `<style>` posterior sobrescreve o `@page` do partial. O relatório usa o cabeçalho fixo
posicionado para uma margem de 110px numa página com margem de 95px. **A sobreposição é
silenciosa** — não dá erro, só sai torto.

#### Defeito 3 — 🔴 a assimetria vertical, que é exatamente o que o gestor apontou

| Origem | Topo | Direita | Base | Esquerda | Simétrico? |
|---|---:|---:|---:|---:|:---:|
| `partials/estilos.blade.php:3` | **110px** | 35px | **70px** | 35px | ❌ 40px de diferença |
| `identidade.blade.php:9` | **95px** | 28px | **52px** | 28px | ❌ 43px de diferença |
| `cadeia-valor.blade.php:7` | 1cm | 1cm | 1cm | 1cm | ✅ |
| `rae.blade.php:7` | 1,5cm | 1,5cm | 1,5cm | 1,5cm | ✅ |

Os dois relatórios "certos" na simetria são justamente os que estão fora do sistema de design.
A assimetria não é descuido: o cabeçalho ocupa 78px e o rodapé 38px, então as margens foram
esticadas para caber cada um. **A correção não é igualar os números — é redesenhar cabeçalho e
rodapé com a mesma altura**, e aí a margem fecha simétrica por construção.

#### Defeito 4 — supressão de seção vazia inconsistente **dentro do mesmo arquivo**

O gestor usou Temas Norteadores como exemplo. Esse caso específico **já está correto**
(`integrado.blade.php:113`). O problema é que o arquivo usa **dois padrões contraditórios**:

| Seção | Linha | Padrão | Resultado quando vazio |
|---|---:|---|---|
| Temas Norteadores | 113 | Título **dentro** do `@if` | ✅ Some por inteiro |
| PESTEL | 257 | Título **dentro** do `@if` | ✅ Some por inteiro |
| Partes Interessadas | 273 | Título **dentro** do `@if` | ✅ Some |
| Cenários Prospectivos | 292 | Título **dentro** do `@if` | ✅ Some |
| Aderência Institucional | 590 | Título **dentro** do `@if` | ✅ Some |
| Objetivos por ODS | 613 | Título **dentro** do `@if` | ✅ Some |
| **Integração com Instrumentos** | **160** | Título **fora** do `@if` | ❌ Título + "ainda não preenchido" |
| **Matriz SWOT** | **227** | Título **fora** do `@if` | ❌ Título + bloco vazio |

Oito seções, dois padrões. O cliente que não preencheu SWOT recebe uma página com o título
"Matriz SWOT" e um aviso de que não preencheu — num documento que vai ao ministro.

#### Defeito 5 — "Dossiê"

`listar-relatorios.blade.php:116`, `integrado.blade.php:5, 48, 70`,
`ReportGenerationService.php:656` (`Dossie_Estrategico_{SIGLA}_{ANO}.pdf`).

O gestor está certo sobre a conotação. Em Brasília, "dossiê" evoca investigação e vigilância, não
prestação de contas. É a primeira palavra que o ministro lê.

---

## 3. A régua: o que o modelo da Presidência ensina

O `RelatriodeGesto2025PReVPR31mar.pdf` (198 páginas) foi aberto e medido. Ele é o padrão que o
cliente já aceita — e é a referência mais defensável que temos.

### 3.1 Estrutura de página

```
┌──────────────────────────────────────────────────────────────────┐
│ Presidência da República   Relatório de Gestão 2025   Cap 02 …   │ ← 3 colunas
├══════════════════════════════════════════════════════════════════┤ ← régua verde 2px
│                                                                  │
│  2. Resultados e Desempenho da Gestão      ← título de capítulo  │
│  2.1 Estratégia                            ← título de seção     │
│                                                                  │
│  corpo em 2 ou 3 colunas, justificado                            │
│                                                                  │
├══════════════════════════════════════════════════════════════════┤ ← régua verde, interrompida
│ https://www.gov.br/planalto…      PÁGINA 27          [ícones]    │
└──────────────────────────────────────────────────────────────────┘
```

**Cabeçalho e rodapé têm a mesma linguagem e peso visual semelhante** — é assim que a simetria que
o gestor pede se obtém.

### 3.2 Paleta medida do modelo (amostragem de pixel da página 27)

| Cor | Hex | Uso no modelo |
|---|---|---|
| Verde institucional | `#54B347` / `#52BC4A` | Réguas, títulos de capítulo, números de seção |
| Texto | `#2C2E35` | Corpo |
| Cinza secundário | `#95969A` · `#595959` | Legendas, fonte, "PÁGINA n" |
| Amarelo | `#EDC009` | Faixa "MISSÃO" |
| Azul | `#3550A0` | Faixa "VISÃO", destaques |
| Verde claro | `#D3EED1` | Fundos de bloco |
| Vermelho | `#FF361E` | Borda "OBJETIVOS FINALÍSTICOS" |

> A paleta atual do sistema (`#1B408E` azul + `#e07b39` laranja) **não é errada** — é a identidade
> do produto. A decisão de qual paleta usar é do gestor: identidade do produto, ou identidade do
> cliente. Ver R9.1.

### 3.3 Padrão de tabela do modelo

Cabeçalho em faixa escura (`#2C2E35`), texto branco, centralizado; corpo com borda fina cinza;
células com respiro generoso; **texto justificado dentro da célula**; listas com marcador `·`.
Ver página 28 do modelo (Tabela 2.2.1).

---

## 4. Soluções propostas

### 4.1 Nome do relatório integrado

| Candidato | Avaliação |
|---|---|
| **Relatório Estratégico Integrado** | ✅ **Recomendado.** Neutro, institucional, descreve o conteúdo, e o arquivo vira `Relatorio_Estrategico_Integrado_SIGLA_ANO.pdf` |
| Panorama Estratégico Integrado | ✅ Bom. "Panorama" sugere visão ampla, sem carga negativa |
| Relatório Integrado do PEI | ⚠️ Preciso, mas exige que o leitor saiba o que é PEI |
| Prestação de Contas Estratégica | ⚠️ Alinha com a linguagem de controle, mas promete escopo contábil |
| Dossiê Estratégico Integrado | ❌ Atual |

**Recomendação: "Relatório Estratégico Integrado"** — e a decisão final é do gestor.

⚠️ O nome aparece em **5 lugares**, incluindo o nome do arquivo gerado. Trocar o nome do arquivo
muda o que o cliente já tem salvo — vale avisar.

### 4.2 Sistema de design único e obrigatório

**Passo 1 — cabeçalho e rodapé com a mesma altura.** É o que destrava a simetria:

```css
@page { margin: 92px 40px 92px 40px; }   /* simétrico nos quatro lados */

.rpt-header { position: fixed; top: -72px;    height: 62px; }
.rpt-footer { position: fixed; bottom: -72px; height: 62px; }
```

**Passo 2 — nenhum relatório define `@page` próprio.** Variação de orientação vira parâmetro:

```blade
@include('relatorios.partials.estilos', ['orientacao' => 'landscape'])
```

**Passo 3 — os três relatórios órfãos entram no sistema.** `cadeia-valor` e `rae` ganham cabeçalho,
rodapé e numeração. `identidade_bkp_20260116.blade.php` **é excluído** — mas só depois de
`grep -rn "identidade_bkp"` provar que nada o referencia.

**Passo 4 — `identidade.blade.php` perde o `<style>` que sobrescreve o `@page`.**

### 4.3 Componente de seção que se suprime sozinho

O jeito de acabar com o defeito 4 não é corrigir as duas seções — é tornar impossível errar:

```blade
<x-relatorio.secao titulo="Matriz SWOT" :quando="$swot->isNotEmpty()">
    ...
</x-relatorio.secao>
```

O componente não renderiza **nada** — nem título, nem espaço, nem quebra de página — quando
`:quando` é falso. Todas as seções de todos os relatórios passam a usá-lo.

**Regra de ouro:** *seção sem dado não existe.* Nunca "Não preenchido", nunca "Nenhum registro".
Um relatório que anuncia o que falta constrange quem o apresenta.

⚠️ **Uma exceção deliberada:** quando a ausência **é** a informação (nenhum risco crítico
identificado no período), isso se diz afirmativamente — *"Nenhum risco crítico identificado em
2026"* — e não como lacuna. Distinguir os dois casos é decisão editorial, seção a seção.

### 4.4 Textos humanizados

Cada relatório abre com um parágrafo que diz **o que é, de onde vem o número e a que período se
refere** — o requisito nº 1 do padrão do CEO (`CLAUDE.md`): rastreabilidade até a tabela e o
critério.

> **Sobre este relatório.** Consolida a execução do Planejamento Estratégico Institucional da
> **SECOM/PR** no ciclo **2024–2027**, com dados apurados até **31/12/2026**. O percentual de
> atingimento de cada objetivo é a média ponderada dos indicadores vinculados, pelos pesos
> definidos em cada perspectiva. As cores seguem os Graus de Satisfação configurados pela unidade.

E a régua do farol **impressa no rodapé da primeira página** — para o leitor saber o que "verde"
significa naquele documento, sem ter de confiar.

### 4.5 Capa

O modelo tem capa e contracapa. Uma capa institucional — organização, título, ciclo, data de
emissão, quem emitiu — eleva o documento e custa uma página.

---

## 5. Plano de ação

1. **Levantar o estado de cada um dos 11 relatórios** numa tabela: usa o sistema? tem `@page`
   próprio? tem capa? suprime seção vazia? tem texto de abertura?
2. **Decidir a paleta** com o gestor (§3.2).
3. **Decidir o nome** do relatório integrado (§4.1).
4. Redesenhar cabeçalho/rodapé com a mesma altura e fechar a simetria (§4.2, passo 1).
5. Parametrizar orientação; eliminar todo `@page` local.
6. Trazer `cadeia-valor` e `rae` para o sistema.
7. Confirmar que nada referencia `identidade_bkp_20260116` e excluí-lo.
8. Criar `<x-relatorio.secao>` e migrar **todas** as seções.
9. Texto de abertura em cada relatório + régua do farol no rodapé da capa.
10. Capa institucional.
11. Renomear "Dossiê" nos 5 pontos.
12. Gerar os 11 PDFs antes e depois e comparar página a página.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R9.0** Inventário | Tabela de estado dos 11 relatórios | Nenhuma célula vazia | — |
| **R9.1** Decisões | Paleta e nome definidos pelo gestor | Registrados aqui | R9.0 |
| **R9.2** Simetria | Cabeçalho e rodapé de mesma altura; `@page` simétrico | Medição da margem no PDF gerado | R9.1 |
| **R9.3** `@page` único | Nenhum relatório com `@page` local | `grep "@page"` só no partial | R9.2 |
| **R9.4** Órfãos | `cadeia-valor` e `rae` no sistema | Têm cabeçalho, rodapé e numeração | R9.3 |
| **R9.5** Limpeza | `identidade_bkp` excluído | `grep` prova que nada o referencia | R9.4 |
| **R9.6** Componente de seção | `<x-relatorio.secao>` | Seção vazia não deixa rastro | R9.2 |
| **R9.7** Migração | Todas as seções no componente | Nenhum `@if` de seção solto | R9.6 |
| **R9.8** Textos | Abertura + régua do farol | Gestor aprova cada um | R9.7 |
| **R9.9** Capa | Capa institucional em todos | Visual | R9.2 |
| **R9.10** Renomear | 5 pontos do "Dossiê" | `grep "Dossi"` limpo | R9.1 |
| **R9.11** Iniciativas | Vocabulário de [01](01-renomear-plano-de-acao-para-iniciativas.md) | Confere com a p. 28 do modelo | [01] |
| **R9.12** Comparação | 11 PDFs antes/depois | Nenhuma perda de conteúdo | R9.11 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 09-B01 | Inventário dos 11 relatórios | M | — | Tabela completa |
| 09-B02 | Decidir paleta com o gestor | P | B01 | Registrado |
| 09-B03 | Decidir o nome do integrado | P | — | Registrado |
| 09-B04 | Cabeçalho e rodapé de mesma altura | M | B02 | Medição no PDF |
| 09-B05 | `@page` simétrico nos 4 lados | P | B04 | Régua sobre o PDF |
| 09-B06 | Orientação como parâmetro do partial | M | B05 | Landscape e portrait corretos |
| 09-B07 | Remover `@page` de `identidade.blade.php` | P | B06 | `grep` |
| 09-B08 | `cadeia-valor` no sistema de design | M | B06 | Cabeçalho e numeração |
| 09-B09 | `rae` no sistema de design | M | B06 | idem |
| 09-B10 | Provar que `identidade_bkp` não é referenciado | P | — | `grep -rn` |
| 09-B11 | Excluir `identidade_bkp_20260116.blade.php` | P | B10 | Arquivo fora; relatórios geram |
| 09-B12 | `<x-relatorio.secao>` | M | B04 | B13 |
| 09-B13 | Teste: seção vazia não gera título nem espaço | M | B12 | Verde |
| 09-B14 | Migrar as 8 seções do `integrado` | M | B12 | PDF sem seção vazia |
| 09-B15 | Migrar as seções dos outros 10 relatórios | G | B14 | idem |
| 09-B16 | Texto de abertura por relatório | G | B15 | Gestor aprova |
| 09-B17 | Régua do farol no rodapé da capa | M | B16 | Confere com os Graus do PEI |
| 09-B18 | Capa institucional | M | B04 | Visual |
| 09-B19 | Renomear "Dossiê" nos 5 pontos | P | B03 | `grep "Dossi"` limpo |
| 09-B20 | Ajustar o nome do arquivo gerado | P | B19 | Download com o nome novo |
| 09-B21 | Vocabulário "Iniciativas" nos relatórios | M | [01] | Confere com o modelo |
| 09-B22 | Gerar os 11 PDFs antes/depois e comparar | G | B21 | Nenhuma perda |
| 09-B23 | Revisar o `{!! $rptIcon !!}` do cabeçalho | P | B04 | Origem do dado documentada |
| 09-B24 | Testes de fumaça: os 11 relatórios geram sem erro | M | B22 | Verde |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

Relatório é o caso em que "abri e pareceu bom" é a pior verificação possível — o defeito aparece
na página 12, na terceira condição de dado, no cliente que não preencheu SWOT.

1. **Os 11 PDFs, antes e depois, comparados página a página.** Reconstrução de UI é onde mais se
   perde conteúdo sem perceber. Nenhuma seção pode sumir por acidente — só por estar vazia.
2. **A matriz de cenários de dado**, que é o que responde ao pedido do gestor: gerar cada relatório
   com (a) PEI completo, (b) PEI recém-criado com só identidade preenchida, (c) PEI com metade dos
   módulos. Em nenhum dos três pode aparecer título de seção sem conteúdo.
3. **A simetria, medida, não estimada:** abrir o PDF gerado e conferir que a distância do topo do
   cabeçalho à borda é igual à da base do rodapé à borda. É a queixa literal do gestor.
4. **`grep "@page" resources/views/relatorios/`** — só pode haver uma ocorrência, no partial.
5. **`grep -rn "Dossi"`** — vazio.
6. **Cabeçalho de tabela confere com a página 28 do modelo** — é o que a demanda
   [15](15-relatorio-de-gestao-modelo-presidencia.md) vai reaproveitar.
7. **Antes de excluir `identidade_bkp`:** `grep -rn "identidade_bkp"` em todo o projeto. Excluir
   arquivo sem provar que ninguém o referencia é o que o `CLAUDE.md` proíbe.

---

## 9. O que NÃO foi verificado

- **Nenhum PDF foi gerado nesta análise.** Todo o diagnóstico veio da leitura do Blade e do CSS.
  Como o DomPDF renderiza `position: fixed` com margem assimétrica é coisa que só o PDF responde
- Os outros 10 relatórios não tiveram as seções auditadas uma a uma — só o `integrado`
- Se `cadeia-valor` e `rae` estão em uso ou são código morto como o `identidade_bkp`
- Se algum cliente depende do nome de arquivo `Dossie_Estrategico_*.pdf` em rotina própria
- Se `ReportGenerationService` monta HTML fora das Blades
- Se os relatórios em Excel (`maatwebsite/excel`) têm o mesmo problema de padronização — o pedido
  citou `/relatorios`, e há 4 rotas de Excel ali
- A performance do `integrado` (636 linhas de Blade, `ini_set` de recursos em
  `RelatorioController.php:136`) sob dado real de cliente grande
