<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Consulta;
use App\Models\Vacuna;
use App\Models\Desparasitacion;
use App\Models\Antipulga;
use App\Models\Cirugia;
use App\Models\Configuracion;
use App\Models\Logo;
use Illuminate\Http\Request;
use PDF;

class PDFHistoriaController extends Controller
{
    public function descargarConsulta($mascotaId, $consultaId)
    {
        $mascota = Mascota::with('cliente')->findOrFail($mascotaId);
        $consulta = Consulta::findOrFail($consultaId);

        // Obtener datos de configuración
        $nombre_negocio = Configuracion::where('clave', 'nombre_negocio')->first();
        $direccion = Configuracion::where('clave', 'direccion')->first();
        $ruc = Configuracion::where('clave', 'ruc')->first();
        $telefono = Configuracion::where('clave', 'telefono')->first();

        // Obtener logo
        $logo = Logo::first();
        $logo_src = null;
        if ($logo && $logo->imagen) {
            // Si ya tiene el prefijo data:, usarlo directamente
            if (strpos($logo->imagen, 'data:') === 0) {
                $logo_src = $logo->imagen;
            } else {
                // Si es base64 sin prefijo, agregarlo
                $logo_src = 'data:image/jpeg;base64,' . $logo->imagen;
            }
        }

        $cliente = $mascota->cliente;

        // Datos a pasar a la vista
        $data = [
            'mascota' => $mascota,
            'consulta' => $consulta,
            'cliente' => $cliente,
            'nombre_negocio' => $nombre_negocio->valor ?? 'CLÍNICA VETERINARIA',
            'direccion' => $direccion->valor ?? '',
            'ruc' => $ruc->valor ?? '',
            'telefono' => $telefono->valor ?? '',
            'logo' => $logo_src,
            'medico' => $consulta->medico ?? 'No especificado',
        ];

        // Generar PDF desde la vista
        $pdf = PDF::loadView('pdfs.historia_clinica', $data);
        
        return $pdf->download('Historia_Clinica_' . $mascota->nombre . '_' . date('Y-m-d') . '.pdf');
    }

    public function descargarVacuna($mascotaId, $vacunaId)
    {
        $mascota = Mascota::with('cliente')->findOrFail($mascotaId);
        $vacuna = Vacuna::findOrFail($vacunaId);

        return $this->generarPDF($mascota, 'Vacuna', $vacuna);
    }

    public function descargarDesparasitacion($mascotaId, $desparasitacionId)
    {
        $mascota = Mascota::with('cliente')->findOrFail($mascotaId);
        $desparasitacion = Desparasitacion::findOrFail($desparasitacionId);

        return $this->generarPDF($mascota, 'Desparasitación', $desparasitacion);
    }

    public function descargarAntipulga($mascotaId, $antipulgaId)
    {
        $mascota = Mascota::with('cliente')->findOrFail($mascotaId);
        $antipulga = Antipulga::findOrFail($antipulgaId);

        return $this->generarPDF($mascota, 'Antipulga', $antipulga);
    }

    public function descargarCirugia($mascotaId, $cirugiaId)
    {
        $mascota = Mascota::with('cliente')->findOrFail($mascotaId);
        $cirugia = Cirugia::findOrFail($cirugiaId);

        return $this->generarPDF($mascota, 'Cirugía', $cirugia);
    }

    private function generarPDF($mascota, $tipo, $registro)
    {
        // Obtener datos de configuración
        $nombre_negocio = Configuracion::where('clave', 'nombre_negocio')->first();
        $direccion = Configuracion::where('clave', 'direccion')->first();
        $ruc = Configuracion::where('clave', 'ruc')->first();
        $telefono = Configuracion::where('clave', 'telefono')->first();

        // Obtener logo
        $logo = Logo::first();
        $logo_src = null;
        if ($logo && $logo->imagen) {
            if (strpos($logo->imagen, 'data:') === 0) {
                $logo_src = $logo->imagen;
            } else {
                $logo_src = 'data:image/jpeg;base64,' . $logo->imagen;
            }
        }

        $cliente = $mascota->cliente;

        // Datos a pasar a la vista
        $data = [
            'mascota' => $mascota,
            'registro' => $registro,
            'tipo' => $tipo,
            'cliente' => $cliente,
            'nombre_negocio' => $nombre_negocio->valor ?? 'CLÍNICA VETERINARIA',
            'direccion' => $direccion->valor ?? '',
            'ruc' => $ruc->valor ?? '',
            'telefono' => $telefono->valor ?? '',
            'logo' => $logo_src,
        ];

        // Determinar la vista basada en el tipo
        $viewMap = [
            'Vacuna' => 'pdfs.vacuna',
            'Desparasitación' => 'pdfs.desparasitacion',
            'Antipulga' => 'pdfs.antipulga',
            'Cirugía' => 'pdfs.cirugia',
        ];

        $view = $viewMap[$tipo] ?? 'pdfs.registro_clinico';

        // Generar PDF desde la vista
        $pdf = PDF::loadView($view, $data);
        
        return $pdf->download($tipo . '_' . $mascota->nombre . '_' . date('Y-m-d') . '.pdf');
    }

    private function calcularEdad($fechaNacimiento)
    {
        if (!$fechaNacimiento) return 'N/A';

        $fecha = \Carbon\Carbon::parse($fechaNacimiento);
        $ahora = \Carbon\Carbon::now();

        $años = $ahora->diffInYears($fecha);
        $meses = $ahora->copy()->subYears($años)->diffInMonths($fecha);

        if ($años > 0) {
            return "$años " . ($años == 1 ? 'año' : 'años');
        } else {
            return "$meses " . ($meses == 1 ? 'mes' : 'meses');
        }
    }
}

