<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'type',
        'subject',
        'comment',
        'priority',
        'status',
        'admin_response',
        'responded_by',
        'responded_at',
        'metadata'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Relación con el usuario que creó el comentario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el administrador que respondió
     */
    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scope para comentarios pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para comentarios de alta prioridad
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Scope para comentarios de un tipo específico
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para comentarios recientes (últimos 7 días)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>', now()->subDays(7));
    }

    /**
     * Scope para comentarios sin respuesta
     */
    public function scopeUnresponded($query)
    {
        return $query->whereNull('admin_response');
    }

    /**
     * Marcar como resuelto
     */
    public function markAsResolved($adminId = null, $response = null)
    {
        $this->update([
            'status' => 'resolved',
            'admin_response' => $response,
            'responded_by' => $adminId,
            'responded_at' => now()
        ]);
    }

    /**
     * Marcar como en progreso
     */
    public function markAsInProgress($adminId = null)
    {
        $this->update([
            'status' => 'in_progress',
            'responded_by' => $adminId,
            'responded_at' => now()
        ]);
    }

    /**
     * Obtener el nombre del usuario (registrado o anónimo)
     */
    public function getUserDisplayName()
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->name ?: 'Usuario Anónimo';
    }

    /**
     * Obtener el email del usuario (registrado o anónimo)
     */
    public function getUserEmail()
    {
        if ($this->user) {
            return $this->user->email;
        }
        return $this->email;
    }

    /**
     * Obtener el texto del tipo de comentario
     */
    public function getTypeTextAttribute()
    {
        $types = [
            'help_request' => 'Solicitud de Ayuda',
            'suggestion' => 'Sugerencia',
            'bug_report' => 'Reporte de Bug',
            'feedback' => 'Comentario',
            'other' => 'Otro'
        ];

        return $types[$this->type] ?? 'Desconocido';
    }

    /**
     * Obtener el texto de la prioridad
     */
    public function getPriorityTextAttribute()
    {
        $priorities = [
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        ];

        return $priorities[$this->priority] ?? 'Media';
    }

    /**
     * Obtener el texto del estado
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado'
        ];

        return $statuses[$this->status] ?? 'Pendiente';
    }

    /**
     * Obtener el color de la prioridad para la UI
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'dark'
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    /**
     * Obtener el color del estado para la UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Verificar si el comentario está vencido (más de 7 días sin respuesta)
     */
    public function isOverdue()
    {
        return $this->status === 'pending' && $this->created_at < now()->subDays(7);
    }

    /**
     * Obtener estadísticas de comentarios
     */
    public static function getStats()
    {
        return [
            'total' => self::count(),
            'pending' => self::pending()->count(),
            'high_priority' => self::highPriority()->count(),
            'recent' => self::recent()->count(),
            'unresponded' => self::unresponded()->count(),
            'overdue' => self::pending()->where('created_at', '<', now()->subDays(7))->count(),
            'by_type' => self::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_status' => self::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}