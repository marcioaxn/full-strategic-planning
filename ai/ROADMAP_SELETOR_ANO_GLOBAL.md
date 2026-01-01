# Roadmap: Implementação de Seletor de Ano Global e Motor de Cálculo Temporal

## Objetivo
Permitir que o usuário selecione um ano de referência no Navbar que persista em todo o sistema e controle os cálculos de desempenho (YTD vs Ano Completo).

## Status das Atividades

### 1. Componente de Interface (Navbar)
- [ ] Criar Componente Livewire `Shared\SeletorAno`.
- [ ] Implementar lógica para buscar range de anos (Min/Max de todos os PEIs).
- [ ] Inserir seletor no `navigation-menu.blade.php` ao lado do seletor de PEI.

### 2. Persistência e Estado Global
- [ ] Salvar `ano_selecionado` na Sessão.
- [ ] Garantir que o ano vigente seja o padrão inicial.
- [ ] Implementar listener para atualização em tempo real entre componentes.

### 3. Refatoração do Motor de Cálculo (Backend)
- [ ] Ajustar `Indicador::calcularAtingimento($ano, $mes)`:
    - Se $ano < atual: considerar mês 12 (Ano completo).
    - Se $ano == atual: considerar mês vigente (Acumulado até hoje).
    - Respeitar regra de `bln_acumulado` (Sim/Não).
- [ ] Ajustar `Objetivo::calcularAtingimentoConsolidado($ano, $mes)`.
- [ ] Ajustar `Objetivo::getResumoDesempenho($ano)`.

### 4. Integração e Validação
- [ ] Atualizar Dashboard para usar o ano da sessão.
- [ ] Atualizar Mapa Estratégico para usar o ano da sessão.
- [ ] Atualizar Relatório Executivo para sincronizar com o seletor do Navbar.

---
**Legenda:**
- ⏳ Pendente
- 🏗️ Em Construção
- ✅ Concluído
