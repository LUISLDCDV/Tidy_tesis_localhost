<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


/*
Un Request es una clase que encapsula los datos y la lógica 
necesarios para realizar una solicitud. En tu diagrama de clases,
 puedes representar el Request como una clase que contiene atributos y
  métodos relevantes para manejar la solicitu
*/

class ElementoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $nombre = $this->input('nombre');
        $tipo = $this->input('tipo');
        $rules = [
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:alarma,objetivo,meta,calendario,evento,nota',
        ];

        switch ($tipo) {
            case 'alarma':
                $rules['elemento_id'] = 'nullable|exists:elementos,id';  // Asegura que el elemento existe
                $rules['fecha'] = 'nullable|date';
                $rules['hora'] = 'required|date_format:H:i:s';
                $rules['horaVencimiento'] = 'nullable|date_format:H:i:s';
                $rules['fechaVencimiento'] = 'nullable|date';
                $rules['intensidad_volumen'] = 'required|numeric|min:0|max:100';
                $rules['configuraciones'] = 'nullable|json';
                break;
    
            case 'objetivo':
                // Validación para Objetivo
                $rules['elemento_id'] = 'nullable|exists:elementos,id';  // Asegura que el elemento existe            
                $rules['tipo'] = 'required|string|max:50';  // Tipo de objetivo, puedes ajustar según las necesidades
                $rules['fechaCreacion'] = 'required|date';
                $rules['fechaVencimiento'] = 'nullable|date';
                $rules['nombre'] = 'required|string|max:255';
                $rules['informacion'] = 'nullable|string';
                $rules['tipo_objetivo'] = 'required|string';
                $rules['status'] = 'required|string|max:50';  // Estado del objetivo
                break;
    
            case 'meta':
                // Validación para Meta
                $rules['elemento_id'] = 'nullable|exists:elementos,id';  // Asegura que el elemento existe
                $rules['objetivo_id'] = 'required|exists:elementos,id';  // Asegura que el elemento existe
                $rules['fechaCreacion'] = 'required|date';
                $rules['fechaVencimiento'] = 'nullable|date';
                $rules['nombre'] = 'required|string|max:255';
                $rules['informacion'] = 'nullable|string';
                $rules['status'] = 'required|string|max:50';  // Estado del objetivo

                break;
    
            case 'calendario':
                $rules['nombre'] = 'required|string|max:255';
                $rules['color'] = 'required|string|max:20'; // Suponiendo que es un código de color
                $rules['informacion'] = 'nullable|string';
                break;
    
            case 'evento':
                $rules['elemento_id'] = 'nullable|exists:elementos,id';  // Asegura que el elemento existe
                $rules['calendario_id'] = 'required|exists:calendarios,id'; // Asegura que el calendario existe
                $rules['tipo'] = 'required|string|max:255'; // El tipo de evento
                $rules['fechaVencimiento'] = 'required|date';
                $rules['horaVencimiento'] = 'required|date_format:H:i:s';
                $rules['nombre'] = 'required|string|max:255';
                $rules['informacion'] = 'nullable|string';
                $rules['gps'] = 'nullable|array'; // Acepta arrays/objetos JSON
                $rules['clima'] = 'nullable|array'; // Acepta arrays/objetos JSON
                break;
    
            case 'nota':
                // Validación para Nota
                $rules['elemento_id'] = 'nullable|exists:elementos,id';  // Asegura que el elemento existe
                $rules['fecha'] = 'required|date';
                $rules['nombre'] = 'required|string|max:255';
                $rules['tipo_nota_id'] = 'required|integer|min:1|max:16';  // Validar rango de tipos válidos
                $rules['informacion'] = 'nullable|string';
                $rules['contenido'] = 'nullable|string';
                break;


            // Agrega más casos según los modelos que tengas.
        }

        return $rules;
    }
}
