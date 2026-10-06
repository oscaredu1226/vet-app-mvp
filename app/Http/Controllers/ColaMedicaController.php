<?php

namespace App\Http\Controllers;

use App\Models\ColaMedica;
use App\Models\Mascota;
use App\Events\ColaMedicaActualizada;
use Illuminate\Http\Request;

class ColaMedicaController extends Controller
{
    /**
     * Obtener todas las mascotas en cola médica
     */
    public function index(Request $request)
    {
        // Solo permitir peticiones JSON (AJAX)
        if (!$request->expectsJson() && !$request->wantsJson()) {
            return redirect()->route('dashboard.admin');
        }
        
        $cola = ColaMedica::with(['mascota.cliente'])
            ->orderBy('fecha_ingreso', 'desc')
            ->get();

        return response()->json($cola);
    }

    /**
     * Agregar mascota a la cola médica
     */
    public function agregar(Request $request)
    {
        $request->validate([
            'id_mascota' => 'required|exists:mascotas,id_mascota',
            'motivo' => 'nullable|string|max:255'
        ]);

        // Verificar si ya está en cola
        $existe = ColaMedica::where('id_mascota', $request->id_mascota)->exists();
        
        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Esta mascota ya está en la cola médica'
            ], 400);
        }

        $cola = ColaMedica::create([
            'id_mascota' => $request->id_mascota,
            'agregado_por' => auth()->id(),
            'motivo' => $request->motivo
        ]);

        $cola->load(['mascota.cliente']);

        // Emitir evento de actualización a todos los clientes
        try {
            broadcast(new ColaMedicaActualizada());
        } catch (\Exception $e) {
            \Log::warning('No se pudo emitir evento de broadcasting: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Mascota agregada a la cola médica',
            'data' => $cola
        ]);
    }

    /**
     * Terminar atención (eliminar de cola)
     */
    public function terminar($id_mascota)
    {
        $cola = ColaMedica::where('id_mascota', $id_mascota)->first();

        if (!$cola) {
            return response()->json([
                'success' => false,
                'message' => 'La mascota no está en la cola médica'
            ], 404);
        }

        $cola->delete();

        // Emitir evento de actualización a todos los clientes
        try {
            broadcast(new ColaMedicaActualizada());
        } catch (\Exception $e) {
            \Log::warning('No se pudo emitir evento de broadcasting: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Atención terminada'
        ]);
    }

    /**
     * Verificar si una mascota está en cola
     */
    public function verificar($id_mascota)
    {
        $enCola = ColaMedica::where('id_mascota', $id_mascota)->exists();

        return response()->json([
            'en_cola' => $enCola
        ]);
    }

    /**
     * Terminar todas las atenciones (vaciar cola médica)
     */
    public function terminarTodo()
    {
        $cantidad = ColaMedica::count();
        
        if ($cantidad === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No hay mascotas en la cola médica'
            ], 400);
        }

        ColaMedica::truncate();

        // Emitir evento de actualización a todos los clientes
        try {
            broadcast(new ColaMedicaActualizada());
        } catch (\Exception $e) {
            \Log::warning('No se pudo emitir evento de broadcasting: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Se terminaron {$cantidad} atenciones"
        ]);
    }
}
