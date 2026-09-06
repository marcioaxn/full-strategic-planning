# 11 — `Database connection [pei] not configured` e a disposição do modal de Risco

> **Tema:** D — Fluxo de preenchimento · **Tipo:** 🔴 **Correção de bug em produção** + Melhoria
> **Impacto:** 🔴 **crítico — impede salvar risco** · **Risco de regressão:** baixo
> **Prioridade:** onda 0, junto com [14](14-claude-md-fora-do-repositorio.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "Em `/riscos` no(a) Modal [Identificar Novo Risco] fiz o preenchimento de alguns campos, cliquei
> em [Salvar Risco] e o seguinte erro surgiu [Database connection [pei] not configured.],
> [app\Livewire\RiskManagement\ListarRiscos.php:219] (…). Além disso o cliente achou estranho o item
> [Vínculo Estratégico] estar lá embaixo na parte [Monitoramento] e segundo ele deveria ter mais
> evidência. Na verdade a disposição dos elementos no(a) Modal deve ser melhorado."

---

## 2. Parte 1 — o bug. Causa raiz confirmada

### 2.1 A linha

`app/Livewire/RiskManagement/ListarRiscos.php:224`

```php
'form.cod_responsavel_monitoramento' => 'required|exists:pei.users,id',
```

### 2.2 Por que quebra

O Laravel, ao processar a regra `exists`, interpreta o **ponto** como separador de
**conexão**, não de schema:

```php
// Illuminate\Validation\Concerns\ValidatesAttributes::parseTable()
[$connection, $table] = str_contains($table, '.')
    ? explode('.', $table, 2)
    : [null, $table];
```

Então `pei.users` é lido como:
- conexão = `pei`
- tabela = `users`

O validador chama `DB::connection('pei')`. Em `config/database.php` existem `sqlite`, `mysql`,
`mariadb`, `pgsql` e `sqlsrv` — **não existe `pei`**. Daí, literalmente:

> `Database connection [pei] not configured.`

`pei` é um **schema do PostgreSQL**, listado no `search_path` da conexão `pgsql`. Nunca foi uma
conexão.

### 2.3 A ironia que confirma o diagnóstico

Duas telas do mesmo sistema estão em lados opostos da mesma armadilha:

| Arquivo | Regra | Resultado |
|---|---|---|
| `RiskManagement/ListarRiscos.php:224` | `exists:pei.users,id` | ❌ **Quebra** |
| `StrategicPlanning/ListarObjetivos.php:302` | `exists:tab_perspectiva,cod_perspectiva` | ✅ **Funciona** |

O que funciona é justamente o que **não** qualifica o schema — porque aí o Laravel monta
`SELECT ... FROM users` e o PostgreSQL resolve pelo `search_path`.

Isso cria um conflito real com a regra do `CLAUDE.md` ("qualificar sempre o schema"). A regra vale
para migration, `DB::` e `$table` de Model — **não vale para a string da regra `exists`/`unique`**,
onde o ponto tem outro significado. Isso precisa ficar escrito, senão alguém "conserta"
`ListarObjetivos` e quebra a tela de objetivos.

### 2.4 O segundo lugar com o mesmo defeito

```
$ grep -rn "exists:[a-z_]*\.[a-z_]*," app/
app/Livewire/RiskManagement/ListarRiscos.php:224   ← relatado
app/Livewire/StrategicPlanning/GerenciarRae.php:311 ← mesmo bug, ainda não relatado
```

`GerenciarRae.php:311` — `'encForm.cod_responsavel' => 'nullable|exists:pei.users,id'`.

Como é `nullable`, só quebra quando o cliente **preenche** o responsável do encaminhamento —
por isso ninguém reclamou ainda. **É a mesma correção, e entra no mesmo commit.**

### 2.5 Por que nenhum teste pegou

Não há teste que exercite o `save()` de `ListarRiscos` pelo caminho da tela. A regra
`required|exists` só é avaliada quando o campo chega preenchido — e um teste que só afirme
"o risco foi criado" pode passar por outro caminho.

É exatamente a regra nº 5 do topo do `CLAUDE.md`: **a asserção tem de ser sobre o caminho que a
TELA usa.**

### 2.6 A correção

**Opção A — remover o prefixo** ✅ **RECOMENDADA**
```php
'form.cod_responsavel_monitoramento' => 'required|exists:users,id',
```
Uma palavra a menos. O `search_path` resolve, como já faz em `ListarObjetivos`.

**Opção B — `Rule::exists` com conexão explícita** ⚠️
```php
Rule::exists('pgsql.users', 'id')
```
Mais explícito quanto à conexão, e ainda depende do `search_path` para o schema. Verboso sem
ganho real.

**Opção C — regra sobre o Model** 🔵
```php
Rule::exists(User::class, 'id')
```
O Laravel resolve pela tabela do Model, que **é** qualificada (`protected $table = 'pei.users'`).
Elegante e imune a mudança de schema. Exige confirmar como o Laravel 12 monta a query nesse caso —
e o `CLAUDE.md` manda ler o SQL emitido antes de adotar helper de framework. **Avaliar, não adotar
no commit de correção.**

---

## 3. Parte 2 — a disposição do modal

### 3.1 O que existe hoje

`resources/views/livewire/risco/listar-riscos.blade.php:610-800` — `modal-xl`, duas colunas:

```
┌─ modal-xl ────────────────────────────────────────────────────────────┐
│  col-lg-8 (principal)              │  col-lg-4 (lateral)              │
│  ┌──────────────────────────────┐  │  ┌────────────────────────────┐  │
│  │ Identificação do Risco  :635 │  │  │ Avaliação P×I         :691 │  │
│  │  título, descrição,          │  │  │  sliders 1-5, matriz       │  │
│  │  categoria, status           │  │  └────────────────────────────┘  │
│  └──────────────────────────────┘  │  ┌────────────────────────────┐  │
│  ┌──────────────────────────────┐  │  │ Monitoramento         :732 │  │
│  │ Análise de Causa e Efeito:671│  │  │  responsável, revisão      │  │
│  │  causas, consequências       │  │  ├────────────────────────────┤  │
│  └──────────────────────────────┘  │  │ Estratégia de Resposta:745 │  │
│                                    │  ├────────────────────────────┤  │
│                                    │  │ Vínculo Estratégico   :778 │  │← fim
│                                    │  └────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────────┘
```

O cliente está certo: **"Vínculo Estratégico" é o último bloco da coluna estreita**, abaixo de
três outros. Numa tela de 1366px, ele fica abaixo da dobra.

### 3.2 Por que isso é um erro de método, não de estética

Um risco de planejamento estratégico **existe em função de um objetivo**. Um risco que não ameaça
nenhum objetivo não pertence ao PEI. O vínculo não é metadado de acompanhamento — é o que
qualifica o registro como risco estratégico.

Colocá-lo no fim, dentro de "Monitoramento", ensina o contrário: sugere que é detalhe de
acompanhamento, opcional. **A tela está ensinando o método errado.**

E o campo hoje é opcional (`objetivos_vinculados` é array, sem validação). Resultado previsível:
riscos cadastrados sem vínculo, e a matriz de risco desconectada do mapa estratégico.

### 3.3 A ordem proposta

A ordem do formulário é a ordem em que a pessoa **pensa**:

```
┌─ modal-xl ────────────────────────────────────────────────────────────┐
│  1. IDENTIFICAÇÃO                          [largura total]            │
│     Título · Descrição · Categoria · Status                           │
├───────────────────────────────────────────────────────────────────────┤
│  2. 🎯 VÍNCULO ESTRATÉGICO                 [largura total, destacado] │
│     "Qual objetivo estratégico este risco ameaça?"                    │
│     [ ] Crescimento de mercado    [ ] Excelência operacional  …       │
│     ⚠️ Um risco sem objetivo vinculado não aparece no Mapa Estratégico │
├────────────────────────────────────┬──────────────────────────────────┤
│  3. ANÁLISE DE CAUSA E EFEITO      │  4. AVALIAÇÃO (P × I)            │
│     Causas · Consequências         │     sliders + matriz + nível     │
├────────────────────────────────────┼──────────────────────────────────┤
│  5. ESTRATÉGIA DE RESPOSTA         │  6. MONITORAMENTO                │
│     Mitigar/Evitar/Transferir/     │     Responsável                  │
│     Aceitar + justificativa        │     Próxima revisão              │
└────────────────────────────────────┴──────────────────────────────────┘
```

Muda três coisas:
1. **Vínculo sobe para o segundo bloco**, em largura total, com título próprio e pergunta em
   linguagem natural.
2. **Monitoramento fica só com monitoramento** — responsável e data de revisão. O vínculo sai de lá.
3. **Estratégia de Resposta ganha seu lugar** — hoje está espremida entre dois blocos na coluna
   estreita, e é a decisão de gestão mais importante do registro.

### 3.4 Torná-lo obrigatório?

| Opção | Avaliação |
|---|---|
| Obrigatório | ⚠️ Metodologicamente correto. Impede registrar risco no calor da reunião e vincular depois |
| Opcional com aviso ✅ | **Recomendado.** Salva, e a listagem marca `⚠️ sem vínculo estratégico`, com contagem no painel |
| Opcional silencioso | ❌ O de hoje |

O aviso resolve sem travar — mesmo padrão da demanda
[08](08-quem-gerencia-o-que-papeis-e-responsaveis.md) para iniciativa sem responsável.

---

## 4. Plano de ação

### Onda 0 — o bug (é o que impede trabalhar)
1. `exists:pei.users,id` → `exists:users,id` em `ListarRiscos.php:224`.
2. Mesma correção em `GerenciarRae.php:311`.
3. **Teste Livewire** que exercita o `save()` pelo caminho da tela, com responsável preenchido.
4. **Guarda no `guarda-arquivo.php`**: recusar `exists:` / `unique:` com ponto na tabela quando o
   prefixo for um dos seis schemas. É a resposta à pergunta *"que verificação automática teria
   pego isto?"*
5. Registrar no `CLAUDE.md` que a regra de qualificar schema **não vale** para `exists`/`unique`.

### Onda 1 — o modal
6. Reordenar os blocos conforme §3.3.
7. Vínculo Estratégico em largura total, com a pergunta em linguagem natural.
8. Aviso de risco sem vínculo, na listagem e no painel.
9. Conferir que os sliders `wire:model.live` continuam atualizando a matriz.

---

## 5. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R11.0** Correção | `exists:users,id` nos 2 pontos | Salvar risco com responsável funciona | — |
| **R11.1** Teste de regressão | Livewire pelo caminho da tela | Verde; falha se o prefixo voltar | R11.0 |
| **R11.2** Teste do RAE | Encaminhamento com responsável | Verde | R11.0 |
| **R11.3** Guarda | `guarda-arquivo.php` recusa o padrão | Dispara em arquivo de teste; silencioso nos 46 arquivos atuais | R11.1 |
| **R11.4** `CLAUDE.md` | Exceção da regra `exists`/`unique` documentada | Escrito | R11.3 |
| **R11.5** Varredura | Nenhum outro `exists:`/`unique:` com schema | `grep` limpo | R11.0 |
| **R11.6** Reordenação | Blocos na ordem de §3.3 | Vínculo acima da dobra em 1366px | R11.0 |
| **R11.7** Vínculo em destaque | Largura total, pergunta natural | Visual | R11.6 |
| **R11.8** Aviso sem vínculo | Marca na listagem + contagem | Número confere com o banco | R11.7 |
| **R11.9** Regressão da matriz | Sliders e nível de risco intactos | Manual | R11.6 |

---

## 6. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 11-B01 | Corrigir `ListarRiscos.php:224` | P | — | Salvar funciona |
| 11-B02 | Corrigir `GerenciarRae.php:311` | P | — | Encaminhamento salva |
| 11-B03 | Teste Livewire do `save()` de risco pela tela | M | B01 | Verde |
| 11-B04 | Teste Livewire do encaminhamento do RAE | M | B02 | Verde |
| 11-B05 | Guarda contra `exists:`/`unique:` com schema | M | B03 | Dispara no teste; silencioso no legado |
| 11-B06 | Registrar a exceção no `CLAUDE.md` | P | B05 | Escrito |
| 11-B07 | Varredura de `unique:` com schema | P | — | `grep` limpo |
| 11-B08 | Avaliar `Rule::exists(User::class, 'id')` lendo o SQL emitido | M | B03 | SQL conferido |
| 11-B09 | Reordenar os blocos do modal | M | B01 | Visual |
| 11-B10 | Vínculo Estratégico em largura total | M | B09 | Acima da dobra em 1366px |
| 11-B11 | Pergunta em linguagem natural no bloco | P | B10 | Gestor aprova |
| 11-B12 | Estratégia de Resposta com espaço próprio | M | B09 | Visual |
| 11-B13 | Marca `⚠️ sem vínculo` na listagem | M | B10 | Número confere |
| 11-B14 | Contagem de riscos sem vínculo no painel | M | B13 | Número confere |
| 11-B15 | Regressão manual da matriz P×I | P | B09 | Sliders atualizam |
| 11-B16 | `report($e)` nos `catch` de `ListarRiscos` | P | B01 | Exceção forçada logada |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 7. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **Reproduzir o erro antes de corrigir.** Preencher exatamente os quatro campos do relato
   (título, descrição, categoria Financeiro, responsável) e ver a exceção. **Corrigir sem ter
   reproduzido é confiar na leitura do código** — e a leitura pode estar certa e a causa ser outra.
2. **Repetir depois**, com o mesmo preenchimento, e ver o risco salvo.
3. **11-B03**, o teste que faltava: pelo caminho da tela, com o campo preenchido. Um teste que
   chame `Risco::create()` direto passaria verde com o bug intacto — foi assim que ele chegou ao
   cliente.
4. **11-B05**, a trava: o guarda tem de disparar num arquivo de teste com
   `exists:pei.users,id` **e ficar silencioso** nos arquivos atuais depois da correção.
5. **`grep -rn "exists:[a-z_]*\.[a-z_]*,\|unique:[a-z_]*\.[a-z_]*," app/`** — vazio.
6. **O modal, em 1366px:** o bloco Vínculo Estratégico visível sem rolar. É a queixa literal
   ("deveria ter mais evidência") e só se verifica olhando.
7. **Regressão:** criar e editar risco, mover os sliders, conferir o nível calculado e a matriz.
   Reordenar blocos numa Blade de 910 linhas é onde se quebra o que funcionava.

---

## 8. O que NÃO foi verificado

- **O erro não foi reproduzido em execução** — a causa foi determinada por leitura do código do
  Laravel e do arquivo. É diagnóstico sólido, não é reprodução
- Se `GerenciarRae.php:311` já quebrou para algum cliente
- Se há `unique:` com schema em algum lugar (a varredura cobriu só `exists:`)
- Se `Rule::exists(User::class, ...)` monta a query com o schema — precisa ler o SQL emitido
- Quantos riscos existem hoje sem vínculo estratégico (exige autorização)
- Se a Blade de 910 linhas tem outros pontos que dependem da ordem atual dos blocos
- Se o `catch` de `ListarRiscos` engole exceção como o de `ListarObjetivos` — o bloco não foi
  lido por inteiro
