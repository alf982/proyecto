<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión · SIA Contraloría</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --accent:  #4f8ef7;
            --accent2: #7c5cfc;
            --accent3: #22d3a6;
            --bg:      #0a0c14;
            --card:    #111520;
            --border:  rgba(255,255,255,0.08);
            --text:    #eef0f6;
            --muted:   #8a91a8;
        }
        html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); }

        /* ─── BG effect ─── */
        .bg-glow {
            position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 0;
        }
        .bg-glow::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79,142,247,0.12) 0%, transparent 70%);
            top: -200px; left: -150px;
        }
        .bg-glow::after {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,92,252,0.10) 0%, transparent 70%);
            bottom: -100px; right: -100px;
        }

        /* ─── Layout ─── */
        .login-wrap {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 480px;
            position: relative;
            z-index: 1;
        }

        /* ─── Left panel ─── */
        .login-left {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            background: rgba(255,255,255,0.01);
            border-right: 1px solid var(--border);
        }
        .org-logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 16px;
            display: grid; place-items: center;
            font-size: 28px; color: #fff;
            box-shadow: 0 0 40px rgba(79,142,247,0.3);
            margin-bottom: 32px;
        }
        .org-name {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 12px;
        }
        .org-name span { color: var(--accent); }
        .org-sub {
            font-size: 15px;
            color: var(--muted);
            line-height: 1.6;
            max-width: 420px;
            margin-bottom: 48px;
        }
        .feature-list { display: flex; flex-direction: column; gap: 16px; }
        .feature-item {
            display: flex; align-items: center; gap: 14px;
        }
        .feature-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: grid; place-items: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .feature-icon.blue   { background: rgba(79,142,247,0.12); color: var(--accent); }
        .feature-icon.purple { background: rgba(124,92,252,0.12); color: var(--accent2); }
        .feature-icon.green  { background: rgba(34,211,166,0.12); color: var(--accent3); }
        .feature-text { font-size: 13.5px; color: var(--muted); }
        .feature-text strong { color: var(--text); font-weight: 600; }

        /* ─── Right panel (form) ─── */
        .login-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 48px;
        }
        .login-title { font-size: 24px; font-weight: 700; margin-bottom: 6px; }
        .login-subtitle { font-size: 13px; color: var(--muted); margin-bottom: 36px; }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 8px; letter-spacing: .4px; text-transform: uppercase; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; transition: color .2s; }
        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            color: var(--text);
            font-family: inherit;
            transition: border-color .2s, background .2s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); background: rgba(79,142,247,0.05); }
        .form-control:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: var(--accent); }
        .form-control::placeholder { color: rgba(138,145,168,0.6); }

        .form-error { font-size: 12px; color: #f75f5f; margin-top: 6px; display: flex; align-items: center; gap: 5px; }

        .form-options { display: flex; align-items: center; justify-content: space-between; margin: 6px 0 24px; }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); cursor: pointer; }
        .checkbox-wrap input[type="checkbox"] { accent-color: var(--accent); width: 15px; height: 15px; cursor: pointer; }

        .btn-login {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(79,142,247,0.35);
            transition: all .25s ease;
            letter-spacing: .3px;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(79,142,247,0.45); }
        .btn-login:active { transform: translateY(0); }

        .login-footer { margin-top: 28px; text-align: center; font-size: 12px; color: var(--muted); }

        /* Error global */
        .alert-error {
            background: rgba(247,95,95,0.08);
            border: 1px solid rgba(247,95,95,0.25);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #f75f5f;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 9px;
        }

        @keyframes fadeUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp .5s ease both; }

        @media (max-width: 800px) {
            .login-wrap { grid-template-columns: 1fr; }
            .login-left { display: none; }
            .login-right { padding: 40px 28px; }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>

<div class="login-wrap">

    <!-- Left panel -->
    <div class="login-left fade-up">
        <div class="org-logo"><i class="fa-solid fa-layer-group"></i></div>
        <div class="org-name">Sistema <span>Integrado</span><br>de Administración</div>
        <p class="org-sub">
            Contraloría del Estado Portuguesa — Plataforma oficial de gestión presupuestaria, financiera y administrativa institucional.
        </p>
        <div class="feature-list">
            <div class="feature-item">
                <div class="feature-icon blue"><i class="fa-solid fa-chart-pie"></i></div>
                <div class="feature-text"><strong>Control Presupuestario</strong><br>Compromiso, causado y pago integrado</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon purple"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="feature-text"><strong>Roles y Auditoría</strong><br>Acceso granular e historial inmutable</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div class="feature-text"><strong>Reportes en Tiempo Real</strong><br>Datos actualizados para la toma de decisiones</div>
            </div>
        </div>
    </div>

    <!-- Right panel -->
    <div class="login-right fade-up" style="animation-delay:.1s">
        <div class="login-title">Iniciar Sesión</div>
        <div class="login-subtitle">Ingresa tus credenciales institucionales</div>

        @if ($errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="usuario@contraloria.gob.ve"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    <i class="fa-solid fa-envelope input-icon"></i>
                </div>
                @error('email')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <i class="fa-solid fa-lock input-icon"></i>
                </div>
                @error('password')
                    <div class="form-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-options">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    Recordar sesión
                </label>
            </div>

            <button type="submit" class="btn-login" id="btn-login">
                <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right:8px"></i>
                Ingresar al Sistema
            </button>
        </form>

        <div class="login-footer">
            SIA · Contraloría del Estado Portuguesa &copy; {{ date('Y') }}<br>
            <span style="opacity:.5">v1.0.0 · Uso exclusivo institucional</span>
        </div>
    </div>

</div>

<script>
document.getElementById('login-form').addEventListener('submit', function() {
    const btn = document.getElementById('btn-login');
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" style="margin-right:8px"></i> Verificando...';
    btn.disabled = true;
});
</script>

</body>
</html>
