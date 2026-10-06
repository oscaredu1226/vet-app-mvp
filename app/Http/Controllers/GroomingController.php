<?php

namespace App\Http\Controllers;

use App\Models\Grooming;
use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Servicio;
use App\Models\HistoriaBano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GroomingController extends Controller
{
    /**
     * Muestra los turnos de hoy o de una fecha específica
     */
    public function turnosHoy(Request $request)
    {
        $buscar = $request->query('buscar');
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        $query = Grooming::with(['cliente', 'mascota.ultimaHistoriaBano', 'servicio'])
            ->whereDate('fecha', $fecha)
            ->orderBy('numero_turno', 'asc');

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->whereHas('mascota', function ($query) use ($buscar) {
                    $query->where('nombre', 'LIKE', "%{$buscar}%");
                })->orWhereHas('cliente', function ($query) use ($buscar) {
                    $query->where('nombre', 'LIKE', "%{$buscar}%")
                          ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                });
            });
        }

        $perPage = $request->input('per_page', 50);
        $groomings = $query->paginate($perPage)->appends([
            'per_page' => $perPage,
            'fecha' => $fecha,
            'buscar' => $buscar
        ])->withQueryString();

        return view('grooming.turnos-hoy', compact('groomings', 'fecha'));
    }

    /**
     * Muestra los turnos programados (fechas futuras)
     */
    public function programados(Request $request)
    {
        $buscar = $request->query('buscar');

        $query = Grooming::with(['cliente', 'mascota', 'servicio'])
            ->programados()
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_entrada', 'asc');

        if ($buscar) {
            $query->whereHas('mascota', function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%");
            })->orWhereHas('cliente', function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('apellido', 'LIKE', "%{$buscar}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $groomings = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        return view('grooming.programados', compact('groomings'));
    }

    /**
     * Muestra el formulario de creación
     */
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $servicios = Servicio::orderBy('nombre')->get();
        
        return view('grooming.create', compact('clientes', 'servicios'));
    }

    /**
     * Guarda un nuevo turno de grooming
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'id_mascota' => 'required|exists:mascotas,id_mascota',
            'id_servicio' => 'required|exists:servicios,id_servicio',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'observaciones' => 'nullable|string',
        ], [
            'id_cliente.required' => 'Debe seleccionar un cliente.',
            'id_mascota.required' => 'Debe seleccionar una mascota.',
            'id_servicio.required' => 'Debe seleccionar un servicio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            // Obtener el último número de turno para la fecha seleccionada
            $ultimoTurno = Grooming::whereDate('fecha', $request->fecha)->max('numero_turno');
            $numeroTurno = $ultimoTurno ? $ultimoTurno + 1 : 1;

            Grooming::create([
                'numero_turno' => $numeroTurno,
                'id_cliente' => $request->id_cliente,
                'id_mascota' => $request->id_mascota,
                'id_servicio' => $request->id_servicio,
                'fecha' => $request->fecha,
                'hora_entrada' => $request->hora_entrada,
                'observaciones' => $request->observaciones,
                'estado' => 'PENDIENTE',
            ]);

            return response()->json(['success' => true, 'message' => 'Turno de grooming registrado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición
     */
    public function edit(Grooming $grooming)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $servicios = Servicio::orderBy('nombre')->get();
        $mascotas = Mascota::where('id_cliente', $grooming->id_cliente)->orderBy('nombre')->get();
        
        return view('grooming.partials.edit_modal_content', compact('grooming', 'clientes', 'servicios', 'mascotas'));
    }

    /**
     * Actualiza un turno de grooming
     */
    public function update(Request $request, Grooming $grooming)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'id_mascota' => 'required|exists:mascotas,id_mascota',
            'id_servicio' => 'required|exists:servicios,id_servicio',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i',
            'estado' => 'required|in:PENDIENTE,EN_PROCESO,COMPLETADO,CANCELADO',
            'observaciones' => 'nullable|string',
        ], [
            'id_cliente.required' => 'Debe seleccionar un cliente.',
            'id_mascota.required' => 'Debe seleccionar una mascota.',
            'id_servicio.required' => 'Debe seleccionar un servicio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'hora_entrada.required' => 'La hora de entrada es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $grooming->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Turno actualizado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un turno de grooming
     */
    public function destroy(Grooming $grooming)
    {
        try {
            $grooming->delete();
            return response()->json(['success' => true, 'message' => 'Turno eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene las mascotas de un cliente (AJAX)
     */
    public function getMascotasByCliente(Request $request)
    {
        $mascotas = Mascota::where('id_cliente', $request->id_cliente)
            ->orderBy('nombre')
            ->get(['id_mascota', 'nombre', 'raza']);

        return response()->json($mascotas);
    }

    /**
     * Cambia el estado de un turno a COMPLETADO
     */
    public function completar(Grooming $grooming)
    {
        try {
            $grooming->update([
                'estado' => 'COMPLETADO',
                'hora_salida' => now()->format('H:i:s')
            ]);
            return response()->json(['success' => true, 'message' => 'Turno completado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cambia el estado de un turno a CANCELADO
     */
    public function cancelar(Grooming $grooming)
    {
        try {
            $grooming->update(['estado' => 'CANCELADO']);
            return response()->json(['success' => true, 'message' => 'Turno cancelado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un turno (AJAX)
     */
    public function show(Grooming $grooming)
    {
        $grooming->load(['cliente', 'mascota', 'servicio']);
        return view('grooming.partials.show_modal_content', compact('grooming'));
    }

    /**
     * Exporta los turnos de una fecha a Excel
     */
    public function exportarExcel(Request $request)
    {
        $fecha = $request->query('fecha', now()->format('Y-m-d'));
        $buscar = $request->query('buscar');

        $query = Grooming::with(['cliente', 'mascota', 'servicio'])
            ->whereDate('fecha', $fecha)
            ->orderBy('numero_turno', 'asc');

        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->whereHas('mascota', function ($query) use ($buscar) {
                    $query->where('nombre', 'LIKE', "%{$buscar}%");
                })->orWhereHas('cliente', function ($query) use ($buscar) {
                    $query->where('nombre', 'LIKE', "%{$buscar}%")
                          ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                });
            });
        }

        $groomings = $query->get();

        // Crear el archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', '# Turno');
        $sheet->setCellValue('B1', 'Fecha');
        $sheet->setCellValue('C1', 'Hora Entrada');
        $sheet->setCellValue('D1', 'Cliente');
        $sheet->setCellValue('E1', 'Mascota');
        $sheet->setCellValue('F1', 'Raza');
        $sheet->setCellValue('G1', 'Servicio');
        $sheet->setCellValue('H1', 'Estado');
        $sheet->setCellValue('I1', 'Observaciones');

        // Estilo de encabezados
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Datos
        $row = 2;
        foreach ($groomings as $grooming) {
            $sheet->setCellValue('A' . $row, $grooming->numero_turno);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($grooming->fecha)->format('d-m-Y'));
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($grooming->hora_entrada)->format('h:i A'));
            $sheet->setCellValue('D' . $row, $grooming->cliente->nombre . ' ' . $grooming->cliente->apellido);
            $sheet->setCellValue('E' . $row, $grooming->mascota->nombre);
            $sheet->setCellValue('F' . $row, $grooming->mascota->raza ?? 'N/A');
            $sheet->setCellValue('G' . $row, $grooming->servicio->nombre);
            $sheet->setCellValue('H' . $row, $grooming->estado);
            $sheet->setCellValue('I' . $row, $grooming->observaciones ?? '');
            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'grooming_' . $fecha . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Muestra la historia de baños de una mascota
     */
    public function historiaBanos(Mascota $mascota)
    {
        $historial = HistoriaBano::where('id_mascota', $mascota->id_mascota)
            ->orderBy('fecha_registro', 'desc')
            ->get();

        return view('grooming.historia-banos', compact('mascota', 'historial'));
    }

    /**
     * Guarda un nuevo registro en la historia de baños
     */
    public function storeHistoriaBano(Request $request, Mascota $mascota)
    {
        $validator = Validator::make($request->all(), [
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            HistoriaBano::create([
                'id_mascota' => $mascota->id_mascota,
                'fecha_registro' => now(),
                'muerde' => $request->has('muerde'),
                'agresivo' => $request->has('agresivo'),
                'no_recibir' => $request->has('no_recibir'),
                'tener_cuidado' => $request->has('tener_cuidado'),
                'observaciones' => $request->observaciones,
            ]);

            return response()->json(['success' => true, 'message' => 'Registro de historia de baño guardado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
