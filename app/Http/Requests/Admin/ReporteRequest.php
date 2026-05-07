<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_reporte' => 'required|in:perdido,encontrado,maltrato,otro',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|min:10',
            'ubicacion' => 'required|string|max:255',
            'fecha_incidente' => 'required|date',
            'especie' => 'nullable|string|max:100',
            'raza' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'foto_url' => 'nullable|image|max:2048',
            'user_id' => 'nullable|exists:users,id',
            'nombre_reportante' => 'nullable|string|max:255',
            'telefono_reportante' => 'nullable|string|max:20',
            'email_reportante' => 'nullable|email|max:255',
            // ===== NUEVOS CAMPOS =====
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'direccion_completa' => 'nullable|string',
            'contacto_permiso' => 'nullable|boolean',
            'anonimo' => 'nullable|boolean',
            'urgencia' => 'nullable|in:baja,media,alta,critica',
            // Mantener compatibilidad con campos antiguos
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:255',
            'datos_animal' => 'nullable|array',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'contacto_permiso' => $this->boolean('contacto_permiso'),
            'anonimo' => $this->boolean('anonimo'),
        ]);

        // Compatibilidad con latitud/longitud
        if ($this->has('latitud') && !$this->has('lat')) {
            $this->merge(['lat' => $this->latitud]);
        }
        if ($this->has('longitud') && !$this->has('lng')) {
            $this->merge(['lng' => $this->longitud]);
        }
    }
}
