<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tidy - Sistema de Gestión Personal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Styles -->
    <style>
        :root {
            --tidy-primary: rgb(16, 116, 25);
            --tidy-secondary: rgb(45, 158, 149);
            --tidy-accent: rgb(76, 175, 80);
            --tidy-gradient: linear-gradient(135deg, rgb(16, 116, 25) 0%, rgb(45, 158, 149) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef3 100%);
            color: #2c3e50;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-weight: 400;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .position-ref {
            position: relative;
            width: 100%;
        }

        .top-right {
            position: absolute;
            right: 40px;
            top: 40px;
            z-index: 10;
        }

        .top-right a {
            background: var(--tidy-gradient);
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16, 116, 25, 0.2);
        }

        .top-right a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 116, 25, 0.3);
        }

        .content {
            text-align: center;
            max-width: 900px;
            padding: 40px 20px;
        }

        .logo-container {
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease-out;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .brand-logo:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
        }

        .logo-image {
            width: 140px;
            height: auto;
            max-width: 100%;
            transition: transform 0.3s ease;
        }

        .logo-image:hover {
            transform: scale(1.05);
        }

        .tagline {
            font-size: 28px;
            font-weight: 700;
            color: #1A1A2E;
            margin-bottom: 15px;
            line-height: 1.4;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .description {
            font-size: 17px;
            color: #2c3e50;
            margin-bottom: 40px;
            line-height: 1.7;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
        }

        .links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
        }

        .links > a {
            background: white;
            color: var(--tidy-primary);
            padding: 14px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 2px solid transparent;
        }

        .links > a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(16, 116, 25, 0.15);
            border-color: var(--tidy-accent);
            color: var(--tidy-secondary);
        }

        .links > a i {
            margin-right: 8px;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .top-right {
                right: 20px;
                top: 20px;
            }

            .top-right a {
                padding: 10px 20px;
                font-size: 13px;
            }

            .tagline {
                font-size: 22px;
            }

            .description {
                font-size: 14px;
            }

            .links > a {
                padding: 12px 24px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
            <div class="top-right links">
                @auth
                    <a href="{{ url('/home') }}">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Iniciar Sesión</a>
                @endauth
            </div>
        @endif

        <div class="content">
            <div class="logo-container">
                <div class="brand-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tidy" class="logo-image">
                </div>
                <h1 class="tagline">Sistema de Gestión Personal Inteligente</h1>
                <p class="description">
                    Organiza tu vida con Tidy: gestiona calendarios, eventos, objetivos, alarmas y mucho más.
                    Una solución completa para mantener tu día a día ordenado y productivo.
                </p>
            </div>

            <div class="links">
                <a href="https://tidy-personal.web.app/" target="_blank">
                    <i class="fas fa-mobile-alt"></i> Aplicación Web
                </a>
                <a href="{{ route('about.project') }}">
                    <i class="fas fa-info-circle"></i> Acerca del Proyecto
                </a>
                <a href="https://www.linkedin.com/in/luisduartecarvalhosa/" target="_blank">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
                <a href="https://github.com/LUISLDCDV/Tidy_tesis_localhost" target="_blank">
                    <i class="fab fa-github"></i> GitHub
                </a>
            </div>
        </div>
    </div>
</body>
</html>
