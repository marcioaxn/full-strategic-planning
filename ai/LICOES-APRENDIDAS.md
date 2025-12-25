# Lições Aprendidas - Incidentes Resolvidos

## Incidente #1: Modais não abrem após alterações (2025-12-18)

### 📊 Resumo
- **Problema:** Modais de editar/excluir leads não abriam
- **Sintoma:** Spinner aparecia (Livewire funcionando), mas modais não abriam
- **Impacto:** Quase todo limite do plano Pro consumido
- **Causa raiz:** Import duplicado do Alpine.js
- **Tempo para resolver:** Várias tentativas por achismo antes do método científico

### ❌ O Que Foi Feito Errado

1. **Tentativa 1 - Achismo sobre tooltips**
   - Suposição: "tooltips com `data-bs-toggle` bloqueiam `wire:click`"
   - Ação: Substituiu `<x-action-button>` por botões HTML
   - Resultado: Problema persistiu

2. **Tentativa 2 - Achismo sobre Livewire 2 vs 3**
   - Suposição: "eventos do Livewire 2 não funcionam no Livewire 3"
   - Ação: Mudou `livewire:load` para `livewire:init`
   - Resultado: Introduziu NOVOS erros no console

3. **Tentativa 3 - Achismo sobre Alpine faltando**
   - Suposição: "erro diz Alpine undefined, então precisa importar"
   - Ação: Adicionou `import Alpine from 'alpinejs'`
   - Resultado: "Multiple instances of Alpine running"

### ✅ O Que Funcionou (Método Científico)

**Passo 1: Evidência externa**
- Usuário testou em outro computador clonando do GitHub
- Código do GitHub funcionava perfeitamente
- **Conclusão:** Diferença entre código local e GitHub

**Passo 2: Comparação científica**
```bash
git show HEAD:resources/js/app.js
git show HEAD:resources/js/bootstrap.js
```

**Passo 3: Identificação de diferenças exatas**
```diff
# bootstrap.js
- import axios from 'axios';
+ import axios from 'axios';
+ import 'bootstrap/dist/js/bootstrap.bundle.min.js';

# app.js
- import mask from '@alpinejs/mask';
+ import Alpine from 'alpinejs';  # ERRO: duplicado
+ import mask from '@alpinejs/mask';
```

**Passo 4: Análise de causa raiz**
- Livewire 3 já inclui Alpine via `@livewireScripts`
- `window.deferLoadingAlpine = true` existe para controlar isso
- Importar Alpine separadamente cria DUAS instâncias
- Modais usam `@entangle` que precisa do Alpine do Livewire

**Passo 5: Correção baseada em evidência**
- Removeu: `import Alpine from 'alpinejs'` (estava duplicado)
- Adicionou: `import 'bootstrap/dist/js/bootstrap.bundle.min.js'` (estava faltando)
- Exatamente igual ao código do GitHub que funciona

**Resultado:** ✅ Modais funcionaram imediatamente

### 📚 Lições Aprendidas

#### 1. SEMPRE compare com código funcional PRIMEIRO
```bash
# Antes de QUALQUER correção
git show HEAD:arquivo
git diff HEAD arquivo
```

#### 2. Erros de console são EVIDÊNCIAS, não obstáculos
- "Alpine is not defined" → não significa "precisa importar Alpine"
- Significa: Alpine não está acessível ONDE está sendo usado
- Pode ser: falta importar OU já está sendo carregado de outra forma

#### 3. Um erro pode ter múltiplas causas
- Alpine undefined pode ser:
  - Falta importar (raro em projeto com Livewire)
  - Está sendo carregado depois (timing)
  - Conflito de múltiplas instâncias
- **Método científico identifica qual é**

#### 4. Livewire 3 + Alpine: particularidades
- ✅ Alpine vem EMBUTIDO no Livewire 3
- ✅ Exposto via `@livewireScripts`
- ✅ Controlar com `window.deferLoadingAlpine = true`
- ❌ NÃO importar Alpine separadamente
- ❌ NÃO tentar "corrigir" o que já está correto

#### 5. Custo do achismo
- Tentativa 1: ~15k tokens desperdiçados
- Tentativa 2: ~20k tokens desperdiçados
- Tentativa 3: ~10k tokens desperdiçados
- **Solução científica: ~5k tokens**
- **Total desperdiçado: ~45k tokens** (quase 25% do limite mensal Pro)

### 🎯 Protocolo para Incidentes Futuros

#### Antes de QUALQUER ação corretiva:

```bash
# 1. Código funciona em algum lugar?
# SIM → compare com esse código
git show HEAD:arquivo
git diff origin/main arquivo

# NÃO → procure versão anterior que funcionava
git log --oneline -- arquivo
git show COMMIT:arquivo

# 2. Identifique DIFERENÇA EXATA
diff <(git show HEAD:arquivo) arquivo_atual

# 3. Entenda POR QUÊ a diferença causa o problema
# - Não suponha
# - Leia documentação oficial se necessário
# - Teste hipótese isoladamente

# 4. Corrija APENAS a diferença identificada
# 5. Valide
npm run build && php artisan view:clear

# 6. Se não resolver, PARE e reavalie
# - Não faça mais mudanças
# - Volte ao passo 1
```

### 🔍 Red Flags - Quando PARAR e Revisar

Se você pensar/disser qualquer uma dessas frases, PARE:

- ❌ "Acho que o problema é..."
- ❌ "Provavelmente precisa..."
- ❌ "Geralmente quando isso acontece..."
- ❌ "Vou tentar mudar X pra ver se..."
- ❌ "Deve ser porque..."

Em vez disso:

- ✅ "O código do GitHub mostra que..."
- ✅ "O git diff revela que a diferença é..."
- ✅ "O erro na linha X indica que..."
- ✅ "Comparando com HEAD, vejo que..."
- ✅ "A evidência aponta para..."

### 📖 Referências Críticas

**Documentação que deveria ter sido consultada primeiro:**
- [Livewire 3 Docs - Alpine.js](https://livewire.laravel.com/docs/alpine)
- [Livewire 3 Docs - JavaScript](https://livewire.laravel.com/docs/javascript)

**Citação relevante da documentação:**
> "Livewire ships with Alpine.js included. You don't need to import it separately."

**Se tivesse lido isso ANTES: 0 tokens desperdiçados**

### 💡 Aplicação em Futuros Incidentes

#### Template de Análise

```markdown
## Incidente: [Nome do problema]

### Evidências coletadas
- [ ] Erro completo do console/logs
- [ ] Código do git que funciona
- [ ] Diferença exata identificada
- [ ] Documentação oficial consultada

### Comparação científica
```bash
git show HEAD:arquivo
git diff HEAD arquivo
```

### Hipótese baseada em evidência
[Não em achismo]

### Correção aplicada
[Baseada em comparação, não suposição]

### Validação
- [ ] Erro original resolvido
- [ ] Nenhum novo erro introduzido
- [ ] Funciona igual ao código do GitHub/produção
```

---

**Conclusão:** Este incidente custou caro em tokens e tempo, mas gerou documentação que previne recorrência. A metodologia científica não é opcional - é obrigatória.
