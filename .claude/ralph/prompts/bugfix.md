# Ralph — Correção de Bug

## Bug
[DESCREVA O BUG ANTES DE RODAR]

## Comportamento esperado
[O QUE DEVERIA ACONTECER]

## Arquivos suspeitos
[LISTE OS ARQUIVOS]

## Contexto do projeto
- Leia CLAUDE.md antes de qualquer ação
- Stack: Laravel 12 + Livewire 3 + PostgreSQL multi-schema

## Processo
1. Execute git status; leia CLAUDE.md
2. Reproduza o bug localizando a causa raiz nos arquivos suspeitos
3. Escreva um teste de regressão que falha (se aplicável)
4. Implemente a correção mínima e cirúrgica
5. Execute `php -l {arquivo}` no arquivo alterado
6. Execute `vendor/bin/pint {arquivo} --quiet`
7. Rode os testes: `php artisan test --filter=NomeTeste`
8. Se verdes: commit fix
9. Output `<promise>COMPLETE</promise>`
