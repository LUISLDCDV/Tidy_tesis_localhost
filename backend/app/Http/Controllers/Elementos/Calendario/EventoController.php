<?php

namespace App\Http\Controllers\Elementos\Calendario;
use App\Http\Controllers\Controller;

use App\Models\Elementos\Evento;
use App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;



class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        return response()->json($eventos);
    }

    // Guarda un nuevo evento en la base de datos
    public function guardar(Request $request)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'tipo' => 'required|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'hora_vencimiento' => 'nullable|date_format:H:i',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
        ]);

        $evento = Evento::create($validatedData);

        return response()->json([
            'message' => 'Evento creado con éxito.',
            'evento' => $evento,
        ], 201);
    }

    // Muestra un evento específico
    public function show($id)
    {
        $evento = Evento::findOrFail($id);
        return response()->json($evento);
    }

    // Actualiza un evento en la base de datos
    public function actualizar(Request $request, $id)
    {
        $validatedData = $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'tipo' => 'required|string|max:50',
            'fecha_vencimiento' => 'nullable|date',
            'hora_vencimiento' => 'nullable|date_format:H:i',
            'nombre' => 'required|string|max:100',
            'informacion' => 'nullable|string',
        ]);

        $evento = Evento::findOrFail($id);
        $evento->update($validatedData);

        return response()->json([
            'message' => 'Evento actualizado con éxito.',
            'evento' => $evento,
        ]);
    }

    // Elimina un evento de la base de datos
    public function eliminar($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->delete();

        return response()->json([
            'message' => 'Evento eliminado con éxito.'
        ]);
    }

    /**API**/
    public function obtenerEventosPorUsuario($id_calendario)
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // $usuarioCuenta = UsuarioCuenta::find($usuario->id); // Cambia 1 por un id válido
        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first(); // Cambia 1 por un id válido
        

        $eventos = DB::table('eventos')
            ->select('id','elemento_id', 'nombre','informacion', 'horaVencimiento', 'fechaVencimiento', 'gps', 'clima', 'calendario_id')
            // ->where('elementos.cuenta_id', $Cuenta->id)
            ->where('calendario_id', $id_calendario)
            ->get();
    

    
        return response()->json([
            'eventos' => $eventos,
        ]);
        
    }

}
