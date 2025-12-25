# COMANDOS DE DIAGNÓSTICO CIENTÍFICO

## COMPARAÇÃO COM CÓDIGO FUNCIONAL

### Comparar arquivo atual com último commit estável
```bash
git diff HEAD resources/js/app.js
git diff HEAD resources/views/livewire/leads-table.blade.php
```

### Ver versão exata do arquivo em commit específico
```bash
# Último commit
git show HEAD:resources/js/app.js

# Commit específico
git show 9b10208:resources/js/app.js

# Ver diferença entre dois commits
git diff 9b10208 HEAD -- resources/js/app.js
```

### Encontrar quando arquivo foi modificado pela última vez
```bash
git log --oneline --all -- resources/js/app.js
git log -p --all -- resources/js/app.js  # com diff
```

### Comparar com código remoto (GitHub)
```bash
git fetch origin
git diff origin/main resources/js/app.js
```

## DIAGNÓSTICO DE JAVASCRIPT

### Verificar erros de console (via código)
```bash
# Após copiar erros do console para arquivo
cat ai/console.log | grep -i "error\|undefined\|cannot read"
cat ai/console.log | grep -i "Alpine\|Livewire"
```

### Verificar importações e exports
```bash
# Ver todas as importações em app.js
grep "^import" resources/js/app.js

# Ver se Alpine está sendo importado
grep -r "import.*Alpine" resources/js/

# Ver onde Alpine é usado
grep -r "Alpine\." resources/js/
grep -r "window.Alpine" resources/js/
```

## DIAGNÓSTICO DE LIVEWIRE

### Verificar versão exata do Livewire
```bash
composer show livewire/livewire
php artisan about | grep -i livewire
```

### Verificar componentes Livewire
```bash
# Listar todos os componentes
grep -r "class.*extends.*Component" app/Livewire/

# Ver como componente é chamado na view
grep -r "livewire:.*leads" resources/views/
grep -r "@livewire.*leads" resources/views/
```

### Verificar propriedades públicas do componente
```bash
# Ver todas as propriedades públicas
grep "public \$\|public bool\|public array" app/Livewire/LeadsTable.php

# Ver se wire:model aponta para propriedade existente
grep "wire:model" resources/views/livewire/leads-table.blade.php
```

## DIAGNÓSTICO DE MODALS

### Verificar se modal usa wire:model corretamente
```bash
# Ver todas as modais
grep -r "x-dialog-modal\|x-confirmation-modal" resources/views/

# Ver wire:model nas modais
grep -B2 -A2 "wire:model" resources/views/livewire/leads-table.blade.php | grep -i modal
```

### Verificar componente de modal
```bash
# Ver estrutura do componente modal
cat resources/views/components/modal.blade.php

# Ver se usa @entangle
grep "@entangle\|entangle" resources/views/components/modal.blade.php
```

## DIAGNÓSTICO DE ASSETS

### Ver assets compilados atuais
```bash
ls -lh public/build/assets/app-*.js
ls -lh public/build/assets/app-*.css
```

### Verificar manifest
```bash
cat public/build/manifest.json | grep "resources/js/app.js"
```

### Comparar tamanho de builds
```bash
# Build atual
ls -lh public/build/assets/app-*.js

# Após rebuild
npm run build
ls -lh public/build/assets/app-*.js
```

## DIAGNÓSTICO DE CACHE

### Limpar TODOS os caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan clear-compiled
```

### Verificar se tem cache ativo
```bash
# Config em cache?
test -f bootstrap/cache/config.php && echo "Config está em cache"

# Routes em cache?
test -f bootstrap/cache/routes-v7.php && echo "Routes estão em cache"

# Views compiladas?
ls -la storage/framework/views/ | wc -l
```

## DIAGNÓSTICO DE DEPENDÊNCIAS

### Verificar package.json vs instalado
```bash
# O que está no package.json
cat package.json | grep -A5 "dependencies"

# O que está instalado
npm list --depth=0

# Verificar específico
npm list alpinejs
npm list livewire
```

### Verificar composer.json vs instalado
```bash
# Livewire
composer show livewire/livewire

# Jetstream
composer show laravel/jetstream
```

## WORKFLOW COMPLETO DE DIAGNÓSTICO

```bash
# 1. Ver o que mudou desde último commit funcional
git status
git diff HEAD

# 2. Ver arquivos JS especificamente
git diff HEAD resources/js/

# 3. Ver último commit que tocou no arquivo problemático
git log --oneline -5 -- resources/js/app.js

# 4. Comparar com versão funcional
git show HEAD:resources/js/app.js > /tmp/app-original.js
diff resources/js/app.js /tmp/app-original.js

# 5. Se encontrar diferença, entender QUANDO e POR QUE mudou
git log -p -- resources/js/app.js | head -100

# 6. Aplicar correção baseada em evidência
git checkout HEAD -- resources/js/app.js  # ou editar manualmente

# 7. Validar
npm run build
php artisan view:clear
# Testar no browser
```

## QUANDO USAR CADA COMANDO

### Use `git diff HEAD` quando:
- Quiser ver O QUE mudou desde último commit
- Arquivo está modificado mas não sabe o que mudou

### Use `git show HEAD:arquivo` quando:
- Quiser ver EXATAMENTE como estava antes
- Precisa comparar conteúdo completo

### Use `git log -p -- arquivo` quando:
- Quiser ver HISTÓRICO de mudanças
- Precisa entender POR QUE mudou

### Use `npm run build` quando:
- Modificou qualquer arquivo em `resources/js/` ou `resources/scss/`
- Depois de `git checkout` em arquivos de assets
- Sempre antes de testar no browser
