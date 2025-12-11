<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notificación de Tidy' }}</title>
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
            @if(isset($type))
                @if($type === 'success')
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                @elseif($type === 'warning')
                    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
                @elseif($type === 'error')
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                @else
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                @endif
            @else
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            @endif
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
        .notification-icon {
            text-align: center;
            font-size: 64px;
            margin: 20px 0;
        }
        .message-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .admin-signature {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .cta-button {
            @if(isset($type))
                @if($type === 'success')
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                @elseif($type === 'warning')
                    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
                    color: #212529;
                @elseif($type === 'error')
                    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                @else
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                @endif
            @else
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            @endif
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
            <h1>
                @if(isset($type))
                    @if($type === 'success')
                        <i class="fas fa-check-circle"></i> {{ $title ?? 'Notificación Exitosa' }}
                    @elseif($type === 'warning')
                        ⚠️ {{ $title ?? 'Notificación Importante' }}
                    @elseif($type === 'error')
                        ❌ {{ $title ?? 'Notificación de Error' }}
                    @else
                        📢 {{ $title ?? 'Notificación' }}
                    @endif
                @else
                    📢 {{ $title ?? 'Notificación de Tidy' }}
                @endif
            </h1>
        </div>

        <div class="content">
            <div class="notification-icon">
                @if(isset($type))
                    @if($type === 'success')
                        🎉
                    @elseif($type === 'warning')
                        ⚠️
                    @elseif($type === 'error')
                        🚨
                    @else
                        💌
                    @endif
                @else
                    💌
                @endif
            </div>

            <p>Hola {{ $user->name }},</p>

            <div class="message-content">
                {!! nl2br(e($message)) !!}
            </div>

            @if(isset($admin) && $admin)
            <div class="admin-signature">
                <p style="margin: 0;"><strong>👤 Enviado por:</strong> {{ $admin->name }}</p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Administrador de Tidy</p>
            </div>
            @endif

            @if(isset($action_url) && $action_url)
            <div style="text-align: center;">
                <a href="{{ $action_url }}" class="cta-button">
                    {{ $action_text ?? '<i class="fas fa-rocket"></i> Ver en Tidy' }}
                </a>
            </div>
            @endif

            <p style="color: #6c757d; font-size: 14px; text-align: center;">
                <em>Esta notificación fue enviada el {{ now()->format('d/m/Y H:i') }}</em>
            </p>
        </div>

        <div class="footer">
            <p>Has recibido este email desde <strong>Tidy</strong>.</p>
            <p>
                <a href="https://tidy-t.web.app" style="color: #667eea;">Visitar Tidy</a> |
                <a href="mailto:soporte@tidy.com" style="color: #667eea;">Soporte</a>
            </p>
            <p style="margin-top: 15px;">© {{ date('Y') }} Tidy App. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>