<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\UserComment;

class UserCommentController extends Controller
{
    /**
     * Crear nuevo comentario/solicitud de ayuda
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:200',
                'comment' => 'required|string|max:2000',
                'type' => 'required|string|in:help_request,suggestion,bug_report,feedback,other',
                'priority' => 'nullable|string|in:low,medium,high,urgent',
                'email' => 'nullable|email|required_without:user_id',
                'name' => 'nullable|string|max:100|required_without:user_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Metadatos adicionales para contexto
            $metadata = [
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->get('current_url'),
                'timestamp' => now()->toISOString(),
                'platform' => $request->get('platform', 'web')
            ];

            $comment = UserComment::create([
                'user_id' => $user ? $user->id : null,
                'email' => $user ? $user->email : $request->email,
                'name' => $user ? $user->name : $request->name,
                'type' => $request->type,
                'subject' => $request->subject,
                'comment' => $request->comment,
                'priority' => $request->get('priority', 'medium'),
                'metadata' => $metadata
            ]);

            Log::info('💬 Nuevo comentario/solicitud creada', [
                'comment_id' => $comment->id,
                'user_id' => $user ? $user->id : null,
                'type' => $comment->type,
                'priority' => $comment->priority,
                'subject' => $comment->subject
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tu comentario ha sido enviado exitosamente. Te responderemos pronto.',
                'comment_id' => $comment->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('❌ Error creando comentario', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener comentarios del usuario autenticado
     */
    public function getUserComments(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $status = $request->get('status');
            $type = $request->get('type');
            $limit = min($request->get('limit', 10), 50);

            $query = UserComment::where('user_id', $user->id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->where('type', $type);
            }

            $comments = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'type' => $comment->type,
                        'type_text' => $comment->type_text,
                        'subject' => $comment->subject,
                        'comment' => $comment->comment,
                        'priority' => $comment->priority,
                        'priority_text' => $comment->priority_text,
                        'status' => $comment->status,
                        'status_text' => $comment->status_text,
                        'admin_response' => $comment->admin_response,
                        'created_at' => $comment->created_at->toISOString(),
                        'responded_at' => $comment->responded_at?->toISOString(),
                        'is_overdue' => $comment->isOverdue()
                    ];
                });

            return response()->json([
                'success' => true,
                'comments' => $comments,
                'total' => $comments->count()
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo comentarios del usuario', [
                'user_id' => Auth::id(),
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
     * Obtener comentario específico
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $comment = UserComment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            // Solo el usuario que creó el comentario puede verlo (o admin)
            if ($comment->user_id !== $user->id && !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para ver este comentario'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'type' => $comment->type,
                    'type_text' => $comment->type_text,
                    'subject' => $comment->subject,
                    'comment' => $comment->comment,
                    'priority' => $comment->priority,
                    'priority_text' => $comment->priority_text,
                    'status' => $comment->status,
                    'status_text' => $comment->status_text,
                    'admin_response' => $comment->admin_response,
                    'created_at' => $comment->created_at->toISOString(),
                    'responded_at' => $comment->responded_at?->toISOString(),
                    'responded_by' => $comment->respondedBy?->name
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo comentario específico', [
                'comment_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener tipos de comentarios disponibles
     */
    public function getTypes()
    {
        try {
            $types = [
                'help_request' => 'Solicitud de Ayuda',
                'suggestion' => 'Sugerencia',
                'bug_report' => 'Reporte de Bug',
                'feedback' => 'Comentario',
                'other' => 'Otro'
            ];

            $priorities = [
                'low' => 'Baja',
                'medium' => 'Media',
                'high' => 'Alta',
                'urgent' => 'Urgente'
            ];

            return response()->json([
                'success' => true,
                'types' => $types,
                'priorities' => $priorities
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo tipos de comentarios', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de comentarios del usuario
     */
    public function getUserStats()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $stats = [
                'total' => UserComment::where('user_id', $user->id)->count(),
                'pending' => UserComment::where('user_id', $user->id)->pending()->count(),
                'resolved' => UserComment::where('user_id', $user->id)->where('status', 'resolved')->count(),
                'recent' => UserComment::where('user_id', $user->id)->recent()->count(),
                'by_type' => UserComment::where('user_id', $user->id)
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo estadísticas de comentarios', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}