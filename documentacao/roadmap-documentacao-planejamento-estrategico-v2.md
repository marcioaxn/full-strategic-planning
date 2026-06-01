# Roadmap - Documentacao tecnica v2 do Sistema de Planejamento Estrategico

## Contexto

Foi identificada falha grave na documentacao anterior: a identidade "Plataforma Visao 360 - Modulo Integra+" foi incorporada a partir de `AGENTS.md`, arquivo operacional copiado de outro projeto. A documentacao v2 deve reconstruir o artefato com base no dominio real do repositorio: Sistema de Planejamento Estrategico.

## Diagnostico

- `AGENTS.md` nao sera usado como fonte factual de produto, modulo, dominio, regras de negocio ou nomenclatura.
- Fontes factuais permitidas: codigo-fonte, rotas reais, migrations, schema real PostgreSQL, configuracoes Laravel, README quando coerente com codigo e banco.
- O documento anterior pode ser usado apenas como inventario bruto de paths/assinaturas, nunca como fonte conclusiva.
- Toda afirmacao relevante deve ser classificada como: verificada no codigo, verificada no banco, inferida tecnicamente ou nao confirmada.
- Nenhum comando destrutivo, migration, seed, `migrate:fresh`, `RefreshDatabase`, DDL ou limpeza de banco sera executado.

## Plano de execucao

1. Revalidar ambiente, rotas, dependencias e schema real em modo somente leitura.
2. Ler semanticamente os modulos criticos: PEI, objetivos, perspectivas, planos, entregas, indicadores, riscos, relatorios, auditoria, usuarios/perfis e configuracoes.
3. Construir documentacao v2 em arquivo novo, preservando o documento anterior como artefato nao aprovado.
4. Separar claramente fatos verificados, inferencias, lacunas e riscos para upgrade.
5. Registrar intervencao em `gemini/interventions.txt`.
6. Commitar somente os arquivos documentais da v2.

## Rollback

- Remover `documentacao/documentacao-tecnica-planejamento-estrategico-v2.md`.
- Remover `documentacao/roadmap-documentacao-planejamento-estrategico-v2.md`.
- Remover a linha correspondente em `gemini/interventions.txt`.
- Reverter o commit documental da v2, sem tocar em alteracoes preexistentes do worktree.
