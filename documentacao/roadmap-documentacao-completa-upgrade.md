# Roadmap - Documentacao completa para upgrade do projeto

## Contexto

O CEO solicitou uma documentacao ampla e honesta do Sistema de Planejamento Estrategico, com foco em subsidiar um upgrade tecnico. O artefato final deve ficar em `documentacao`, cobrir estrutura, stack, dependencias, backend, frontend, migrations, banco real, models, controllers, componentes Livewire, gates/policies, middlewares, rotas, usuarios, services, exceptions e auditorias.

## Diagnostico inicial

- O projeto esta em Laravel com Livewire, Jetstream/Fortify, PostgreSQL e Vite/Sass.
- A instrucao operacional `AGENTS.md` menciona "Plataforma Visao 360 - Modulo Integra+", mas a leitura do codigo identifica o dominio real como Planejamento Estrategico.
- O worktree ja iniciou com alteracoes preexistentes nao realizadas por esta intervencao, incluindo migrations, seeders, lockfiles, model e arquivos novos. Essas alteracoes devem ser preservadas.
- A documentacao sera baseada apenas no codigo e no schema real acessivel no ambiente local. Pontos nao comprovados serao marcados explicitamente como nao verificados ou nao encontrados.
- Nao serao executados comandos destrutivos, migrations, `migrate:fresh`, `RefreshDatabase`, limpeza de banco ou alteracoes em codigo funcional.

## Plano de execucao

1. Mapear arquivos e estrutura do projeto.
2. Ler dependencias PHP e JavaScript, configuracoes centrais, providers, rotas e middleware.
3. Extrair rotas efetivas com `php artisan route:list` quando possivel.
4. Inventariar migrations, models, controllers, Livewire components, policies, services, observers, commands, mailables, exports e tests.
5. Consultar o banco PostgreSQL em modo somente leitura para comparar tabelas reais, colunas, chaves, indices e constraints com o que existe nas migrations.
6. Produzir um arquivo Markdown extenso em `documentacao` com fatos verificaveis, lacunas e riscos para upgrade.
7. Registrar a intervencao em `gemini/interventions.txt`.
8. Validar os arquivos gerados e preparar commit atomico da documentacao.

## Rollback

- Remover `documentacao/documentacao-completa-projeto-upgrade.md`.
- Remover `documentacao/roadmap-documentacao-completa-upgrade.md`.
- Remover a entrada correspondente em `gemini/interventions.txt` ou remover o arquivo se ele tiver sido criado apenas por esta intervencao.
- Reverter o commit desta documentacao, sem tocar nas alteracoes preexistentes do worktree.
