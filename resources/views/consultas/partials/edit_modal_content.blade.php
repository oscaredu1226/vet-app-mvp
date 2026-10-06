<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Consulta</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
    <form id="formEditarConsulta" data-id="{{ $consulta->id_consulta }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="edit_anamnesis" class="form-label fw-bold">Anamnesis y descripción del caso</label>
            <textarea class="form-control" id="edit_anamnesis" name="anamnesis" rows="4">{{ $consulta->anamnesis }}</textarea>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Constantes Fisiológicas</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $consulta->temperatura }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Peso (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="peso" value="{{ $consulta->peso }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">FC (bpm)</label>
                        <input type="number" class="form-control" name="frecuencia_cardiaca" value="{{ $consulta->frecuencia_cardiaca }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">FR (rpm)</label>
                        <input type="number" class="form-control" name="frecuencia_respiratoria" value="{{ $consulta->frecuencia_respiratoria }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">TLC</label>
                        <input type="text" class="form-control" name="tlc_tiempo_llenado" value="{{ $consulta->tlc_tiempo_llenado }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hidratación</label>
                        <input type="text" class="form-control" name="hidratacion" value="{{ $consulta->hidratacion }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="edit_examen_fisico" class="form-label fw-bold">Examen Clínico</label>
            <textarea class="form-control" id="edit_examen_fisico" name="examen_fisico" rows="4">{{ $consulta->examen_fisico }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_diagnostico" class="form-label fw-bold">Diagnóstico <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_diagnostico" name="diagnostico" rows="3" required>{{ $consulta->diagnostico }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_examenes" class="form-label fw-bold">Exámenes Auxiliares</label>
            <textarea class="form-control" id="edit_examenes" name="examenes" rows="2">{{ $consulta->examenes }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_plan_tratamiento" class="form-label fw-bold">Plan de Tratamiento</label>
            <textarea class="form-control" id="edit_plan_tratamiento" name="plan_tratamiento" rows="2">{{ $consulta->plan_tratamiento }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_receta" class="form-label fw-bold">Receta</label>
            <textarea class="form-control" id="edit_receta" name="receta" rows="2">{{ $consulta->receta }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_observaciones" class="form-label fw-bold">Observaciones</label>
            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="2">{{ $consulta->observaciones }}</textarea>
        </div>

        <div class="mb-3">
            <label for="edit_proxima_cita" class="form-label fw-bold">Próxima Cita</label>
            <input type="text" class="form-control fecha-input" id="edit_proxima_cita" name="proxima_cita" 
                value="{{ $consulta->proxima_cita ? \Carbon\Carbon::parse($consulta->proxima_cita)->format('d-m-Y') : '' }}"
                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                maxlength="10">
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-primary" id="btnActualizarConsulta">
        <i class="fas fa-save me-2"></i>Actualizar Consulta
    </button>
</div>
