<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Cliente;
use App\Http\Traits\FormatsFechaDDMMYYYY;
use App\Http\Traits\ExportsToExcel;
use App\Http\Traits\ChecksExportPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MascotaController extends Controller
{
    use FormatsFechaDDMMYYYY, ExportsToExcel, ChecksExportPermission;
    /**
     * Muestra la lista de mascotas.
     * Reemplaza la lógica principal de 'modules/mascotas.php'
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar');
        $filtro = $request->query('filtro');
        $clienteId = $request->query('cliente');

        // Si se solicita ver mascotas de un cliente específico, usar vista diferente
        if ($clienteId) {
            $cliente = \App\Models\Cliente::findOrFail($clienteId);
            $mascotas = Mascota::where('id_cliente', $clienteId)
                ->orderBy('id_mascota', 'desc')
                ->get();
            return view('mascotas.por-cliente', compact('cliente', 'mascotas'));
        }

        // Estadísticas
        $stats = [
            'total' => Mascota::count(),
            'caninos' => Mascota::where('especie', 'Canino')->count(),
            'felinos' => Mascota::where('especie', 'Felino')->count(),
            'esterilizados' => Mascota::where('esterilizado', 'Si')->count(),
        ];

        // Usamos 'with' para cargar al cliente (propietario) y evitar N+1 queries
        $query = Mascota::with('cliente')->orderBy('id_mascota', 'desc');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('mascotas.nombre', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        // Aplicar filtros
        if ($filtro) {
            switch ($filtro) {
                case 'caninos':
                    $query->where('especie', 'Canino');
                    break;
                case 'felinos':
                    $query->where('especie', 'Felino');
                    break;
                case 'esterilizados':
                    $query->where('esterilizado', 'Si');
                    break;
                case 'no_esterilizados':
                    $query->where('esterilizado', 'No');
                    break;
                case 'fallecidos':
                    $query->where('estado', 'Fallecido');
                    break;
            }
        }

        $perPage = $request->input('per_page', 10);
        $mascotas = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        return view('mascotas.index', compact('mascotas', 'stats'));
    }

    /**
     * Guarda una nueva mascota.
     * Reemplaza la lógica POST de 'modules/mascotas.php'
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subYears(30)->format('Y-m-d'),
            'especie' => 'required|string|max:50',
            'raza' => 'required|string|max:100',
            'genero' => 'required|string|in:Macho,Hembra',
            'esterilizado' => 'required|string|in:Si,No',
        ], [
            'fecha_nacimiento.after_or_equal' => 'La fecha de nacimiento no puede ser mayor a 30 años atrás.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            
            // Convertir fecha de DD-MM-AAAA a YYYY-MM-DD si es necesario
            $datos = $this->convertirFechasAISO($datos, ['fecha_nacimiento']);
            
            $datos['estado'] = 'Activo'; // Estado por defecto

            Mascota::create($datos);
            return response()->json(['success' => true, 'message' => 'Mascota registrada correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición (para AJAX en modal).
     * Reemplaza 'modules/editar_mascota.php' (GET)
     */
    public function edit(Mascota $mascota)
    {
        // $mascota ya viene cargada gracias al Route-Model Binding
        $mascota->load('cliente'); // Carga la info del propietario
        return view('mascotas.partials.edit_modal_content', compact('mascota'));
    }

    /**
     * Actualiza una mascota.
     * Reemplaza 'modules/editar_mascota.php' (POST)
     */
    public function update(Request $request, Mascota $mascota)
    {
        $validator = Validator::make($request->all(), [
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date|before_or_equal:today|after_or_equal:' . now()->subYears(30)->format('Y-m-d'),
            'especie' => 'required|string|max:50',
            'raza' => 'required|string|max:100',
            'genero' => 'required|string|in:Macho,Hembra',
            'esterilizado' => 'required|string|in:Si,No',
            'estado' => 'required|string|in:Activo,Fallecido',
        ], [
            'fecha_nacimiento.after_or_equal' => 'La fecha de nacimiento no puede ser mayor a 30 años atrás.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $datos = $validator->validated();
            
            // Convertir fecha de DD-MM-AAAA a YYYY-MM-DD si es necesario
            $datos = $this->convertirFechasAISO($datos, ['fecha_nacimiento']);
            
            $mascota->update($datos);
            return response()->json(['success' => true, 'message' => 'Mascota actualizada correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina una mascota.
     * Reemplaza 'modules/eliminar_mascota.php'
     */
    public function destroy(Mascota $mascota)
    {
        try {
            // Lógica de seguridad (MEJORADA de tu eliminar_cliente.php)
            // Revisa si la mascota tiene registros relacionados antes de borrar.
            if ($mascota->historiasClinicas()->exists() || $mascota->consultas()->exists() || $mascota->vacunas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar: la mascota tiene historial clínico, consultas o vacunas registradas.'
                ], 400);
            }

            $mascota->delete();
            return response()->json(['success' => true, 'message' => 'Mascota eliminada correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca mascotas para el modal de ventas/citas.
     * Reemplaza 'modules/buscar_mascotas.php'
     */
    public function buscarMascotas(Request $request)
    {
        $query = $request->input('query');

        $mascotas = Mascota::with('cliente')
            ->where('mascotas.nombre', 'LIKE', "%{$query}%")
            ->orWhereHas('cliente', function ($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                    ->orWhere('apellido', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // Devolvemos JSON, el frontend (JS) se encargará de mostrarlo
        return response()->json($mascotas->map(function ($mascota) {
            return [
                'id' => $mascota->id_mascota,
                'nombre' => $mascota->nombre,
                'especie' => $mascota->especie,
                'propietario' => $mascota->cliente->nombre . ' ' . $mascota->cliente->apellido,
                'display' => $mascota->nombre . ' - ' . $mascota->cliente->nombre . ' ' . $mascota->cliente->apellido
            ];
        }));
    }

    /**
     * Exporta las mascotas a Excel
     */
    public function exportar(Request $request)
    {
        // Verificar permiso usando trait
        if ($response = $this->checkExportPermission('puede_exportar_mascotas', 'No tienes permiso para exportar mascotas.')) {
            return $response;
        }

        $buscar = $request->query('buscar');
        $filtro = $request->query('filtro');

        $query = Mascota::with('cliente')->orderBy('nombre');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('mascotas.nombre', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('cliente', function ($sq) use ($buscar) {
                        $sq->where('nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('apellido', 'LIKE', "%{$buscar}%");
                    });
            });
        }

        // Aplicar filtros
        if ($filtro) {
            switch ($filtro) {
                case 'caninos':
                    $query->where('especie', 'Canino');
                    break;
                case 'felinos':
                    $query->where('especie', 'Felino');
                    break;
                case 'esterilizados':
                    $query->where('esterilizado', 'Si');
                    break;
                case 'no_esterilizados':
                    $query->where('esterilizado', 'No');
                    break;
                case 'fallecidos':
                    $query->where('estado', 'Fallecido');
                    break;
            }
        }

        $mascotas = $query->get();

        // Preparar datos para exportación
        $data = [];
        foreach ($mascotas as $mascota) {
            $data[] = [
                $mascota->id_mascota,
                $mascota->nombre,
                $mascota->especie,
                $mascota->raza,
                $mascota->genero,
                Carbon::parse($mascota->fecha_nacimiento)->format('d/m/Y'),
                ($mascota->cliente->nombre ?? '') . ' ' . ($mascota->cliente->apellido ?? ''),
                $mascota->esterilizado ?? 'No',
                $mascota->estado ?? 'Activo'
            ];
        }

        // Exportar usando trait (elimina ~70 líneas de código duplicado)
        return $this->exportToExcel(
            'LISTA DE MASCOTAS',
            ['ID', 'Nombre', 'Especie', 'Raza', 'Género', 'Fecha Nac.', 'Propietario', 'Esterilizado', 'Estado'],
            $data,
            'mascotas_' . date('Y-m-d_His') . '.xlsx'
        );
    }
}