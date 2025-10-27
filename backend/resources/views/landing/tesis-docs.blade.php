<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentación de Tesis - Tidy</title>

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
            background: linear-gradient(135deg,rgb(19, 94, 42) 0%,rgb(15, 82, 32) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
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
            background: linear-gradient(135deg,rgb(16, 94, 61) 0%,rgb(25, 83, 47) 100%);
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
        .progress-section {
            background: #f7fafc;
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
        }
        .progress-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }
        .progress-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
        }
        .progress-icon.completed {
            background: #48bb78;
            color: white;
        }
        .progress-icon.in-progress {
            background: #ed8936;
            color: white;
        }
        .progress-icon.pending {
            background:rgb(20, 65, 124);
            color:rgb(8, 35, 70);
        }
        .progress-content h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
        }
        .progress-content p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color:rgb(25, 94, 51);
        }
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .tech-item {
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            color:rgb(27, 83, 39);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 30px;
            transition: color 0.3s;
        }
        .back-link:hover {
            color:rgb(23, 83, 33);
        }
        .back-link i {
            margin-right: 8px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat {
            text-align: center;
            background: #f7fafc;
            padding: 20px;
            border-radius: 10px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color:rgb(19, 100, 53);
            display: block;
        }
        .stat-label {
            font-size: 14px;
            color: #718096;
            margin-top: 5px;
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
            <h1 class="title">Documentación de Tesis</h1>

            <p class="text">
                Esta documentación presenta el progreso y desarrollo del proyecto Tidy como trabajo de tesis de grado. Aquí encontrarás información detallada sobre la metodología, tecnologías implementadas y el estado actual del desarrollo.
            </p>

            <h2 class="subtitle">📊 Estadísticas del Proyecto</h2>
            <div class="stats">
                <div class="stat">
                    <span class="stat-number">6</span>
                    <div class="stat-label">Meses de Desarrollo</div>
                </div>
                <div class="stat">
                    <span class="stat-number">2</span>
                    <div class="stat-label">Aplicaciones</div>
                </div>
                <div class="stat">
                    <span class="stat-number">15+</span>
                    <div class="stat-label">Funcionalidades</div>
                </div>
                <div class="stat">
                    <span class="stat-number">100%</span>
                    <div class="stat-label">Responsive</div>
                </div>
            </div>

            <h2 class="subtitle">🔄 Progreso de Desarrollo</h2>
            <div class="progress-section">
                <div class="progress-item">
                    <div class="progress-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Investigación y Análisis</h4>
                        <p>Estudio de mercado, análisis de competencia y definición de requisitos</p>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Diseño de Arquitectura</h4>
                        <p>Diseño de base de datos, API REST y arquitectura del sistema</p>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Desarrollo Backend</h4>
                        <p>API Laravel con autenticación, CRUD completo y dashboard administrativo</p>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Desarrollo Frontend</h4>
                        <p>Aplicación Vue.js con Quasar Framework, responsive y PWA</p>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-icon in-progress">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Testing y Optimización</h4>
                        <p>Pruebas de funcionalidad, optimización de rendimiento y UX</p>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="progress-content">
                        <h4>Documentación Final</h4>
                        <p>Redacción de memoria de tesis y preparación de presentación</p>
                    </div>
                </div>
            </div>

            <h2 class="subtitle">🛠️ Stack Tecnológico</h2>
            <div class="tech-grid">
                <div class="tech-item">
                    <h4>Frontend</h4>
                    <p>Vue.js 3, Quasar Framework, Pinia, Vue Router</p>
                </div>
                <div class="tech-item">
                    <h4>Backend</h4>
                    <p>Laravel 10, PHP 8.1, Laravel Sanctum</p>
                </div>
                <div class="tech-item">
                    <h4>Base de Datos</h4>
                    <p>MySQL, Migraciones, Seeders</p>
                </div>
                <div class="tech-item">
                    <h4>Despliegue</h4>
                    <p>Railway, Firebase Hosting</p>
                </div>
            </div>

            <h2 class="subtitle">📋 Metodología</h2>
            <p class="text">
                El desarrollo del proyecto sigue una metodología ágil adaptada para el contexto académico:
            </p>
            <ul class="text">
                <li><strong>Análisis de Requisitos:</strong> Definición clara de funcionalidades y objetivos</li>
                <li><strong>Diseño Iterativo:</strong> Prototipado y mejora continua de la interfaz</li>
                <li><strong>Desarrollo Incremental:</strong> Implementación por módulos funcionales</li>
                <li><strong>Testing Continuo:</strong> Pruebas en cada iteración de desarrollo</li>
                <li><strong>Documentación Paralela:</strong> Registro detallado del proceso y decisiones</li>
            </ul>

            <h2 class="subtitle">🎯 Objetivos Académicos</h2>
            <p class="text">
                Este proyecto de tesis tiene como objetivos principales:
            </p>
            <ul class="text">
                <li>Demostrar competencias en desarrollo full-stack moderno</li>
                <li>Aplicar principios de ingeniería de software en un proyecto real</li>
                <li>Implementar mejores prácticas de UX/UI y accesibilidad</li>
                <li>Desarrollar una solución escalable y mantenible</li>
                <li>Generar conocimiento aplicable en el contexto profesional</li>
            </ul>

            <h2 class="subtitle">📈 Resultados Esperados</h2>
            <p class="text">
                Al finalizar este proyecto de tesis, se espera haber desarrollado:
            </p>
            <ul class="text">
                <li>Una aplicación web completa y funcional</li>
                <li>Documentación técnica detallada</li>
                <li>Análisis de rendimiento y usabilidad</li>
                <li>Propuestas de mejora y escalabilidad</li>
                <li>Contribución al conocimiento en desarrollo web</li>
            </ul>
        </div>
    </div>
</body>
</html>