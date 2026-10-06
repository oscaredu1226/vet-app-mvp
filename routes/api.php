<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotApiController;
use App\Http\Middleware\AuthenticateBotApi;

// API exclusiva del bot externo. La clave nunca se envía al navegador del cliente.
Route::prefix('bot')->middleware(AuthenticateBotApi::class)->group(function () {
    Route::get('/services', [BotApiController::class, 'services']);
    Route::get('/availability', [BotApiController::class, 'availability']);
    Route::post('/customers/lookup', [BotApiController::class, 'customer']);
    Route::post('/reservations/preview', [BotApiController::class, 'preview']);
    Route::post('/reservations/{reservation}/confirm', [BotApiController::class, 'confirm']);
    Route::post('/reservations/{reservation}/cancel', [BotApiController::class, 'cancel']);
});
use App\Http\Controllers\AuthController; // Crearemos este
use App\Http\Controllers\Admin\UserController;
// ... (Importa TODOS tus otros controladores: Cliente, Mascota, Venta, etc.)

// --- RUTAS PÚBLICAS (Login) ---
// (Necesitarás crear un AuthController para manejar el login y logout)
// Route::post('/login', [AuthController::class, 'login']);

// MVP_POSTERIOR: API auxiliar; el MVP utiliza las rutas web con sesion.
// MVP_POSTERIOR | // --- RUTAS PROTEGIDAS (Requieren Token) ---
// MVP_POSTERIOR | Route::middleware('auth:sanctum')->group(function () {
// MVP_POSTERIOR |     
// MVP_POSTERIOR |     // Ruta para el usuario autenticado
// MVP_POSTERIOR |     Route::get('/user', function (Request $request) {
// MVP_POSTERIOR |         return $request->user();
// MVP_POSTERIOR |     });
// MVP_POSTERIOR |     
// MVP_POSTERIOR |     // --- RUTAS PARA TODOS LOS USUARIOS AUTENTICADOS (admin y usuario) ---
// MVP_POSTERIOR |     // (Aquí mueves la mayoría de tus rutas de web.php)
// MVP_POSTERIOR |     Route::get('/buscar-propietarios', [ClienteController::class, 'buscarPropietarios']);
// MVP_POSTERIOR |     Route::get('/buscar-mascotas', [MascotaController::class, 'buscarMascotas']);
// MVP_POSTERIOR |     Route::get('/buscar-items', [VentaController::class, 'buscarItems']);
// MVP_POSTERIOR |     // ... (etc.)
// MVP_POSTERIOR |     Route::resource('clientes', ClienteController::class);
// MVP_POSTERIOR |     Route::resource('mascotas', MascotaController::class);
// MVP_POSTERIOR |     Route::resource('ventas', VentaController::class)->only(['index', 'store', 'show']);
// MVP_POSTERIOR |     // ... (TODAS las rutas de Historia Clínica, Exámenes, Egresos, Caja, etc.)
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     // --- RUTAS SOLO PARA ADMIN ---
// MVP_POSTERIOR |     // 
// MVP_POSTERIOR |     // Este grupo protege las rutas para que solo usuarios con role='admin' puedan entrar
// MVP_POSTERIOR |     Route::middleware('role:admin')->prefix('admin')->group(function () {
// MVP_POSTERIOR |         
// MVP_POSTERIOR |         // Gestionar usuarios (Crear, Editar, Eliminar)
// MVP_POSTERIOR |         // (Ej. POST /api/admin/usuarios)
// MVP_POSTERIOR |         Route::resource('usuarios', UserController::class);
// MVP_POSTERIOR |         
// MVP_POSTERIOR |         // (Aquí podrías poner otras rutas que solo el admin puede ver,
// MVP_POSTERIOR |         // como reportes financieros o configuraciones globales)
// MVP_POSTERIOR |     });
// MVP_POSTERIOR | 
// MVP_POSTERIOR |     // Route::post('/logout', [AuthController::class, 'logout']);
// MVP_POSTERIOR | });
