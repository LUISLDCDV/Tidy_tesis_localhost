<?php

namespace App\Http\Controllers\Elementos;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElementoRequest;

use App\Services\ElementoServiceFactory;
use App\Models\Elementos\Elemento;
use App\Models\Elementos\Alarma;
use App\Models\Elementos\Objetivo;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Calendario;
use App\Models\Elementos\Evento;
use App\Models\Elementos\Nota;
use Illuminate\Http\Request;

use App\Models\UsuarioCuenta;

class ElementoController extends Controller
{
    public function guardarElemento(ElementoRequest $request)
    {

        try {
            $data = $request->validated();

            // Log para depurar datos recibidos
            \Log::info('💾 ElementoController - Datos recibidos:', $data);
            if (isset($data['gps'])) {
                \Log::info('💾 ElementoController - GPS:', ['value' => $data['gps'], 'type' => gettype($data['gps'])]);
            }
            if (isset($data['clima'])) {
                \Log::info('💾 ElementoController - Clima:', ['value' => $data['clima'], 'type' => gettype($data['clima'])]);
            }

            $tipo = $data['tipo'];
            $id = $data['elemento_id'] ?? null;
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // return response($tipo);
            // return response($request);
            // return response($data);
            // return response($data['elemento_id']);
            // return response($id);

            //TODO TERMINAR UPDATE Y CREATE Y PROBAR
            // Si hay ID, obtenemos el elemento y lo actualizamos, si no lo creamos
            if ($id) {
                // Aquí llamamos a un nuevo método de actualización
                $elemento = ElementoServiceFactory::updateElemento($id, $tipo, $data);
            } else {
                // Si no existe un ID, creamos el elemento
                $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
                $elemento = ElementoServiceFactory::crearElemento($Cuenta->id,$tipo, $data);
                // $elemento->cuenta_id = $usuario->cuenta->id; // Asociar el elemento con la cuenta del usuario
                $elemento->save();
            }


            return response()->json($elemento, $id ? 200 : 201);
        } catch (\Exception $e) {
            \Log::error("❌ Error guardando elemento", [
                'user_id' => auth()->id(),
                'tipo' => $data['tipo'] ?? null,
                'elemento_id' => $data['elemento_id'] ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function elementosPorUsuario()
    {
        try {
            // Obtén el usuario autenticado por el token
            $usuario = auth()->user();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            \Log::info("🔍 elementosPorUsuario - Usuario autenticado:", ['user_id' => $usuario->id, 'email' => $usuario->email]);

            $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

            if (!$Cuenta) {
                \Log::error("❌ No se encontró cuenta para user_id: " . $usuario->id);
                return response()->json(['error' => 'Cuenta no encontrada'], 404);
            }

        \Log::info("🔍 Cuenta encontrada:", ['cuenta_id' => $Cuenta->id, 'user_id' => $Cuenta->user_id]);

        $elementosOriginales = Elemento::where('cuenta_id', $Cuenta->id)->get()->toArray();

        \Log::info("🔍 Elementos encontrados:", ['cuenta_id' => $Cuenta->id, 'total_elementos' => count($elementosOriginales)]);

        $elementos = Elemento::where('cuenta_id', $Cuenta->id)
            ->whereNull('deleted_at')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'desc')
            ->get(); 

        // Obtener el tipo solicitado desde query params
        $tipoFiltro = request()->get('tipo');

        // Si se solicita un tipo específico, filtrar elementos
        if ($tipoFiltro) {
            $elementos = $elementos->where('tipo', $tipoFiltro);
        }

        // Enriquecer elementos con datos específicos según el tipo
        $elementosEnriquecidos = $elementos->map(function ($elemento) {
            $elementoArray = $elemento->toArray();
            
            switch ($elemento->tipo) {
                case 'alarma':
                    $alarma = Alarma::where('elemento_id', $elemento->id)->first();
                    if ($alarma) {
                        // Solo agregar campos específicos de alarma, evitando conflictos
                        $elementoArray['fecha'] = $alarma->fecha;
                        $elementoArray['hora'] = $alarma->hora;
                        $elementoArray['fechaVencimiento'] = $alarma->fechaVencimiento;
                        $elementoArray['horaVencimiento'] = $alarma->horaVencimiento;
                        $elementoArray['intensidad_volumen'] = $alarma->intensidad_volumen;
                        $elementoArray['configuraciones'] = $alarma->configuraciones;
                        $elementoArray['nombre'] = $alarma->nombre ?: $elemento->nombre;
                    }
                    break;
                case 'nota':
                    $nota = Nota::where('elemento_id', $elemento->id)->first();
                    if ($nota) {
                        $elementoArray['fecha'] = $nota->fecha;
                        $elementoArray['contenido'] = $nota->contenido;
                        $elementoArray['tipo_nota_id'] = $nota->tipo_nota_id;
                        $elementoArray['informacion'] = $nota->informacion;
                        $elementoArray['clave'] = $nota->clave;
                        $elementoArray['nombre'] = $nota->nombre ?: $elemento->nombre;
                    }
                    break;
                case 'calendario':
                    $calendario = Calendario::where('elemento_id', $elemento->id)->first();
                    if ($calendario) {
                        $elementoArray['color'] = $calendario->color;
                        $elementoArray['informacion'] = $calendario->informacion;
                        $elementoArray['nombre'] = $calendario->nombre ?: $elemento->nombre;
                    }
                    break;
                case 'evento':
                    $evento = Evento::where('elemento_id', $elemento->id)->first();
                    if ($evento) {
                        $elementoArray['calendario_id'] = $evento->calendario_id;
                        $elementoArray['fechaVencimiento'] = $evento->fechaVencimiento;
                        $elementoArray['horaVencimiento'] = $evento->horaVencimiento;
                        $elementoArray['informacion'] = $evento->informacion;
                        $elementoArray['gps'] = $evento->gps;
                        $elementoArray['clima'] = $evento->clima;
                        $elementoArray['nombre'] = $evento->nombre ?: $elemento->nombre;
                    }
                    break;
                case 'objetivo':
                    $objetivo = Objetivo::where('elemento_id', $elemento->id)->first();
                    if ($objetivo) {
                        $elementoArray['status'] = $objetivo->status;
                        $elementoArray['fechaCreacion'] = $objetivo->fechaCreacion;
                        $elementoArray['fechaVencimiento'] = $objetivo->fechaVencimiento;
                        $elementoArray['informacion'] = $objetivo->informacion;
                        $elementoArray['nombre'] = $objetivo->nombre ?: $elemento->nombre;
                    }
                    break;
                case 'meta':
                    $meta = Meta::where('elemento_id', $elemento->id)->first();
                    if ($meta) {
                        $elementoArray['objetivo_id'] = $meta->objetivo_id;
                        $elementoArray['fechaCreacion'] = $meta->fechaCreacion;
                        $elementoArray['fechaVencimiento'] = $meta->fechaVencimiento;
                        $elementoArray['status'] = $meta->status;
                        $elementoArray['informacion'] = $meta->informacion;
                        $elementoArray['nombre'] = $meta->nombre ?: $elemento->nombre;
                    }
                    break;
            }
            
            return $elementoArray;
        });

        $resultado = $elementosEnriquecidos->values();

            \Log::info("📤 Enviando respuesta de elementos:", [
                'total_elementos' => $resultado->count(),
                'tipos' => $resultado->groupBy('tipo')->keys()->toArray(),
                'elementos_sample' => $resultado->take(2)->toArray()
            ]);

            return response()->json($resultado);
        } catch (\Exception $e) {
            \Log::error("❌ Error obteniendo elementos por usuario", [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    // public function moverElemento($array, $idElemento, $nuevaPosicion) {
    //     $elemento = null;
    //     $index = 0;
    
    //     // Buscar el elemento por ID (asumiendo que cada elemento tiene 'id')
    //     foreach ($array as $key => $item) {
    //         if ($item['id'] === $idElemento) {
    //             $elemento = $item;
    //             $index = $key;
    //             break;
    //         }
    //     }
    
    //     if (!$elemento || $nuevaPosicion < 0 || $nuevaPosicion >= count($array)) {
    //         return $array;
    //     }
    
    //     // Eliminar el elemento original
    //     array_splice($array, $index, 1);
    //     // Insertar en la nueva posición
    //     array_splice($array, $nuevaPosicion, 0, [$elemento]);
        
    //     return $array;
    // }

    public function obtenerObjetivoId($elementoId)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Verificar si existe el elemento
        $elemento = Elemento::find($elementoId);
        \Log::info("Buscando objetivo para elemento_id: {$elementoId}");
        \Log::info("Elemento encontrado: " . ($elemento ? "SÍ (tipo: {$elemento->tipo})" : "NO"));

        $objetivo = Objetivo::where('elemento_id', $elementoId)->first();
        \Log::info("Objetivo encontrado: " . ($objetivo ? "SÍ (id: {$objetivo->id})" : "NO"));

        if (!$objetivo) {
            return response()->json([
                'error' => 'Objetivo no encontrado',
                'elemento_id' => $elementoId,
                'elemento_existe' => $elemento ? true : false,
                'elemento_tipo' => $elemento ? $elemento->tipo : null
            ], 404);
        }

        return response()->json(['objetivo_id' => $objetivo->id]);
    }

    public function obtenerMetasDeObjetivoNuevo($elementoId)
    {
        // Log detallado de peticiones entrantes
        $timestamp = now()->format('Y-m-d H:i:s.u');
        $userAgent = request()->header('User-Agent', 'Unknown');
        $ip = request()->ip();

        \Log::info("🆕 [METAS REQUEST] Iniciando obtenerMetasDeObjetivo", [
            'timestamp' => $timestamp,
            'elementoId' => $elementoId,
            'user_ip' => $ip,
            'user_agent' => substr($userAgent, 0, 100), // Truncar UA
            'referer' => request()->header('Referer'),
            'request_id' => request()->header('X-Request-ID', 'no-id')
        ]);

        $usuario = auth()->user();

        if (!$usuario) {
            \Log::warning("🆕 [METAS REQUEST] Usuario no autenticado", ['elementoId' => $elementoId]);
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        \Log::info("🆕 [METAS REQUEST] Usuario autenticado", [
            'elementoId' => $elementoId,
            'user_id' => $usuario->id,
            'email' => $usuario->email
        ]);

        // Obtener el objetivo desde elemento_id
        \Log::info("🆕 Buscando objetivo para elemento_id: {$elementoId}");
        $objetivo = Objetivo::where('elemento_id', $elementoId)->first();

        if (!$objetivo) {
            return response()->json(['error' => 'Objetivo no encontrado'], 404);
        }

        // Obtener metas reales de la tabla metas - SOLO tabla metas, no elementos
        \Log::info("🆕 Consultando metas para objetivo_id: {$objetivo->id}");
        $metas = Meta::where('objetivo_id', $objetivo->id)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE
                WHEN status = 'completada' THEN 1
                WHEN status = 'en_progreso' THEN 2
                WHEN status = 'pendiente' THEN 3
                ELSE 4
            END")  // Primero por status: completadas, en progreso, pendientes
            ->orderBy('created_at', 'asc')  // Luego por fecha de creación
            ->get()
            ->map(function($meta) {
                \Log::info("📋 Meta ordenada: {$meta->nombre} | Status: {$meta->status} | ID: {$meta->id}");
                return [
                    'id' => $meta->id,
                    'elemento_id' => $meta->elemento_id,
                    'nombre' => $meta->nombre,
                    'status' => $meta->status,
                    'objetivo_id' => $meta->objetivo_id,
                    'fechaCreacion' => $meta->fechaCreacion,
                    'fechaVencimiento' => $meta->fechaVencimiento,
                    'informacion' => $meta->informacion,
                    'tipo' => $meta->tipo
                ];
            });

        \Log::info("✅ Metas encontradas para objetivo_id: {$objetivo->id}, total: " . $metas->count());

        return response()->json($metas);
    }

    public function obtenerMetasDeObjetivo($elementoId)
    {
        // Redirecto al método nuevo para evitar código cacheado
        return $this->obtenerMetasDeObjetivoNuevo($elementoId);
    }

    public function eliminarElemento($id)
    {
        // return response($id);
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

        // Obtener el elemento primero (solo si no está eliminado)
        $elemento = Elemento::where('cuenta_id', $Cuenta->id)
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$elemento) {
            return response()->json(['error' => 'Elemento no encontrado'], 404);
        }

        // Marcar como eliminado
        $elemento->deleted_at = now();
        $elemento->save();

        \Log::info("Eliminando elemento", [
            'elemento_id' => $elemento->id,
            'tipo' => $elemento->tipo,
            'usuario_id' => $usuario->id
        ]);

        switch ($elemento->tipo) {
            
            case 'meta':
                \Log::info("🗑️ Eliminando meta", [
                    'elemento_id' => $elemento->id,
                    'tipo' => $elemento->tipo,
                    'usuario_id' => $usuario->id
                ]);

                $metaAntes = Meta::where('elemento_id', $elemento->id)->first();
                if ($metaAntes) {
                    \Log::info("✅ Meta encontrada para eliminar", [
                        'meta_id' => $metaAntes->id,
                        'nombre' => $metaAntes->nombre,
                        'status' => $metaAntes->status
                    ]);
                }

                $meta = Meta::where('elemento_id', $elemento->id)
                ->update(['deleted_at' => now()]);

                \Log::info("✅ Meta eliminada - filas afectadas: {$meta}");

                return response()->json($meta);
                break;

            case 'objetivo':
                // Obtener el objetivo antes de eliminarlo
                $objetivo = Objetivo::where('elemento_id', $elemento->id)->first();
                if (!$objetivo) {
                    return response()->json(['error' => 'Objetivo no encontrado'], 404);
                }

                // Eliminar también todas sus metas asociadas
                Meta::where('objetivo_id', $objetivo->id)
                    ->update(['deleted_at' => now()]);

                // Marcar el objetivo como eliminado
                $objetivo->update(['deleted_at' => now()]);

                \Log::info("🗑️ Objetivo eliminado exitosamente", [
                    'objetivo_id' => $objetivo->id,
                    'elemento_id' => $elemento->id,
                    'usuario_id' => auth()->user()->id
                ]);

                return response()->json([
                    'message' => 'Objetivo eliminado exitosamente',
                    'objetivo_id' => $objetivo->id
                ]);
                break;
            case 'alarma':
                $alarma = Alarma::where('elemento_id', $elemento->id)
                ->update(['deleted_at' => now()]);

                return response()->json($alarma);
                break;
            case 'nota':
                $nota = Nota::where('elemento_id', $elemento->id)
                ->update(['deleted_at' => now()]);

                return response()->json($nota);
                break;
            case 'calendario':
                $calendario = Calendario::where('elemento_id', $elemento->id)
                ->update(['deleted_at' => now()]);

                return response()->json($calendario);
                break;

            case 'evento':
                $evento = Evento::where('elemento_id', $elemento->id)
                ->update(['deleted_at' => now()]);

                return response()->json($evento);                 
                break;

            default:
                return response()->json(['success' => true]);

        }




        
    }
    
    public function elementoPorId($id)
    {
        // Obtén el usuario autenticado por el token
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();
        
        if (!$Cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }
        
        $elemento = Elemento::where('cuenta_id', $Cuenta->id)
                     ->where('id', $id)
                     ->first();
                     
        if (!$elemento) {
            return response()->json(['error' => 'Elemento no encontrado'], 404);
        }
    
        switch ($elemento->tipo) {
            case 'alarma':
                $alarma = Alarma::where('elemento_id', $elemento->id)->first();
                if (!$alarma) {
                    return response()->json(['error' => 'Alarma no encontrada'], 404);
                }
                return response()->json($alarma);

            case 'objetivo':
                $objetivo = Objetivo::where('elemento_id', $elemento->id)->first();
                if (!$objetivo) {
                    return response()->json(['error' => 'Objetivo no encontrado'], 404);
                }
                        
                $metas = Meta::where('objetivo_id', $objetivo->id)
                    ->whereNull('deleted_at')
                    ->select('metas.*')
                    ->get();
            
                $objetivo->metas = $metas; 
            
                return response()->json($objetivo);

            case 'calendario':
                $calendario = Calendario::where('elemento_id', $elemento->id)->first();
                if (!$calendario) {
                    return response()->json(['error' => 'Calendario no encontrado'], 404);
                }
                return response()->json($calendario);                 

            case 'evento':
                $evento = Evento::where('elemento_id', $elemento->id)->first();
                if (!$evento) {
                    return response()->json(['error' => 'Evento no encontrado'], 404);
                }
                return response()->json($evento);                 

            case 'nota':
                $nota = Nota::where('elemento_id', $elemento->id)->first();
                if (!$nota) {
                    return response()->json(['error' => 'Nota no encontrada'], 404);
                }
                return response()->json($nota);

            case 'meta':
                $meta = Meta::where('elemento_id', $elemento->id)->first();
                if (!$meta) {
                    return response()->json(['error' => 'Meta no encontrada'], 404);
                }
                return response()->json($meta);

            default:
                return response()->json(['error' => 'Tipo de elemento no válido'], 400);              
        }
    }

    public function actualizarOrden(Request $request)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first();

        if (!$Cuenta) {
            return response()->json(['error' => 'Cuenta no encontrada'], 404);
        }

        $data = $request->input('elementos'); // Array de elementos con id y orden

        if (!is_array($data)) {
            return response()->json(['error' => 'Datos inválidos'], 400);
        }

        foreach ($data as $elemento) {
            if (isset($elemento['id'], $elemento['orden'])) {
                Elemento::where('id', $elemento['id'])
                    ->where('cuenta_id', $Cuenta->id)
                    ->update(['orden' => $elemento['orden']]);
            }
        }

        return response()->json(['success' => true]);
    }
}
