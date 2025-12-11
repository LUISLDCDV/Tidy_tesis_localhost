<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Felicidades por tu nuevo nivel!</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 500;
        }
        .level-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 10px 20px;
            margin: 15px 0;
            font-size: 18px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .achievement-icon {
            text-align: center;
            font-size: 64px;
            margin: 20px 0;
        }
        .stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .cta-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 20px auto;
            text-align: center;
            font-weight: 500;
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
            <h1>🎉 ¡Felicidades, {{ $user->name }}!</h1>
            <div class="level-badge">
                Nivel {{ $level }} Alcanzado
            </div>
        </div>

        <div class="content">
            <div class="achievement-icon">
                <i class="fas fa-trophy"></i>
            </div>

            <p>¡Increíble! Has alcanzado el <strong>Nivel {{ $level }}</strong> en Tidy. Tu dedicación y constancia han dado frutos.</p>

            <div class="stats">
                <h3 style="margin-top: 0; color: #2c3e50;"><i class="fas fa-chart-bar"></i> Tus Estadísticas</h3>
                <div class="stat-item">
                    <span><strong>Nuevo Nivel:</strong></span>
                    <span style="color: #667eea; font-weight: 700;">{{ $level }}</span>
                </div>
                <div class="stat-item">
                    <span><strong>Experiencia Total:</strong></span>
                    <span>{{ number_format($user->userLevel->total_experience ?? 0) }} XP</span>
                </div>
                <div class="stat-item">
                    <span><strong>Fecha del Logro:</strong></span>
                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <p>Este logro te desbloquea nuevas funcionalidades y demuestra tu compromiso con la productividad. ¡Sigue así!</p>

            <div style="text-align: center;">
                <a href="https://tidy-t.web.app" class="cta-button">
                    <i class="fas fa-rocket"></i> Continuar en Tidy
                </a>
            </div>

            <p style="color: #6c757d; font-size: 14px; text-align: center;">
                <em>Recuerda: cada pequeño paso cuenta. Tu progreso es inspirador.</em>
            </p>
        </div>

        <div class="footer">
            <p>Has recibido este email porque alcanzaste un nuevo nivel en <strong>Tidy</strong>.</p>
            <p>
                <a href="https://tidy-t.web.app" style="color: #667eea;">Visitar Tidy</a> |
                <a href="mailto:soporte@tidy.com" style="color: #667eea;">Soporte</a>
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Tidy App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>