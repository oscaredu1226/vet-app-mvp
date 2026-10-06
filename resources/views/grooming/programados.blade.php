@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 grooming">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Grooming - Programados</h2>
            <a href="{{ route('grooming.turnos-hoy') }}" class="btn btn-outline-primary">
                <i class="fas fa-clock me-1"></i> Ver Turnos de Hoy
            </a>
        </div>

        {{-- Tarjeta de Búsqueda y Botón de Nuevo Servicio --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('grooming.programados') }}" id="formBuscarGroomingProgramados">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar"
                                    placeholder="Buscar por nombre de mascota o cliente..."
                                    value="{{ request('buscar') }}" autocomplete="off">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('grooming.programados') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 d-grid">
                            <a href="{{ route('grooming.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> Crear Nuevo Servicio
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultados (Tabla de Turnos Programados) --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Turnos Programados</h5>
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
                                <th># Turno</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Cliente</th>
                                <th>Mascota</th>
                                <th>Raza</th>
                                <th>Servicios</th>
                                <th>Estado</th>
                                <th class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groomings as $grooming)
                                <tr>
                                    <td>{{ $grooming->numero_turno }}</td>
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
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info btn-view-grooming"
                                                data-id="{{ $grooming->id_grooming }}" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-primary btn-edit-grooming"
                                                data-id="{{ $grooming->id_grooming }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($grooming->estado != 'CANCELADO' && $grooming->estado != 'COMPLETADO')
                                                <button class="btn btn-outline-danger btn-cancelar-grooming"
                                                    data-id="{{ $grooming->id_grooming }}" title="Cancelar">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
                                        <h4>No hay turnos programados</h4>
                                        @if(request('buscar'))
                                            <p class="text-muted">No se encontraron turnos que coincidan con la búsqueda.</p>
                                        @else
                                            <p class="text-muted">Aún no se han programado turnos de grooming para fechas futuras.</p>
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

    {{-- Modal para Ver Detalles --}}
    <div class="modal fade" id="viewGroomingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" id="viewGroomingModalContent">
                <!-- El contenido se cargará vía AJAX -->
            </div>
        </div>
    </div>

    {{-- Modal para Editar --}}
    <div class="modal fade" id="editGroomingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" id="editGroomingModalContent">
                <!-- El contenido se cargará vía AJAX -->
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
<script>
$(document).ready(function() {
    // Ver detalles del turno
    $('.btn-view-grooming').click(function() {
        const id = $(this).data('id');
        $.get(`/grooming/${id}`, function(html) {
            $('#viewGroomingModalContent').html(html);
            $('#viewGroomingModal').modal('show');
        });
    });

    // Editar turno
    $('.btn-edit-grooming').click(function() {
        const id = $(this).data('id');
        $.get(`/grooming/${id}/edit`, function(html) {
            $('#editGroomingModalContent').html(html);
            $('#editGroomingModal').modal('show');
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
});
</script>
@endpush
