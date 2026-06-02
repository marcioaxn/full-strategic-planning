# Contexto e Motivação

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Problema identificado pelo gestor

- **Indicadores:** o usuário não sabe onde lançar a evolução do indicador. A rota `/indicadores/{id}/evolucao` existe, mas não há chamada de ação visível na listagem ou no detalhe do indicador.
- **Entregas/Atividades:** a vinculação Plano → Entrega não é navegável de forma intuitiva; o usuário precisa encontrar a rota `/planos/{id}/entregas` manualmente.
- **Desconexão entre módulos:** o sistema foi construído em silos. Não há fio condutor visual que guie o usuário pela sequência metodológica Inaugurar → Planejar → Monitorar.
- **Falta de norte metodológico:** os módulos existem mas não expressam a sequência do GPPEI. Um gestor novo não sabe por onde começar.
- **Perfis mal definidos:** as Policies existem mas não há uma tela que torne os papéis (Administrador Geral, Gestor com Edição, Gestor sem Edição) compreensíveis para o usuário.

## O Guia GPPEI como referência

O GPPEI (156 páginas, PNUD/MGI, maio/2025) é o documento validado pelos pares de governo e organismos internacionais que define a metodologia oficial de Planejamento Estratégico Institucional para a Administração Pública Federal brasileira.
