# Contribuindo com o Sistema PEI

Regras técnicas do código deste repositório. Curtas, verificáveis e — onde possível — cobradas
por trava automática.

---

## Stack

Laravel 12 · Livewire 4 (+ `livewire/blaze`) · Jetstream 5 + Fortify · Sanctum 4 · Alpine.js 3 ·
Bootstrap 5.3 · Vite 7 + Sass · PHP ^8.2 · **PostgreSQL multi-schema**.

Testes: Pest 4. Lint: Pint (preset Laravel).
Chart.js e Bootstrap Icons entram por CDN em `resources/views/layouts/app.blade.php` — não estão
no `package.json`.

> Laravel 12 e Livewire 4 mudaram bastante em relação às versões anteriores. Consulte a
> documentação da versão instalada antes de estender peça do framework, registrar em Service
> Provider ou adotar pacote do ecossistema — memória vinda do Laravel 10/11 ou do Livewire 3 erra
> em silêncio.

---

## 🔴 Os seis schemas — qualificar sempre

| Schema | Domínio |
|---|---|
| `pei` | Infraestrutura: usuários, sessões, cache, filas, auditoria, configurações, relatórios |
| `strategic_planning` | Ciclo PEI, identidade, perspectivas, objetivos, SWOT/PESTEL, ODS, cadeia de valor |
| `action_plan` | Iniciativas, entregas, RACI, lições aprendidas |
| `performance_indicators` | Indicadores, metas por ano, linha de base, evolução |
| `risk_management` | Riscos, mitigações, ocorrências, vínculo risco↔objetivo |
| `organization` | Organizações, perfis de acesso e vínculos usuário↔organização↔perfil |

O `search_path` (`config/database.php`, conexão `pgsql`) lista os seis e **começa por `pei`**:
SQL sem schema cai lá, silenciosamente.

**Qualifique o schema** em migration, em `DB::` e no `$table` de Model:

```php
protected $table = 'performance_indicators.tab_indicador';   // ✅
protected $table = 'tab_indicador';                          // ❌ resolve por sorte
```

### ⚠️ A exceção: `exists` e `unique` na validação

Nas regras de validação, o Laravel lê o ponto como **nome de conexão**, não como schema:

```php
'campo' => 'required|exists:users,id'         // ✅ o search_path resolve
'campo' => 'required|exists:pei.users,id'     // ❌ "Database connection [pei] not configured"
```

Isso já quebrou a tela de riscos em produção. Há guarda automática e teste de varredura
(`tests/Feature/RiskManagement/SalvarRiscoPelaTelaTest.php`).

---

## Banco de dados

### Migration e seeder são o caminho até o cliente

Este é um **produto multicliente**: cada cliente instala a própria instância e executa
`php artisan migrate` e `php artisan db:seed` no terminal dele.

- **Migration cria estrutura. Seeder popula dado de referência.** Nunca semeie vocabulário
  controlado dentro do `up()` de uma migration: migration já aplicada não roda de novo, e o
  cliente instalado nunca recebe a opção nova.
- **Migration já aplicada é imutável.** Corrigir estrutura = migration nova.
- **Seeder tem de ser idempotente**: rodar duas vezes não duplica nem sobrescreve o que o cliente
  ajustou.
- Criar migration com o editor, não via `artisan make:migration`.
- Rodar uma específica: `php artisan migrate --path=database/migrations/ARQUIVO.php`.

### Primeira instalação

`php artisan app:init-schemas` cria os seis schemas e garante `gen_random_uuid()`.
No PostgreSQL 12 a função vem da extensão `pgcrypto`, que **precisa ser instalada num schema do
`search_path`** — em `public` (o default do `CREATE EXTENSION`) a função existe e mesmo assim não
é resolvida. O comando cuida disso e, se faltar privilégio, imprime o SQL exato para o DBA.

### Compatibilidade de versão

Verifique a versão mínima de PostgreSQL suportada antes de usar sintaxe recente. Em ambientes
antigos (9.3), evite: `ON CONFLICT` (inclusive via `upsert()` e `insertOrIgnore()`),
`json_build_object`, `jsonb`, `GENERATED AS IDENTITY`, `FILTER (WHERE ...)`,
`CREATE INDEX IF NOT EXISTS`, `ADD COLUMN IF NOT EXISTS`.

**O helper do framework também gera SQL** — leia o SQL emitido antes de adotar driver ou pacote
que toque o banco. Teste que só confere o resultado não protege.

---

## 🔴 Autorização — RBAC + ABAC

Três camadas. Todas precisam permitir; qualquer uma pode negar.

1. **`App\Services\Authorization\CapacidadeResolver`** — matriz `módulo × perfil → capacidades`
   (`acessar`, `ver-sensivel`, `criar`, `editar`, `excluir`, `exportar`). **Nega por padrão.**
2. **Policies** (`app/Policies/`) — somam o recorte organizacional (ABAC) e a titularidade.
3. **Componente Livewire + Blade** — onde o usuário de fato bate.

Quatro perfis, com UUID fixo em `App\Models\PerfilAcesso`: `SUPER_ADMIN`, `ADMIN_UNIDADE`,
`GESTOR_RESPONSAVEL`, `GESTOR_SUBSTITUTO`. O Super Admin é liberado incondicionalmente.

**Ao criar módulo, rota ou capacidade nova:** acrescente a entrada na `MATRIZ` no mesmo commit.
Esquecer não gera erro — gera acesso negado silencioso para todo perfil não-Super-Admin.

> **Todo método `public` de um componente Livewire é invocável direto pelo navegador.**
> Filtrar a listagem não protege a ação: a verificação vai dentro do método.

---

## Segurança — não negociável

### Proibido

| Padrão | Motivo | Alternativa |
|---|---|---|
| `->withoutVerifying()` · `'verify' => false` | Desliga TLS | Corrigir o certificado |
| `DB::select("... '" . $var . "'")` | SQL Injection | `DB::select('... ?', [$var])` |
| `{!! $model->campo !!}` em Blade | Stored XSS | `{{ }}` ou `strip_tags()` com allowlist |
| `'isRemoteEnabled' => true` no DomPDF | SSRF | `false` |
| `trustProxies(at: '*')` | IP spoofing | IPs reais do proxy |
| `?key=$apiKey` em URL | Chave vaza no log de acesso | Header `Authorization` |
| `ini_set(...)` em escopo de arquivo | Vale para toda requisição | Dentro do método que precisa |
| Credencial em arquivo rastreado | Vaza no histórico | `.env` + `env()` na config |

### Obrigatório

- **Upload:** validar por `mimes:` **antes** de tocar o arquivo. Nunca confiar em
  `getClientOriginalExtension()`.
- **Arquivo gerado com conteúdo de cliente** vai em disco privado (`relatorios`), nunca no disco
  `public` — e o download passa por Policy.
- **`$request->all()`** direto em `create()`/`update()`: usar FormRequest ou `only()`.
- **Não logar** `session_id`, token ou senha.
- **Campo novo no `$fillable` do `User`**: conferir que não permite escalonamento de privilégio.

---

## Texto sensível nasce cifrado

Conteúdo de **texto livre sobre pessoa** usa a criptografia nativa do Laravel, **no Model**:

```php
protected function casts(): array
{
    return ['txt_observacao' => 'encrypted', 'jsn_dados' => 'encrypted:array'];
}

protected $auditExclude = ['txt_observacao', 'jsn_dados'];   // obrigatório junto
```

Não resolva com `setAttribute()`, mutator ou accessor: nenhum cobre `toArray()`, serialização
Livewire e exports de forma uniforme — o cast cobre.

**Não se cifra:** chave, coluna usada em `JOIN`/índice/`ORDER BY` (a cifra é não determinística),
e-mail, senha, estrutura do Laravel, vocabulário controlado, data e número.

Três armadilhas medidas: o texto cifrado é ~185 bytes + 1,37× o original (`varchar(N)` com N < 700
precisa virar `text` **antes**); sem `$auditExclude` o valor em claro reaparece na auditoria; e a
asserção do teste é sobre o **dado que sairia**, não sobre a aparência do código.

---

## Testes

```bash
php artisan test                      # suíte completa
php artisan test --filter=Nome        # um arquivo
vendor/bin/pint                       # formatação
```

O banco de teste é declarado em `phpunit.xml` (`projeto_base_test`). O **nome diferente** é o que
impede a suíte de apagar o banco de desenvolvimento.

### O que faz um teste valer alguma coisa aqui

- **A asserção tem de ser sobre o caminho que a TELA usa.** Testar Policy, scope ou service
  isolado passa verde com o componente Livewire quebrado. A autorização real atravessa
  `CapacidadeResolver` → Policy → Livewire → Blade.
- **Verde não prova nada sozinho.** A asserção precisa ser sobre o artefato real: o SQL emitido,
  o PHP gerado pelo Blade, o byte que o navegador executou.
- **A cada defeito real, pergunte:** *"que verificação automática teria pego isto?"* — e
  implemente no mesmo commit.

---

## Travas automáticas

As travas **do produto** vivem na suíte de testes e rodam com `php artisan test` — em qualquer
máquina, a partir de um checkout limpo. Exemplos:

| Trava | O que cobra |
|---|---|
| `tests/Feature/RiskManagement/SalvarRiscoPelaTelaTest.php` | Salvar pelo caminho da tela; `exists`/`unique` sem schema |
| `tests/Unit/DependenciasDeRuntimeAceitamPhp82Test.php` | Nenhum pacote de runtime do `composer.lock` exige PHP acima do piso `^8.2` — o `config.platform.php = 8.3.0` (necessário ao Pest 4) não pode puxar dependência que derrube servidor em 8.2 |

**Ferramental do assistente de IA não é versionado.** Hooks, permissões e instruções do
assistente (`.claude/`, `CLAUDE.md`, `.mcp.json`, `boost.json`) ficam na máquina de quem
desenvolve — este repositório é público (ver `.gitignore`). Por isso nenhuma regra deste
documento depende deles: o que é obrigatório para o código está aqui e nos testes.

Se uma trava apontar falso positivo: corrija a **trava**, e diga qual e por quê. Nunca contorne
em silêncio.

---

## Escopo de uma alteração

- Toque os arquivos **diretamente relacionados** ao que foi pedido.
- Precisou de outro arquivo? Informe qual e por quê antes.
- **Infraestrutura global só com pedido explícito:** `routes/web.php`, `bootstrap/app.php`,
  `bootstrap/providers.php`, `config/`, `resources/views/layouts/app.blade.php`,
  `resources/views/navigation-menu.blade.php`, `app/Providers/`, `app/Models/User.php`,
  `app/Services/Authorization/CapacidadeResolver.php`.

### Pedido em massa não suspende a análise linha a linha

"Remova todos os console.log", "renomeie X em todo lugar": cada linha alterada continua exigindo
análise individual. Uma linha só sai se contiver **apenas** o alvo — se ela declara ou atribui um
identificador, confirme por busca que nada mais o usa. Ao final, releia o `git diff` inteiro
procurando remoção que não seja o alvo literal.

> Caso real: um commit "silencia console.logs" apagou a declaração de `logoutUrl` em
> `session-timer.js` junto com os logs vizinhos, destruindo o logout por término de sessão.
> Descoberto dias depois, em produção. O arquivo continua aqui, e **não há ESLint configurado**.

### Antes de commitar

1. `git diff --name-only` — todo arquivo listado tem relação direta com o pedido?
2. Releia o código alterado: entrega exatamente o que foi pedido, nem mais nem menos.
3. Mexeu em UI? Confira que não há efeito colateral em outra tela.
4. Mexeu em lógica crítica? Confira se há teste — e diga se não houver.

---

## Convenções de nomenclatura

| Prefixo | Uso |
|---|---|
| `tab_` | Tabela de entidade |
| `rel_` | Tabela de relacionamento |
| `cod_` | Chave (UUID) |
| `dsc_` | Descrição curta / rótulo |
| `nom_` | Nome |
| `txt_` | Texto livre |
| `num_` | Número |
| `dte_` | Data |
| `bln_` | Booleano |
| `jsn_` | JSON |

---

## Documentação

`documentacao/` guarda o inventário técnico, o dicionário de dados, o manual operacional e os
estudos de melhoria (`documentacao/melhorias/`).

**Consulte antes de varrer o projeto inteiro.** Nos arquivos grandes, use `grep` com padrão
específico ou leia por trecho — não leia um arquivo de milhares de linhas por inteiro.

> ⚠️ O dicionário de dados está desatualizado (descreve 56 tabelas; o banco tem 72, e a
> infraestrutura migrou de `public` para `pei`). Para coluna, tipo ou relacionamento, consulte o
> `information_schema` ou a migration que criou a tabela.
