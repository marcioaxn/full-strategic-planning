# Personas

> **Artefato extraído** do Documento Mestre de Evolução do Sistema PEI (`documentacao/documento-mestre-evolucao-sistema-pei.md`, v1.0 · 2026-05-30).

## Persona 1 — Administrador Geral (Gestor de TI/Sistemas)

**Nome:** Carlos Alberto, 45 anos, Assessor de Tecnologia  
**Contexto:** Responsável pela implantação e manutenção do sistema na organização. Cria usuários, define perfis, configura o PEI inicial, acessa todos os módulos.  
**Objetivos:**
- Configurar a organização e o ciclo PEI sem depender de suporte técnico externo
- Assumir temporariamente o perfil de qualquer usuário para diagnóstico de incidentes
- Garantir que os dados estejam corretos e os relatórios geráveis

**Frustrações:**
- Não há tela clara mostrando o que cada perfil pode ou não fazer
- Não é possível simular a visão de outro usuário logado
- Configurações de sistema espalhadas em rotas pouco documentadas

**Necessidades específicas:**
- Tela de gestão de perfis com tabela de permissões por funcionalidade
- Opção de "assumir identidade" (impersonate) de um usuário cadastrado
- Log de ações administrativas

---

## Persona 2 — Gestor Estratégico (Gestor com Edição)

**Nome:** Fernanda Oliveira, 38 anos, Diretora de Planejamento  
**Contexto:** Coordena o ciclo PEI da organização. Ela é quem preenche Missão/Visão, cria objetivos, define indicadores e acompanha o progresso.  
**Objetivos:**
- Seguir o roteiro metodológico do GPPEI sem precisar consultar o PDF externamente
- Lançar a evolução de indicadores mês a mês de forma rápida
- Gerar relatórios executivos para a alta direção
- Saber em que fase do PEI está e o que falta concluir

**Frustrações:**
- Não encontra onde lançar a evolução do indicador
- Não sabe se o plano de ação está vinculado corretamente ao objetivo
- O sistema não mostra um "checklist" do que está completo ou pendente

**Necessidades específicas:**
- Barra de progresso do ciclo PEI (qual fase está concluída)
- Botão "Lançar Evolução" visível na listagem de indicadores
- Links para o PDF do GPPEI em cada módulo
- Checklist visual do ciclo PEI

---

## Persona 3 — Gestor Operacional (Gestor sem Edição)

**Nome:** Roberto Silva, 32 anos, Analista de Planejamento  
**Contexto:** Responsável por atividades pontuais: lançar atualizações de entregas, visualizar planos e indicadores do seu setor, sem alterar dados estratégicos.  
**Objetivos:**
- Atualizar o status das entregas do seu setor
- Visualizar o progresso dos indicadores relacionados à sua área
- Consultar o plano de ação e entender as prioridades

**Frustrações:**
- Às vezes vê botões de edição que não deveria ter acesso (confusão de permissão)
- Não sabe o que está sob sua responsabilidade
- Precisa navegar por múltiplas telas para ver o resumo do que lhe foi atribuído

**Necessidades específicas:**
- Painel pessoal com "minhas entregas" e "meus indicadores"
- Interface simplificada sem botões de ação que estejam bloqueados por policy
- Notificações de prazos vencidos ou próximos do vencimento

---

## Persona 4 — Alta Direção (Apenas Leitura)

**Nome:** Dra. Márcia Costa, 55 anos, Secretária Executiva  
**Contexto:** Acessa o sistema para visualizar o Mapa Estratégico, o progresso geral do PEI e relatórios executivos. Não cadastra nada.  
**Objetivos:**
- Ver rapidamente se os objetivos estratégicos estão no rumo
- Exportar relatório executivo em PDF para reunião
- Entender o status geral sem precisar navegar por múltiplas telas

**Necessidades específicas:**
- Dashboard executivo como página inicial
- Relatório executivo acessível em 1 clique
- Mapa estratégico com indicadores de desempenho visíveis
