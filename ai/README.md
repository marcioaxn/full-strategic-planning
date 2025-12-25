# Documentação AI do Projeto

Este diretório contém documentação essencial para assistentes de IA trabalharem neste projeto.

## 📋 Arquivos Obrigatórios

### 1. [METODOLOGIA-CIENTIFICA.md](./METODOLOGIA-CIENTIFICA.md)
**LEIA PRIMEIRO** antes de resolver qualquer problema.
- Protocolo obrigatório de resolução de problemas
- Método científico vs "achismo"
- Checklist pré-correção
- Regra de ouro: Compare primeiro, modifique depois

### 2. [COMANDOS-DIAGNOSTICO.md](./COMANDOS-DIAGNOSTICO.md)
Comandos prontos para diagnóstico científico.
- Comparação com código funcional (git)
- Diagnóstico de JavaScript/Alpine/Livewire
- Diagnóstico de modais e componentes
- Diagnóstico de assets e cache
- Workflow completo de troubleshooting

### 3. [architecture.md](./architecture.md)
Documentação completa da arquitetura do projeto.
- Versões de Laravel, Livewire, Jetstream, Bootstrap
- Estrutura de banco de dados
- Autenticação e autorização
- Frontend: Vite, SCSS, Alpine
- Padrões e convenções

## 🚨 Regras Fundamentais

### SEMPRE faça:
1. ✅ Compare com código funcional (`git show HEAD:arquivo`)
2. ✅ Leia erros COMPLETOS do console/logs
3. ✅ Identifique diferenças EXATAS
4. ✅ Baseie correções em EVIDÊNCIAS
5. ✅ Modifique APENAS o necessário
6. ✅ Valide após cada mudança

### NUNCA faça:
1. ❌ Trabalhe por "achismo" ou suposições
2. ❌ Modifique código sem comparar com versão funcional
3. ❌ Ignore erros de console/logs
4. ❌ Faça múltiplas mudanças simultâneas
5. ❌ Atualize APIs sem verificar versão usada
6. ❌ Introduza novos bugs ao corrigir problemas

## 🔧 Workflow Padrão de Troubleshooting

```bash
# 1. Identifique o problema exato
# - Leia erro completo do console/logs
# - Copie para arquivo se necessário (ai/console.log)

# 2. Compare com código funcional
git diff HEAD resources/js/app.js
git show HEAD:resources/js/app.js

# 3. Identifique a diferença
diff <(git show HEAD:arquivo) arquivo_atual

# 4. Entenda POR QUE a diferença causa o problema
git log -p -- arquivo

# 5. Aplique correção baseada em evidência
# - Reverta para versão funcional OU
# - Corrija baseado em comparação exata

# 6. Valide
npm run build
php artisan view:clear
php artisan cache:clear
# Teste no browser
```

## 📝 Casos de Uso Reais

### Caso 1: Modais não abrem (RESOLVIDO)
- ❌ Tentativa por achismo: mudou eventos Livewire, introduziu novos erros
- ✅ Método científico: comparou app.js com git, identificou import Alpine duplicado, removeu, funcionou

**Lição:** Compare com código funcional ANTES de modificar.

### Caso 2: Erros no console JavaScript
- ❌ Ignorar erros e tentar "corrigir" aleatoriamente
- ✅ Copiar erro exato, identificar linha/arquivo, comparar com git, corrigir baseado em evidência

**Lição:** Erros são evidências, não obstáculos.

## 🎯 Quando Consultar Cada Arquivo

| Situação | Arquivo a Consultar |
|----------|-------------------|
| Antes de qualquer troubleshooting | [METODOLOGIA-CIENTIFICA.md](./METODOLOGIA-CIENTIFICA.md) |
| Precisa de comandos git/diagnóstico | [COMANDOS-DIAGNOSTICO.md](./COMANDOS-DIAGNOSTICO.md) |
| Dúvida sobre arquitetura/versões | [architecture.md](./architecture.md) |
| Erros de console salvos | `console.log` (se existir) |

## 💡 Princípios Fundamentais

1. **Evidência sobre Suposição**
   - Toda correção deve ser baseada em comparação com código funcional
   - "Achar" não é método científico

2. **Reversibilidade**
   - Toda mudança deve ser facilmente reversível
   - Use git para comparar e reverter

3. **Isolamento**
   - Uma mudança de cada vez
   - Valide antes de próxima mudança

4. **Documentação**
   - Erros vão para `ai/console.log`
   - Análises ficam documentadas
   - Histórico preservado no git

## 🔍 Como Este Diretório Deve Ser Usado

1. **Antes de iniciar qualquer troubleshooting:**
   - Leia `METODOLOGIA-CIENTIFICA.md`
   - Tenha `COMANDOS-DIAGNOSTICO.md` aberto para consulta

2. **Durante o troubleshooting:**
   - Siga o checklist pré-correção
   - Use comandos do arquivo de diagnóstico
   - Compare SEMPRE com código funcional

3. **Após resolver:**
   - Documente a solução se for caso novo
   - Atualize este README se necessário
   - Commit das mudanças com mensagem clara

## ⚖️ Consequências de Não Seguir

- Desperdício de recursos (tokens do plano Pro)
- Introdução de novos bugs
- Perda de tempo e confiança
- **INACEITÁVEL**

---

**Última atualização:** 2025-12-18
**Motivação:** Após quase esgotar limite do plano Pro por não seguir metodologia científica, esta documentação se tornou OBRIGATÓRIA para prevenir desperdício de recursos e garantir qualidade nas correções.
