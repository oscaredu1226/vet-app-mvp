<div class="modal-header bg-info text-white">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Antipulgas</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
    <form id="formEditarAntipulga" data-id="{{ $antipulga->id_antipulgas }}">
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
                        <input type="number" step="0.01" class="form-control" name="peso" value="{{ $antipulga->peso }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $antipulga->temperatura }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">FC (lat/min)</label>
                        <input type="number" class="form-control" name="frecuencia_cardiaca" value="{{ $antipulga->frecuencia_cardiaca }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">FR (resp/min)</label>
                        <input type="number" class="form-control" name="frecuencia_respiratoria" value="{{ $antipulga->frecuencia_respiratoria }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TLC (seg)</label>
                        <input type="number" step="0.1" class="form-control" name="tlc" value="{{ $antipulga->tlc }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hidratación (%)</label>
                        <input type="number" class="form-control" name="hidratacion" value="{{ $antipulga->hidratacion }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info bg-opacity-25">
                <h6 class="mb-0 fw-bold"><i class="fas fa-bug me-2"></i>Antipulgas Aplicado</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="edit_producto" class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_producto" name="producto" value="{{ $antipulga->producto }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_laboratorio" class="form-label">Laboratorio</label>
                        <input type="text" class="form-control" id="edit_laboratorio" name="laboratorio" value="{{ $antipulga->laboratorio }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_dosis" class="form-label">Dosis</label>
                        <input type="text" class="form-control" id="edit_dosis" name="dosis" value="{{ $antipulga->dosis }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="edit_via_administracion" class="form-label">Vía de Administración</label>
                    <input type="text" class="form-control" id="edit_via_administracion" name="via_administracion" value="{{ $antipulga->via_administracion }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Próxima Aplicación</h6>
            </div>
            <div class="card-body">
                <label for="edit_proxima_aplicacion" class="form-label">Fecha Próxima Aplicación</label>
                <input type="text" class="form-control fecha-input" id="edit_proxima_aplicacion" name="proxima_aplicacion" 
                    value="{{ $antipulga->proxima_aplicacion ? \Carbon\Carbon::parse($antipulga->proxima_aplicacion)->format('d-m-Y') : '' }}"
                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                    maxlength="10">
            </div>
        </div>

        <div class="mb-3">
            <label for="edit_observaciones" class="form-label fw-bold">Observaciones</label>
            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3">{{ $antipulga->observaciones }}</textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-info text-white" id="btnActualizarAntipulga">
        <i class="fas fa-save me-2"></i>Actualizar Antipulgas
    </button>
</div>
