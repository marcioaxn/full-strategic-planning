# Ralph — Implementação de Feature

## Objetivo
[DESCREVA A FEATURE AQUI ANTES DE RODAR]

## Contexto do projeto
- Leia CLAUDE.md antes de qualquer ação
- Stack: Laravel 12 + Livewire 3 + PostgreSQL multi-schema + Bootstrap 5
- Documento mestre: documentacao/documento-mestre-evolucao-sistema-pei.md
- GPPEI: documentacao/pdf/Guia_PEI_VF.pdf

## Processo por iteração
1. Execute git status e git log --oneline -5
2. Leia CLAUDE.md
3. Identifique o próximo item não concluído do checklist abaixo
4. Implemente APENAS esse item
5. Execute `php -l {arquivo}` em cada PHP alterado
6. Execute `vendor/bin/pint {arquivo} --quiet` no arquivo PHP alterado
7. Execute os testes relacionados: `php artisan test --filter=NomeTeste`
8. Se passarem: commit parcial e avance para o próximo item
9. Se falharem: corrija antes de avançar

## Checklist
- [ ] [Item 1]
- [ ] [Item 2]
- [ ] [Item 3]
- [ ] php -l em todos os arquivos PHP alterados: OK
- [ ] vendor/bin/pint nos arquivos alterados: OK

## Parada
Quando todos os itens estiverem concluídos:
output `<promise>COMPLETE</promise>`
