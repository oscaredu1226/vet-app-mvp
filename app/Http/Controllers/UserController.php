<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Muestra la lista de usuarios (solo para administradores)
     */
    public function index(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $perPage = $request->input('per_page', 10);
        $usuarios = User::orderBy('created_at', 'desc')->paginate($perPage)->appends(['per_page' => $perPage]);

        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Guarda un nuevo usuario
     */
    public function store(Request $request)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'role' => 'required|string|in:administrador,usuario',
            'usuario' => 'required|string|max:255|unique:users,usuario',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'cargo.required' => 'El cargo es obligatorio.',
            'role.required' => 'El rol es obligatorio.',
            'usuario.required' => 'El usuario es obligatorio.',
            'usuario.unique' => 'Este usuario ya está registrado.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            User::create([
                'name' => $request->name,
                'apellido' => $request->apellido,
                'cargo' => $request->cargo,
                'role' => $request->role,
                'usuario' => $request->usuario,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'puede_exportar_clientes' => $request->has('puede_exportar_clientes'),
                'puede_exportar_mascotas' => $request->has('puede_exportar_mascotas'),
                'puede_exportar_ventas' => $request->has('puede_exportar_ventas'),
                'puede_exportar_productos' => $request->has('puede_exportar_productos'),
                'puede_exportar_servicios' => $request->has('puede_exportar_servicios'),
                'puede_exportar_caja' => $request->has('puede_exportar_caja'),
                'puede_acceder_caja' => $request->has('puede_acceder_caja'),
                'puede_acceder_ventas' => $request->has('puede_acceder_ventas'),
                'puede_acceder_egresos' => $request->has('puede_acceder_egresos'),
            ]);

            return response()->json(['success' => true, 'message' => 'Usuario registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un usuario
     */
    public function update(Request $request, User $user)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'role' => 'required|string|in:administrador,usuario',
            'usuario' => 'required|string|max:255|unique:users,usuario,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'cargo.required' => 'El cargo es obligatorio.',
            'role.required' => 'El rol es obligatorio.',
            'usuario.required' => 'El usuario es obligatorio.',
            'usuario.unique' => 'Este usuario ya está registrado.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $data = [
                'name' => $request->name,
                'apellido' => $request->apellido,
                'cargo' => $request->cargo,
                'role' => $request->role,
                'usuario' => $request->usuario,
                'email' => $request->email,
                'puede_exportar_clientes' => $request->has('puede_exportar_clientes'),
                'puede_exportar_mascotas' => $request->has('puede_exportar_mascotas'),
                'puede_exportar_ventas' => $request->has('puede_exportar_ventas'),
                'puede_exportar_productos' => $request->has('puede_exportar_productos'),
                'puede_exportar_servicios' => $request->has('puede_exportar_servicios'),
                'puede_exportar_caja' => $request->has('puede_exportar_caja'),
                'puede_acceder_caja' => $request->has('puede_acceder_caja'),
                'puede_acceder_ventas' => $request->has('puede_acceder_ventas'),
                'puede_acceder_egresos' => $request->has('puede_acceder_egresos'),
            ];

            // Solo actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un usuario
     */
    public function destroy(User $user)
    {
        // Verificar que el usuario sea administrador
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }

        // No permitir eliminar al usuario autenticado
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.'], 400);
        }

        try {
            $user->delete();
            return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
