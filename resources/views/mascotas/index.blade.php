{{-- Heredamos de nuestra plantilla principal (que aún no hemos creado, pero la llamaremos 'app') --}}
@extends('layouts.app')

{{-- Esta sección reemplazará el @yield('content') en la plantilla --}}
@section('content')
    <div class="container-fluid px-4 mascotas">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-paw me-2"></i>Gestión de Mascotas</h2>
        </div>

        {{-- Tarjetas de Estadísticas --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h6>TOTAL MASCOTAS</h6>
                        <h2>{{ $stats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <h6>CANINOS</h6>
                        <h2>{{ $stats['caninos'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">
                        <h6>FELINOS</h6>
                        <h2>{{ $stats['felinos'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <h6>ESTERILIZADOS</h6>
                        <h2>{{ $stats['esterilizados'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta de Búsqueda --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Mascotas</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('mascotas.index') }}" class="row g-3">
                    <div class="col-md-8"> <label class="form-label">Buscar por nombre o propietario</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="buscar"
                                placeholder="Nombre de mascota o propietario..." value="{{ request('buscar') }}" autocomplete="off">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>

                            @if(request('buscar'))
                                <a href="{{ route('mascotas.index', ['filtro' => request('filtro')]) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-filter me-1"></i>Filtros</label>
                        <select class="form-select" name="filtro" onchange="this.form.submit()">
                            <option value="">Todas las mascotas</option>
                            <option value="caninos" {{ request('filtro') == 'caninos' ? 'selected' : '' }}>Solo Caninos
                            </option>
                            <option value="felinos" {{ request('filtro') == 'felinos' ? 'selected' : '' }}>Solo Felinos
                            </option>
                            <option value="esterilizados" {{ request('filtro') == 'esterilizados' ? 'selected' : '' }}>
                                Esterilizados</option>
                            <option value="no_esterilizados" {{ request('filtro') == 'no_esterilizados' ? 'selected' : '' }}>
                                No Esterilizados</option>
                            <option value="fallecidos" {{ request('filtro') == 'fallecidos' ? 'selected' : '' }}>Fallecidos
                            </option>
                        </select>
                    </div>
                </form>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mascotaModal">
                        <i class="fas fa-plus-circle me-1"></i> Registrar Mascota
                    </button>
                </div>
            </div>
        </div>

        {{-- Lista de Mascotas --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Todas las Mascotas</h5>
                @if(auth()->user()->isAdmin() || auth()->user()->puede_exportar_mascotas)
                    {{-- MVP_POSTERIOR: BEGIN Exportacion Excel
<a href="{{ route('mascotas.exportar', ['buscar' => request('buscar'), 'filtro' => request('filtro')]) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                    </a>
MVP_POSTERIOR: END Exportacion Excel --}}
                @endif
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
                                <th>Mascota</th>
                                <th>Especie/Raza</th>
                                <th>Propietario</th>
                                <th>Edad</th>
                                <th>Estado</th>
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mascotas as $mascota)
                                    <tr class="clickable-row" style="cursor: pointer;" 
                                        onclick="if (!event.target.closest('button, a')) { window.location='{{ url('/mascotas/' . $mascota->id_mascota . '/historia') }}'; }">
                                        <td>
                                            <strong>{{ $mascota->nombre }}</strong>
                                            <small class="d-block text-muted">{{ $mascota->genero }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $mascota->especie == 'Canino' ? 'success' : 'info' }}">
                                                {{ $mascota->especie }}
                                            </span>
                                            <div class="small text-muted">{{ $mascota->raza }}</div>
                                        </td>
                                        <td>{{ $mascota->cliente->nombre ?? 'Sin' }}
                                            {{ $mascota->cliente->apellido ?? 'Propietario' }}
                                        </td>
                                        <td>
                                {{ $mascota->edad_completa }}
                            </td>
                                        <td>
                                            <span class="badge bg-{{ $mascota->estado == 'Activo' ? 'success' : 'secondary' }}">
                                                {{ $mascota->estado }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-edit"
                                                    data-id="{{ $mascota->id_mascota }}" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="{{ url('/mascotas/' . $mascota->id_mascota . '/historia') }}"
                                                    class="btn btn-outline-info" title="Historia">
                                                    <i class="fas fa-file-medical"></i>
                                                </a>
                                                @if($mascota->fallecido != 1)
                                                {{-- MVP_POSTERIOR: BEGIN Enviar a cola medica
<button class="btn btn-outline-success"
                                                    onclick="agregarAColaMedica({{ $mascota->id_mascota }}, '{{ addslashes($mascota->nombre) }}')"
                                                    title="Enviar a Cola Médica">
                                                    <i class="fas fa-stethoscope"></i>
                                                </button>
MVP_POSTERIOR: END Enviar a cola medica --}}
                                                @endif
                                                <button class="btn btn-outline-danger btn-delete"
                                                    data-id="{{ $mascota->id_mascota }}" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-paw fa-4x text-muted mb-3"></i>
                                        <h4>No hay mascotas registradas</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="d-flex justify-content-center">
                    {{ $mascotas->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>


    {{-- MODAL PARA NUEVA MASCOTA (Modal de `mascotas.php`) --}}
    <div class="modal fade" id="mascotaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="mascotaModalLabel"><i class="fas fa-paw me-2"></i> Registrar Nueva Mascota
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevaMascota">
                        @csrf {{-- Token de seguridad de Laravel --}}

                        <h6 class="border-bottom pb-2"><i class="fas fa-user me-2"></i>Datos del Propietario</h6>
                        <div class="mb-3">
                            <label for="buscar_propietario" class="form-label">Buscar Propietario <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="buscar_propietario" autocomplete="off"
                                    placeholder="Nombre, apellido o DNI...">
                                <input type="hidden" id="id_cliente" name="id_cliente">
                            </div>
                            <div id="resultados_propietario" class="list-group mt-1"
                                style="max-height: 150px; overflow-y: auto;"></div>
                        </div>

                        <h6 class="border-bottom pb-2 mt-4"><i class="fas fa-paw me-2"></i>Datos de la Mascota</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre_mascota" class="form-label">Nombre <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_mascota" name="nombre" autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control fecha-input" id="fecha_nacimiento" name="fecha_nacimiento"
                                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                    maxlength="10">
                                <small class="text-muted">Máximo 30 años atrás.</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="especie" class="form-label">Especie <span class="text-danger">*</span></label>
                                <select class="form-select" id="especie" name="especie">
                                    <option value="">Seleccionar...</option>
                                    <option value="Canino">Canino</option>
                                    <option value="Felino">Felino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="raza" class="form-label">Raza <span class="text-danger">*</span></label>
                                <select class="form-select" id="raza" name="raza">
                                    <option value="">Seleccionar raza...</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                                <select class="form-select" id="genero" name="genero">
                                    <option value="">Seleccionar...</option>
                                    <option value="Macho">Macho</option>
                                    <option value="Hembra">Hembra</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="esterilizado" class="form-label">Esterilizado</label>
                            <select class="form-select" id="esterilizado" name="esterilizado">
                                <option value="No">No</option>
                                <option value="Si">Sí</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarMascota">Guardar Mascota</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR MASCOTA (Cargará contenido AJAX) --}}
    <div class="modal fade" id="mascotaEditModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{-- El contenido se carga dinámicamente desde el controlador --}}
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- Incluimos el JS específico para esta sección --}}
    <script src="{{ asset('js/mascotas-razas.js') }}"></script>
    <script src="{{ asset('js/mascotas.js') }}"></script>
@endpush