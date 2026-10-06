@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 configuracion">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración</h2>
        </div>

        {{-- Mensajes de éxito o error --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            {{-- Menú Lateral --}}
            <div class="col-md-3">
                <div class="list-group">
                    <a href="javascript:void(0)" 
                        class="list-group-item list-group-item-action section-link {{ (!$seccion || $seccion == 'datos_veterinaria') ? 'active' : '' }}"
                        data-section="datosVeterinaria">
                        <i class="fas fa-hospital me-2"></i>Datos de la Veterinaria
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'diagnosticos' ? 'active' : '' }}"
                        data-section="diagnosticos">
                        <i class="fas fa-stethoscope me-2"></i>Diagnósticos
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'tratamientos' ? 'active' : '' }}"
                        data-section="tratamientos">
                        <i class="fas fa-pills me-2"></i>Tratamientos
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'examenes_recomendados' ? 'active' : '' }}"
                        data-section="examenes">
                        <i class="fas fa-flask me-2"></i>Exámenes Recomendados
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'recetas' ? 'active' : '' }}"
                        data-section="recetas">
                        <i class="fas fa-prescription me-2"></i>Recetas
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'laboratorios' ? 'active' : '' }}"
                        data-section="laboratorios">
                        <i class="fas fa-microscope me-2"></i>Laboratorios
                    </a>
                    <a href="javascript:void(0)"
                        class="list-group-item list-group-item-action section-link {{ $seccion == 'cirugias' ? 'active' : '' }}"
                        data-section="cirugias">
                        <i class="fas fa-scalpel me-2"></i>Tipos de Cirugía
                    </a>
                </div>
            </div>

            {{-- Contenido Principal --}}
            <div class="col-md-9">
                {{-- Datos de la Veterinaria --}}
                <div class="card" id="datosVeterinaria">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-hospital me-2"></i>Datos de la Veterinaria</h5>
                        @if(auth()->user()->isAdmin())
                            <button type="button" class="btn btn-sm btn-light" id="btnEditar">
                                <i class="fas fa-edit me-1"></i>Editar
                            </button>
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.update') }}" method="POST" id="formDatos">
                            @csrf
                            <div class="mb-3">
                                <label for="nombre_negocio" class="form-label">Nombre del Negocio <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre_negocio') is-invalid @enderror"
                                    id="nombre_negocio" name="nombre_negocio"
                                    value="{{ old('nombre_negocio', $nombre->valor ?? '') }}" required maxlength="255"
                                    readonly>
                                @error('nombre_negocio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion"
                                    name="direccion" rows="3" required maxlength="500"
                                    readonly>{{ old('direccion', $direccion->valor ?? '') }}</textarea>
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="ruc" class="form-label">RUC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ruc') is-invalid @enderror" id="ruc"
                                    name="ruc" value="{{ old('ruc', $ruc->valor ?? '') }}" required pattern="[0-9]{11}"
                                    maxlength="11" placeholder="11 dígitos numéricos" readonly>
                                <small class="text-muted">Ingrese exactamente 11 dígitos numéricos</small>
                                @error('ruc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Número de Teléfono <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono"
                                    name="telefono" value="{{ old('telefono', $telefono->valor ?? '') }}" required pattern="[0-9]{9}"
                                    maxlength="9" placeholder="9 dígitos numéricos" readonly
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)">
                                <small class="text-muted">Ingrese exactamente 9 dígitos numéricos</small>
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid" id="botonesAccion" style="display: none !important;">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success flex-fill">
                                        <i class="fas fa-save me-2"></i>Guardar Cambios
                                    </button>
                                    <button type="button" class="btn btn-secondary flex-fill" id="btnCancelar">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Diagnósticos --}}
                <div class="card" id="diagnosticos" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Diagnósticos</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.diagnostico.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nuevo diagnóstico..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($diagnosticos as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('configuracion.diagnostico.delete', $item->id_diagnostico) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar este diagnóstico?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay diagnósticos registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $diagnosticos->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Tratamientos --}}
                <div class="card" id="tratamientos" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-pills me-2"></i>Tratamientos</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.tratamiento.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nuevo tratamiento..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tratamientos as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('configuracion.tratamiento.delete', $item->id_tratamiento) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar este tratamiento?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay tratamientos registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $tratamientos->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Exámenes Recomendados --}}
                <div class="card" id="examenes" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Exámenes Recomendados</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.examen.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nuevo examen..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($examenes as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('configuracion.examen.delete', $item->id_examen_catalogo) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar este examen?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay exámenes registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $examenes->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Recetas --}}
                <div class="card" id="recetas" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-prescription me-2"></i>Recetas</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.receta.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nueva receta..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recetas as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form action="{{ route('configuracion.receta.delete', $item->id_receta) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar esta receta?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay recetas registradas</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $recetas->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Laboratorios --}}
                <div class="card" id="laboratorios" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-microscope me-2"></i>Laboratorios</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.laboratorio.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nuevo laboratorio..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($laboratorios as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('configuracion.laboratorio.delete', $item->id_laboratorio) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar este laboratorio?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay laboratorios registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $laboratorios->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Tipos de Cirugía --}}
                <div class="card" id="cirugias" style="display: none;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-scalpel me-2"></i>Tipos de Cirugía</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('configuracion.cirugia.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="nombre" placeholder="Nuevo tipo de cirugía..."
                                    required>
                                <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Agregar</button>
                            </div>
                        </form>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <label class="me-2">Mostrar</label>
                                <select class="form-select form-select-sm d-inline-block w-auto"
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
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th style="width: 80px;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tiposCirugia as $item)
                                        <tr>
                                            <td>{{ $item->nombre }}</td>
                                            <td>
                                                <form
                                                    action="{{ route('configuracion.cirugia.delete', $item->id_tipo_cirugia) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('¿Eliminar este tipo de cirugía?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No hay tipos de cirugía registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $tiposCirugia->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-cerrar alertas después de 5 segundos
            setTimeout(function () {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // Control de edición
            const btnEditar = document.getElementById('btnEditar');
            const btnCancelar = document.getElementById('btnCancelar');
            const botonesAccion = document.getElementById('botonesAccion');
            const campos = ['nombre_negocio', 'direccion', 'ruc', 'telefono'];
            const valoresOriginales = {};

            // Guardar valores originales
            campos.forEach(campo => {
                valoresOriginales[campo] = document.getElementById(campo).value;
            });

            // Botón Editar
            if (btnEditar) {
                btnEditar.addEventListener('click', function () {
                    campos.forEach(campo => {
                        document.getElementById(campo).removeAttribute('readonly');
                    });
                    botonesAccion.style.display = 'block';
                    btnEditar.style.display = 'none';
                });
            }

            // Botón Cancelar
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function () {
                    campos.forEach(campo => {
                        const input = document.getElementById(campo);
                        input.value = valoresOriginales[campo];
                        input.setAttribute('readonly', true);
                    });
                    botonesAccion.style.display = 'none';
                    btnEditar.style.display = 'block';
                });
            }

            // Validación para RUC solo números
            const rucInput = document.getElementById('ruc');
            if (rucInput) {
                rucInput.addEventListener('input', function (e) {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11);
                });
            }

            // Validación para Teléfono solo números
            const telefonoInput = document.getElementById('telefono');
            if (telefonoInput) {
                telefonoInput.addEventListener('input', function (e) {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 9);
                });
            }

            // Función para cambiar de sección
            function cambiarSeccion(seccionId) {
                // Ocultar todas las secciones
                document.querySelectorAll('#datosVeterinaria, #diagnosticos, #tratamientos, #examenes, #recetas, #laboratorios, #cirugias').forEach(el => {
                    el.style.display = 'none';
                });
                
                // Mostrar la sección seleccionada
                const seccion = document.getElementById(seccionId);
                if (seccion) {
                    seccion.style.display = 'block';
                }
                
                // Actualizar clases active en el menú
                document.querySelectorAll('.section-link').forEach(link => {
                    link.classList.remove('active');
                });
                const linkActivo = document.querySelector(`[data-section="${seccionId}"]`);
                if (linkActivo) {
                    linkActivo.classList.add('active');
                }
                
                // Scroll al inicio del contenido
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            // Manejar clicks en el menú lateral
            document.querySelectorAll('.section-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const seccionId = this.getAttribute('data-section');
                    cambiarSeccion(seccionId);
                });
            });

            // Mostrar la sección correcta al cargar la página
            const seccionActual = '{{ $seccion ?? "datos_veterinaria" }}';
            const mapeoSecciones = {
                'datos_veterinaria': 'datosVeterinaria',
                'diagnosticos': 'diagnosticos',
                'tratamientos': 'tratamientos',
                'examenes_recomendados': 'examenes',
                'recetas': 'recetas',
                'laboratorios': 'laboratorios',
                'cirugias': 'cirugias'
            };

            // Mostrar la sección inicial
            const seccionMostrar = mapeoSecciones[seccionActual] || 'datosVeterinaria';
            cambiarSeccion(seccionMostrar);
        });
    </script>
@endsection