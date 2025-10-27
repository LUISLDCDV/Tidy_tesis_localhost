@extends('layouts.app')

@section('title', 'Test Email - Maileroo')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-envelope"></i> Test Email - Maileroo SMTP
                </h1>
                <div>
                    <a href="{{ route('admin.dashboard.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Configuración actual -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog"></i> Configuración Actual de Email
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Mailer:</th>
                                    <td><code>{{ config('mail.default') }}</code></td>
                                </tr>
                                <tr>
                                    <th>Host:</th>
                                    <td><code>{{ config('mail.mailers.smtp.host') }}</code></td>
                                </tr>
                                <tr>
                                    <th>Port:</th>
                                    <td><code>{{ config('mail.mailers.smtp.port') }}</code></td>
                                </tr>
                                <tr>
                                    <th>Encryption:</th>
                                    <td><code>{{ config('mail.mailers.smtp.encryption') }}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Username:</th>
                                    <td>
                                        @if(config('mail.mailers.smtp.username'))
                                            <span class="badge bg-success">Configurado</span>
                                        @else
                                            <span class="badge bg-danger">No configurado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Password:</th>
                                    <td>
                                        @if(config('mail.mailers.smtp.password'))
                                            <span class="badge bg-success">Configurado</span>
                                        @else
                                            <span class="badge bg-danger">No configurado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>From Address:</th>
                                    <td><code>{{ config('mail.from.address') }}</code></td>
                                </tr>
                                <tr>
                                    <th>From Name:</th>
                                    <td><code>{{ config('mail.from.name') }}</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de test -->
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-paper-plane"></i> Enviar Email de Prueba
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.test-email.send') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Destinatario</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           placeholder="ejemplo@dominio.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Asunto</label>
                                    <input type="text"
                                           class="form-control @error('subject') is-invalid @enderror"
                                           id="subject"
                                           name="subject"
                                           value="{{ old('subject', 'Test Email - Maileroo SMTP') }}"
                                           required
                                           maxlength="255">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Mensaje</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message"
                                      name="message"
                                      rows="6"
                                      required
                                      maxlength="2000"
                                      placeholder="Escribe aquí el mensaje de prueba...">{{ old('message', 'Este es un email de prueba enviado desde Tidy usando Maileroo SMTP.

Si recibes este mensaje, la configuración está funcionando correctamente.

Timestamp: ' . now() . '

Saludos,
Tidy Admin System') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Máximo 2000 caracteres</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Enviar Email de Prueba
                            </button>
                            <small class="text-muted align-self-center">
                                <i class="fas fa-info-circle"></i>
                                El email será enviado usando la configuración de Maileroo
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Instrucciones -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle"></i> Configuración de Variables de Entorno
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Para configurar Maileroo SMTP, agrega estas variables a tu archivo <code>.env</code>:</p>
                    <div class="bg-dark text-light p-3 rounded">
                        <code>
                            MAIL_MAILER=smtp<br>
                            MAIL_HOST=smtp.maileroo.com<br>
                            MAIL_PORT=587<br>
                            MAIL_USERNAME=tu_usuario_maileroo<br>
                            MAIL_PASSWORD=tu_password_maileroo<br>
                            MAIL_ENCRYPTION=tls<br>
                            MAIL_FROM_ADDRESS="noreply@tudominio.com"<br>
                            MAIL_FROM_NAME="Tidy"
                        </code>
                    </div>
                    <div class="mt-3">
                        <strong>Puertos alternativos:</strong>
                        <ul class="mb-0">
                            <li><strong>465 (SSL):</strong> MAIL_PORT=465, MAIL_ENCRYPTION=ssl</li>
                            <li><strong>587 (TLS):</strong> MAIL_PORT=587, MAIL_ENCRYPTION=tls</li>
                            <li><strong>2525 (TLS):</strong> MAIL_PORT=2525, MAIL_ENCRYPTION=tls</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
    width: 30%;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

code {
    color: #06d6a0;
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
@endsection