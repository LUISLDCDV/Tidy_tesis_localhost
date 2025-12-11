<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Desactivada</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
        .alert-icon {
            text-align: center;
            font-size: 64px;
            margin: 20px 0;
        }
        .reason-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .reason-box h3 {
            margin-top: 0;
            color: #856404;
        }
        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
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
            <h1>⚠️ Cuenta Desactivada</h1>
        </div>

        <div class="content">
            <div class="alert-icon">
                🚫
            </div>

            <p>Hola {{ $user->name }},</p>

            <p>Te informamos que tu cuenta en <strong>Tidy</strong> ha sido desactivada temporalmente.</p>

            @if(isset($reason) && $reason)
            <div class="reason-box">
                <h3><i class="fas fa-clipboard-list"></i> Motivo de la desactivación:</h3>
                <p>{{ $reason }}</p>
            </div>
            @endif

            <p>Durante este período, no podrás acceder a tu cuenta ni utilizar las funcionalidades de Tidy.</p>

            <div class="contact-info">
                <h3 style="margin-top: 0; color: #2c3e50;"><i class="fas fa-comments"></i> ¿Necesitas ayuda?</h3>
                <p>Si consideras que esta acción fue tomada por error o deseas obtener más información, no dudes en contactarnos.</p>
                <p>
                    <strong>Email de soporte:</strong>
                    <a href="mailto:soporte@tidy.com" style="color: #dc3545;">soporte@tidy.com</a>
                </p>
            </div>

            <p style="color: #6c757d; font-size: 14px; text-align: center;">
                <em>Agradecemos tu comprensión. Esperamos resolver esta situación pronto.</em>
            </p>
        </div>

        <div class="footer">
            <p>Has recibido este email porque tu cuenta en <strong>Tidy</strong> ha sido desactivada.</p>
            <p>
                <a href="https://tidy-t.web.app" style="color: #dc3545;">Visitar Tidy</a> |
                <a href="mailto:soporte@tidy.com" style="color: #dc3545;">Soporte</a>
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Tidy App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>