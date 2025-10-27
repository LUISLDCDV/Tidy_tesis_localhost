<?php
namespace App\Http\Controllers;

use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Laravel\Socialite\Facades\Socialite;

// use Socialite

class AuthController extends Controller
{

    // public function redirectToProvider()
    // {
    //     return Socialite::driver('google')->redirect();
    // }

    // public function handleProviderCallback()
    // {
    //     $user = Socialite::driver('google')->stateless()->user();

    //     // $user->token;
    //     // $user->getId();
    //     // $user->getNickname();
    //     // $user->getName();
    //     // $user->getEmail();
    //     // $user->getAvatar();

    //     // Aquí puedes buscar al usuario en tu base de datos y autenticarlo
    // }


    public function register(Request $request)
    {
        try {
            \Log::info("📝 Iniciando registro de usuario", [
                'email' => $request->email,
                'name' => $request->name
            ]);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
                'last_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20|unique:users',
            ]);

            if ($validator->fails()) {
                \Log::warning("⚠️ Validación fallida en registro", [
                    'email' => $request->email,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json($validator->errors(), 400);
            }

            $user = User::create(
                [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                ]
            );

            \Log::info("👤 Usuario creado exitosamente", [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            $nuevaCuenta = UsuarioCuenta::create(
                [
                    'user_id' => $user->id,
                    'id_medio_pago' => null,
                    'configuraciones' => json_encode(['lat' => '19.432608', 'lng' => '-99.133209']),
                ]
            );

            \Log::info("💳 Cuenta de usuario creada exitosamente", [
                'user_id' => $user->id,
                'cuenta_id' => $nuevaCuenta->id
            ]);

            // Crear token con expiración personalizada según tipo de login
            $tokenName = 'auth_token';
            $expiresAt = now()->addMinutes(config('sanctum.expiration'));

            $tokenResult = $user->createToken($tokenName, ['*'], $expiresAt);
            $token = $tokenResult->plainTextToken;

            \Log::info("✅ Registro completado exitosamente", [
                'user_id' => $user->id,
                'expires_at' => $expiresAt->toISOString()
            ]);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
                'expires_in' => config('sanctum.expiration') * 60, // en segundos
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error en registro de usuario", [
                'email' => $request->email ?? 'No proporcionado',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor durante el registro',
                'message' => 'No se pudo completar el registro. Intente nuevamente.'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            \Log::info("🔐 Intento de login", [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                \Log::warning("⚠️ Login fallido - credenciales incorrectas", [
                    'email' => $request->email,
                    'ip' => $request->ip()
                ]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            // Crear token con expiración
            $tokenName = 'auth_token';
            $expiresAt = now()->addMinutes(config('sanctum.expiration'));

            $tokenResult = $user->createToken($tokenName, ['*'], $expiresAt);
            $token = $tokenResult->plainTextToken;

            // Actualizar último login
            $user->update(['last_login_at' => now()]);

            \Log::info("✅ Login exitoso", [
                'user_id' => $user->id,
                'email' => $user->email,
                'expires_at' => $expiresAt->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toISOString(),
                'expires_in' => config('sanctum.expiration') * 60, // en segundos
                'user' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error en login", [
                'email' => $request->email ?? 'No proporcionado',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor durante el login',
                'message' => 'No se pudo completar la autenticación. Intente nuevamente.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            
            if ($user) {
                // Eliminar todos los tokens del usuario
                $user->tokens()->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No authenticated user found'
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateToken(Request $request)
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        return response()->json([
            'valid' => true,
            'user' => $user,
            'token_name' => $currentToken->name,
            'expires_at' => $currentToken->expires_at?->toISOString(),
            'last_used_at' => $currentToken->last_used_at?->toISOString()
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        // Revocar token actual
        $user->currentAccessToken()->delete();

        // Crear nuevo token
        $tokenName = 'auth_token_refreshed';
        $expiresAt = now()->addMinutes(config('sanctum.expiration'));

        $tokenResult = $user->createToken($tokenName, ['*'], $expiresAt);
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toISOString(),
            'expires_in' => config('sanctum.expiration') * 60,
            'user' => $user
        ]);
    }

    public function getUserSessions(Request $request)
    {
        $user = $request->user();
        $tokens = $user->tokens()->get();

        return response()->json([
            'sessions' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at?->toISOString(),
                    'expires_at' => $token->expires_at?->toISOString(),
                    'is_current' => $token->id === request()->user()->currentAccessToken()->id
                ];
            })
        ]);
    }

    public function revokeSession(Request $request, $tokenId)
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json(['message' => 'Token not found'], 404);
        }

        $token->delete();

        return response()->json(['message' => 'Session revoked successfully']);
    }

}
