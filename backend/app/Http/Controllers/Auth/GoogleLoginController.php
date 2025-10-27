<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GoogleLoginController extends Controller
{
    /**
     * Login o registro con Firebase Google Auth
     */
    public function login(Request $request)
    {
        try {
            // Validar datos requeridos
            $validator = Validator::make($request->all(), [
                'firebase_uid' => 'required|string',
                'email' => 'required|email',
                'name' => 'required|string',
                'firebase_token' => 'required|string',
                'photo' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $firebaseUid = $request->input('firebase_uid');
            $email = $request->input('email');
            $name = $request->input('name');
            $photo = $request->input('photo');
            $firebaseToken = $request->input('firebase_token');

            Log::info('Intento de login con Google Firebase:', [
                'firebase_uid' => $firebaseUid,
                'email' => $email,
                'name' => $name
            ]);

            // Buscar usuario existente por firebase_uid o email
            $user = User::where('firebase_uid', $firebaseUid)
                       ->orWhere('email', $email)
                       ->first();

            if ($user) {
                // Usuario existente - actualizar datos
                $user->update([
                    'firebase_uid' => $firebaseUid,
                    'firebase_token' => $firebaseToken,
                    'photo' => $photo,
                    'last_login_at' => now(),
                    'provider' => 'google'
                ]);

                Log::info('Usuario existente actualizado:', ['user_id' => $user->id]);
            } else {
                // Crear nuevo usuario
                // Separar el nombre completo en nombre y apellido
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0] ?? $name;
                $lastName = $nameParts[1] ?? '';

                $user = User::create([
                    'name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => null, // Google no provee teléfono
                    'email' => $email,
                    'firebase_uid' => $firebaseUid,
                    'firebase_token' => $firebaseToken,
                    'photo' => $photo,
                    'password' => bcrypt('firebase-auth-' . $firebaseUid),
                    'email_verified_at' => now(), // Google ya verificó el email
                    'provider' => 'google',
                    'last_login_at' => now()
                ]);

                Log::info('✅ Nuevo usuario creado con Firebase:', ['user_id' => $user->id]);

                // Crear UserLevel automáticamente (IMPORTANTE: debe existir antes de UsuarioCuenta)
                $userLevel = \App\Models\UserLevel::create([
                    'user_id' => $user->id,
                    'level' => 1,
                    'current_experience' => 0,
                    'total_experience' => 0,
                    'experience_to_next_level' => 150,
                ]);

                Log::info('⭐ Nivel de usuario Firebase creado:', [
                    'user_id' => $user->id,
                    'level_id' => $userLevel->id
                ]);

                // Crear UsuarioCuenta automáticamente para usuarios de Firebase
                $nuevaCuenta = \App\Models\UsuarioCuenta::create([
                    'user_id' => $user->id,
                    'id_medio_pago' => null,
                    'configuraciones' => json_encode([
                        'lat' => '19.432608',
                        'lng' => '-99.133209',
                        'sync_enabled' => true,
                        'offline_mode' => true
                    ]),
                    'total_xp' => 0,
                    'current_level' => 1,
                ]);

                Log::info('💳 Cuenta de usuario Firebase creada:', [
                    'user_id' => $user->id,
                    'cuenta_id' => $nuevaCuenta->id
                ]);
            }

            // Crear token Sanctum para la sesión
            $token = $user->createToken('google-auth')->plainTextToken;

            // Cargar relaciones necesarias
            $user->load('userLevel', 'usuarioCuenta');

            // Preparar datos del usuario para respuesta
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $user->photo,
                'level' => $user->userLevel ? $user->userLevel->level : 1,
                'experience' => $user->userLevel ? $user->userLevel->current_experience : 0,
                'is_premium' => $user->usuarioCuenta ? ($user->usuarioCuenta->is_premium ?? false) : false,
                'provider' => 'google',
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Login con Google exitoso',
                'user' => $userData,
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en login con Google:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['firebase_token', 'password'])
            ]);

            // En desarrollo, devolver el error específico
            $errorMessage = 'Error interno del servidor';
            $errorDetails = null;

            if (config('app.debug')) {
                $errorMessage = $e->getMessage();
                $errorDetails = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => $errorDetails
            ], 500);
        }
    }
}
