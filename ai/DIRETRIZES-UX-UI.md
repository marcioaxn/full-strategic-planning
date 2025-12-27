# Diretrizes de UX/UI - Sistema SEAE

## Filosofia Central

> **"O sistema não é para nós desenvolvedores, mas para usuários que são experts no assunto OU para aqueles que nem sabem do que se trata e estão aprendendo."**

---

## Princípios Fundamentais

### 1. Contextualização Completa
- **Sempre fornecer contexto completo ao usuário**
- Quando exibir dados de um nível hierárquico (ex: Indicadores), mostrar também os níveis superiores (Objetivo, Perspectiva, PEI)
- O usuário nunca deve se perguntar "de onde veio isso?" ou "a que isso se refere?"
- Incluir metadados relevantes que ajudem na compreensão

### 2. Facilitação da Gestão
- **Incluir elementos visuais que facilitem a tomada de decisão**
- Gráficos de progresso, tendências e comparativos
- Indicadores visuais (cores, ícones, badges)
- Dashboards com visão consolidada
- KPIs destacados visualmente

### 3. Clareza Acima de Tudo
- **Explicar em palavras, gráficos ou qualquer meio que facilite**
- Usar linguagem clara e acessível
- Evitar jargões técnicos sem explicação
- Tooltips e ajudas contextuais quando necessário
- Legendas explicativas em gráficos e tabelas

### 4. Hierarquia Visual
- **Mostrar a cadeia de relacionamentos**
- PEI → Perspectiva → Objetivo → Plano de Ação → Indicador
- Breadcrumbs contextuais
- Cards com informações hierárquicas
- Navegação que permita subir/descer na hierarquia

---

## Checklist de Implementação

### Para toda tela de detalhamento:

- [ ] **Cabeçalho Contextual**
  - Nome do PEI ativo
  - Perspectiva relacionada (cor, nome, nível)
  - Objetivo estratégico completo
  - Responsáveis quando aplicável

- [ ] **Metadados do Objetivo**
  - Descrição completa
  - Data de criação/atualização
  - Status atual
  - Percentual de conclusão

- [ ] **Resumo Visual**
  - Cards com totalizadores
  - Gráficos de progresso
  - Indicadores de tendência (↑ ↓ →)
  - Semáforo de status (cores)

- [ ] **Navegação Contextual**
  - Link para voltar ao mapa estratégico
  - Links para itens relacionados
  - Breadcrumb completo

- [ ] **Ações Disponíveis**
  - Botões de ação claros
  - Opções de exportação
  - Filtros relevantes

---

## Exemplos de Aplicação

### Página de Indicadores (filtrada por objetivo)

**Deve conter:**
1. **Header com contexto:**
   - Badge: "Perspectiva: [Nome] (Nível X)"
   - Título: "Indicadores do Objetivo: [Nome do Objetivo]"
   - Descrição do objetivo

2. **Cards de resumo:**
   - Total de indicadores
   - % média de atingimento
   - Indicadores em alerta (vermelho/amarelo)
   - Tendência geral

3. **Gráfico:**
   - Evolução mensal dos indicadores
   - Comparativo meta vs realizado

4. **Lista detalhada:**
   - Cada indicador com suas métricas
   - Farol de status
   - Última atualização

### Página de Planos de Ação (filtrada por objetivo)

**Deve conter:**
1. **Header com contexto:**
   - Badge: "Perspectiva: [Nome] (Nível X)"
   - Título: "Planos de Ação do Objetivo: [Nome do Objetivo]"
   - Descrição do objetivo

2. **Cards de resumo:**
   - Total de planos
   - Planos concluídos
   - Planos em andamento
   - Planos atrasados
   - Orçamento total previsto vs executado

3. **Gráfico:**
   - Status dos planos (pizza ou barras)
   - Timeline de execução

4. **Lista detalhada:**
   - Cada plano com status, datas, responsável
   - Progresso visual (barra)
   - Alertas de prazo

---

## Lembrete para Desenvolvedores

> **"Para nós que trabalhamos com o desenvolvimento é fácil imaginar ou lembrar dos relacionamentos, mas é preciso ter a ciência que o usuário não é obrigado a lembrar desses detalhes. O máximo que pudermos deixar claro para o usuário é o mínimo que podemos fazer."**

- Sempre se coloque no lugar de um usuário que nunca viu o sistema
- Teste cada tela perguntando: "Um usuário novo entenderia o contexto?"
- Adicione informações de contexto mesmo que pareçam "óbvias"
- Prefira redundância de informação a falta de clareza

---

## Histórico de Atualizações

| Data | Descrição |
|------|-----------|
| 2025-12-27 | Criação do documento com diretrizes iniciais |

---

*Documento criado como parte das diretrizes do projeto SEAE - Sistema de Planejamento Estratégico*
