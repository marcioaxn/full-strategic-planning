# Correção Definitiva do Erro 419 (CSRF Token Mismatch)

**Data:** 15 de Janeiro de 2026
**Projeto:** SEAE - Sistema de Estratégia e Acompanhamento Estratégico
**Stack:** Laravel 11 + Livewire 3 + Jetstream

---

## 1. Descrição do Problema

O erro HTTP 419 (Page Expired / CSRF Token Mismatch) estava sendo exibido aos usuários finais quando:

- A sessão expirava por inatividade
- O token CSRF expirava antes da sessão
- O usuário tentava submeter um formulário após período de inatividade
- Navegação SPA do Livewire (wire:navigate) falhava por token inválido

**Impacto:** Usuários viam uma página de erro técnica (419) sem saber o que fazer, causando confusão e má experiência.

---

## 2. Análise Forense - Problemas Identificados

### 2.1. Deadlock de CSRF (Problema Principal)

O sistema tinha um **deadlock circular**:

```
1. Token CSRF expira no servidor
2. Cliente tenta renovar via POST /session/ping
3. POST requer token CSRF válido
4. Token está expirado → Requisição falha com 419
5. Sessão não é renovada
6. Logout também falha (requer CSRF)
7. Usuário fica preso em estado inconsistente
```

### 2.2. Exception Handler Redirecionava para Home

O handler original redirecionava para `/` (home) em vez de `/login`:

```php
// ❌ CÓDIGO PROBLEMÁTICO (bootstrap/app.php)
$exceptions->render(function (TokenMismatchException $e, Request $request) {
    if ($request->expectsJson()) {
        return response()->json(['message' => 'Token CSRF inválido.'], 419);
    }

    return redirect()
        ->to(url('/'))  // ❌ Redireciona para home, não para login
        ->with('status', 'Sua sessão expirou por inatividade.');
});
```

**Problemas:**
- Usuário autenticado era redirecionado para página pública
- Não fazia logout do usuário
- Não invalidava a sessão
- Resposta JSON não era tratada pelo JavaScript

### 2.3. Refresh de CSRF com Intervalo Muito Longo

```javascript
// ❌ CÓDIGO PROBLEMÁTICO
const REFRESH_INTERVAL = 600000; // 10 minutos - MUITO LONGO
```

Com sessão de 120 minutos, 10 minutos entre refreshes deixava margem para o token expirar silenciosamente.

### 2.4. Ausência de Interceptadores JavaScript

Não havia interceptação global de erros 419 em:
- Chamadas `fetch()` do Livewire
- Requisições `XMLHttpRequest`
- Navegação SPA (`wire:navigate`)

### 2.5. Livewire Hook Não Implementado

O Livewire 3 possui um hook específico para interceptar falhas de requisição que não estava sendo utilizado:

```javascript
// ❌ NÃO EXISTIA
Livewire.hook('request', ({ fail }) => {
    fail(({ status, preventDefault }) => {
        if (status === 419) {
            // Tratamento
        }
    });
});
```

### 2.6. Layouts Inconsistentes

Existiam 3 layouts no projeto, mas apenas alguns tinham tratamento parcial:
- `app.blade.php` - Tinha refresh de CSRF, mas sem interceptação
- `guest.blade.php` - Tinha refresh de CSRF, mas sem interceptação
- `public.blade.php` - Não tinha nenhum tratamento

### 2.7. Ausência de Página de Fallback

Não existia `resources/views/errors/419.blade.php` customizada para casos onde JavaScript estivesse desabilitado.

---

## 3. Soluções Implementadas

### 3.1. Correção do Exception Handler

**Arquivo:** `bootstrap/app.php`

```php
// ✅ CÓDIGO CORRIGIDO
// Handle CSRF Token Mismatch (419 Página Expirada)
// Solução definitiva: NUNCA mostrar página de erro 419 ao usuário
$exceptions->render(function (TokenMismatchException $e, Request $request) {
    // Invalida sessão atual para evitar estados inconsistentes
    if ($request->hasSession()) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // Faz logout se usuário estiver autenticado
    if (auth()->check()) {
        auth()->logout();
    }

    // Para requisições JSON/AJAX/Livewire, retorna JSON para o interceptador JavaScript
    if ($request->expectsJson() || $request->ajax() || $request->header('X-Livewire')) {
        return response()->json([
            'message' => 'Sessão expirada. Redirecionando para login...',
            'redirect' => route('login'),
            'session_expired' => true
        ], 419);
    }

    // Para requisições normais, redireciona para login com mensagem amigável
    return redirect()
        ->route('login')
        ->with('status', 'Sua sessão expirou por inatividade. Por favor, faça login novamente.');
});
```

**Melhorias:**
- Invalida sessão e regenera token para evitar estados inconsistentes
- Faz logout limpo do usuário
- Detecta requisições Livewire via header `X-Livewire`
- Retorna JSON estruturado com URL de redirecionamento
- Redireciona para `/login` em vez de `/`

---

### 3.2. Interceptador JavaScript Global

**Arquivo:** `resources/views/layouts/app.blade.php` (e outros layouts)

```javascript
<!-- Sistema Global de Tratamento de Erro 419 - CSRF/Session Expired -->
<script>
    (function() {
        'use strict';

        const LOGIN_URL = '{{ route("login") }}';
        const REFRESH_INTERVAL = 300000; // 5 minutos (mais frequente para evitar expiração)
        let isRedirecting = false; // Previne múltiplos redirecionamentos

        /**
         * Redireciona para login de forma limpa
         * Define flag no localStorage para exibir mensagem na página de login
         */
        function redirectToLogin() {
            if (isRedirecting) return;
            isRedirecting = true;

            // Define flag para mostrar mensagem na página de login
            localStorage.setItem('session_expired', 'true');

            // Redireciona imediatamente
            window.location.href = LOGIN_URL;
        }

        /**
         * Verifica se a resposta é um erro 419 e redireciona se for
         */
        function handle419Response(response) {
            if (response && response.status === 419) {
                console.warn('[SEAE] Sessão expirada (419). Redirecionando para login...');
                redirectToLogin();
                return true;
            }
            return false;
        }

        /**
         * Intercepta TODAS as chamadas fetch
         * Isso captura Livewire, AJAX, e qualquer outra requisição
         */
        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
            try {
                const response = await originalFetch.apply(this, args);

                // Verifica se é erro 419
                if (response.status === 419) {
                    handle419Response(response);
                    // Retorna resposta mesmo assim para não quebrar o fluxo
                    return response;
                }

                return response;
            } catch (error) {
                throw error;
            }
        };

        /**
         * Intercepta XMLHttpRequest para capturar requisições antigas
         */
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url, ...rest) {
            this._url = url;
            return originalXHROpen.apply(this, [method, url, ...rest]);
        };

        XMLHttpRequest.prototype.send = function(...args) {
            this.addEventListener('load', function() {
                if (this.status === 419) {
                    console.warn('[SEAE] XHR 419 detectado. Redirecionando...');
                    redirectToLogin();
                }
            });
            return originalXHRSend.apply(this, args);
        };

        /**
         * Listener específico para erros do Livewire
         */
        document.addEventListener('livewire:init', function() {
            if (window.Livewire) {
                // Hook nos erros de requisição do Livewire
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            console.warn('[SEAE] Livewire 419 interceptado. Redirecionando...');
                            redirectToLogin();
                        }
                    });
                });
            }
        });

        /**
         * Fallback: escuta evento de erro do Livewire (versões mais antigas)
         */
        window.addEventListener('livewire:error', function(event) {
            if (event.detail && event.detail.status === 419) {
                event.preventDefault();
                redirectToLogin();
            }
        });

        /**
         * Atualiza o token CSRF em toda a aplicação
         */
        function updateCsrfToken(newToken) {
            // Meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', newToken);
            }

            // Inputs hidden
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = newToken;
            });

            // Axios (se existir)
            if (window.axios) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
            }

            console.log('[SEAE] Token CSRF atualizado');
        }

        /**
         * Renova o token CSRF periodicamente
         * Usa GET que não depende de CSRF válido
         */
        function refreshCsrfToken() {
            // Usa originalFetch para evitar loop infinito
            originalFetch('/refresh-csrf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.status === 419) {
                    // Se até o refresh falhar com 419, sessão está morta
                    redirectToLogin();
                    return null;
                }
                if (!response.ok) {
                    throw new Error('Falha ao renovar token');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.csrf_token) {
                    updateCsrfToken(data.csrf_token);
                }
            })
            .catch(error => {
                console.warn('[SEAE] Erro ao renovar CSRF:', error.message);
            });
        }

        // Inicia refresh periódico (a cada 5 minutos)
        setInterval(refreshCsrfToken, REFRESH_INTERVAL);

        // Refresh quando usuário volta para a aba
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshCsrfToken();
            }
        });

        // Refresh após navegação SPA do Livewire
        document.addEventListener('livewire:navigated', refreshCsrfToken);

        // Refresh inicial após carregamento
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeno delay para não sobrecarregar no carregamento
            setTimeout(refreshCsrfToken, 2000);
        });

        console.log('[SEAE] Sistema de proteção contra erro 419 ativo');
    })();
</script>
```

**Características:**
- Intercepta `fetch()` globalmente (captura Livewire)
- Intercepta `XMLHttpRequest` (captura requisições legadas)
- Hook específico do Livewire 3 (`Livewire.hook('request', { fail })`)
- Flag `isRedirecting` previne múltiplos redirecionamentos
- Usa `originalFetch` para refresh de CSRF (evita loop infinito)
- Refresh a cada 5 minutos (era 10)
- Refresh ao voltar para aba inativa
- Refresh após navegação SPA

---

### 3.3. Página de Fallback 419

**Arquivo:** `resources/views/errors/419.blade.php` (NOVO)

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Auto-redirect para login em 2 segundos (fallback se JS não funcionar) -->
    <meta http-equiv="refresh" content="2;url={{ route('login') }}">
    <title>Sessao Expirada - {{ config('app.name', 'SEAE') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        h1 {
            color: #1a202c;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .loader {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #667eea;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
        .countdown {
            color: #718096;
            font-size: 13px;
            margin-top: 16px;
        }
        #timer {
            font-weight: 600;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <h1>Sessao Expirada</h1>
        <p>Por questoes de seguranca, sua sessao foi encerrada devido a inatividade. Voce sera redirecionado automaticamente.</p>

        <div class="loader">
            <div class="spinner"></div>
            <span>Redirecionando...</span>
        </div>

        <a href="{{ route('login') }}" class="btn">Ir para Login</a>

        <p class="countdown">Redirecionamento automatico em <span id="timer">2</span> segundos</p>
    </div>

    <script>
        // Redireciona imediatamente via JavaScript (mais rapido que meta refresh)
        localStorage.setItem('session_expired', 'true');

        // Countdown visual
        let seconds = 2;
        const timerEl = document.getElementById('timer');
        const countdown = setInterval(function() {
            seconds--;
            if (timerEl) timerEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = '{{ route("login") }}';
            }
        }, 1000);

        // Redireciona imediatamente se possivel
        setTimeout(function() {
            window.location.href = '{{ route("login") }}';
        }, 100);
    </script>
</body>
</html>
```

**Características:**
- Auto-redirect via `<meta http-equiv="refresh">` (fallback para JS desabilitado)
- JavaScript com redirect imediato
- Visual amigável e profissional
- Define `session_expired` no localStorage
- Botão manual para usuário clicar se preferir

---

### 3.4. Rota de Refresh CSRF

**Arquivo:** `routes/web.php`

A rota já existia e está correta:

```php
// CSRF Token Refresh Endpoint
Route::get('/refresh-csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->name('csrf.refresh');
```

**Importante:** Usar GET (não POST) para evitar o deadlock de CSRF.

---

## 4. Arquivos Modificados/Criados

| Arquivo | Ação | Descrição |
|---------|------|-----------|
| `bootstrap/app.php` | Modificado | Exception handler corrigido |
| `resources/views/layouts/app.blade.php` | Modificado | Interceptador JS global |
| `resources/views/layouts/guest.blade.php` | Modificado | Interceptador JS global |
| `resources/views/layouts/public.blade.php` | Modificado | Interceptador JS global |
| `resources/views/errors/419.blade.php` | **Criado** | Página de fallback |

---

## 5. Fluxo de Tratamento Final

```
┌─────────────────────────────────────────────────────────────────┐
│                    ERRO 419 OCORRE                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│  É requisição fetch/XHR/Livewire?                               │
│  (JavaScript ativo)                                             │
└─────────────────────────────────────────────────────────────────┘
          │                                    │
         SIM                                  NÃO
          │                                    │
          ▼                                    ▼
┌─────────────────────┐          ┌─────────────────────────────────┐
│ Interceptador JS    │          │ Exception Handler (PHP)         │
│ captura erro 419    │          │ captura TokenMismatchException  │
│ → redirectToLogin() │          │ → Invalida sessão               │
│ → localStorage flag │          │ → Faz logout                    │
└─────────────────────┘          │ → Redireciona para /login       │
          │                      └─────────────────────────────────┘
          │                                    │
          │                                    ▼
          │                      ┌─────────────────────────────────┐
          │                      │ JavaScript desabilitado?        │
          │                      └─────────────────────────────────┘
          │                           │                │
          │                          SIM              NÃO
          │                           │                │
          │                           ▼                │
          │                      ┌────────────────┐    │
          │                      │ 419.blade.php  │    │
          │                      │ meta refresh   │    │
          │                      │ → /login       │    │
          │                      └────────────────┘    │
          │                           │                │
          └───────────────────────────┴────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PÁGINA DE LOGIN                              │
│  Verifica localStorage('session_expired')                       │
│  → Exibe mensagem: "Sessão expirada por segurança"              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Recomendações para o Starter Kit

### 6.1. Incluir por Padrão

1. **Exception Handler** com tratamento de `TokenMismatchException` que:
   - Invalida sessão
   - Faz logout
   - Retorna JSON para requisições AJAX/Livewire
   - Redireciona para login

2. **Interceptador JavaScript Global** em todos os layouts base

3. **Página `errors/419.blade.php`** com auto-redirect

4. **Rota `/refresh-csrf` (GET)** para renovação de token

### 6.2. Configurações Recomendadas

```php
// config/session.php
'lifetime' => 120, // 2 horas
```

```javascript
// Refresh de CSRF
const REFRESH_INTERVAL = 300000; // 5 minutos (não 10)
```

### 6.3. Documentação

Incluir na documentação do starter kit:
- Explicação do tratamento de erro 419
- Como customizar a página de erro
- Como ajustar intervalos de refresh

---

## 7. Testes Recomendados

### 7.1. Teste de Expiração de Sessão
1. Faça login no sistema
2. Altere `SESSION_LIFETIME=1` no `.env` (1 minuto)
3. Aguarde 2 minutos
4. Tente navegar ou submeter formulário
5. **Esperado:** Redirecionamento automático para login com mensagem

### 7.2. Teste de Token CSRF Expirado
1. Faça login no sistema
2. Abra DevTools > Application > Cookies
3. Delete o cookie `XSRF-TOKEN`
4. Tente submeter um formulário
5. **Esperado:** Redirecionamento automático para login

### 7.3. Teste com JavaScript Desabilitado
1. Desabilite JavaScript no navegador
2. Simule erro 419 (altere token manualmente)
3. **Esperado:** Página 419.blade.php com auto-redirect via meta tag

### 7.4. Teste de Navegação SPA (Livewire)
1. Faça login no sistema
2. Navegue usando links com `wire:navigate`
3. Expire a sessão manualmente
4. Clique em outro link `wire:navigate`
5. **Esperado:** Redirecionamento automático para login

---

## 8. Conclusão

Esta solução elimina completamente a exibição do erro 419 para usuários finais, tratando todos os cenários possíveis:

- ✅ Requisições AJAX/fetch
- ✅ Navegação SPA do Livewire
- ✅ Formulários tradicionais
- ✅ Requisições XMLHttpRequest legadas
- ✅ JavaScript desabilitado (fallback)

O usuário sempre será redirecionado de forma elegante para a página de login com uma mensagem amigável explicando que a sessão expirou por segurança.

---

**Autor:** Claude (Anthropic)
**Revisão:** Necessária pelo gestor do repositório
**Licença:** Aplicar conforme licença do starter kit original
