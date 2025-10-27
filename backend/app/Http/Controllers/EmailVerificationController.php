<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\GamificationService;
use App\Models\EmailVerificationToken;
use App\Models\User;

class EmailVerificationController extends Controller
{
    /**
     * Enviar notificación de verificación de email
     */
    public function send(Request $request)
    {
        try {
            $user = $request->user();

            Log::info("📧 Iniciando envío de email de verificación", [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_default' => config('mail.default'),
                'maileroo_api_key_set' => !empty(config('mail.mailers.maileroo.api_key')),
                'mail_from' => config('mail.from.address')
            ]);

            if ($user->email_verified_at) {
                Log::info("📧 Usuario ya tiene email verificado", ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Email ya verificado',
                    'verified' => true
                ]);
            }

            // Crear token de verificación
            $verificationToken = EmailVerificationToken::createToken($user->id, 24);

            Log::info("🔑 Token de verificación creado", [
                'user_id' => $user->id,
                'token_length' => strlen($verificationToken->token),
                'expires_at' => $verificationToken->expires_at
            ]);

            // Generar URL de verificación
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:9000');
            $verificationUrl = $frontendUrl . '/verify-email?token=' . $verificationToken->token;

            Log::info("🔗 URL de verificación generada", [
                'url' => $verificationUrl
            ]);

            // Enviar email
            Log::info("📬 Llamando Mail::send()...");

            Mail::send('emails.verify-email', [
                'userName' => $user->name,
                'verificationUrl' => $verificationUrl,
            ], function ($mail) use ($user) {
                Log::info("📝 Configurando mensaje de email", [
                    'to' => $user->email,
                    'from' => config('mail.from.address')
                ]);

                $mail->to($user->email)
                     ->subject('Verificar tu email - Tidy')
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("✅ Mail::send() completado sin excepciones");

            Log::info("📧 Email de verificación enviado exitosamente", [
                'user_id' => $user->id,
                'email' => $user->email,
                'expires_at' => $verificationToken->expires_at
            ]);

            return response()->json([
                'message' => 'Email de verificación enviado',
                'sent' => true
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error enviando email de verificación", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al enviar email de verificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar email a través del enlace
     */
    public function verify(EmailVerificationRequest $request)
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                Log::info("📧 Usuario ya tenía email verificado en verify", ['user_id' => $user->id]);
                return redirect('/home?verified=already');
            }

            if ($request->fulfill()) {
                event(new Verified($request->user()));

                // Otorgar logro de verificación de email
                try {
                    $gamificationService = new GamificationService();
                    $gamificationService->checkEmailVerificationAchievement($user);
                } catch (\Exception $e) {
                    Log::warning("No se pudo otorgar logro de verificación", [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::info("✅ Email verificado exitosamente", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'verified_at' => now()
                ]);

                return redirect('/home?verified=success');
            }

            Log::warning("⚠️ Falló la verificación de email", ['user_id' => $user->id]);
            return redirect('/home?verified=failed');

        } catch (\Exception $e) {
            Log::error("❌ Error verificando email", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect('/home?verified=error');
        }
    }

    /**
     * Mostrar aviso de verificación de email
     */
    public function notice()
    {
        $user = Auth::user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect('/home');
        }

        return view('auth.verify');
    }

    /**
     * API: Verificar estado de email del usuario
     */
    public function status(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'verified' => false,
                    'authenticated' => false
                ], 401);
            }

            Log::info("📧 Consultando estado de verificación", [
                'user_id' => $user->id,
                'email_verified' => (bool)$user->email_verified_at
            ]);

            return response()->json([
                'verified' => (bool)$user->email_verified_at,
                'authenticated' => true,
                'email' => $user->email
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error consultando estado de verificación", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al consultar estado de verificación'
            ], 500);
        }
    }

    /**
     * API: Verificar email usando token desde el frontend
     */
    public function verifyToken(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required|string|size:64'
            ]);

            // Buscar token
            $verificationToken = EmailVerificationToken::where('token', $request->token)->first();

            if (!$verificationToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de verificación inválido'
                ], 404);
            }

            // Verificar si expiró
            if ($verificationToken->isExpired()) {
                $verificationToken->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'El token de verificación ha expirado. Por favor solicita uno nuevo.'
                ], 410);
            }

            // Obtener usuario
            $user = User::find($verificationToken->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar si ya estaba verificado
            if ($user->email_verified_at) {
                $verificationToken->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Email ya estaba verificado',
                    'already_verified' => true
                ]);
            }

            // Marcar email como verificado
            $user->email_verified_at = now();
            $user->save();

            // Eliminar token usado
            $verificationToken->delete();

            // Otorgar logro de verificación de email
            try {
                $gamificationService = new GamificationService();
                $gamificationService->checkEmailVerificationAchievement($user);
            } catch (\Exception $e) {
                Log::warning("No se pudo otorgar logro de verificación", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info("✅ Email verificado exitosamente con token", [
                'user_id' => $user->id,
                'email' => $user->email,
                'verified_at' => $user->email_verified_at
            ]);

            return response()->json([
                'success' => true,
                'message' => '¡Email verificado exitosamente!',
                'verified_at' => $user->email_verified_at
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ Error verificando token de email", [
                'token' => $request->token ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}