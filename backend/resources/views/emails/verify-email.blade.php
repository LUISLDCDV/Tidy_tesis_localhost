<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar tu email - Tidy</title>
    <style>
        body {
            font-family: 'Google Sans Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .app-name {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
        }
        .content p {
            color: #555;
            margin-bottom: 15px;
        }
        .verify-button {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 30px auto;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }
        .verify-button:hover {
            transform: translateY(-2px);
        }
        .alternative-link {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            word-wrap: break-word;
        }
        .alternative-link p {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }
        .alternative-link a {
            color: #667eea;
            word-break: break-all;
            font-size: 12px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #e3e6f0;
            padding-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        .expiry-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .expiry-notice p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🧹</div>
            <div class="app-name">Tidy</div>
        </div>

        <div class="content">
            <h2>¡Hola {{ $userName }}!</h2>

            <p>Gracias por registrarte en Tidy. Para completar tu registro y comenzar a usar tu cuenta, necesitamos verificar tu dirección de email.</p>

            <p>Haz clic en el botón a continuación para verificar tu email:</p>

            <a href="{{ $verificationUrl }}" class="verify-button">
                ✓ Verificar mi email
            </a>

            <div class="expiry-notice">
                <p><strong>⏰ Este link expira en 24 horas</strong></p>
            </div>

            <div class="alternative-link">
                <p>Si el botón no funciona, copia y pega este link en tu navegador:</p>
                <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
            </div>

            <p>Si no creaste esta cuenta, puedes ignorar este email de forma segura.</p>
        </div>

        <div class="footer">
            <p>
                <strong>Tidy</strong><br>
                Tu asistente personal de organización<br>
                <em>Este es un email automático - Por favor no responder</em>
            </p>
        </div>
    </div>
</body>
</html>
