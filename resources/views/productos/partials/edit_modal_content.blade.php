<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Producto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="formEditarProducto" data-id="{{ $producto->id_producto }}">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-12 mb-3">
                <label for="edit_nombre_producto" class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_nombre_producto" name="nombre" value="{{ $producto->nombre }}" required autocomplete="off">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_precio" class="form-label fw-semibold">Precio (S/.) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_precio" name="precio" step="0.01" min="0" value="{{ $producto->precio }}" required autocomplete="off">
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_stock" class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_stock" name="stock" min="0" value="{{ $producto->stock }}" required autocomplete="off">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_tipo" class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_tipo" name="tipo" required>
                    <option value="clinica" @if($producto->tipo == 'clinica') selected @endif>Clínica</option>
                    <option value="farmacia" @if($producto->tipo == 'farmacia') selected @endif>Farmacia</option>
                    <option value="petshop" @if($producto->tipo == 'petshop') selected @endif>PetShop</option>
                    <option value="spa" @if($producto->tipo == 'spa') selected @endif>Spa</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_stock_minimo" class="form-label fw-semibold">Stock mínimo <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_stock_minimo" name="stock_minimo" min="1" value="{{ $producto->stock_minimo }}" required autocomplete="off">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_fecha_vencimiento" class="form-label fw-semibold">Fecha de Vencimiento (FV)</label>
                <input type="date" class="form-control" id="edit_fecha_vencimiento" name="fecha_vencimiento" 
                    value="{{ $producto->fecha_vencimiento ? $producto->fecha_vencimiento->format('Y-m-d') : '' }}"
                    autocomplete="off" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                <small class="text-muted">Opcional. Si se vence pronto, aparecerá en el calendario.</small>
            </div>
        </div>
        <div class="mb-3">
            <label for="edit_descripcion" class="form-label fw-semibold">Descripción</label>
            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2">{{ $producto->descripcion }}</textarea>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarProducto">Actualizar Producto</button>
</div>