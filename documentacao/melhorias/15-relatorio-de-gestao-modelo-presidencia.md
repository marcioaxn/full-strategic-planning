# 15 — Relatório de Gestão nos moldes da Presidência da República (DOCX + PDF)

> **Tema:** F — Relatórios como produto · **Tipo:** Funcionalidade nova
> **Impacto:** 🔴 **o entregável de maior visibilidade** · **Esforço:** o maior de todo o ciclo
> **Depende de:** [01](01-renomear-plano-de-acao-para-iniciativas.md), [09](09-reconstrucao-ui-dos-relatorios.md)
> **Verificado em:** 05/09/2026 · **Modelo analisado:** 198 páginas, extraídas e renderizadas

---

## 1. O pedido

> "Este arquivo é um modelo de Relatório de Gestão da Presidência da República. Ele quer que na
> parte de relatórios tenha o [Relatório de Gestão] com a saída em docx e em PDF. Ele quer que o
> relatório que o sistema irá emitir seja idêntico ao modelo (…). E para que ele não se frustre com
> o resultado é importante entregar dois tipos de relatório: um que será idêntico ao modelo; e o
> outro que será feito por ti. Na estrutura do Governo sempre há mudanças de autoridades e nesse
> caso é necessário que antes de gerar o relatório o processo vá em determinadas URLs e nesse caso
> procure quais URLs que o projeto pode capturar os dados, as fotos e outros dados para compor o
> relatório."

---

## 2. Como o modelo foi analisado

Não foi lido "por alto". O PDF foi aberto de verdade:

| Método | Resultado |
|---|---|
| `pdftotext -layout` (poppler) | 198 páginas, 778 KB de texto com layout preservado |
| Renderização por `pdf-to-img` (Node) | Páginas-chave em PNG, para medir grid, tipografia e cor |
| Amostragem de pixel (`pngjs`) | Paleta institucional extraída da página 27 |

Isso importa porque "idêntico ao modelo" só é verificável contra o modelo real.

---

## 3. 🔴 A constatação que muda tudo: o sistema pode gerar ~20% do relatório

O modelo tem **5 capítulos e 1 anexo**. A maior parte vem de fontes que **não existem neste
sistema e nunca vão existir** — Tesouro Gerencial, SIAFI, SIAPE, Comprasnet.

| Capítulo / seção | Páginas | Fonte real | O sistema gera? |
|---|---:|---|:---:|
| **1.1** Estrutura Organizacional | 2–4 | Organograma institucional | ⚠️ Parcial — há `Organization` hierárquica |
| **1.2** Identificação das Unidades | 5–7 | Cadastro institucional | ⚠️ Parcial |
| **1.3** Principais Normas | 8 | Normativos do órgão | ❌ |
| **1.4** Estrutura de Governança | 9–10 | Comitês, colegiados | ❌ |
| **1.5** Modelo de Negócios | 11 | Orçamento, força de trabalho, TIC | ❌ |
| **1.6** Cadeia de Valor | 12 | — | ✅ **Sim** — módulo existe |
| **1.7** Programas e ações orçamentárias | 13 | SIOP | ❌ |
| **1.8** Ambiente Externo | 14–26 | Análise + texto autoral | ⚠️ Parcial — SWOT/PESTEL |
| **2.1 Estratégia** | **27** | — | ✅✅ **Integralmente** |
| **2.2 Resultados Alcançados** | **28–60** | — | ✅✅ **Integralmente** |
| **2.3–2.5** Grandes Números | 61–63 | Infográfico curado | ❌ Não sistematizável |
| **3.1** Gestão Orçamentária | 65–69 | **Tesouro Gerencial** | ❌ |
| **3.2** Gestão de Custos | 70–71 | Sistema de Custos | ❌ |
| **3.3** Gestão de Pessoas | 72–82 | **SIAPE** | ❌ |
| **3.4** Licitações e Contratos | 83–93 | **Comprasnet** | ❌ |
| **3.5–3.8** Patrimônio, TIC, Sustentabilidade, Visitação | 94–107 | Diversas | ❌ |
| **3.9** Oportunidades e Perspectivas | 108–110 | Texto autoral | ❌ |
| **4.1 Gestão de Riscos** | **111–114** | — | ✅ **Sim** — módulo existe |
| **4.2–4.7** Integridade, Ética, Ouvidoria, Correição, Auditoria | 115–132 | Áreas próprias | ❌ |
| **5.1–5.14** Demonstrações Contábeis | 133–158 | **Tesouro Gerencial / SIAFI** | ❌ |
| **Anexo** | 159–198 | Unidades vinculadas | ❌ |

**Conta:** o sistema cobre, sozinho e por completo, as seções **2.1**, **2.2**, **1.6** e **4.1** —
cerca de **40 das 198 páginas**, e são justamente as que dão a espinha estratégica do documento.

### 3.1 Por que isso é a informação mais importante deste estudo

O gestor escreveu: *"para que ele não se frustre com o resultado"*. A frustração viria de gerar o
relatório e receber um documento com 80% de seções vazias — que é exatamente o que a demanda
[09](09-reconstrucao-ui-dos-relatorios.md) proíbe.

**Prometer "idêntico ao modelo" sem dizer isto é preparar a frustração, não evitá-la.** Dizer
agora é o que permite entregar algo de que ele goste.

---

## 4. O que o sistema gera com excelência — e é muito

### 4.1 Seção 2.1 Estratégia (página 27 do modelo)

A página renderizada mostra, e o sistema tem tudo:

| Elemento do modelo | Origem no sistema |
|---|---|
| Texto de contextualização do PEI | `PEI` + campo de texto editável |
| Faixa **MISSÃO** (amarela) | `MissaoVisaoValores.dsc_missao` |
| Faixa **VISÃO** (azul) | `MissaoVisaoValores.dsc_visao` |
| Bloco **VALORES** (verde), 8 valores com descrição | `Valor` |
| Bloco **OBJETIVOS FINALÍSTICOS** (borda vermelha), 9 objetivos | `Objetivo` por `Perspectiva` |
| Bloco **SUPORTE** (borda preta), 5 objetivos | idem |
| Link "Para mais informações sobre o PEI/PR" | Configurável |

**Essa página inteira é gerável, hoje, com o dado que o sistema já tem.**

### 4.2 Seção 2.2 Resultados Alcançados (páginas 28 e 34–37)

**Tabela 2.2.1 — Informações sobre o Planejamento Estratégico Integrado:**

| Identificador | Objetivo Estratégico | Descrição (resumida) | Principais Iniciativas |
|---|---|---|---|

**Tabela 2.2.2 — Resultados, Objetivos Estratégicos e Prioridades:**

| Objetivo | **Iniciativas** | Resultados |
|---|---|---|

🔴 **Nota que fecha o ciclo com a demanda [01](01-renomear-plano-de-acao-para-iniciativas.md):** o
documento oficial do cliente já usa **"Iniciativas"**. Sem aquela renomeação, o relatório gerado
diverge do modelo **na primeira tabela**. É por isso que 01 bloqueia 15.

### 4.3 Especificação visual medida

**Paleta** (amostragem de pixel da página 27):

| Papel | Hex |
|---|---|
| Verde institucional | `#54B347` / `#52BC4A` |
| Texto | `#2C2E35` |
| Cinza secundário | `#95969A` · `#595959` |
| Amarelo (faixa Missão) | `#EDC009` |
| Azul (faixa Visão) | `#3550A0` |
| Verde claro (fundo de bloco) | `#D3EED1` |
| Vermelho (borda Finalísticos) | `#FF361E` |

**Página:**
```
┌──────────────────────────────────────────────────────────────────┐
│ Presidência da República │ Relatório de Gestão 2025 │ Cap 02 …   │ ← 3 colunas, verde
├══════════════════════════════════════════════════════════════════┤ ← régua verde 2px
│  2. Resultados e Desempenho da Gestão     ← capítulo, verde 20px │
│  2.1 Estratégia                           ← seção, preto 13px    │
│  corpo em 2 ou 3 colunas, justificado, ~10,5px                   │
├══════════════════════════════════════════════════════════════════┤ ← régua verde, cortada
│ https://www.gov.br/planalto/pt-br │ PÁGINA 27 │ [redes sociais]  │
└──────────────────────────────────────────────────────────────────┘
```

**Tabelas:** cabeçalho em faixa `#2C2E35`, texto branco centralizado; corpo com borda fina cinza;
texto justificado dentro da célula; listas com marcador `·`.

---

## 5. As três decisões estruturais

### 5.1 DOCX — a capacidade não existe hoje

```
$ grep -n "phpword\|phpoffice" composer.json
(vazio)
```

O projeto tem `barryvdh/laravel-dompdf` (PDF) e `maatwebsite/excel` (planilha). **Não há nada que
gere `.docx`.**

| Opção | Avaliação |
|---|---|
| **`phpoffice/phpword`** | ✅ **Recomendada.** Padrão do ecossistema, gera DOCX nativo, controla estilo, cabeçalho, rodapé, numeração e tabela |
| HTML renomeado para `.doc` | ❌ Abre no Word e **não é DOCX**. Quebra ao editar, imprimir ou converter. É enganar o cliente |
| Template DOCX + substituição | ⚠️ `TemplateProcessor` do PHPWord. Bom para documento de estrutura fixa; ruim para tabela de N linhas |

⚠️ **Consequência inevitável:** PDF e DOCX são motores diferentes. **Manter os dois pixel-a-pixel
idênticos é impossível.** O que se garante é **mesmo conteúdo, mesma estrutura, mesma paleta,
mesma tipografia** — e isso precisa estar dito ao gestor antes, não depois.

### 5.2 As duas variantes

| | **Variante A — Réplica** | **Variante B — Autoral** |
|---|---|---|
| Estrutura | Numeração e títulos do modelo (1.1, 2.1, 2.2…) | Livre, orientada à leitura |
| Seções sem fonte no sistema | Aparecem com marcação **"a preencher pela unidade"** | **Não existem** |
| Público | Quem vai comparar com o modelo de 2025 | Quem quer ler o desempenho estratégico |
| Extensão | ~40 páginas geradas + esqueleto | ~25 páginas, todas com conteúdo |
| Risco | Frustração com o esqueleto vazio | Não parece o modelo |

**A Variante A é a que precisa de cuidado.** Ela contradiz a regra da demanda
[09](09-reconstrucao-ui-dos-relatorios.md) ("seção sem dado não existe") — e **deve contradizer**,
porque seu propósito é ser um **rascunho editável** que a unidade completa com o que vem de fora.

Para isso funcionar, a marcação precisa ser honesta e óbvia:

> **3.1 Gestão Orçamentária e Financeira**
> 🔲 *Seção a preencher pela unidade. Fonte: Tesouro Gerencial. Este sistema não coleta dado
> orçamentário.*

Assim o DOCX vira ferramenta de trabalho: a unidade abre no Word e completa. **É o que justifica
a saída em DOCX existir** — se fosse só para ler, PDF bastava.

### 5.3 🔴 As "determinadas URLs" — onde eu discordo do caminho, não do objetivo

O gestor pediu que o processo vá a URLs buscar dados e fotos de autoridades. O objetivo está
certo: **autoridade muda e o relatório não pode sair com o nome errado.** O caminho tem quatro
problemas concretos:

1. **SSRF.** `CLAUDE.md`, tabela de proibições: `'isRemoteEnabled' => true` no DomPDF é vetor de
   SSRF, e é item aberto em `.claude/seguranca-em-aberto.md` (nº 5). Buscar URL na geração do
   relatório é exatamente esse risco.
2. **Não há contrato.** `gov.br/planalto` é HTML de portal, não API. Raspagem quebra na primeira
   mudança de layout — e quebra **na hora de gerar o relatório**, que é o pior momento possível.
3. **Fotografia tem licença e uso.** As do modelo são creditadas ("Foto: Patrick Grosner/PR").
   Baixar imagem de portal e embutir em documento oficial exige autorização, não apenas acesso.
4. **É um produto multicliente.** URLs da Presidência não servem ao próximo cliente. Amarrar o
   gerador a `gov.br/planalto` é amarrar o produto a um cliente.

#### O que eu recomendo no lugar

**Cadastro de Autoridades no próprio sistema** — que hoje **não existe** (`grep` por
`autoridade|dirigente|titular` em Models e Livewire não retorna nada).

```
strategic_planning.tab_autoridade
  cod_autoridade · cod_organizacao · nom_autoridade · dsc_cargo
  num_ordem_protocolar · dte_inicio_exercicio · dte_fim_exercicio
  dsc_caminho_foto · dsc_credito_foto · dsc_fonte
```

Ganhos: histórico por período (o relatório de 2025 traz a autoridade **de 2025**, não a de hoje),
crédito de foto explícito, funciona para qualquer cliente, e não depende de rede externa na
geração.

⚠️ **Nome e foto são dado pessoal.** Aplicam-se o mandamento da criptografia (`CLAUDE.md`), o
`$auditExclude`, e a proibição de expor isso em tela pública
([07](07-mapa-estrategico-publico.md) §4).

**Importação assistida, se o gestor insistir nas URLs** — e é um bom meio-termo:
- Roda como comando manual (`autoridades:importar`), **nunca** na geração do relatório
- Allowlist estrita de domínios, configurável por cliente
- Apresenta o que encontrou e **exige confirmação humana** antes de gravar
- Nunca baixa imagem automaticamente; sugere a URL, a pessoa faz o upload

Isso atende ao objetivo do gestor sem colocar rede externa no caminho crítico.

---

## 6. Plano de ação

### Fase 0 — alinhar expectativa (antes de qualquer código)
1. Apresentar a tabela da §3 ao gestor. **Nada começa antes disso.**
2. Confirmar as duas variantes e a marcação "a preencher".
3. Decidir sobre as URLs (§5.3).
4. Confirmar que DOCX e PDF não serão pixel-idênticos.
5. Decidir a paleta: institucional do cliente (§4.3) ou do produto.

### Fase 1 — fundação
6. `phpoffice/phpword` no `composer.json`.
7. **Camada de conteúdo comum**: um serviço que monta a estrutura do relatório como **dado**
   (array/DTO), e dois renderizadores — PDF e DOCX — consumindo o mesmo dado. Sem isso, os dois
   formatos divergem na primeira manutenção.
8. Sistema de design do relatório de gestão, sobre [09](09-reconstrucao-ui-dos-relatorios.md).
9. Cadastro de Autoridades: migration, Model, CRUD, upload de foto com validação por MIME
   (`CLAUDE.md`, seção de upload), cifra dos campos de pessoa.

### Fase 2 — as seções que o sistema gera
10. **2.1 Estratégia** — a página 27 completa.
11. **2.2 Resultados** — Tabelas 2.2.1 e 2.2.2, com "Iniciativas".
12. **1.6 Cadeia de Valor**.
13. **4.1 Gestão de Riscos**.
14. **1.8 Ambiente Externo** (SWOT/PESTEL), parcial.

### Fase 3 — as duas variantes
15. Variante A: esqueleto completo do modelo, com marcação nas seções externas.
16. Variante B: só o que tem conteúdo, com narrativa própria.
17. Capa, contracapa, sumário com numeração automática.
18. Cabeçalho e rodapé conforme §4.3.

### Fase 4 — saída e verificação
19. Renderizador DOCX.
20. Renderizador PDF.
21. Comparação página a página com o modelo.
22. Registro no histórico ([10](10-historico-de-relatorios-e-textos-humanizados.md)).

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R15.0** Alinhamento | Tabela §3 aceita pelo gestor | Registrado aqui | — |
| **R15.1** Decisões | Variantes, URLs, paleta, DOCX≠PDF | Registradas | R15.0 |
| **R15.2** PHPWord | Dependência instalada | DOCX de teste abre no Word | R15.1 |
| **R15.3** Camada de conteúdo | Estrutura como dado, 2 renderizadores | Mesmo dado, 2 saídas | R15.2 |
| **R15.4** Design | Cabeçalho, rodapé, tabela, paleta | Confere com a p. 27 | [09](09-reconstrucao-ui-dos-relatorios.md) |
| **R15.5** Autoridades | CRUD + foto + histórico por período | Cifra e `$auditExclude` aplicados | R15.1 |
| **R15.6** Seção 2.1 | Estratégia completa | Comparação com a p. 27 | R15.4 |
| **R15.7** Seção 2.2 | Tabelas 2.2.1 e 2.2.2 | Cabeçalho igual à p. 28 | R15.6, [01](01-renomear-plano-de-acao-para-iniciativas.md) |
| **R15.8** Seção 1.6 | Cadeia de Valor | Visual | R15.4 |
| **R15.9** Seção 4.1 | Gestão de Riscos | Visual | R15.4 |
| **R15.10** Seção 1.8 | SWOT/PESTEL | Visual | R15.4 |
| **R15.11** Variante A | Esqueleto do modelo com marcação | Toda seção externa marcada e explicada | R15.10 |
| **R15.12** Variante B | Autoral, sem seção vazia | Nenhuma seção sem conteúdo | R15.10 |
| **R15.13** Capa e sumário | Numeração automática | Página do sumário confere | R15.11 |
| **R15.14** DOCX | Saída editável | Abre no Word; tabelas editáveis | R15.3, R15.12 |
| **R15.15** PDF | Saída final | Confere com o DOCX em conteúdo | R15.14 |
| **R15.16** Comparação | Lado a lado com o modelo | Gestor aprova | R15.15 |
| **R15.17** Importação assistida | Só se R15.1 decidir | Allowlist + confirmação humana | R15.5 |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 15-B01 | Apresentar a tabela §3 ao gestor | P | — | Aceite registrado |
| 15-B02 | Decidir variantes, URLs, paleta | P | B01 | Registrado |
| 15-B03 | Instalar `phpoffice/phpword` | P | B02 | DOCX de teste abre |
| 15-B04 | DTO/serviço da estrutura do relatório | G | B03 | Testável sem renderizar |
| 15-B05 | Renderizador PDF sobre o DTO | G | B04 | PDF gera |
| 15-B06 | Renderizador DOCX sobre o DTO | G | B04 | DOCX abre no Word |
| 15-B07 | Teste: os 2 renderizadores recebem o mesmo DTO | M | B06 | Verde |
| 15-B08 | Cabeçalho e rodapé do modelo | M | B05 | Confere com a p. 27 |
| 15-B09 | Estilo de tabela do modelo | M | B05 | Confere com a p. 28 |
| 15-B10 | Migration `tab_autoridade` | M | B02 | `migrate` limpo |
| 15-B11 | Model com cast `encrypted` + `$auditExclude` | M | B10 | Teste sobre o dado que sai |
| 15-B12 | CRUD de Autoridades | M | B11 | Tela |
| 15-B13 | Upload de foto com validação por MIME | M | B12 | Arquivo forjado é recusado |
| 15-B14 | Entrada de `autoridades` na `MATRIZ` | P | B12 | Perfil certo acessa |
| 15-B15 | Seção 2.1 Estratégia | G | B09 | Comparação com a p. 27 |
| 15-B16 | Tabela 2.2.1 | M | B15 | Cabeçalho igual |
| 15-B17 | Tabela 2.2.2 | M | B16 | Cabeçalho igual |
| 15-B18 | Seção 1.6 Cadeia de Valor | M | B09 | Visual |
| 15-B19 | Seção 4.1 Gestão de Riscos | M | B09 | Visual |
| 15-B20 | Seção 1.8 SWOT/PESTEL | M | B09 | Visual |
| 15-B21 | Variante A com marcação de seção externa | G | B20 | Toda seção marcada |
| 15-B22 | Texto da marcação aprovado pelo gestor | P | B21 | Registrado |
| 15-B23 | Variante B autoral | G | B20 | Nenhuma seção vazia |
| 15-B24 | Capa e contracapa | M | B08 | Visual |
| 15-B25 | Sumário com numeração automática | M | B21 | Páginas conferem |
| 15-B26 | Seleção de ano/ciclo na geração | M | B23 | Relatório de 2025 traz dado de 2025 |
| 15-B27 | Registrar no histórico | P | B26, [10](10-historico-de-relatorios-e-textos-humanizados.md) | Linha aparece |
| 15-B28 | Comparação página a página com o modelo | G | B26 | Gestor aprova |
| 15-B29 | Importação assistida de autoridades | G | B12, B02 | Allowlist + confirmação |
| 15-B30 | Teste: geração com PEI incompleto não quebra | M | B23 | Verde |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

O pedido tem um critério explícito — *"idêntico ao modelo"* — e um implícito — *"para que ele não
se frustre"*. Os dois precisam de verificação, e o segundo é o que decide.

1. **Comparação lado a lado, página a página**, do PDF gerado contra o modelo real, nas seções que
   o sistema gera. O modelo já está extraído e renderizado — **a comparação é objetiva**, não é
   impressão: cabeçalho, régua, paleta, estilo de tabela, cabeçalho de coluna.
2. **A Tabela 2.2.1 e a 2.2.2 com o cabeçalho exato** — `Identificador · Objetivo Estratégico ·
   Descrição (resumida) · Principais Iniciativas` e `Objetivo · Iniciativas · Resultados`.
   Se ainda disser "Planos de Ação", a demanda [01](01-renomear-plano-de-acao-para-iniciativas.md)
   não foi concluída e esta não pode ser dada por pronta.
3. **DOCX aberto no Word de verdade** — não "gerou sem erro". Tabelas editáveis, numeração
   funcionando, cabeçalho repetindo, fonte substituída corretamente. E **conteúdo idêntico ao PDF**,
   conferido seção a seção.
4. **15-B30, o teste que evita a frustração:** gerar as duas variantes com um PEI **incompleto** —
   sem SWOT, sem riscos, com um objetivo. A Variante B não pode ter seção vazia; a Variante A tem
   de marcar cada ausência com a explicação. É o cenário real do cliente que está começando.
5. **A conversa da §3, antes de tudo.** Se o gestor esperar 198 páginas geradas e receber 40, a
   entrega falhou mesmo estando tecnicamente perfeita. **Esta é a verificação que mais importa, e
   ela acontece antes da primeira linha de código.**
6. **Segurança:** `isRemoteEnabled` continua `false`; nenhuma requisição externa na geração;
   upload de foto validado por MIME; campos de pessoa cifrados com `$auditExclude`.

---

## 10. O que NÃO foi verificado

- **Se o gestor aceita gerar ~40 das 198 páginas** — a premissa central deste estudo
- Se a Presidência tem **modelo obrigatório do TCU** para Relatório de Gestão — o formato pode ser
  normatizado (IN TCU), o que muda a liberdade da Variante B
- Se há **API oficial** de dados abertos que cubra alguma seção do capítulo 3 ou 5
- Como o `phpoffice/phpword` se comporta com tabela de N linhas e quebra de página — **não testado**
- Se o DomPDF renderiza o layout de 3 colunas do modelo — não testado
- Fidelidade tipográfica: a fonte do modelo não foi identificada; foi medida a paleta, não a família
- Desempenho com dado real: o `integrado` já exige `ini_set` de recursos
  (`RelatorioController.php:136`); este será maior
- Se as fotos do modelo têm licença que permita reuso pelo sistema
- Quantas autoridades a Presidência teria de cadastrar, e se aceita fazê-lo manualmente

---

## 11. O que foi ENTREGUE (05/09/2026)

Executado, com teste verde. O que não foi feito está no item 12, nomeado.

### Código

| Arquivo | Papel |
|---|---|
| `app/Services/Reports/RelatorioGestao/EstruturaRelatorioGestao.php` | Monta o relatório como **dado**, não como HTML. Cinco capítulos, 27 seções na réplica, numeração conferida contra o sumário do modelo (páginas 3 a 6 do PDF oficial), incluindo 1.2 e as catorze seções do capítulo 5. Cada seção externa nomeia a fonte que a preenche. |
| `resources/views/relatorios/gestao/estilos.blade.php` | Sistema de design medido do modelo: paleta por amostragem de pixel da página 27, margens `96px 46px 96px 46px`, cabeçalho e rodapé de 64px (simetria por construção). |
| `resources/views/relatorios/gestao/relatorio.blade.php` + `secoes/` (9 arquivos) | Saída em PDF. |
| `app/Services/Reports/RelatorioGestao/RenderizadorDocx.php` | Saída em DOCX (PHPWord), consumindo **a mesma estrutura**. |
| `RelatorioController::gestaoPdf/gestaoDocx` + rotas `relatorios.gestao.{pdf,docx}` | Entrega, com autorização `modulo.exportar` e registro no Histórico. |
| `resources/views/livewire/relatorio/listar-relatorios.blade.php` | Card próprio em `/relatorios`, fora do catálogo, com os quatro botões. |

### As duas variantes

- **`?variante=replica`** — o esqueleto completo do modelo. As 20 seções que o
  sistema não alimenta aparecem marcadas, dizendo qual sistema as preenche
  (Tesouro Gerencial, SIAPE, Comprasnet, SIOP, SIAFI). É o documento que a
  unidade completa no Word.
- **`?variante=autoral`** (padrão) — só o que o PEI registra. Seção vazia é
  podada. É o documento que se publica sem completar nada.

### Achados corrigidos no caminho

1. **`$this->authorize()` não existia no controller.** O Laravel 12 não põe
   `AuthorizesRequests` no `Controller` base. Sem a trait, a chamada é fatal — e isso
   revelou um achado de segurança nas demais rotas do controller, registrado em
   `.claude/seguranca-em-aberto.md` (fora do versionamento: o repositório é público).
2. **Cabeçalho na capa.** O DomPDF repete todo `position: fixed` na página 1.
   Corrigido pintando as duas faixas de margem da primeira página.
3. **Terceira coluna do cabeçalho em branco** — `$tituloCorrente` nunca era
   definido. Passou a exibir a sigla, como no modelo.
4. **`A Gabinete de Segurança Institucional`** — artigo definido fixo no Blade,
   com o nome do órgão vindo do cliente. Removido.
5. **Seção interna vazia saía como título e nada.** Passou a dizer o que falta,
   nos dois formatos.
6. **O guarda de vocabulário lia comentário.** `VocabularioIniciativasTest`
   varria o arquivo cru, então um comentário explicando a renomeação era
   acusado de ser o rótulo antigo. Corrigido no **guarda**: `textoVisivel()`
   remove comentário Blade e PHP antes de casar. Provado que continua pegando
   rótulo real.

### Verificação

`php artisan test tests/Feature/Reports/RelatorioGestaoTest.php` — **14 testes,
135 asserções, verde**. Suíte inteira: **203 passando, 13 skipped, 0 falhando**.

Os testes provam: PDF começa com `%PDF-`; DOCX abre como zip com
`word/document.xml`; os cabeçalhos das Tabelas 2.2.1 e 2.2.2 são os do modelo;
a réplica traz 1.1–5.14 com fonte em toda seção externa; a autoral não traz
nenhuma seção vazia; o ciclo é o vigente no exercício relatado; gera com PEI
pela metade e sem ciclo algum; quem não pode exportar não gera e nada é
registrado; **PDF e DOCX trazem exatamente os mesmos títulos de seção**.

Também gerado de verdade contra o banco de dev (GSI, ciclo PEI 2024-2027) e
inspecionado página a página, rasterizado em PNG.

---

## 12. O que NÃO foi feito, e por quê

| Pendência | Situação |
|---|---|
| **Cadastro de autoridades** (`tab_autoridade`) com campos de pessoa cifrados e `$auditExclude` | Não feito. O modelo abre com a mensagem do dirigente e a foto; hoje o relatório não tem essa página. |
| **Importação assistida por URL** (allowlist, comando manual, confirmação humana) | Não feito. Depende do cadastro acima. **Nunca deve rodar na geração do relatório** — seria SSRF acionável por quem clica em "gerar". |
| **Um achado de segurança no módulo de relatórios** | 🔴 **Aberto.** Detalhe e correção em `.claude/seguranca-em-aberto.md` — não aqui, porque este repositório é público. |
| Anexo do modelo (Tabela A.1.1) | Não feito. A seção 4.1 já usa o formato do anexo. |
| Seções 2.4 e 2.5 (Grandes Números por unidade) | Generalizadas em uma única 2.3. O número de unidades varia por cliente; replicar a divisão da Presidência não serviria aos demais. |
