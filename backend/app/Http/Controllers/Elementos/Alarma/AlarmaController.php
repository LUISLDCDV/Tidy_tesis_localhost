<?php

namespace App\Http\Controllers\Elementos\Alarma;
use App\Http\Controllers\Controller;
use  App\Models\UsuarioCuenta;
use Illuminate\Support\Facades\DB;


use Illuminate\Support\Facades\Validator;
use App\Models\Elementos\Alarma;
use Illuminate\Http\Request;

class AlarmaController extends Controller
{
    public function index()
    {
        $alarmas = Alarma::all();
        return response()->json($alarmas);
    }

    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_hora' => 'required|date_format:H:i',
            '_nombre' => 'required|string|max:100',
        //     '_idElemento' => 'required|exists:elementos,_idElemento',
        //     '_fecha' => 'nullable|date',
        //     '_hora' => 'required|date_format:H:i',
        //     '_fecha_vencimiento' => 'nullable|date',
        //     '_hora_vencimiento' => 'nullable|date_format:H:i',
        //     '_nombre' => 'required|string|max:100',
        //     '_informacion' => 'nullable|string',
        //     '_configuracion' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // $elemento = new Elemento();
        // $elemento->tipo = '1';
        // $elemento->nombre = 'nombre';
        // $elemento->save();

        // $alarma = new Alarma();
        // $alarma->tipo = 'Tipo 1';
        // $alarma->elemento()->associate($elemento);
        // $alarma->save();
        

        $alarma = Alarma::create($request->all());

        return response()->json(['message' => 'Alarma creada con éxito.', 'alarma' => $alarma], 201);
    }

    public function show($id)
    {
        $alarma = Alarma::findOrFail($id);
        return response()->json($alarma);
    }

    public function actualizar(Request $request, $id)
    {
        $request->validate([
            '_idElemento' => 'required|exists:elementos,_idElemento',
            '_fecha' => 'nullable|date',
            '_hora' => 'required|date_format:H:i',
            '_fecha_vencimiento' => 'nullable|date',
            '_hora_vencimiento' => 'nullable|date_format:H:i',
            '_nombre' => 'required|string|max:100',
            '_informacion' => 'nullable|string',
            '_configuracion' => 'nullable|array',
        ]);

        $alarma = Alarma::findOrFail($id);
        $alarma->update($request->all());

        return response()->json(['message' => 'Alarma actualizada con éxito.', 'alarma' => $alarma]);
    }

    public function eliminar($id)
    {
        $alarma = Alarma::findOrFail($id);
        $alarma->delete();

        return response()->json(['message' => 'Alarma eliminada con éxito.']);
    }

    /**API**/
    public function obtenerAlarmasPorUsuario()
    {
        $usuario = auth()->user();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // $usuarioCuenta = UsuarioCuenta::find($usuario->id); // Cambia 1 por un id válido
        $Cuenta = UsuarioCuenta::where('user_id', $usuario->id)->first(); // Cambia 1 por un id válido
        
        $alarmas = DB::table('alarmas')
        ->join('elementos', 'alarmas.elemento_id', '=', 'elementos.id')
        ->where('elementos.cuenta_id', $Cuenta->id)
        ->whereNull('elementos.deleted_at')
        ->select('alarmas.*') // Seleccionar solo los campos de las notas
        ->get();


        return response()->json($alarmas);
    }

}
