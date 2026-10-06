@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mb-4 text-dark fw-bold"><i class="fas fa-home me-2"></i>Dashboard</h2>
    
    <!-- Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-2" style="font-size: 0.85rem; letter-spacing: 1px;">Clientes</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 2.5rem;">{{ \App\Models\Cliente::count() }}</h2>
                        </div>
                        <div class="text-white-50" style="font-size: 3rem; opacity: 0.3;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-10 border-0">
                    <a href="{{ route('clientes.index') }}" class="text-white text-decoration-none small">
                        <i class="fas fa-arrow-right me-1"></i> Ver todos
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-2" style="font-size: 0.85rem; letter-spacing: 1px;">Mascotas</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 2.5rem;">{{ \App\Models\Mascota::count() }}</h2>
                        </div>
                        <div class="text-white-50" style="font-size: 3rem; opacity: 0.3;">
                            <i class="fas fa-paw"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-10 border-0">
                    <a href="{{ route('mascotas.index') }}" class="text-white text-decoration-none small">
                        <i class="fas fa-arrow-right me-1"></i> Ver todas
                    </a>
                </div>
            </div>
        </div>
        {{-- MVP_POSTERIOR: BEGIN Indicador fuera del MVP
<div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-2" style="font-size: 0.85rem; letter-spacing: 1px;">Productos</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 2.5rem;">{{ \App\Models\Producto::count() }}</h2>
                        </div>
                        <div class="text-white-50" style="font-size: 3rem; opacity: 0.3;">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-10 border-0">
                    <a href="{{ route('productos.index') }}" class="text-white text-decoration-none small">
                        <i class="fas fa-arrow-right me-1"></i> Ver todos
                    </a>
                </div>
            </div>
        </div>
MVP_POSTERIOR: END Indicador fuera del MVP --}}
        {{-- MVP_POSTERIOR: BEGIN Indicador fuera del MVP
<div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-2" style="font-size: 0.85rem; letter-spacing: 1px;">Servicios</h6>
                            <h2 class="mb-0 fw-bold" style="font-size: 2.5rem;">{{ \App\Models\Servicio::count() }}</h2>
                        </div>
                        <div class="text-white-50" style="font-size: 3rem; opacity: 0.3;">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white bg-opacity-10 border-0">
                    <a href="{{ route('servicios.index') }}" class="text-white text-decoration-none small">
                        <i class="fas fa-arrow-right me-1"></i> Ver todos
                    </a>
                </div>
            </div>
        </div>
MVP_POSTERIOR: END Indicador fuera del MVP --}}
    </div>

    <!-- Mensaje de Bienvenida -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary bg-gradient rounded-circle p-3 me-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-md text-white" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h4 class="mb-1 fw-bold text-dark">¡Bienvenido, {{ Auth::user()->name }}!</h4>
                    <p class="mb-0 text-muted">Has iniciado sesión correctamente en el sistema de gestión veterinaria.</p>
                </div>
            </div>
            <hr class="my-3">
            <div class="row g-3 text-center">
                <div class="col-md-3">
                    <div class="p-3 rounded" style="background-color: #f0f4ff;">
                        <i class="fas fa-calendar-check text-primary mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-1 small text-muted">Pendientes de hoy</p>
                        <h5 class="mb-0 fw-bold text-dark">{{ \App\Models\Evento::where('estado', 'pendiente')->whereDate('fecha', today())->count() }}</h5>
                    </div>
                </div>

                {{-- MVP_POSTERIOR: BEGIN Indicador fuera del MVP
<div class="col-md-3">
                    <div class="p-3 rounded" style="background-color: #fff0f0;">
                        <i class="fas fa-shopping-cart text-danger mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-1 small text-muted">Ventas hoy</p>
                        <h5 class="mb-0 fw-bold text-dark">{{ \App\Models\Venta::whereDate('fecha_venta', today())->count() }}</h5>
                    </div>
                </div>
MVP_POSTERIOR: END Indicador fuera del MVP --}}
                {{-- MVP_POSTERIOR: BEGIN Indicador fuera del MVP
<div class="col-md-3">
                    <div class="p-3 rounded" style="background-color: #f0fff4;">
                        <i class="fas fa-flask text-success mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-1 small text-muted">Exámenes pendientes</p>
                        <h5 class="mb-0 fw-bold text-dark">{{ \App\Models\ExamenLaboratorio::where('pagado', false)->count() }}</h5>
                    </div>
                </div>
MVP_POSTERIOR: END Indicador fuera del MVP --}}
                <div class="col-md-3">
                    <div class="p-3 rounded" style="background-color: #fff8f0;">
                        <i class="fas fa-clock text-warning mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-1 small text-muted">Última actividad</p>
                        <h5 class="mb-0 fw-bold text-dark small">{{ now()->format('H:i') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-info:hover,
    .btn-outline-warning:hover {
        transform: translateX(5px);
        transition: transform 0.2s ease;
    }
</style>
@endsection
