@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 grooming">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-cut me-2"></i>Grooming - Turnos de Hoy</h2>
            <div>
                <a href="{{ route('grooming.programados') }}" class="btn btn-outline-primary me-2">
                    <i class="fas fa-calendar-alt me-1"></i> Ver Programados
                </a>
            </div>
        </div>

        {{-- Tarjeta de Búsqueda y Botón de Nuevo Servicio --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('grooming.turnos-hoy') }}" id="formBuscarGrooming">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input type="date" class="form-control" name="fecha" id="fecha"
                                value="{{ request('fecha', $fecha) }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-6">
                            <label for="buscar" class="form-label">Buscar:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" id="buscar"
                                    placeholder="Buscar por nombre de mascota o cliente..."
                                    value="{{ request('buscar') }}" autocomplete="off">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('grooming.turnos-hoy', ['fecha' => request('fecha', $fecha)]) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <a href="{{ route('grooming.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus-circle me-1"></i> Crear Nuevo Servicio
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultados (Tabla de Turnos) --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Turnos - {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h5>
                <button type="button" class="btn btn-success btn-sm" id="btnExportarExcel">
                    <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                </button>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="me-2">Mostrar</label>
                        <select class="form-select form-select-sm d-inline-block w-auto" id="per_page"
                            onchange="window.location.href=this.value">
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}" {{ request('per_page', 50) == 10 ? 'selected' : '' }}>10</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 100]) }}" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ms-2">registros</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Cliente</th>
                                <th>Mascota</th>
                                <th>Raza</th>
                                <th>Servicios</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groomings as $grooming)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($grooming->fecha)->format('d-m-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($grooming->hora_entrada)->format('h:i A') }}</td>
                                    <td>
                                        {{ $grooming->cliente->nombre }} {{ $grooming->cliente->apellido }}
                                    </td>
                                    <td>
                                        <a href="{{ route('grooming.historia-banos', $grooming->mascota->id_mascota) }}" class="text-info">
                                            {{ $grooming->mascota->nombre }}
                                        </a>
                                    </td>
                                    <td>{{ $grooming->mascota->raza ?? 'N/A' }}</td>
                                    <td>
                                        {{ $grooming->servicio->nombre }}
                                    </td>
                                    <td>
                                        @if($grooming->estado == 'PENDIENTE')
                                            <span class="badge bg-warning text-dark">PENDIENTE</span>
                                        @elseif($grooming->estado == 'EN_PROCESO')
                                            <span class="badge bg-info">EN PROCESO</span>
                                        @elseif($grooming->estado == 'COMPLETADO')
                                            <span class="badge bg-success">COMPLETADO</span>
                                        @elseif($grooming->estado == 'CANCELADO')
                                            <span class="badge bg-danger">CANCELADO</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grooming->mascota->ultimaHistoriaBano)
                                            @php $historia = $grooming->mascota->ultimaHistoriaBano; @endphp
                                            @if($historia->muerde)
                                                <span class="badge bg-danger me-1" title="Muerde">
                                                    <i class="fas fa-exclamation-triangle"></i> Muerde
                                                </span>
                                            @endif
                                            @if($historia->agresivo)
                                                <span class="badge bg-warning text-dark me-1" title="Agresivo">
                                                    <i class="fas fa-angry"></i> Agresivo
                                                </span>
                                            @endif
                                            @if($historia->no_recibir)
                                                <span class="badge bg-dark me-1" title="No Recibir">
                                                    <i class="fas fa-ban"></i> No Recibir
                                                </span>
                                            @endif
                                            @if($historia->tener_cuidado)
                                                <span class="badge bg-info me-1" title="Tener Cuidado">
                                                    <i class="fas fa-shield-alt"></i> Tener Cuidado
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @if($grooming->estado == 'PENDIENTE' || $grooming->estado == 'EN_PROCESO')
                                                <button class="btn btn-outline-success btn-completar-grooming"
                                                    data-id="{{ $grooming->id_grooming }}" title="Marcar como Realizado">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            @endif
                                            @if($grooming->estado != 'CANCELADO' && $grooming->estado != 'COMPLETADO')
                                                <button class="btn btn-outline-warning btn-cancelar-grooming"
                                                    data-id="{{ $grooming->id_grooming }}" title="Cancelar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-danger btn-eliminar-grooming"
                                                data-id="{{ $grooming->id_grooming }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                                        <h4>No hay turnos para hoy</h4>
                                        @if(request('buscar'))
                                            <p class="text-muted">No se encontraron turnos que coincidan con la búsqueda.</p>
                                        @else
                                            <p class="text-muted">Aún no se han programado turnos de grooming para hoy.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if ($groomings->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Mostrando {{ $groomings->firstItem() }} a {{ $groomings->lastItem() }} de {{ $groomings->total() }} turnos
                        </div>
                        {{ $groomings->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .grooming .badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
$(document).ready(function() {
    // Exportar a Excel
    $('#btnExportarExcel').click(function() {
        const fechaActual = '{{ request("fecha", $fecha) }}';
        const buscarActual = '{{ request("buscar") }}';
        
        Swal.fire({
            title: 'Exportar a Excel',
            html: `
                <div class="mb-3 text-start">
                    <label for="fechaExport" class="form-label">Selecciona la fecha a exportar:</label>
                    <input type="text" class="form-control" id="fechaExport" readonly>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-download me-1"></i> Descargar Excel',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
            didOpen: () => {
                flatpickr("#fechaExport", {
                    locale: "es",
                    dateFormat: "Y-m-d",
                    defaultDate: fechaActual,
                    disableMobile: true,
                    allowInput: false
                });
            },
            preConfirm: () => {
                const fecha = document.getElementById('fechaExport').value;
                if (!fecha) {
                    Swal.showValidationMessage('Por favor selecciona una fecha');
                    return false;
                }
                return fecha;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const fecha = result.value;
                let url = `/grooming/exportar-excel?fecha=${fecha}`;
                if (buscarActual) {
                    url += `&buscar=${buscarActual}`;
                }
                window.location.href = url;
            }
        });
    });

    // Completar turno
    $('.btn-completar-grooming').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Completar turno?',
            text: 'El turno se marcará como completado',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, completar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/grooming/${id}/completar`,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    success: function(response) {
                        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al completar el turno', 'error');
                    }
                });
            }
        });
    });

    // Cancelar turno
    $('.btn-cancelar-grooming').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Cancelar turno?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/grooming/${id}/cancelar`,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    success: function(response) {
                        Swal.fire('Cancelado', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cancelar el turno', 'error');
                    }
                });
            }
        });
    });

    // Eliminar turno
    $('.btn-eliminar-grooming').click(function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar turno?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/grooming/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: CSRF_TOKEN
                    },
                    success: function(response) {
                        Swal.fire('Eliminado', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar el turno', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
