# Ralph — Correções UX Críticas (Fase 1 do Roadmap)

## Contexto
Sistema de Planejamento Estratégico PEI. Problemas críticos de UX identificados no
documento mestre: documentacao/documento-mestre-evolucao-sistema-pei.md (seção 5).

## Leia antes de iniciar
- CLAUDE.md
- documentacao/documento-mestre-evolucao-sistema-pei.md

## Problemas a corrigir (em ordem de prioridade)

### P1 — Botão "Lançar Evolução" invisível nos indicadores
- Arquivo PHP: `app/Livewire/PerformanceIndicators/ListarIndicadores.php`
- View Blade: `resources/views/livewire/performance-indicators/listar-indicadores.blade.php`
- Ação: adicionar botão/ícone "Lançar Evolução" em cada linha da listagem de indicadores
- Rota existente: `route('indicadores.evolucao', $indicador->cod_indicador)`
- O modal de lançamento já pode existir no componente LancarEvolucao — verifique primeiro

### P2 — Link "Ver Entregas" pouco visível nos planos
- Arquivo PHP: `app/Livewire/ActionPlan/ListarPlanos.php`
- View Blade: `resources/views/livewire/plano-acao/listar-planos.blade.php`
- Ação: adicionar botão "Ver Entregas" visível no card e detalhe de cada plano
- Rota existente: `route('planos.entregas', $plano->cod_plano_de_acao)`

### P3 — Página inicial sem portal de módulos
- Verificar: `app/Livewire/Dashboard/Index.php` e view correspondente
- Ação: criar seção de portal mostrando os 3 módulos GPPEI com status de progresso
- Usar `PeiGuidanceService` para obter o status atual do ciclo

## Processo por iteração
1. Identifique o próximo problema não resolvido
2. Leia o arquivo Livewire e a view correspondente
3. Implemente a correção mínima e cirúrgica (não refatorar)
4. `php -l {arquivo}` + `vendor/bin/pint {arquivo} --quiet`
5. Commit parcial com mensagem descritiva

## Parada
Todos os 3 problemas corrigidos.
Output: `<promise>COMPLETE</promise>`
