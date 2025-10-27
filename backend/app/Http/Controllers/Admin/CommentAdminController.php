<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\UserComment;

class CommentAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('role:admin'); // Descomenta si usas roles
    }

    /**
     * Mostrar vista principal de comentarios
     */
    public function index(Request $request)
    {
        try {
            $status = $request->get('status');
            $type = $request->get('type');
            $priority = $request->get('priority');
            $search = $request->get('search');

            $query = UserComment::with(['user', 'respondedBy']);

            // Filtros
            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->where('type', $type);
            }

            if ($priority) {
                $query->where('priority', $priority);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('subject', 'LIKE', "%{$search}%")
                      ->orWhere('comment', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            $comments = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            $stats = UserComment::getStats();

            return view('admin.comments.index', compact('comments', 'stats'));

        } catch (\Exception $e) {
            Log::error('❌ Error en vista de comentarios admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error cargando comentarios');
        }
    }

    /**
     * Mostrar comentario específico
     */
    public function show($id)
    {
        try {
            $comment = UserComment::with(['user', 'respondedBy'])->findOrFail($id);

            return view('admin.comments.show', compact('comment'));

        } catch (\Exception $e) {
            Log::error('❌ Error mostrando comentario específico', [
                'comment_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Comentario no encontrado');
        }
    }

    /**
     * Responder a un comentario
     */
    public function respond(Request $request, $id)
    {
        try {
            $request->validate([
                'admin_response' => 'required|string|max:2000',
                'status' => 'required|string|in:pending,in_progress,resolved,closed'
            ]);

            $comment = UserComment::findOrFail($id);

            $comment->update([
                'admin_response' => $request->admin_response,
                'status' => $request->status,
                'responded_by' => Auth::id(),
                'responded_at' => now()
            ]);

            Log::info('💬 Admin respondió comentario', [
                'comment_id' => $comment->id,
                'admin_id' => Auth::id(),
                'new_status' => $request->status
            ]);

            return back()->with('success', 'Respuesta guardada exitosamente');

        } catch (\Exception $e) {
            Log::error('❌ Error respondiendo comentario', [
                'comment_id' => $id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error guardando respuesta');
        }
    }

    /**
     * Cambiar estado de comentario
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,in_progress,resolved,closed'
            ]);

            $comment = UserComment::findOrFail($id);

            $comment->update([
                'status' => $request->status,
                'responded_by' => Auth::id(),
                'responded_at' => now()
            ]);

            Log::info('🔄 Estado de comentario actualizado', [
                'comment_id' => $comment->id,
                'admin_id' => Auth::id(),
                'old_status' => $comment->getOriginal('status'),
                'new_status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error actualizando estado de comentario', [
                'comment_id' => $id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error actualizando estado'
            ], 500);
        }
    }

    /**
     * Cambiar prioridad de comentario
     */
    public function updatePriority(Request $request, $id)
    {
        try {
            $request->validate([
                'priority' => 'required|string|in:low,medium,high,urgent'
            ]);

            $comment = UserComment::findOrFail($id);

            $comment->update([
                'priority' => $request->priority
            ]);

            Log::info('⚡ Prioridad de comentario actualizada', [
                'comment_id' => $comment->id,
                'admin_id' => Auth::id(),
                'old_priority' => $comment->getOriginal('priority'),
                'new_priority' => $request->priority
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prioridad actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error actualizando prioridad de comentario', [
                'comment_id' => $id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error actualizando prioridad'
            ], 500);
        }
    }

    /**
     * Eliminar comentario
     */
    public function destroy($id)
    {
        try {
            $comment = UserComment::findOrFail($id);

            $comment->delete();

            Log::info('🗑️ Comentario eliminado por admin', [
                'comment_id' => $id,
                'admin_id' => Auth::id(),
                'comment_subject' => $comment->subject
            ]);

            return back()->with('success', 'Comentario eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('❌ Error eliminando comentario', [
                'comment_id' => $id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error eliminando comentario');
        }
    }

    /**
     * Dashboard de estadísticas
     */
    public function dashboard()
    {
        try {
            $stats = UserComment::getStats();

            // Comentarios recientes (últimos 10)
            $recentComments = UserComment::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Comentarios de alta prioridad pendientes
            $urgentComments = UserComment::with(['user'])
                ->highPriority()
                ->pending()
                ->orderBy('created_at', 'asc')
                ->get();

            // Comentarios vencidos (más de 7 días sin respuesta)
            $overdueComments = UserComment::with(['user'])
                ->pending()
                ->where('created_at', '<', now()->subDays(7))
                ->orderBy('created_at', 'asc')
                ->get();

            return view('admin.comments.dashboard', compact(
                'stats',
                'recentComments',
                'urgentComments',
                'overdueComments'
            ));

        } catch (\Exception $e) {
            Log::error('❌ Error en dashboard de comentarios', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error cargando dashboard');
        }
    }
}