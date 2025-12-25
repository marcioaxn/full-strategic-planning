# ARTEFATOS DO SISTEMA DE PLANEJAMENTO ESTRATÉGICO
## Modernização com Laravel 12 + Livewire 3 + Bootstrap 5

**Versão:** 1.0
**Data:** 23 de Dezembro de 2025
**Cliente:** Sistema de Planejamento Estratégico
**Stack:** Laravel 12 + Livewire 3 + Bootstrap 5 + PostgreSQL

---

## 📋 SOBRE ESTE PACOTE

Este pacote contém **todos os artefatos necessários** para implementar a modernização completa do Sistema de Planejamento Estratégico. Os artefatos foram criados com base no texto inicial fornecido, nas migrations do banco de dados legado e nas melhores práticas de desenvolvimento Laravel/Livewire.

### ✅ Diferenças em Relação aos Artefatos Anteriores (v0.app)

**Problemas identificados nos artefatos anteriores:**
1. ❌ Propunham muitas tabelas novas que não existem no banco legado (SWOT, PESTEL, Canvas, Porter, BCG)
2. ❌ Não deixavam claro o que era legado vs. o que era novo
3. ❌ Faltava alinhamento com as migrations existentes
4. ❌ Roadmap não era realista para desenvolvedor solo

**Melhorias neste pacote:**
1. ✅ **100% compatível** com o banco de dados legado
2. ✅ **Separação clara** entre funcionalidades existentes e opcionais
3. ✅ **Foco pragmático** em aproveitar os dados históricos
4. ✅ **Roadmap realista** de 12-14 semanas para desenvolvedor solo
5. ✅ **Artefatos práticos** prontos para implementação direta

---

## 📁 ESTRUTURA DOS ARTEFATOS

```
novos_artefatos/
├── README.md (este arquivo)
├── 01-ANALISE-E-ESCOPO.md
├── 02-REQUISITOS-FUNCIONAIS.md
├── 03-REQUISITOS-NAO-FUNCIONAIS.md
├── 04-MODELOS-ELOQUENT.md
├── 05-COMPONENTES-LIVEWIRE.md
├── 06-ESTRUTURA-PASTAS.md
├── 07-ROADMAP-IMPLEMENTACAO.md
└── 08-MAPA-ESTRATEGICO-ESPECIFICACAO.md
```

---

## 📖 GUIA DE USO DOS ARTEFATOS

### 1️⃣ Comece por: **01-ANALISE-E-ESCOPO.md**

**O que contém:**
- Contexto do projeto e objetivos
- Análise completa do banco de dados legado
- Identificação do que já existe vs. o que é novo
- Decisão de escopo (Core vs. Adicional vs. Fora)
- Estratégia de implementação em fases

**Quando usar:**
- Antes de começar qualquer implementação
- Para entender o que já existe no banco
- Para definir prioridades com o cliente
- Para estimar prazos e recursos

**🎯 Leia primeiro para ter visão completa do projeto!**

---

### 2️⃣ Especificações Funcionais: **02-REQUISITOS-FUNCIONAIS.md**

**O que contém:**
- **RF-01 a RF-13**: 13 módulos funcionais detalhados
- Critérios de aceitação para cada funcionalidade
- Regras de negócio
- Tabelas do banco relacionadas
- Matriz de permissões por perfil de usuário

**Quando usar:**
- Durante planejamento de sprints
- Para criar user stories
- Para validação com cliente
- Para escrever testes de aceitação

**🎯 Use como checklist durante desenvolvimento!**

---

### 3️⃣ Requisitos Técnicos: **03-REQUISITOS-NAO-FUNCIONAIS.md**

**O que contém:**
- Performance (tempos de resposta, otimizações)
- Segurança (OWASP, criptografia, auditoria)
- Usabilidade (UI/UX, responsividade)
- Escalabilidade
- Confiabilidade (uptime, tratamento de erros)
- Padrões de código e conformidade (LGPD, PSR-12)

**Quando usar:**
- Definição da arquitetura
- Code review
- Testes de performance
- Auditorias de segurança
- Deploy em produção

**🎯 Use como checklist de qualidade!**

---

### 4️⃣ Modelos de Dados: **04-MODELOS-ELOQUENT.md**

**O que contém:**
- Código completo de todos os Models
- Relacionamentos (HasMany, BelongsTo, BelongsToMany)
- Scopes úteis
- Métodos auxiliares
- Observers e Events
- Matriz de relacionamentos

**Quando usar:**
- Ao criar os Models no Laravel
- Para entender relacionamentos complexos
- Para implementar lógica de negócio
- Para otimizar queries (Eager Loading)

**🎯 Copie e cole diretamente no seu projeto!**

---

### 5️⃣ Interface Reativa: **05-COMPONENTES-LIVEWIRE.md**

**O que contém:**
- Lista completa de componentes Livewire necessários
- Estrutura de pastas
- Código de exemplo para componentes principais
- Propriedades e métodos de cada componente
- Traits reutilizáveis

**Quando usar:**
- Ao criar componentes Livewire
- Para padronizar nomenclatura
- Para implementar funcionalidades reativas
- Para reutilizar lógica comum

**🎯 Use como template para seus componentes!**

---

### 6️⃣ Organização do Código: **06-ESTRUTURA-PASTAS.md**

**O que contém:**
- Estrutura completa de pastas do projeto
- Padrões de nomenclatura (PHP, Blade, rotas)
- Configurações importantes (.env, composer.json)
- Localização de cada arquivo

**Quando usar:**
- Configuração inicial do projeto
- Para manter organização consistente
- Para encontrar onde criar novos arquivos
- Para onboarding de novos desenvolvedores

**🎯 Siga rigorosamente para manter projeto organizado!**

---

### 7️⃣ Planejamento de Execução: **07-ROADMAP-IMPLEMENTACAO.md**

**O que contém:**
- Cronograma de 7 fases (14 semanas)
- Tarefas detalhadas de cada fase
- Entregas incrementais
- Critérios de aceitação por fase
- Comandos úteis
- Checklists de qualidade

**Quando usar:**
- Planejamento de sprints
- Acompanhamento de progresso
- Priorização de tarefas
- Comunicação com stakeholders

**🎯 Siga passo-a-passo para implementar o sistema!**

---

### 8️⃣ Mapa Estratégico (Componente Principal): **08-MAPA-ESTRATEGICO-ESPECIFICACAO.md**

**O que contém:**
- Especificação técnica completa do componente de Mapa Estratégico
- Código completo do componente Livewire (`MapaEstrategico/ShowDashboard.php`)
- Código completo da Blade view com UI 100% do starter kit
- Lógica de cálculo de desempenho e coloração dinâmica
- Integração com Chart.js (gráficos doughnut e barras horizontais)
- Padrões de UI baseados exclusivamente no starter kit Bootstrap 5 atual
- Sistema de cores baseado em `pei.tab_grau_satisfacao`
- Montagem dinâmica conforme preenchimento do usuário
- Checklist de implementação detalhado

**Quando usar:**
- Ao implementar o componente central do sistema
- Para entender a lógica de cálculo de performance
- Para configurar gráficos Chart.js
- Para aplicar padrões de UI do starter kit
- Para implementar coloração dinâmica por desempenho
- Para entender o conceito de montagem dinâmica

**🎯 Componente mais importante! Copie e implemente primeiro!**

---

## 🚀 COMO COMEÇAR A IMPLEMENTAÇÃO

### Passo 1: Leia a Análise de Escopo
```bash
# Leia primeiro
novos_artefatos/01-ANALISE-E-ESCOPO.md
```

### Passo 2: Configure o Ambiente (Fase 0 do Roadmap)
```bash
# Instale Laravel 12
composer create-project laravel/laravel seae

# Entre no projeto
cd seae

# Instale Jetstream com Livewire
composer require laravel/jetstream
php artisan jetstream:install livewire

# Instale Bootstrap 5
npm install bootstrap @popperjs/core

# Configure .env com banco legado
# (veja 06-ESTRUTURA-PASTAS.md para exemplo)
```

### Passo 3: Crie os Models
```bash
# Use o código de 04-MODELOS-ELOQUENT.md
# Copie os models para app/Models/
```

### Passo 4: Siga o Roadmap
```bash
# Implemente fase por fase conforme 07-ROADMAP-IMPLEMENTACAO.md
# Fase 0: Fundação (Semana 1)
# Fase 1: Core Básico (Semanas 2-3)
# ... e assim por diante
```

---

## 📊 RESUMO DO PROJETO

### Funcionalidades Principais (Escopo CORE)

| Módulo | Descrição | Tabelas Principais |
|--------|-----------|-------------------|
| **1. Organizações** | Hierarquia de unidades | `tab_organizacoes` |
| **2. Usuários** | CRUD e permissões | `users`, `tab_perfil_acesso` |
| **3. PEI** | Ciclos de planejamento | `pei.tab_pei` |
| **4. Identidade** | Missão, Visão, Valores | `pei.tab_missao_visao_valores`, `pei.tab_valores` |
| **5. BSC** | Perspectivas e Objetivos | `pei.tab_perspectiva`, `pei.tab_objetivo_estrategico` |
| **6. Planos de Ação** | Ações, Iniciativas, Projetos | `pei.tab_plano_de_acao`, `pei.tab_entregas` |
| **7. Indicadores** | KPIs com evolução mensal | `pei.tab_indicador`, `pei.tab_evolucao_indicador` |
| **8. Cadeia de Valor** | Processos e atividades | `pei.tab_atividade_cadeia_valor` |
| **9. Dashboards** | Painéis executivos | (calculados) |
| **10. Relatórios** | PDF e Excel | (gerados) |
| **11. Gestão de Riscos** | Identificação, avaliação e mitigação de riscos estratégicos | `pei.tab_risco`, `pei.tab_risco_mitigacao`, `pei.tab_risco_ocorrencia` |
| **12. Auditoria** | Logs de alterações | `tab_audit`, `audits` |

### Tecnologias Utilizadas

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Backend | Laravel | 12.x |
| Frontend | Blade + Livewire | 3.x |
| UI | Bootstrap | 5.x |
| Interatividade | Alpine.js | 3.x |
| Banco de Dados | PostgreSQL | 14+ |
| Cache/Sessão | Redis | 6+ |
| Gráficos | ApexCharts | 3.x |
| PDF | DomPDF | 2.x |
| Excel | Maatwebsite Excel | 3.x |
| Auditoria | Laravel Auditing | 13.x |

### Estimativa de Esforço

- **Prazo Total:** 14-16 semanas (incluindo Gestão de Riscos)
- **Desenvolvedor:** Solo (1 pessoa)
- **Horas/semana:** 40 horas
- **Total de horas:** ~600 horas

---

## ⚠️ DECISÕES IMPORTANTES

### O que ESTÁ no Escopo
✅ Modernizar interface de todas as funcionalidades existentes no banco
✅ Dashboards executivos com gráficos interativos
✅ Relatórios em PDF e Excel
✅ Sistema completo de permissões
✅ Auditoria detalhada de alterações
✅ **Módulo de Gestão de Riscos completo** (identificação, avaliação, mitigação, matriz de riscos, ocorrências)

### O que NÃO está no Escopo (Fase 1)
❌ Análises estratégicas novas (SWOT, PESTEL, Canvas, Porter, BCG)
❌ Sistema de Notificações push
❌ API REST pública
❌ Mobile app

**Nota:** Funcionalidades fora do escopo podem ser implementadas em Fase 2, após validação do Core com o cliente.

---

## 🎯 CRITÉRIOS DE SUCESSO

### Técnicos
- [ ] 100% compatível com banco de dados legado
- [ ] Sem perda de dados históricos
- [ ] Cobertura de testes ≥ 60%
- [ ] Performance adequada (páginas ≤ 2s)
- [ ] Código seguindo PSR-12

### Funcionais
- [ ] Todos os requisitos funcionais implementados
- [ ] Dashboards exibindo dados corretos
- [ ] Relatórios gerados sem erros
- [ ] Permissões funcionando corretamente

### Negócio
- [ ] CEO aprova interface e usabilidade
- [ ] Usuários realizam tarefas sem treinamento extenso
- [ ] Sistema estável em produção por 30 dias

---

## 📞 PRÓXIMOS PASSOS

1. **Revisar artefatos** com o cliente (CEO)
2. **Validar escopo** e prioridades
3. **Configurar ambiente** de desenvolvimento
4. **Iniciar Fase 0** (Fundação)
5. **Reuniões semanais** de acompanhamento
6. **Entregas incrementais** ao final de cada fase

---

## 📝 CONTROLE DE VERSÃO

| Versão | Data | Alterações |
|--------|------|------------|
| 1.0 | 23/12/2025 | Criação inicial dos artefatos |
| 1.1 | 23/12/2025 | Adição do artefato 08-MAPA-ESTRATEGICO-ESPECIFICACAO.md (v1.0 - ApexCharts) |
| 2.0 | 23/12/2025 | **CORREÇÃO IMPORTANTE**: Artefato 08 reescrito (v2.0) - Substituído ApexCharts por Chart.js, UI 100% baseada no starter kit Bootstrap 5 atual (não do projeto antigo), mantida apenas a lógica de montagem dinâmica |
| 3.0 | 24/12/2025 | **ATUALIZAÇÃO DE ESCOPO**: Módulo de Gestão de Riscos movido para escopo obrigatório. Atualizados todos os artefatos: 01 (análise), 02 (RF-14 adicionado), 04 (4 novos models), 05 (7 novos componentes Livewire), 07 (Fase 6 adicionada, prazo total 14-16 semanas), README (escopo e estimativas). Fase 0 marcada como concluída (starter kit pronto). |

---

## ✨ OBSERVAÇÕES FINAIS

Este pacote de artefatos foi criado especificamente para **você (Claude Code)** implementar. Ele contém:

- ✅ **Código real** que pode ser copiado e colado
- ✅ **Decisões arquiteturais** justificadas
- ✅ **Checklists** de qualidade
- ✅ **Roadmap realista** baseado em desenvolvedor solo
- ✅ **100% alinhado** com o banco de dados legado

**Diferencial deste pacote:**
- Não há "alucinações" ou funcionalidades fictícias
- Tudo é baseado nas migrations reais do banco
- Foco pragmático em entregar valor rapidamente
- Separação clara entre obrigatório e opcional

---

**Bom desenvolvimento! 🚀**

*Este documento foi gerado por Claude Code (Anthropic) em 23/12/2025*
