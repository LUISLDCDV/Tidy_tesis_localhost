<?php

namespace App\Http\Controllers;

use App\Models\MedioDePago;
use Illuminate\Http\Request;

class MedioDePagoController extends Controller
{
    // Muestra una lista de medios de pago
    public function index()
    {
        $mediosDePago = MedioDePago::all();
        return response()->json($mediosDePago);
    }

    // Guarda un nuevo medio de pago en la base de datos
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'identificador' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
        ]);

        $medioDePago = MedioDePago::create($validatedData);

        return response()->json([
            'message' => 'Medio de Pago creado con éxito.',
            'medio_de_pago' => $medioDePago,
        ], 201);
    }

    // Muestra un medio de pago específico
    public function show($id)
    {
        $medioDePago = MedioDePago::findOrFail($id);
        return response()->json($medioDePago);
    }

    // Actualiza un medio de pago en la base de datos
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'identificador' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
        ]);

        $medioDePago = MedioDePago::findOrFail($id);
        $medioDePago->update($validatedData);

        return response()->json([
            'message' => 'Medio de Pago actualizado con éxito.',
            'medio_de_pago' => $medioDePago,
        ]);
    }

    // Elimina un medio de pago de la base de datos
    public function destroy($id)
    {
        $medioDePago = MedioDePago::findOrFail($id);
        $medioDePago->delete();

        return response()->json([
            'message' => 'Medio de Pago eliminado con éxito.'
        ]);
    }
}
