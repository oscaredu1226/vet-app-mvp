<?php

namespace App\Http\Controllers;

use App\Models\Egreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EgresoController extends Controller
{
    /**
     * Muestra la lista de egresos y el formulario de creación.
     */
    public function index(Request $request)
    {
        $query = Egreso::orderBy('fecha', 'desc');
        $egresos = $query->paginate(10);
        
        return view('egresos.index', compact('egresos'));
    }

    /**
     * Guarda un nuevo egreso.
     * Reemplaza la lógica POST de 'modules/egresos.php'
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string', // 'motivo' en tu HTML
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            Egreso::create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Egreso registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un egreso.
     */
    public function destroy(Egreso $egreso)
    {
        try {
            $egreso->delete();
            return response()->json(['success' => true, 'message' => 'Egreso eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}