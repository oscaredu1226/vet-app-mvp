<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Http\Traits\ExportsToExcel;
use App\Http\Traits\ChecksExportPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicioController extends Controller
{
    use ExportsToExcel, ChecksExportPermission;
    /**
     * Muestra la lista de servicios (y la búsqueda).
     * Reemplaza la lógica GET de 'modules/servicios.php' [cite: 1387]
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar_servicio');

        $query = Servicio::orderBy('id_servicio', 'desc');

        if ($buscar) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        $perPage = $request->input('per_page', 10);
        $servicios = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        // Esto carga la vista 'resources/views/servicios/index.blade.php'
        return view('servicios.index', compact('servicios'));
    }

    /**
     * Guarda un nuevo servicio.
     * Reemplaza la lógica POST de 'modules/servicios.php' [cite: 1385-1386]
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:servicios,nombre',
            'precio' => 'required|numeric|min:0.01',
            'tipo' => 'required|string|in:clinica,farmacia,petshop,spa',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            Servicio::create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Servicio registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición (para AJAX en modal).
     * (Inferido de editar_producto.php)
     */
    public function edit(Servicio $servicio)
    {
        return view('servicios.partials.edit_modal_content', compact('servicio'));
    }

    /**
     * Actualiza un servicio.
     * (Inferido de editar_producto.php)
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:servicios,nombre,' . $servicio->id_servicio . ',id_servicio',
            'precio' => 'required|numeric|min:0.01',
            'tipo' => 'required|string|in:clinica,farmacia,petshop,spa',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $servicio->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Servicio actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un servicio.
     * (Inferido de eliminar_producto.php)
     */
    public function destroy(Servicio $servicio)
    {
        try {
            // TODO: Añadir lógica de seguridad si el servicio está en ventas
            // if ($servicio->ventas()->exists()) { ... }

            $servicio->delete();
            return response()->json(['success' => true, 'message' => 'Servicio eliminado correctamente']);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar: el servicio está asociado a ventas existentes.'], 400);
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta servicios a Excel
     */
    public function exportar(Request $request)
    {
        // Verificar permiso usando trait
        if ($response = $this->checkExportPermission('puede_exportar_servicios', 'No tienes permiso para exportar servicios.')) {
            return $response;
        }

        $buscar = $request->query('buscar_servicio');

        $query = Servicio::orderBy('nombre', 'asc');

        if ($buscar) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        $servicios = $query->get();

        // Preparar datos para exportación
        $data = [];
        foreach ($servicios as $servicio) {
            $data[] = [
                $servicio->id_servicio,
                $servicio->nombre,
                ucfirst($servicio->tipo),
                'S/ ' . number_format($servicio->precio, 2)
            ];
        }

        // Exportar usando trait
        return $this->exportToExcel(
            'LISTA DE SERVICIOS',
            ['ID', 'Nombre', 'Tipo', 'Precio'],
            $data,
            'servicios_' . date('Y-m-d_His') . '.xlsx'
        );
    }
}