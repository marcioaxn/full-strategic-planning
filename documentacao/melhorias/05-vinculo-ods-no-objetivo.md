# 05 — Vincular ODS ao criar o Objetivo Estratégico

> **Tema:** D — Fluxo de preenchimento do ciclo · **Tipo:** Melhoria
> **Impacto:** baixo (a funcionalidade existe) · **Risco de regressão:** baixo
> **Verificado em:** 05/09/2026

---

## 1. 🔴 Conclusão primeiro: isto já está implementado

> "No momento de inserir um novo Objetivo Estratégico em `/objetivos` o cliente já pode relacionar
> `/agenda2030`."

**Já pode.** O vínculo de ODS está no mesmo modal que cria o objetivo, e é gravado no mesmo
`save()`. Não há trabalho de implementação nesta demanda — há trabalho de **descoberta**: o cliente
não percebeu que já podia.

### Evidência

| Elemento | Local | Estado |
|---|---|---|
| Modal único para criar **e** editar | `listar-objetivos.blade.php:612-789` (`{{ $objetivoId ? 'Editar' : 'Novo' }}`) | ✅ |
| Bloco "Contribuição para a Agenda 2030 (ODS)" **dentro** do modal | `listar-objetivos.blade.php:728-780` | ✅ |
| Grade dos 17 ODS clicáveis | `:749` — `@foreach($todosOds as $ods)` com `x-ods-badge` | ✅ |
| Seleção com limite institucional | `ListarObjetivos.php:222` `toggleOds()`, `MAX_ODS = 3` | ✅ |
| Campo de contribuição por ODS escolhido | `listar-objetivos.blade.php:766-780` | ✅ |
| Persistência **na criação** | `ListarObjetivos.php:331-341` — `$objetivo->ods()->sync($syncOds)` roda depois do `updateOrCreate`, para novo e para existente | ✅ |
| Exibição na listagem | `:512-516` e `:551-555` — badges de ODS por objetivo | ✅ |

O limite de 3 ODS por objetivo tem aviso próprio, e é uma decisão de método defensável (foco
estratégico). Está correto.

---

## 2. Então qual é o problema real?

Se a funcionalidade existe e o gestor pediu que ela existisse, **a funcionalidade não está sendo
encontrada**. Isso é um defeito de produto tanto quanto um campo faltando — só que mais barato de
corrigir.

Três hipóteses, em ordem de probabilidade:

### H1 — O bloco fica no fim de um modal muito alto ⭐ mais provável
O modal é `modal-xl` e vai da linha 612 à 789 da blade. O bloco de ODS começa na **728** — ou seja,
depois de identificação, descrição, perspectiva, hierarquia, objetivo-pai e ordem. O cliente
preenche o essencial, encontra o botão "Salvar" com o olhar e **nunca rola até o fim**.

É o mesmo padrão da demanda [11](11-erro-conexao-pei-e-modal-de-risco.md), em que o "Vínculo
Estratégico" ficou enterrado no bloco de Monitoramento e o cliente reclamou de que "deveria ter
mais evidência".

### H2 — Nada anuncia que o vínculo existe
Não há indicação, na tela de objetivos ou no painel `/agenda2030`, de que os dois módulos se
conversam. O cliente vai ao painel ODS, vê que está vazio, e não tem como saber que o
preenchimento acontece na tela de objetivos.

### H3 — O `catch` genérico esconde falha de gravação ⚠️
`ListarObjetivos.php:350`:

```php
} catch (\Exception $e) {
    $this->errorMessage = 'Não foi possível processar o registro do objetivo. Por favor, revise as informações e tente novamente.';
    $this->showErrorModal = true;
}
```

`$e` é capturado e **descartado** — não é logado. Se o `sync()` de ODS falhar, o cliente vê
"revise as informações", revisa, e o problema persiste sem rastro em lugar nenhum. Não é possível
afirmar que isso está acontecendo; é possível afirmar que, se acontecer, **ninguém vai saber**.

> Este padrão não é exclusivo deste componente. Levantamento em
> [12 — Achados transversais](12-visao-holistica-achados-transversais.md).

---

## 3. Um achado colateral que vale registrar

`ListarObjetivos.php:302`:

```php
'cod_perspectiva' => 'required|exists:tab_perspectiva,cod_perspectiva',
```

A tabela **não está qualificada com o schema**. Funciona porque o `search_path` percorre os seis
schemas e encontra `tab_perspectiva` em `strategic_planning`.

E aqui está a lição que conecta com a demanda [11](11-erro-conexao-pei-e-modal-de-risco.md):
**se alguém "corrigir" isto para `exists:strategic_planning.tab_perspectiva,...`, a tela quebra
na hora** — o Laravel lê o ponto como `conexão.tabela` e vai procurar uma conexão chamada
`strategic_planning`, que não existe. É exatamente o erro que a demanda 11 relata.

Ou seja: as duas telas estão em lados opostos da mesma armadilha. A correção precisa ser a mesma
nas duas, e está documentada em [11](11-erro-conexao-pei-e-modal-de-risco.md).

---

## 4. Soluções propostas

### Opção A — Promover o bloco de ODS na hierarquia visual ✅ **RECOMENDADA**
- Contador **no cabeçalho do modal**: `Agenda 2030: 0/3` — visível sem rolar.
- Âncora clicável no topo que rola até o bloco.
- Borda ou fundo destacado quando `count($odsSelecionados) === 0`, sinalizando "há algo aqui".

### Opção B — Passo explícito no modal de sucesso ✅ **RECOMENDADA, barata**
O modal de sucesso já existe (`showSuccessModal`) e já traz texto orientando o próximo passo.
Quando o objetivo for salvo **sem nenhum ODS**, acrescentar uma ação:

> *"Este objetivo ainda não indica contribuição para a Agenda 2030.
> [Vincular ODS agora] · [Depois]"*

Sem obrigatoriedade — nem todo objetivo contribui para ODS, e forçar produziria vínculo falso.

### Opção C — Ponte a partir do painel `/agenda2030` ✅ **RECOMENDADA**
Quando o painel ODS estiver vazio ou parcialmente preenchido, mostrar quantos objetivos ainda não
têm vínculo e um link direto para a tela de objetivos. Resolve a H2 pela outra ponta.

### Opção D — Tornar o vínculo obrigatório ❌
Produziria ODS escolhido no chute para destravar o formulário. Dado ruim é pior que dado ausente,
e o `CLAUDE.md` é explícito sobre número que não se sustenta. Descartada.

### Opção E — Logar a exceção do `catch` ✅ **obrigatória, independente do resto**
```php
} catch (\Exception $e) {
    report($e);   // ou Log::error(...) com contexto, sem dado sensível
    $this->errorMessage = '...';
}
```
Uma linha. Sem ela, a H3 permanece invisível para sempre.

---

## 5. Plano de ação

1. **Reproduzir antes de mexer.** Criar um objetivo com 2 ODS e conferir em
   `strategic_planning.rel_objetivo_ods` que as duas linhas foram gravadas com a contribuição.
   Se gravou, H1/H2 são a explicação e o trabalho é de descoberta. Se não gravou, é a H3 e vira
   correção de bug.
2. Logar a exceção (Opção E) — independente do resultado do passo 1.
3. Contador de ODS no cabeçalho do modal (Opção A).
4. Ação no modal de sucesso quando nenhum ODS foi vinculado (Opção B).
5. Ponte no painel `/agenda2030` (Opção C).
6. Teste Livewire cobrindo o caminho da tela: criar objetivo **com** ODS e afirmar a linha na
   tabela de relacionamento.

---

## 6. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R5.0** Reprodução | Resposta escrita: grava ou não grava | Contagem em `rel_objetivo_ods` antes/depois | Autorização de leitura |
| **R5.1** Log | `report($e)` no `catch` | Exceção forçada aparece em `storage/logs` | — |
| **R5.2** Contador no cabeçalho | `Agenda 2030: n/3` visível sem rolar | Inspeção | — |
| **R5.3** Modal de sucesso | Ação "Vincular ODS agora" quando vazio | Aparece só quando `count === 0` | R5.2 |
| **R5.4** Ponte do painel ODS | Link e contagem de objetivos sem vínculo | Número confere com o banco | R5.0 |
| **R5.5** Teste | Criação com ODS persiste o vínculo | Verde | R5.0 |
| **R5.6** Correção de bug | Só se R5.0 apontar falha | — | R5.0 |

---

## 7. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 05-B01 | Reproduzir criação com ODS e conferir a gravação | P | autorização | Contagem antes/depois |
| 05-B02 | `report($e)` nos `catch` de `ListarObjetivos` | P | — | Exceção forçada logada |
| 05-B03 | Contador `Agenda 2030: n/3` no cabeçalho do modal | P | — | Tela |
| 05-B04 | Âncora do cabeçalho até o bloco de ODS | P | B03 | Tela |
| 05-B05 | Destaque visual quando nenhum ODS selecionado | P | B03 | Tela |
| 05-B06 | Ação "Vincular ODS agora" no modal de sucesso | M | B03 | Só quando vazio |
| 05-B07 | Contagem de objetivos sem ODS no painel `/agenda2030` | M | B01 | Número confere com o banco |
| 05-B08 | Link do painel ODS para a tela de objetivos | P | B07 | Tela |
| 05-B09 | Teste Livewire: criar objetivo com 2 ODS e contribuição | M | B01 | Verde |
| 05-B10 | Teste: `MAX_ODS` respeitado — 4º clique não seleciona | P | B09 | Verde |
| 05-B11 | Ajustar a mensagem de sucesso ("planos de ação" → "Iniciativas") | P | [01](01-renomear-plano-de-acao-para-iniciativas.md) | `grep` |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 8. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

O pedido foi "o cliente já pode relacionar". A verificação honesta tem duas metades:

1. **Que a funcionalidade grava** — teste Livewire que cria um objetivo com 2 ODS e afirma as
   duas linhas em `strategic_planning.rel_objetivo_ods`, com `txt_contribuicao` preenchida. É a
   asserção sobre o dado que sai, não sobre a aparência do código.
2. **Que o cliente encontra** — que é o problema real. Só se confirma com o cliente. A verificação
   possível de minha parte é: abrir o modal e conferir que o estado do vínculo é visível
   **sem rolar a página**.

Além disso: `php artisan view:clear` antes de julgar a tela, e conferência de que o painel
`/agenda2030` mostra o mesmo total que a tela de objetivos — divergência entre módulos é o que
mais rápido destrói a confiança na plataforma.

---

## 9. O que NÃO foi verificado

- **Se a gravação de fato funciona em execução** — o código foi lido, a tela não foi exercitada
- Quantos objetivos hoje têm vínculo com ODS (exige leitura no banco)
- Se o painel `/agenda2030` lê da mesma tabela de relacionamento que a tela grava
- Se os relatórios (`integrado`, `objetivos`) exibem os ODS vinculados
- Se `x-ods-badge` renderiza corretamente os 17 ODS — o componente não foi aberto
- Qual das três hipóteses (H1, H2, H3) é a verdadeira — depende do passo 1 do plano
