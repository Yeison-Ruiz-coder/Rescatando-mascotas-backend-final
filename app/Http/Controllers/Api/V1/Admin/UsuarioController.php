<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    /**
     * Listado de usuarios con filtros
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtros
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->buscar . '%')
                    ->orWhere('apellidos', 'like', '%' . $request->buscar . '%')
                    ->orWhere('email', 'like', '%' . $request->buscar . '%');
            });
        }

        $usuarios = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    }

    /**
     * Crear usuario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'tipo' => 'required|in:admin,user,veterinaria,fundacion',
            'estado' => 'required|in:activo,inactivo,suspendido,pendiente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('avatar');
        $data['password'] = Hash::make($request->password);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $user
        ], 201);
    }

    /**
     * Mostrar usuario
     */
    public function show($id)
    {
        $user = User::with(['solicitudes', 'adopciones', 'donaciones', 'suscripciones'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'tipo' => 'sometimes|in:admin,user,veterinaria,fundacion',
            'estado' => 'sometimes|in:activo,inactivo,suspendido,pendiente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['avatar', 'password']);
        $data['updated_by'] = auth()->id();

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado',
            'data' => $user
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Verificar relaciones
        if ($user->adopciones()->exists() ||
            $user->solicitudes()->exists() ||
            $user->donaciones()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: tiene registros asociados'
            ], 400);
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado'
        ]);
    }

    /**
     * Cambiar estado de usuario
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:activo,inactivo,suspendido,pendiente'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'estado' => $request->estado,
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'data' => $user
        ]);
    }


    /**
     * Usuario Pendiente
     */
    public function pendientesCount()
    {
        $count = User::where('estado', 'pendiente')
            ->whereIn('tipo', ['fundacion', 'veterinaria'])
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
    /**
     * Verificar email
     */
    public function verificarEmail($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'email_verified_at' => now(),
            'updated_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verificado'
        ]);
    }
}
