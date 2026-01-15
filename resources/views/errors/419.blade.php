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
