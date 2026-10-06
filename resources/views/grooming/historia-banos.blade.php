@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        {{-- Sidebar Izquierdo con Info de la Mascota --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-paw fa-5x text-primary"></i>
                    </div>
                    <h4 class="mb-2">{{ $mascota->nombre }}</h4>
                    <span class="badge bg-success text-uppercase mb-3">{{ $mascota->especie ?? 'CANINO' }}</span>
                    
                    <div class="text-start mt-3">
                        <p class="mb-2"><strong>Raza:</strong> {{ $mascota->raza ?? 'No especificada' }}</p>
                        <p class="mb-2"><strong>Sexo:</strong> {{ $mascota->sexo ?? 'No especificado' }}</p>
                        <p class="mb-2"><strong>¿Esterilizado?:</strong> {{ $mascota->esterilizado ? 'Sí' : 'No' }}</p>
                        @if($mascota->fecha_nacimiento)
                        <p class="mb-2"><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') }}</p>
                        @endif
                        @php
                            $edad = 'No especificada';
                            if ($mascota->fecha_nacimiento) {
                                $fechaNac = \Carbon\Carbon::parse($mascota->fecha_nacimiento);
                                $years = $fechaNac->age;
                                $edad = $years . ' año' . ($years != 1 ? 's' : '');
                            } elseif ($mascota->edad) {
                                $edad = $mascota->edad . ' año' . ($mascota->edad != 1 ? 's' : '');
                            }
                        @endphp
                        <p class="mb-2"><strong>Edad:</strong> {{ $edad }}</p>
                    </div>
                </div>
            </div>

            {{-- Historial de Registros --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Registros
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if ($historial->count() > 0)
                        <div class="timeline">
                            @foreach ($historial as $registro)
                                <div class="timeline-item mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ \Carbon\Carbon::parse($registro->fecha_registro)->format('d/m/Y H:i') }}
                                    </small>
                                    <div class="mt-2">
                                        @if($registro->muerde)
                                            <span class="badge bg-danger me-1">
                                                <i class="fas fa-exclamation-triangle"></i> Muerde
                                            </span>
                                        @endif
                                        @if($registro->agresivo)
                                            <span class="badge bg-warning text-dark me-1">
                                                <i class="fas fa-angry"></i> Agresivo
                                            </span>
                                        @endif
                                        @if($registro->no_recibir)
                                            <span class="badge bg-dark me-1">
                                                <i class="fas fa-ban"></i> No Recibir
                                            </span>
                                        @endif
                                        @if($registro->tener_cuidado)
                                            <span class="badge bg-info me-1">
                                                <i class="fas fa-shield-alt"></i> Tener Cuidado
                                            </span>
                                        @endif
                                    </div>
                                    @if($registro->observaciones)
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-sticky-note me-1"></i>
                                            {{ $registro->observaciones }}
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No hay registros en la historia.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contenido Principal - Formulario de Registro --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Registrar Historia de Baño
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formHistoriaBano" action="{{ route('grooming.historia-banos.store', $mascota->id_mascota) }}" method="POST">
                        @csrf
                        
                        {{-- Información del Propietario (Solo lectura) --}}
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2">
                                <i class="fas fa-user me-2"></i>Nombre del Propietario:
                            </h6>
                            <p class="mb-0">
                                <strong>{{ $mascota->cliente->nombre }} {{ $mascota->cliente->apellido }}</strong>
                            </p>
                        </div>

                        {{-- Opciones con Switches --}}
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Características de Comportamiento</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    {{-- Muerde --}}
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                            <div>
                                                <i class="fas fa-exclamation-triangle text-danger me-2 fa-lg"></i>
                                                <strong>Muerde</strong>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="muerde" name="muerde" style="width: 3rem; height: 1.5rem;">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Agresivo --}}
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                            <div>
                                                <i class="fas fa-angry text-warning me-2 fa-lg"></i>
                                                <strong>Agresivo</strong>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="agresivo" name="agresivo" style="width: 3rem; height: 1.5rem;">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- No Recibir --}}
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                            <div>
                                                <i class="fas fa-ban text-dark me-2 fa-lg"></i>
                                                <strong>No Recibir</strong>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="no_recibir" name="no_recibir" style="width: 3rem; height: 1.5rem;">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tener Cuidado --}}
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                            <div>
                                                <i class="fas fa-shield-alt text-info me-2 fa-lg"></i>
                                                <strong>Tener Cuidado</strong>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="tener_cuidado" name="tener_cuidado" style="width: 3rem; height: 1.5rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-sticky-note me-2"></i>Observaciones
                            </label>
                            <textarea class="form-control" 
                                      id="observaciones"
                                      name="observaciones" 
                                      rows="5" 
                                      placeholder="Ingrese observaciones adicionales sobre el comportamiento de la mascota durante el baño..."></textarea>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('grooming.turnos-hoy') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver a Turnos
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="btnRegistrar">
                                <i class="fas fa-save me-1"></i> Registrar Historia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Envío del formulario
    $('#formHistoriaBano').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btnRegistrar');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(btnText);
                const message = xhr.responseJSON?.message || 'Error al guardar el registro';
                Swal.fire('Error', message, 'error');
            }
        });
    });
});
</script>
@endpush
