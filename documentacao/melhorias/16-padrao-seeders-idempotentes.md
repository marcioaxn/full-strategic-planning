# 16 — Padrão de seeder idempotente para vocabulário controlado

> **Tema:** B — Vocabulários controlados · **Origem:** derivado das demandas 2 e 3
> **Tipo:** Correção estrutural · **Impacto:** alto · **Risco de regressão:** médio
> **Bloqueia:** [02](02-tipos-de-iniciativa-seeder.md), [03](03-modal-nova-atividade.md) e toda opção nova de lista fechada daqui para frente
> **Verificado em:** 05/09/2026

---

## 1. O problema, na frase do gestor

> "É necessário que quando o novo cliente clone o projeto e na execução da seeder o tipo já seja
> preenchido, no entanto, é necessário um service de seeder específico para os clientes que já
> instalaram o projeto."

O gestor descreveu o sintoma com precisão: **duas populações de cliente, e só uma é atendida**.
Este documento ataca a causa, para que as demandas 2 e 3 — e as próximas dez iguais a elas — sejam
uma linha de código cada, e não um projeto cada.

---

## 2. Análise técnica — por que o cliente instalado não recebe

### 2.1 A causa, com evidência

`database/migrations/2021_11_14_221355_create_pei_tab_tipo_execucao_table.php` cria a tabela **e
insere os dados no mesmo `up()`**:

```php
Schema::create('action_plan.tab_tipo_execucao', function (Blueprint $table) { /* ... */ });

// Inserir tipos de execução padrão
DB::table('action_plan.tab_tipo_execucao')->insert([
    ['cod_tipo_execucao' => 'c00b9ebc-...', 'dsc_tipo_execucao' => 'Ação',       /* ... */],
    ['cod_tipo_execucao' => 'ecef6a50-...', 'dsc_tipo_execucao' => 'Iniciativa', /* ... */],
    ['cod_tipo_execucao' => '57518c30-...', 'dsc_tipo_execucao' => 'Projeto',    /* ... */],
]);
```

O Laravel registra a migration em `pei.migrations` na primeira execução e **nunca mais roda o
`up()`**. Consequência exata:

| População | Roda `migrate` | Recebe tipo novo acrescentado ao `up()` |
|---|---|---|
| Cliente que clona hoje | Sim, do zero | ✅ Sim |
| Cliente instalado em 2025 | Sim, só as pendentes | ❌ **Não** — esta migration já está em `pei.migrations` |

Editar a migration existente para acrescentar um tipo **não resolve e ainda mente**: o arquivo
passa a descrever um estado que a base do cliente antigo não tem.

### 2.2 O alcance real do problema

Não é um caso isolado. Migrations que semeiam dado no `up()` (verificado por `grep` de
`->insert(` em `database/migrations/`):

| Migration | Dado semeado | Afetada |
|---|---|---|
| `2021_11_14_221355_create_pei_tab_tipo_execucao_table.php` | 3 tipos de execução | ✅ demanda 2 |
| `2026_02_01_003107_add_dsc_polaridade_and_migrate_legacy_types.php` | migração de tipos legados | ⚠️ verificar |

E o caso simétrico — dado que **deveria** existir e não tem semeadura nenhuma:

| Tabela | Situação |
|---|---|
| `strategic_planning.tab_atividade_cadeia_valor.dsc_tipo` | Vocabulário vive só em `AtividadeCadeiaValor::TIPOS` (constante PHP), sem tabela de referência — demanda 3 |

### 2.3 Os seeders que existem hoje

```
database/seeders/
├── DatabaseSeeder.php
├── OrganizacaoRaizSeeder.php
├── PerfilAcessoSeeder.php
├── SuperAdministradorSeeder.php
└── TruncarBancoSeeder.php
```

`php artisan db:seed` já é não-destrutivo desde 04/09/2026 (a truncagem saiu do caminho padrão) —
isso é bom, e é a base sobre a qual este padrão se apoia. **Não existe hoje nenhum seeder de
vocabulário controlado.**

---

## 3. Soluções avaliadas

### Opção A — "Service de seeder específico para quem já instalou" (a letra do pedido) ❌
Criar um `AtualizarTiposExistentesSeeder` separado do seeder de instalação nova.

**Por que não:** duplica a lista de tipos em dois lugares. Quando alguém acrescentar um quarto
tipo, vai acrescentar em um e esquecer o outro — e a divergência só aparece no cliente. Também
obriga o gestor a saber **qual** seeder cada cliente deve rodar, o que é conhecimento que se perde.

> Esta é a única recomendação deste documento que **contraria a forma** pedida pelo gestor.
> Ela **atende integralmente o resultado** pedido (cliente novo e cliente antigo recebem o tipo),
> por um caminho com menos peça para desalinhar. Se o gestor preferir a forma original, ela é
> viável — o custo é a duplicação da lista, e fica registrado aqui.

### Opção B — Um seeder idempotente único ✅ **RECOMENDADA**
Um seeder por vocabulário, que **converge o banco para o estado desejado**, seja ele qual for.
O mesmo comando serve às duas populações:

```
php artisan db:seed --class=TipoExecucaoSeeder
```

- Instalação nova: a tabela está vazia → o seeder insere os 2 tipos.
- Instalação de 2025: os 3 tipos antigos estão lá → o seeder insere o que falta, atualiza rótulo
  divergente e **não toca** no que já está certo.
- Rodar de novo: nada acontece. Essa é a definição de idempotente.

### Opção C — B, com registro de "vocabulário aplicado" ⚠️
Uma tabela `pei.vocabulario_aplicado` para saber qual versão de cada vocabulário cada base tem.
Correto para produto maduro; excesso para o volume atual (2 vocabulários). **Fica no backlog como
16-B09, para quando houver 5+.**

---

## 4. O desenho recomendado

### 4.1 A classe base

`app/Services/Seeding/VocabularioControlado.php` — o serviço que o gestor pediu, só que **um**
para todos os vocabulários, em vez de um por população de cliente.

Responsabilidades, e só estas:

| Método | O que faz | O que NÃO faz |
|---|---|---|
| `sincronizar(string $tabela, string $pk, string $rotulo, array $itens)` | Insere o que falta; atualiza rótulo divergente | Nunca apaga |
| `aposentar(string $tabela, string $pk, array $ids)` | Marca `deleted_at` **só se** não houver linha filha apontando para o id | Nunca `DELETE`, nunca `CASCADE` |
| `relatorio(): array` | Devolve `['inseridos' => n, 'atualizados' => n, 'ignorados' => n, 'recusados' => [...]]` | Não decide nada sozinho |

### 4.2 As cinco regras que o padrão precisa obedecer

1. **UUID fixo em constante, nunca `gen_random_uuid()` no seeder.** O id do tipo "Ação" tem de
   ser o mesmo em todas as instalações — é ele que o código referencia
   (`TipoExecucao::ACAO`) e é ele que faz a segunda execução ser inócua.
2. **`updateOrInsert` por chave primária, não `upsert()`.** `upsert()` do Laravel emite
   `ON CONFLICT`. O `CLAUDE.md` proíbe `ON CONFLICT` neste projeto por conta do PostgreSQL 9.3.
   ⚠️ **Ver a pendência da seção 9** — se a versão mínima de PostgreSQL suportada mudou, esta
   regra deve ser revista antes de virar código.
3. **Nunca apagar dado do cliente.** Um cliente pode ter renomeado "Ação" para "Ação Corretiva".
   O seeder **não desfaz** customização: só insere ausente. Atualizar rótulo é decisão declarada
   item a item, não comportamento padrão.
4. **Sempre qualificar o schema.** `action_plan.tab_tipo_execucao`, nunca `tab_tipo_execucao` —
   o `search_path` começa em `pei` e uma query sem schema cai lá em silêncio.
5. **Toda aposentadoria conta as linhas filhas antes.** Marcar um tipo como excluído com 400
   iniciativas apontando para ele quebra a tela do cliente. O serviço **recusa e relata**, nunca
   força.

### 4.3 Onde ele é chamado

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    $this->call([
        PerfilAcessoSeeder::class,
        OrganizacaoRaizSeeder::class,
        SuperAdministradorSeeder::class,
        TipoExecucaoSeeder::class,          // ← demanda 02
        TipoAtividadeCadeiaValorSeeder::class, // ← demanda 03
    ]);
}
```

Um único `php artisan db:seed` atende cliente novo e cliente antigo. O roteiro de atualização de
versão passa a ter uma linha só, igual para todo mundo.

---

## 5. Plano de ação

1. **Medir antes de escrever.** No banco de dev, e depois com o gestor numa base de cliente:
   - `SELECT dsc_tipo_execucao, COUNT(*) FROM action_plan.tab_tipo_execucao GROUP BY 1`
   - `SELECT t.dsc_tipo_execucao, COUNT(p.*) FROM action_plan.tab_tipo_execucao t
      LEFT JOIN action_plan.tab_plano_de_acao p ON p.cod_tipo_execucao = t.cod_tipo_execucao
      GROUP BY 1`
   🔴 Exige **autorização explícita do gestor** antes de executar, mesmo sendo `SELECT`.
2. Criar `app/Services/Seeding/VocabularioControlado.php` com os três métodos.
3. Criar `tests/Seeders/VocabularioControladoTest.php` **antes** de qualquer seeder concreto,
   cobrindo os quatro cenários da seção 7.
4. Criar `TipoExecucaoSeeder` (demanda 02) usando o serviço.
5. Criar `TipoAtividadeCadeiaValorSeeder` (demanda 03) usando o serviço.
6. Registrar ambos em `DatabaseSeeder::run()`.
7. Escrever o roteiro de atualização de versão, com o comando exato que o cliente roda.
8. **Não** editar `2021_11_14_221355`. A migration antiga fica como está — ela descreve o que era
   verdade no dia em que rodou.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R16.0** Medição | Contagem por tipo e por tipo×iniciativa, em dev e em uma base de cliente | Números escritos neste arquivo, não estimados | Autorização do gestor |
| **R16.1** Serviço | `VocabularioControlado` com `sincronizar`, `aposentar`, `relatorio` | `php -l` limpo; nenhum `ON CONFLICT`; schema qualificado em toda query | — |
| **R16.2** Testes | 4 cenários da seção 7 verdes | Suíte verde | R16.1 |
| **R16.3** Seeder de tipos de Iniciativa | Demanda [02](02-tipos-de-iniciativa-seeder.md) entregue | Cliente novo e antigo com os mesmos tipos | R16.2 |
| **R16.4** Seeder de tipos da Cadeia de Valor | Demanda [03](03-modal-nova-atividade.md) entregue | idem | R16.2 |
| **R16.5** Roteiro de versão | Um `.md` com a sequência que o cliente executa | Gestor consegue seguir sem perguntar nada | R16.3, R16.4 |
| **R16.6** Trava | Guarda que recusa `->insert(` novo em migration | Ver 16-B08 | R16.4 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 16-B01 | Medir tipos e vínculos em dev e em base de cliente | P | autorização | Números registrados no doc |
| 16-B02 | `VocabularioControlado::sincronizar()` | M | — | Teste 16-B05 |
| 16-B03 | `VocabularioControlado::aposentar()` com contagem de filhos | M | B02 | Teste 16-B06 |
| 16-B04 | `VocabularioControlado::relatorio()` e saída no console do seeder | P | B03 | Saída legível ao rodar |
| 16-B05 | Teste: **instalação nova** — tabela vazia → N itens inseridos | M | B02 | Verde |
| 16-B06 | Teste: **instalação antiga** — itens preexistentes → só o ausente é inserido | M | B02 | Verde |
| 16-B07 | Teste: **segunda execução** — rodar duas vezes não muda nada (`relatorio()` zerado) | P | B05 | Verde |
| 16-B08 | Teste: **customização preservada** — rótulo alterado pelo cliente não é sobrescrito | M | B02 | Verde |
| 16-B09 | Teste: **aposentadoria recusada** — tipo com filho não é marcado como excluído | M | B03 | Verde |
| 16-B10 | Teste: **nenhum `ON CONFLICT`** — asserção sobre o SQL emitido, via `DB::listen` | M | B02 | Verde; é a asserção que o `CLAUDE.md` exige (regra nº 2 do topo) |
| 16-B11 | Guarda no `guarda-arquivo.php`: alertar `->insert(`/`->update(` dentro de migration | M | B04 | Guarda dispara em arquivo de teste, não dispara nas 46 migrations existentes (dívida congelada) |
| 16-B12 | Registro `pei.vocabulario_aplicado` (opção C) | G | B04 | Adiar até haver 5+ vocabulários |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

A pergunta do gestor é literalmente sobre **duas populações**. Então a verificação tem de
simular as duas, e não pode ser "rodei o seeder e não deu erro":

1. **Instalação nova:** banco limpo → `migrate` + `db:seed` → conferir a lista de tipos.
2. **Instalação antiga:** restaurar um dump com a base de 2025 (3 tipos antigos, iniciativas
   vinculadas) → `git pull` + `migrate` + `db:seed` → conferir que (a) o tipo novo apareceu,
   (b) nenhuma iniciativa perdeu o vínculo, (c) nada foi apagado.
3. **Idempotência:** rodar `db:seed` três vezes seguidas; `relatorio()` precisa vir zerado da
   segunda em diante.
4. **SQL emitido:** `DB::listen` capturando as queries do seeder, asserção de que nenhuma contém
   `ON CONFLICT`. É o artefato real, não o resultado.
5. Tela `/planos` (futuras Iniciativas) e `/pei/cadeia-de-valor` abrindo com o combo populado.

**O passo 2 é o único que prova o que o gestor pediu, e ele depende de um dump de cliente.**
Sem esse dump, a entrega fica declarada como parcialmente verificada.

---

## 9. Pendências e o que não foi verificado

### 🔴 Pendência que precisa de resposta antes de virar código

O `CLAUDE.md` também afirma que **produção roda PostgreSQL 9.3**, o que proíbe `ON CONFLICT`,
`upsert()`, `insertOrIgnore()`, `jsonb`, `FILTER (WHERE ...)` e `CREATE INDEX IF NOT EXISTS`.
Essa afirmação está no **mesmo bloco de regras** que a premissa de migration recém-corrigida, e
tem a mesma origem (projeto de instância única).

Num produto multicliente a pergunta muda de forma: não é "qual versão produção roda", é
**"qual a versão mínima de PostgreSQL que o produto suporta"**. A resposta altera diretamente o
desenho do serviço:

- Se o mínimo for **9.5+**: `upsert()` nativo resolve `sincronizar()` em uma linha.
- Se o mínimo for **9.3**: `updateOrInsert` manual, como desenhado aqui.

O desenho acima assume 9.3 — o caminho conservador, que funciona nos dois casos. Vale
confirmar antes de investir, e vale revisar o resto do bloco de sintaxe proibida do `CLAUDE.md`
com o mesmo olhar.

### Não verificado

- Quantos clientes existem instalados e em que versão cada um está
- Se algum cliente renomeou tipo de execução (motiva a regra nº 3 da seção 4.2, mas não foi medido)
- Contagem de linhas em qualquer tabela — exige autorização
- Se `2026_02_01_003107_add_dsc_polaridade_and_migrate_legacy_types.php` semeia ou apenas migra
  dado existente — o arquivo não foi lido em profundidade
