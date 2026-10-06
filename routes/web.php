<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MascotaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\ProductoController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\ServicioController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\VentaController;
use App\Http\Controllers\HistoriaClinicaController;
use App\Http\Controllers\ConsultaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\VacunaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\DesparasitacionController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\AntipulgaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\ExamenLaboratorioController;
// MVP_POSTERIOR: Importacion de modulo desactivado
use App\Http\Controllers\CalendarioController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Models\Mascota;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\EgresoController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\CajaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\LogoController;
use App\Http\Controllers\ConfiguracionController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\UserController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\ArchivoHistoriaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\PDFHistoriaController;
// MVP_POSTERIOR: Importacion de modulo desactivado
// MVP_POSTERIOR | use App\Http\Controllers\GroomingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas web para tu aplicación.
|
*/

// --- RUTA PRINCIPAL ---
// Redirige al dashboard
Route::middleware('auth')->get('/', function () {
    return redirect()->route('dashboard');
});


// --- RUTAS PROTEGIDAS CON AUTENTICACIÓN ---
Route::middleware('auth')->group(function () {
    // --- RUTAS DE RECURSOS (CRUDs) ---
    // Gestionan las operaciones principales de ABM (Alta, Baja, Modificación)
// MVP_POSTERIOR: Declaracion completa del recurso
// MVP_POSTERIOR |     Route::resource('clientes', ClienteController::class)->parameters(['clientes' => 'cliente:id_cliente']);
    Route::resource('clientes', ClienteController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])->parameters(['clientes' => 'cliente:id_cliente']);
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/clientes-exportar', [ClienteController::class, 'exportar'])->name('clientes.exportar');
// MVP_POSTERIOR: Declaracion completa del recurso
// MVP_POSTERIOR |     Route::resource('mascotas', MascotaController::class)->parameters(['mascotas' => 'mascota:id_mascota']);
    Route::resource('mascotas', MascotaController::class)->only(['index', 'store', 'edit', 'update', 'destroy'])->parameters(['mascotas' => 'mascota:id_mascota']);
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/mascotas-exportar', [MascotaController::class, 'exportar'])->name('mascotas.exportar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::resource('productos', ProductoController::class)->parameters(['productos' => 'producto:id_producto']);
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/productos-exportar', [ProductoController::class, 'exportar'])->name('productos.exportar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/productos/buscar-antipulgas-desparasitantes', [ProductoController::class, 'buscarAntipulgasDesparasitantes'])->name('productos.buscar-antipulgas-desparasitantes');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::resource('servicios', ServicioController::class)->parameters(['servicios' => 'servicio:id_servicio']);
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/servicios-exportar', [ServicioController::class, 'exportar'])->name('servicios.exportar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::resource('examenes', ExamenLaboratorioController::class)->parameters(['examenes' => 'examen:id_examen_lab']); // Reemplaza examenes.php, editar_examen.php, etc.
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/examenes-exportar', [ExamenLaboratorioController::class, 'exportar'])->name('examenes.exportar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show'])->parameters(['ventas' => 'venta:id_venta'])->middleware('module.access:ventas');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/ventas/{venta}/detalle', [VentaController::class, 'detalle'])->name('ventas.detalle')->middleware('module.access:ventas');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('/ventas-exportar', [VentaController::class, 'exportar'])->name('ventas.exportar')->middleware('module.access:ventas');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::resource('egresos', EgresoController::class)->only(['index', 'store', 'destroy'])->parameters(['egresos' => 'egreso:id_egreso'])->middleware('module.access:egresos');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/configuracion/{seccion?}', [ConfiguracionController::class, 'index'])->name('configuracion.index');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/diagnostico', [ConfiguracionController::class, 'storeDiagnostico'])->name('configuracion.diagnostico.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/diagnostico/{id}', [ConfiguracionController::class, 'deleteDiagnostico'])->name('configuracion.diagnostico.delete');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/tratamiento', [ConfiguracionController::class, 'storeTratamiento'])->name('configuracion.tratamiento.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/tratamiento/{id}', [ConfiguracionController::class, 'deleteTratamiento'])->name('configuracion.tratamiento.delete');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/receta', [ConfiguracionController::class, 'storeReceta'])->name('configuracion.receta.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/receta/{id}', [ConfiguracionController::class, 'deleteReceta'])->name('configuracion.receta.delete');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/examen', [ConfiguracionController::class, 'storeExamen'])->name('configuracion.examen.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/examen/{id}', [ConfiguracionController::class, 'deleteExamen'])->name('configuracion.examen.delete');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/laboratorio', [ConfiguracionController::class, 'storeLaboratorio'])->name('configuracion.laboratorio.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/laboratorio/{id}', [ConfiguracionController::class, 'deleteLaboratorio'])->name('configuracion.laboratorio.delete');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/configuracion/cirugia', [ConfiguracionController::class, 'storeTipoCirugia'])->name('configuracion.cirugia.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/configuracion/cirugia/{id}', [ConfiguracionController::class, 'deleteTipoCirugia'])->name('configuracion.cirugia.delete');
        // Rutas para Cirugías
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/cirugias/create', [App\Http\Controllers\CirugiaController::class, 'create'])->name('cirugias.create');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('mascotas/{mascota}/cirugias', [App\Http\Controllers\CirugiaController::class, 'store'])->name('cirugias.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/cirugias/{id}/edit', [App\Http\Controllers\CirugiaController::class, 'edit'])->name('cirugias.edit');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('mascotas/{mascota}/cirugias/{id}', [App\Http\Controllers\CirugiaController::class, 'update'])->name('cirugias.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('cirugias/{id}', [App\Http\Controllers\CirugiaController::class, 'destroy'])->name('cirugias.destroy');
    
    // --- RUTAS DE GROOMING ---
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/turnos-hoy', [GroomingController::class, 'turnosHoy'])->name('grooming.turnos-hoy');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('grooming/exportar-excel', [GroomingController::class, 'exportarExcel'])->name('grooming.exportar-excel');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/programados', [GroomingController::class, 'programados'])->name('grooming.programados');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/create', [GroomingController::class, 'create'])->name('grooming.create');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('grooming', [GroomingController::class, 'store'])->name('grooming.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/{grooming}/edit', [GroomingController::class, 'edit'])->name('grooming.edit');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('grooming/{grooming}', [GroomingController::class, 'update'])->name('grooming.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('grooming/{grooming}', [GroomingController::class, 'destroy'])->name('grooming.destroy');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/{grooming}', [GroomingController::class, 'show'])->name('grooming.show');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('grooming/{grooming}/completar', [GroomingController::class, 'completar'])->name('grooming.completar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('grooming/{grooming}/cancelar', [GroomingController::class, 'cancelar'])->name('grooming.cancelar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming-mascotas-by-cliente', [GroomingController::class, 'getMascotasByCliente'])->name('grooming.mascotas-by-cliente');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('grooming/mascota/{mascota}/historia-banos', [GroomingController::class, 'historiaBanos'])->name('grooming.historia-banos');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('grooming/mascota/{mascota}/historia-banos', [GroomingController::class, 'storeHistoriaBano'])->name('grooming.historia-banos.store');
    

    // --- RUTAS DE BÚSQUEDA (AJAX) ---
    // Rutas específicas para los buscadores autocompletables
    Route::get('/buscar-propietarios', [ClienteController::class, 'buscarPropietarios'])->name('clientes.buscarPropietarios');
    Route::get('/buscar-mascotas', [MascotaController::class, 'buscarMascotas'])->name('mascotas.buscar');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/buscar-items', [VentaController::class, 'buscarItems'])->name('ventas.buscarItems');
    Route::get('/buscar-diagnosticos', [ConfiguracionController::class, 'buscarDiagnosticos'])->name('buscar.diagnosticos');
    Route::get('/buscar-tratamientos', [ConfiguracionController::class, 'buscarTratamientos'])->name('buscar.tratamientos');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/buscar-examenes-catalogo', [ConfiguracionController::class, 'buscarExamenes'])->name('buscar.examenes');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/buscar-recetas', [ConfiguracionController::class, 'buscarRecetas'])->name('buscar.recetas');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/buscar-tipos-cirugia', [App\Http\Controllers\CirugiaController::class, 'buscarTipos'])->name('buscar.tipos_cirugia');

    // Ruta para consultar DNI en RENIEC
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::post('/consultar-reniec', [ClienteController::class, 'buscarReniec'])->name('clientes.buscarReniec');


    // --- MÓDULO DE HISTORIA CLÍNICA (HC) ---
    // Rutas anidadas que dependen de una mascota

    // Ruta principal que muestra el dashboard de la HC de una mascota
    // Reemplaza 'modules/historia_clinica.php' y 'modules/vermascota.php'
    Route::get('mascotas/{mascota}/historia', [HistoriaClinicaController::class, 'show'])->name('historia.show');

    // Rutas para MOSTRAR los formularios de nuevos registros médicos
    Route::get('mascotas/{mascota}/consultas/create', [ConsultaController::class, 'create'])->name('consultas.create');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/vacunas/create', [VacunaController::class, 'create'])->name('vacunas.create');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/desparasitaciones/create', [DesparasitacionController::class, 'create'])->name('desparasitaciones.create');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/antipulgas/create', [AntipulgaController::class, 'create'])->name('antipulgas.create');

    // Rutas para GUARDAR (POST) los datos de los formularios médicos
    Route::post('mascotas/{mascota}/consultas', [ConsultaController::class, 'store'])->name('consultas.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('mascotas/{mascota}/vacunas', [VacunaController::class, 'store'])->name('vacunas.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('mascotas/{mascota}/desparasitaciones', [DesparasitacionController::class, 'store'])->name('desparasitaciones.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('mascotas/{mascota}/antipulgas', [AntipulgaController::class, 'store'])->name('antipulgas.store');

    // Rutas para EDITAR, ACTUALIZAR y ELIMINAR registros médicos
    Route::get('mascotas/{mascota}/consultas/{id}/edit', [ConsultaController::class, 'edit'])->name('consultas.edit');
    Route::put('mascotas/{mascota}/consultas/{id}', [ConsultaController::class, 'update'])->name('consultas.update');
    Route::delete('consultas/{id}', [ConsultaController::class, 'destroy'])->name('consultas.destroy');

// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/vacunas/{id}/edit', [VacunaController::class, 'edit'])->name('vacunas.edit');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('mascotas/{mascota}/vacunas/{id}', [VacunaController::class, 'update'])->name('vacunas.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('vacunas/{id}', [VacunaController::class, 'destroy'])->name('vacunas.destroy');

// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/desparasitaciones/{id}/edit', [DesparasitacionController::class, 'edit'])->name('desparasitaciones.edit');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('mascotas/{mascota}/desparasitaciones/{id}', [DesparasitacionController::class, 'update'])->name('desparasitaciones.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('desparasitaciones/{id}', [DesparasitacionController::class, 'destroy'])->name('desparasitaciones.destroy');

// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/antipulgas/{id}/edit', [AntipulgaController::class, 'edit'])->name('antipulgas.edit');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('mascotas/{mascota}/antipulgas/{id}', [AntipulgaController::class, 'update'])->name('antipulgas.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('antipulgas/{id}', [AntipulgaController::class, 'destroy'])->name('antipulgas.destroy');

    // --- RUTAS PARA DESCARGAR PDF DE HISTORIA CLÍNICA ---
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/consultas/{id}/descargar-pdf', [PDFHistoriaController::class, 'descargarConsulta'])->name('consultas.descargarPDF');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/vacunas/{id}/descargar-pdf', [PDFHistoriaController::class, 'descargarVacuna'])->name('vacunas.descargarPDF');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/desparasitaciones/{id}/descargar-pdf', [PDFHistoriaController::class, 'descargarDesparasitacion'])->name('desparasitaciones.descargarPDF');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/antipulgas/{id}/descargar-pdf', [PDFHistoriaController::class, 'descargarAntipulga'])->name('antipulgas.descargarPDF');
// MVP_POSTERIOR: Fuera del primer MVP
// MVP_POSTERIOR |     Route::get('mascotas/{mascota}/cirugias/{id}/descargar-pdf', [PDFHistoriaController::class, 'descargarCirugia'])->name('cirugias.descargarPDF');

    // --- MÓDULO DE CALENDARIO / EVENTOS ---
    // Reemplaza 'modules/calendario.php', 'acciones_evento.php', 'obtener_evento.php'
// MVP_POSTERIOR: Modulo para una version posterior
    Route::resource('eventos', CalendarioController::class)->only(['index', 'store', 'update', 'destroy', 'show'])->parameters(['eventos' => 'evento:id_evento']);
// MVP_POSTERIOR: Modulo para una version posterior
    Route::get('/contador-eventos', [CalendarioController::class, 'contadorEventos'])->name('eventos.contador');

    // --- GESTIÓN DE CAJA ---
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('caja', [CajaController::class, 'index'])->name('caja.index')->middleware('module.access:caja');
    // Reemplaza la lógica POST para actualizar el total manual
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('caja/actualizar-total', [CajaController::class, 'updateTotalCaja'])->name('caja.updateTotal')->middleware('module.access:caja');
    // Reemplaza 'modules/cerrar_caja.php'
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('caja/cerrar', [CajaController::class, 'cerrarCaja'])->name('caja.cerrar')->middleware('module.access:caja');
    
    // --- REPORTES DE CAJA ---
    // Ver lista de reportes guardados (Solo Administradores)
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::middleware('admin')->get('caja/reportes', [CajaController::class, 'mostrarReportes'])->name('caja.reportes');
    // Descargar reporte de caja almacenado (usuarios: reportes recientes, admin: todos)
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('caja/reportes/{filename}', [CajaController::class, 'descargarReporte'])->name('caja.descargarReporte');

    // --- GESTIÓN DEL LOGO ---
    // Reemplaza 'modules/cambiar_logo.php' y 'guardar_logo.php'
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('logo/update', [LogoController::class, 'update'])->name('logo.update');
    // Reemplaza 'modules/eliminar_logo.php'
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('logo/destroy', [LogoController::class, 'destroy'])->name('logo.destroy');

    // --- GESTIÓN DE USUARIOS (Solo administradores) ---
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
// MVP_POSTERIOR: Modulo para una version posterior
// MVP_POSTERIOR |     Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
});

// --- DASHBOARDS SEGÚN ROL ---
Route::middleware('auth')->group(function () {
    // Ruta única de dashboard (muestra contenido según rol sin cambiar URL)
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return view('dashboard');
        }
        return view('dashboard-user');
    })->name('dashboard');
    
    // Mantener rutas específicas por compatibilidad (redirigen a /dashboard)
    Route::get('/dashboard/admin', function () {
        return redirect()->route('dashboard');
    });
    
    Route::get('/dashboard/user', function () {
        return redirect()->route('dashboard');
    });
});

// MVP_POSTERIOR: Perfil, adjuntos, pruebas y cola medica
// MVP_POSTERIOR | // --- AUTENTICACIÓN (Laravel Breeze) ---
// MVP_POSTERIOR | Route::middleware('auth')->group(function () {
// MVP_POSTERIOR |     Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
// MVP_POSTERIOR |     Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
// MVP_POSTERIOR |     Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
// MVP_POSTERIOR |     
// MVP_POSTERIOR |     // Rutas para archivos de historias médicas
// MVP_POSTERIOR |     Route::post('/historias/archivos', [ArchivoHistoriaController::class, 'store'])->name('historias.archivos.store');
// MVP_POSTERIOR |     Route::get('/historias/archivos', [ArchivoHistoriaController::class, 'getArchivos'])->name('historias.archivos.get');
// MVP_POSTERIOR |     Route::delete('/historias/archivos/{id}', [ArchivoHistoriaController::class, 'destroy'])->name('historias.archivos.destroy');
// MVP_POSTERIOR |     
// MVP_POSTERIOR |     // Ruta optimizada para subida de archivos con procesamiento de imágenes
// MVP_POSTERIOR |     Route::post('/archivos/upload', [ArchivoHistoriaController::class, 'storeConProcesamiento'])->name('archivos.upload');
// MVP_POSTERIOR |     
// MVP_POSTERIOR |     // Ruta de prueba para upload
// MVP_POSTERIOR |     Route::get('/test-upload', function () {
// MVP_POSTERIOR |         return view('test-upload');
// MVP_POSTERIOR |     });
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     // --- RUTAS DE COLA MÉDICA ---
// MVP_POSTERIOR |     Route::prefix('cola-medica')->group(function () {
// MVP_POSTERIOR |         Route::get('/', [App\Http\Controllers\ColaMedicaController::class, 'index'])->name('cola.index');
// MVP_POSTERIOR |         Route::post('/agregar', [App\Http\Controllers\ColaMedicaController::class, 'agregar'])->name('cola.agregar');
// MVP_POSTERIOR |         Route::delete('/terminar/{id_mascota}', [App\Http\Controllers\ColaMedicaController::class, 'terminar'])->name('cola.terminar');
// MVP_POSTERIOR |         Route::delete('/terminar-todo', [App\Http\Controllers\ColaMedicaController::class, 'terminarTodo'])->name('cola.terminar.todo');
// MVP_POSTERIOR |         Route::get('/verificar/{id_mascota}', [App\Http\Controllers\ColaMedicaController::class, 'verificar'])->name('cola.verificar');
// MVP_POSTERIOR |     });
// MVP_POSTERIOR | });
// MVP_POSTERIOR | 
require __DIR__.'/auth.php';