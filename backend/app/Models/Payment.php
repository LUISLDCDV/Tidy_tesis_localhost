<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'collection_id',
        'subscription_id',
        'status',
        'payment_type',
        'payment_method',
        'amount',
        'currency',
        'plan_type',
        'description',
        'metadata',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /**
     * Scope para pagos aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para suscripciones
     */
    public function scopeSubscriptions($query)
    {
        return $query->where('payment_type', 'subscription');
    }

    /**
     * Formatea el monto con la moneda
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Obtiene el nombre del estado en español
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'approved' => 'Aprobado',
            'pending' => 'Pendiente',
            'rejected' => 'Rechazado',
            'cancelled' => 'Cancelado'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Obtiene el nombre del tipo de plan en español
     */
    public function getPlanTypeLabelAttribute()
    {
        $labels = [
            'monthly' => 'Mensual',
            'annual' => 'Anual'
        ];

        return $labels[$this->plan_type] ?? $this->plan_type;
    }
}
