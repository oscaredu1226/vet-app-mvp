@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    
    {{-- Encabezado con información del cliente --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-paw me-2"></i>Mascotas de {{ $cliente->nombre }} {{ $cliente->apellido }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-phone me-2"></i>{{ $cliente->telefono }}
            </p>
        </div>
        <div>
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Clientes
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mascotaModal">
                <i class="fas fa-plus me-2"></i>Nueva Mascota
            </button>
        </div>
    </div>

    {{-- Tarjeta de lista de mascotas --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Mascotas</h5>
        </div>
        <div class="card-body p-0">
            @if($mascotas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                            @foreach($mascotas as $mascota)
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
                                <td>{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                                <td>
                                    {{ $mascota->edad_completa }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $mascota->estado == 'Activo' ? 'success' : 'secondary' }}">
                                        {{ $mascota->estado }}
                                    </span>
                                </td>
                                <td class="text-center btn-actions-cell">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-edit-mascota" 
                                                data-id="{{ $mascota->id_mascota }}" 
                                                title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="{{ route('historia.show', $mascota->id_mascota) }}" 
                                           class="btn btn-outline-info" 
                                           title="Historia Clínica">
                                            <i class="fas fa-file-medical"></i>
                                        </a>
                                        @if($mascota->estado != 'Fallecido')
                                        {{-- MVP_POSTERIOR: BEGIN Enviar a cola medica
<button type="button" class="btn btn-outline-success"
                                            onclick="agregarAColaMedica({{ $mascota->id_mascota }}, '{{ addslashes($mascota->nombre) }}')"
                                            title="Enviar a Cola Médica">
                                            <i class="fas fa-stethoscope"></i>
                                        </button>
MVP_POSTERIOR: END Enviar a cola medica --}}
                                        @endif
                                        <button type="button" class="btn btn-outline-danger btn-delete-mascota" 
                                                data-id="{{ $mascota->id_mascota }}" 
                                                data-nombre="{{ $mascota->nombre }}" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-paw fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Este cliente aún no tiene mascotas registradas.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mascotaModal">
                        <i class="fas fa-plus me-2"></i>Registrar Primera Mascota
                    </button>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Modal para Nueva Mascota --}}
<div class="modal fade" id="mascotaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-paw me-2"></i>Registrar Nueva Mascota para {{ $cliente->nombre }} {{ $cliente->apellido }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevaMascota" action="{{ route('mascotas.store') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="id_cliente" value="{{ $cliente->id_cliente }}">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Propietario:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}
                        <span class="ms-3"><i class="fas fa-phone me-1"></i>{{ $cliente->telefono }}</span>
                    </div>

                    <h6 class="border-bottom pb-2 mt-3"><i class="fas fa-paw me-2"></i>Datos de la Mascota</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_mascota" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_mascota" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control fecha-input" id="fecha_nacimiento" name="fecha_nacimiento" 
                                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                maxlength="10">
                            <small class="text-muted">Máximo 30 años atrás</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="especie" class="form-label">Especie <span class="text-danger">*</span></label>
                            <select class="form-select" id="especie" name="especie" required>
                                <option value="">Seleccionar...</option>
                                <option value="Canino">Canino</option>
                                <option value="Felino">Felino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="raza" class="form-label">Raza <span class="text-danger">*</span></label>
                            <select class="form-select" id="raza" name="raza" required>
                                <option value="">Seleccionar raza...</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="">Seleccionar...</option>
                                <option value="Macho">Macho</option>
                                <option value="Hembra">Hembra</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="esterilizado" class="form-label">Esterilizado</label>
                            <select class="form-select" id="esterilizado" name="esterilizado">
                                <option value="No">No</option>
                                <option value="Si">Sí</option>
                            </select>
                        </div>
                        <input type="hidden" id="estado" name="estado" value="Activo">
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

{{-- Modal para Editar Mascota --}}
<div class="modal fade" id="mascotaEditModal" tabindex="-1" aria-labelledby="mascotaEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- El contenido se carga dinámicamente -->
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const clienteIdSeleccionado = {{ $cliente->id_cliente }};
</script>
<script src="{{ asset('js/mascotas-razas.js') }}"></script>
<script src="{{ asset('js/mascotas.js') }}"></script>
@endpush
