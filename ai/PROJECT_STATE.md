# Estado Atual do Projeto: SEAE (Sistema de Apoio à Estratégia)

## 📌 Contexto Geral
O projeto é um sistema de Planejamento Estratégico Institucional (PEI) utilizando **Laravel 11, Livewire 3, AlpineJS e PostgreSQL**. O sistema opera com múltiplos schemas, mas as diretrizes de desenvolvimento proíbem a declaração manual de schemas (`public.` ou `pei.`) no código, confiando no `search_path` definido no `config/database.php`.

## ✅ Funcionalidades Vencidas (Destaques)
1.  **Motor de Cálculo Temporal:** Inteligência nos Models `Indicador` e `Objetivo` que diferencia cálculos YTD (até o mês atual) para o ano vigente e cálculos Full Year para anos passados.
2.  **Sincronização Inteligente (UX):** Seletores de PEI e Ano no Navbar com inteligência bidirecional (mudar o ano troca o PEI automaticamente e vice-versa).
3.  **Arquitetura de Gráficos Ultra-Estável:** Dashboard utiliza AlpineJS com `@entangle` e `wire:ignore` para garantir que os gráficos não "pisquem" ou desapareçam durante o `wire:poll`.
4.  **Relatório Executivo Premium:** Relatório em PDF (DomPDF) com gráficos de colunas em CSS puro, tabelas detalhadas de riscos e legendas dinâmicas baseadas no banco de dados.
5.  **Padronização de Satisfação:** Lógica de cores para Planos de Ação centralizada no Model (`#475569`, `#429B22`, `#F3C72B`).

## 🛠 Boas Práticas Estabelecidas
*   **DRY (Don't Repeat Yourself):** Legendas e cores são definidas nos Models e consumidas via Partials.
*   **SPA Feeling:** Uso obrigatório de `wire:navigate` em todos os links de navegação.
*   **Segurança Git:** Commits granulares e branches específicas para funcionalidades.
*   **Zero Inferência:** O Agente deve sempre perguntar antes de assumir decisões de design ou arquitetura.

## 🚀 Próximos Passos
- Melhorias contínuas na UX do Dashboard.
- [Inserir próxima tarefa aqui]

---
*Última atualização: 28 de Dezembro de 2025*
