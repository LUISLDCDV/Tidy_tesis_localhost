<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tidy</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Text:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Google Sans Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-weight: 400;
            height: 100vh;
            margin: 0;
        }
        .full-height {
            height: 100vh;
        }
        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .position-ref {
            position: relative;
        }
        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }
        .content {
            text-align: center;
        }
        .title {
            font-size: 84px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 600;
            color: #636b6f;
            margin-bottom: 30px;
        }
        .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .brand-icon i {
            color: white;
            font-size: 28px;
        }
        .brand-text {
            color: #2d3748;
            font-family: 'Google Sans Text', sans-serif;
            font-weight: 700;
            font-size: 52px;
            letter-spacing: -1px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }
        .m-b-md {
            margin-bottom: 30px;
        }

        .logo-image {
            width: 120px; /* Ajusta este valor según necesites */
            height: auto; /* Mantiene la proporción */
            max-width: 100%;
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
            <div class="logo-container m-b-md">
                <div class="brand-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Tidy" class="logo-image">
                </div>
            </div>

            <div class="links">
                <a href="{{ route('tesis.docs') }}">Docs</a>
                <a href="https://tidy-personal.web.app/" target="_blank">App</a>
                <a href="{{ route('about.project') }}">Sobre el Proyecto</a>
                <a href="https://www.linkedin.com/in/luisduartecarvalhosa/" target="_blank">LinkedIn</a>
                <a href="https://github.com/LUISLDCDV" target="_blank">GitHub</a>
            </div>
        </div>
    </div>
</body>
</html>
