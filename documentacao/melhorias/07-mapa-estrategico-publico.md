# 07 — Mapa Estratégico navegável sem autenticação

> **Tema:** E — Acesso, transparência e governança · **Tipo:** Melhoria
> **Impacto:** alto · **Risco de regressão:** 🔴 **alto — é superfície de exposição pública**
> **Bloqueada por:** decisão institucional do gestor, não por código
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "É importante e necessário que na rota [home] ou [/] desde que o preenchimento tenha sido
> iniciado mostre o Mapa Estratégico da mesma forma como é mostrado em `/pei/mapa`, mesmo sem estar
> autenticado. (…) o cliente precisa navegar de forma plena no mapa estratégico sem estar
> autenticado inclusive conseguindo mergulhar nos dados por meio dos cliques (…). O que não pode é
> estar aberto para preenchimento de qualquer campo que precise de log."

---

## 2. 🔴 A primeira coisa a saber: já há dado público hoje, e ninguém decidiu isso

A rota `/` (`App\Livewire\LandingPage`, layout `layouts.public`) **já publica**, sem autenticação
nenhuma:

| Dado exposto | Origem no código |
|---|---|
| Missão, visão e valores da organização | `LandingPage.php:57` |
| Todas as perspectivas, na ordem correta do BSC | `:61-64` |
| **Todos os objetivos estratégicos**, com nome | `:62` |
| **Percentual de atingimento de cada objetivo** | `:70` `calcularAtingimentoConsolidado()` |
| **Cor do farol de cada objetivo e de cada perspectiva** | `:71`, `:79` |
| Atingimento global da organização | `:86` |
| Total de indicadores e de planos | `:92`, `:96` |
| 🔴 **Quantidade de riscos críticos** (`num_nivel_risco >= 16`) | `:100-101` |

O último item merece atenção. **A contagem de riscos críticos de um órgão da Presidência da
República está publicada na internet**, sem opção de desligar, para qualquer visitante da raiz do
sistema. Não é o mapa completo que o gestor pede — é um número agregado. Mas é informação de
gestão de riscos, exposta por padrão, e a decisão de expô-la não aparece em lugar nenhum.

> Esta é a única constatação deste documento que eu levantaria ao gestor **antes** de discutir o
> pedido em si.

---

## 3. O que existe e o que falta

### 3.1 Já pronto

| Peça | Estado |
|---|---|
| `layouts/public.blade.php` | ✅ Existe, 161 linhas |
| Componente `public-navbar` | ✅ Existe (`layouts/public.blade.php:71`) |
| Rota `/` fora do middleware `auth` | ✅ `routes/web.php:5` |
| Ordenação BSC correta na landing | ✅ `orderBy(..., 'desc')` — **e o `/pei/mapa` concorda** |
| Cache de 5 min para não onerar o banco | ✅ `Cache::remember('lp_dados_publicos', 300, ...)` |
| Redirecionamento do usuário logado para `/dashboard` | ✅ `LandingPage::mount()` |
| Preparo para visitante no mapa | ✅ `mapa-estrategico.blade.php:194` já tem `@auth … @endauth` no clique |

### 3.2 Falta

| Peça | Situação |
|---|---|
| `/pei/mapa` acessível ao visitante | ❌ Está dentro do grupo `auth:sanctum` (`routes/web.php:7-11`) |
| Mergulho nos dados sem login | ❌ Os cliques do mapa apontam para `objetivos.index`, `indicadores.index`, `planos.index` — **as três protegidas** |
| Memória de cálculo para visitante | ⚠️ `abrirMemoriaCalculo()` existe e não tem verificação de perfil |
| Seleção de organização e de ciclo pelo visitante | ❌ `MapaEstrategico::mount()` depende de `Session` e de `Auth::user()->cod_organizacao` |
| **Chave para ligar/desligar por cliente** | ❌ **Não existe** — e é o item mais importante |

### 3.3 O ponto que o pedido não cobre, e que muda o desenho

**Este é um produto multicliente.** A Presidência da República quer o mapa público. O próximo
cliente pode ser um órgão que não pode publicar nada disso — ou que pode publicar o mapa, mas não
os riscos.

Entregar isso como comportamento fixo transforma uma decisão de transparência de **cada cliente**
numa decisão do produto. Não é aceitável. **A transparência pública precisa nascer desligada e ser
ligada por quem responde pelo órgão.**

---

## 4. Análise de risco — o que pode e o que não pode ser público

O pedido diz "não pode estar aberto para preenchimento". Correto, e insuficiente: o risco maior
de uma tela pública não é escrita, é **leitura de coisa que não devia sair**.

| Camada | Publicar? | Por quê |
|---|---|---|
| Missão, visão, valores | ✅ | Já é publicado no site institucional do órgão |
| Perspectivas e objetivos estratégicos | ✅ | Idem — o modelo da PR publica no Relatório de Gestão, p. 27 |
| Atingimento e farol por objetivo | ✅ | É o coração da transparência ativa |
| Indicadores: nome, meta, resultado, série histórica | ⚠️ **por decisão** | Legítimo em transparência ativa; a decisão é do órgão |
| Iniciativas: nome, prazo, % de execução | ⚠️ **por decisão** | Idem |
| **Nome e e-mail de responsável** | ❌ **nunca** | Dado pessoal. LGPD. Nada de pessoa em tela pública |
| **Entregas do Kanban, comentários, anexos** | ❌ **nunca** | Trabalho em andamento, texto livre, possível dado pessoal |
| **Riscos — título, descrição, causas, consequências** | ❌ **nunca** | Publicar risco identificado é publicar vulnerabilidade |
| **Contagem de riscos críticos** | ⚠️ **hoje está público** | Ver seção 2 |
| **Lições aprendidas** | ❌ **nunca** | Texto livre, frequentemente sobre pessoas |
| **Memória de cálculo** | ⚠️ **por decisão** | Rastreabilidade é boa; expõe a fórmula e os pesos internos |
| **Auditoria, usuários, organizações, configurações** | ❌ **nunca** | — |

### 4.1 Os quatro riscos técnicos

1. **Enumeração de identificadores.** Uma rota pública `/publico/objetivo/{uuid}` permite varrer.
   UUID v7 é sequencial no tempo — não é segredo. Mitigar com `abort_unless` verificando que o
   objetivo pertence ao PEI publicado, e nunca aceitar id de outro escopo.
2. **DoS por cálculo.** `calcularAtingimentoConsolidado()` roda por objetivo. Sem cache e sem
   limite de taxa, a tela pública é um amplificador. O cache de 5 min da landing já é o padrão
   certo — precisa valer para todas as telas públicas, e com `throttle` na rota.
3. **Vazamento por serialização Livewire.** O snapshot do Livewire vai para o HTML. Se o
   componente carregar um Model inteiro, **campos que a Blade não mostra viajam mesmo assim**.
   Telas públicas precisam trafegar **arrays montados**, nunca Models.
4. **Escrita por método público.** Todo método `public` de um componente Livewire é chamável pelo
   navegador. `setViewMode()`, `atualizarOrganizacao()`, `atualizarPEI()` e `abrirMemoriaCalculo()`
   estão expostos hoje. Numa versão pública, cada um precisa ser reexaminado — `atualizarPEI($id)`
   aceita qualquer id.

---

## 5. Soluções avaliadas

### Opção A — Tirar `/pei/mapa` do grupo `auth` ❌
Uma linha, e é a pior. O componente foi escrito assumindo sessão, carrega Models completos no
snapshot, tem 4 métodos públicos sem verificação e liga para 3 rotas protegidas. Descartada.

### Opção B — Área pública própria, somente leitura ✅ **RECOMENDADA**

Um grupo de rotas `/transparencia/*` com componentes **próprios**, que compartilham os *serviços*
de cálculo com a área autenticada, mas não os componentes:

```
/transparencia                      → Mapa Estratégico público
/transparencia/objetivo/{uuid}      → Objetivo: descrição, ODS, indicadores e iniciativas
/transparencia/indicador/{uuid}     → Indicador: meta, resultado, série histórica
```

Governança da área:
- Middleware `TransparenciaPublica`: verifica a chave do cliente, aplica `throttle`, e **aborta
  qualquer requisição que não seja GET**.
- Componentes marcados com um contrato explícito (`interface SomenteLeitura`) e **sem um único
  método público** além de `mount`/`render`.
- Dados trafegados como **array montado**, campo a campo. Nada de Model no snapshot.
- Cache por rota, com a mesma janela da landing.

**Por que componentes separados, e não reaproveitar:** um `@auth` esquecido numa Blade
compartilhada vaza dado para sempre e ninguém percebe. Separação física é a única garantia que não
depende de alguém lembrar.

### Opção C — B, com chave por cliente ✅ **RECOMENDADA — obrigatória junto com B**

Em `SystemSetting`, com **padrão desligado**:

| Chave | Padrão | O que libera |
|---|---|---|
| `transparencia_ativa` | `false` | A área pública inteira. Desligada, `/transparencia` devolve 404 |
| `transparencia_exibe_indicadores` | `false` | Meta, resultado e série histórica |
| `transparencia_exibe_iniciativas` | `false` | Nome, prazo e % de execução |
| `transparencia_exibe_memoria_calculo` | `false` | A memória de cálculo |
| `transparencia_exibe_riscos_agregado` | **`false`** | A contagem de riscos críticos da landing — **hoje ela está ligada e sem chave** |

Tela de configuração com o alerta na cara: *"Ao ligar, estes dados ficam visíveis para qualquer
pessoa na internet, sem login."*

### Opção D — Publicação por ciclo, e não global 🔵 **evolução**
Marcar no PEI qual ciclo está publicado. Permite manter o ciclo vigente fechado e publicar o
encerrado. Corresponde ao que órgãos costumam fazer. **Backlog.**

---

## 6. Plano de ação

### Onda 0 — o que precisa de decisão antes de qualquer código
1. Levar ao gestor a seção 2 (riscos críticos já públicos) e a seção 4 (tabela do que pode sair).
2. Obter, por escrito, **linha a linha** da tabela da seção 4: publica ou não publica.
3. Confirmar que a chave nasce **desligada** para todo cliente, inclusive a Presidência, e que
   ligar é ato do órgão.

### Onda 1 — fechar o que já vaza
4. Colocar a contagem de riscos críticos da landing atrás de `transparencia_exibe_riscos_agregado`,
   padrão `false`. **Isto muda o comportamento atual e precisa ser avisado ao gestor.**

### Onda 2 — a área pública
5. `SystemSetting` com as 5 chaves e a tela de configuração com o alerta.
6. Middleware `TransparenciaPublica` (chave + `throttle` + só GET).
7. Componente `Transparencia\MapaPublico`, sem método público além de `mount`/`render`,
   trafegando arrays.
8. Reaproveitar `IndicadorCalculoService` — **o serviço**, não o componente. É o que garante que o
   número público e o número interno sejam o mesmo.
9. Cache por rota.

### Onda 3 — o mergulho
10. `Transparencia\ObjetivoPublico` e `Transparencia\IndicadorPublico`, cada um respeitando a
    chave correspondente.
11. Ajustar os cliques do mapa: autenticado vai para a tela interna; visitante vai para a pública.
12. Trilha de navegação e botão "Entrar" visível — o visitante precisa saber que existe mais.

### Onda 4 — verificação de exposição
13. Teste que **enumera todo campo renderizado** nas telas públicas e falha se aparecer campo de
    uma lista negra (nome, e-mail, texto de risco, comentário, anexo).
14. Teste que afirma `404` com a chave desligada.
15. Teste que afirma que nenhum componente público tem método público além de `mount`/`render`.

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R7.0** Decisão | Tabela da seção 4 respondida pelo gestor | Registrada aqui, linha a linha | — |
| **R7.1** Fechar o vazamento atual | Riscos críticos atrás de chave, padrão off | Landing sem o número em instalação nova | R7.0 |
| **R7.2** Chaves | 5 `SystemSetting` + tela com alerta | Padrão `false` em instalação nova | R7.0 |
| **R7.3** Middleware | `TransparenciaPublica` | POST devolve 405; chave off devolve 404; `throttle` ativo | R7.2 |
| **R7.4** Mapa público | `/transparencia` | Mesma leitura do `/pei/mapa`, sem dado de pessoa | R7.3 |
| **R7.5** Paridade de número | Mesmo objetivo, mesmo % público e interno | Comparação lado a lado | R7.4 |
| **R7.6** Mergulho | Objetivo e indicador públicos | Cada um respeita a chave | R7.4 |
| **R7.7** Cliques condicionais | Visitante → público; logado → interno | Inspeção nos dois estados | R7.6 |
| **R7.8** Testes de exposição | 3 testes da onda 4 | Verdes | R7.6 |
| **R7.9** Revisão de segurança | `/security-review` sobre o diff | Sem achado aberto | R7.8 |
| **R7.10** Publicação por ciclo | Opção D | — | Backlog |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 07-B01 | Levar a exposição atual de riscos ao gestor | P | — | Resposta escrita |
| 07-B02 | Tabela da seção 4 decidida linha a linha | P | B01 | Registrada |
| 07-B03 | Riscos críticos da landing atrás de chave | P | B02 | Landing limpa por padrão |
| 07-B04 | 5 chaves em `SystemSetting` | M | B02 | Padrão `false` |
| 07-B05 | Tela de configuração com alerta de exposição | M | B04 | Gestor aprova o texto |
| 07-B06 | Middleware `TransparenciaPublica` | M | B04 | B07, B08 |
| 07-B07 | Teste: chave off → 404 | P | B06 | Verde |
| 07-B08 | Teste: método não-GET → 405 | P | B06 | Verde |
| 07-B09 | `Transparencia\MapaPublico` sem método público | G | B06 | B14 |
| 07-B10 | Trafegar arrays, nunca Model, no snapshot | M | B09 | Inspeção do HTML do snapshot |
| 07-B11 | Cache por rota pública | P | B09 | Segunda visita não consulta o banco |
| 07-B12 | `Transparencia\ObjetivoPublico` | M | B09 | Tela |
| 07-B13 | `Transparencia\IndicadorPublico` | M | B09 | Tela |
| 07-B14 | **Teste de lista negra de campos** nas telas públicas | G | B12, B13 | Verde — é a trava que importa |
| 07-B15 | Teste: componente público sem método público extra | M | B09 | Verde |
| 07-B16 | Teste de paridade: % público = % interno | M | B12 | Verde |
| 07-B17 | Cliques condicionais no mapa | M | B12 | Inspeção nos dois estados |
| 07-B18 | Botão "Entrar" e trilha na área pública | P | B09 | Tela |
| 07-B19 | `/security-review` sobre o diff completo | M | B17 | Sem achado aberto |
| 07-B20 | Publicação por ciclo | G | B19 | Futuro |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

Numa tela pública, "funciona" é a parte fácil. A verificação que importa é **o que não aparece**:

1. **07-B14 — a única verificação que realmente protege.** Um teste que percorre o HTML de cada
   rota pública e falha se encontrar qualquer campo da lista negra. Inspeção visual não serve:
   o snapshot do Livewire carrega dado que a Blade não mostra, e olho nenhum lê snapshot.
2. **Navegar em janela anônima**, sem sessão, pelas três telas públicas, e conferir contra a
   tabela da seção 4 — item por item, não "parece ok".
3. **Ler o HTML gerado**, incluindo o `wire:snapshot`, procurando e-mail, nome de pessoa e texto
   de risco.
4. **Paridade de número:** o mesmo objetivo aberto na tela pública e na interna tem de mostrar o
   mesmo percentual. Divergência aqui destrói a credibilidade da transparência.
5. **Tentar escrever:** POST direto nas rotas públicas; chamada de método Livewire pelo console.
   Ambos precisam ser recusados.
6. **Chave desligada:** instalação nova não publica nada. Se publicar, o padrão está errado.
7. `/security-review` sobre o diff, antes de fechar.

---

## 10. O que NÃO foi verificado

- **Se o gestor da Presidência tem autorização institucional** para publicar isso — é a pergunta
  que antecede todas as outras, e não é técnica
- Se o órgão tem norma própria de transparência ativa que restrinja ou obrigue algum item
- Se `layouts/public.blade.php` e `public-navbar` já expõem algo além do levantado (o arquivo tem
  161 linhas; foram lidas as referências, não o conteúdo integral)
- Se `calcularAtingimentoConsolidado()` tem custo aceitável sob carga pública — não foi medido
- Se `Organization::first()` da landing devolve a organização certa quando há mais de uma raiz
- Se há robô ou indexador que já capturou o número de riscos críticos hoje publicado
