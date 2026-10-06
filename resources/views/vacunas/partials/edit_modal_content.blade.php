<div class="modal-header bg-success text-white">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Vacuna</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
    <form id="formEditarVacuna" data-id="{{ $vacuna->id_vacuna }}">
        @csrf
        @method('PUT')
        
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Constantes Fisiológicas</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" step="0.01" class="form-control" name="peso" value="{{ $vacuna->peso }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Temperatura (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $vacuna->temperatura }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success bg-opacity-25">
                <h6 class="mb-0 fw-bold"><i class="fas fa-syringe me-2"></i>Información de la Vacuna</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="edit_vacuna_aplicada" class="form-label fw-bold">Vacuna Aplicada <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_vacuna_aplicada" name="vacuna_aplicada" value="{{ $vacuna->vacuna_aplicada }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_laboratorio" class="form-label">Laboratorio</label>
                        <input type="text" class="form-control" id="edit_laboratorio" name="laboratorio" value="{{ $vacuna->laboratorio }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_lote" class="form-label">Lote</label>
                        <input type="text" class="form-control" id="edit_lote" name="lote" value="{{ $vacuna->lote }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_vencimiento" class="form-label">Vencimiento</label>
                        <input type="text" class="form-control fecha-input" id="edit_vencimiento" name="vencimiento" 
                            value="{{ $vacuna->vencimiento ? \Carbon\Carbon::parse($vacuna->vencimiento)->format('d-m-Y') : '' }}"
                            inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                            maxlength="10">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_via_administracion" class="form-label">Vía de Administración</label>
                        <input type="text" class="form-control" id="edit_via_administracion" name="via_administracion" value="{{ $vacuna->via_administracion }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="edit_dosis" class="form-label">Dosis</label>
                    <input type="text" class="form-control" id="edit_dosis" name="dosis" value="{{ $vacuna->dosis }}">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Próxima Vacuna</h6>
            </div>
            <div class="card-body">
                <label for="edit_proxima_dosis" class="form-label">Fecha Próxima Dosis</label>
                <input type="text" class="form-control fecha-input" id="edit_proxima_dosis" name="proxima_dosis" 
                    value="{{ $vacuna->proxima_dosis ? \Carbon\Carbon::parse($vacuna->proxima_dosis)->format('d-m-Y') : '' }}"
                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                    maxlength="10">
            </div>
        </div>

        <div class="mb-3">
            <label for="edit_observaciones" class="form-label fw-bold">Observaciones</label>
            <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3">{{ $vacuna->observaciones }}</textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-success" id="btnActualizarVacuna">
        <i class="fas fa-save me-2"></i>Actualizar Vacuna
    </button>
</div>
