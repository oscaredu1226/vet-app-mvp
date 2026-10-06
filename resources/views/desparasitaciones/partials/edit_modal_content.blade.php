<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Desparasitación</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
    <form id="formEditarDesparasitacion" data-id="{{ $desparasitacion->id_desparasitacion }}">
        @csrf
        @method('PUT')
        
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Constantes Fisiológicas</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Peso (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" name="peso" value="{{ $desparasitacion->peso }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $desparasitacion->temperatura }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">FC (lat/min)</label>
                        <input type="number" class="form-control" name="frecuencia_cardiaca" value="{{ $desparasitacion->frecuencia_cardiaca }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">FR (resp/min)</label>
                        <input type="number" class="form-control" name="frecuencia_respiratoria" value="{{ $desparasitacion->frecuencia_respiratoria }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TLC (seg)</label>
                        <input type="number" step="0.1" class="form-control" name="tlc" value="{{ $desparasitacion->tlc }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hidratación (%)</label>
                        <input type="number" class="form-control" name="hidratacion" value="{{ $desparasitacion->hidratacion }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning bg-opacity-25">
                <h6 class="mb-0 fw-bold"><i class="fas fa-pills me-2"></i>Desparasitante Aplicado</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="edit_producto" class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_producto" name="producto" value="{{ $desparasitacion->producto }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_laboratorio" class="form-label">Laboratorio</label>
                        <input type="text" class="form-control" id="edit_laboratorio" name="laboratorio" value="{{ $desparasitacion->laboratorio }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_dosis" class="form-label">Dosis</label>
                        <input type="text" class="form-control" id="edit_dosis" name="dosis" value="{{ $desparasitacion->dosis }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_via_administracion" class="form-label">Vía de Administración</label>
                        <input type="text" class="form-control" id="edit_via_administracion" name="via_administracion" value="{{ $desparasitacion->via_administracion }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_tipo_parasito" class="form-label">Tipo de Parásito</label>
                        <input type="text" class="form-control" id="edit_tipo_parasito" name="tipo_parasito" value="{{ $desparasitacion->tipo_parasito }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Próxima Desparasitación</h6>
            </div>
            <div class="card-body">
                <label for="edit_proxima_dosis" class="form-label">Fecha Próxima Dosis</label>
                <input type="text" class="form-control fecha-input" id="edit_proxima_dosis" name="proxima_dosis" 
                    value="{{ $desparasitacion->proxima_dosis ? \Carbon\Carbon::parse($desparasitacion->proxima_dosis)->format('d-m-Y') : '' }}"
                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                    maxlength="10">
            </div>
        </div>

        <div class="mb-3">
            <label for="edit_observaciones" class="form-label fw-bold">Observaciones</label>
            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3">{{ $desparasitacion->observaciones }}</textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarDesparasitacion">
        <i class="fas fa-save me-2"></i>Actualizar Desparasitación
    </button>
</div>
