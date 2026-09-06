# 12 — Visão holística: o que o levantamento revelou e ninguém pediu

> **Tema:** G — Qualidade transversal · **Tipo:** Correção estrutural
> **Impacto:** alto no médio prazo · **Risco de regressão:** varia por achado
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "É preciso ter uma visão holística nesse projeto de forma que as ações de melhoria e de correções
> não fiquem apenas nas partes onde foram efetivadas. É preciso por meio de estudo e análises [ver]
> se outras partes podem ser melhoradas ou corrigidas."

Este documento é o resultado dessa varredura. **Todo número aqui foi medido com comando** — nenhum
é estimativa.

---

## 2. Resumo: sete achados, por gravidade

| # | Achado | Medida | Gravidade |
|---|---|---|---|
| A1 | Metade dos Models não qualifica o schema | **26 de 51** | 🔴 Alta |
| A2 | O farol mistura critérios de PEIs diferentes | **13 de 17** consultas sem filtro | 🔴 Alta |
| A3 | Exceção engolida na camada de tela | **15 de 23** `catch` sem log | 🟠 Média-alta |
| A4 | Cobertura de teste ausente onde mais importa | **10 de 58** componentes; **1 de 10** services | 🟠 Média-alta |
| A5 | `exists:` com schema quebra a validação | **2** ocorrências | 🔴 Alta (1 já em produção) |
| A6 | Query dentro de Blade | **2** arquivos | 🟡 Média |
| A7 | Arquivo de backup em pasta de produção | **1** arquivo, 220 linhas | 🟡 Baixa |

A2 e A5 já têm estudo próprio ([06](06-grau-de-satisfacao-antes-do-indicador.md) e
[11](11-erro-conexao-pei-e-modal-de-risco.md)); aparecem aqui porque o padrão é transversal.

---

## 3. A1 — 🔴 Metade dos Models não qualifica o schema

### O número

```
$ grep -rh "protected \$table" app/Models/ | sed "s/.*= *'//;s/'.*//"
51 Models declaram $table
25 qualificam o schema     ✅  strategic_planning.tab_valores
26 NÃO qualificam          ❌  tab_indicador
```

**Exatamente metade.** Não é descuido pontual — são duas convenções coexistindo no mesmo projeto.

Entre os não qualificados: `tab_indicador`, `tab_objetivo`, `tab_perspectiva`, `tab_risco`,
`tab_plano_de_acao`, `tab_entregas`, `tab_grau_satisfacao`, `tab_organizacoes`,
`tab_perfil_acesso`, `tab_evolucao_indicador` — ou seja, **as tabelas centrais do sistema**.

### Por que funciona hoje, e por que isso é o problema

O `search_path` (`config/database.php:97`) percorre os seis schemas em ordem, começando por `pei`.
`tab_indicador` não existe em `pei`, então o PostgreSQL segue até achar em
`performance_indicators`.

**Funciona por ausência de colisão, não por construção.** No dia em que qualquer tabela homônima
aparecer em `pei` — ou em qualquer schema anterior na lista — 26 Models passam a ler a tabela
errada, **em silêncio**, sem erro. É a pior classe de defeito: o sistema continua respondendo, com
o dado errado.

O `CLAUDE.md` já manda qualificar sempre. A regra existe; a metade do código não a segue.

### ⚠️ A armadilha que este achado esconde

Corrigir A1 é uma linha por Model — e **não é trabalho de `sed`**. Cada `$table` alterado muda o
SQL emitido. E a demanda [11](11-erro-conexao-pei-e-modal-de-risco.md) mostra que "qualificar o
schema" tem uma exceção: na regra `exists:`/`unique:`, o ponto significa **conexão**, e
qualificar quebra. Uma varredura que trate os dois casos igual troca um defeito latente por um
defeito imediato.

### Correção proposta
1. Qualificar Model a Model, em lotes por schema, rodando os testes do módulo a cada lote.
2. Guarda no `guarda-arquivo.php`: `$table` sem ponto em `app/Models/` vira aviso, com os 26
   atuais na dívida congelada — que **só encolhe**.

---

## 4. A2 — 🔴 O farol mistura critérios de PEIs diferentes

Detalhado em [06](06-grau-de-satisfacao-antes-do-indicador.md) §3. **13 das 17 consultas a
`GrauSatisfacao` não filtram por `cod_pei`**, embora o Model tenha `cod_pei` e `num_ano`.

Aparece aqui porque o padrão é transversal e vale a pergunta em todo o sistema:

> **Que outra entidade tem `cod_pei` e é consultada sem ele?**

Candidatas a auditar: `Perspectiva`, `Objetivo`, `Risco`, `TemaNorteador`, `AtividadeCadeiaValor`,
`AnaliseAmbiental`, `Rae`, `CenarioProspectivo`.

⚠️ **Não medido.** É a varredura 12-B05 abaixo, e pode ser o achado mais grave da lista.

---

## 5. A3 — 🟠 Exceção engolida na camada de tela

### O número

```
app/Livewire/: 23 blocos catch
                8 registram (report() ou Log::) nas 4 linhas seguintes
               15 NÃO registram nada
```

### O padrão

`app/Livewire/StrategicPlanning/ListarObjetivos.php:350`:

```php
} catch (\Exception $e) {
    $this->errorMessage = 'Não foi possível processar o registro do objetivo. Por favor, revise as informações e tente novamente.';
    $this->showErrorModal = true;
}
```

`$e` é capturado e **descartado**. Consequências, em ordem:

1. **O cliente recebe orientação errada.** "Revise as informações" quando o problema foi
   indisponibilidade do banco faz a pessoa revisar dado correto, repetidamente.
2. **Não há rastro.** Nada em `storage/logs`. O suporte não tem o que investigar.
3. **O defeito nunca chega ao desenvolvimento.** Falha intermitente em produção é invisível.

É o inverso do mandamento nº 6 do `CLAUDE.md`: a interface afirma saber a causa quando não sabe.

### Correção proposta
Uma linha por bloco — `report($e);` — e mensagem que **não afirma a causa**:

> *"Não foi possível salvar. A ocorrência foi registrada. Se persistir, informe o código
> `ERR-7A3F` ao suporte."*

Um identificador curto que apareça na tela **e** no log fecha o ciclo entre a queixa do cliente e
a linha de log.

⚠️ Cuidado: `report()` envia a exceção ao handler, e a mensagem pode conter dado do formulário.
Confirmar que o canal de log não sai da máquina antes de generalizar.

---

## 6. A4 — 🟠 Cobertura de teste ausente onde mais importa

### Os números

| Camada | Existem | Citados em teste | Cobertura |
|---|---:|---:|---:|
| Componentes Livewire | 58 | **10** | 17% |
| Services | 10 | **1** (`CapacidadeResolver`) | 10% |
| Arquivos de teste | 31 | — | — |

### O que isso significa na prática

Dos 31 arquivos de teste, boa parte é do scaffold do Jetstream (`RegistrationTest`,
`PasswordResetTest`, `TwoFactorAuthenticationSettingsTest`, `BrowserSessionsTest`,
`DeleteAccountTest`, `ExampleTest`...). O que testa **regra deste sistema** é menor que 31.

**Nenhum service de cálculo tem teste.** `IndicadorCalculoService` — que produz o farol e o
percentual de execução, os dois números que o `CLAUDE.md` nomeia como o maior risco de
desonestidade da interface — **não é citado em teste algum**.

Os três defeitos deste ciclo confirmam o custo:

| Defeito | Teste que teria pego |
|---|---|
| `exists:pei.users,id` ([11](11-erro-conexao-pei-e-modal-de-risco.md)) | Livewire no `save()` pelo caminho da tela |
| Guia manda para tela com 403 ([06](06-grau-de-satisfacao-antes-do-indicador.md)) | Coerência guia × permissão |
| Farol de PEI trocado ([06](06-grau-de-satisfacao-antes-do-indicador.md) §3) | Cálculo com dois PEIs de faixas diferentes |

**Nenhum dos três seria pego por teste de unidade.** Todos exigem o caminho da tela — que é
exatamente o que a regra nº 5 do topo do `CLAUDE.md` diz.

### Correção proposta — priorizada, não exaustiva
Chegar a 100% é fantasia. A ordem que dá retorno:

1. **`IndicadorCalculoService`** — farol, polaridade, ponderação. É o número que o CEO lê.
2. **Coerência entre módulos** — mesmo objetivo, mesmo total no Dashboard, Mapa, ODS e PDF.
3. **Autorização por perfil** — [08](08-quem-gerencia-o-que-papeis-e-responsaveis.md) 08-B14.
4. **`save()` dos componentes de CRUD**, pelo caminho da tela.

> ⚠️ **Bloqueio real:** o `CLAUDE.md` registra que o banco de teste (porta 5434) não está no ar
> nesta máquina. **Nada disso avança sem ele.** Levantar o banco de teste é pré-requisito de todo
> este achado, e provavelmente o item de maior alavancagem do documento inteiro.

---

## 7. A5 — 🔴 `exists:` com schema

Detalhado em [11](11-erro-conexao-pei-e-modal-de-risco.md). Duas ocorrências:
`ListarRiscos.php:224` (já quebrando em produção) e `GerenciarRae.php:311` (latente, porque é
`nullable`).

Transversal porque revela uma **exceção não documentada** à regra de qualificar schema. Sem
registrá-la no `CLAUDE.md`, a correção de A1 vai reintroduzir A5.

---

## 8. A6 — 🟡 Query dentro de Blade

```
$ grep -rl "\\App\\Models\|::where(\|::orderBy(" resources/views/
2 arquivos
```

Um deles: `resources/views/livewire/partials/objetivo-contexto.blade.php:87` —
`GrauSatisfacao::orderBy('vlr_minimo')->get()`.

Três problemas de uma vez: N+1 se o partial for incluído em laço; nenhum teste de componente
alcança; e é um dos 13 pontos de A2.

**Correção:** mover para o componente. Guarda que recuse `::where(` / `::orderBy(` / `\App\Models`
em `resources/views/`.

---

## 9. A7 — 🟡 Arquivo de backup em pasta de produção

`resources/views/relatorios/identidade_bkp_20260116.blade.php` — 220 linhas, fora do sistema de
design dos relatórios ([09](09-reconstrucao-ui-dos-relatorios.md) §2.2).

Backup pertence ao git, não à pasta de views. **Antes de excluir:** `grep -rn "identidade_bkp"`
em todo o projeto, para provar que nada o referencia.

---

## 10. Plano de ação

### Onda 1 — o que destrava tudo
1. **Levantar o banco de teste** (porta 5434). Sem isso, A4 não avança e nenhum outro achado tem
   verificação automática.
2. Registrar no `CLAUDE.md` a exceção de `exists:`/`unique:` (A5).

### Onda 2 — os números que o CEO lê
3. Testes de `IndicadorCalculoService` (A4, prioridade 1).
4. Corrigir A2 nos 13 pontos ([06](06-grau-de-satisfacao-antes-do-indicador.md) R6.4).
5. Auditar as 8 outras entidades com `cod_pei` (A2, varredura).
6. Teste de coerência entre módulos.

### Onda 3 — a base
7. Qualificar os 26 Models, em lotes por schema (A1).
8. Guarda de `$table` sem schema, com dívida congelada.
9. `report($e)` nos 15 blocos silenciosos (A3).
10. Identificador de ocorrência na mensagem de erro.

### Onda 4 — higiene
11. Mover as queries das 2 Blades (A6).
12. Guarda contra query em Blade.
13. Excluir o arquivo de backup, após provar que nada o referencia (A7).

---

## 11. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R12.0** Banco de teste | Porta 5434 no ar | `php artisan test` roda | — |
| **R12.1** Exceção documentada | `CLAUDE.md` + guarda | Escrito e travado | — |
| **R12.2** Teste do cálculo | `IndicadorCalculoService` coberto | Farol, polaridade e ponderação verdes | R12.0 |
| **R12.3** A2 corrigido | 13 pontos com `cod_pei` | [06](06-grau-de-satisfacao-antes-do-indicador.md) R6.6 verde | R12.0 |
| **R12.4** Varredura `cod_pei` | 8 entidades auditadas | Tabela preenchida | R12.3 |
| **R12.5** Coerência entre módulos | Teste do mesmo total em 4 lugares | Verde | R12.2 |
| **R12.6** A1 corrigido | 51 Models qualificados | Testes por lote verdes | R12.0, R12.1 |
| **R12.7** Guarda de `$table` | Dívida congelada com 26 | Dispara em Model novo | R12.6 |
| **R12.8** A3 corrigido | 15 `catch` com `report()` | Exceção forçada aparece no log | R12.0 |
| **R12.9** Código de ocorrência | Identificador na tela e no log | Rastreável ponta a ponta | R12.8 |
| **R12.10** A6 corrigido | 2 Blades sem query | Guarda ativa | R12.3 |
| **R12.11** A7 | Backup excluído | `grep` prova que nada referencia | [09](09-reconstrucao-ui-dos-relatorios.md) |

---

## 12. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 12-B01 | Levantar o banco de teste na porta 5434 | M | — | `php artisan test` roda |
| 12-B02 | Documentar a exceção `exists:`/`unique:` | P | — | `CLAUDE.md` |
| 12-B03 | Guarda contra `exists:`/`unique:` com schema | M | B02 | Dispara no teste |
| 12-B04 | Testes de `IndicadorCalculoService` | G | B01 | Verde |
| 12-B05 | **Auditar 8 entidades com `cod_pei` consultadas sem ele** | M | B01 | Tabela medida |
| 12-B06 | Corrigir o que B05 encontrar | G | B05 | Testes verdes |
| 12-B07 | Teste de coerência entre módulos | M | B04 | Verde |
| 12-B08 | Qualificar Models de `performance_indicators` | P | B01 | Testes do módulo |
| 12-B09 | Qualificar Models de `action_plan` | M | B08 | idem |
| 12-B10 | Qualificar Models de `risk_management` | P | B09 | idem |
| 12-B11 | Qualificar Models de `strategic_planning` restantes | M | B10 | idem |
| 12-B12 | Qualificar Models de `organization` e `pei` | P | B11 | idem |
| 12-B13 | Guarda de `$table` sem schema + dívida congelada | M | B12 | Dispara em Model novo |
| 12-B14 | `report($e)` nos 15 `catch` de Livewire | M | B01 | Exceção forçada logada |
| 12-B15 | Confirmar que o canal de log não sai da máquina | P | B14 | Config lida |
| 12-B16 | Código de ocorrência na mensagem de erro | M | B14 | Rastreável |
| 12-B17 | Mover query de `objetivo-contexto.blade.php` | M | B05 | Sem `::` de Model na Blade |
| 12-B18 | Identificar e mover a segunda Blade com query | P | B17 | idem |
| 12-B19 | Guarda contra query em Blade | M | B18 | Dispara no teste |
| 12-B20 | `grep` provando que `identidade_bkp` não é referenciado | P | — | Saída vazia |
| 12-B21 | Excluir `identidade_bkp_20260116.blade.php` | P | B20 | Relatórios geram |
| 12-B22 | Auditar os 27 `{!! !!}` e documentar a origem de cada | G | — | Tabela |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 13. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **Cada número deste documento é reproduzível por comando.** Os comandos estão no corpo do texto.
   Qualquer pessoa pode rodá-los e chegar aos mesmos 26, 13, 15, 10 e 27. Se algum não bater, o
   documento está errado e precisa ser corrigido — não defendido.
2. **A1:** depois de qualificar, `grep -rh "protected \$table" app/Models/ | grep -vc "\."` = 0.
   E os testes de cada módulo verdes **por lote** — não só no fim.
3. **A3:** forçar uma exceção em cada um dos 15 pontos e confirmar a linha em
   `storage/logs/laravel.log`. Sem forçar, é confiança na leitura do código.
4. **A4:** a métrica não é "cobertura subiu". É: **os três defeitos deste ciclo agora falham**
   se alguém reverter a correção. Escrever o teste que pega o bug já conhecido é a única forma
   honesta de saber que o teste serve para alguma coisa.
5. **A6/A7:** `grep` limpo, e os relatórios continuam gerando.
6. **O documento inteiro:** relido daqui a um ciclo. Se algum número mudou para pior, alguma
   trava faltou.

---

## 14. O que NÃO foi verificado

- **A varredura de `cod_pei` nas 8 outras entidades (12-B05)** — pode ser o achado mais grave, e
  não foi feita
- **Nenhum teste foi executado** — o banco de teste não está no ar nesta máquina. Todo o
  diagnóstico é leitura estática
- Os 27 `{!! !!}` não foram auditados um a um; o número confere com o `CLAUDE.md`, a origem do
  dado de cada um não foi verificada
- Quais dos 31 arquivos de teste são scaffold do Jetstream e quais testam regra do sistema —
  a estimativa da §6 é qualitativa
- N+1 nas telas de listagem — não medido; exigiria `DB::listen` sob dado real
- `resources/js/` não foi varrido (o `CLAUDE.md` registra que não há ESLint, e o caso `logoutUrl`
  do mandamento nº 4 vive lá)
- Índices do banco versus as queries mais frequentes — exige acesso ao banco
- Se algum dos 26 Models não qualificados **depende** da resolução por `search_path` de propósito
