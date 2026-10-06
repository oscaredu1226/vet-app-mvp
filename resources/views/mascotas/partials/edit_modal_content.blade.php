{{-- Este archivo reemplaza a 'editar_mascota.php' [cite: 798-815] --}}
<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title" id="mascotaModalLabel"><i class="fas fa-edit me-2"></i> Editar Mascota</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="formEditarMascota" data-id="{{ $mascota->id_mascota }}">
        @csrf {{-- Token de seguridad de Laravel --}}
        @method('PUT') {{-- Método para 'update' --}}

        <h6 class="border-bottom pb-2"><i class="fas fa-user me-2"></i>Datos del Propietario</h6>
        <div class="mb-3">
            <label for="edit_buscar_propietario" class="form-label">Propietario <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" class="form-control" id="edit_buscar_propietario" autocomplete="off" 
                       value="{{ $mascota->cliente->nombre ?? '' }} {{ $mascota->cliente->apellido ?? '' }}" 
                       placeholder="Nombre, apellido o DNI...">
                <input type="hidden" id="edit_id_cliente" name="id_cliente" value="{{ $mascota->id_cliente }}">
            </div>
            <div id="edit_resultados_propietario" class="list-group mt-1" style="max-height: 150px; overflow-y: auto;"></div>
        </div>

        <h6 class="border-bottom pb-2 mt-4"><i class="fas fa-paw me-2"></i>Datos de la Mascota</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_nombre_mascota" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_nombre_mascota" name="nombre" value="{{ $mascota->nombre }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                <input type="text" class="form-control fecha-input" id="edit_fecha_nacimiento" name="fecha_nacimiento" 
                    value="{{ \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') }}"
                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                    maxlength="10">
                <small class="text-muted">Máximo 30 años atrás. Formato: DD-MM-AAAA</small>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="edit_especie" class="form-label">Especie <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_especie" name="especie">
                    <option value="Canino" @if($mascota->especie == 'Canino') selected @endif>Canino</option>
                    <option value="Felino" @if($mascota->especie == 'Felino') selected @endif>Felino</option>
                    <option value="Otro" @if($mascota->especie == 'Otro') selected @endif>Otro</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="edit_raza" class="form-label">Raza <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_raza" name="raza" data-selected="{{ htmlspecialchars($mascota->raza, ENT_QUOTES, 'UTF-8') }}">
                    <option value="">Seleccionar raza...</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="edit_genero" class="form-label">Género <span class="text-danger">*</span></label>
                <select class="form-select" id="edit_genero" name="genero">
                    <option value="Macho" @if($mascota->genero == 'Macho') selected @endif>Macho</option>
                    <option value="Hembra" @if($mascota->genero == 'Hembra') selected @endif>Hembra</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="edit_esterilizado" class="form-label">Esterilizado</label>
                <select class="form-select" id="edit_esterilizado" name="esterilizado">
                    <option value="No" @if($mascota->esterilizado == 'No') selected @endif>No</option>
                    <option value="Si" @if($mascota->esterilizado == 'Si') selected @endif>Sí</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="edit_estado" class="form-label">Estado</label>
                <select class="form-select" id="edit_estado" name="estado">
                    <option value="Activo" @if($mascota->estado == 'Activo') selected @endif>Activo</option>
                    <option value="Fallecido" @if($mascota->estado == 'Fallecido') selected @endif>Fallecido</option>
                </select>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarMascota">Actualizar Mascota</button>
</div>