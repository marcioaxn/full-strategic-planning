# METODOLOGIA CIENTÍFICA OBRIGATÓRIA

## ⚠️ REGRA FUNDAMENTAL

**NUNCA trabalhe por "achismo" ou suposições. SEMPRE siga o método científico.**

## PROTOCOLO OBRIGATÓRIO PARA RESOLUÇÃO DE PROBLEMAS

### 1. COLETA DE EVIDÊNCIAS (OBRIGATÓRIO)

Antes de qualquer ação corretiva, SEMPRE:

#### A. Compare com código que funciona
```bash
# Código do último commit (versão estável)
git show HEAD:caminho/do/arquivo

# Código em produção/ambiente funcional
git show origin/main:caminho/do/arquivo

# Diferenças entre versões
git diff HEAD caminho/do/arquivo
```

#### B. Verifique logs e erros reais
- Leia console do browser (NUNCA ignore erros de JavaScript)
- Verifique logs do Laravel (`storage/logs/laravel.log`)
- Analise erros de servidor (Apache/Nginx)

#### C. Identifique o estado atual do sistema
```bash
# Versões exatas instaladas
php artisan about
composer show | grep nome-do-pacote
npm list nome-do-pacote

# Configurações relevantes
cat config/arquivo-relevante.php
```

### 2. FORMULAÇÃO DE HIPÓTESE (BASEADA EM EVIDÊNCIAS)

❌ **ERRADO:** "Acho que o problema é X porque geralmente..."
✅ **CORRETO:** "O erro no console diz 'Alpine is not defined' na linha 16 de app.js, portanto falta importar Alpine OU Alpine está sendo carregado de outra forma"

### 3. VERIFICAÇÃO DA HIPÓTESE

Antes de aplicar correção:

```bash
# Teste a hipótese comparando com código funcional
diff <(git show HEAD:arquivo) arquivo_atual

# Ou busque no histórico quando funcionava
git log --oneline --all -- caminho/do/arquivo
git show COMMIT_HASH:caminho/do/arquivo
```

### 4. APLICAÇÃO DE CORREÇÃO BASEADA EM EVIDÊNCIAS

A correção DEVE:
1. Ser baseada em comparação com código que comprovadamente funciona
2. Modificar APENAS o necessário para resolver o problema identificado
3. Ser testável e reversível

❌ **NUNCA faça:**
- Mudanças baseadas em "melhores práticas" sem verificar o contexto
- Atualizações de API sem confirmar a versão usada no projeto
- Modificações em múltiplos arquivos simultaneamente sem evidência

✅ **SEMPRE faça:**
- Uma mudança de cada vez
- Baseada em comparação com código funcional
- Documente a evidência que levou à mudança

### 5. VALIDAÇÃO

Após correção:
```bash
# Limpe caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Recompile assets
npm run build

# Verifique que o erro original foi resolvido
# Verifique que NENHUM novo erro foi introduzido
```

## CASOS DE USO DESTE PROJETO

### Problema: Modais não abrem

❌ **MÉTODO ERRADO (achismo):**
1. "Deve ser problema de Livewire 2 vs 3"
2. Muda eventos para Livewire 3
3. Introduz novos erros
4. Tenta corrigir novos erros
5. Desperdiça tokens

✅ **MÉTODO CORRETO (científico):**
1. Compara `resources/js/app.js` local com `git show HEAD:resources/js/app.js`
2. Identifica diferença: falta `import Alpine from 'alpinejs'`
3. Verifica console: "Alpine is not defined"
4. Hipótese: Alpine não está sendo importado
5. Verifica: Alpine vem do Livewire via `@livewireScripts`
6. Conclusão: NÃO deve importar Alpine separadamente
7. Aplica correção: remove import manual
8. Valida: modais funcionam

### Problema: Erros no console JavaScript

❌ **MÉTODO ERRADO:**
- Ignorar erros
- Tentar "corrigir" sem ler a mensagem
- Modificar código sem entender o erro

✅ **MÉTODO CORRETO:**
1. Copiar erro EXATO do console
2. Identificar linha e arquivo do erro
3. Comparar arquivo com versão funcional (git)
4. Identificar O QUE mudou
5. Entender POR QUE mudou
6. Reverter OU corrigir baseado em evidência

## CHECKLIST PRÉ-CORREÇÃO

Antes de modificar QUALQUER código, responda:

- [ ] Li o erro/problema COMPLETO?
- [ ] Comparei com código que funciona (git/outro ambiente)?
- [ ] Identifiquei a DIFERENÇA EXATA?
- [ ] Entendo POR QUE a diferença causa o problema?
- [ ] Minha correção é baseada em EVIDÊNCIA, não achismo?
- [ ] Vou modificar APENAS o necessário?
- [ ] Sei como reverter se der errado?

**Se respondeu NÃO para qualquer item, NÃO faça a correção ainda.**

## REGRA DE OURO

> **"Comparar com código funcional PRIMEIRO, modificar DEPOIS"**

## CONSEQUÊNCIAS DE NÃO SEGUIR

- Desperdício de tokens do plano Pro do usuário
- Introdução de novos bugs
- Perda de tempo
- Perda de confiança
- **INACEITÁVEL**

## ARQUIVO DE REFERÊNCIA

Este documento deve ser consultado ANTES de qualquer troubleshooting.

**Se você está lendo isso depois de já ter tentado resolver algo: PARE. Reverta. Comece de novo seguindo este método.**
