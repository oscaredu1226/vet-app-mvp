@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Banner de Bienvenida --}}
    <div class="welcome-banner mb-4">
        <h1 class="display-5 fw-bold text-white mb-2">¡Bienvenido a Vet App!</h1>
        <p class="lead text-white mb-3">Sistema integral de gestión veterinaria</p>
        <p class="text-white-50">Utiliza el menú lateral para navegar entre las diferentes secciones del sistema.</p>
    </div>

    {{-- Tarjetas de Accesos Rápidos --}}
    <div class="row g-4">
        {{-- Clientes --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('clientes.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #007bff;">
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Clientes</h5>
                        <p class="card-text text-muted">Gestión de clientes y propietarios</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Mascotas --}}
        <div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('mascotas.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #28a745;">
                            <i class="fas fa-paw fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Mascotas</h5>
                        <p class="card-text text-muted">Registro y seguimiento de mascotas</p>
                    </div>
                </div>
            </a>
        </div>

        {{-- Hospitalizaciones --}}
        {{-- MVP_POSTERIOR: BEGIN Hospitalizaciones
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="#" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #17a2b8;">
                            <i class="fas fa-hospital fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Hospitalizaciones</h5>
                        <p class="card-text text-muted">Control de pacientes hospitalizados</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Hospitalizaciones --}}

        {{-- Ventas --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('ventas.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #ffc107;">
                            <i class="fas fa-shopping-cart fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Ventas</h5>
                        <p class="card-text text-muted">Gestión de ventas y productos</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}

        {{-- Exámenes --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('examenes.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #dc3545;">
                            <i class="fas fa-vial fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Exámenes</h5>
                        <p class="card-text text-muted">Gestión de exámenes médicos</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}

        {{-- Productos --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('productos.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #6c757d;">
                            <i class="fas fa-box fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Productos</h5>
                        <p class="card-text text-muted">Inventario de productos</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}

        {{-- Servicios --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('servicios.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #343a40;">
                            <i class="fas fa-tools fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Servicios</h5>
                        <p class="card-text text-muted">Gestión de servicios veterinarios</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}

        {{-- Caja --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('caja.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #20c997;">
                            <i class="fas fa-cash-register fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Caja</h5>
                        <p class="card-text text-muted">Control de ingresos y ventas</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}

        {{-- Egresos --}}
        {{-- MVP_POSTERIOR: BEGIN Acceso fuera del MVP
<div class="col-xl-3 col-lg-4 col-md-6">
            <a href="{{ route('egresos.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100">
                    <div class="card-body text-center">
                        <div class="dashboard-icon mb-3" style="color: #e74c3c;">
                            <i class="fas fa-money-bill-wave fa-3x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Egresos</h5>
                        <p class="card-text text-muted">Gestión de gastos</p>
                    </div>
                </div>
            </a>
        </div>
MVP_POSTERIOR: END Acceso fuera del MVP --}}
    </div>
</div>

<style>
.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.dashboard-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.dashboard-icon {
    transition: transform 0.3s ease;
}

.dashboard-card:hover .dashboard-icon {
    transform: scale(1.1);
}

.dashboard-card .card-body {
    padding: 2rem 1rem;
}

.dashboard-card .card-title {
    color: #2c3e50;
    margin-bottom: 0.75rem;
}

.dashboard-card .card-text {
    font-size: 0.9rem;
}
</style>
@endsection
