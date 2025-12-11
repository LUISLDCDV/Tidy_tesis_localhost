<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tidy - Panel de Administración</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Preload critical resources -->
    <!-- <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"> -->

    <!-- Critical inline CSS for immediate rendering -->
    <style>
        :root {
            --tidy-primary: rgb(16, 116, 25);
            --tidy-secondary: rgb(45, 158, 149);
            --tidy-accent: rgb(22, 94, 107);
            --tidy-dark: #1A1A2E;
            --tidy-light: #F8F9FA;
            --tidy-gradient: linear-gradient(135deg, rgb(16, 116, 25) 0%, rgb(45, 158, 149) 100%);
        }

        /* Minimal critical CSS to prevent layout shift */
        html, body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8eef3 100%);
            min-height: 100vh;
        }

        /* Show content immediately with basic styling */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Professional navbar styling */
        .navbar {
            background: var(--tidy-gradient) !important;
            box-shadow: 0 4px 12px rgba(13, 71, 161, 0.15);
            padding: 0.8rem 0;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
        }

        .navbar-text {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
        }

        .btn-outline-danger {
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        .btn-outline-danger:hover {
            background-color: rgba(220, 53, 69, 0.9);
            border-color: white;
            color: white;
        }

        /* Hide dropdowns by default */
        .dropdown-menu {
            display: none !important;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
        }
        .dropdown-menu.show {
            display: block !important;
        }

        /* Main content wrapper */
        main {
            padding: 2.5rem 0 !important;
        }

        /* Card improvements */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .card-header {
            background: var(--tidy-gradient);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }

        /* Button improvements */
        .btn-primary {
            background: var(--tidy-gradient);
            border: none;
            padding: 0.6rem 1.8rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 71, 161, 0.3);
        }

        /* Alert improvements */
        .alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        /* Table improvements */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--tidy-gradient);
            color: white;
            font-weight: 600;
            border: none;
        }

        /* Basic loading state */
        .loading-content {
            min-height: 100vh;
            opacity: 1;
            transition: opacity 0.2s ease;
        }

        /* Override Bootstrap primary colors with green */
        .text-primary {
            color: var(--tidy-primary) !important;
        }

        .bg-primary {
            background-color: var(--tidy-primary) !important;
        }

        .border-left-primary {
            border-left: 0.25rem solid var(--tidy-primary) !important;
        }

        .badge-primary {
            background-color: var(--tidy-primary) !important;
            color: white !important;
        }

        /* Ensure card headers are white text */
        .card-header h6,
        .card-header .font-weight-bold {
            color: white !important;
        }

        /* Tooltip for coming soon features */
        [data-coming-soon] {
            cursor: not-allowed;
            opacity: 0.6;
            position: relative;
        }

        [data-coming-soon]:hover::after {
            content: "Próximamente";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 5px;
        }

        [data-coming-soon]:hover::before {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.9);
            margin-bottom: -5px;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Tidy
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <span class="navbar-text me-3">
                                    <i class="fas fa-user"></i> {{ Auth::user()->name }}
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-danger btn-sm" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </main>

        <!-- Modal Próximamente Reutilizable -->
        <div class="modal fade" id="comingSoonModal" tabindex="-1" aria-labelledby="comingSoonModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header" style="background: var(--tidy-gradient); color: white;">
                        <h5 class="modal-title" id="comingSoonModalLabel">
                            <i class="fas fa-clock"></i> Próximamente
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-tools fa-4x text-muted"></i>
                        </div>
                        <h4 class="mb-3">Funcionalidad en desarrollo</h4>
                        <p class="text-muted mb-0">
                            Esta característica estará disponible próximamente.
                            Estamos trabajando para ofrecerte la mejor experiencia.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                            <i class="fas fa-check"></i> Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript - cargado al final -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Simple initialization script -->
    <script>
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Simple initialization - no layout forcing
            document.body.classList.add('loaded');
        });

        // Función global para mostrar el modal de próximamente
        function showComingSoonModal() {
            const modal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
            modal.show();
        }
    </script>
</body>
</html>
