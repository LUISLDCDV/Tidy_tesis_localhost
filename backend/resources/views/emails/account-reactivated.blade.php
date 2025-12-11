<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Cuenta Reactivada!</title>
    <style>
        body {
            font-family: 'Google Sans Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 500;
        }
        .content {
            padding: 30px;
        }
        .success-icon {
            text-align: center;
            font-size: 64px;
            margin: 20px 0;
        }
        .welcome-back {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 20px auto;
            text-align: center;
            font-weight: 500;
        }
        .features-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .features-list ul {
            margin: 0;
            padding-left: 20px;
        }
        .features-list li {
            margin: 8px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ¡Bienvenido de vuelta!</h1>
        </div>

        <div class="content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <p>¡Hola {{ $user->name }}!</p>

            <div class="welcome-back">
                <h3 style="margin-top: 0; color: #155724;">🔓 Tu cuenta ha sido reactivada</h3>
                <p style="margin-bottom: 0;">Ya puedes volver a acceder a todas las funcionalidades de Tidy.</p>
            </div>

            <p>Nos alegra tenerte de vuelta. Tu cuenta está completamente funcional y puedes continuar donde lo dejaste.</p>

            <div class="features-list">
                <h3 style="margin-top: 0; color: #2c3e50;"><i class="fas fa-rocket"></i> ¿Qué puedes hacer ahora?</h3>
                <ul>
                    <li>✨ Crear y gestionar tus elementos</li>
                    <li><i class="fas fa-chart-bar"></i> Revisar tus estadísticas y progreso</li>
                    <li><i class="fas fa-trophy"></i> Continuar ganando experiencia y logros</li>
                    <li><i class="fas fa-cog"></i> Configurar tu perfil y preferencias</li>
                    <li><i class="fas fa-mobile-alt"></i> Sincronizar en todos tus dispositivos</li>
                </ul>
            </div>

            <div style="text-align: center;">
                <a href="https://tidy-t.web.app" class="cta-button">
                    <i class="fas fa-bullseye"></i> Acceder a Tidy
                </a>
            </div>

            <p style="color: #6c757d; font-size: 14px; text-align: center;">
                <em>Gracias por tu paciencia. ¡Esperamos que disfrutes usando Tidy!</em>
            </p>
        </div>

        <div class="footer">
            <p>Has recibido este email porque tu cuenta en <strong>Tidy</strong> ha sido reactivada.</p>
            <p>
                <a href="https://tidy-t.web.app" style="color: #28a745;">Visitar Tidy</a> |
                <a href="mailto:soporte@tidy.com" style="color: #28a745;">Soporte</a>
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Tidy App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>