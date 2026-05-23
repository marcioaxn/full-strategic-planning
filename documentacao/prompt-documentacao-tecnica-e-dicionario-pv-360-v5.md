# Prompt para documentacao tecnica e dicionario de dados - pv-360-v5

```text
Você está atuando no projeto localizado em:

E:\xampp\htdocs\pv-360-v5

Objetivo:
Construir dois artefatos técnicos completos, em Markdown, para subsidiar avaliação técnica, governança, manutenção e upgrade da Plataforma Visão 360:

1. Documentação técnica completa do projeto.
2. Dicionário de Dados do banco de dados real.

Regras de qualidade e confiança:
- Não invente nada.
- Não use arquivos de agente, instruções operacionais ou prompts como fonte factual de produto, domínio ou arquitetura.
- Use como fonte primária o código real, configurações reais, rotas reais, runtime real e banco de dados real.
- Separe claramente:
  - Verificado no código.
  - Verificado no banco.
  - Verificado em runtime.
  - Inferido tecnicamente.
  - Não confirmado.
- Se houver divergência entre README, código, migrations e banco real, registre a divergência explicitamente.
- Não execute comandos destrutivos.
- Não rode `migrate:fresh`, `RefreshDatabase`, seeds, DDL, limpeza de banco, rollback agressivo, `git reset --hard` ou `git clean`.
- Consultas ao banco devem ser somente leitura.
- Preserve qualquer alteração preexistente no worktree.
- Não altere código funcional.
- Crie somente documentação e registros de intervenção.

Antes de começar:
1. Rode `git status --short --branch` e registre mentalmente o estado inicial.
2. Crie uma branch contextual para documentação, se possível.
3. Crie roadmaps em `documentacao` antes dos artefatos:
   - `documentacao/roadmap-documentacao-tecnica-completa.md`
   - `documentacao/roadmap-dicionario-dados-postgresql.md`
4. Se existir protocolo local de intervenções, registre em `gemini/interventions.txt`. Se não existir, crie o diretório/arquivo.
5. Faça backup preventivo dos arquivos documentais antes de sobrescrever, se já existirem.

Artefato 1:
Criar:

documentacao/documentacao-tecnica-plataforma-visao-360.md

Esse documento deve conter, no mínimo:

- Título e finalidade.
- Critérios de confiança.
- Fontes usadas.
- Identidade correta do projeto baseada no código e README apenas quando coerente.
- Sumário executivo técnico.
- Stack real:
  - linguagem;
  - framework;
  - versões reais por runtime;
  - dependências PHP;
  - dependências JS/CSS;
  - drivers;
  - cache;
  - queue;
  - session;
  - mail;
  - storage.
- Estrutura do projeto.
- Arquitetura geral.
- Configurações relevantes:
  - `bootstrap/app.php`;
  - providers;
  - middleware;
  - exceptions;
  - auth;
  - database;
  - cache;
  - queue;
  - filesystems;
  - livewire/jetstream/fortify/sanctum, se existirem.
- Rotas reais:
  - método;
  - URI;
  - nome;
  - action/controller/componente;
  - middleware;
  - entrada esperada;
  - saída esperada.
- Módulos funcionais identificados no código.
- Leitura semântica dos módulos críticos:
  - leia corpo dos principais controllers;
  - leia corpo dos principais componentes Livewire/Inertia/Vue/React, conforme stack real;
  - leia services importantes;
  - leia policies/gates;
  - leia middlewares;
  - leia observers/listeners/jobs/commands.
- Models:
  - namespace;
  - tabela;
  - primary key;
  - tipo de chave;
  - incrementing;
  - fillable/casts quando relevante;
  - relações;
  - scopes;
  - métodos de domínio.
- Controllers:
  - métodos;
  - entradas;
  - validações;
  - regras;
  - saídas;
  - views/responses/downloads/redirects.
- Componentes frontend/backend interativos:
  - componentes Livewire, Vue, React, Inertia ou equivalentes;
  - estado;
  - eventos;
  - validações;
  - persistência;
  - feedback ao usuário.
- Services:
  - responsabilidade;
  - entradas;
  - saídas;
  - efeitos colaterais.
- Policies/Gates:
  - matriz real de autorização inferida do código.
- Middlewares:
  - responsabilidade;
  - efeito sobre request/response.
- Exceptions e tratamento de falhas.
- Auditoria:
  - pacote usado;
  - tabelas;
  - eventos auditados;
  - models auditáveis;
  - telas de consulta.
- Jobs, commands, observers, listeners, mailables, exports/imports.
- Testes existentes e lacunas.
- Frontend:
  - assets;
  - build;
  - layouts;
  - componentes;
  - padrões visuais;
  - riscos UX.
- Integrações externas:
  - IA;
  - APIs;
  - e-mail;
  - storage;
  - PDF;
  - Excel;
  - outros provedores.
- Riscos técnicos para upgrade.
- Inconsistências encontradas.
- Recomendações objetivas para upgrade.

Importante:
A documentação técnica deve ser autônoma e executiva. Não mencione que ela é “reconstruída”, “corrigida”, “substitui documento anterior” ou qualquer histórico interno. Ela deve parecer o documento oficial de referência técnica.

Artefato 2:
Criar:

documentacao/dicionario-dados-postgresql-plataforma-visao-360.md

Se o banco não for PostgreSQL, adapte o título e as consultas ao banco real, mas mantenha o mesmo nível de detalhe.

O Dicionário de Dados deve conter, no mínimo:

- Título e finalidade.
- Critérios de confiança.
- Fonte primária: catálogo real do banco em modo somente leitura.
- Sumário do banco:
  - schemas/databases;
  - quantidade de tabelas;
  - domínio predominante por schema;
  - migrations aplicadas versus arquivos locais;
  - divergências.
- Mapa funcional das tabelas:
  - tabela;
  - módulo/domínio;
  - contagem atual de linhas;
  - model relacionado;
  - finalidade.
- Relacionamentos por FK:
  - origem;
  - coluna;
  - destino;
  - constraint;
  - ON UPDATE;
  - ON DELETE.
- Tabelas pivot/associativas identificadas.
- Dicionário detalhado por tabela:
  - schema;
  - tabela;
  - finalidade;
  - contagem de linhas;
  - model relacionado;
  - colunas;
  - ordem;
  - tipo físico;
  - nullable;
  - default;
  - PK;
  - FK;
  - unique;
  - índices;
  - constraints.
- Cruzamento Models x Banco:
  - model;
  - tabela declarada;
  - PK declarada;
  - existência real no banco;
  - observações.
- Observações de upgrade e governança de dados:
  - campos legados;
  - dependência de search_path/schema;
  - tabelas críticas;
  - auditoria/histórico;
  - tabelas de segurança/acesso;
  - riscos de DDL.

Para PostgreSQL:
Use preferencialmente `pg_catalog` para FKs, porque `information_schema.constraint_column_usage` pode falhar ou ficar incompleto em relações entre schemas.

Consultas recomendadas:
- `information_schema.columns`
- `pg_constraint`
- `pg_class`
- `pg_namespace`
- `pg_attribute`
- `pg_indexes`
- tabela `migrations`
- contagem real por tabela com `count(*)`, somente leitura

Validação obrigatória antes do commit:
- Verifique cabeçalho dos dois documentos.
- Verifique se não há referência indevida a outro projeto.
- Verifique se as FKs têm destino real, não `..`.
- Verifique se as seções principais existem.
- Verifique número de linhas dos documentos.
- Rode buscas por termos suspeitos herdados de outro contexto.
- Verifique `git diff --cached --name-status` antes de commitar.
- Commitar somente arquivos documentais e log de intervenção.
- Não incluir alterações preexistentes do worktree.

Commits esperados:
1. Commit da documentação técnica completa.
2. Commit do dicionário de dados.

Mensagens sugeridas:
- `docs: adiciona documentacao tecnica completa`
- `docs: adiciona dicionario de dados do banco`

Após commit:
- Faça push para branch remota se houver remoto configurado e se o ambiente permitir.
- Informe:
  - arquivos criados;
  - commits;
  - branch;
  - se push foi feito;
  - limitações ou pontos não confirmados.
```
