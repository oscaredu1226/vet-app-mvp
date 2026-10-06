@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 servicios">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Gestión de Servicios</h2>
        </div>

        {{-- Tarjeta de Búsqueda --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Servicios</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('servicios.index') }}">
                    <div class="row g-2">
                        <div class="col">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar_servicio" autocomplete="off"
                                    placeholder="Buscar por nombre..." value="{{ request('buscar_servicio') }}">

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>

                                @if(request('buscar_servicio'))
                                    <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#nuevoServicioModal">
                                <i class="fas fa-plus-circle me-1"></i> Nuevo Servicio
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista de Servicios --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Todos los Servicios</h5>
                    @if(auth()->user()->isAdmin() || auth()->user()->puede_exportar_servicios)
                        <a href="{{ route('servicios.exportar') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="me-2">Mostrar</label>
                        <select class="form-select form-select-sm d-inline-block w-auto" id="per_page"
                            onchange="window.location.href=this.value">
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 100]) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ms-2">registros</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($servicios as $servicio)
                                <tr>
                                    <td>
                                        <strong>{{ $servicio->nombre }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ ucfirst($servicio->tipo) }}</span>
                                    </td>
                                    <td class="text-center fw-bold">S/ {{ number_format($servicio->precio, 2) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-edit-servicio"
                                                data-id="{{ $servicio->id_servicio }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-delete-servicio"
                                                data-id="{{ $servicio->id_servicio }}" data-nombre="{{ $servicio->nombre }}"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="fas fa-concierge-bell fa-4x text-muted mb-3"></i>
                                        <h4>No hay servicios registrados</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="d-flex justify-content-center">
                    {{ $servicios->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA NUEVO SERVICIO (de servicios.php) [cite: 1406-1413] --}}
    <div class="modal fade" id="nuevoServicioModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Nuevo Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoServicio">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre_servicio" class="form-label fw-semibold">Nombre del servicio <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_servicio" name="nombre" required autocomplete="off">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_servicio" class="form-label fw-semibold">Precio <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/.</span>
                                    <input type="number" class="form-control" id="precio_servicio" name="precio" step="0.01" autocomplete="off"
                                        min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_servicio" class="form-label fw-semibold">Tipo <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_servicio" name="tipo" required>
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    <option value="clinica">Clínica</option>
                                    <option value="farmacia">Farmacia</option>
                                    <option value="petshop">PetShop</option>
                                    <option value="spa">Spa</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarServicio">Guardar Servicio</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR SERVICIO (Cargará contenido AJAX) --}}
    <div class="modal fade" id="editarServicioModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{-- El contenido se carga dinámicamente desde el controlador --}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Crea este archivo 'public/js/servicios.js' con el JS de 'modules/servicios.php' --}}
    <script src="{{ asset('js/servicios.js') }}"></script>
@endpush