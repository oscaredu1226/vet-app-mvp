<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // (Aquí iría la vista para mostrar todos los usuarios)
    public function index()
    {
        $usuarios = User::all();
        // Cargarías una vista de admin:
        // return view('admin.usuarios.index', compact('usuarios'));
        return response()->json($usuarios); // Por ahora, solo JSON
    }

    /**
     * Guarda un nuevo usuario creado por el Admin.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'usuario'])], // Solo permite estos roles
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            
            return response()->json(['success' => true, 'message' => 'Usuario creado exitosamente', 'user' => $user], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}