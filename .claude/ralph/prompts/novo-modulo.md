# Ralph — Novo Módulo PEI

## Objetivo
[DESCREVA O MÓDULO AQUI]

## Referência no GPPEI
[INDICAR PÁGINAS DO PDF: documentacao/pdf/Guia_PEI_VF.pdf, ex: páginas 42-55]

## Referência no documento mestre
[INDICAR SEÇÃO: documentacao/documento-mestre-evolucao-sistema-pei.md, ex: seção 8.7]

## Artefatos a criar (em ordem)
1. Migration: `database/migrations/{dominio}/YYYY_MM_DD_HHMMSS_create_{tabela}_table.php`
2. Model: `app/Models/{Namespace}/{Model}.php`
   - UUID PK com `gen_random_uuid()`, `$incrementing = false`, `$keyType = 'string'`
   - Soft delete com `deleted_at`
   - `$table = 'schema.tabela'` qualificado
3. Livewire component: `app/Livewire/{Namespace}/{Componente}.php`
4. View Blade: `resources/views/livewire/{namespace}/{componente}.blade.php`
   - Bootstrap 5 + Bootstrap Icons
   - Seguir padrão visual das telas existentes
5. Rota em `routes/web.php` (grupo auth:sanctum)
6. Policy (se necessário): `app/Policies/{Modelo}Policy.php`
   - Registrar em AppServiceProvider no array `$policies`

## Restrições
- NUNCA executar `php artisan migrate` — apenas criar o arquivo de migration
- Schema sempre qualificado no `$table` do model
- Usar padrão Bootstrap 5 nas views, sem CSS inline

## Processo por iteração
1. git status + ler CLAUDE.md
2. Criar o próximo artefato da lista acima
3. `php -l {arquivo}` + `vendor/bin/pint {arquivo} --quiet`
4. Commit parcial descritivo
5. Avançar para o próximo artefato

## Parada
Todos os artefatos criados, sem erros de lint ou sintaxe.
Output: `<promise>COMPLETE</promise>`
