<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sobre el Proyecto - Tidy</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Styles -->
    <style>
        html, body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 60px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
        .brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 4px 15px rgba(16, 116, 25, 0.4);
        }
        .brand-icon i {
            color: white;
            font-size: 24px;
        }
        .brand-text {
            color: #2d3748;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 42px;
            letter-spacing: -1px;
            background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 50px;
            margin-bottom: 40px;
        }
        .title {
            font-size: 42px;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 30px;
        }
        .subtitle {
            font-size: 24px;
            font-weight: 600;
            color: #4a5568;
            margin: 40px 0 20px 0;
        }
        .text {
            font-size: 18px;
            line-height: 1.8;
            color: #718096;
            margin-bottom: 25px;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .feature {
            background: #f7fafc;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg,rgb(16, 116, 25) 0%,rgb(45, 158, 149) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
        }
        .feature-icon i {
            color: white;
            font-size: 24px;
        }
        .feature h3 {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
        }
        .feature p {
            font-size: 16px;
            color: #718096;
            line-height: 1.6;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            color:rgb(24, 92, 54);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 30px;
            transition: color 0.3s;
        }
        .back-link:hover {
            color:rgb(35, 77, 22);
        }
        .back-link i {
            margin-right: 8px;
        }


        .logo-image {
            width: 120px; /* Ajusta este valor según necesites */
            height: auto; /* Mantiene la proporción */
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ url('/') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>

        <div class="content">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Tidy" class="logo-image">
                <p class="text-muted mt-2">Organiza tu vida digital</p>
            </div>
            <h1 class="title">Sobre el Proyecto Tidy</h1>

            <p class="text">
                Tidy es una aplicación web innovadora diseñada para revolucionar la forma en que organizamos nuestras tareas diarias y gestionamos nuestra productividad personal. Este proyecto representa la culminación de un trabajo de tesis enfocado en crear soluciones tecnológicas que mejoren la calidad de vida de las personas.
            </p>

            <h2 class="subtitle"><i class="fas fa-bullseye"></i> Misión del Proyecto</h2>
            <p class="text">
                Desarrollar una plataforma integral que combine la simplicidad de uso con funcionalidades avanzadas, permitiendo a los usuarios organizar sus tareas, establecer objetivos y mantener un seguimiento efectivo de su progreso personal y profesional.
            </p>

            <h2 class="subtitle">✨ Características Principales</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Gestión de Tareas</h3>
                    <p>Sistema intuitivo para crear, organizar y priorizar tareas con diferentes niveles de importancia.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Alarmas Personalizadas</h3>
                    <p>Notificaciones personalizables para recordatorios que se adaptan a tus necesidades.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <h3>Notas Avanzadas</h3>
                    <p>Editor de texto enriquecido para capturar ideas y documentar procesos importantes.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Objetivos</h3>
                    <p>Sistema de metas con seguimiento de progreso y logros para mantener la motivación.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Analíticas</h3>
                    <p>Reportes detallados sobre productividad y patrones de comportamiento.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Multiplataforma</h3>
                    <p>Disponible en web y móvil con sincronización en tiempo real entre dispositivos.</p>
                </div>
            </div>

            <h2 class="subtitle"><i class="fas fa-rocket"></i> Tecnologías Utilizadas</h2>
            <p class="text">
                El proyecto está construido utilizando tecnologías modernas y robustas:
            </p>
            <ul class="text">
                <li><strong>Frontend:</strong> Vue.js 3 con Quasar Framework para una experiencia de usuario fluida</li>
                <li><strong>Backend:</strong> Laravel 10 para una API RESTful sólida y escalable</li>
                <li><strong>Base de Datos:</strong> MySQL con migraciones y seeders para gestión de datos</li>
                <li><strong>Autenticación:</strong> Laravel Sanctum para seguridad robusta</li>
                <li><strong>Despliegue:</strong> Railway para backend y Firebase para frontend</li>
            </ul>

            <h2 class="subtitle">🎓 Contexto Académico</h2>
            <p class="text">
                Este proyecto forma parte de una tesis de grado enfocada en el desarrollo de aplicaciones web modernas que resuelvan problemas reales de productividad. La investigación incluye análisis de UX/UI, arquitectura de software, y mejores prácticas de desarrollo.
            </p>

            <h2 class="subtitle">👨‍💻 Desarrollo</h2>
            <p class="text">
                Desarrollado por Luis Duarte Carvalhosa como parte de su trabajo de tesis. El proyecto representa meses de investigación, diseño, desarrollo y testing para crear una solución completa y funcional.
            </p>
        </div>
    </div>
</body>
</html>