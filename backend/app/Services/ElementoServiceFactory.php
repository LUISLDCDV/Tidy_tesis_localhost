<?php


namespace App\Services;

use App\Models\Elementos\Alarma;
use App\Models\Elementos\Objetivo;
use App\Models\Elementos\Meta;
use App\Models\Elementos\Calendario;
use App\Models\Elementos\Evento;
use App\Models\Elementos\Nota;
use App\Models\Elementos\Elemento;
use App\Models\UsuarioCuenta;
use App\Models\GamificationConfig;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ElementoServiceFactory
{


    /**
     * Crea un nuevo elemento y su tipo específico.
     *
     * @param int $id ID de la cuenta a la que pertenece el elemento.
     * @param string $tipo Tipo del elemento (alarma, objetivo, meta, calendario, evento, nota).
     * @param array $data Datos específicos del tipo de elemento.
     * @return Elemento El elemento creado.
     */
    public static function crearElemento($id,$tipo, $data)
    {
        // Primero creamos el Elemento, ya que es la entidad principal
        $elemento = new Elemento();
        $elemento->descripcion = $data['nombre'] ?? $elemento->descripcion;
        $elemento->cuenta_id = $id;
        $elemento->tipo = $tipo;
        // Puedes agregar más campos a Elemento aquí si es necesario
        $elemento->save();  // Guarda el registro en la base de datos y obtiene su ID

        // Luego, dependiendo del tipo, creamos el tipo específico de elemento
        switch ($tipo) {
            case 'alarma':
                $alarma = new Alarma();
                $alarma->elemento_id = $elemento->id; // Asocia el id del elemento
                $alarma->nombre = $data['nombre'];
                $alarma->fecha = $data['fecha'];
                $alarma->hora = $data['hora'];
                $alarma->fechaVencimiento = $data['fechaVencimiento'];
                $alarma->horaVencimiento = $data['horaVencimiento'];
                $alarma->intensidad_volumen = $data['intensidad_volumen'];
                $alarma->configuraciones = $data['configuraciones'] ?? null;
                $alarma->save();
                break;

            case 'objetivo':
                $objetivo = new Objetivo();
                $objetivo->elemento_id = $elemento->id; // Asocia el id del elemento
                // $objetivo->descripcion = $data['descripcion'];
                $objetivo->nombre = $data['nombre'] ?? $objetivo->nombre;
                $objetivo->status = $data['status'] ?? $objetivo->status;
                $objetivo->tipo = $data['tipo_objetivo'] ?? $objetivo->tipo;
                $objetivo->fechaCreacion = $data['fechaCreacion'];
                $objetivo->fechaVencimiento = $data['fechaVencimiento'] ?? null;
                $objetivo->informacion = $data['informacion'] ?? null;
                $objetivo->save();
                break;

            case 'meta':
                $meta = new Meta();
                $meta->elemento_id = $elemento->id; // Asocia el id del elemento
                $meta->objetivo_id = $data['objetivo_id'];
                $meta->nombre = $data['nombre'] ?? $meta->nombre;
                $meta->fechaCreacion = $data['fechaCreacion'];
                $meta->fechaVencimiento = $data['fechaVencimiento'] ?? null;
                $meta->status = $data['status'] ?? $meta->status;
                $meta->informacion = $data['informacion'] ?? null;
                $meta->tipo = $data['categoria'] ?? $data['tipo_meta'] ?? 'preparacion'; // Tipo específico de meta
                $meta->save();
                break;

            case 'calendario':
                $calendario = new Calendario();
                $calendario->elemento_id = $elemento->id; // Asocia el id del elemento
                $calendario->color = $data['color'] ?? '#FFFFFF'; // Color por defecto
                $calendario->nombre = $data['nombre'] ?? 'Calendario';
                $calendario->informacion = $data['informacion'] ?? null;
                $calendario->save();
                break;

            case 'evento':
                $evento = new Evento();
                $evento->elemento_id = $elemento->id; // Asocia el id del elemento
                $evento->calendario_id = $data['calendario_id'];
                $evento->tipo = $data['tipo'];
                $evento->nombre = $data['nombre'];
                $evento->fechaVencimiento = $data['fechaVencimiento'];
                $evento->horaVencimiento = $data['horaVencimiento'];
                $evento->informacion = $data['informacion'] ?? null;
                // gps
                $evento->gps = $data['gps'] ?? null;
                // clima
                $evento->clima = $data['clima'] ?? null;
                $evento->save();
                break;

            case 'nota':
                $nota = new Nota();
                $nota->elemento_id = $elemento->id; // Asocia el id del elemento
                $nota->fecha = $data['fecha'];
                $nota->nombre = $data['nombre'];
                $nota->tipo_nota_id = $data['tipo_nota_id'];
                $nota->informacion = $data['informacion'] ?? null;
                $nota->contenido = $data['contenido'] ?? null;
                $nota->clave = $data['clave'] ?? null;
                $nota->save();
                break;


            default:
                throw new \Exception("Tipo de elemento no soportado");
        }

        // Otorgar experiencia al crear el elemento
        try {
            $cuenta = UsuarioCuenta::find($id);
            if ($cuenta && $cuenta->user) {
                $gamificationService = new GamificationService();

                // Obtener XP desde configuración de BD
                $xpGanada = GamificationConfig::getXP("xp_crear_{$tipo}", 5);
                $gamificationService->giveExperience($cuenta->user->id, $xpGanada, "Crear {$tipo}");

                \Log::info("✨ XP otorgada por crear elemento", [
                    'user_id' => $cuenta->user->id,
                    'tipo' => $tipo,
                    'xp' => $xpGanada
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning("⚠️ No se pudo otorgar XP por crear elemento", [
                'tipo' => $tipo,
                'error' => $e->getMessage()
            ]);
        }

        return $elemento; // Regresamos el Elemento principal (con su id asignado)
    }



    public static function updateElemento($id, $tipo, $data)
    {

        // return $data;
        // Buscamos el Elemento
        $elemento = Elemento::findOrFail($id);

        // Actualizamos los datos del Elemento principal solo si no es una meta
        // Las metas no deben cambiar el tipo del elemento padre (objetivo)
        if ($tipo !== 'meta') {
            $elemento->descripcion = $data['nombre'];
            $elemento->tipo = $tipo; // Actualizar el tipo del elemento
            $elemento->save();  // Guardamos los cambios en el Elemento principal
        }

        // Dependiendo del tipo de elemento, actualizamos los detalles de ese tipo específico
        switch ($tipo) {
            case 'alarma':

                $alarma = Alarma::where('elemento_id', $elemento->id)->first();
                if ($alarma) {
                    $alarma->nombre = $data['nombre'] ?? $alarma->nombre;
                    $alarma->fecha = $data['fecha'] ?? $alarma->fecha;
                    $alarma->hora = $data['hora'] ?? $alarma->hora;
                    $alarma->fechaVencimiento = $data['fechaVencimiento'] ?? $alarma->fechaVencimiento;
                    $alarma->horaVencimiento = $data['horaVencimiento'] ?? $alarma->horaVencimiento;
                    $alarma->intensidad_volumen = $data['intensidad_volumen'] ?? $alarma->intensidad_volumen;
                    $alarma->configuraciones = $data['configuraciones'] ?? $alarma->configuraciones;
                    $alarma->save();
                }
                break;

            case 'objetivo':
                $objetivo = Objetivo::where('elemento_id', $elemento->id)->first();
                if ($objetivo) {
                    $statusAnterior = $objetivo->status;

                    $objetivo->fechaCreacion = $data['fechaCreacion'] ?? $objetivo->fechaCreacion;
                    $objetivo->fechaVencimiento = $data['fechaVencimiento'] ?? $objetivo->fechaVencimiento;
                    $objetivo->tipo = $data['tipo_objetivo'] ?? $objetivo->tipo;
                    $objetivo->nombre = $data['nombre'] ?? $objetivo->nombre;
                    $objetivo->status = $data['status'] ?? $objetivo->status;
                    $objetivo->informacion = $data['informacion'] ?? $objetivo->informacion;
                    $objetivo->save();

                    // Otorgar XP si se completó el objetivo
                    if ($statusAnterior !== 'completado' && $objetivo->status === 'completado') {
                        try {
                            $cuenta = UsuarioCuenta::find($elemento->cuenta_id);
                            if ($cuenta && $cuenta->user) {
                                $gamificationService = new GamificationService();
                                $xpGanada = GamificationConfig::getXP('xp_completar_objetivo', 15);
                                $gamificationService->giveExperience($cuenta->user->id, $xpGanada, "Completar objetivo");
                                \Log::info("✨ XP otorgada por completar objetivo", ['user_id' => $cuenta->user->id, 'xp' => $xpGanada]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning("⚠️ No se pudo otorgar XP por completar objetivo", ['error' => $e->getMessage()]);
                        }
                    }
                }
                break;

            case 'meta':
                \Log::info("🔄 Actualizando meta", [
                    'elemento_id' => $elemento->id,
                    'data_recibida' => $data
                ]);

                $meta = Meta::where('elemento_id', $elemento->id)->first();

                \Log::info("🔍 Buscando meta en DB", [
                    'elemento_id' => $elemento->id,
                    'meta_encontrada' => $meta ? 'SI' : 'NO',
                    'meta_id' => $meta ? $meta->id : null
                ]);

                if ($meta) {
                    \Log::info("✅ Meta encontrada en DB", [
                        'meta_id' => $meta->id,
                        'status_anterior' => $meta->status,
                        'status_nuevo' => $data['status'] ?? $meta->status
                    ]);

                    $statusAnterior = $meta->status;

                    $meta->objetivo_id = $data['objetivo_id'] ?? $meta->objetivo_id;
                    $meta->fechaCreacion = $data['fechaCreacion'] ?? $meta->fechaCreacion;
                    $meta->fechaVencimiento = $data['fechaVencimiento'] ?? $meta->fechaVencimiento;
                    $meta->informacion = $data['informacion'] ?? $meta->informacion;
                    $meta->nombre = $data['nombre'] ?? $meta->nombre;
                    $meta->status = $data['status'] ?? $meta->status;
                    $meta->tipo = $data['categoria'] ?? $data['tipo_meta'] ?? 'preparacion';
                    $meta->save();

                    \Log::info("✅ Meta actualizada exitosamente", [
                        'meta_id' => $meta->id,
                        'nuevo_status' => $meta->status
                    ]);

                    // Otorgar XP si se completó la meta
                    if ($statusAnterior !== 'completada' && $meta->status === 'completada') {
                        try {
                            $cuenta = UsuarioCuenta::find($elemento->cuenta_id);
                            if ($cuenta && $cuenta->user) {
                                $gamificationService = new GamificationService();
                                $xpGanada = GamificationConfig::getXP('xp_completar_meta', 12);
                                $gamificationService->giveExperience($cuenta->user->id, $xpGanada, "Completar meta");
                                \Log::info("✨ XP otorgada por completar meta", ['user_id' => $cuenta->user->id, 'xp' => $xpGanada]);
                            }
                        } catch (\Exception $e) {
                            \Log::warning("⚠️ No se pudo otorgar XP por completar meta", ['error' => $e->getMessage()]);
                        }
                    }

                    // Para las metas, devolvemos la meta actualizada, no el elemento padre
                    return $meta;
                } else {
                    \Log::warning("❌ Meta no encontrada para elemento_id: {$elemento->id}");

                    // Buscar si existe alguna relación para este elemento
                    $todasLasMetas = Meta::all(['id', 'elemento_id', 'nombre'])->toArray();
                    \Log::info("🔍 Debug - Todas las metas en DB:", $todasLasMetas);

                    // Verificar si el elemento existe y su tipo
                    \Log::info("🔍 Debug - Elemento actual:", [
                        'id' => $elemento->id,
                        'tipo' => $elemento->tipo,
                        'descripcion' => $elemento->descripcion
                    ]);
                }
                break;

            case 'calendario':
                $calendario = Calendario::where('elemento_id', $elemento->id)->first();
                if ($calendario) {
                    $calendario->color = $data['color'] ?? $calendario->color;
                    $calendario->informacion = $data['informacion'] ?? $calendario->informacion;
                    $calendario->save();
                }
                break;

            case 'evento':
                $evento = Evento::where('elemento_id', $elemento->id)->first();
                if ($evento) {
                    $evento->calendario_id = $data['calendario_id'] ?? $evento->calendario_id;
                    $evento->tipo = $data['tipo'] ?? $evento->tipo;
                    $evento->fechaVencimiento = $data['fechaVencimiento'] ?? $evento->fechaVencimiento;
                    $evento->horaVencimiento = $data['horaVencimiento'] ?? $evento->horaVencimiento;
                    $evento->informacion = $data['informacion'] ?? $evento->informacion;
                    // nombre: event.nombre || event.title, // Asegurar compatibilidad
                    // informacion: event.informacion || event.content,
                    // fechaVencimiento: event.fechaVencimiento || this.formatDate(event.start, 'YYYY-MM-DD'),
                    // horaVencimiento: event.horaVencimiento || this.formatTime(event.start),
                    // calendario_id: event.calendario_id || this.calendarioSeleccionado?.id,
                    // gps: event.gps || null,
                    // clima: event.clima || null,
                    // elemento_id: event.elemento_id || null,
                    $evento->nombre = $data['nombre'] ?? $evento->nombre;
                    $evento->gps = $data['gps'] ?? $evento->gps;
                    $evento->clima = $data['clima'] ?? $evento->clima;

                    $evento->save();
                }
                break;

            case 'nota':
                $nota = Nota::where('elemento_id', $elemento->id)->first();
                if ($nota) {
                    $nota->fecha = $data['fecha'] ?? $nota->fecha;
                    $nota->informacion = $data['informacion'] ?? $nota->informacion;
                    $nota->contenido = $data['contenido'] ?? $nota->contenido;
                    $nota->nombre = $data['nombre'] ?? $nota->nombre;
                    $nota->clave = $data['clave'] ?? $nota->clave;
                    $nota->save();
                }
                break;


            default:
                throw new \Exception("Tipo de elemento no soportado");
        }

        return $elemento; // Regresamos el Elemento con los cambios
    }

    
}
