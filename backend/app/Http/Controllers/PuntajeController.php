<?php

namespace App\Http\Controllers;

use App\Models\Puntaje;
use Illuminate\Http\Request;

class PuntajeController extends Controller
{
    // Muestra una lista de puntajes
    public function index()
    {
        $puntajes = Puntaje::all();
        return response()->json($puntajes);
    }

    // Guarda un nuevo puntaje en la base de datos
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '_cantidad' => 'required|integer',
            '_rango' => 'required|string|max:50',
            '_id_usuario' => 'required|integer',
        ]);

        $puntaje = Puntaje::create($validatedData);

        return response()->json([
            'message' => 'Puntaje creado con éxito.',
            'puntaje' => $puntaje,
        ], 201);
    }

    // Muestra un puntaje específico
    public function show($id)
    {
        $puntaje = Puntaje::findOrFail($id);
        return response()->json($puntaje);
    }

    // Actualiza un puntaje en la base de datos
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            '_cantidad' => 'required|integer',
            '_rango' => 'required|string|max:50',
            '_id_usuario' => 'required|integer',
        ]);

        $puntaje = Puntaje::findOrFail($id);
        $puntaje->update($validatedData);

        return response()->json([
            'message' => 'Puntaje actualizado con éxito.',
            'puntaje' => $puntaje,
        ]);
    }

    // Elimina un puntaje de la base de datos
    public function destroy($id)
    {
        $puntaje = Puntaje::findOrFail($id);
        $puntaje->delete();

        return response()->json([
            'message' => 'Puntaje eliminado con éxito.'
        ]);
    }
}
