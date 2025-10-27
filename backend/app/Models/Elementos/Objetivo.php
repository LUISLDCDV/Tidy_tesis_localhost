<?php


namespace App\Models\Elementos;

use App\Models\Elementos\Elemento;
use App\Models\Elementos\Meta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objetivo extends Model
{
    use HasFactory;

    protected $table = 'objetivos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipo',
        'elemento_id',
        'fechaCreacion',
        'fechaVencimiento',
        'nombre',
        'informacion',
        'status', 

    ];

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    // Relación con Metas
    public function metas()
    {
        return $this->hasMany(Meta::class, 'objetivo_id');
    }
}
