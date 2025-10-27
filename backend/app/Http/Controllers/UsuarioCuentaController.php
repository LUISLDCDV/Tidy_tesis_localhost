<?php
namespace App\Http\Controllers;

use App\Models\UsuarioCuenta;
use Illuminate\Http\Request;
use App\Models\Elementos\Elemento;


class UsuarioCuentaController extends Controller
{
     // Muestra una lista de cuentas de usuario
     public function index()
     {
         $cuentas = UsuarioCuenta::with(['usuario', 'medioPago', 'puntaje', 'elementos'])->get();
         return view('usuario_cuentas.index', compact('cuentas'));
     }
 
     // Muestra el formulario para crear una nueva cuenta de usuario
     public function create()
     {
         return view('usuario_cuentas.create');
     }
 
     // Guarda una nueva cuenta de usuario en la base de datos
     public function store(Request $request)
     {
         $request->validate([
             'id_usuario' => 'required|exists:usuarios,id',
             'id_medio_pago' => 'required|exists:medios_de_pago,id',
             'puntaje_id' => 'nullable|exists:puntajes,id',
             'configuraciones' => 'nullable|json',
         ]);
 
         UsuarioCuenta::create($request->all());
 
         return redirect()->route('usuario_cuentas.index')->with('success', 'Cuenta de usuario creada con éxito.');
     }
 
     // Muestra una cuenta de usuario específica
     public function show($id)
     {
         $cuenta = UsuarioCuenta::with(['usuario', 'medioPago', 'puntaje', 'elementos'])->findOrFail($id);
         return view('usuario_cuentas.show', compact('cuenta'));
     }
 
     // Muestra el formulario para editar una cuenta de usuario
     public function edit($id)
     {
         $cuenta = UsuarioCuenta::findOrFail($id);
         return view('usuario_cuentas.edit', compact('cuenta'));
     }
 
     // Actualiza una cuenta de usuario en la base de datos
     public function update(Request $request, $id)
     {
         $request->validate([
             'id_usuario' => 'required|exists:usuarios,id',
             'id_medio_pago' => 'required|exists:medios_de_pago,id',
             'puntaje_id' => 'nullable|exists:puntajes,id',
             'configuraciones' => 'nullable|json',
         ]);
 
         $cuenta = UsuarioCuenta::findOrFail($id);
         $cuenta->update($request->all());
 
         return redirect()->route('usuario_cuentas.index')->with('success', 'Cuenta de usuario actualizada con éxito.');
     }
 
     // Elimina una cuenta de usuario de la base de datos
     public function destroy($id)
     {
         $cuenta = UsuarioCuenta::findOrFail($id);
         $cuenta->delete();
 
         return redirect()->route('usuario_cuentas.index')->with('success', 'Cuenta de usuario eliminada con éxito.');
     }


     
}
