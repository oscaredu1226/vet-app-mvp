@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <!-- Columna izquierda - Información de la mascota -->
        <div class="col-md-4">
            @include('historia.partials.info_mascota', ['mascota' => $mascota])
            
            <!-- Historia Médica de Desparasitaciones -->
            @if($historial && $historial->count() > 0)
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historia Médica
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Peso</th>
                                    <th>Producto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historial as $registro)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                    <td>{{ $registro->peso ? $registro->peso . ' kg' : '-' }}</td>
                                    <td>
                                        <small>{{ $registro->producto }}</small>
                                        @if($registro->observaciones)
                                            <br><small class="text-muted">{{ Str::limit($registro->observaciones, 30) }}</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historia Médica
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-2">
                        <i class="fas fa-info-circle text-muted fa-lg mb-2"></i>
                        <p class="text-muted mb-0 small">No hay registros de desparasitaciones.</p>
                        <small class="text-muted">Los registros aparecerán aquí después de guardar tratamientos.</small>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Columna derecha - Formulario de desparasitación -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-pills me-2"></i>Desparasitación
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formDesparasitacion" action="{{ route('desparasitaciones.store', $mascota->id_mascota) }}" method="POST">
                        @csrf
                        
                        <!-- Fecha y Hora de Atención -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha y Hora de Atención <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                    class="form-control" 
                                    name="fecha" 
                                    value="{{ now('America/Lima')->format('Y-m-d\\TH:i') }}" 
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motivo de Atención</label>
                                <input type="text" class="form-control" value="Desparasitación" readonly>
                            </div>
                        </div>

                        <!-- Constantes fisiológicas -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Constantes fisiológicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-balance-scale text-muted"></i>
                                            </span>
                                            <input type="number" step="0.01" class="form-control" 
                                                   name="peso" placeholder="0.00" required>
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Temperatura (°C)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-thermometer-half text-muted"></i>
                                            </span>
                                            <input type="number" step="0.1" class="form-control" 
                                                   name="temperatura" placeholder="38.5">
                                            <span class="input-group-text">°C</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Desparasitantes -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Desparasitantes</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="buscar_tipo" id="buscar_solo_catalogo" value="catalogo" checked>
                                    <label class="form-check-label" for="buscar_solo_catalogo">
                                        Buscar en el catálogo de productos y servicios
                                    </label>
                                </div>
                                
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Escriba el nombre del desparasitante y presione Enter para agregarlo, o busque en el catálogo
                                </p>
                                
                                <div class="position-relative">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="buscar_desparasitante" autocomplete="off" 
                                               placeholder="Buscar o agregar desparasitante">
                                        <button type="button" class="btn btn-outline-primary d-md-none" id="btn_agregar_desparasitante" 
                                                style="display:none;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div id="resultados_desparasitante" class="border rounded mt-1" 
                                         style="display:none; position:absolute; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto;"></div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Desparasitante</th>
                                                <th>Dosis</th>
                                                <th>Vía</th>
                                                <th>Tipo Parásito</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaDesparasitantes">
                                            <!-- Se llenará dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <input type="hidden" id="producto" name="producto" required>
                                <input type="hidden" id="dosis" name="dosis">
                                <input type="hidden" id="via_administracion" name="via_administracion">
                                <input type="hidden" id="tipo_parasito" name="tipo_parasito">
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" 
                                      rows="3" placeholder="Observaciones adicionales sobre el tratamiento..."></textarea>
                        </div>

                        {{-- SECCIÓN: PRÓXIMA CITA Y MENSAJE --}}
                        <div class="card mb-4 bg-light border-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="proxima_cita" class="form-label fw-bold"><i class="fas fa-calendar-plus me-2"></i>Próxima Cita (Opcional)</label>
                                        <input type="datetime-local" class="form-control" name="proxima_cita" min="{{ now()->format('Y-m-d\TH:i') }}">
                                        <small class="text-muted">Se creará un evento en el calendario.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mensaje_proxima_cita" class="form-label fw-bold"><i class="fas fa-comment-alt me-2"></i>Mensaje del Evento</label>
                                        <input type="text" class="form-control" name="mensaje_proxima_cita" value="{{ $desparasitacion->mensaje_proxima_cita ?? '' }}" placeholder="Por defecto: Desparasitación de control programada">
                                        <small class="text-muted">Personaliza el recordatorio.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Guardar Desparasitación
                            </button>
                            <a href="{{ route('historia.show', $mascota->id_mascota) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/desparasitacion.js') }}"></script>
@endpush

@endsection