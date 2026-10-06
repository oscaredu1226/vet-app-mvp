<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Traits\ExportsToExcel;
use App\Http\Traits\ChecksExportPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    use ExportsToExcel, ChecksExportPermission;
    /**
     * Muestra la lista de productos (y la búsqueda).
     * Reemplaza la lógica GET de 'modules/productos.php'
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar_producto');
        $filtro = $request->query('filtro');

        // Estadísticas
        $stats = [
            'total' => Producto::count(),
            'stock_alto' => Producto::whereRaw('stock > stock_minimo')->count(),
            'stock_bajo' => Producto::whereRaw('stock > 0 AND stock <= stock_minimo')->count(),
            'sin_stock' => Producto::where('stock', 0)->count(),
        ];

        $query = Producto::orderBy('id_producto', 'desc');

        if ($buscar) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        // Aplicar filtros de stock
        if ($filtro) {
            switch ($filtro) {
                case 'stock_alto':
                    $query->whereRaw('stock > stock_minimo');
                    break;
                case 'stock_minimo':
                    $query->whereRaw('stock > 0 AND stock <= stock_minimo');
                    break;
                case 'sin_stock':
                    $query->where('stock', '=', 0);
                    break;
            }
        }

        $perPage = $request->input('per_page', 10);
        $productos = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        return view('productos.index', compact('productos', 'stats'));
    }

    /**
     * Guarda un nuevo producto.
     * Reemplaza la lógica POST de 'modules/productos.php'
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:productos,nombre',
            'precio' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'tipo' => 'required|string|in:clinica,farmacia,petshop,spa',
            'descripcion' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            Producto::create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Producto registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición (para AJAX en modal).
     * Reemplaza 'modules/editar_producto.php' (GET)
     */
    public function edit(Producto $producto)
    {
        // $producto ya viene cargado gracias al Route-Model Binding
        return view('productos.partials.edit_modal_content', compact('producto'));
    }

    /**
     * Actualiza un producto.
     * Reemplaza 'modules/editar_producto.php' (POST)
     */
    public function update(Request $request, Producto $producto)
    {
        $validator = Validator::make($request->all(), [
            // Validar 'unique' ignorando el ID del producto actual
            'nombre' => 'required|string|max:255|unique:productos,nombre,' . $producto->id_producto . ',id_producto',
            'precio' => 'required|numeric|min:0.01',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:1',
            'tipo' => 'required|string|in:clinica,farmacia,petshop,spa',
            'descripcion' => 'nullable|string',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $producto->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Producto actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un producto.
     * Reemplaza 'modules/eliminar_producto.php'
     */
    public function destroy(Producto $producto)
    {
        try {
            // TODO: Añadir lógica de seguridad si el producto está en ventas
            // if ($producto->ventas()->exists()) { ... }

            $producto->delete();
            return response()->json(['success' => true, 'message' => 'Producto eliminado correctamente']);
        } catch (\Exception $e) {
            // Manejar error de clave foránea si está en una venta
            if (str_contains($e->getMessage(), 'foreign key constraint fails')) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar: el producto está asociado a ventas existentes.'], 400);
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta los productos a Excel
     */
    public function exportar(Request $request)
    {
        // Verificar permiso usando trait
        if ($response = $this->checkExportPermission('puede_exportar_productos', 'No tienes permiso para exportar productos.')) {
            return $response;
        }

        $buscar = $request->query('buscar_producto');
        $filtro = $request->query('filtro');

        $query = Producto::orderBy('nombre');

        if ($buscar) {
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        // Aplicar filtros
        if ($filtro) {
            switch ($filtro) {
                case 'stock_alto':
                    $query->whereRaw('stock > stock_minimo');
                    break;
                case 'stock_minimo':
                    $query->whereRaw('stock > 0 AND stock <= stock_minimo');
                    break;
                case 'sin_stock':
                    $query->where('stock', '=', 0);
                    break;
            }
        }

        $productos = $query->get();

        // Preparar datos - Hoja 1: Productos por categoría de stock
        $data1 = [];
        foreach ($productos as $producto) {
            $stockAlto = $producto->stock > 2 ? $producto->stock : '';
            $stockBajo = ($producto->stock > 0 && $producto->stock <= 2) ? $producto->stock : '';
            $sinStock = $producto->stock == 0 ? '0' : '';
            $fechaVencimiento = $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('d-m-Y') : 'N/A';
            
            $data1[] = [$producto->nombre, $stockAlto, $stockBajo, $sinStock, $fechaVencimiento];
        }

        // Preparar datos - Hoja 2: Stock de productos
        $data2 = [];
        foreach ($productos as $producto) {
            $fechaVencimiento = $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('d-m-Y') : 'N/A';
            $data2[] = [$producto->nombre, $producto->stock, $producto->stock_minimo, $fechaVencimiento];
        }

        // Crear archivo Excel con múltiples hojas
        $fileName = 'productos_' . date('Y-m-d_His') . '.xlsx';
        return $this->exportMultipleSheets([
            [
                'title' => 'LISTA DE PRODUCTOS',
                'headers' => ['Nombre del Producto', 'Stock Alto (>2)', 'Stock Bajo (<=2)', 'Sin Stock', 'FV'],
                'data' => $data1,
                'sheetName' => 'Productos'
            ],
            [
                'title' => 'STOCK DE PRODUCTOS',
                'headers' => ['Producto', 'Stock Actual', 'Stock Mínimo', 'FV'],
                'data' => $data2,
                'sheetName' => 'Stock de Productos'
            ]
        ], $fileName);
    }
}
