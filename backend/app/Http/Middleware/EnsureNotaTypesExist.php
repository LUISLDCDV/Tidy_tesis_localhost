<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TipoNota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureNotaTypesExist
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo aplicar este middleware para rutas de elementos/notas
        if (!$request->is('api/elementos/*') || $request->input('tipo') !== 'nota') {
            return $next($request);
        }

        try {
            // Verificar si la tabla tipos_notas existe
            if (!Schema::hasTable('tipos_notas')) {
                // Si no existe, crear en memoria los tipos válidos
                $this->createInMemoryValidation($request);
                return $next($request);
            }

            // Verificar si hay tipos de nota en la base de datos
            if (TipoNota::count() === 0) {
                TipoNota::seedTiposDefault();
            }

        } catch (\Exception $e) {
            // Si hay error con la BD, usar validación en memoria
            $this->createInMemoryValidation($request);
        }

        return $next($request);
    }

    /**
     * Crear validación en memoria cuando no hay acceso a BD
     */
    private function createInMemoryValidation(Request $request)
    {
        $tipoNotaId = $request->input('tipo_nota_id');

        // Tipos de nota válidos (1-16)
        $tiposValidos = range(1, 16);

        if (!in_array($tipoNotaId, $tiposValidos)) {
            abort(422, 'Tipo de nota inválido. Debe estar entre 1 y 16.');
        }
    }
}