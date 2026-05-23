# Roadmap - Dicionario de Dados PostgreSQL

## Contexto

Foi autorizada a construcao de um Dicionario de Dados do banco PostgreSQL real do Sistema de Planejamento Estrategico. O documento deve apoiar upgrade, manutencao, auditoria tecnica e entendimento das relacoes entre dominios.

## Diagnostico

- A aplicacao usa PostgreSQL com schemas de dominio e infraestrutura.
- A fonte primaria do dicionario sera o catalogo real do banco, consultado em modo somente leitura.
- Models Eloquent e migrations serao usados apenas como apoio para mapear dominio e identificar divergencias.
- Nao sera executado DDL, migration, seed, limpeza de banco, `migrate:fresh` ou qualquer comando destrutivo.

## Plano de execucao

1. Consultar `information_schema.columns`, `information_schema.table_constraints`, `information_schema.key_column_usage`, `information_schema.constraint_column_usage`, `pg_indexes` e tabela `migrations`.
2. Inventariar models Eloquent e suas tabelas declaradas.
3. Classificar tabelas por modulo funcional: infraestrutura, organizacao, PEI/BSC, planos/entregas, indicadores, riscos, relatorios e auditoria.
4. Gerar Markdown com schemas, tabelas, colunas, tipos, nulidade, defaults, PKs, FKs, uniques, indices, relacionamentos e observacoes.
5. Registrar divergencias e riscos tecnicos para upgrade.
6. Registrar intervencao em `gemini/interventions.txt`.
7. Commitar e enviar a documentacao para a branch remota.

## Rollback

- Remover `documentacao/dicionario-dados-postgresql-planejamento-estrategico.md`.
- Remover `documentacao/roadmap-dicionario-dados-postgresql.md`.
- Remover a linha correspondente em `gemini/interventions.txt`.
- Reverter o commit documental, sem tocar nas alteracoes preexistentes do worktree.
