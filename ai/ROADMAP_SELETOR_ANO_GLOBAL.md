# Roadmap: Implementação de Seletor de Ano Global e Motor de Cálculo Temporal

## Objetivo
Permitir que o usuário selecione um ano de referência no Navbar que persista em todo o sistema e controle os cálculos de desempenho (YTD vs Ano Completo).

## Status das Atividades

### 1. Componente de Interface (Navbar)
- [x] Criar Componente Livewire `Shared\SeletorAno` - ✅
- [x] Implementar lógica para buscar range de anos (Min/Max de todos os PEIs) - ✅
- [x] Inserir seletor no `navigation-menu.blade.php` ao lado do seletor de PEI - ✅

### 2. Persistência e Estado Global
- [x] Salvar `ano_selecionado` na Sessão - ✅
- [x] Garantir que o ano vigente seja o padrão inicial - ✅
- [x] Implementar inteligência bidirecional entre PEI e Ano - ✅

### 3. Refatoração do Motor de Cálculo (Backend)
- [x] Ajustar `Indicador::calcularAtingimento($ano, $mes)` - ✅
- [x] Ajustar `Objetivo::calcularAtingimentoConsolidado($ano, $mes)` - ✅
- [x] Ajustar `Objetivo::getResumoDesempenho($ano)` - ✅

### 4. Integração e Validação
- [x] Atualizar Dashboard (Listeners e Otimização de Gráficos) - ✅
- [x] Atualizar Mapa Estratégico (Suporte ao Ano Selecionado) - ✅
- [x] Atualizar Relatório Executivo (Sincronização Temporal) - ✅

---
**STATUS FINAL: 100% CONCLUÍDO**