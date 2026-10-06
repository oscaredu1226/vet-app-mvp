<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\PagoVenta;
use App\Models\Configuracion;
use App\Http\Traits\ExportsToExcel;
use App\Http\Traits\ChecksExportPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Luecano\NumeroALetras\NumeroALetras;

class VentaController extends Controller
{
    use ExportsToExcel, ChecksExportPermission;
    
    /**
     * Muestra la página de gestión de ventas y el historial.
     * Reemplaza la lógica GET de 'modules/ventas.php' [cite: 1502-1532]
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar');
        $filtro_fecha = $request->query('filtro_fecha');

        // Obtener IDs representativos usando una subconsulta SQL pura compatible con ONLY_FULL_GROUP_BY
        $subqueryBuilder = DB::table('ventas')
            ->select(DB::raw('MIN(id_venta) as min_id'))
            ->groupBy('id_mascota', 'fecha_venta');

        // Aplicar filtros a la subconsulta si es necesario
        if ($filtro_fecha) {
            $subqueryBuilder->whereDate('fecha_venta', $filtro_fecha);
        }

        // Si hay búsqueda, necesitamos aplicarla después de obtener los IDs
        $idsRepresentativos = $subqueryBuilder->pluck('min_id')->toArray();

        // Cargar las ventas representativas con sus relaciones
        $query = Venta::with(['mascota.cliente', 'producto', 'servicio'])
            ->whereIn('id_venta', $idsRepresentativos);

        // Aplicar filtro de búsqueda
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('mascota', function ($sq) use ($buscar) {
                    $sq->where('nombre', 'LIKE', "%{$buscar}%");
                })
                    ->orWhereHas('mascota.cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('producto', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('servicio', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        $query->orderBy('fecha_venta', 'desc');

        // Estadísticas para las tarjetas [cite: 1507-1512]
        // Contar transacciones únicas usando SQL puro
        $totalTransacciones = DB::table('ventas')
            ->select(DB::raw('COUNT(DISTINCT CONCAT(id_mascota, "-", fecha_venta)) as total'))
            ->value('total');

        $ventasHoy = DB::table('ventas')
            ->whereDate('fecha_venta', Carbon::today())
            ->select(DB::raw('COUNT(DISTINCT CONCAT(id_mascota, "-", fecha_venta)) as total'))
            ->value('total');

        $stats = [
            'total_mascotas' => Mascota::count(),
            'total_productos' => Producto::where('stock', '>', 0)->count(),
            'total_servicios' => Servicio::count(),
            'ventas_hoy' => $ventasHoy,
            'total_monto_ventas' => Venta::sum('subtotal'),
            'total_ventas' => $totalTransacciones,
        ];

        $perPage = $request->input('per_page', 10);
        $ventas = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        // Calcular el subtotal real para cada venta (sumando todos los items de la transacción)
        foreach ($ventas as $venta) {
            $venta->subtotal_real = Venta::where('id_mascota', $venta->id_mascota)
                ->where('fecha_venta', $venta->fecha_venta)
                ->sum('subtotal');
        }

        return view('ventas.index', compact('ventas', 'stats'));
    }

    /**
     * Guarda una nueva venta (simple o mixta).
     * Reemplaza la lógica POST de 'modules/ventas.php' [cite: 1490-1501]
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mascota' => 'required|integer|exists:mascotas,id_mascota',
            'items' => 'required|json',
            'medio_pago' => 'required|string',
            'total_general' => 'required|numeric|min:0.01',
            'pagos_realizados' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $datos = $validator->validated();
        $items = json_decode($datos['items'], true);
        $pagos = json_decode($datos['pagos_realizados'], true) ?? [];
        $fecha_venta = Carbon::now();

        // DEBUG: Log para ver cuántos items se reciben
        \Log::info('=== NUEVA VENTA ===');
        \Log::info('Total de items recibidos: ' . count($items));
        \Log::info('Items: ' . json_encode($items));
        \Log::info('Medio de pago: ' . $datos['medio_pago']);

        // Iniciar transacción
        DB::beginTransaction();
        try {
            // 1. Validar Stock
            foreach ($items as $item) {
                if ($item['tipo'] == 'producto') {
                    $producto = Producto::find($item['id']);
                    if (!$producto || $producto->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para: " . ($producto->nombre ?? 'Item ID ' . $item['id']));
                    }
                }
            }

            // 2. Insertar Venta(s) y Pagos
            if ($datos['medio_pago'] == 'Mixto' && !empty($pagos)) {
                // Venta Mixta: Insertar TODOS los items como registros separados
                \Log::info('Guardando venta MIXTA con ' . count($items) . ' items');

                foreach ($items as $item) {
                    $subtotal = $item['cantidad'] * $item['precio'];
                    $ventaCreada = Venta::create([
                        'id_mascota' => $datos['id_mascota'],
                        'tipo_item' => $item['tipo'],
                        'id_item' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $subtotal,
                        'medio_pago' => 'Mixto',
                        'tipo_negocio' => $item['tipo_negocio'] ?? 'clinica',
                        'fecha_venta' => $fecha_venta,
                    ]);
                    \Log::info('  - Creada venta ID: ' . $ventaCreada->id_venta . ' para item: ' . $item['nombre']);
                }

                // Usar el ID de la primera venta para los pagos
                $primeraVenta = Venta::where('fecha_venta', $fecha_venta)
                    ->where('id_mascota', $datos['id_mascota'])
                    ->orderBy('id_venta', 'asc')
                    ->first();

                foreach ($pagos as $pago) {
                    PagoVenta::create([
                        'id_venta' => $primeraVenta->id_venta,
                        'id_mascota' => $datos['id_mascota'],
                        'medio_pago' => $pago['medio_pago'],
                        'monto' => $pago['cantidad'],
                        'fecha_pago' => $fecha_venta,
                    ]);
                }
            } else {
                // Venta Simple: Insertar cada item como una venta separada
                \Log::info('Guardando venta SIMPLE con ' . count($items) . ' items');

                foreach ($items as $item) {
                    $subtotal = $item['cantidad'] * $item['precio'];
                    $ventaCreada = Venta::create([
                        'id_mascota' => $datos['id_mascota'],
                        'tipo_item' => $item['tipo'],
                        'id_item' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $subtotal,
                        'medio_pago' => $datos['medio_pago'],
                        'tipo_negocio' => $item['tipo_negocio'] ?? 'clinica',
                        'fecha_venta' => $fecha_venta,
                    ]);
                    \Log::info('  - Creada venta ID: ' . $ventaCreada->id_venta . ' para item: ' . $item['nombre']);
                }
            }

            // 3. Actualizar Stock
            foreach ($items as $item) {
                if ($item['tipo'] == 'producto') {
                    $producto = Producto::find($item['id']);
                    $producto->stock -= $item['cantidad'];
                    $producto->save();
                }
            }

            // 4. Confirmar transacción
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Venta registrada correctamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el detalle de una venta (ticket).
     * Reemplaza 'modules/detalle_venta.php' [cite: 652-663]
     */
    public function show(Venta $venta)
    {
        // Cargar relaciones necesarias
        $venta->load(['mascota.cliente', 'producto', 'servicio']);

        // Obtener todos los items de esta venta (todas las ventas con la misma fecha y mascota)
        $todasLasVentas = Venta::with(['producto', 'servicio'])
            ->where('id_mascota', $venta->id_mascota)
            ->where('fecha_venta', $venta->fecha_venta)
            ->get();

        $items = [];
        $subtotalTotal = 0;

        // Agregar todos los items de esta transacción de venta
        foreach ($todasLasVentas as $ventaItem) {
            $descripcion = '';
            if ($ventaItem->tipo_item === 'producto' && $ventaItem->producto) {
                $descripcion = $ventaItem->producto->nombre;
            } elseif ($ventaItem->tipo_item === 'servicio' && $ventaItem->servicio) {
                $descripcion = $ventaItem->servicio->nombre;
            } else {
                $descripcion = 'Item no disponible';
            }

            $items[] = [
                'descripcion' => $descripcion,
                'cantidad' => $ventaItem->cantidad,
                'precio' => $ventaItem->precio_unitario,
                'total' => $ventaItem->cantidad * $ventaItem->precio_unitario
            ];

            $subtotalTotal += $ventaItem->cantidad * $ventaItem->precio_unitario;
        }

        // Obtener datos de configuración
        $nombre_negocio = Configuracion::where('clave', 'nombre_negocio')->first()->valor ?? 'VETERINARIA';
        $direccion = Configuracion::where('clave', 'direccion')->first()->valor ?? '';
        $ruc = Configuracion::where('clave', 'ruc')->first()->valor ?? '';

        // Obtener logo
        $logo_src = asset('images/usuario/user.svg');
        try {
            $logo_record = DB::table('logo')->where('id', 1)->first();
            if ($logo_record && !empty($logo_record->imagen)) {
                $logo_src = 'data:image/jpeg;base64,' . $logo_record->imagen;
            }
        } catch (\Exception $e) {
            // Logo table might not exist
        }

        // Convertir total a letras usando luecano/numero-a-letras
        $formatter = new NumeroALetras();
        $total_letras = $formatter->toMoney($subtotalTotal, 2, 'SOLES', 'CENTIMOS');

        // Obtener pagos
        $pagos = PagoVenta::where('id_venta', $venta->id_venta)->get();

        // Generar PDF con dompdf
        $pdf = Pdf::loadView('ventas.ticket_pdf', compact(
            'venta',
            'items',
            'nombre_negocio',
            'direccion',
            'ruc',
            'logo_src',
            'total_letras',
            'subtotalTotal',
            'pagos'
        ));

        // Configurar el papel para ticket (80mm de ancho x tamaño ajustado al contenido)
        // 80mm = 226.77pt de ancho, altura ajustada para tickets
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait'); // Altura reducida a 200mm

        return $pdf->stream('ticket_' . $venta->id_venta . '.pdf');
    }

    /**
     * Exporta las ventas a Excel
     */
    public function exportar(Request $request)
    {
        // Verificar permiso usando trait
        if ($response = $this->checkExportPermission('puede_exportar_ventas', 'No tienes permiso para exportar ventas.')) {
            return $response;
        }

        // Validar que las fechas sean requeridas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.required' => 'La fecha final es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha de inicio.',
        ]);

        $fecha_inicio = $request->query('fecha_inicio');
        $fecha_fin = $request->query('fecha_fin');
        $buscar = $request->query('buscar');
        $filtro_fecha = $request->query('filtro_fecha');

        $query = Venta::with(['mascota.cliente', 'producto', 'servicio'])
            ->orderBy('fecha_venta', 'desc');

        // Filtrar por rango de fechas personalizado
        if ($fecha_inicio && $fecha_fin) {
            $query->whereBetween('fecha_venta', [
                Carbon::parse($fecha_inicio)->startOfDay(),
                Carbon::parse($fecha_fin)->endOfDay()
            ]);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('mascota', function ($sq) use ($buscar) {
                    $sq->where('nombre', 'LIKE', "%{$buscar}%");
                })
                    ->orWhereHas('mascota.cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('producto', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('servicio', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        if ($filtro_fecha) {
            $hoy = Carbon::today();
            switch ($filtro_fecha) {
                case 'hoy':
                    $query->whereDate('fecha_venta', $hoy);
                    break;
                case 'semana':
                    $query->whereBetween('fecha_venta', [$hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek()]);
                    break;
                case 'mes':
                    $query->whereMonth('fecha_venta', $hoy->month)
                        ->whereYear('fecha_venta', $hoy->year);
                    break;
            }
        }

        $ventas = $query->get();

        // Preparar datos para exportación
        $data = [];
        $totalGeneral = 0;

        foreach ($ventas as $venta) {
            $cliente = $venta->mascota->cliente->nombre . ' ' . $venta->mascota->cliente->apellido;
            $mascota = $venta->mascota->nombre;
            $item = $venta->producto ? $venta->producto->nombre : ($venta->servicio ? $venta->servicio->nombre : 'N/A');

            $data[] = [
                $venta->id_venta,
                $venta->fecha_venta->format('d/m/Y H:i'),
                $cliente,
                $mascota,
                $item,
                $venta->cantidad,
                ucfirst($venta->medio_pago),
                number_format($venta->subtotal, 2)
            ];

            $totalGeneral += $venta->subtotal;
        }

        // Agregar fila de total
        $data[] = ['', '', '', '', '', '', 'TOTAL:', number_format($totalGeneral, 2)];

        // Exportar usando trait (elimina ~60 líneas de código duplicado)
        return $this->exportToExcel(
            'REPORTE DE VENTAS',
            ['ID', 'Fecha', 'Cliente', 'Mascota', 'Producto/Servicio', 'Cantidad', 'Medio Pago', 'Subtotal'],
            $data,
            'ventas_' . date('YmdHis') . '.xlsx'
        );
    }

    /**
     * Busca productos y servicios para el autocompletado de ventas.
     * Reemplaza 'modules/buscar_productos_servicios.php' [cite: 273-277]
     */
    public function buscarItems(Request $request)
    {
        $termino = $request->query('query');
        $resultados = [];

        // Buscar productos
        $productos = Producto::where('nombre', 'LIKE', "%{$termino}%")
            ->where('stock', '>', 0)
            ->limit(5)
            ->get();

        foreach ($productos as $producto) {
            $resultados[] = [
                'id' => $producto->id_producto,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'stock' => $producto->stock,
                'tipo' => 'producto',
                'tipo_negocio' => $producto->tipo ?? 'clinica',
            ];
        }

        // Buscar servicios
        $servicios = Servicio::where('nombre', 'LIKE', "%{$termino}%")
            ->limit(5)
            ->get();

        foreach ($servicios as $servicio) {
            $resultados[] = [
                'id' => $servicio->id_servicio,
                'nombre' => $servicio->nombre,
                'precio' => $servicio->precio,
                'stock' => null, // Servicios no tienen stock
                'tipo' => 'servicio',
                'tipo_negocio' => $servicio->tipo ?? 'clinica',
            ];
        }

        return response()->json($resultados);
    }

    /**
     * Obtiene el detalle de una venta en formato JSON para el modal
     */
    public function detalle($id)
    {
        $venta = Venta::with(['mascota.cliente', 'producto', 'servicio'])->findOrFail($id);

        // Obtener todos los items de esta venta (todas las ventas con la misma fecha y mascota)
        // Esto es necesario porque cada item se guarda como un registro separado en la tabla ventas
        $todasLasVentas = Venta::with(['producto', 'servicio'])
            ->where('id_mascota', $venta->id_mascota)
            ->where('fecha_venta', $venta->fecha_venta)
            ->get();

        $items = [];
        $subtotalTotal = 0;

        // Agregar todos los items de esta transacción de venta
        foreach ($todasLasVentas as $ventaItem) {
            $descripcion = '';
            if ($ventaItem->tipo_item === 'producto' && $ventaItem->producto) {
                $descripcion = $ventaItem->producto->nombre;
            } elseif ($ventaItem->tipo_item === 'servicio' && $ventaItem->servicio) {
                $descripcion = $ventaItem->servicio->nombre;
            } else {
                $descripcion = 'Item no disponible';
            }

            $items[] = [
                'descripcion' => $descripcion,
                'cantidad' => $ventaItem->cantidad,
                'precio' => $ventaItem->precio_unitario,
                'total' => $ventaItem->cantidad * $ventaItem->precio_unitario,
            ];

            $subtotalTotal += $ventaItem->cantidad * $ventaItem->precio_unitario;
        }

        // Obtener datos de configuración
        $nombre_negocio = Configuracion::where('clave', 'nombre_negocio')->first()->valor ?? 'VETERINARIA';
        $direccion = Configuracion::where('clave', 'direccion')->first()->valor ?? '';
        $ruc = Configuracion::where('clave', 'ruc')->first()->valor ?? '';

        // Obtener logo
        $logo_src = asset('images/usuario/user.svg');
        try {
            $logo_record = DB::table('logo')->where('id', 1)->first();
            if ($logo_record && !empty($logo_record->imagen)) {
                $logo_src = 'data:image/jpeg;base64,' . $logo_record->imagen;
            }
        } catch (\Exception $e) {
            // Logo table might not exist
        }

        // Obtener pagos
        $pagos = PagoVenta::where('id_venta', $venta->id_venta)->get();

        // Convertir total a letras usando luecano/numero-a-letras
        $formatter = new NumeroALetras();
        $total_letras = $formatter->toMoney($subtotalTotal, 2, 'SOLES', 'CENTIMOS');

        return response()->json([
            'venta' => [
                'id_venta' => $venta->id_venta,
                'subtotal' => $subtotalTotal, // Usar el total calculado de todos los items
                'medio_pago' => $venta->medio_pago,
            ],
            'fecha' => $venta->fecha_venta->format('d-m-Y'),
            'hora' => $venta->fecha_venta->format('H:i:s A'),
            'mascota' => [
                'nombre' => $venta->mascota->nombre ?? 'N/A',
            ],
            'cliente' => [
                'nombre' => $venta->mascota->cliente->nombre ?? 'N/A',
                'apellido' => $venta->mascota->cliente->apellido ?? '',
                'dni' => $venta->mascota->cliente->dni ?? 'N/A',
                'telefono' => $venta->mascota->cliente->telefono ?? '',
            ],
            'items' => $items,
            'configuracion' => [
                'nombre_negocio' => $nombre_negocio,
                'direccion' => $direccion,
                'ruc' => $ruc,
            ],
            'logo_src' => $logo_src,
            'pagos' => $pagos,
            'total_letras' => $total_letras,
        ]);
    }
}