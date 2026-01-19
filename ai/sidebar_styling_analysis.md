## [2026-01-15] Evolução de Relatório: Identidade -> Mapa Estratégico

### Objetivo
Substituir o relatório estático de "Identidade" (Missão/Visão/Valores) por um **Mapa Estratégico Visual (Landscape)**, alinhado com o Dossiê Integrado, mas focado exclusivamente na visualização das perspectivas e objetivos.

### Implementação
1.  **Backend (`ReportGenerationService`)**:
    -   Refatorado `generateIdentidade`. Agora carrega a árvore completa de Perspectivas > Objetivos > Indicadores.
    -   Alterada orientação do PDF para `landscape` (Paisagem) para melhor acomodação das "raias" (swimlanes) do mapa.
2.  **Frontend (`listar-relatorios.blade.php`)**:
    -   Renomeado card para "Mapa Estratégico".
    -   Ícone atualizado para `bi-diagram-3`.
3.  **View (`identidade.blade.php`)**:
    -   Reconstruída do zero.
    -   **Layout**: Tabela de largura total com cabeçalhos de perspectiva verticais na esquerda (coloridos conforme nível hierárquico).
    -   **Conteúdo**: Cards de objetivos distribuídos horizontalmente (4 por linha).
    -   **Identidade**: Missão e Visão integradas discretamente no cabeçalho do documento para contexto.

### Status
- ✅ Relatório transformado em ferramenta visual de gestão.