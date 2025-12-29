# Seeders do Sistema PEI/BSC

## Visão Geral

Este conjunto de seeders popula o banco de dados com dados realistas simulando uma grande organização com planejamento estratégico completo baseado no Balanced Scorecard (BSC).

## ⚠️ IMPORTANTE - Pré-requisitos

**Antes de executar os seeders, certifique-se de que você já tem:**

1. ✅ **Organizações** cadastradas (tab_organizacao)
2. ✅ **Usuários** cadastrados (tab_usuario / users)
3. ✅ **PEI Ativo** configurado (tab_pei)
4. ✅ **Perspectivas** cadastradas (tab_perspectiva)
5. ✅ **Objetivos Estratégicos** cadastrados (tab_objetivo_estrategico)
6. ✅ **Missão, Visão e Valores** cadastrados (tab_missao_visao_valores, tab_valores)
7. ✅ **Tipos de Execução** cadastrados (tab_tipo_execucao: Ação, Iniciativa, Projeto)
8. ✅ **Graus de Satisfação** cadastrados (tab_grau_satisfacao)

## 📋 O que será Criado

Os seeders criarão dados para as seguintes tabelas:

### 1. Planos de Ação
- **Tabela:** `tab_plano_de_acao`
- **Quantidade:** 3-5 planos por objetivo
- **Dados:** Descrição, datas, orçamento, status, PPA/LOA

### 2. Entregas
- **Tabela:** `tab_entregas`
- **Quantidade:** 3-7 entregas por plano de ação
- **Dados:** Descrição, status, período de medição

### 3. Indicadores
- **Tabela:** `tab_indicador`
- **Quantidade:** 2-3 por objetivo + alguns vinculados a planos
- **Dados:** Nome, tipo, fórmula, unidade de medida, fonte, meta

### 4. Linha de Base
- **Tabela:** `tab_linha_base_indicador`
- **Quantidade:** 1 por indicador
- **Dados:** Valor base do ano anterior ao início do PEI

### 5. Metas Anuais
- **Tabela:** `tab_meta_por_ano`
- **Quantidade:** 1 meta por ano do PEI por indicador
- **Dados:** Meta anual com crescimento incremental

### 6. Evolução Mensal
- **Tabela:** `tab_evolucao_indicador`
- **Quantidade:** Mensal desde início do PEI até mês atual
- **Dados:** Valores previstos, realizados, avaliação

### 7. Riscos
- **Tabela:** `tab_risco`
- **Quantidade:** 20-30 riscos
- **Dados:** Título, categoria, probabilidade, impacto, causas, consequências

### 8. Vínculo Risco-Objetivo
- **Tabela:** `tab_risco_objetivo`
- **Quantidade:** 1-3 objetivos por risco
- **Dados:** Relacionamento N:N

### 9. Mitigações de Risco
- **Tabela:** `tab_risco_mitigacao`
- **Quantidade:** 1-4 planos por risco
- **Dados:** Tipo, descrição, responsável, prazo, custo

### 10. Ocorrências de Risco
- **Tabela:** `tab_risco_ocorrencia`
- **Quantidade:** 30% dos riscos têm 1-3 ocorrências
- **Dados:** Data, descrição, impacto real, ações, lições

### 11. Análises SWOT e PESTEL
- **Tabela:** `tab_analise_ambiental`
- **Quantidade:** SWOT completa (4 quadrantes) + PESTEL completa (6 fatores) por organização
- **Dados:** Tipo, categoria, descrição, impacto

## 🚀 Como Executar

### Executar Todos os Seeders (Recomendado)

```bash
php artisan db:seed --class=PEIDataSeeder
```

Este comando executará todos os seeders na ordem correta.

### Executar Seeders Individualmente

Se preferir executar seeders específicos:

```bash
# Planos de Ação
php artisan db:seed --class=PlanoAcaoSeeder

# Entregas
php artisan db:seed --class=EntregaSeeder

# Indicadores
php artisan db:seed --class=IndicadorSeeder

# Linha de Base
php artisan db:seed --class=LinhaBaseIndicadorSeeder

# Metas Anuais
php artisan db:seed --class=MetaPorAnoSeeder

# Evolução Mensal
php artisan db:seed --class=EvolucaoIndicadorSeeder

# Riscos
php artisan db:seed --class=RiscoSeeder

# Vínculo Risco-Objetivo
php artisan db:seed --class=RiscoObjetivoSeeder

# Mitigações
php artisan db:seed --class=RiscoMitigacaoSeeder

# Ocorrências
php artisan db:seed --class=RiscoOcorrenciaSeeder

# Análises SWOT/PESTEL
php artisan db:seed --class=AnaliseAmbientalSeeder
```

## ⚙️ Ordem de Execução

A ordem é **CRÍTICA** devido aos relacionamentos entre tabelas:

1. PlanoAcaoSeeder *(depende de Objetivos)*
2. EntregaSeeder *(depende de Planos)*
3. IndicadorSeeder *(depende de Objetivos e Planos)*
4. LinhaBaseIndicadorSeeder *(depende de Indicadores)*
5. MetaPorAnoSeeder *(depende de Indicadores)*
6. EvolucaoIndicadorSeeder *(depende de Metas)*
7. RiscoSeeder *(independente)*
8. RiscoObjetivoSeeder *(depende de Riscos e Objetivos)*
9. RiscoMitigacaoSeeder *(depende de Riscos)*
10. RiscoOcorrenciaSeeder *(depende de Riscos)*
11. AnaliseAmbientalSeeder *(independente)*

## 🔄 Re-executar Seeders

Os seeders são **idempotentes** - podem ser executados múltiplas vezes:

- **NÃO dropam** dados de Organizações, Usuários, PEI, Perspectivas, Objetivos, Missão/Visão/Valores
- **Limpam apenas** os dados das tabelas que estão sendo populadas
- Seguro para re-execução sem perda de dados mestres

## 📊 Dados Gerados

### Características dos Dados

- **Realistas:** Descrições e valores baseados em casos reais
- **Variados:** Uso de randomização para diversidade
- **Consistentes:** Relacionamentos mantidos corretamente
- **Temporais:** Datas e períodos coerentes com o ciclo do PEI

### Distribuição

- **Indicadores:** 80% com evolução positiva (70-110% da meta)
- **Planos:** 70% em andamento, 20% concluídos, 10% não iniciados
- **Riscos:** Distribuição realista de probabilidade e impacto
- **Ocorrências:** 30% dos riscos materializados

## 🛠️ Troubleshooting

### Erro: "Nenhum PEI ativo encontrado"
**Solução:** Cadastre um PEI ativo antes de executar os seeders

### Erro: "Nenhum objetivo encontrado"
**Solução:** Cadastre objetivos estratégicos no sistema

### Erro: "Foreign key constraint fails"
**Solução:** Verifique se todas as tabelas de referência (Organizações, Usuários, etc.) estão populadas

### Performance Lenta
**Solução:** Os seeders inserem em lotes. Para grandes volumes, considere ajustar o tamanho dos chunks.

## 📝 Customização

Para ajustar os seeders:

1. **Quantidade de dados:** Modifique os `rand()` nos loops
2. **Descrições:** Edite os arrays de templates
3. **Distribuições:** Ajuste percentuais e probabilidades
4. **Períodos:** Altere ranges de datas

## 🎯 Próximos Passos

Após executar os seeders:

1. ✅ Acesse o **Mapa Estratégico** para visualizar objetivos
2. ✅ Confira os **Indicadores** e suas evoluções
3. ✅ Revise os **Planos de Ação** e entregas
4. ✅ Analise a **Matriz de Riscos**
5. ✅ Explore as análises **SWOT** e **PESTEL**
6. ✅ Gere **Relatórios** para validar os dados

---

**Desenvolvido para:** Sistema SEAE - Planejamento Estratégico
**Versão:** 1.0
**Data:** Dezembro 2024
