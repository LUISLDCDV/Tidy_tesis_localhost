<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailVerificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function createToken($userId, $expiresInHours = 24)
    {
        // Eliminar tokens antiguos del usuario
        self::where('user_id', $userId)->delete();

        return self::create([
            'user_id' => $userId,
            'token' => Str::random(64),
            'expires_at' => now()->addHours($expiresInHours),
        ]);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
