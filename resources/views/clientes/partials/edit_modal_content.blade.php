{{-- Este archivo reemplaza el HTML de 'modules/editar_cliente.php' [cite: 691-694] --}}
<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Cliente</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    {{-- El ID se usa en JS para construir la URL de UPDATE --}}
    <form id="formEditarCliente" data-id="{{ $cliente->id_cliente }}">
        @csrf {{-- Token de seguridad --}}
        @method('PUT') {{-- Informa a Laravel que esto es una actualización --}}
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="edit_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_nombre" name="nombre" value="{{ $cliente->nombre }}" required autocomplete="off">
            </div>
            <div class="col-md-6">
                <label for="edit_apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_apellido" name="apellido" value="{{ $cliente->apellido }}" required autocomplete="off">
            </div>
            <div class="col-md-6">
                <label for="edit_celular" class="form-label">Celular <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="edit_celular" name="celular" value="{{ $cliente->celular }}" required pattern="[0-9]{9}" maxlength="9" minlength="9">
                <small class="text-muted">9 dígitos numéricos</small>
            </div>
            <div class="col-md-6">
                <label for="edit_dni" class="form-label">DNI</label>
                <input type="text" class="form-control" id="edit_dni" name="dni" value="{{ $cliente->dni }}" pattern="[0-9]{8}" maxlength="8" minlength="8" inputmode="numeric" autocomplete="off">
                <small class="text-muted">8 dígitos numéricos</small>
            </div>
            <div class="col-12">
                <label for="edit_direccion" class="form-label">Dirección</label>
                <textarea class="form-control" id="edit_direccion" name="direccion" rows="2">{{ $cliente->direccion }}</textarea>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarCliente">Actualizar Cliente</button>
</div>
