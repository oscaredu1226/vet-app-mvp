<div class="card shadow-sm">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="fas fa-edit me-2"></i>Editar Cirugía
        </h5>
    </div>
    <div class="card-body">
        <form id="formCirugia" 
              action="{{ route('cirugias.update', [$mascota->id_mascota, $cirugia->id_cirugia]) }}" 
              method="POST"
              data-redirect-url="{{ route('historia.show', $mascota->id_mascota) }}">
            @csrf
            @method('PUT')
            
            <!-- 1. DATOS GENERALES -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha y Hora de Atención <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" name="fecha" 
                           value="{{ \Carbon\Carbon::parse($cirugia->fecha)->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Motivo de Atención</label>
                    <input type="text" class="form-control" name="motivo" 
                           value="{{ $cirugia->motivo }}" readonly style="background-color: #f8f9fa;">
                </div>
            </div>

            {{-- SECCIÓN DIAGNÓSTICO --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Diagnóstico Pre-operatorio <span class="text-danger">*</span></label>
                <textarea class="form-control" name="diagnostico" id="diagnostico" rows="3" required placeholder="Diagnóstico detallado...">{{ $cirugia->diagnostico }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones Pre-operatorias (Opcional)</label>
                <textarea class="form-control" name="observaciones_pre" rows="2">{{ $cirugia->observaciones_pre }}</textarea>
            </div>

            <!-- CONSTANTES PRE (Diseño Mejorado) -->
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
                                <input type="number" step="0.1" class="form-control" name="temperatura_pre" 
                                       value="{{ $cirugia->temperatura_pre }}" placeholder="38.5" required>
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-balance-scale text-muted"></i></span>
                                <input type="number" step="0.01" class="form-control" name="peso_pre" 
                                       value="{{ $cirugia->peso_pre }}" placeholder="0.00" required>
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frecuencia Cardiaca<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heartbeat"></i></span>
                                <input type="number" class="form-control" name="fc_pre" 
                                       value="{{ $cirugia->fc_pre }}" placeholder="80" required>
                                <span class="input-group-text">bpm</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frecuencia Respiratoria<span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lungs"></i></span>
                                <input type="number" class="form-control" name="fr_pre" 
                                       value="{{ $cirugia->fr_pre }}" placeholder="20" required>
                                <span class="input-group-text">rpm</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hidratación</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                <input type="text" class="form-control" name="hidratacion_pre" 
                                       value="{{ $cirugia->hidratacion_pre }}" placeholder="Normal">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">TLC (Tiempo Llenado Capilar)</label>
                            <div class="input-group">
                                <span class="input-group-text">TLC</span>
                                <input type="text" class="form-control" name="tlc_pre" 
                                       value="{{ $cirugia->tlc_pre }}" placeholder="< 2 seg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PROCEDIMIENTO -->
            <div class="mb-3">
                <label class="form-label fw-bold">Tipo de Cirugía <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="tipo_cirugia_search" 
                       value="{{ $cirugia->tipo_cirugia }}" placeholder="Buscar o escribir tipo de cirugía..." autocomplete="off">
                <input type="hidden" name="tipo_cirugia" id="tipo_cirugia" value="{{ $cirugia->tipo_cirugia }}" required>
                <div class="list-group position-absolute" id="tipo_cirugia_results" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                <small class="text-muted">Busque en el catálogo o escriba un nuevo tipo y presione Enter</small>
            </div>

            <!-- Médicos Cirujanos (Formato Bonito) -->
            <div class="mb-4">
                <div class="card border-light shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <label class="form-label fw-bold text-secondary"><i class="fas fa-user-md me-2"></i>Médicos Cirujanos</label>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-sm" id="tablaCirujanos">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Nombre <span class="text-danger">*</span></th>
                                    <th style="width: 30%">Cargo <span class="text-danger">*</span></th>
                                    <th style="width: 25%">CMV (Opcional)</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarFilaMedico('tablaCirujanos', true)">
                            <i class="fas fa-plus-circle me-1"></i> Añadir Cirujano
                        </button>
                        <textarea class="form-control mt-3 form-control-sm bg-light" name="observaciones_cirujanos" rows="2" placeholder="Comentarios sobre el equipo quirúrgico...">{{ $cirugia->observaciones_cirujanos }}</textarea>
                        <input type="hidden" name="cirujanos_data" id="cirujanos_data" value="{{ $cirugia->cirujanos_data }}">
                    </div>
                </div>
            </div>

            <!-- Médicos Anestesistas (Formato Bonito) -->
            <div class="mb-4">
                <div class="card border-light shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pb-0">
                        <label class="form-label fw-bold text-secondary"><i class="fas fa-syringe me-2"></i>Médicos Anestesistas</label>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-sm" id="tablaAnestesistas">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Nombre <span class="text-danger">*</span></th>
                                    <th style="width: 30%">Cargo <span class="text-danger">*</span></th>
                                    <th style="width: 25%">CMV (Opcional)</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarFilaMedico('tablaAnestesistas', false)">
                            <i class="fas fa-plus-circle me-1"></i> Añadir Anestesista
                        </button>
                        <input type="hidden" name="anestesistas_data" id="anestesistas_data" value="{{ $cirugia->anestesistas_data }}">
                    </div>
                </div>
            </div>

            <!-- Tratamiento Intraoperatorio -->
            <div class="mb-3">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">Tratamiento Intraoperatorio</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Agregar Medicamento</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="input_medicamento" placeholder="Nombre del medicamento">
                                <button type="button" class="btn btn-primary" onclick="agregarTratamiento()">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <table class="table table-sm table-bordered" id="tablaTratamientos">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Medicamento <span class="text-danger">*</span></th>
                                    <th style="width: 20%">Dosis <span class="text-danger">*</span></th>
                                    <th style="width: 20%">Vía </th>
                                    <th style="width: 20%">Volumen</th>
                                    <th style="width: 10%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Filas dinámicas --}}
                            </tbody>
                        </table>
                        
                        <textarea class="form-control mt-2" name="observaciones_tratamiento" rows="2" placeholder="Comentarios adicionales sobre el tratamiento (Opcional)">{{ $cirugia->observaciones_tratamiento ?? '' }}</textarea>
                        
                        {{-- Input oculto que enviará los datos consolidados --}}
                        <input type="hidden" name="tratamiento_aplicado" id="tratamiento_aplicado" value="{{ $cirugia->tratamiento_aplicado }}">
                    </div>
                </div>
            </div>

            <!-- 3. POST-QUIRÚRGICO -->
            <!-- CONSTANTES POST -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0 fw-bold">Constantes fisiológicas Post-Quirúrgicas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Temperatura (°C) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-thermometer-half"></i></span>
                                <input type="number" step="0.1" class="form-control" name="temperatura_post" 
                                       value="{{ $cirugia->temperatura_post }}" placeholder="38.5" required>
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-weight"></i></span>
                                <input type="number" step="0.01" class="form-control" name="peso_post" 
                                       value="{{ $cirugia->peso_post }}" placeholder="0.00" required>
                                <span class="input-group-text">kg</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frecuencia Cardiaca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-heartbeat"></i></span>
                                <input type="number" class="form-control" name="fc_post" 
                                       value="{{ $cirugia->fc_post }}" placeholder="80" required>
                                <span class="input-group-text">bpm</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frecuencia Respiratoria <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lungs"></i></span>
                                <input type="number" class="form-control" name="fr_post" 
                                       value="{{ $cirugia->fr_post }}" placeholder="20" required>
                                <span class="input-group-text">rpm</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NUEVA SECCIÓN: PRÓXIMA CITA Y MENSAJE --}}
            <div class="card mb-4 bg-light border-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="proxima_cita" class="form-label fw-bold"><i class="fas fa-calendar-plus me-2"></i>Próxima Cita (Opcional)</label>
                            <input type="datetime-local" class="form-control" name="proxima_cita" 
                                   value="{{ $cirugia->proxima_cita ? \Carbon\Carbon::parse($cirugia->proxima_cita)->format('Y-m-d\TH:i') : '' }}" 
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            <small class="text-muted">Se creará un evento en el calendario.</small>
                        </div>
                        <div class="col-md-6">
                            <label for="mensaje_proxima_cita" class="form-label fw-bold"><i class="fas fa-comment-alt me-2"></i>Mensaje del Evento</label>
                            <input type="text" class="form-control" name="mensaje_proxima_cita" 
                                   value="{{ $cirugia->mensaje_proxima_cita ?? '' }}" 
                                   placeholder="Por defecto: Control post-quirúrgico programado">
                            <small class="text-muted">Personaliza el recordatorio.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Actualizar Cirugía
                </button>
                <a href="{{ route('historia.show', $mascota->id_mascota) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
