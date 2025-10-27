<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
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
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .config-info {
            background-color: #f8f9fc;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .config-info h4 {
            margin-top: 0;
            color: #667eea;
        }
        .config-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .config-table td {
            padding: 8px;
            border-bottom: 1px solid #e3e6f0;
        }
        .config-table td:first-child {
            font-weight: 600;
            color: #5a5c69;
            width: 40%;
        }
        .config-table td:last-child {
            font-family: 'Courier New', monospace;
            background-color: #f8f9fa;
            color: #06d6a0;
            padding: 4px 8px;
            border-radius: 3px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #e3e6f0;
            padding-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        .success-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">🧹 Tidy</div>
            <h2>{{ $emailSubject }}</h2>
        </div>

        <div class="content">
            <p>¡Hola!</p>

            <p>{{ $messageContent }}</p>

            <div class="config-info">
                <h4>📧 Configuración de Email Actual</h4>
                <table class="config-table">
                    <tr>
                        <td>Proveedor SMTP:</td>
                        <td>Maileroo <span class="success-badge">✓ Funcionando</span></td>
                    </tr>
                    <tr>
                        <td>Host:</td>
                        <td>{{ config('mail.mailers.smtp.host') }}</td>
                    </tr>
                    <tr>
                        <td>Puerto:</td>
                        <td>{{ config('mail.mailers.smtp.port') }}</td>
                    </tr>
                    <tr>
                        <td>Encriptación:</td>
                        <td>{{ strtoupper(config('mail.mailers.smtp.encryption')) }}</td>
                    </tr>
                    <tr>
                        <td>Enviado desde:</td>
                        <td>{{ config('mail.from.address') }}</td>
                    </tr>
                    <tr>
                        <td>Timestamp:</td>
                        <td>{{ now()->format('d/m/Y H:i:s T') }}</td>
                    </tr>
                </table>
            </div>

            <p>Si has recibido este email, significa que la configuración de Maileroo SMTP está funcionando correctamente y el sistema está listo para enviar notificaciones a los usuarios.</p>

            <p><strong>Próximos pasos:</strong></p>
            <ul>
                <li>✅ Configuración SMTP verificada</li>
                <li>✅ Conectividad con Maileroo establecida</li>
                <li>📧 Sistema listo para notificaciones de usuario</li>
                <li>🔔 Sistema listo para emails de administración</li>
            </ul>
        </div>

        <div class="footer">
            <p>
                <strong>Tidy Admin System</strong><br>
                Sistema de gestión administrativa<br>
                <em>Email generado automáticamente - No responder</em>
            </p>
        </div>
    </div>
</body>
</html>