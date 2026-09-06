# 08 — "Quem pode lançar a evolução?" — a regra existe e é invisível

> **Tema:** E — Acesso, transparência e governança · **Tipo:** Melhoria (produto), Correção (documentação)
> **Impacto:** alto — foi o que travou o maior cliente · **Risco de regressão:** baixo
> **Relacionada a:** [06](06-grau-de-satisfacao-antes-do-indicador.md) — o caso concreto do mesmo defeito
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "O nosso maior cliente [Presidência da República] foi fazer o preenchimento e ficou sem entender
> quem poderá fazer a gestão, cadastrar a evolução, dos Indicadores, das Iniciativas, antigo Plano
> de Ação, das Entregas."

---

## 2. Conclusão primeiro

**As regras existem, são sofisticadas e estão corretas. O sistema simplesmente nunca as diz a
ninguém.**

O cliente não encontrou a resposta porque a resposta só existe em três lugares que ele não pode
ler: a `MATRIZ` do `CapacidadeResolver`, as seis Policies, e o pivô `perfil × usuário × plano`.
Nenhuma tela, nenhum texto, nenhum manual traduz isso.

E há um caso em que a regra **contradiz a própria orientação do sistema** — documentado em
[06](06-grau-de-satisfacao-antes-do-indicador.md): o sistema manda o cliente configurar os Graus de
Satisfação e a tela devolve 403. Provavelmente foi aí que a Presidência travou.

---

## 3. A regra real, levantada do código

O sistema combina **três camadas**. Cada uma pode negar; todas precisam permitir.

```
  ┌──────────────────────────────────────────────────────────────┐
  │ 1. RBAC — CapacidadeResolver::MATRIZ                         │
  │    "este PERFIL pode 'editar' no MÓDULO 'indicadores'?"      │
  │    Nega por padrão. Super Admin passa incondicionalmente.    │
  └────────────────────────────┬─────────────────────────────────┘
                               ▼
  ┌──────────────────────────────────────────────────────────────┐
  │ 2. ABAC organizacional — ResolveEscopoOrganizacional         │
  │    "este registro pertence a uma ORGANIZAÇÃO que eu alcanço?"│
  │    podeAcessarOrganizacao() → organizacaoIdsPermitidas()     │
  └────────────────────────────┬─────────────────────────────────┘
                               ▼
  ┌──────────────────────────────────────────────────────────────┐
  │ 3. Titularidade — User::isGestorResponsavel($codPlano)       │
  │    "eu sou o responsável DESTE plano especificamente?"       │
  │    Vem do pivô perfil × usuário × cod_plano_de_acao          │
  └──────────────────────────────────────────────────────────────┘
```

### 3.1 Quem pode o quê — a tabela que o cliente precisa ver

Derivada de `CapacidadeResolver::MATRIZ` + `IndicadorPolicy` + `EntregaPolicy` + `PlanoDeAcaoPolicy`:

| | Super Admin | Admin de Unidade | Gestor Responsável | Gestor Substituto |
|---|:---:|:---:|:---:|:---:|
| **Indicadores** — ver | ✅ | ✅ | ✅ | ✅ |
| **Indicadores** — criar | ✅ | ✅ | ✅ | ❌ |
| **Indicadores** — **lançar evolução** | ✅ | ✅ (da sua unidade) | ✅ (dos planos que ele responde) | ✅ (editar) |
| **Indicadores** — excluir | ✅ | ✅ | ❌ | ❌ |
| **Iniciativas** — ver | ✅ | ✅ | ✅ | ✅ |
| **Iniciativas** — criar | ✅ | ✅ | ✅ | ❌ |
| **Iniciativas** — editar | ✅ | ✅ | ✅ (as suas) | ✅ |
| **Iniciativas** — excluir | ✅ | ✅ | ❌ | ❌ |
| **Entregas** — criar/editar | ✅ | ✅ | ✅ | ✅ |
| **Entregas** — excluir | ✅ | ✅ | ✅ | ❌ |
| **Graus de Satisfação** | ✅ | ❌ 🔴 | ❌ 🔴 | ❌ 🔴 |
| **Auditoria, Perfis, Configurações** | ✅ | ❌ | ❌ | ❌ |

🔴 = o bloqueio que quebra o fluxo. Ver [06](06-grau-de-satisfacao-antes-do-indicador.md).

> ⚠️ Esta tabela foi **derivada do código, não validada com o gestor**. Ela é insumo para a
> decisão de R8.0, não a decisão.

### 3.2 A sutileza que ninguém adivinha

"Gestor Responsável" **não é um crachá geral** — é vínculo a uma iniciativa específica:

```php
// app/Models/User.php:198
public function isGestorResponsavel(string $codPlanoDeAcao): bool
{
    return $this->perfisAcesso()
        ->where('tab_perfil_acesso.cod_perfil', PerfilAcesso::GESTOR_RESPONSAVEL)
        ->wherePivot('cod_plano_de_acao', $codPlanoDeAcao)   // ← por iniciativa
        ->exists();
}
```

A mesma pessoa é Gestor Responsável da Iniciativa A e **não é nada** na Iniciativa B. Ela consegue
lançar evolução num indicador e não no outro, **na mesma tela**, sem nenhuma explicação. Para quem
está preenchendo, isso parece defeito do sistema.

### 3.3 O comentário no código que confirma que o assunto é difícil

`app/Policies/IndicadorPolicy.php:96-101` traz um comentário sobre uma falha já corrigida — a
verificação usava a organização **selecionada na sessão** em vez da organização real do indicador,
o que permitia a um Admin de Unidade qualquer editar indicador de outra unidade.

A correção está feita e está certa. O que ela mostra é que **essas regras são difíceis até para
quem as escreve** — e que documentá-las tem valor defensivo, não só didático.

---

## 4. Onde exatamente o cliente trava

| Momento | O que ele vê | O que ele conclui |
|---|---|---|
| Cria usuário | Quatro perfis com nomes parecidos, sem descrição | Escolhe pelo nome, no chute |
| Abre a Iniciativa | Nada diz quem é o responsável dela | "Ninguém é responsável?" |
| Tenta lançar evolução | Botão ausente, ou 403 | "Está quebrado" |
| Sistema manda configurar Graus | Clica e toma 403 | "Está quebrado mesmo" |
| Procura ajuda | Não há tela de ajuda sobre papéis | Liga para o suporte |

**Nenhum desses cinco momentos tem um texto que explique.**

---

## 5. Soluções propostas

### Opção A — Explicar o perfil no momento de escolher ✅
Na tela de usuários, cada perfil ganha uma frase e um exemplo:

> **Admin de Unidade** — responde pelo planejamento da sua unidade e das unidades abaixo dela.
> Cria e exclui indicadores e iniciativas, define a régua do Grau de Satisfação, e lança evolução
> de tudo que pertence à sua unidade.
>
> **Gestor Responsável** — responde por **iniciativas específicas**. Lança a evolução dos
> indicadores e das entregas **das iniciativas em que foi designado** — não das outras.
> ⚠️ Este perfil só funciona se a pessoa for vinculada a pelo menos uma iniciativa.

O aviso do Gestor Responsável é o mais importante: hoje é possível criar o perfil e não vincular
ninguém a nada, gerando um usuário que não consegue fazer nada e não sabe por quê.

### Opção B — "Quem responde por isto" na própria tela ✅ **a que resolve a queixa**
Um bloco no cabeçalho da Iniciativa e do Indicador:

```
  Quem responde por esta Iniciativa
  ┌──────────────────────────────────────────────────────┐
  │ Responsável   Maria Silva          lança evolução ✅ │
  │ Substituto    (não designado)                     ⚠️ │
  │ Unidade       SECOM/PR — Admin: João Souza           │
  │                                                      │
  │ Você (Gestor Substituto): pode editar, não excluir.  │
  └──────────────────────────────────────────────────────┘
```

A última linha é a que responde à pergunta do cliente **no momento em que ele a faz** — em vez de
mandá-lo procurar um manual.

⚠️ Nome de pessoa em tela é dado pessoal. Isto vale **só** na área autenticada, e o bloco tem de
respeitar a capacidade `ver-sensivel`. Em tela pública, nada disso aparece
([07](07-mapa-estrategico-publico.md), seção 4).

### Opção C — Explicar a ausência do botão ✅ **barata e de alto retorno**
Hoje, quem não pode simplesmente não vê o botão. Trocar por botão desabilitado com o motivo:

> 🔒 *Somente o Gestor Responsável desta Iniciativa ou o Admin da unidade podem lançar evolução.
> O responsável atual é **Maria Silva**.*

Botão sumido é ambíguo (será que existe? será que quebrou?). Botão com motivo é honesto — e é
exatamente o mandamento nº 6c do `CLAUDE.md` aplicado à ausência.

### Opção D — Tela "Papéis e responsabilidades" ✅
Uma página em `/ajuda/papeis` com a tabela da seção 3.1, **gerada a partir da `MATRIZ`**, não
escrita à mão. Assim ela nunca envelhece: mudou a matriz, mudou a página.

### Opção E — Alerta de iniciativa sem responsável ✅
No painel de gestão: *"3 iniciativas não têm Gestor Responsável designado — ninguém pode lançar
evolução nelas."* Ataca a causa antes de virar dúvida.

### Opção F — Afrouxar as regras ❌
Tentador, e errado. As regras estão certas; a comunicação é que falta. Afrouxar autorização em
sistema de Estado para resolver problema de UX troca um incômodo por um risco.

---

## 6. Plano de ação

1. **Validar a tabela da seção 3.1 com o gestor.** Ela foi derivada do código — pode revelar que
   alguma regra atual não é a desejada. Essa conversa vem antes de qualquer tela.
2. Resolver o bloqueio dos Graus de Satisfação ([06](06-grau-de-satisfacao-antes-do-indicador.md)) —
   é o travamento concreto, e sem ele o resto é cosmético.
3. Descrição de perfil na tela de usuários (Opção A).
4. Bloco "Quem responde por isto" na Iniciativa e no Indicador (Opção B), respeitando `ver-sensivel`.
5. Botão desabilitado com motivo, no lugar de botão ausente (Opção C).
6. Tela `/ajuda/papeis` derivada da `MATRIZ` (Opção D).
7. Alerta de iniciativa sem responsável (Opção E).
8. Testes por perfil — hoje há 7 testes em `tests/Feature/Authorization/`; a matriz tem 4 perfis
   × 8 módulos × 6 capacidades. A cobertura é parcial.

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R8.0** Validação | Tabela 3.1 confirmada ou corrigida pelo gestor | Registrada aqui | — |
| **R8.1** Destravar | Graus de Satisfação acessível | [06](06-grau-de-satisfacao-antes-do-indicador.md) R6.1 | R8.0 |
| **R8.2** Descrição de perfil | Texto + aviso do Gestor Responsável | Gestor aprova | R8.0 |
| **R8.3** Quem responde | Bloco na Iniciativa e no Indicador | Respeita `ver-sensivel` | R8.0 |
| **R8.4** Botão com motivo | Desabilitado + explicação | Nenhum botão de ação some sem motivo visível | R8.3 |
| **R8.5** `/ajuda/papeis` | Tabela derivada da `MATRIZ` | Mudar a matriz muda a página | R8.0 |
| **R8.6** Alerta de órfã | Contagem de iniciativas sem responsável | Número confere com o banco | R8.3 |
| **R8.7** Cobertura de testes | Teste por perfil × módulo × capacidade | Verde; matriz coberta | R8.1 |
| **R8.8** Manual | Seção no manual operacional | Revisado pelo gestor | R8.5 |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 08-B01 | Validar a tabela 3.1 com o gestor | M | — | Registrada |
| 08-B02 | Descrição dos 4 perfis na tela de usuários | M | B01 | Tela |
| 08-B03 | Aviso "Gestor Responsável precisa de vínculo com iniciativa" | P | B02 | Tela |
| 08-B04 | Bloco "Quem responde" na Iniciativa | M | B01 | Tela |
| 08-B05 | Bloco "Quem responde" no Indicador | M | B04 | Tela |
| 08-B06 | Respeitar `ver-sensivel` nos blocos | M | B05 | Teste por perfil |
| 08-B07 | Botão desabilitado + motivo em lançar evolução | M | B05 | Tela |
| 08-B08 | Mesmo padrão em editar e excluir | M | B07 | Tela |
| 08-B09 | `/ajuda/papeis` derivada da `MATRIZ` | M | B01 | Alterar a matriz altera a página |
| 08-B10 | Entrada de `/ajuda/papeis` na `MATRIZ` (todos os perfis) | P | B09 | Todo perfil acessa |
| 08-B11 | Alerta de iniciativa sem Gestor Responsável | M | B04 | Número confere |
| 08-B12 | Teste: Gestor Responsável da Iniciativa A **não** lança na B | M | B01 | Verde |
| 08-B13 | Teste: Admin de Unidade não edita indicador de outra unidade | M | B01 | Verde (regressão do bug da Policy) |
| 08-B14 | Teste: para todo módulo da `MATRIZ`, um teste por perfil | G | B12 | Verde |
| 08-B15 | Seção "Quem faz o quê" no manual operacional | M | B09 | Gestor aprova |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

A queixa foi *"ficou sem entender"*. Não se verifica isso rodando teste — verifica-se assim:

1. **O percurso do cliente, com o perfil do cliente.** Criar quatro usuários, um por perfil, e
   percorrer com cada um: criar indicador, lançar evolução, criar iniciativa, mover entrega.
   Em **todo** ponto em que a ação for negada, a tela precisa dizer **por quê e quem pode**.
   Um único "botão que sumiu sem explicação" reprova a entrega.
   🔴 **Nunca verificar isso como Super Admin** — ele passa por tudo, e foi por isso que o
   defeito chegou ao cliente.
2. **A tabela 3.1 confrontada com o comportamento real**, célula a célula. Se alguma divergir,
   ou a tabela está errada, ou o código está — e as duas hipóteses importam.
3. **08-B12 e 08-B13**, que travam as duas regras mais sutis: titularidade por iniciativa e
   escopo organizacional real (esta última já quebrou uma vez).
4. **`/ajuda/papeis` derivada:** alterar a `MATRIZ` num rascunho e confirmar que a página muda
   sozinha. Se não mudar, a página vai envelhecer e voltamos ao ponto de partida.
5. `tests/Feature/Authorization/` inteiro — obrigatório pelo `CLAUDE.md` ao tocar autorização.

---

## 10. O que NÃO foi verificado

- **Se a tabela 3.1 reflete o que o gestor quer** — ela reflete o que o código faz. São coisas
  diferentes até que ele confirme
- `PlanoDeAcaoPolicy`, `RiscoPolicy`, `OrganizationPolicy` e `UserPolicy` não foram lidas por
  inteiro; a tabela pode ter imprecisão nas linhas de Iniciativas
- Como o Gestor Substituto é vinculado, e se ele herda o escopo do titular
- Se existe tela hoje que mostre os responsáveis de uma iniciativa (há
  `AtribuirResponsaveis` e RACI — não foram abertos nesta análise)
- Quantas iniciativas em base real estão sem Gestor Responsável
- Se o manual operacional já tem alguma seção sobre perfis
