@extends('layouts.app') {{-- 1. Heredamos la plantilla principal --}}

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            {{-- 2. Incluimos el parcial de info de mascota --}}
            {{-- $mascota es pasada por el VacunaController@create --}}
            @include('historia.partials.info_mascota', ['mascota' => $mascota])
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Vacunas
                    </h6>
                </div>
                <div class="card-body">
                    @if ($historial_vacunas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Peso</th>
                                        <th>Vacuna</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- 3. Bucle de Blade (reemplaza tu 'while') --}}
                                    @foreach ($historial_vacunas as $registro)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $registro->peso ? $registro->peso . ' kg' : '-' }}</td>
                                        <td>{{ $registro->vacuna_aplicada }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No hay registros de vacunas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-syringe me-2"></i>Registro de Vacuna
                    </h5>
                </div>
                <div class="card-body">
                    {{-- 4. Formulario actualizado para Laravel --}}
                    <form id="formVacuna" 
                          action="{{ route('vacunas.store', $mascota) }}" 
                          method="POST"
                          data-redirect-url="{{ route('historia.show', $mascota) }}">
                        @csrf {{-- Token de seguridad de Laravel --}}
                        
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
                                <input type="text" class="form-control" name="motivo_atencion" value="Vacuna" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="anamnesis_vacuna" class="form-label fw-bold">Anamnesis</label>
                            <textarea class="form-control" id="anamnesis_vacuna" name="anamnesis" rows="3" placeholder="(Opcional)"></textarea>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Constantes Fisiológicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Peso (kg)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-balance-scale text-muted"></i></span>
                                            <input type="number" step="0.01" class="form-control" name="peso" placeholder="0.00">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Temperatura (°C) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-thermometer-half"></i></span>
                                            <input type="number" step="0.1" class="form-control" name="temperatura" placeholder="38.5" required>
                                            <span class="input-group-text">°C</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-info bg-opacity-25">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-syringe me-2"></i>Vacuna Aplicada <span class="text-danger">*</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscar_vacuna" placeholder="Buscar o agregar vacuna..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-primary d-md-none" id="btn_agregar_vacuna" 
                                            style="display:none;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="resultados_vacuna" class="dropdown-menu w-100"></div>
                                
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vacuna</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaVacunas">
                                        </tbody>
                                </table>
                                
                                {{-- Campos ocultos para enviar los datos --}}
                                <input type="hidden" id="vacuna_aplicada" name="vacuna_aplicada">
                                <input type="hidden" id="laboratorio" name="laboratorio">
                                <input type="hidden" id="lote" name="lote">
                                <input type="hidden" id="vencimiento" name="vencimiento">
                                <input type="hidden" id="via_administracion" name="via_administracion">
                                <input type="hidden" id="dosis" name="dosis">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones_vacuna" class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="observaciones_vacuna" name="observaciones" rows="3" placeholder="Reacciones, recomendaciones..."></textarea>
                        </div>

                        <div class="card mb-3">
                        {{-- NUEVA SECCIÓN: PRÓXIMA VACUNA Y MENSAJE --}}
                        <div class="card mb-4 bg-light border-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="proxima_dosis" class="form-label fw-bold"><i class="fas fa-calendar-plus me-2"></i>Próxima Vacuna (Opcional)</label>
                                        <input type="datetime-local" class="form-control" name="proxima_dosis" min="{{ now()->format('Y-m-d\TH:i') }}">
                                        <small class="text-muted">Se creará un evento en el calendario.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mensaje_proxima_cita" class="form-label fw-bold"><i class="fas fa-comment-alt me-2"></i>Mensaje del Evento</label>
                                        <input type="text" class="form-control" name="mensaje_proxima_cita" placeholder="Por defecto: Vacuna de refuerzo programada">
                                        <small class="text-muted">Personaliza el recordatorio.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info" data-original-text="Guardar Vacuna">
                                <i class="fas fa-save me-2"></i>Guardar Vacuna
                            </button>
                            <a href="{{ route('historia.show', $mascota) }}" class="btn btn-secondary">
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
    {{-- 5. Cargamos el JS específico para esta vista --}}
    <script src="{{ asset('js/vacuna.js') }}"></script>
@endpush