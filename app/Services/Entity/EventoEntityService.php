<?php

namespace App\Services\Entity;

use App\Models\Evento;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventoEntityService
{
    use ImageUploadTrait;

    /**
     * Obtener la entidad (fundación o veterinaria) del usuario autenticado
     */
    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        return null;
    }

    /**
     * Obtener el ID de la entidad y su tipo para guardar en eventos
     */
    public function getEntidadData()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de entidad no encontrado');
        }

        $user = Auth::user();
        $tipo = $user->tipo;

        if ($tipo === 'fundacion') {
            return [
                'fundacion_id' => $entidad->id,
                'veterinaria_id' => null,
                'tipo' => 'fundacion'
            ];
        }

        if ($tipo === 'veterinaria') {
            return [
                'fundacion_id' => null,
                'veterinaria_id' => $entidad->id,
                'tipo' => 'veterinaria'
            ];
        }

        throw new \Exception('Tipo de entidad no válido');
    }

    /**
     * Obtener todos los eventos de la entidad
     */
    public function getMisEventos()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de entidad no encontrado');
        }

        $user = Auth::user();

        $query = Evento::query();

        if ($user->tipo === 'fundacion') {
            $query->where('fundacion_id', $entidad->id);
        } elseif ($user->tipo === 'veterinaria') {
            $query->where('veterinaria_id', $entidad->id);
        } else {
            throw new \Exception('Tipo de usuario no válido para eventos');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Encontrar un evento específico de la entidad
     */
    public function findEvento(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de entidad no encontrado');
        }

        $user = Auth::user();

        $query = Evento::where('id', $id);

        if ($user->tipo === 'fundacion') {
            $query->where('fundacion_id', $entidad->id);
        } elseif ($user->tipo === 'veterinaria') {
            $query->where('veterinaria_id', $entidad->id);
        } else {
            throw new \Exception('Tipo de usuario no válido para eventos');
        }

        $evento = $query->first();

        if (!$evento) {
            throw new ModelNotFoundException('Evento no encontrado');
        }

        return $evento;
    }

    /**
     * Crear un nuevo evento
     */
    public function createEvento(array $data, $imagen = null)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de entidad no encontrado');
        }

        $entidadData = $this->getEntidadData();

        // Datos básicos del evento
        $eventoData = [
            'nombre_evento' => $data['nombre_evento'],
            'lugar_evento' => $data['lugar_evento'],
            'descripcion' => $data['descripcion'],
            'fecha_evento' => $data['fecha_evento'],
            'tipo' => $entidadData['tipo'],
            'fundacion_id' => $entidadData['fundacion_id'],
            'veterinaria_id' => $entidadData['veterinaria_id'],
            'likes' => 0,
        ];

        // Campos opcionales
        if (isset($data['fecha_fin'])) {
            $eventoData['fecha_fin'] = $data['fecha_fin'];
        }
        if (isset($data['capacidad_maxima'])) {
            $eventoData['capacidad_maxima'] = $data['capacidad_maxima'];
        }
        if (isset($data['costo'])) {
            $eventoData['costo'] = $data['costo'];
        }
        if (isset($data['organizador'])) {
            $eventoData['organizador'] = $data['organizador'];
        }
        if (isset($data['telefono_contacto'])) {
            $eventoData['telefono_contacto'] = $data['telefono_contacto'];
        }
        if (isset($data['email_contacto'])) {
            $eventoData['email_contacto'] = $data['email_contacto'];
        }
        if (isset($data['categoria'])) {
            $eventoData['categoria'] = $data['categoria'];
        }
        if (isset($data['tags']) && is_array($data['tags'])) {
            $eventoData['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        // Crear el evento
        $evento = Evento::create($eventoData);

        // Subir imagen a Cloudinary si existe
        if ($imagen) {
            $evento->imagen_url = $this->uploadImage($imagen, 'eventos');
            $evento->imagen_public_id = null;
            $evento->save();
        }

        return $evento;
    }

    /**
     * Actualizar un evento existente
     */
    public function updateEvento(int $id, array $data, $imagen = null)
    {
        $evento = $this->findEvento($id);

        // Actualizar campos que vienen en la petición
        if (isset($data['nombre_evento'])) {
            $evento->nombre_evento = $data['nombre_evento'];
        }
        if (isset($data['lugar_evento'])) {
            $evento->lugar_evento = $data['lugar_evento'];
        }
        if (isset($data['descripcion'])) {
            $evento->descripcion = $data['descripcion'];
        }
        if (isset($data['fecha_evento'])) {
            $evento->fecha_evento = $data['fecha_evento'];
        }
        if (isset($data['fecha_fin'])) {
            $evento->fecha_fin = $data['fecha_fin'];
        }
        if (isset($data['capacidad_maxima'])) {
            $evento->capacidad_maxima = $data['capacidad_maxima'];
        }
        if (isset($data['costo'])) {
            $evento->costo = $data['costo'];
        }
        if (isset($data['organizador'])) {
            $evento->organizador = $data['organizador'];
        }
        if (isset($data['telefono_contacto'])) {
            $evento->telefono_contacto = $data['telefono_contacto'];
        }
        if (isset($data['email_contacto'])) {
            $evento->email_contacto = $data['email_contacto'];
        }
        if (isset($data['categoria'])) {
            $evento->categoria = $data['categoria'];
        }
        if (isset($data['tags']) && is_array($data['tags'])) {
            $evento->tags = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        // Actualizar imagen si viene una nueva (eliminando la anterior)
        if ($imagen) {
            if ($evento->imagen_url) {
                $this->deleteImage($evento->imagen_url);
            }
            $evento->imagen_url = $this->uploadImage($imagen, 'eventos');
            $evento->imagen_public_id = null;
        }

        $evento->save();
        return $evento;
    }

    /**
     * Eliminar un evento
     */
    public function deleteEvento(int $id)
    {
        $evento = $this->findEvento($id);

        if ($evento->imagen_url) {
            $this->deleteImage($evento->imagen_url);
        }

        $evento->delete();
    }

    /**
     * Obtener evento por ID (sin verificar entidad - para admin)
     */
    public function findEventoById(int $id): Evento
    {
        $evento = Evento::find($id);

        if (!$evento) {
            throw new ModelNotFoundException('Evento no encontrado');
        }

        return $evento;
    }

    /**
     * Obtener todos los eventos (para admin)
     */
    public function getAllEventos()
    {
        return Evento::with(['fundacion', 'veterinaria'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
