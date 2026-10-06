<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use Illuminate\Http\Request;

class HistoriaClinicaController extends Controller
{
    /**
     * Muestra el dashboard de la historia clínica de una mascota.
     */
    public function show(Mascota $mascota)
    {
        // 1. Carga la mascota con sus registros ordenados por FECHA DEL EVENTO DESCENDENTE (más reciente primero)
        $mascota->load([
            'cliente',
            'consultas' => fn($q) => $q->with('archivos')->orderBy('fecha', 'desc'),
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             'vacunas' => fn($q) => $q->with('archivos')->orderBy('fecha', 'desc'),
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             'desparasitaciones' => fn($q) => $q->with('archivos')->orderBy('fecha', 'desc'),
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             'antipulgas' => fn($q) => $q->with('archivos')->orderBy('fecha', 'desc'),
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             'cirugias' => fn($q) => $q->with('archivos')->orderBy('fecha', 'desc')
        ]);

        // 2. Combinar todos los registros
        // Ordenamos por fecha del evento DESCENDENTE (más nuevo primero)
        $timeline = collect()
            ->merge($mascota->consultas->map(fn($item) => ['tipo' => 'Consulta', 'fecha' => $item->fecha, 'created_at' => $item->created_at, 'id' => $item->id_consulta, 'datos' => $item]))
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             ->merge($mascota->vacunas->map(fn($item) => ['tipo' => 'Vacuna', 'fecha' => $item->fecha, 'created_at' => $item->created_at, 'id' => $item->id_vacuna, 'datos' => $item]))
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             ->merge($mascota->desparasitaciones->map(fn($item) => ['tipo' => 'Desparasitación', 'fecha' => $item->fecha, 'created_at' => $item->created_at, 'id' => $item->id_desparasitacion, 'datos' => $item]))
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             ->merge($mascota->antipulgas->map(fn($item) => ['tipo' => 'Antipulgas', 'fecha' => $item->fecha, 'created_at' => $item->created_at, 'id' => $item->id_antipulgas, 'datos' => $item]))
// MVP_POSTERIOR: Historial especializado fuera del MVP
// MVP_POSTERIOR |             ->merge($mascota->cirugias->map(fn($item) => ['tipo' => 'Cirugía', 'fecha' => $item->fecha, 'created_at' => $item->created_at, 'id' => $item->id_cirugia, 'datos' => $item]))
            ->sortByDesc(function ($registro) {
                // Ordenar por fecha del evento médico de más nuevo a más antiguo
                return \Carbon\Carbon::parse($registro['fecha'])->timestamp;
            });

        // Obtener el último peso registrado (el primero de la lista porque está ordenado por más reciente)
        $ultimoPeso = $timeline->pluck('datos.peso')->filter(fn($peso) => $peso > 0)->first() ??
            $timeline->pluck('datos.peso_pre')->filter(fn($peso) => $peso > 0)->first() ?? 0;

        // Paginación manual de la colección
        $perPage = 5; // Solo mostrar 5 por página
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $timeline->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $timeline = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $timeline->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('historia.show', compact('mascota', 'timeline', 'ultimoPeso'));
    }
}