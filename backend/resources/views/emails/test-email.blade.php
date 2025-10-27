<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email de Prueba - Tidy</title>
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
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
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
        .test-icon {
            text-align: center;
            font-size: 64px;
            margin: 20px 0;
        }
        .test-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .test-details {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .test-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .test-details td {
            padding: 8px 0;
            border-bottom: 1px solid #cce7ff;
        }
        .test-details td:first-child {
            font-weight: 500;
            width: 30%;
        }
        .test-details tr:last-child td {
            border-bottom: none;
        }
        .success-badge {
            background: #d4edda;
            color: #155724;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
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
            <h1>🧪 Email de Prueba</h1>
        </div>

        <div class="content">
            <div class="test-icon">
                ⚗️
            </div>

            <p>Hola {{ $user->name }},</p>

            <div class="test-info">
                <h3 style="margin-top: 0; color: #2c3e50;">📧 Sistema de Email Funcionando</h3>
                <p>Este es un email de prueba para verificar que el sistema de notificaciones por correo electrónico de <strong>Tidy</strong> está funcionando correctamente.</p>
                <div class="success-badge">✅ Test Exitoso</div>
            </div>

            <div class="test-details">
                <h4 style="margin-top: 0; color: #007bff;">📊 Detalles de la Prueba</h4>
                <table>
                    <tr>
                        <td><strong>Usuario de Prueba:</strong></td>
                        <td>{{ $user->name }} ({{ $user->email }})</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha y Hora:</strong></td>
                        <td>{{ now()->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Servidor:</strong></td>
                        <td>{{ config('app.name') }} ({{ config('app.env') }})</td>
                    </tr>
                    <tr>
                        <td><strong>Configuración SMTP:</strong></td>
                        <td>{{ config('mail.default') }}</td>
                    </tr>
                    @if(isset($admin) && $admin)
                    <tr>
                        <td><strong>Enviado por:</strong></td>
                        <td>{{ $admin->name }} (Administrador)</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p>Si has recibido este email, significa que:</p>
            <ul>
                <li>✅ La configuración de email está funcionando correctamente</li>
                <li>✅ El servidor puede enviar notificaciones</li>
                <li>✅ Tu dirección de email está configurada apropiadamente</li>
                <li>✅ Los templates de email se renderizan correctamente</li>
            </ul>

            <p style="color: #6c757d; font-size: 14px; text-align: center;">
                <em>Este es un mensaje automático de prueba. No requiere respuesta.</em>
            </p>
        </div>

        <div class="footer">
            <p>Email de prueba enviado desde <strong>Tidy</strong>.</p>
            <p>
                <a href="https://tidy-t.web.app" style="color: #6c757d;">Visitar Tidy</a> |
                <a href="mailto:soporte@tidy.com" style="color: #6c757d;">Soporte</a>
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Tidy App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>