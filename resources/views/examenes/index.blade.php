@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-vial me-2"></i>Gestión de Exámenes</h2>
        </div>

        {{-- Tarjetas de Resumen --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">Total Exámenes <h4 class="mb-0">{{ $stats['total_examenes'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">Pagados <h4 class="mb-0">{{ $stats['examenes_pagados'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">Subidos a Historia <h4 class="mb-0">{{ $stats['subidos_historia'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">Lecturas Realizadas <h4 class="mb-0">{{ $stats['lecturas_realizadas'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Búsqueda y Botón Nuevo --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('examenes.index') }}">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar_termino" autocomplete="off"
                                    placeholder="Buscar por mascota, propietario o análisis..."
                                    value="{{ request('buscar_termino') }}">

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>

                                @if(request('buscar_termino'))
                                    <a href="{{ route('examenes.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#nuevoExamenModal">
                            <i class="fas fa-plus-circle me-1"></i> Nuevo Examen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de Exámenes [cite: 910-925] --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Todos los Exámenes</h5>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportarExamenes">
                    <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                </button>
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
                                <th>Mascota / Propietario</th>
                                <th>Análisis / Laboratorio</th>
                                <th>Costo</th>
                                <th>Fecha Envío</th>
                                <th>Pagado</th>
                                <th>Subido</th>
                                <th>Lectura</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($examenes as $examen)
                                <tr>
                                    <td>
                                        <strong>{{ $examen->mascota->nombre }}</strong>
                                        <small class="d-block text-muted">{{ $examen->mascota->cliente->nombre ?? '' }}
                                            {{ $examen->mascota->cliente->apellido ?? '' }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $examen->tipo_analisis }}</strong>
                                        <small class="d-block text-muted">{{ $examen->laboratorio }}</small>
                                    </td>
                                    <td class="fw-bold text-success">S/ {{ number_format($examen->costo, 2) }}</td>
                                    <td>{{ $examen->fecha_envio->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($examen->pagado)
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($examen->subido_historia)
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($examen->lectura_realizada)
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-edit-examen"
                                                data-id="{{ $examen->id_examen_lab }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-delete-examen"
                                                data-id="{{ $examen->id_examen_lab }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-vial fa-4x text-muted mb-3"></i>
                                        <h4>No hay exámenes registrados</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Paginación --}}
                <div class="d-flex justify-content-center">
                    {{ $examenes->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA NUEVO EXAMEN (de examenes.php) [cite: 928-953] --}}
    <div class="modal fade" id="nuevoExamenModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-vial me-2"></i> Registrar Nuevo Examen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoExamen" action="{{ route('examenes.store') }}" method="POST">
                        @csrf
                        {{-- (Pega aquí el HTML completo del formulario de 'modules/examenes.php' [cite: 929-952]) --}}
                        {{-- Ejemplo de campos migrados: --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="mascota_search" class="form-label fw-semibold">Buscar Mascota <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mascota_search" autocomplete="off"
                                    placeholder="Escriba el nombre...">
                                <input type="hidden" id="mascota_select" name="id_mascota" required>
                                <div id="mascota_results" class="dropdown-menu w-100"></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="laboratorio" class="form-label fw-semibold">Laboratorio <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="laboratorio_search" autocomplete="off"
                                    placeholder="Buscar o escribir laboratorio..." autocomplete="off">
                                <input type="hidden" id="laboratorio" name="laboratorio" required>
                                <div id="laboratorio_results" class="list-group mt-1"
                                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000;">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="tipo_analisis" class="form-label fw-semibold">Tipo de Análisis <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tipo_analisis_search" autocomplete="off"
                                    placeholder="Buscar o escribir tipo de análisis..." autocomplete="off">
                                <input type="hidden" id="tipo_analisis" name="tipo_analisis" required>
                                <div id="tipo_analisis_results" class="list-group mt-1"
                                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000;">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="costo" class="form-label fw-semibold">Costo (S/) <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="costo" name="costo"
                                    required>
                            </div>
                            <div class="col-sm-6">
                                <label for="fecha_envio" class="form-label fw-semibold">Fecha de Envío <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control fecha-input" id="fecha_envio" name="fecha_envio" autocomplete="off"
                                    value="{{ today()->format('d-m-Y') }}"
                                    required
                                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                    maxlength="10">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="pagado" name="pagado"
                                        value="1">
                                    <label class="form-check-label" for="pagado">
                                        <i class="fas fa-money-bill-wave me-1"></i>¿Pagado?
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="subido_historia"
                                        name="subido_historia" value="1">
                                    <label class="form-check-label" for="subido_historia">
                                        <i class="fas fa-upload me-1"></i>¿Subido a Historia?
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="lectura_realizada"
                                        name="lectura_realizada" value="1">
                                    <label class="form-check-label" for="lectura_realizada">
                                        <i class="fas fa-check-circle me-1"></i>¿Lectura Realizada?
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarExamen">Guardar Examen</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR EXAMEN (Cargará contenido AJAX) --}}
    <div class="modal fade" id="editarExamenModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{-- Contenido AJAX de editar_examen.php --}}
            </div>
        </div>
    </div>

    {{-- Modal para Exportar Exámenes --}}
    <div class="modal fade" id="modalExportarExamenes" tabindex="-1" aria-labelledby="modalExportarExamenesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExportarExamenesLabel">
                        <i class="fas fa-file-excel me-2"></i>Exportar Exámenes a Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formExportarExamenes" action="{{ route('examenes.exportar') }}" method="GET">
                    <div class="modal-body">
                        <p class="text-muted mb-3">Seleccione el rango de fechas para exportar los exámenes:</p>
                        
                        <div class="mb-3">
                            <label for="fecha_inicio_export" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_inicio_export" 
                                   name="fecha_inicio" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_fin_export" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Final <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_fin_export" 
                                   name="fecha_fin" 
                                   required>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Se exportarán todos los exámenes registrados en el rango de fechas seleccionado.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Crea 'public/js/examenes.js' y pega el JS de 'modules/examenes.php' [cite: 928-997] --}}
    {{-- Asegúrate de actualizar las URLs de AJAX en ese archivo JS: --}}
    {{-- 'modules/procesar_examen.php' -> '{{ route('examenes.store') }}' --}}
    {{-- 'modules/editar_examen.php?id=' -> BASE_URL + '/examenes/' + id + '/edit' --}}
    {{-- 'modules/eliminar_examen.php' -> BASE_URL + '/examenes/' + id --}}
    {{-- 'modules/buscar_mascotas.php' -> '{{ route('mascotas.buscar') }}' --}}
    <script>
        // Datos de laboratorios y tipos de análisis - Disponibles globalmente
        window.laboratorios = @json($laboratorios->pluck('nombre'));
        window.tiposAnalisis = @json($tiposAnalisis->pluck('nombre'));

        // Búsqueda de laboratorio
        $('#laboratorio_search').on('input', function () {
            const termino = $(this).val().toLowerCase();
            const resultados = $('#laboratorio_results');

            if (termino.length === 0) {
                resultados.empty().hide();
                $('#laboratorio').val('');
                return;
            }

            const coincidencias = laboratorios.filter(lab =>
                lab.toLowerCase().includes(termino)
            );

            if (coincidencias.length > 0) {
                resultados.empty().show();
                coincidencias.forEach(lab => {
                    resultados.append(`
                                <a href="#" class="list-group-item list-group-item-action select-laboratorio" data-nombre="${lab}">
                                    ${lab}
                                </a>
                            `);
                });
            } else {
                resultados.empty().hide();
            }

            // Permitir valor escrito manualmente
            $('#laboratorio').val($(this).val());
        });

        // Seleccionar laboratorio
        $(document).on('click', '.select-laboratorio', function (e) {
            e.preventDefault();
            const nombre = $(this).data('nombre');
            $('#laboratorio_search').val(nombre);
            $('#laboratorio').val(nombre);
            $('#laboratorio_results').empty().hide();
        });

        // Búsqueda de tipo de análisis
        $('#tipo_analisis_search').on('input', function () {
            const termino = $(this).val().toLowerCase();
            const resultados = $('#tipo_analisis_results');

            if (termino.length === 0) {
                resultados.empty().hide();
                $('#tipo_analisis').val('');
                return;
            }

            const coincidencias = tiposAnalisis.filter(tipo =>
                tipo.toLowerCase().includes(termino)
            );

            if (coincidencias.length > 0) {
                resultados.empty().show();
                coincidencias.forEach(tipo => {
                    resultados.append(`
                                <a href="#" class="list-group-item list-group-item-action select-tipo-analisis" data-nombre="${tipo}">
                                    ${tipo}
                                </a>
                            `);
                });
            } else {
                resultados.empty().hide();
            }

            // Permitir valor escrito manualmente
            $('#tipo_analisis').val($(this).val());
        });

        // Seleccionar tipo de análisis
        $(document).on('click', '.select-tipo-analisis', function (e) {
            e.preventDefault();
            const nombre = $(this).data('nombre');
            $('#tipo_analisis_search').val(nombre);
            $('#tipo_analisis').val(nombre);
            $('#tipo_analisis_results').empty().hide();
        });

        // Cerrar resultados al hacer clic fuera
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#laboratorio_search, #laboratorio_results').length) {
                $('#laboratorio_results').empty().hide();
            }
            if (!$(e.target).closest('#tipo_analisis_search, #tipo_analisis_results').length) {
                $('#tipo_analisis_results').empty().hide();
            }
        });

        // Manejar envío del formulario de exportación y cerrar modal después
        $('#formExportarExamenes').on('submit', function() {
            setTimeout(function() {
                const modalElement = document.getElementById('modalExportarExamenes');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
            }, 500);
        });
    </script>
    <script src="{{ asset('js/examenes.js') }}"></script>
@endpush