# 13 — Auditoria de UI/UX navegada no Chrome

> **Tema:** G — Qualidade transversal · **Tipo:** Melhoria
> **Impacto:** médio-alto (é a primeira tela que o cidadão vê) · **Risco de regressão:** baixo
> **Verificado em:** 05/09/2026 · **Método:** Chrome 100% real, dirigido por `puppeteer-core`

---

## 1. O pedido

> "Acesse `http://localhost/fs-v1/public/` por meio do Chrome e observe pontos de melhoria em UI e
> UX e execute."

---

## 2. Como esta auditoria foi feita

**O Chrome instalado na máquina foi de fato aberto e dirigido** — não é leitura de código.

| Item | Valor |
|---|---|
| Navegador | Chrome real (`C:\Program Files\Google\Chrome\Application\chrome.exe`), modo headless |
| Ferramenta | `puppeteer-core` (sem baixar Chromium) |
| Páginas | `/` (landing) e `/login` — as duas públicas |
| Resoluções | 1366×768, 1280×720, 768×1024 (tablet), 390×844 (celular) |
| Medido | Contraste WCAG calculado, alvos de toque, hierarquia de títulos, marcos ARIA, rótulos de formulário, erros de console, requisições falhas, tempo de carga, scroll horizontal |
| Capturas | 8 PNGs, incluindo página inteira em 1366px |

> ⚠️ **Só as telas públicas foram auditadas.** As telas internas exigem sessão autenticada — está
> no plano (13-B14), e é onde está a maior parte do sistema.

---

## 3. Achados medidos

### 3.1 O que está bom — e vale registrar

| Verificação | Resultado |
|---|---|
| Scroll horizontal | ✅ **Nenhum** nas 4 resoluções (1366, 1280, 768, 390) |
| `lang` do documento | ✅ `pt-BR` |
| Imagens sem `alt` | ✅ 0 de 0 |
| Campos sem rótulo | ✅ **0**, na landing e no login |
| Botões sem nome acessível | ✅ 0 no desktop |
| Links sem texto / `href="#"` | ✅ 0 |
| Requisições falhas | ✅ 0 |
| Assets do Vite | ✅ 200 nos três |

O responsivo está sólido. Não há aquele defeito clássico de estourar a viewport no celular.

### 3.2 🔴 A1 — Publicar "0%" num portal de transparência

A captura de 1366px mostra a página inteira publicando:

```
0% ATINGIMENTO GLOBAL   ·   2 PERSPECTIVAS   ·   1 OBJETIVO   ·   0 PLANOS   ·   0 INDICADORES
Processos Internos       0%
Aprendizado e Crescimento 0%   (1 objetivo · 1 abaixo de 50%)
```

A página se apresenta como **"Portal da Transparência"** (é o `<title>` e o texto sob a marca).
Um portal de transparência que estampa **0%** não comunica "ciclo em preenchimento" — comunica
**"este órgão não executou nada"**.

Tecnicamente o número está certo. Institucionalmente, é o pior recado possível — e é exatamente o
que o `CLAUDE.md` chama de desonestidade de interface pela via inversa: o número é verdadeiro e a
leitura que ele induz é falsa.

**Correção:** estado vazio explícito, em vez de zero.

> *"Ciclo 2024-2027 em fase de preenchimento. Os indicadores de desempenho serão publicados a
> partir do primeiro lançamento de evolução."*

E o painel de percentuais só aparece quando houver indicador com evolução lançada.

### 3.3 🔴 A2 — Informação de risco publicada sem login

A landing traz um card:

> **Gestão de Riscos** — Riscos identificados e monitorados no ciclo.
> ✅ **Nenhum risco crítico** — Riscos monitorados dentro do limite aceitável.
> 🔒 Detalhes completos disponíveis para usuários autenticados.

Confirma, com evidência visual, o que a demanda [07](07-mapa-estrategico-publico.md) §2 levantou
por leitura de código. O card é bem-feito e honesto sobre o que esconde — **mas ninguém decidiu
publicá-lo, e não há chave para desligar.**

Num órgão da Presidência da República, "nenhum risco crítico" e o seu inverso são informação de
gestão. Tratado em [07](07-mapa-estrategico-publico.md) R7.1.

### 3.4 🟠 A3 — Contraste abaixo do mínimo WCAG AA

Cinco ocorrências detectadas. **Três são falso positivo do meu método** — a heurística sobe o DOM
procurando `background-color` e não enxerga `background-image`/gradiente. Sou explícito sobre isso:

| Texto | Medido | Mínimo | Situação |
|---|---:|---:|---|
| `0%` (`#dee2e6` sobre `#f8fafc`), 12,5px | **1,24** | 4,5 | 🔴 **Confirmado** — cinza claríssimo sobre quase-branco |
| `Processos Internos` (branco sobre `#2e8b57`), 14,4px | **4,25** | 4,5 | 🔴 **Confirmado** — falta pouco, e é o verde do farol |
| `Módulo 03` (`#2e8b57` sobre `#e8f5ef`), 11,5px | **3,79** | 4,5 | 🔴 **Confirmado** |
| `Acesse o painel completo`, 25,6px | 1,05 | 3 | ⚠️ Falso positivo (faixa azul-marinho em gradiente) |
| `Guia Prático de PEI — GPPEI/MGI 2025`, 28px | 1,00 | 3 | ⚠️ Falso positivo (faixa laranja em gradiente) |

O primeiro é o mais grave: **é o número que o portal existe para mostrar**, e está quase
invisível. O segundo importa porque `#2e8b57` é o verde do farol — a mesma cor aparece nos
relatórios ([09](09-reconstrucao-ui-dos-relatorios.md) §2.1).

### 3.5 🟠 A4 — Alvos de toque abaixo de 44×44

| Elemento | Desktop | Celular |
|---|---|---|
| Links do menu (`Módulos`, `Funcionalidades`, `Guia GPPEI`) | 40px de altura | — |
| Botão de tema (sem rótulo) | 40×40 | **46×30** |
| `Área Restrita` | 173×40 | — |
| `Entrar no Sistema` (rodapé) | 109×**18** | 109×**18** |
| `Guia GPPEI` (rodapé) | 66×**18** | 66×**18** |
| **Total** | **7** | **3** |

Os links de rodapé com **18px de altura** são o pior caso — no celular, é metade do recomendado
pela WCAG 2.5.5 (44×44) e abaixo do mínimo do próprio Material Design (48).

### 3.6 🟠 A5 — Tipografia abaixo de 12px

| Tamanho | Ocorrências (desktop) | Ocorrências (celular) |
|---|---:|---:|
| 11,5px | 12 | 6 |
| 10,4px | 1 | 1 |
| 9,9px | 2 | 6 |
| 9,6px | 2 | — |
| **Total** | **17** | **13** |

**Seis elementos a 9,9px no celular.** Para o público de um órgão de Estado — que inclui gestores
seniores — isso é ilegível. E 9,6px em tela pública é indefensável.

### 3.7 🟡 A6 — Estrutura semântica e acessibilidade

| Verificação | Landing | Login |
|---|---|---|
| `<main>` | ❌ **0** | ❌ **0** |
| `<header>` | ❌ 0 | ❌ 0 |
| `<nav>` / `<footer>` | ✅ 1 / 1 | — |
| Link "pular para o conteúdo" | ❌ Ausente | ❌ Ausente |
| `<meta name="description">` | ❌ **Ausente** | ❌ Ausente |
| Hierarquia de títulos | ❌ `H1 H2 **H5 H5** H2 H3 H2 H3 H3 H3 H2 **H6**×9 H2 H3` | ✅ `H1 H2` |

Dois problemas concretos:
- **Sem `<main>` e sem skip link**, quem usa leitor de tela percorre a navegação inteira a cada
  página. Num portal público de órgão federal, acessibilidade não é opcional — é a
  **Lei 13.146/2015 (LBI)** e o **eMAG**.
- **A hierarquia pula de H2 para H5 e usa nove H6 seguidos.** Leitor de tela navega por níveis;
  saltar dois níveis quebra a navegação estrutural.
- **Sem `meta description`**, o resultado no Google fica a cargo do buscador.

### 3.8 🟡 A7 — Inconsistência de marca

| Onde | Diz |
|---|---|
| `<title>` | **Sistema PEI** \| Portal da Transparência |
| Marca no cabeçalho | **SPS** · Portal da Transparência |
| Rodapé | **Sistema de Planejamento Estratégico Institucional** |
| H1 | **PR** Planejamento Estratégico Institucional |

**Quatro nomes na mesma página.** Além disso, o H1 concatena a sigla da organização com o nome do
sistema — para um leitor de tela, sai `"PR Planejamento Estratégico Institucional"`, uma frase só.

### 3.9 🟡 A8 — Tempo de carga

| Página | Primeira carga | Cargas seguintes |
|---|---:|---:|
| Landing | 3.131 ms | ~2.010 ms |
| Login | 2.973 ms | 1.629 ms |

~2s em **localhost, sem latência de rede e com o banco na mesma máquina**. Em produção, com rede
e banco remoto, será pior. O cache de 5 minutos da landing já existe
(`Cache::remember('lp_dados_publicos', 300)`), então a carga não é consulta ao banco — é
renderização e assets.

Dois recursos externos bloqueiam a pintura: `fonts.bunny.net` e `cdn.jsdelivr.net`
(Bootstrap Icons). São também **dependência externa numa página pública de órgão de Estado** —
se o CDN cair ou for bloqueado pela rede do órgão, a tipografia e os ícones somem.

### 3.10 ℹ️ A9 — Um 404 não reproduzível

Um `404` apareceu no console **apenas na primeira execução** da landing. As três execuções
seguintes e as duas do login não o reproduziram, e todos os assets testados manualmente
devolveram 200. Fica registrado como **não reproduzido**, não como defeito.

---

## 4. Plano de ação

### Onda 1 — o que uma pessoa de fora vê hoje
1. Estado vazio no lugar de "0%" (A2).
2. Card de riscos atrás de chave — [07](07-mapa-estrategico-publico.md) R7.1 (A3).
3. Corrigir os 3 contrastes confirmados (A4).
4. Confirmar manualmente os 2 falso-positivos, com conta-gotas sobre a captura.

### Onda 2 — acessibilidade (obrigação legal)
5. `<main>` e `<header>` nas duas páginas.
6. Skip link.
7. Hierarquia de títulos sem saltos.
8. Separar a sigla da organização do H1.
9. Alvos de toque ≥ 44×44.
10. Piso tipográfico de 12px; 14px no celular.

### Onda 3 — marca e desempenho
11. Definir **um** nome e aplicá-lo nos quatro pontos.
12. `meta description` e Open Graph.
13. Avaliar servir fonte e ícones localmente.

### Onda 4 — o resto do sistema
14. **Repetir esta auditoria nas telas autenticadas** — dashboard, mapa, indicadores, iniciativas,
    entregas, riscos, relatórios. É onde o cliente passa o tempo, e não foi coberto.
15. Transformar o script numa verificação repetível.

---

## 5. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R13.0** Estado vazio | Sem "0%" público | Portal comunica "em preenchimento" | — |
| **R13.1** Riscos sob chave | [07](07-mapa-estrategico-publico.md) R7.1 | Card some por padrão | [07] |
| **R13.2** Contraste | 3 correções | Reexecução acusa 0 confirmados | — |
| **R13.3** Falso-positivo | 2 casos conferidos à mão | Registrado aqui | — |
| **R13.4** Marcos ARIA | `<main>`, `<header>`, skip link | Reexecução acusa `main: 1`, `skipLink: true` | — |
| **R13.5** Títulos | Sem salto de nível | `H1 H2 H3 …` sem pular | R13.4 |
| **R13.6** H1 | Sigla fora do H1 | Leitor de tela lê duas coisas | R13.5 |
| **R13.7** Alvos | ≥ 44×44 | Reexecução acusa 0 | — |
| **R13.8** Tipografia | Piso 12px / 14px celular | Reexecução acusa 0 abaixo | — |
| **R13.9** Marca | Um nome nos 4 pontos | Inspeção | — |
| **R13.10** Meta | `description` + Open Graph | Inspeção | R13.9 |
| **R13.11** Assets locais | Fonte e ícones sem CDN | Página completa com rede externa bloqueada | — |
| **R13.12** Telas internas | Auditoria autenticada | Relatório equivalente a este | R13.8 |
| **R13.13** Verificação repetível | Script versionado | Roda em qualquer máquina | R13.12 |

---

## 6. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 13-B01 | Estado vazio no lugar de "0%" | M | — | Captura |
| 13-B02 | Texto do estado vazio aprovado pelo gestor | P | B01 | Registrado |
| 13-B03 | Card de riscos sob chave | P | [07] | Some por padrão |
| 13-B04 | Contraste do `0%` (`#dee2e6`) | P | — | Reexecução |
| 13-B05 | Contraste de `Processos Internos` no verde | P | — | Reexecução |
| 13-B06 | Contraste de `Módulo 03` | P | — | Reexecução |
| 13-B07 | Conferir os 2 falso-positivos à mão | P | — | Registrado |
| 13-B08 | `<main>` e `<header>` na landing e no login | M | — | Reexecução |
| 13-B09 | Skip link | P | B08 | Reexecução |
| 13-B10 | Hierarquia de títulos sem salto | M | B08 | Reexecução |
| 13-B11 | Sigla fora do H1 | P | B10 | Inspeção |
| 13-B12 | Alvos de toque ≥ 44×44 | M | — | Reexecução |
| 13-B13 | Piso tipográfico | M | — | Reexecução |
| 13-B14 | Definir o nome do produto | P | — | Gestor decide |
| 13-B15 | Aplicar o nome nos 4 pontos | P | B14 | Inspeção |
| 13-B16 | `meta description` + Open Graph | P | B15 | Inspeção |
| 13-B17 | Servir fonte e ícones localmente | M | — | Página completa sem rede externa |
| 13-B18 | Auditar as telas autenticadas | G | B13 | Relatório equivalente |
| 13-B19 | Versionar o script de auditoria | M | B18 | Roda em outra máquina |
| 13-B20 | Auditoria de teclado (tab, foco visível, armadilha em modal) | M | B08 | Manual |
| 13-B21 | Auditoria de leitor de tela (NVDA) na landing | G | B10 | Manual |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 7. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

Esta auditoria já é a resposta à primeira metade do pedido ("acesse e observe"). Para a segunda
("execute"):

1. **Reexecutar o mesmo script depois das correções**, nas mesmas 4 resoluções, e comparar número
   a número: contraste confirmado 3 → 0; alvos 7 → 0; fontes 17 → 0; `main` 0 → 1;
   `skipLink` false → true. **É a mesma medida antes e depois — não é impressão.**
2. **Comparar as capturas** antes/depois em 1366 e 390. Correção de acessibilidade quebra layout
   com frequência.
3. **13-B07:** os dois falso-positivos precisam ser conferidos com conta-gotas sobre a captura.
   Reportá-los como defeito sem conferir seria repassar erro do meu método como erro do sistema.
4. **13-B20 e 13-B21:** nem tudo se mede por script. Navegação por teclado e leitor de tela real
   são manuais, e é onde a acessibilidade de verdade se prova.
5. **Bloquear `fonts.bunny.net` e `cdn.jsdelivr.net`** no navegador e recarregar. A página tem de
   continuar legível. É o teste que simula a rede restritiva de um órgão.
6. `php artisan view:clear` antes de qualquer medição.

---

## 8. O que NÃO foi verificado

- **Todas as telas autenticadas** — dashboard, mapa, indicadores, iniciativas, entregas, riscos,
  relatórios, admin. **É a maior parte do sistema**, e não foi tocada (13-B18)
- **Navegação por teclado** — ordem de tabulação, foco visível, armadilha de foco em modal
- **Leitor de tela real** (NVDA/JAWS) — o script mede estrutura, não experiência
- **Modo escuro** — há um botão de tema e ele não foi exercitado
- **Impressão** — o `@media print` das telas não foi verificado
- Os 2 contrastes marcados como falso positivo — **não confirmados**
- O 404 da primeira execução — **não reproduzido**
- Comportamento com dado real de cliente: a base de dev tem 2 perspectivas e 1 objetivo. Uma tela
  com 6 perspectivas e 40 objetivos pode se comportar de outra forma
- Desempenho sob rede real e banco remoto
