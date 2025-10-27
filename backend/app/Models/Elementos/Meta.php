<?php


namespace App\Models\Elementos;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\objetivo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    use HasFactory;

    protected $table = 'metas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'elemento_id',
        'tipo',
        'categoria',
        'fechaCreacion',
        'fechaVencimiento',
        'nombre',
        'informacion',
        'objetivo_id',
        'status',
        'position',
    ];

    // Relación con Elemento
    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    // Relación con Objetivo
    public function objetivo()
    {
        return $this->belongsTo(objetivo::class, 'objetivo_id');
    }
}
