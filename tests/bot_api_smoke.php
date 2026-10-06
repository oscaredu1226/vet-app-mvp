<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('http://127.0.0.1:8000'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\Evento;
use App\Models\BotReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

set_exception_handler(function (Throwable $error) { fwrite(STDERR, 'FAIL: ' . $error->getMessage() . PHP_EOL); exit(1); });
if (!app()->environment('local') || config('database.default') !== 'mysql'
    || config('database.connections.mysql.host') !== '127.0.0.1'
    || (int) config('database.connections.mysql.port') !== 3307
    || config('database.connections.mysql.database') !== 'solutionvet_db') {
    throw new RuntimeException('Solo se permite la base local solutionvet_db.');
}
config(['bot_api.token' => 'test-only-bot-api', 'cache.default' => 'array']);
function checkApi(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
function callApi(string $method, string $path, array $data = [], int $expected = 200, ?string $token = 'test-only-bot-api'): array
{
    global $kernel;
    $request = Request::create('http://127.0.0.1:8000/api/bot' . $path, $method, $data);
    $request->headers->set('Accept', 'application/json');
    if ($token) $request->headers->set('Authorization', 'Bearer ' . $token);
    $response = $kernel->handle($request);
    checkApi($response->getStatusCode() === $expected, "$method $path: HTTP {$response->getStatusCode()} " . substr($response->getContent(), 0, 300));
    $kernel->terminate($request, $response);
    return json_decode($response->getContent(), true);
}
function countsApi(): array { return [Cliente::count(), Mascota::count(), Evento::count(), BotReservationRequest::count(), DB::table('appointment_day_locks')->count()]; }
$before = countsApi(); $level = DB::transactionLevel(); DB::beginTransaction();
try {
    callApi('GET', '/services', [], 401, null);
    callApi('GET', '/services', [], 401, 'wrong');
    $catalog = callApi('GET', '/services');
    checkApi($catalog['timezone'] === 'America/Lima' && isset($catalog['services']['consulta']), 'No se publicó el catálogo esperado.');
    $day = now()->addDays(70)->toDateString();
    while (Evento::whereDate('fecha', $day)->exists()) $day = Carbon\Carbon::parse($day)->addDay()->toDateString();
    $phone = '9'.random_int(10000000, 99999999);
    $data = ['request_id' => (string) Str::uuid(), 'chat_id' => 771001, 'phone' => '+51'.$phone,
        'service' => 'consulta', 'pet_name' => 'Mascota API Prueba', 'date' => $day, 'time' => '09:30',
        'nombre' => 'Cliente API', 'apellido' => 'Prueba', 'especie' => 'Canino', 'raza' => 'Mestizo',
        'genero' => 'Hembra', 'fecha_nacimiento' => now()->subYears(2)->toDateString(), 'esterilizado' => 'No'];
    $preview = callApi('POST', '/reservations/preview', $data);
    checkApi($preview['status'] === 'pending' && [Cliente::count(), Mascota::count(), Evento::count()] === array_slice($before, 0, 3), 'Se registraron datos antes de confirmar.');
    $again = callApi('POST', '/reservations/preview', $data);
    checkApi($again['request_id'] === $preview['request_id'] && BotReservationRequest::count() === $before[3] + 1, 'El reintento de preview creó otra solicitud.');
    callApi('POST', '/reservations/preview', array_merge($data, ['time' => '10:00']), 409);
    $path = '/reservations/'.$preview['request_id'].'/confirm';
    callApi('POST', $path, ['chat_id' => 771002], 404);
    $confirmed = callApi('POST', $path, ['chat_id' => 771001]);
    checkApi($confirmed['status'] === 'confirmed' && Evento::findOrFail($confirmed['event_id'])->hora === '09:30:00', 'La confirmación no guardó la cita.');
    checkApi(Cliente::count() === $before[0] + 1 && Mascota::count() === $before[1] + 1, 'No se registraron los datos nuevos.');
    $repeat = callApi('POST', $path, ['chat_id' => 771001]);
    checkApi($repeat['event_id'] === $confirmed['event_id'] && Evento::count() === $before[2] + 1, 'La confirmación HTTP repetida duplicó la cita.');
    callApi('POST', '/reservations/'.$preview['request_id'].'/cancel', ['chat_id' => 771001], 409);
    $lookup = callApi('POST', '/customers/lookup', ['phone' => $phone]);
    checkApi(count($lookup['customer']['pets']) === 1, 'La API no devuelve las mascotas del cliente verificado.');
    $free = callApi('GET', '/availability?date='.$day)['slots'];
    checkApi(in_array('08:00', $free) && in_array('20:00', $free) && !in_array('09:30', $free), 'La disponibilidad no respeta horarios/citas ocupadas.');
    foreach (['07:30', '20:30', '09:15'] as $time) {
        callApi('POST', '/reservations/preview', array_merge($data, ['request_id' => (string) Str::uuid(), 'time' => $time]), 422);
    }
    // Dos propuestas válidas; el cupo se comprueba nuevamente al confirmar.
    $one = callApi('POST', '/reservations/preview', array_merge($data, ['request_id' => (string) Str::uuid(), 'time' => '11:00']));
    $otherData = array_merge($data, ['request_id' => (string) Str::uuid(), 'time' => '11:00', 'chat_id' => 771002, 'phone' => '9'.random_int(10000000,99999999)]);
    $two = callApi('POST', '/reservations/preview', $otherData);
    callApi('POST', '/reservations/'.$one['request_id'].'/confirm', ['chat_id' => 771001]);
    $counts = countsApi();
    callApi('POST', '/reservations/'.$two['request_id'].'/confirm', ['chat_id' => 771002], 422);
    checkApi($counts === countsApi(), 'La reserva sin cupo dejó registros de cliente/mascota huérfanos.');
    $expired = callApi('POST', '/reservations/preview', array_merge($data, ['request_id' => (string) Str::uuid(), 'time' => '12:00']));
    BotReservationRequest::findOrFail($expired['request_id'])->update(['expires_at' => now()->subMinute()]);
    callApi('POST', '/reservations/'.$expired['request_id'].'/confirm', ['chat_id' => 771001], 410);
    $cancelled = callApi('POST', '/reservations/preview', array_merge($data, ['request_id' => (string) Str::uuid(), 'time' => '12:30']));
    callApi('POST', '/reservations/'.$cancelled['request_id'].'/cancel', ['chat_id' => 771001]);
    callApi('POST', '/reservations/'.$cancelled['request_id'].'/confirm', ['chat_id' => 771001], 410);
    echo "OK: API autenticada, clientes nuevos, preview sin escrituras clínicas, confirmación idempotente, cupos, horarios y caducidad.\n";
} finally {
    while (DB::transactionLevel() > $level) DB::rollBack();
    checkApi(countsApi() === $before, 'No se revirtieron los datos de prueba.');
    echo "OK: pruebas revertidas; sin cambios en registros existentes.\n";
}
