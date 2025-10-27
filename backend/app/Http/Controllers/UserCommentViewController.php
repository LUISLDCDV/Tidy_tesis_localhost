<?php

namespace App\Http\Controllers;

use App\Models\UserComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserCommentViewController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $comments = UserComment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.comments.index', compact('comments'));
    }

    public function create()
    {
        return view('user.comments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'type' => ['required', Rule::in(['help_request', 'suggestion', 'bug_report', 'feedback', 'other'])],
            'comment' => 'required|string',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
        ]);

        $user = Auth::user();

        $comment = UserComment::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name . ' ' . ($user->last_name ?? ''),
            'subject' => $request->subject,
            'type' => $request->type,
            'comment' => $request->comment,
            'priority' => $request->priority ?? 'medium',
            'status' => 'pending',
            'metadata' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->headers->get('referer'),
                'platform' => 'web'
            ]
        ]);

        return redirect()->route('user.comments.show', $comment->id)
            ->with('success', 'Tu comentario ha sido enviado exitosamente. Te responderemos pronto.');
    }

    public function show(UserComment $comment)
    {
        $user = Auth::user();

        // Verificar que el usuario solo pueda ver sus propios comentarios
        if ($comment->user_id !== $user->id) {
            abort(403, 'No tienes permisos para ver este comentario.');
        }

        return view('user.comments.show', compact('comment'));
    }
}