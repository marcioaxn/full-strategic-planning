<x-guest-layout>
    <div class="auth-full-page d-flex flex-column flex-lg-row">
        
        {{-- Coluna Esquerda: Visual & Inspiração --}}
        <div class="auth-visual d-none d-lg-flex flex-column justify-content-between p-5 text-white">
            <div class="auth-logo-wrapper animate-fade-in-down">
                <a href="/" class="d-flex align-items-center text-decoration-none text-white">
                    <div class="icon-circle bg-white bg-opacity-20 backdrop-blur me-3 shadow-sm">
                        <i class="bi bi-diagram-3 fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 tracking-tight">SEAE</h4>
                        <small class="opacity-75 text-uppercase letter-spacing-1" style="font-size: 0.65rem;">Sistema de Gestão Estratégica</small>
                    </div>
                </a>
            </div>

            <div class="auth-quote animate-fade-in">
                <h1 class="display-4 fw-bold mb-4">Bem-vindo à sua central de <span class="text-warning">governança</span>.</h1>
                <p class="lead opacity-75">Gerencie ciclos PEI, monitore indicadores SMART e execute planos de ação com o rigor técnico que sua organização exige.</p>
                
                <div class="mt-5 d-flex gap-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-white bg-opacity-10 p-2"><i class="bi bi-shield-lock text-warning"></i></div>
                        <span class="small fw-medium">Ambiente Seguro</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-white bg-opacity-10 p-2"><i class="bi bi-bar-chart-line text-warning"></i></div>
                        <span class="small fw-medium">BI & Analytics</span>
                    </div>
                </div>
            </div>

            <div class="auth-footer-visual animate-fade-in-up">
                <p class="small opacity-50 mb-0">&copy; {{ date('Y') }} Strategic Planning System. Todos os direitos reservados.</p>
            </div>
        </div>

        {{-- Coluna Direita: Formulário --}}
        <div class="auth-form-container d-flex align-items-center justify-content-center p-4 p-lg-5 bg-body">
            <div class="auth-form-card animate-fade-in-right">
                
                <div class="mb-5 text-start">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="fw-800 text-dark mb-0">Entrar no Sistema</h2>
                        <a href="{{ url('/') }}" class="btn btn-sm btn-light rounded-pill px-3 border shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Voltar ao Início
                        </a>
                    </div>
                    <p class="text-muted">Acesse sua conta para gerenciar a estratégia institucional.</p>
                </div>

                <x-validation-errors class="mb-4 alert-modern" />

                @session('status')
                    <div class="alert alert-success alert-modern mb-4 d-flex align-items-center rounded-4 shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                        <span class="small fw-medium text-dark">{{ $value }}</span>
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    {{-- E-mail --}}
                    <div class="mb-4">
                        <label class="form-label-premium">E-mail Corporativo</label>
                        <div class="input-group-premium">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="seu@email.com" required autofocus autocomplete="username">
                        </div>
                    </div>

                    {{-- Senha --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label-premium mb-0">Senha de Acesso</label>
                            @if (Route::has('password.request'))
                                <a class="text-primary small fw-bold text-decoration-none hover-underline" style="font-size: 0.7rem; text-transform: uppercase;" href="{{ route('password.request') }}">
                                    Esqueceu a senha?
                                </a>
                            @endif
                        </div>
                        <div class="input-group-premium">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" id="password" class="form-control" style="padding-right: 3rem;" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="btn-password-toggle" onclick="togglePassword('password')" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Manter Conectado --}}
                    <div class="mb-4">
                        <div class="form-check form-check-premium d-flex align-items-center">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label small text-muted ms-2" for="remember_me" style="cursor: pointer;">
                                Manter conectado por 30 dias
                            </label>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill gradient-theme-btn py-3 shadow-lg hover-scale" id="loginButton">
                            <span class="btn-text">
                                <i class="bi bi-box-arrow-in-right me-2"></i> {{ __('Entrar') }}
                            </span>
                            <span class="btn-loading d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                {{ __('Validando credenciais...') }}
                            </span>
                        </button>
                    </div>

                    @if (Route::has('register'))
                        <div class="text-center pt-2">
                            <span class="text-muted small">Ainda não tem acesso?</span>
                            <a href="{{ route('register') }}" class="text-primary small fw-bold text-decoration-none ms-1 hover-underline">
                                Criar conta agora
                            </a>
                        </div>
                    @endif
                </form>

                {{-- Security Badge --}}
                <div class="mt-5 p-3 rounded-4 bg-light bg-opacity-50 border text-center d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-check text-success"></i>
                    <span class="x-small text-muted fw-medium">Conexão segura com criptografia ponta a ponta.</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Reutilização de estilos Premium da Register */
        .auth-full-page { min-height: 100vh; overflow-x: hidden; }
        
        .auth-visual {
            width: 45%;
            background: linear-gradient(135deg, #1B408E 0%, #4361EE 100%), 
                        url('https://images.unsplash.com/photo-1454165833767-027ffea9e778?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-blend-mode: multiply;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .auth-form-container { width: 55%; flex-grow: 1; }
        .auth-form-card { width: 100%; max-width: 420px; }

        @media (max-width: 991.98px) {
            .auth-form-container { width: 100%; }
        }

        .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .tracking-tight { letter-spacing: -1px; }

        .form-label-premium {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--bs-secondary);
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .input-group-premium {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-premium i:not(.btn-password-toggle i) {
            position: absolute;
            left: 1rem;
            color: var(--bs-primary);
            font-size: 1.1rem;
            opacity: 0.7;
            z-index: 5;
        }

        .input-group-premium .form-control {
            padding: 0.8rem 1rem 0.8rem 3rem;
            border-radius: 12px;
            border: 2px solid rgba(0,0,0,0.05);
            background-color: rgba(var(--bs-body-color-rgb), 0.03);
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }

        .input-group-premium .form-control:focus {
            border-color: var(--bs-primary);
            background-color: #fff;
            box-shadow: 0 10px 20px rgba(var(--bs-primary-rgb), 0.08);
        }

        [data-bs-theme="dark"] .input-group-premium .form-control {
            background-color: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.1);
            color: #fff;
        }

        .btn-password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--bs-secondary);
            cursor: pointer;
            padding: 0.25rem;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .btn-password-toggle i {
            position: static !important;
            opacity: 1 !important;
            font-size: 1.25rem !important;
        }
        .btn-password-toggle:hover { color: var(--bs-primary); transform: scale(1.1); }

        .form-check-premium .form-check-input { width: 1.1rem; height: 1.1rem; cursor: pointer; }

        .animate-fade-in-right { animation: fadeInRight 0.6s ease-out; }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        
        /* Shake Animation para erros */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        .shake { animation: shake 0.4s ease-in-out; }

        .backdrop-blur { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
        .hover-scale:hover { transform: scale(1.02); }
        .x-small { font-size: 0.7rem; }
    </style>

    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const btn = input.nextElementSibling;
            const icon = btn.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginButton');

            if (form) {
                // Efeito shake se houver erros do Laravel
                @if ($errors->any())
                    form.classList.add('shake');
                    setTimeout(() => form.classList.remove('shake'), 500);
                @endif

                form.addEventListener('submit', function() {
                    if (form.checkValidity()) {
                        btn.disabled = true;
                        btn.querySelector('.btn-text').classList.add('d-none');
                        btn.querySelector('.btn-loading').classList.remove('d-none');
                    }
                });
            }
        });
    </script>
</x-guest-layout>