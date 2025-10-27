<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseStorageService;
use App\Services\FirebaseSyncService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileImageController extends Controller
{
    protected $firebaseStorage;
    protected $firebaseSync;

    public function __construct(FirebaseStorageService $firebaseStorage, FirebaseSyncService $firebaseSync)
    {
        $this->firebaseStorage = $firebaseStorage;
        $this->firebaseSync = $firebaseSync;
    }
    /**
     * Subir imagen de perfil desde archivo
     */
    public function upload(Request $request)
    {
        try {
            Log::info("📸 Iniciando upload de imagen de perfil", [
                'user_id' => $request->user()->id
            ]);

            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB máximo
                'is_offline' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imagen inválida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $image = $request->file('image');

            // Procesar imagen
            $imageData = $this->processImage($image->getRealPath());

            // Subir a Firebase Storage
            $imagePath = $this->firebaseStorage->uploadProfileImage($user->id, $imageData, $image->getClientOriginalExtension());

            // Actualizar perfil del usuario
            $result = $this->updateUserProfile($user, $imagePath, $request->get('is_offline', false));

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("❌ Error subiendo imagen de perfil", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Subir imagen de perfil desde base64 (para móvil)
     */
    public function uploadBase64(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|string',
                'filename' => 'string|nullable',
                'is_offline' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $imageBase64 = $request->image;
            $isOffline = $request->get('is_offline', false);

            // Validar formato base64
            if (!preg_match('/^data:image\/(jpeg|png|jpg|gif);base64,/', $imageBase64)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de imagen base64 inválido'
                ], 422);
            }

            // Decodificar imagen base64
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageBase64));

            if ($imageData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error decodificando imagen base64'
                ], 422);
            }

            // Validar tamaño (5MB max)
            if (strlen($imageData) > 5 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'La imagen es demasiado grande (máximo 5MB)'
                ], 422);
            }

            // Procesar imagen
            $processedImageData = $this->processBase64Image($imageData);

            if ($isOffline) {
                // Guardar en Firebase Sync para sincronización posterior
                $this->firebaseSync->saveForSync($user->id, 'profile_image_upload', [
                    'image_data' => base64_encode($processedImageData),
                    'filename' => $request->get('filename', 'profile.jpg'),
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen guardada para sincronización',
                    'offline_mode' => true
                ]);
            }

            // Subir a Firebase Storage
            $imagePath = $this->firebaseStorage->uploadProfileImage($user->id, $processedImageData, 'jpg');

            // Actualizar perfil del usuario
            $result = $this->updateUserProfile($user, $imagePath, false);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("❌ Error subiendo imagen base64", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener imagen de perfil del usuario
     */
    public function get(Request $request)
    {
        try {
            $user = $request->user();

            // Buscar imagen en el perfil del usuario
            $imagePath = null;

            if ($user->usuarioCuenta && $user->usuarioCuenta->configuraciones) {
                $config = json_decode($user->usuarioCuenta->configuraciones, true);
                $imagePath = $config['profile_image'] ?? null;
            }

            if (!$imagePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Imagen de perfil no encontrada'
                ], 404);
            }

            // Obtener URL pública de Firebase Storage
            $imageUrl = $this->firebaseStorage->getPublicUrl($imagePath);

            return response()->json([
                'success' => true,
                'image_url' => $imageUrl
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error obteniendo imagen de perfil", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Eliminar imagen de perfil
     */
    public function delete(Request $request)
    {
        try {
            $user = $request->user();
            $isOffline = $request->get('is_offline', false);

            // Obtener configuración actual
            $config = [];
            if ($user->usuarioCuenta && $user->usuarioCuenta->configuraciones) {
                $config = json_decode($user->usuarioCuenta->configuraciones, true);
            }

            $oldImagePath = $config['profile_image'] ?? null;

            if ($isOffline) {
                // Guardar en Firebase Sync para sincronización posterior
                $this->firebaseSync->saveForSync($user->id, 'profile_image_delete', [
                    'old_image_path' => $oldImagePath,
                    'timestamp' => now()->toISOString()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen programada para eliminación',
                    'offline_mode' => true
                ]);
            }

            // Eliminar de Firebase Storage
            if ($oldImagePath) {
                $this->firebaseStorage->deleteFile($oldImagePath);
            }

            // Eliminar referencia de la imagen
            unset($config['profile_image']);

            // Actualizar configuración
            $user->usuarioCuenta->update([
                'configuraciones' => json_encode($config)
            ]);

            Log::info("🗑️ Imagen de perfil eliminada", [
                'user_id' => $user->id,
                'old_path' => $oldImagePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Imagen de perfil eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error eliminando imagen de perfil", [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Procesar imagen desde archivo
     */
    protected function processImage($imagePath)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);

        // Redimensionar manteniendo aspecto (max 800x800)
        $image->scale(width: 800, height: 800);

        // Convertir a JPEG para optimizar tamaño
        return $image->toJpeg(85);
    }

    /**
     * Procesar imagen desde base64
     */
    protected function processBase64Image($imageData)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageData);

        // Redimensionar manteniendo aspecto (max 800x800)
        $image->scale(width: 800, height: 800);

        // Convertir a JPEG para optimizar tamaño
        return $image->toJpeg(85);
    }

    /**
     * Actualizar perfil del usuario con nueva imagen
     */
    protected function updateUserProfile($user, $imagePath, $isOffline = false)
    {
        // Obtener configuración actual
        $config = [];
        if ($user->usuarioCuenta && $user->usuarioCuenta->configuraciones) {
            $config = json_decode($user->usuarioCuenta->configuraciones, true);
        }

        $oldImagePath = $config['profile_image'] ?? null;

        if ($isOffline) {
            // Guardar en Firebase Sync para sincronización posterior
            $this->firebaseSync->saveForSync($user->id, 'profile_image_update', [
                'new_image_path' => $imagePath,
                'old_image_path' => $oldImagePath,
                'timestamp' => now()->toISOString()
            ]);

            return [
                'success' => true,
                'message' => 'Imagen guardada para sincronización',
                'offline_mode' => true,
                'temp_path' => $imagePath
            ];
        }

        // Actualizar configuración con nueva imagen
        $config['profile_image'] = $imagePath;

        $user->usuarioCuenta->update([
            'configuraciones' => json_encode($config)
        ]);

        // Eliminar imagen anterior de Firebase Storage si existe
        if ($oldImagePath && $oldImagePath !== $imagePath) {
            $this->firebaseStorage->deleteFile($oldImagePath);
        }

        Log::info("✅ Imagen de perfil actualizada", [
            'user_id' => $user->id,
            'new_path' => $imagePath,
            'old_path' => $oldImagePath
        ]);

        // Obtener URL pública
        $imageUrl = $this->firebaseStorage->getPublicUrl($imagePath);

        return [
            'success' => true,
            'message' => 'Imagen de perfil actualizada correctamente',
            'image_url' => $imageUrl
        ];
    }
}