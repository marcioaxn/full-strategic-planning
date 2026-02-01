<x-guest-layout>
    <div class="auth-full-page d-flex flex-column flex-lg-row">
        
        {{-- Coluna Esquerda: Visual & Inspiração --}}
        <div class="auth-visual d-none d-lg-flex flex-column justify-content-between p-5 text-white">
            <div class="auth-logo-wrapper animate-fade-in-down">
                <a href="/" class="d-flex align-items-center text-decoration-none text-white">
                    <div class="icon-circle bg-white bg-opacity-20 backdrop-blur me-3">
                        <i class="bi bi-diagram-3 fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 tracking-tight">SEAE</h4>
                        <small class="opacity-75 text-uppercase letter-spacing-1" style="font-size: 0.65rem;">Sistema de Gestão Estratégica</small>
                    </div>
                </a>
            </div>

            <div class="auth-quote animate-fade-in">
                <h1 class="display-4 fw-bold mb-4">Transforme sua visão em <span class="text-warning">resultados</span> concretos.</h1>
                <p class="lead opacity-75">Junte-se a centenas de gestores que utilizam o SEAE para governança de alto nível e monitoramento estratégico em tempo real.</p>
                
                <div class="mt-5 d-flex gap-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check fs-4 text-warning"></i>
                        <span class="small fw-medium">Segurança de Dados</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge fs-4 text-warning"></i>
                        <span class="small fw-medium">Alta Performance</span>
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
                
                <div class="mb-5">
                    <h2 class="fw-800 text-dark mb-2">Criar sua conta</h2>
                    <p class="text-muted">Preencha os dados abaixo para iniciar sua jornada estratégica.</p>
                </div>

                <x-validation-errors class="mb-4 alert-modern" />

                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    {{-- Nome --}}
                    <div class="mb-4">
                        <label class="form-label-premium">Nome Completo</label>
                        <div class="input-group-premium">
                            <i class="bi bi-person"></i>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Digite seu nome completo" required autofocus autocomplete="name">
                        </div>
                    </div>

                    {{-- E-mail --}}
                    <div class="mb-4">
                        <label class="form-label-premium">E-mail Corporativo</label>
                        <div class="input-group-premium">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="joao@organizacao.com" required autocomplete="username">
                        </div>
                    </div>

                    {{-- Senha --}}
                    <div class="mb-4">
                        <label class="form-label-premium">Senha de Acesso</label>
                        <div class="input-group-premium">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" id="password" class="form-control" style="padding-right: 3rem;" placeholder="Mínimo 8 caracteres" required autocomplete="new-password">
                            <button type="button" class="btn-password-toggle" onclick="togglePassword('password')" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <i class="bi bi-eye" style="position: static; opacity: 1;"></i>
                            </button>
                        </div>
                        {{-- Indicador de força da senha --}}
                        <div class="password-strength-meter mt-2">
                            <div class="meter-bar"><div class="meter-fill" id="strength-fill"></div></div>
                            <small class="text-muted x-small" id="strength-text">Segurança da senha</small>
                        </div>
                    </div>

                    {{-- Confirmar Senha --}}
                    <div class="mb-4">
                        <label class="form-label-premium">Confirmar Senha</label>
                        <div class="input-group-premium">
                            <i class="bi bi-shield-lock"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Repita sua senha" required autocomplete="new-password">
                        </div>
                    </div>

                    {{-- Termos e Condições --}}
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="mb-4">
                            <div class="form-check form-check-premium">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label small text-muted" for="terms">
                                    {!! __('Eu li e concordo com os :terms_of_service e :privacy_policy', [
                                            'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-primary text-decoration-none fw-bold">Termos de Uso</a>',
                                            'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-primary text-decoration-none fw-bold">Política de Privacidade</a>',
                                    ]) !!}
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill gradient-theme-btn py-3 shadow-lg hover-scale">
                            <i class="bi bi-person-plus-fill me-2"></i> {{ __('Finalizar Cadastro') }}
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted small">Já possui uma conta?</span>
                        <a href="{{ route('login') }}" class="text-primary small fw-bold text-decoration-none ms-1 hover-underline">
                            Fazer Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Layout Geral */
        .auth-full-page { min-height: 100vh; overflow-x: hidden; }
        
        .auth-visual {
            width: 45%;
            background: linear-gradient(135deg, #1B408E 0%, #4361EE 100%), 
                        url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-blend-mode: multiply;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .auth-form-container { width: 55%; flex-grow: 1; }
        .auth-form-card { width: 100%; max-width: 480px; }

        @media (max-width: 991.98px) {
            .auth-form-container { width: 100%; }
        }

        /* Tipografia */
        .fw-800 { font-weight: 800; }
        .letter-spacing-1 { letter-spacing: 1px; }
        .tracking-tight { letter-spacing: -1px; }

        /* Estilos de Formulário Premium */
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

        .input-group-premium i {
            position: absolute;
            left: 1rem;
            color: var(--bs-primary);
            font-size: 1.1rem;
            opacity: 0.7;
        }

        .input-group-premium .form-control {
            padding: 0.8rem 1rem 0.8rem 3rem;
            border-radius: 12px;
            border: 2px solid rgba(0,0,0,0.05);
            background-color: rgba(var(--bs-body-color-rgb), 0.03);
            font-weight: 500;
            transition: all 0.3s ease;
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

        /* Password Toggle */
        .btn-password-toggle {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            color: var(--bs-secondary);
            cursor: pointer;
            padding: 0.25rem;
        }

        /* Meter de Senha */
        .password-strength-meter { height: 4px; width: 100%; }
        .meter-bar { height: 4px; background: #eee; border-radius: 10px; overflow: hidden; }
        .meter-fill { height: 100%; width: 0; transition: all 0.3s ease; }

        /* Checkbox Premium */
        .form-check-premium .form-check-input { width: 1.2rem; height: 1.2rem; margin-top: 0.1rem; cursor: pointer; }

        /* Animações */
        .animate-fade-in-right { animation: fadeInRight 0.6s ease-out; }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        
        .backdrop-blur { backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
        .hover-scale:hover { transform: scale(1.02); }
    </style>

    <script>
        function togglePassword(id) {
            const el = document.getElementById(id);
            el.type = el.type === 'password' ? 'text' : 'password';
        }

        // Simples detector de força de senha
        document.getElementById('password').addEventListener('input', function(e) {
            const val = e.target.value;
            const fill = document.getElementById('strength-fill');
            const text = document.getElementById('strength-text');
            
            let strength = 0;
            if(val.length > 5) strength += 25;
            if(val.match(/[A-Z]/)) strength += 25;
            if(val.match(/[0-9]/)) strength += 25;
            if(val.match(/[^A-Za-z0-9]/)) strength += 25;

            const colors = ['#dc3545', '#ffc107', '#0dcaf0', '#198754'];
            const labels = ['Fraca', 'Média', 'Boa', 'Excelente'];
            const idx = Math.max(0, Math.floor(strength/26));

            fill.style.width = strength + '%';
            fill.style.backgroundColor = colors[idx];
            text.innerText = val.length > 0 ? 'Segurança: ' + labels[idx] : 'Segurança da senha';
        });
    </script>
</x-guest-layout>