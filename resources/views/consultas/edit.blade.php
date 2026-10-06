@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            @include('historia.partials.info_mascota', ['mascota' => $mascota])
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Consultas
                    </h6>
                </div>
                <div class="card-body">
                    @if ($historial_consultas->count() > 0)
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Peso</th>
                                        <th>Diagnóstico</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historial_consultas as $registro)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $registro->peso ? $registro->peso . ' kg' : '-' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($registro->diagnostico, 50) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No hay registros de consultas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Consulta Médica
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formConsulta" 
                          action="{{ route('consultas.update', ['mascota' => $mascota->id_mascota, 'id' => $consulta->id_consulta]) }}" 
                          method="POST"
                          data-redirect-url="{{ route('historia.show', $mascota->id_mascota) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha y Hora de Atención <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                    class="form-control" 
                                    name="fecha" 
                                    value="{{ \Carbon\Carbon::parse($consulta->fecha)->format('Y-m-d\TH:i') }}" 
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="motivo" class="form-label">Motivo de Atención</label>
                                <input type="text" class="form-control" name="motivo" value="{{ $consulta->motivo }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="anamnesis" class="form-label fw-bold">Anamnesis y descripción del caso (Opcional)</label>
                            <textarea class="form-control" id="anamnesis" name="anamnesis" rows="4" placeholder="Describir síntomas, comportamiento, historial relevante...">{{ $consulta->anamnesis }}</textarea>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Constantes fisiológicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Temperatura (°C) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-thermometer-half"></i></span>
                                            <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $consulta->temperatura }}" placeholder="38.5">
                                            <span class="input-group-text">°C</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-balance-scale text-muted"></i></span>
                                            <input type="number" step="0.01" class="form-control" name="peso" value="{{ $consulta->peso }}" placeholder="0.00" required>
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Frecuencia Cardiaca</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-heartbeat"></i></span>
                                            <input type="number" class="form-control" name="frecuencia_cardiaca" value="{{ $consulta->frecuencia_cardiaca }}" placeholder="80">
                                            <span class="input-group-text">bpm</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Frecuencia Respiratoria</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lungs"></i></span>
                                            <input type="number" class="form-control" name="frecuencia_respiratoria" value="{{ $consulta->frecuencia_respiratoria }}" placeholder="30">
                                            <span class="input-group-text">rpm</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Hidratación</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                            <input type="text" class="form-control" name="hidratacion" value="{{ $consulta->hidratacion }}" placeholder="Normal">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">TLC (Tiempo Llenado Capilar)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TLC</span>
                                            <input type="text" class="form-control" name="tlc_tiempo_llenado" value="{{ $consulta->tlc_tiempo_llenado }}" placeholder="< 2 seg">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="examen_fisico" class="form-label fw-bold">Examen clínico (Opcional)</label>
                            <textarea class="form-control" id="examen_fisico" name="examen_fisico" rows="4" placeholder="Resultados del examen físico completo...">{{ $consulta->examen_fisico }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="diagnostico" class="form-label fw-bold">Diagnóstico <span class="text-danger">*</span></label>
                            <div class="input-group mb-2" style="position: relative;">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="buscar_diagnostico" placeholder="Buscar diagnóstico o escribir y presionar Enter..." autocomplete="off">
                                <div id="resultados_diagnostico" class="border rounded mt-1" style="display:none; position:absolute; top:100%; left:0; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                            </div>
                            <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3" required placeholder="Diagnóstico detallado...">{{ $consulta->diagnostico }}</textarea>
                        </div>

                        {{-- MVP_POSTERIOR: BEGIN Examenes y recetas
<div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Exámenes Auxiliares (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3" style="position: relative;">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscar_examen" placeholder="Buscar examen o escribir y presionar Enter..." autocomplete="off">
                                    <div id="resultados_examen" class="border rounded mt-1" style="display:none; position:absolute; top:100%; left:0; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                </div>
                                <div id="tablaExamenes" class="d-flex gap-2 overflow-x-auto pb-2" style="flex-wrap: nowrap;"></div>
                                <input type="hidden" name="examenes" id="examenes" value="{{ $consulta->examenes }}">
                            </div>
                        </div>
MVP_POSTERIOR: END Examenes y recetas --}}

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Tratamiento (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3" style="position: relative;">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscar_tratamiento" placeholder="Buscar tratamiento o escribir y presionar Enter..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-primary d-md-none" id="btn_agregar_tratamiento" 
                                            style="display:none;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <div id="resultados_tratamiento" class="border rounded mt-1" style="display:none; position:absolute; top:100%; left:0; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                </div>
                                <div id="tablaTratamientos" class="d-flex gap-2 overflow-x-auto pb-2" style="flex-wrap: nowrap;"></div>
                                <input type="hidden" name="plan_tratamiento" id="plan_tratamiento" value="{{ $consulta->plan_tratamiento }}">
                            </div>
                        </div>

                        {{-- MVP_POSTERIOR: BEGIN Examenes y recetas
<div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Receta (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3" style="position: relative;">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscar_receta" placeholder="Buscar medicamento o escribir y presionar Enter..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-primary d-md-none" id="btn_agregar_receta" 
                                            style="display:none;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <div id="resultados_receta" class="border rounded mt-1" style="display:none; position:absolute; top:100%; left:0; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                                </div>
                                <div id="tablaRecetas" class="d-flex gap-2 overflow-x-auto pb-2" style="flex-wrap: nowrap;"></div>
                                <input type="hidden" name="receta" id="receta" value="{{ $consulta->receta }}">
                            </div>
                        </div>
MVP_POSTERIOR: END Examenes y recetas --}}

                        <div class="mb-3">
                            <label for="observaciones" class="form-label fw-bold">Observaciones (Opcional)</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Observaciones adicionales...">{{ $consulta->observaciones }}</textarea>
                        </div>

                        {{-- NUEVA SECCIÓN: PRÓXIMA CITA Y MENSAJE --}}
                        <div class="card mb-4 bg-light border-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="proxima_cita" class="form-label fw-bold"><i class="fas fa-calendar-plus me-2"></i>Próxima Cita (Opcional)</label>
                                        @include('eventos.partials.proxima_cita', ['value' => $consulta->proxima_cita])
                                        <small class="text-muted">Horarios cada 30 minutos (:00 o :30). Se actualizará la cita en el calendario.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mensaje_proxima_cita" class="form-label fw-bold"><i class="fas fa-comment-alt me-2"></i>Mensaje del Evento</label>
                                        <input type="text" class="form-control" name="mensaje_proxima_cita" value="{{ $consulta->mensaje_proxima_cita }}" placeholder="Por defecto: Consulta de control programada">
                                        <small class="text-muted">Personaliza el recordatorio.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success" data-original-text="Actualizar Consulta">
                                <i class="fas fa-save me-2"></i>Actualizar Consulta
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

@endsection

@push('scripts')
    <script src="{{ asset('js/consultas.js') }}"></script>
@endpush
