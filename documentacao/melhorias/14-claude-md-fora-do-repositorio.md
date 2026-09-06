# 14 — `CLAUDE.md` fora do repositório remoto

> **Tema:** G — Higiene do repositório · **Tipo:** Correção
> **Impacto:** médio · **Risco de regressão:** baixo · **Risco de execução:** ⚠️ médio (envolve histórico)
> **Prioridade:** onda 0, junto com [11](11-erro-conexao-pei-e-modal-de-risco.md)
> **Verificado em:** 05/09/2026

---

## 1. O pedido

> "CLAUDE.md não pode ir para o repositório remoto. Deve viver apenas no local."

---

## 2. Estado atual, verificado

```
$ git ls-files | grep -i claude
.claude/hooks/divida-congelada.json
.claude/hooks/gerar-divida.php
.claude/hooks/guarda-arquivo.php
.claude/hooks/guarda-comando.php
.claude/settings.json
CLAUDE.md                          ← rastreado
```

| Fato | Valor |
|---|---|
| Tamanho | 810 linhas |
| Commits que o tocaram | **3** |
| Entrou em | `e40fa4d`, 01/06/2026 |
| Última alteração | `0d53f1e`, 05/09/2026 |
| Repositório | `github.com/marcioaxn/full-strategic-planning` — **público** |
| Está no `.gitignore`? | ❌ Não |

O `.gitignore` já trata o diretório `.claude/` seletivamente (linhas 51-55), mantendo
`settings.json` e `hooks/` versionados — que **é o correto**, são regra de equipe. O `CLAUDE.md`
da raiz é que ficou de fora dessa decisão.

---

## 3. Por que o gestor está certo

Três motivos, em ordem de peso:

### 3.1 O arquivo carrega premissa de outro projeto — e isso já causou dano
Corrigido hoje: a seção de Migrations afirmava que produção não roda `migrate`, premissa importada
de um projeto de instância única. **Este produto é multicliente e cada cliente executa `migrate` e
`db:seed` no próprio terminal.** Um arquivo de instrução que viaja entre projetos e vira parte do
repositório propaga o erro para quem clonar.

⚠️ O mesmo bloco afirma que produção roda PostgreSQL 9.3. Essa afirmação **não foi revalidada** —
ver [16](16-padrao-seeders-idempotentes.md) §9.

### 3.2 É contexto de trabalho, não código do produto
Quem clona o repositório recebe: o perfil do CEO, a forma de trabalhar do gestor, mandamentos de
conduta escritos para um assistente, e o caso do `logoutUrl` em produção. Nada disso é do produto.
Um cliente que clone o repositório lê material interno da relação de trabalho.

### 3.3 Contém mapa da arquitetura em repositório público
Os seis schemas com contagem de tabelas, o inventário de módulos, a estrutura da matriz de
autorização, os quatro perfis, e a menção de que a única cifra existente é feita à mão em
`SystemSetting` por nome de chave. Não é vulnerabilidade — é **reconhecimento**, e reduz o
trabalho de quem estiver mapeando o sistema.

> Coerente com a decisão já tomada de manter achados de segurança em
> `.claude/seguranca-em-aberto.md`, fora do versionamento.

---

## 4. 🔴 O que remover do índice NÃO resolve

`git rm --cached CLAUDE.md` tira o arquivo dos commits **futuros**. Os três commits que já
existem continuam com o conteúdo, e o GitHub o serve para sempre.

| Ação | Resolve o futuro? | Resolve o passado? |
|---|:---:|:---:|
| `git rm --cached` + `.gitignore` | ✅ | ❌ |
| + reescrita de histórico + force-push | ✅ | ⚠️ **parcialmente** |

O "parcialmente" tem nome: o GitHub retém objetos em `refs/pull/*`, que nenhuma reescrita alcança.
É a mesma limitação registrada em `.claude/seguranca-em-aberto.md` item 1.

**Consequência honesta:** se o conteúdo do `CLAUDE.md` for considerado sensível, tirá-lo agora
reduz a exposição mas **não a desfaz**. Se for considerado apenas inadequado — que é a leitura
mais provável — remover do índice basta, e reescrever histórico é custo sem retorno.

**Esta é uma decisão do gestor**, e as duas respostas são defensáveis.

---

## 5. Soluções avaliadas

### Opção A — `git rm --cached` + `.gitignore` ✅ **RECOMENDADA**
```
git rm --cached CLAUDE.md
# .gitignore:
/CLAUDE.md
git commit -m "chore: tira o CLAUDE.md do versionamento"
```
O arquivo continua no disco e o Claude Code continua lendo. Reversível. Não mexe em histórico.

### Opção B — A + reescrita de histórico ⚠️
Só se o gestor considerar o conteúdo sensível. Custo: 3 commits reescritos, force-push,
invalidação de clones existentes — e ainda assim os `refs/pull/*` permanecem.

### Opção C — Manter versionado e sanitizar ❌
Contraria o pedido direto do gestor, e um arquivo com duas versões (uma real, uma pública) tende
a divergir. Descartada.

### Opção D — A + um `CONTRIBUTING.md` público ✅ **complemento**
O que é **regra do projeto** (schemas, convenção de nomenclatura, obrigação de qualificar schema,
padrões de segurança proibidos) tem valor para qualquer pessoa que trabalhe no código. Isso migra
para um documento público e limpo. O que é **contexto da relação** sai do repositório.

Separa bem: `CONTRIBUTING.md` = regra do código; `CLAUDE.md` = contexto de trabalho, local.

---

## 6. Plano de ação

1. **Perguntar ao gestor:** o conteúdo é sensível (Opção B) ou apenas inadequado (Opção A)?
2. `git rm --cached CLAUDE.md`.
3. Acrescentar `/CLAUDE.md` ao `.gitignore`, junto do bloco `.claude/` que já existe.
4. **Fazer cópia de segurança do arquivo fora do repositório antes de commitar.** `rm --cached`
   não apaga o arquivo — mas um `git clean -xdf` posterior apagaria, e aí a única cópia se perde.
5. Confirmar que os guardas do `.claude/hooks/` **continuam versionados** — são regra de equipe.
6. `git status` conferindo que só `.gitignore` e a remoção do índice aparecem.
7. Se a resposta de 1 for "sensível": planejar a reescrita em separado, com o gestor ciente da
   limitação dos `refs/pull/*`.
8. Avaliar a Opção D.

---

## 7. Roadmap

| Fase | Entregável | Critério de aceite | Depende de |
|---|---|---|---|
| **R14.0** Decisão | Sensível ou inadequado? | Registrado aqui | — |
| **R14.1** Cópia de segurança | `CLAUDE.md` copiado fora do repositório | Arquivo existe fora | — |
| **R14.2** Remoção do índice | `git rm --cached` + `.gitignore` | `git ls-files` não lista; arquivo no disco | R14.1 |
| **R14.3** Verificação | Guardas seguem versionados | `git ls-files .claude/` inalterado | R14.2 |
| **R14.4** Push | Remoto sem o arquivo | GitHub não mostra `CLAUDE.md` no HEAD | R14.3 |
| **R14.5** Reescrita | Só se R14.0 = sensível | 0 ocorrências em todos os blobs | R14.4 |
| **R14.6** `CONTRIBUTING.md` | Regras do código, público e limpo | Gestor aprova | R14.4 |

---

## 8. Backlog

| ID | Tarefa | Esforço | Depende | Verificação |
|---|---|---|---|---|
| 14-B01 | Perguntar ao gestor: sensível ou inadequado? | P | — | Resposta registrada |
| 14-B02 | Copiar `CLAUDE.md` para fora do repositório | P | — | Arquivo existe fora |
| 14-B03 | `git rm --cached CLAUDE.md` | P | B02 | Fora do índice, no disco |
| 14-B04 | `/CLAUDE.md` no `.gitignore` | P | B03 | `git status` limpo depois de editar o arquivo |
| 14-B05 | Confirmar `.claude/hooks/` ainda versionado | P | B04 | `git ls-files .claude/` |
| 14-B06 | Commit e push | P | B05 | GitHub sem o arquivo no HEAD |
| 14-B07 | Verificar que o Claude Code segue lendo o arquivo | P | B06 | Sessão nova carrega as instruções |
| 14-B08 | Reescrita de histórico (só se B01 = sensível) | G | B06 | 0 ocorrências nos blobs |
| 14-B09 | Extrair regras do código para `CONTRIBUTING.md` | M | B06 | Gestor aprova |
| 14-B10 | Revalidar a premissa de PostgreSQL 9.3 | M | — | Ver [16](16-padrao-seeders-idempotentes.md) §9 |

**Esforço:** P = até 1h · M = 1 a 4h · G = mais de 4h

---

## 9. Como eu confirmaria que está correto

> *"Claude, como você confirmaria que isso que você entregou está correto e atende ao que foi pedido?"*

1. **`git ls-files | grep -i claude`** — só as 5 linhas de `.claude/`, sem `CLAUDE.md`.
2. **`ls -la CLAUDE.md`** — o arquivo continua no disco. Se sumiu, a entrega destruiu o que devia
   preservar. Por isso a cópia de segurança (14-B02) vem antes.
3. **Editar o arquivo e rodar `git status`** — não pode aparecer como modificado. Prova que o
   `.gitignore` pegou.
4. **`git ls-files .claude/`** — as 5 entradas intactas. Este é o erro fácil: acrescentar
   `CLAUDE*` ao `.gitignore` e derrubar os guardas junto. Os guardas são regra de equipe e **têm**
   de continuar versionados.
5. **Abrir o repositório no GitHub** e confirmar que o arquivo não está no HEAD.
6. **Abrir uma sessão nova do Claude Code** e confirmar que as instruções ainda carregam — o
   arquivo precisa continuar funcionando localmente, que é o ponto do pedido.
7. **`git diff --cached --name-status`** antes do commit: só `.gitignore` modificado e
   `CLAUDE.md` removido. Nada mais.

---

## 10. O que NÃO foi verificado

- **Se o gestor considera o conteúdo sensível** — determina se há ou não reescrita de histórico
- Se alguém já clonou o repositório com o arquivo (não é verificável)
- Se há fork público do repositório — um fork mantém o histórico independentemente da reescrita
- Se outro projeto da máquina usa este mesmo `CLAUDE.md` como base (o arquivo carrega premissa de
  outro projeto, o que sugere que sim)
- Se o `README.md`, também versionado e público, repete algum conteúdo do `CLAUDE.md`
- Se a premissa de PostgreSQL 9.3 é verdadeira para este produto — pendência registrada em
  [16](16-padrao-seeders-idempotentes.md) §9
