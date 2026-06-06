# Runbook — Migração v1 → v2 (Sistema de Planejamento Estratégico)

**Público-alvo:** analistas de infraestrutura responsáveis pela execução.
**Tempo estimado:** 30–60 min (depende do volume de dados).
**Reversível até a Fase 5.** O legado é preservado em quarentena e só é descartado por comando explícito.

> Documento técnico complementar (mapa De→Para campo a campo):
> `documentacao/migracao-legado-v1-para-v2-mapa-de-para.md`

---

## 0. Pré-requisitos (obrigatórios)

| Item | Requisito | Como verificar |
|---|---|---|
| Banco | É o banco PostgreSQL da **v1** em produção/cópia | contém o schema `pei` |
| Versão | **PostgreSQL ≥ 9.4** | `SHOW server_version;` |
| Backup | `pg_dump` completo **feito e testado** | ver passo 1 |
| Código | Esta v2 (Laravel 12) já implantada e apontando para o **mesmo** banco | `.env` → `DB_*` |
| Acesso | Usuário do banco com permissão de DDL (CREATE/ALTER SCHEMA) | — |
| `pgcrypto` | Necessário só em PG entre 9.4 e 12 (PG 13+ já tem `gen_random_uuid`) | o assistente **detecta e oferece criar** automaticamente |

> ⚠️ **Não execute `php artisan migrate` manualmente.** Todo o processo — inclusive a criação do schema novo — é orquestrado pelo comando único abaixo.

> 💬 **O comando é um assistente interativo.** Ele faz perguntas **apenas operacionais** e **sempre por seleção** (Sim/Não ou escolha de opção) — você nunca digita texto livre. Para execução não-interativa (automação), use `--force`.

---

## 1. Backup (NÃO PULE)

```bash
pg_dump -U <usuario> -h <host> -F c -b -v -f backup_v1_antes_migracao.dump <nome_do_banco>
```

Guarde o arquivo `.dump` em local seguro. **Sem este backup, não prossiga.**

---

## 2. Simulação (dry-run) — não grava nada

Roda a leitura completa e produz o relatório de contagens **sem alterar o banco**.
Serve para detectar tabelas/colunas inesperadas antes de tocar nos dados.

```bash
php artisan migracao:v1-para-v2 --dry-run
```

Confira a **pré-visualização** (tabela "Destino × Origem × Registros × Situação") e o bloco "Decisões aplicadas".
- `IGNORADA` / `(não encontrada)` → tabela legada com nome diferente do esperado. **Anote e avise o desenvolvedor antes de seguir.**
- `OK` em todas e total de registros coerente → pode prosseguir.

---

## 3. Execução real

```bash
php artisan migracao:v1-para-v2
```

O comando executa em sequência:

| Fase | O que faz | Reversível? |
|---|---|---|
| 0 — Pré-checagem | Versão do PG, confirmação de backup, estado do banco | — |
| 1 — Quarentena | `pei` → `legacy_pei`; tabelas de `public` → `legacy_public` | ✅ sim |
| 2 — Construção | Cria os 6 schemas e tabelas da v2 (`migrate`) | ✅ sim (drop schemas v2) |
| 3 — Transferência | Copia os dados legacy_* → v2, **preservando os UUIDs** | ✅ sim (legado intacto) |
| 4 — Validação | Compara contagens origem × destino | — |

Durante a execução, o assistente faz estas perguntas **de seleção** (responda com as setas/Sim-Não):

| Pergunta | Quando aparece | Resposta segura |
|---|---|---|
| Backup completo foi feito? | sempre (Fase 0) | **Sim** (você já fez no passo 1) |
| Criar a extensão `pgcrypto` agora? | só se faltar `gen_random_uuid` (PG < 13) | **Sim** |
| Prosseguir mesmo com tabelas não encontradas? | só se o preview detectar | **Não** (cancele e acione o suporte) |
| Prosseguir com a gravação? | após o preview de volumes | **Sim** (se conferiu o preview) |
| O que fazer com o legado? | ao final | **Manter como rede de segurança** |

### Opções (flags) do comando

| Flag | Efeito |
|---|---|
| `--dry-run` | Simula e mostra os volumes; não grava nada |
| `--force` | Não faz perguntas (assume as opções seguras) — para automação/reprodutibilidade |
| `--pular-backup` | Pula a confirmação de backup (**não recomendado**) |
| `--descartar-legado` | Remove os schemas `legacy_*` ao final (**irreversível**) |
| `--migrar-auditoria` | Inclui `audits`/`tab_audit` na migração (padrão: **não** migra) |
| `--status-entrega-padrao="..."` | Status para entregas legadas não reconhecidas (padrão: `"Não Iniciado"`; valor inválido aborta com a lista correta) |

---

## 4. Conferência pós-migração

1. Leia a tabela de validação no fim da execução: coluna **OK** deve estar `✓` em todas as linhas com status `OK`.
2. Acesse a aplicação e confira visualmente: PEI, Objetivos, Indicadores, Planos, Entregas.
3. Teste login com um usuário conhecido (as senhas foram preservadas; se `trocarsenha` estiver ativo, o sistema pedirá troca no primeiro acesso — comportamento esperado).
4. **Permissões / Super Admin:** na v2, o acesso total é definido pelo **perfil** "Super Administrador" (não pelo campo `adm`). Observe, no fim da execução, a linha `users.adm sincronizado pelo perfil — Super Admins: N`. Se **N = 0**, o serviço emite um alerta: atribua o perfil "Super Administrador" a um usuário em **/usuarios**, senão o sistema ficará sem administrador com acesso total. Confira também se os demais usuários têm o perfil correto por organização.

> Enquanto a conferência não terminar, **não descarte o legado**. Ele continua disponível em `legacy_pei` / `legacy_public` como rede de segurança.

---

## 5. Descarte do legado (somente após validação OK)

Quando tudo estiver conferido e aprovado:

```bash
php artisan migracao:v1-para-v2 --descartar-legado
```

Isso remove **definitivamente** `legacy_pei` e `legacy_public`. **Irreversível** (use o backup do passo 1 se precisar voltar).

---

## Em caso de erro

- O comando **aborta na primeira falha** e informa a fase. O legado **não é destruído**.
- Para reverter a quarentena manualmente (se a Fase 1 rodou e quer voltar à v1):

```sql
-- desfaz a Fase 1 (apenas se a v2 ainda NÃO foi populada/precisar reverter)
DROP SCHEMA IF EXISTS strategic_planning CASCADE;   -- e os demais schemas v2, se criados
DROP SCHEMA IF EXISTS action_plan CASCADE;
DROP SCHEMA IF EXISTS performance_indicators CASCADE;
DROP SCHEMA IF EXISTS risk_management CASCADE;
DROP SCHEMA IF EXISTS organization CASCADE;
ALTER SCHEMA legacy_pei RENAME TO pei;
-- mover de volta as tabelas de legacy_public para public, uma a uma:
-- ALTER TABLE legacy_public.<tabela> SET SCHEMA public;
```

- Em qualquer dúvida, **restaure o `.dump` do passo 1** — o estado original volta integralmente.

---

## Casos em que a migração NÃO traz dados (esperado)

| Item | Motivo |
|---|---|
| **Auditoria** (`audits`, `tab_audit`) | Decisão de projeto: não migrada. Histórico de auditoria fica apenas no backup. |
| **Riscos** (`risk_management.*`) | Módulo **novo** na v2; não existia na v1. Nasce vazio. |
| **Temas Norteadores** | Conceito novo da v2. Nasce vazio. |
| **Campos de Entrega sem equivalente** (unidade de medida, item entregue, quantidade prevista) | Preservados dentro de `json_propriedades` da entrega (não se perdem, mas mudam de formato). |
