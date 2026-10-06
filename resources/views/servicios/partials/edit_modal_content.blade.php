<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Servicio</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="formEditarServicio" data-id="{{ $servicio->id_servicio }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="edit_nombre_servicio" class="form-label fw-semibold">Nombre del servicio <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_nombre_servicio" name="nombre" value="{{ $servicio->nombre }}" required autocomplete="off">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_precio_servicio" class="form-label fw-semibold">Precio <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">S/.</span>
                    <input type="number" class="form-control" id="edit_precio_servicio" name="precio" step="0.01" min="0" value="{{ $servicio->precio }}" required autocomplete="off">
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_tipo_servicio" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_tipo_servicio" name="tipo" required>
                    <option value="clinica" @if($servicio->tipo == 'clinica') selected @endif>Clínica</option>
                    <option value="farmacia" @if($servicio->tipo == 'farmacia') selected @endif>Farmacia</option>
                    <option value="petshop" @if($servicio->tipo == 'petshop') selected @endif>PetShop</option>
                    <option value="spa" @if($servicio->tipo == 'spa') selected @endif>Spa</option>
                </select>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarServicio">Actualizar Servicio</button>
</div>