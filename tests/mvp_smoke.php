<?php

// Prueba funcional del MVP. Los registros de prueba se revierten al terminar.
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8000'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

set_exception_handler(function (Throwable $error): void {
    fwrite(STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL);
    exit(1);
});

if (!app()->environment('local') || config('database.default') !== 'mysql'
    || config('database.connections.mysql.host') !== '127.0.0.1'
    || (int) config('database.connections.mysql.port') !== 3307
    || config('database.connections.mysql.database') !== 'solutionvet_db') {
    throw new RuntimeException('Esta prueba solo se ejecuta en la base local solutionvet_db.');
}

// Solo en este proceso CLI: evita CSRF para las peticiones internas de prueba.
$app->detectEnvironment(fn () => 'testing');

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function requestMvp(string $method, string $url, array $data = [], int $status = 200): string
{
    global $kernel;
    $request = Request::create('http://127.0.0.1:8000' . $url, $method, $data);
    $request->headers->set('Accept', 'application/json');
    $response = $kernel->handle($request);
    expect($response->getStatusCode() === $status,
        "$method $url: HTTP {$response->getStatusCode()} " . substr($response->getContent(), 0, 350));
    $content = $response->getContent();
    $kernel->terminate($request, $response);
    return $content;
}

$countsBefore = [Cliente::count(), Mascota::count(), Consulta::count(), DB::table('eventos')->count()];
$initialLevel = DB::transactionLevel();
DB::beginTransaction();

try {
    foreach (['ventas.index', 'productos.index', 'servicios.index', 'caja.index', 'egresos.index',
              'usuarios.index', 'configuracion.index', 'cola.index', 'grooming.turnos-hoy',
              'vacunas.create', 'consultas.descargarPDF', 'historias.archivos.store', 'clientes.exportar',
              'password.request', 'profile.edit'] as $name) {
        expect(!Route::has($name), 'La ruta fuera del MVP sigue activa: ' . $name);
    }
    expect(count(array_filter(Route::getRoutes()->getRoutes(), fn ($route) => str_starts_with($route->uri(), 'api/'))) === 0,
        'La API auxiliar sigue expuesta.');

    Auth::logout();
    requestMvp('GET', '/login');
    requestMvp('GET', '/clientes', [], 401);
    requestMvp('POST', '/login', ['email' => 'admin', 'password' => 'admin123'], 302);
    expect(Auth::check(), 'El inicio de sesion no autentico al administrador.');

    foreach (['/dashboard', '/clientes', '/mascotas'] as $url) {
        $html = requestMvp('GET', $url);
        expect(!str_contains($html, 'id="colaMedicaDropdownBtn"'), 'La cola sigue visible.');
        expect(!str_contains($html, '/clientes-exportar'), 'La exportacion sigue visible.');
        expect(!str_contains($html, 'href="http://127.0.0.1:8000/ventas"'), 'Ventas sigue en el menu.');
    }
    $user = Auth::user();
    $originalRole = $user->role;
    $user->role = 'usuario';
    $user->save();
    requestMvp('GET', '/dashboard');
    $user->role = $originalRole;
    $user->save();

    $tag = 'PruebaMVP' . bin2hex(random_bytes(4));
    $clientData = ['nombre' => $tag, 'apellido' => 'Prueba', 'celular' => '900000001',
                   'dni' => null, 'direccion' => 'Direccion de prueba'];
    expect(json_decode(requestMvp('POST', '/clientes', $clientData), true)['success'] === true, 'No se registro el cliente.');
    $cliente = Cliente::where('nombre', $tag)->firstOrFail();
    requestMvp('GET', '/clientes?buscar_nombre=' . $tag);
    requestMvp('GET', "/clientes/{$cliente->id_cliente}/edit");
    $clientData['direccion'] = 'Direccion editada';
    requestMvp('PUT', "/clientes/{$cliente->id_cliente}", $clientData);
    expect($cliente->fresh()->direccion === 'Direccion editada', 'La edicion del cliente no se guardo.');
    requestMvp('GET', '/buscar-propietarios?query=' . $tag);

    $petData = ['id_cliente' => $cliente->id_cliente, 'nombre' => $tag . 'Mascota',
                'fecha_nacimiento' => '2024-01-01', 'especie' => 'Canino', 'raza' => 'Mestizo',
                'genero' => 'Macho', 'esterilizado' => 'No'];
    expect(json_decode(requestMvp('POST', '/mascotas', $petData), true)['success'] === true, 'No se registro la mascota.');
    $mascota = Mascota::where('nombre', $petData['nombre'])->firstOrFail();
    $petId = $mascota->id_mascota;
    requestMvp('GET', '/mascotas?cliente=' . $cliente->id_cliente);
    requestMvp('GET', "/mascotas/$petId/edit");
    $petData['estado'] = 'Activo';
    $petData['nombre'] .= 'Editada';
    requestMvp('PUT', "/mascotas/$petId", $petData);
    expect($mascota->fresh()->nombre === $petData['nombre'], 'La edicion de la mascota no se guardo.');

    $form = requestMvp('GET', "/mascotas/$petId/consultas/create");
    expect(str_contains($form, 'name="proxima_cita"') && !str_contains($form, 'id="buscar_examen"')
        && !str_contains($form, '/archivos/upload'), 'El formulario aun expone funciones posteriores.');
    $eventsBefore = DB::table('eventos')->count();
    $consultData = ['fecha' => '2026-10-05T14:00', 'motivo' => 'Consulta de prueba', 'peso' => '7.50',
                    'diagnostico' => 'Diagnostico MVP de prueba', 'plan_tratamiento' => 'Tratamiento MVP de prueba'];
    expect(json_decode(requestMvp('POST', "/mascotas/$petId/consultas", $consultData), true)['success'] === true,
        'No se registro la consulta.');
    $consulta = Consulta::where('id_mascota', $petId)->firstOrFail();
    expect($consulta->plan_tratamiento === $consultData['plan_tratamiento'], 'El tratamiento no se guardo.');
    // Un registro de la version completa conserva sus campos desactivados al editarlo en el MVP.
    $consulta->update(['examenes' => 'Examen anterior', 'receta' => 'Receta anterior',
                       'proxima_cita' => '2026-11-01', 'mensaje_proxima_cita' => 'Cita anterior']);
    $history = requestMvp('GET', "/mascotas/$petId/historia");
    expect(str_contains($history, $consultData['diagnostico']), 'El historial no muestra la consulta registrada.');
    expect(str_contains($history, 'id="nuevoEventoModal"'), 'El historial no permite programar citas.');
    foreach (['/vacunas/create', '/cirugias/create', 'Descargar PDF', 'Archivos Adjuntos'] as $hidden) {
        expect(!str_contains($history, $hidden), 'El historial muestra una funcion posterior: ' . $hidden);
    }
    requestMvp('GET', "/mascotas/$petId/consultas/{$consulta->id_consulta}/edit");
    $consultData['diagnostico'] = 'Diagnostico MVP editado';
    requestMvp('PUT', "/mascotas/$petId/consultas/{$consulta->id_consulta}", $consultData);
    expect($consulta->fresh()->diagnostico === $consultData['diagnostico'], 'La edicion de consulta no se guardo.');
    $preserved = $consulta->fresh();
    expect($preserved->examenes === 'Examen anterior' && $preserved->receta === 'Receta anterior'
        && str_starts_with($preserved->proxima_cita, '2026-11-01') && $preserved->mensaje_proxima_cita === 'Cita anterior',
        'La edicion del MVP modifico campos de los modulos desactivados.');
    expect(DB::table('eventos')->count() === $eventsBefore, 'El MVP creo un evento de calendario.');

    // Citas manuales: formato de fecha, hora persistida, edición y estado.
    $calendar = requestMvp('GET', '/eventos');
    expect(str_contains($calendar, 'Calendario y citas') && str_contains($calendar, 'name="hora"'), 'Calendario sin formulario de cita.');
    $day = now()->addDays(2)->format('Y-m-d');
    $appointment = ['id_mascota' => $petId, 'fecha' => now()->addDays(2)->format('d-m-Y'),
                    'hora' => '16:30', 'titulo' => 'Cita MVP de prueba', 'descripcion' => 'Control'];
    requestMvp('POST', '/eventos', $appointment);
    $event = App\Models\Evento::where('id_mascota', $petId)->firstOrFail();
    expect($event->fecha === $day && $event->hora === '16:30:00', 'La fecha u hora de la cita no se guardó.');
    $detail = json_decode(requestMvp('GET', "/eventos/{$event->id_evento}"), true);
    expect($detail['evento']['hora'] === '16:30:00', 'La edición no recibe la hora.');
    $appointment['hora'] = '17:00';
    requestMvp('PUT', "/eventos/{$event->id_evento}", $appointment);
    expect($event->fresh()->hora === '17:00:00', 'No se reprogramó la hora.');
    $countBeforeConflict = DB::table('eventos')->count();
    requestMvp('POST', '/eventos', $appointment, 400);
    expect(DB::table('eventos')->count() === $countBeforeConflict, 'Una reserva manual duplicó un horario ocupado.');
    $appointment['hora'] = '17:15';
    requestMvp('POST', '/eventos', $appointment, 400);
    requestMvp('PUT', "/eventos/{$event->id_evento}", $appointment, 400);
    expect($event->fresh()->hora === '17:00:00', 'Una hora fuera del intervalo modificó la cita.');
    foreach (['00:00', '07:30', '20:30', '23:30'] as $outside) {
        $appointment['hora'] = $outside;
        requestMvp('POST', '/eventos', $appointment, 400);
        requestMvp('PUT', "/eventos/{$event->id_evento}", $appointment, 400);
    }
    foreach (['08:00', '20:00'] as $boundary) {
        $appointment['hora'] = $boundary;
        $appointment['titulo'] = 'Horario límite MVP ' . $boundary;
        requestMvp('POST', '/eventos', $appointment);
        $boundaryId = App\Models\Evento::where('id_mascota', $petId)->where('titulo', $appointment['titulo'])->value('id_evento');
        requestMvp('DELETE', "/eventos/$boundaryId");
    }
    $appointment['hora'] = '25:70';
    requestMvp('POST', '/eventos', $appointment, 400);
    requestMvp('PUT', "/eventos/{$event->id_evento}", ['accion' => 'completar']);
    expect($event->fresh()->estado === 'completado', 'No se completó la cita.');
    $feed = json_decode(requestMvp('GET', '/eventos?feed=1&start='.$day.'&end='.now()->addDays(3)->format('Y-m-d')), true);
    expect(in_array($event->id_evento, array_column($feed, 'id')), 'El calendario mensual no muestra la cita.');
    requestMvp('GET', '/contador-eventos');
    requestMvp('DELETE', "/eventos/{$event->id_evento}");

    // La próxima cita crea un control y editarla reprograma ese mismo control.
    $consultData['proxima_cita'] = now()->addDays(4)->format('Y-m-d').'T10:30';
    $consultData['mensaje_proxima_cita'] = 'Revisar evolución';
    requestMvp('PUT', "/mascotas/$petId/consultas/{$consulta->id_consulta}", $consultData);
    $control = App\Models\Evento::where('id_consulta', $consulta->id_consulta)->firstOrFail();
    expect($control->hora === '10:30:00' && str_ends_with($consulta->fresh()->proxima_cita, '10:30:00'), 'El control perdió la hora.');
    $invalidConsult = array_merge($consultData, ['proxima_cita' => now()->addDays(4)->format('Y-m-d').'T10:15']);
    requestMvp('POST', "/mascotas/$petId/consultas", $invalidConsult, 400);
    requestMvp('PUT', "/mascotas/$petId/consultas/{$consulta->id_consulta}", $invalidConsult, 400);
    $invalidConsult['proxima_cita'] = now()->addDays(4)->format('Y-m-d').'T10:30:01';
    requestMvp('POST', "/mascotas/$petId/consultas", $invalidConsult, 400);
    expect($control->fresh()->hora === '10:30:00', 'Un horario inválido modificó el control.');
    foreach (['07:30', '20:30'] as $outside) {
        $invalidConsult['proxima_cita'] = now()->addDays(4)->format('Y-m-d').'T'.$outside;
        requestMvp('POST', "/mascotas/$petId/consultas", $invalidConsult, 400);
        requestMvp('PUT', "/mascotas/$petId/consultas/{$consulta->id_consulta}", $invalidConsult, 400);
    }
    $consultData['proxima_cita'] = now()->addDays(5)->format('Y-m-d').'T11:00';
    requestMvp('PUT', "/mascotas/$petId/consultas/{$consulta->id_consulta}", $consultData);
    expect(App\Models\Evento::where('id_consulta', $consulta->id_consulta)->count() === 1
        && $control->fresh()->hora === '11:00:00', 'Reprogramar el control duplicó o no actualizó la cita.');
    $consultData['proxima_cita'] = now()->addDays(5)->format('Y-m-d').'T12:00';
    $created = json_decode(requestMvp('POST', "/mascotas/$petId/consultas", $consultData), true);
    expect(App\Models\Evento::where('id_consulta', $created['consulta_id'])->value('hora') === '12:00:00',
        'Registrar una consulta con próxima cita no creó el control.');
    $history = requestMvp('GET', "/mascotas/$petId/historia");
    expect(str_contains($history, '11:00'), 'El historial no muestra la hora de la próxima cita.');

    foreach (['/ventas', '/caja', '/productos', '/usuarios', '/cola-medica', '/api/ventas'] as $url) {
        requestMvp('GET', $url, [], 404);
    }
    requestMvp('POST', '/logout', [], 302);
    expect(!Auth::check(), 'El cierre de sesion no invalido el acceso.');
    echo "OK: acceso, clientes, mascotas, consultas, historial, calendario, citas y modulos desactivados.\n";
} finally {
    while (DB::transactionLevel() > $initialLevel) {
        DB::rollBack();
    }
    expect([Cliente::count(), Mascota::count(), Consulta::count(), DB::table('eventos')->count()] === $countsBefore, 'Los datos de prueba no se revirtieron.');
    echo "OK: transaccion revertida; no se conservaron registros de prueba.\n";
}
