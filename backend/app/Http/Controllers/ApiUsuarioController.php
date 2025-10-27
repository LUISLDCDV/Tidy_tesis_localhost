<?php

namespace App\Http\Controllers;

use App\Models\Elementos\Elemento;
use App\Models\User;
use App\Models\UsuarioCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class ApiUsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function getUserData(Request $request)
    {
        $user = Auth::user(); // Obtener el usuario autenticado

        // Cargar la cuenta del usuario con información de premium
        $cuenta = UsuarioCuenta::where('user_id', $user->id)->first();

        // Agregar información de premium al usuario
        $userData = $user->toArray();
        $userData['is_premium'] = $cuenta ? $cuenta->is_premium : false;
        $userData['premium_expires_at'] = $cuenta ? $cuenta->premium_expires_at : null;

        return response()->json([
            'user' => $userData,
        ]);
    }

    public function getProfile(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

            // Parsear configuraciones desde JSON
            $config = $cuenta ? json_decode($cuenta->configuraciones, true) : [];

            return response()->json([
                'data' => [
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'phone' => $config['phone'] ?? '',
                    'bio' => $config['bio'] ?? '',
                    'imageUrl' => $config['imageUrl'] ?? '',
                    'emailVerified' => !is_null($usuario->email_verified_at)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener perfil'], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $usuario->id,
                'phone' => 'sometimes|string|max:20',
                'bio' => 'sometimes|string|max:500',
                'imageUrl' => 'sometimes|url|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Actualizar usuario
            if ($request->has('name')) {
                $usuario->name = $request->name;
            }
            if ($request->has('email')) {
                $usuario->email = $request->email;
            }
            $usuario->save();

            // Actualizar o crear cuenta
            $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
            if (!$cuenta) {
                $cuenta = new UsuarioCuenta();
                $cuenta->user_id = $usuario->id;
                $cuenta->configuraciones = '{}';
            }

            // Parsear configuraciones existentes
            $config = json_decode($cuenta->configuraciones, true) ?? [];

            // Actualizar configuraciones del perfil
            if ($request->has('phone')) {
                $config['phone'] = $request->phone;
            }
            if ($request->has('bio')) {
                $config['bio'] = $request->bio;
            }
            if ($request->has('imageUrl')) {
                $config['imageUrl'] = $request->imageUrl;
            }

            // Guardar configuraciones actualizadas
            $cuenta->configuraciones = json_encode($config);
            $cuenta->save();

            return response()->json([
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'phone' => $config['phone'] ?? '',
                    'bio' => $config['bio'] ?? '',
                    'imageUrl' => $config['imageUrl'] ?? '',
                    'emailVerified' => !is_null($usuario->email_verified_at)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar perfil'], 500);
        }
    }

    public function getSettings(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

            // Parsear configuraciones desde JSON
            $config = $cuenta ? json_decode($cuenta->configuraciones, true) : [];

            // Devolver configuraciones por defecto si no existen
            $settings = [
                'profile' => [
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'phone' => $config['phone'] ?? '',
                    'bio' => $config['bio'] ?? '',
                    'imageUrl' => $config['imageUrl'] ?? '',
                    'emailVerified' => !is_null($usuario->email_verified_at)
                ],
                'privacy' => [
                    'publicProfile' => $config['privacy']['publicProfile'] ?? true,
                    'showStats' => $config['privacy']['showStats'] ?? true,
                    'shareLocation' => $config['privacy']['shareLocation'] ?? false,
                    'allowAnalytics' => $config['privacy']['allowAnalytics'] ?? true
                ],
                'appearance' => [
                    'theme' => $config['appearance']['theme'] ?? 'auto',
                    'language' => $config['appearance']['language'] ?? 'es',
                    'fontSize' => $config['appearance']['fontSize'] ?? 'medium',
                    'colorScheme' => $config['appearance']['colorScheme'] ?? 'default',
                    'compactMode' => $config['appearance']['compactMode'] ?? false
                ],
                'functionality' => [
                    'autoSave' => $config['functionality']['autoSave'] ?? true,
                    'autoSync' => $config['functionality']['autoSync'] ?? true,
                    'offlineMode' => $config['functionality']['offlineMode'] ?? false
                ]
            ];

            return response()->json(['data' => $settings]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener configuraciones'], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
            if (!$cuenta) {
                $cuenta = new UsuarioCuenta();
                $cuenta->user_id = $usuario->id;
                $cuenta->configuraciones = '{}';
            }

            // Parsear configuraciones existentes
            $config = json_decode($cuenta->configuraciones, true) ?? [];

            // Actualizar configuraciones de privacidad
            if ($request->has('privacy')) {
                $privacy = $request->privacy;
                if (!isset($config['privacy'])) {
                    $config['privacy'] = [];
                }

                if (isset($privacy['publicProfile'])) {
                    $config['privacy']['publicProfile'] = $privacy['publicProfile'];
                }
                if (isset($privacy['showStats'])) {
                    $config['privacy']['showStats'] = $privacy['showStats'];
                }
                if (isset($privacy['shareLocation'])) {
                    $config['privacy']['shareLocation'] = $privacy['shareLocation'];
                }
                if (isset($privacy['allowAnalytics'])) {
                    $config['privacy']['allowAnalytics'] = $privacy['allowAnalytics'];
                }
            }

            // Actualizar configuraciones de apariencia
            if ($request->has('appearance')) {
                $appearance = $request->appearance;
                if (!isset($config['appearance'])) {
                    $config['appearance'] = [];
                }

                if (isset($appearance['theme'])) {
                    $config['appearance']['theme'] = $appearance['theme'];
                }
                if (isset($appearance['language'])) {
                    $config['appearance']['language'] = $appearance['language'];
                }
                if (isset($appearance['fontSize'])) {
                    $config['appearance']['fontSize'] = $appearance['fontSize'];
                }
                if (isset($appearance['colorScheme'])) {
                    $config['appearance']['colorScheme'] = $appearance['colorScheme'];
                }
                if (isset($appearance['compactMode'])) {
                    $config['appearance']['compactMode'] = $appearance['compactMode'];
                }
            }

            // Actualizar configuraciones de funcionalidad
            if ($request->has('functionality')) {
                $functionality = $request->functionality;
                if (!isset($config['functionality'])) {
                    $config['functionality'] = [];
                }

                if (isset($functionality['autoSave'])) {
                    $config['functionality']['autoSave'] = $functionality['autoSave'];
                }
                if (isset($functionality['autoSync'])) {
                    $config['functionality']['autoSync'] = $functionality['autoSync'];
                }
                if (isset($functionality['offlineMode'])) {
                    $config['functionality']['offlineMode'] = $functionality['offlineMode'];
                }
            }

            // Guardar configuraciones actualizadas
            $cuenta->configuraciones = json_encode($config);
            $cuenta->save();

            return response()->json([
                'message' => 'Configuraciones actualizadas exitosamente',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar configuraciones'], 500);
        }
    }

}
