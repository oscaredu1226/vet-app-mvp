<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Diagnostico;
use App\Models\Tratamiento;
use App\Models\Receta;
use App\Models\ExamenCatalogo;
use App\Models\Laboratorio;
use App\Models\TipoCirugia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function index(Request $request, $seccion = null)
    {
        $nombre = Configuracion::where('clave', 'nombre_negocio')->first();
        $direccion = Configuracion::where('clave', 'direccion')->first();
        $ruc = Configuracion::where('clave', 'ruc')->first();
        $telefono = Configuracion::where('clave', 'telefono')->first();

        $perPage = $request->input('per_page', 10);

        $diagnosticos = Diagnostico::orderBy('nombre')->paginate($perPage, ['*'], 'page_diagnosticos')->appends(['per_page' => $perPage])->withQueryString();
        $tratamientos = Tratamiento::orderBy('nombre')->paginate($perPage, ['*'], 'page_tratamientos')->appends(['per_page' => $perPage])->withQueryString();
        $recetas = Receta::orderBy('nombre')->paginate($perPage, ['*'], 'page_recetas')->appends(['per_page' => $perPage])->withQueryString();
        $examenes = ExamenCatalogo::orderBy('nombre')->paginate($perPage, ['*'], 'page_examenes')->appends(['per_page' => $perPage])->withQueryString();
        $laboratorios = Laboratorio::orderBy('nombre')->paginate($perPage, ['*'], 'page_laboratorios')->appends(['per_page' => $perPage])->withQueryString();
        $tiposCirugia = TipoCirugia::orderBy('nombre')->paginate($perPage, ['*'], 'page_cirugias')->appends(['per_page' => $perPage])->withQueryString();

        return view('configuracion.index', compact('nombre', 'direccion', 'ruc', 'telefono', 'diagnosticos', 'tratamientos', 'recetas', 'examenes', 'laboratorios', 'tiposCirugia', 'seccion'));
    }

    public function update(Request $request)
    {
        // Validar que solo administradores puedan editar
        if (!auth()->user()->isAdmin()) {
            return back()->with('error', 'No tienes permiso para editar la configuración de la veterinaria.');
        }

        $validator = Validator::make($request->all(), [
            'nombre_negocio' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'ruc' => 'required|numeric|digits:11',
            'telefono' => 'required|numeric|digits:9',
        ], [
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.numeric' => 'El RUC solo debe contener números.',
            'telefono.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
            'telefono.numeric' => 'El teléfono solo debe contener números.'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Configuracion::updateOrCreate(
            ['clave' => 'nombre_negocio'],
            ['valor' => $request->nombre_negocio]
        );

        Configuracion::updateOrCreate(
            ['clave' => 'direccion'],
            ['valor' => $request->direccion]
        );

        Configuracion::updateOrCreate(
            ['clave' => 'ruc'],
            ['valor' => $request->ruc]
        );

        Configuracion::updateOrCreate(
            ['clave' => 'telefono'],
            ['valor' => $request->telefono]
        );

        return back()->with('success', 'Datos de la veterinaria actualizados correctamente.');
    }

    // Diagnósticos
    public function storeDiagnostico(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:diagnosticos,nombre']);
        Diagnostico::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Diagnóstico agregado correctamente.');
    }

    public function deleteDiagnostico($id)
    {
        Diagnostico::findOrFail($id)->delete();
        return back()->with('success', 'Diagnóstico eliminado correctamente.');
    }

    // Tratamientos
    public function storeTratamiento(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tratamientos,nombre']);
        Tratamiento::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Tratamiento agregado correctamente.');
    }

    public function deleteTratamiento($id)
    {
        Tratamiento::findOrFail($id)->delete();
        return back()->with('success', 'Tratamiento eliminado correctamente.');
    }

    // Recetas
    public function storeReceta(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:recetas,nombre']);
        Receta::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Receta agregada correctamente.');
    }

    public function deleteReceta($id)
    {
        Receta::findOrFail($id)->delete();
        return back()->with('success', 'Receta eliminada correctamente.');
    }

    // Exámenes
    public function storeExamen(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:examenes_catalogo,nombre']);
        ExamenCatalogo::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Examen agregado correctamente.');
    }

    public function deleteExamen($id)
    {
        ExamenCatalogo::findOrFail($id)->delete();
        return back()->with('success', 'Examen eliminado correctamente.');
    }

    // Búsquedas para consultas
    public function buscarDiagnosticos(Request $request)
    {
        $query = $request->query('query', '');
        $diagnosticos = Diagnostico::where('nombre', 'LIKE', "%{$query}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();
        return response()->json($diagnosticos);
    }

    public function buscarTratamientos(Request $request)
    {
        $query = $request->query('query', '');
        $tratamientos = Tratamiento::where('nombre', 'LIKE', "%{$query}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();
        return response()->json($tratamientos);
    }

    public function buscarExamenes(Request $request)
    {
        $query = $request->query('query', '');
        $examenes = ExamenCatalogo::where('nombre', 'LIKE', "%{$query}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();
        return response()->json($examenes);
    }

    public function buscarRecetas(Request $request)
    {
        $query = $request->query('query', '');
        $recetas = Receta::where('nombre', 'LIKE', "%{$query}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();
        return response()->json($recetas);
    }

    // Laboratorios
    public function storeLaboratorio(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:laboratorios,nombre']);
        Laboratorio::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Laboratorio agregado correctamente.');
    }

    public function deleteLaboratorio($id)
    {
        Laboratorio::findOrFail($id)->delete();
        return back()->with('success', 'Laboratorio eliminado correctamente.');
    }

    // Tipos de Cirugía
    public function storeTipoCirugia(Request $request)
    {
        $request->validate(['nombre' => 'required|string|max:255|unique:tipos_cirugia,nombre']);
        TipoCirugia::create(['nombre' => $request->nombre]);
        return back()->with('success', 'Tipo de cirugía agregado correctamente.');
    }

    public function deleteTipoCirugia($id)
    {
        TipoCirugia::findOrFail($id)->delete();
        return back()->with('success', 'Tipo de cirugía eliminado correctamente.');
    }
}
