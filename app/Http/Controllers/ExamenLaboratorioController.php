<?php

namespace App\Http\Controllers;

use App\Models\ExamenLaboratorio;
use App\Models\Mascota;
use App\Models\Laboratorio;
use App\Models\ExamenCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExamenLaboratorioController extends Controller
{
    /**
     * Muestra la lista de exámenes (la página principal).
     * [cite_start]Reemplaza 'modules/examenes.php' (GET) [cite: 884-925]
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar_termino');

        $query = ExamenLaboratorio::with(['mascota.cliente'])
            ->orderBy('created_at', 'desc');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('laboratorio', 'LIKE', "%{$buscar}%")
                    ->orWhere('tipo_analisis', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('mascota', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('mascota.cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $examenes = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        // Estadísticas
        $stats = [
            'total_examenes' => ExamenLaboratorio::count(),
            'examenes_pagados' => ExamenLaboratorio::where('pagado', 1)->count(),
            'subidos_historia' => ExamenLaboratorio::where('subido_historia', 1)->count(),
            'lecturas_realizadas' => ExamenLaboratorio::where('lectura_realizada', 1)->count(),
        ];

        // Obtener laboratorios y tipos de análisis para los selectores
        $laboratorios = Laboratorio::orderBy('nombre')->get();
        $tiposAnalisis = ExamenCatalogo::orderBy('nombre')->get();

        return view('examenes.index', compact('examenes', 'stats', 'laboratorios', 'tiposAnalisis'));
    }

    /**
     * Guarda un nuevo examen (lógica del modal).
     * Reemplaza la lógica POST de 'modules/examenes.php'
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mascota' => 'required|integer|exists:mascotas,id_mascota',
            'laboratorio' => 'required|string|max:255',
            'tipo_analisis' => 'required|string|max:255',
            'costo' => 'required|numeric|min:0',
            'fecha_envio' => 'required|date|after_or_equal:today',
            'pagado' => 'nullable|boolean',
            'subido_historia' => 'nullable|boolean',
            'lectura_realizada' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            // Convertir 'on' o '1' a booleano
            $datos['pagado'] = $request->has('pagado');
            $datos['subido_historia'] = $request->has('subido_historia');
            $datos['lectura_realizada'] = $request->has('lectura_realizada');

            ExamenLaboratorio::create($datos);
            return response()->json(['success' => true, 'message' => 'Examen registrado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición (para AJAX en modal).
     * [cite_start]Reemplaza 'modules/editar_examen.php' (GET) [cite: 711-731]
     */
    public function edit(ExamenLaboratorio $examen)
    {
        $examen->load(['mascota.cliente']);
        return view('examenes.partials.edit_modal_content', ['examen' => $examen]);
    }

    /**
     * Actualiza un examen.
     * [cite_start]Reemplaza 'modules/editar_examen.php' (POST) [cite: 708-710]
     */
    public function update(Request $request, ExamenLaboratorio $examen)
    {
        $validator = Validator::make($request->all(), [
            'laboratorio' => 'required|string|max:255',
            'tipo_analisis' => 'required|string|max:255',
            'costo' => 'required|numeric|min:0',
            'fecha_envio' => 'required|date',
            'pagado' => 'nullable|boolean',
            'subido_historia' => 'nullable|boolean',
            'lectura_realizada' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            $datos['pagado'] = $request->has('pagado');
            $datos['subido_historia'] = $request->has('subido_historia');
            $datos['lectura_realizada'] = $request->has('lectura_realizada');

            $examen->update($datos);
            return response()->json(['success' => true, 'message' => 'Examen actualizado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un examen.
     * [cite_start]Reemplaza 'modules/eliminar_examen.php' [cite: 880]
     */
    public function destroy(ExamenLaboratorio $examen)
    {
        try {
            $examen->delete();
            return response()->json(['success' => true, 'message' => 'Examen eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta los exámenes a Excel
     */
    public function exportar(Request $request)
    {
        $buscar = $request->query('buscar_termino');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');

        $query = ExamenLaboratorio::with(['mascota.cliente'])
            ->orderBy('created_at', 'desc');

        // Filtrar por rango de fechas
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_envio', [$fechaInicio, $fechaFin]);
        }

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('laboratorio', 'LIKE', "%{$buscar}%")
                    ->orWhere('tipo_analisis', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('mascota', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('mascota.cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        $examenes = $query->get();

        // Crear spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->setCellValue('A1', 'LISTA DE EXÁMENES DE LABORATORIO');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Encabezados
        $headers = ['ID', 'Mascota', 'Propietario', 'Laboratorio', 'Tipo Análisis', 'Costo', 'Fecha Envío', 'Pagado', 'Subido Historia', 'Lectura'];
        $sheet->fromArray($headers, null, 'A3');

        // Estilo de encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ];
        $sheet->getStyle('A3:J3')->applyFromArray($headerStyle);

        // Datos
        $row = 4;
        foreach ($examenes as $examen) {
            $sheet->setCellValue('A' . $row, $examen->id_examen_lab);
            $sheet->setCellValue('B' . $row, $examen->mascota->nombre ?? 'N/A');
            $sheet->setCellValue('C' . $row, ($examen->mascota->cliente->nombre ?? '') . ' ' . ($examen->mascota->cliente->apellido ?? ''));
            $sheet->setCellValue('D' . $row, $examen->laboratorio);
            $sheet->setCellValue('E' . $row, $examen->tipo_analisis);
            $sheet->setCellValue('F' . $row, 'S/ ' . number_format($examen->costo, 2));
            $sheet->setCellValue('G' . $row, $examen->fecha_envio->format('d/m/Y'));
            $sheet->setCellValue('H' . $row, $examen->pagado ? 'Sí' : 'No');
            $sheet->setCellValue('I' . $row, $examen->subido_historia ? 'Sí' : 'No');
            $sheet->setCellValue('J' . $row, $examen->lectura_realizada ? 'Sí' : 'No');

            $row++;
        }

        // Aplicar bordes a toda la tabla
        $sheet->getStyle('A3:J' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);

        // Ajustar ancho de columnas
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $fileName = 'examenes_' . date('Y-m-d_His') . '.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}