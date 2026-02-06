# 📊 Cálculo Automático de Indicadores por Entregas Ponderadas

**Data de Implementação:** 06/02/2026  
**Versão:** 1.0  
**Módulo:** Performance Indicators + Action Plan

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Conceitos Fundamentais](#conceitos-fundamentais)
3. [Fórmula de Cálculo](#fórmula-de-cálculo)
4. [Arquitetura Técnica](#arquitetura-técnica)
5. [Guia de Uso](#guia-de-uso)
6. [Validação de Pesos](#validação-de-pesos)
7. [Fluxo de Atualização Automática](#fluxo-de-atualização-automática)
8. [Exemplos Práticos](#exemplos-práticos)
9. [FAQ](#faq)

---

## 1. Visão Geral

Esta funcionalidade permite que indicadores de desempenho sejam calculados **automaticamente** com base no progresso ponderado das entregas de um Plano de Ação vinculado, dispensando o lançamento manual de evoluções.

### Problema Resolvido

| Antes (Manual) | Depois (Automático) |
|----------------|---------------------|
| Gestor precisava lançar evoluções mensalmente | Sistema calcula automaticamente |
| Risco de esquecimento ou inconsistência | Dados sempre atualizados em tempo real |
| Todas as entregas tinham peso igual | Entregas críticas podem ter peso maior |
| Desconexão entre plano e indicador | Indicador reflete exatamente o plano |

---

## 2. Conceitos Fundamentais

### 2.1 Tipos de Cálculo

O sistema agora suporta dois modos de medição para indicadores:

| Tipo | Código | Descrição |
|------|--------|-----------|
| **Medição Manual** | `manual` | Modo tradicional. O gestor lança valores realizados mensalmente na tela "Lançar Evolução". |
| **Baseado em Plano de Ação** | `action_plan` | Modo automático. O progresso é calculado pela fórmula ponderada das entregas do plano vinculado. |

### 2.2 Peso da Entrega

Cada entrega agora possui um campo **Peso** (0 a 100) que representa sua importância relativa no plano.

- **Exemplo:** Um projeto com 5 entregas pode ter:
  - Entrega A: Peso 40 (mais importante)
  - Entrega B: Peso 25
  - Entrega C: Peso 15
  - Entrega D: Peso 10
  - Entrega E: Peso 10
  - **Total: 100%**

### 2.3 Mapeamento de Status para Percentual

Cada status de entrega é convertido para um percentual de conclusão:

| Status | Percentual | Ícone | Descrição |
|--------|------------|-------|-----------|
| Concluído | **100%** | ✅ | Entrega finalizada |
| Em Andamento | **50%** | 🔄 | Trabalho em progresso |
| Suspenso | **25%** | ⏸️ | Temporariamente pausado |
| Não Iniciado | **0%** | ⬜ | Ainda não começou |
| Cancelado | **Excluído** | ❌ | Não entra no cálculo |

---

## 3. Fórmula de Cálculo

### Fórmula Principal

```
                    Σ (Peso_i × Status_Percentual_i)
Progresso (%) = ────────────────────────────────────── × 100
                         Σ Peso_i
```

### Exemplo de Cálculo

Considere um plano com 3 entregas:

| Entrega | Peso | Status | Status (%) | Contribuição |
|---------|------|--------|------------|--------------|
| Relatório Final | 50 | Concluído | 100% | 50 × 1.0 = **50** |
| Treinamento | 30 | Em Andamento | 50% | 30 × 0.5 = **15** |
| Documentação | 20 | Não Iniciado | 0% | 20 × 0.0 = **0** |
| **TOTAL** | **100** | - | - | **65** |

**Progresso do Indicador = 65 / 100 × 100 = 65%**

---

## 4. Arquitetura Técnica

### 4.1 Diagrama de Componentes

```
┌──────────────────────────────────────────────────────────────────┐
│                        CAMADA DE APRESENTAÇÃO                     │
├──────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────┐    ┌─────────────────────────────────┐  │
│  │ ListarIndicadores   │    │ GerenciarEntregas               │  │
│  │ (Livewire)          │    │ (Livewire)                      │  │
│  │                     │    │                                 │  │
│  │ • Seletor de tipo   │    │ • Coluna de peso                │  │
│  │ • Escolha de plano  │    │ • Input de peso no modal        │  │
│  │                     │    │ • Validação de 100%             │  │
│  └─────────────────────┘    │ • Barra ponderada               │  │
│                             └─────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────┐
│                        CAMADA DE NEGÓCIO                          │
├──────────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                  IndicadorCalculoService                     │ │
│  │  app/Services/IndicadorCalculoService.php                   │ │
│  ├─────────────────────────────────────────────────────────────┤ │
│  │  • calcularProgressoPlano($plano)                           │ │
│  │  • calcularProgressoEntregaComFilhos($entrega)              │ │
│  │  • atualizarIndicadorAutomatico($indicador)                 │ │
│  │  • atualizarIndicadoresDoPlano($plano)                      │ │
│  │  • validarPesosPlano($plano)                                │ │
│  │  • redistribuirPesosIguais($plano)                          │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                              │                                    │
│                              ▼                                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                    EntregaObserver                           │ │
│  │  app/Observers/EntregaObserver.php                          │ │
│  ├─────────────────────────────────────────────────────────────┤ │
│  │  Eventos monitorados:                                        │ │
│  │  • created()  → Recalcula indicadores do plano               │ │
│  │  • updated()  → Recalcula se status/peso/pai mudou           │ │
│  │  • deleted()  → Recalcula indicadores do plano               │ │
│  │  • restored() → Recalcula indicadores do plano               │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────┐
│                        CAMADA DE DADOS                            │
├──────────────────────────────────────────────────────────────────┤
│  ┌───────────────────────┐    ┌────────────────────────────────┐ │
│  │ Indicador (Model)     │    │ Entrega (Model)                │ │
│  │                       │    │                                │ │
│  │ + dsc_calculation_type│    │ + num_peso                     │ │
│  │ + calcularAtingimento │    │ + bln_status                   │ │
│  │ + planoDeAcao()       │    │ + planoDeAcao()                │ │
│  └───────────────────────┘    └────────────────────────────────┘ │
│                                                                   │
│  ┌───────────────────────────────────────────────────────────┐   │
│  │                    PostgreSQL                              │   │
│  │  performance_indicators.tab_indicador                      │   │
│  │    └── dsc_calculation_type VARCHAR(20)                    │   │
│  │  action_plan.tab_entregas                                  │   │
│  │    └── num_peso DECIMAL(8,2)                               │   │
│  └───────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────┘
```

### 4.2 Arquivos Modificados/Criados

| Arquivo | Tipo | Alteração |
|---------|------|-----------|
| `database/migrations/PerformanceIndicators/2026_02_06_160000_add_calculation_type_to_tab_indicador.php` | 🆕 Novo | Migration para `dsc_calculation_type` |
| `database/migrations/ActionPlan/2026_02_06_160001_add_weight_to_tab_entregas.php` | 🆕 Novo | Migration para `num_peso` |
| `app/Services/IndicadorCalculoService.php` | 🆕 Novo | Service com lógica de cálculo |
| `app/Observers/EntregaObserver.php` | 🆕 Novo | Observer para recálculo automático |
| `app/Models/PerformanceIndicators/Indicador.php` | ✏️ Modificado | Adicionado `CALCULATION_TYPES` e lógica no `calcularAtingimento()` |
| `app/Models/ActionPlan/Entrega.php` | ✏️ Modificado | Adicionado `num_peso` ao `$fillable` e `$casts` |
| `app/Providers/AppServiceProvider.php` | ✏️ Modificado | Registro do `EntregaObserver` |
| `app/Livewire/PerformanceIndicators/ListarIndicadores.php` | ✏️ Modificado | Adicionado campo `dsc_calculation_type` ao form |
| `app/Livewire/ActionPlan/GerenciarEntregas.php` | ✏️ Modificado | Adicionado `num_peso`, validação e redistribuição |
| `resources/views/livewire/indicador/listar-indicadores.blade.php` | ✏️ Modificado | UI de seleção de tipo de cálculo |
| `resources/views/livewire/plano-acao/gerenciar-entregas.blade.php` | ✏️ Modificado | Coluna peso, barras, validação |

---

## 5. Guia de Uso

### 5.1 Criando um Indicador com Cálculo Automático

1. Acesse **Indicadores de Desempenho** no menu lateral
2. Clique em **"+ Novo Indicador"**
3. Na seção **Vínculo Estratégico**:
   - Selecione **"Plano"** como origem
   - Escolha o Plano de Ação desejado
4. Em **Método de Cálculo**:
   - Selecione **"Automático"** (ícone de raio)
5. Preencha os demais campos normalmente
6. Clique em **"Salvar"**

> 💡 **Dica:** Quando o modo automático está ativado, não é necessário lançar evoluções manualmente. O sistema calcula o progresso baseado nas entregas.

### 5.2 Definindo Pesos nas Entregas

1. Acesse o **Plano de Ação** vinculado
2. Clique em **"Gerenciar Entregas"**
3. Para cada entrega, clique no ícone de **edição** (lápis)
4. No modal, preencha o campo **"Peso (%)"**
5. Clique em **"Salvar"**

> ⚠️ **Importante:** A soma dos pesos de todas as entregas deve totalizar **100%**. O sistema exibe um alerta caso a soma esteja diferente.

### 5.3 Usando a Redistribuição Automática

Se preferir distribuir pesos iguais entre todas as entregas:

1. Acesse **Gerenciar Entregas** do plano
2. Observe o alerta de validação de pesos (se houver)
3. Clique no botão **"Redistribuir Pesos Iguais"**
4. O sistema dividirá 100% igualmente entre as entregas

---

## 6. Validação de Pesos

O sistema valida automaticamente a soma dos pesos das entregas:

### Estados de Validação

| Estado | Soma | Exibição | Ação |
|--------|------|----------|------|
| ✅ Válido | 100% | Badge verde com check | Nenhuma ação necessária |
| ⚠️ Inválido | ≠ 100% | Badge amarelo com alerta | Ajustar pesos manualmente ou redistribuir |

### Tolerância

O sistema aceita uma tolerância de **0.01%** para erros de arredondamento. Por exemplo, uma soma de 99.99% ou 100.01% é considerada válida.

### Sem Pesos Definidos

Se nenhuma entrega tiver peso definido (todos = 0), o sistema automaticamente:
- Considera todas as entregas com peso igual
- Calcula o progresso simples (contagem de concluídas)

---

## 7. Fluxo de Atualização Automática

```
┌─────────────────────┐
│  Usuário altera     │
│  status/peso de     │
│  uma entrega        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  EntregaObserver    │
│  detecta a mudança  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Verifica se campo  │──── Não ────┐
│  relevante mudou    │             │
└──────────┬──────────┘             │
           │ Sim                    │
           ▼                        ▼
┌─────────────────────┐    ┌───────────────┐
│  Busca indicadores  │    │  Nada a fazer │
│  do tipo action_plan│    └───────────────┘
│  vinculados ao plano│
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Para cada indicador│
│  chama calcular-    │
│  ProgressoPlano()   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Cria/Atualiza      │
│  EvolucaoIndicador  │
│  do mês/ano atual   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Indicador exibe    │
│  novo progresso!    │
└─────────────────────┘
```

### Campos Monitorados

O Observer só dispara recálculo quando estes campos são alterados:
- `bln_status` (status da entrega)
- `num_peso` (peso da entrega)
- `cod_entrega_pai` (hierarquia)
- `bln_arquivado` (visibilidade)

---

## 8. Exemplos Práticos

### Exemplo 1: Projeto de Modernização de TI

**Plano de Ação:** Modernização da Infraestrutura

| Entrega | Peso | Status Atual | Contribuição |
|---------|------|--------------|--------------|
| Levantamento de requisitos | 10% | ✅ Concluído | 10 × 100% = 10 |
| Aquisição de equipamentos | 20% | ✅ Concluído | 20 × 100% = 20 |
| Instalação de servidores | 30% | 🔄 Em Andamento | 30 × 50% = 15 |
| Migração de dados | 25% | ⬜ Não Iniciado | 25 × 0% = 0 |
| Treinamento de usuários | 15% | ⬜ Não Iniciado | 15 × 0% = 0 |

**Progresso do Indicador = (10 + 20 + 15 + 0 + 0) / 100 = 45%**

---

### Exemplo 2: Impacto de Conclusão de Entrega Crítica

Cenário: A entrega "Instalação de servidores" (peso 30%) foi concluída.

| Antes | Depois |
|-------|--------|
| 30 × 50% = 15 | 30 × 100% = 30 |

**Novo Progresso = (10 + 20 + 30 + 0 + 0) / 100 = 60%**

O indicador **saltou de 45% para 60%** automaticamente!

---

### Exemplo 3: Entregas com Sub-entregas

O sistema suporta hierarquia de entregas. O progresso de uma entrega pai é calculado pelo progresso ponderado de suas sub-entregas.

```
📁 Entrega Pai (Peso: 40%)
   ├── Sub-entrega 1 (Peso: 60%) → ✅ Concluído
   └── Sub-entrega 2 (Peso: 40%) → 🔄 Em Andamento

Progresso da Entrega Pai = (60×100% + 40×50%) / 100 = 80%
Contribuição para o Plano = 40% × 80% = 32%
```

---

## 9. FAQ

### P: Posso mudar de manual para automático depois?
**R:** Sim! Basta editar o indicador e alterar o "Método de Cálculo". As evoluções manuais anteriores serão preservadas, mas o sistema passará a calcular automaticamente a partir de então.

### P: O que acontece com indicadores já existentes?
**R:** Todos os indicadores existentes foram definidos como "manual" automaticamente. O comportamento anterior é preservado.

### P: Preciso definir peso em todas as entregas?
**R:** Não é obrigatório. Se nenhuma entrega tiver peso, o sistema usa peso igual para todas. Porém, para cálculo ponderado, recomendamos definir os pesos.

### P: Como é tratada uma entrega cancelada?
**R:** Entregas canceladas são excluídas do cálculo. Nem seu peso nem seu status contribuem para o progresso.

### P: Com que frequência o indicador é atualizado?
**R:** Em tempo real! A cada alteração de status ou peso de uma entrega, o indicador é recalculado imediatamente.

### P: O sistema funciona com sub-entregas (hierarquia)?
**R:** Sim! O cálculo é recursivo. O progresso de uma entrega pai considera o progresso ponderado de suas sub-entregas.

### P: Posso ver os dois tipos de progresso?
**R:** Sim! Na tela de Gerenciar Entregas, são exibidas duas barras:
- **Progresso Simples:** Baseado na contagem de entregas concluídas
- **Progresso Ponderado:** Baseado na fórmula com pesos

---

## 📞 Suporte

Em caso de dúvidas ou problemas:
- Consulte os logs em `storage/logs/laravel.log`
- O sistema registra cada recálculo com a mensagem: `"Indicador X atualizado automaticamente: Y%"`

---

**Documento criado em:** 06/02/2026  
**Última atualização:** 06/02/2026  
**Autor:** Sistema SEAE - Planejamento Estratégico
