<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Egreso;
use App\Models\Configuracion;
use App\Models\PagoVenta;
use App\Models\TotalCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CajaController extends Controller
{
    /**
     * Muestra la página principal de Caja.
     * Reemplaza 'modules/caja.php' [cite: 282-314]
     */
    public function index(Request $request)
    {
        // Optimizado: usar selectRaw y sum directo en la base de datos
        $ingresos = [
            'Efectivo' => 0,
            'Yape' => 0,
            'Tarjeta' => 0,
            'Transferencia' => 0,
        ];

        // 1. Calcular ingresos de ventas simples (no mixtas) de hoy en caja
        $ventasSimples = Venta::where('en_caja', true)
            ->whereDate('fecha_venta', today())
            ->where('medio_pago', '!=', 'Mixto')
            ->selectRaw('medio_pago, SUM(subtotal) as total')
            ->groupBy('medio_pago')
            ->get();

        foreach ($ventasSimples as $venta) {
            if (isset($ingresos[$venta->medio_pago])) {
                $ingresos[$venta->medio_pago] += $venta->total;
            }
        }

        // 2. Calcular ingresos de pagos mixtos de hoy
        $pagosMixtos = PagoVenta::whereHas('venta', function ($q) {
            $q->where('en_caja', true)->whereDate('fecha_venta', today());
        })
            ->selectRaw('medio_pago, SUM(monto) as total')
            ->groupBy('medio_pago')
            ->get();

        foreach ($pagosMixtos as $pago) {
            if (isset($ingresos[$pago->medio_pago])) {
                $ingresos[$pago->medio_pago] += $pago->total;
            }
        }

        // 3. Calcular egresos del día
        $total_egresos = Egreso::whereDate('fecha', today())->sum('monto');

        // 4. Obtener Total Manual de total_caja
        $ultimo_total = TotalCaja::where('tipo_operacion', 'manual')->latest()->first();
        $total_caja_manual = $ultimo_total ? (float) $ultimo_total->monto : 0.00;

        // 5. Calcular totales (incluye el total manual en el efectivo)
        $total_ingresos = array_sum($ingresos);
        $total_caja_calculado = ($ingresos['Efectivo'] + $total_caja_manual) - $total_egresos;

        // 6. Obtener lista de ventas agrupadas por transacción
        // Primero obtenemos todas las ventas del día
        $ventasDelDia = Venta::with(['mascota:id_mascota,nombre,id_cliente', 'mascota.cliente:id_cliente,nombre,apellido', 'producto:id_producto,nombre', 'servicio:id_servicio,nombre', 'pagos'])
            ->where('en_caja', true)
            ->whereDate('fecha_venta', today())
            ->orderBy('fecha_venta', 'desc')
            ->get();

        // Agrupar por id_mascota + fecha_venta para consolidar items de la misma transacción
        // Cada item tiene un id_venta diferente, pero comparten id_mascota y fecha_venta
        $ventasAgrupadas = $ventasDelDia->groupBy(function ($venta) {
            return $venta->id_mascota . '_' . $venta->fecha_venta;
        })->map(function ($items) {
            $primeraVenta = $items->first();

            // Obtener todos los productos y servicios de esta venta
            $productos = $items->filter(fn($v) => $v->tipo_item === 'producto' && $v->producto)
                ->pluck('producto.nombre')
                ->unique()
                ->toArray();

            $servicios = $items->filter(fn($v) => $v->tipo_item === 'servicio' && $v->servicio)
                ->pluck('servicio.nombre')
                ->unique()
                ->toArray();

            // Combinar productos y servicios
            $todosLosItems = array_merge($productos, $servicios);

            return (object) [
                'id_venta' => $primeraVenta->id_venta,
                'fecha_venta' => $primeraVenta->fecha_venta,
                'mascota' => $primeraVenta->mascota,
                'items_nombres' => $todosLosItems,
                'subtotal' => $items->sum('subtotal'),
                'medio_pago' => $primeraVenta->medio_pago,
                'pagos' => $primeraVenta->pagos,
            ];
        })->values();

        // Totales para el resumen
        $total_ventas_dia_count = $ventasAgrupadas->count();
        $total_ventas_dia_sum = $ventasAgrupadas->sum('subtotal');

        // Paginación manual
        $perPage = $request->input('per_page', 10);
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $historial_ventas = new \Illuminate\Pagination\LengthAwarePaginator(
            $ventasAgrupadas->forPage($currentPage, $perPage),
            $ventasAgrupadas->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => ['per_page' => $perPage]]
        );

        return view('caja.index', compact(
            'ingresos',
            'total_ingresos',
            'total_egresos',
            'total_caja_calculado',
            'total_caja_manual',
            'historial_ventas',
            'total_ventas_dia_count',
            'total_ventas_dia_sum'
        ));
    }

    /**
     * Actualiza el total manual en caja.
     * Reemplaza lógica POST de 'modules/caja.php' [cite: 306-314]
     */
    public function updateTotalCaja(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nuevo_total_caja' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        TotalCaja::create([
            'monto' => $request->input('nuevo_total_caja'),
            'tipo_operacion' => 'manual',
            'concepto' => 'Actualización manual de total en caja',
            'usuario' => null
        ]);

        return back()->with('success', 'Total en caja actualizado correctamente.');
    }

    /**
     * Cierra la caja (elimina ventas y egresos) y genera reporte automático.
     * Reemplaza 'modules/cerrar_caja.php' [cite: 265-268]
     */
    public function cerrarCaja(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Generar el reporte Excel ANTES de cerrar la caja
            $reporte = $this->generarReporteExcel();
            
            // 2. Marcar todas las ventas como fuera de caja
            Venta::where('en_caja', true)->update(['en_caja' => false]);

            // 3. Eliminar egresos
            Egreso::query()->delete();

            // 4. Eliminar pagos asociados
            PagoVenta::query()->delete();

            DB::commit();
            
            // 5. Retornar éxito con el nombre del archivo para descarga
            return response()->json([
                'success' => true, 
                'message' => 'Caja cerrada. Las ventas se guardaron en el historial.',
                'filename' => $reporte['filename'],
                'download_url' => $reporte['download_url']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Genera el reporte Excel y lo guarda en storage.
     * Método privado usado por cerrarCaja y exportarCaja.
     */
    private function generarReporteExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Caja');

        // Obtener datos
        $ventasQuery = Venta::with(['producto', 'servicio', 'pagos'])
            ->where('en_caja', true)
            ->whereDate('fecha_venta', today());
        $ventas = $ventasQuery->get();
        $egresos = Egreso::whereDate('fecha', today())->get();
        $ultimo_total = TotalCaja::where('tipo_operacion', 'manual')->latest()->first();
        $caja_inicial = $ultimo_total ? (float) $ultimo_total->monto : 0.00;

        $ingresos = [
            'Efectivo' => 0,
            'Yape' => 0,
            'Tarjeta' => 0,
            'Transferencia' => 0,
        ];

        $pagosMixtos = PagoVenta::whereHas('venta', function ($q) {
            $q->where('en_caja', true)->whereDate('fecha_venta', today());
        })->get();

        foreach ($ventas as $venta) {
            if ($venta->medio_pago != 'Mixto' && isset($ingresos[$venta->medio_pago])) {
                $ingresos[$venta->medio_pago] += $venta->subtotal;
            }
        }

        foreach ($pagosMixtos as $pago) {
            if (isset($ingresos[$pago->medio_pago])) {
                $ingresos[$pago->medio_pago] += $pago->monto;
            }
        }

        // Calcular ingresos por tipo de negocio
        $ingresosPorTipo = [
            'clinica' => Venta::where('en_caja', true)->whereDate('fecha_venta', today())->where('tipo_negocio', 'clinica')->sum('subtotal'),
            'farmacia' => Venta::where('en_caja', true)->whereDate('fecha_venta', today())->where('tipo_negocio', 'farmacia')->sum('subtotal'),
            'spa' => Venta::where('en_caja', true)->whereDate('fecha_venta', today())->where('tipo_negocio', 'spa')->sum('subtotal'),
            'petshop' => Venta::where('en_caja', true)->whereDate('fecha_venta', today())->where('tipo_negocio', 'petshop')->sum('subtotal'),
        ];

        $total_ingresos = array_sum($ingresos);
        $total_egresos = $egresos->sum('monto');

        // === TÍTULO ===
        $sheet->setCellValue('A1', 'REPORTE DE CUADRE DE CAJA');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Fecha: ' . now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // === CAJA INICIAL ===
        $row = 4;
        $sheet->setCellValue('A' . $row, 'CAJA INICIAL');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($caja_inicial, 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B4C7E7');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->getColor()->setRGB('000000');

        // === RESUMEN DE INGRESOS POR MEDIO DE PAGO ===
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RESUMEN DE INGRESOS');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');

        $row++;
        $sheet->setCellValue('A' . $row, 'Medio de Pago');
        $sheet->setCellValue('B' . $row, 'Total');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');

        foreach ($ingresos as $medio => $total) {
            $row++;
            $sheet->setCellValue('A' . $row, $medio);
            $sheet->setCellValue('B' . $row, 'S/ ' . number_format($total, 2));
        }

        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL INGRESOS');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($total_ingresos, 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');

        // === INGRESOS POR TIPO DE NEGOCIO ===
        $row += 2;
        $sheet->setCellValue('A' . $row, 'INGRESOS POR TIPO DE NEGOCIO');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');

        $row++;
        $sheet->setCellValue('A' . $row, 'Tipo de Negocio');
        $sheet->setCellValue('B' . $row, 'Total');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');

        $row++;
        $sheet->setCellValue('A' . $row, 'Clínica');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($ingresosPorTipo['clinica'], 2));

        $row++;
        $sheet->setCellValue('A' . $row, 'Farmacia');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($ingresosPorTipo['farmacia'], 2));

        $row++;
        $sheet->setCellValue('A' . $row, 'Spa');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($ingresosPorTipo['spa'], 2));

        $row++;
        $sheet->setCellValue('A' . $row, 'Pet Shop');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($ingresosPorTipo['petshop'], 2));

        // === EGRESOS ===
        $row += 2;
        $sheet->setCellValue('A' . $row, 'EGRESOS');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E74C3C');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setRGB('FFFFFF');

        $row++;
        $sheet->setCellValue('A' . $row, 'Descripción');
        $sheet->setCellValue('B' . $row, 'Monto');
        $sheet->setCellValue('C' . $row, 'Fecha');
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4CCCC');

        foreach ($egresos as $egreso) {
            $row++;
            $sheet->setCellValue('A' . $row, $egreso->descripcion);
            $sheet->setCellValue('B' . $row, 'S/ ' . number_format($egreso->monto, 2));
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y'));
        }

        $row++;
        $sheet->setCellValue('A' . $row, 'TOTAL EGRESOS');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($total_egresos, 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4CCCC');

        // === TOTAL EN CAJA (Caja Inicial + Efectivo - Egresos) ===
        $row += 2;
        $total_en_caja = $caja_inicial + $ingresos['Efectivo'] - $total_egresos;
        $sheet->setCellValue('A' . $row, 'TOTAL EN CAJA (Efectivo - Egresos)');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($total_en_caja, 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->getColor()->setRGB('000000');

        // === INGRESO TOTAL (Caja Inicial + Efectivo - Egresos) ===
        $row++;
        $ingreso_total = $caja_inicial + $ingresos['Efectivo'] - $total_egresos;
        $sheet->setCellValue('A' . $row, 'INGRESO TOTAL (Caja Inicial + Efectivo - Egresos)');
        $sheet->setCellValue('B' . $row, 'S/ ' . number_format($ingreso_total, 2));
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row . ':B' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('92D050');
        $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->getColor()->setRGB('FFFFFF');

        // Ajustar anchos de columna
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // Aplicar bordes a todas las celdas con datos
        $sheet->getStyle('A4:C' . $row)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);

        // === CREAR SEGUNDA HOJA CON DETALLE DE VENTAS ===
        $sheet2 = $spreadsheet->createSheet(1);
        $sheet2->setTitle('Detalle de Ventas');
        
        // Título de la segunda hoja
        $sheet2->setCellValue('A1', 'DETALLE DE VENTAS DEL DÍA');
        $sheet2->mergeCells('A1:E1');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet2->setCellValue('A2', 'Fecha: ' . now()->format('d/m/Y H:i:s'));
        $sheet2->mergeCells('A2:E2');
        $sheet2->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Encabezados
        $row2 = 4;
        $sheet2->setCellValue('A' . $row2, 'Fecha');
        $sheet2->setCellValue('B' . $row2, 'Mascota / Propietario');
        $sheet2->setCellValue('C' . $row2, 'Producto/Servicio');
        $sheet2->setCellValue('D' . $row2, 'Subtotal');
        $sheet2->setCellValue('E' . $row2, 'Medio de Pago');
        $sheet2->getStyle('A' . $row2 . ':E' . $row2)->getFont()->setBold(true);
        $sheet2->getStyle('A' . $row2 . ':E' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
        $sheet2->getStyle('A' . $row2 . ':E' . $row2)->getFont()->getColor()->setRGB('FFFFFF');
        
        // Cargar ventas con relaciones y agrupar por mascota + fecha
        $ventasDetalladas = Venta::with(['mascota.cliente', 'producto', 'servicio', 'pagos'])
            ->where('en_caja', true)
            ->whereDate('fecha_venta', today())
            ->orderBy('fecha_venta', 'desc')
            ->get();
        
        // Agrupar ventas por id_mascota + fecha_venta para consolidar items de la misma transacción
        $ventasAgrupadas = $ventasDetalladas->groupBy(function ($venta) {
            return $venta->id_mascota . '_' . $venta->fecha_venta;
        })->map(function ($items) {
            $primeraVenta = $items->first();
            
            // Obtener todos los productos y servicios de esta transacción
            $productos = $items->filter(fn($v) => $v->tipo_item === 'producto' && $v->producto)
                ->pluck('producto.nombre')
                ->unique()
                ->toArray();
            
            $servicios = $items->filter(fn($v) => $v->tipo_item === 'servicio' && $v->servicio)
                ->pluck('servicio.nombre')
                ->unique()
                ->toArray();
            
            // Combinar productos y servicios en una sola lista
            $todosLosItems = array_merge($productos, $servicios);
            
            return (object) [
                'fecha_venta' => $primeraVenta->fecha_venta,
                'mascota' => $primeraVenta->mascota,
                'items_nombres' => $todosLosItems,
                'subtotal' => $items->sum('subtotal'),
                'medio_pago' => $primeraVenta->medio_pago,
                'pagos' => $primeraVenta->pagos,
            ];
        })->values();
        
        // Llenar datos de ventas agrupadas (una fila por transacción)
        $row2++;
        foreach ($ventasAgrupadas as $ventaAgrupada) {
            $fecha = \Carbon\Carbon::parse($ventaAgrupada->fecha_venta)->format('d/m/Y H:i');
            
            // Obtener nombre del cliente y mascota
            $mascotaPropietario = '';
            if ($ventaAgrupada->mascota) {
                $mascotaPropietario = $ventaAgrupada->mascota->nombre;
                if ($ventaAgrupada->mascota->cliente) {
                    $mascotaPropietario .= "\n" . $ventaAgrupada->mascota->cliente->nombre . ' ' . $ventaAgrupada->mascota->cliente->apellido;
                }
            } else {
                $mascotaPropietario = 'Venta general';
            }
            
            // Combinar todos los productos/servicios con saltos de línea
            $productoServicio = implode("\n", $ventaAgrupada->items_nombres);
            
            // Determinar medio de pago
            $medioPago = $ventaAgrupada->medio_pago;
            if ($ventaAgrupada->medio_pago == 'Mixto' && $ventaAgrupada->pagos->count() > 0) {
                $medios = $ventaAgrupada->pagos->pluck('medio_pago')->toArray();
                $medioPago = implode(' + ', $medios);
                $montos = $ventaAgrupada->pagos->map(function($pago) {
                    return $pago->medio_pago . ': S/ ' . number_format($pago->monto, 2);
                })->toArray();
                $medioPago .= "\n" . implode("\n", $montos);
            }
            
            $sheet2->setCellValue('A' . $row2, $fecha);
            $sheet2->setCellValue('B' . $row2, $mascotaPropietario);
            $sheet2->setCellValue('C' . $row2, $productoServicio);
            $sheet2->setCellValue('D' . $row2, 'S/ ' . number_format($ventaAgrupada->subtotal, 2));
            $sheet2->setCellValue('E' . $row2, $medioPago);
            
            // Permitir texto multilínea
            $sheet2->getStyle('B' . $row2)->getAlignment()->setWrapText(true);
            $sheet2->getStyle('C' . $row2)->getAlignment()->setWrapText(true);
            $sheet2->getStyle('E' . $row2)->getAlignment()->setWrapText(true);
            
            $row2++;
        }
        
        // Total de la segunda hoja
        $totalVentas = $ventasAgrupadas->sum('subtotal');
        $sheet2->setCellValue('C' . $row2, 'TOTAL:');
        $sheet2->setCellValue('D' . $row2, 'S/ ' . number_format($totalVentas, 2));
        $sheet2->getStyle('C' . $row2 . ':D' . $row2)->getFont()->setBold(true);
        $sheet2->getStyle('C' . $row2 . ':D' . $row2)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C6E0B4');
        
        // Ajustar anchos de columna
        $sheet2->getColumnDimension('A')->setWidth(18);
        $sheet2->getColumnDimension('B')->setWidth(30);
        $sheet2->getColumnDimension('C')->setWidth(35);
        $sheet2->getColumnDimension('D')->setWidth(15);
        $sheet2->getColumnDimension('E')->setWidth(25);
        
        // Aplicar bordes
        $sheet2->getStyle('A4:E' . $row2)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Volver a la primera hoja como activa
        $spreadsheet->setActiveSheetIndex(0);

        // Asegurar que el directorio existe
        $directory = 'reportes_caja';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Generar nombre de archivo
        $filename = 'reporte_caja_' . now()->format('Y-m-d_His') . '.xlsx';
        $filepath = $directory . '/' . $filename;

        // Guardar archivo
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), 'reporte_caja');
        $writer->save($temp_file);
        Storage::put($filepath, file_get_contents($temp_file));
        unlink($temp_file);

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'download_url' => route('caja.descargarReporte', ['filename' => $filename])
        ];
    }

    /**
     * Lista los reportes de caja almacenados.
     * Solo accesible para administradores.
     */
    public function listarReportes()
    {
        // Verificar que el usuario es administrador
        if (!auth()->user()->isAdmin()) {
            return [];
        }

        // Obtener todos los archivos del directorio
        $archivos = Storage::files('reportes_caja');
        
        // Mapear información de cada archivo
        $reportes = collect($archivos)->map(function ($archivo) {
            return [
                'nombre' => basename($archivo),
                'fecha' => \Carbon\Carbon::createFromTimestamp(Storage::lastModified($archivo))->setTimezone('America/Lima'),
                'tamaño' => Storage::size($archivo),
            ];
        })->sortByDesc('fecha')->values();
        
        return $reportes;
    }

    /**
     * Muestra la página de reportes de caja.
     * Solo accesible para administradores.
     */
    public function mostrarReportes()
    {
        // Verificar que el usuario es administrador
        if (!auth()->user()->isAdmin()) {
            abort(403, 'No tienes permiso para ver los reportes de caja.');
        }

        $reportes = $this->listarReportes();
        
        return view('caja.reportes', compact('reportes'));
    }



    /**
     * Descarga un reporte de caja almacenado.
     * Usuarios normales: solo reportes recientes (últimos 10 minutos).
     * Administradores: todos los reportes.
     */
    public function descargarReporte($filename)
    {
        // Validar que el nombre de archivo sea seguro (evitar directory traversal)
        if (!preg_match('/^reporte_caja_\d{4}-\d{2}-\d{2}_\d{6}\.xlsx$/', $filename)) {
            abort(404, 'Archivo no válido.');
        }

        $filepath = 'reportes_caja/' . $filename;

        // Verificar que el archivo existe
        if (!Storage::exists($filepath)) {
            abort(404, 'El archivo no existe o ha sido eliminado.');
        }

        // Si no es administrador, verificar que el archivo sea reciente (últimos 10 minutos)
        if (!auth()->user()->isAdmin()) {
            $fileModifiedTime = Storage::lastModified($filepath);
            $minutosTranscurridos = (now()->timestamp - $fileModifiedTime) / 60;
            
            if ($minutosTranscurridos > 10) {
                abort(403, 'Solo los administradores pueden descargar reportes antiguos.');
            }
        }

        // Descargar archivo de forma segura
        return Storage::download($filepath, $filename);
    }
}