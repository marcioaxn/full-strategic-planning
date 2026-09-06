# 02 — Tipos de Iniciativa (`Ação` e `Projeto`) populados no banco

> **Tema:** B — Vocabulários controlados
> **Tipo:** Correção + Melhoria · **Impacto:** alto · **Risco de regressão:** **alto** (mexe em dado vinculado)
> **Depende de:** [16 — Padrão de seeder idempotente](16-padrao-seeders-idempotentes.md)
> **Relacionada a:** [01 — Renomear para Iniciativas](01-renomear-plano-de-acao-para-iniciativas.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "No formulário para inserir um novo Plano de Ação, que agora será chamado de Iniciativas é
> necessário termos os tipos populados no banco de dados. (…) Os tipos são [Ação] e [Projeto]."

---

## 2. 🔴 A primeira coisa que o gestor precisa saber

**A tabela já existe e já está populada — com três tipos, não dois.**

`action_plan.tab_tipo_execucao` é criada e semeada por
`database/migrations/2021_11_14_221355_create_pei_tab_tipo_execucao_table.php`, com UUID fixo:

| UUID | Rótulo | Constante em `App\Models\ActionPlan\TipoExecucao` |
|---|---|---|
| `c00b9ebc-7014-4d37-97dc-7875e55fff1b` | **Ação** | `TipoExecucao::ACAO` |
| `ecef6a50-c010-4cda-afc3-cbda245b55b0` | **Iniciativa** | `TipoExecucao::INICIATIVA` |
| `57518c30-3bc5-4305-a998-8ce8b11550ed` | **Projeto** | `TipoExecucao::PROJETO` |

Ou seja: o pedido não é "popular uma tabela vazia". É **remover um tipo de uma tabela que pode ter
dado vinculado a ele.** São dois trabalhos de risco muito diferente, e o segundo exige decisão.

### 2.1 Por que "Iniciativa" precisa mesmo sair

Não é só o gestor ter listado dois tipos. Depois da demanda [01](01-renomear-plano-de-acao-para-iniciativas.md),
o módulo inteiro passa a se chamar **Iniciativas**. Manter um *tipo* chamado "Iniciativa" produz
na tela:

> *"Iniciativa · Tipo: Iniciativa"*

O cliente não tem como saber o que isso distingue. É exatamente o rótulo ambíguo que o
mandamento nº 6c do `CLAUDE.md` chama de defeito.

### 2.2 A decisão que só o gestor pode tomar

| Caminho | O que acontece com as iniciativas já classificadas como "Iniciativa" | Recomendação |
|---|---|---|
| **A — Aposentar com remanejamento** | Migram para `Ação` (ou `Projeto`), depois o tipo é marcado como excluído | ✅ **Recomendado**, se a contagem permitir |
| **B — Aposentar sem remanejamento** | Ficam órfãs, apontando para tipo com `deleted_at` | ❌ Quebra a tela |
| **C — Manter os três** | Nada muda; a ambiguidade permanece | ⚠️ Só se a contagem for alta demais para remanejar |

**O caminho depende de um número que ainda não foi medido.** Ver seção 5, passo 1.

---

## 3. Análise técnica

### 3.1 O que já funciona

| Elemento | Estado | Evidência |
|---|---|---|
| Tabela | ✅ Existe, com `softDeletes` | migration `2021_11_14_221355` |
| Model | ✅ `TipoExecucao`, com UUID em constante | `app/Models/ActionPlan/TipoExecucao.php` |
| Relacionamento | ✅ `PlanoDeAcao::tipoExecucao()` (`BelongsTo`) | `PlanoDeAcao.php:106` |
| Scope | ✅ `scopePorTipo($tipo)` filtra por `dsc_tipo_execucao` | `PlanoDeAcao.php:247` |
| Campo | ✅ `cod_tipo_execucao` no `$fillable` | `PlanoDeAcao.php:46` |

### 3.2 O que está quebrado

**Defeito 1 — o cliente instalado nunca recebe tipo novo.** Causa e solução em
[16](16-padrao-seeders-idempotentes.md). É a razão pela qual esta demanda existe.

**Defeito 2 — o Model não qualifica o schema.**

```php
// app/Models/ActionPlan/TipoExecucao.php
protected $table = 'tab_tipo_execucao';   // ❌ sem schema
```

O `search_path` de `config/database.php` começa em **`pei`**. Uma query sem schema procura
`pei.tab_tipo_execucao` antes de `action_plan.tab_tipo_execucao`. Hoje funciona porque não existe
tabela homônima em `pei` — funciona **por sorte**, não por construção. O `CLAUDE.md` manda
qualificar sempre.

> Este não é o único Model assim. `AtividadeCadeiaValor` tem o mesmo problema
> (`protected $table = 'tab_atividade_cadeia_valor'`). Levantamento completo em
> [12 — Achados transversais](12-visao-holistica-achados-transversais.md).

**Defeito 3 — o formulário não foi lido nesta análise.** O pedido cita "o formulário para inserir
um novo Plano de Ação". Se hoje o combo de tipo está vazio na tela do cliente, pode ser (a) a
tabela vazia naquela instalação, (b) o componente não carregando a lista, ou (c) o campo nem
existir no formulário. **São causas diferentes com correções diferentes** — o passo 2 da seção 5
distingue.

---

## 4. Soluções avaliadas

### Opção A — `DELETE` do tipo "Iniciativa" ❌
Viola FK se houver iniciativa vinculada, e é irreversível. Descartada.

### Opção B — `UPDATE` do rótulo "Iniciativa" → "Projeto" ❌
Parece elegante e é armadilha: iniciativas classificadas como "Iniciativa" viram "Projeto"
**sem que ninguém tenha decidido isso**, e o UUID `ecef6a50-…` (referenciado por
`TipoExecucao::INICIATIVA` no código) passa a significar outra coisa. Reescreve a história do
cliente em silêncio. Descartada.

### Opção C — Remanejar e aposentar por `soft delete` ✅ **RECOMENDADA**
Três passos declarados, nesta ordem, e cada um verificável:

1. **Medir** quantas iniciativas apontam para "Iniciativa".
2. **Remanejar** para o tipo que o gestor decidir, com registro de quantas linhas mudaram.
3. **Aposentar** o tipo (`deleted_at`), só se o passo 2 zerou a contagem.

O `soft delete` preserva o histórico de auditoria: um registro antigo em `pei.tab_audit` que
menciona o UUID continua resolvível.

### Opção D — C, mas com a aposentadoria adiada ⚠️
Fazer 1 e 2 agora, e deixar 3 para a versão seguinte, depois de o gestor confirmar em base real
que nada quebrou. **Recomendada se a contagem do passo 1 vier alta.**

---

## 5. Plano de ação

### Passo 1 — Medir (🔴 exige autorização explícita do gestor, mesmo sendo `SELECT`)

```sql
-- Quantas iniciativas por tipo, incluindo as sem tipo
SELECT COALESCE(t.dsc_tipo_execucao, '(sem tipo)') AS tipo,
       COUNT(p.cod_plano_de_acao) AS qtd
  FROM action_plan.tab_plano_de_acao p
  LEFT JOIN action_plan.tab_tipo_execucao t
         ON t.cod_tipo_execucao = p.cod_tipo_execucao
 WHERE p.deleted_at IS NULL
 GROUP BY 1
 ORDER BY 2 DESC;
```

Rodar em **dev** e, com o gestor, em **pelo menos uma base de cliente real**. O número decide
entre a Opção C e a Opção D.

> **Resultado da medição:** _(preencher — não estimar)_
> | Ambiente | Ação | Iniciativa | Projeto | (sem tipo) |
> |---|---:|---:|---:|---:|
> | dev `fs_v1` | | | | |
> | cliente ___ | | | | |

### Passo 2 — Diagnosticar o formulário
Ler `app/Livewire/ActionPlan/ListarPlanos.php` e a blade correspondente, e responder por escrito:
- O campo de tipo existe no formulário de criação?
- A lista vem de `TipoExecucao::all()` ou de constante hardcoded?
- Há filtro de `deleted_at`?

### Passo 3 — Qualificar o schema no Model
```php
protected $table = 'action_plan.tab_tipo_execucao';
```
Alteração de uma linha. Rodar os testes do módulo depois — é o tipo de mudança que parece
inofensiva e muda a query emitida.

### Passo 4 — Criar `TipoExecucaoSeeder`
Sobre `VocabularioControlado` ([16](16-padrao-seeders-idempotentes.md)):

- `sincronizar()` garante `Ação` e `Projeto` com os UUIDs de constante.
- `aposentar([TipoExecucao::INICIATIVA])` — que **recusa e relata** se ainda houver filho.
- O seeder **não remaneja sozinho**: remanejamento é decisão, não efeito colateral de `db:seed`.

### Passo 5 — Comando de remanejamento, explícito e separado
```
php artisan iniciativas:remanejar-tipo --de=Iniciativa --para=Ação [--dry-run]
```
- `--dry-run` é o **padrão**; sem ele o comando não escreve.
- Imprime a contagem antes, pede confirmação, imprime a contagem depois.
- É o comando que o cliente roda uma vez, no roteiro de atualização.

### Passo 6 — Ajustar o código que referencia `TipoExecucao::INICIATIVA`
`grep -rn "TipoExecucao::INICIATIVA\|'Iniciativa'" app/ resources/` e tratar cada ocorrência.
A constante **permanece no Model** (registros antigos de auditoria a referenciam), com comentário
dizendo que está aposentada.

### Passo 7 — Roteiro de atualização de versão
```
git pull
php artisan migrate
php artisan iniciativas:remanejar-tipo --de=Iniciativa --para=Ação --dry-run   # confere
php artisan iniciativas:remanejar-tipo --de=Iniciativa --para=Ação            # aplica
php artisan db:seed --class=TipoExecucaoSeeder
php artisan view:clear && php artisan config:clear
```

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R2.0** Medição | Tabela da seção 5 preenchida com números reais | Nenhuma célula vazia; nenhuma estimada | Autorização |
| **R2.1** Decisão | Gestor escolhe C ou D, e o tipo de destino do remanejamento | Registrado neste arquivo | R2.0 |
| **R2.2** Diagnóstico do form | Resposta às 3 perguntas do passo 2 | Escrita neste arquivo | — |
| **R2.3** Schema qualificado | `TipoExecucao::$table` com prefixo | Testes do módulo verdes | — |
| **R2.4** Seeder | `TipoExecucaoSeeder` idempotente | Testes 16-B05 a 16-B09 verdes | [16](16-padrao-seeders-idempotentes.md) R16.2 |
| **R2.5** Comando de remanejamento | `iniciativas:remanejar-tipo` com `--dry-run` padrão | Teste que prova que sem a flag nada é escrito | R2.1 |
| **R2.6** Limpeza de referências | Nenhum caminho de escrita usa `INICIATIVA` | `grep` limpo, exceto a constante comentada | R2.5 |
| **R2.7** Roteiro | `.md` de atualização de versão | Gestor segue sem perguntar nada | R2.4, R2.5 |
| **R2.8** Ensaio em dump de cliente | Sequência completa numa cópia de base real | Zero iniciativa órfã ao final | R2.7 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 02-B01 | Medir distribuição por tipo em dev | P | autorização | Números na tabela |
| 02-B02 | Medir o mesmo em base de cliente | P | gestor | idem |
| 02-B03 | Ler `ListarPlanos.php` + blade e responder o passo 2 | P | — | Respostas escritas |
| 02-B04 | Qualificar `$table` em `TipoExecucao` | P | — | `php artisan test --filter=Plano` verde |
| 02-B05 | `TipoExecucaoSeeder` sobre `VocabularioControlado` | M | 16-B02 | Verde |
| 02-B06 | Teste: seeder em base com os 3 tipos antigos não apaga nada | M | 02-B05 | Verde |
| 02-B07 | Teste: seeder em base vazia cria exatamente `Ação` e `Projeto` | P | 02-B05 | Verde |
| 02-B08 | Comando `iniciativas:remanejar-tipo` | M | 02-B01 | Teste 02-B09 |
| 02-B09 | Teste: sem `--dry-run` explícito, o comando não escreve | M | 02-B08 | Verde |
| 02-B10 | Teste: `aposentar()` recusa tipo com iniciativa vinculada | M | 16-B03 | Verde |
| 02-B11 | Limpar referências a `TipoExecucao::INICIATIVA` no código de escrita | P | 02-B08 | `grep` justificado |
| 02-B12 | Corrigir o combo do formulário, se o passo 2 apontar defeito | M | 02-B03 | Tela com os 2 tipos |
| 02-B13 | Roteiro de atualização de versão | P | 02-B08 | Revisado pelo gestor |
| 02-B14 | Ensaio ponta a ponta em dump de cliente | M | 02-B13 | Zero órfã |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **Antes e depois, com número.** A query do passo 1 rodada antes e depois do remanejamento.
   Depois: `Iniciativa = 0`, e a soma total de iniciativas **idêntica** à de antes. Se a soma
   mudou, alguma linha se perdeu — e a entrega está errada, por mais que a tela pareça certa.
2. **Zero órfã:**
   ```sql
   SELECT COUNT(*) FROM action_plan.tab_plano_de_acao p
     JOIN action_plan.tab_tipo_execucao t ON t.cod_tipo_execucao = p.cod_tipo_execucao
    WHERE p.deleted_at IS NULL AND t.deleted_at IS NOT NULL;
   ```
   Precisa vir `0`.
3. **As duas populações**, como manda [16](16-padrao-seeders-idempotentes.md) seção 8: base limpa
   e dump de cliente.
4. **Idempotência:** `db:seed --class=TipoExecucaoSeeder` três vezes; a partir da segunda,
   relatório zerado.
5. **A tela:** abrir o formulário de nova Iniciativa e ver exatamente dois tipos no combo.
6. **`php artisan test`** no escopo do módulo.

---

## 9. O que NÃO foi verificado

- **Nenhuma contagem de linha** — o item mais importante desta análise, e ele depende de
  autorização para consultar o banco
- O formulário de criação em si (passo 2 do plano)
- Se algum cliente renomeou os rótulos dos tipos
- Se `2026_02_01_003107_add_dsc_polaridade_and_migrate_legacy_types.php` já mexeu nesses tipos —
  o nome sugere que sim, e o arquivo precisa ser lido antes do passo 4
