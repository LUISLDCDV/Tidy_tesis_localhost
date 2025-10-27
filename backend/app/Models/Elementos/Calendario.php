<?php


namespace App\Models\Elementos;
// namespace App\Models;
use  App\Models\Elementos\Elemento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendario extends Model
{
    use HasFactory;

    protected $table = 'calendarios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'elemento_id',
        'nombre',
        'color',
        'informacion',
    ];

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    // Relación con Eventos
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'calendario_id');
    }
}
