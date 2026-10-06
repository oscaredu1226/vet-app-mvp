<div class="modal-header bg-primary text-white">
    <h5 class="modal-title">
        <i class="fas fa-edit me-2"></i>Editar Turno #{{ $grooming->numero_turno }}
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form id="formEditGrooming" action="{{ route('grooming.update', $grooming->id_grooming) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="modal-body">
        {{-- Cliente --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-user me-2"></i>Cliente <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="edit_id_cliente" name="id_cliente" required>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id_cliente }}" {{ $grooming->id_cliente == $cliente->id_cliente ? 'selected' : '' }}>
                        {{ $cliente->nombre }} {{ $cliente->apellido }} - DNI: {{ $cliente->dni ?? 'N/A' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Mascota --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-paw me-2"></i>Mascota <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="edit_id_mascota" name="id_mascota" required>
                @foreach($mascotas as $mascota)
                    <option value="{{ $mascota->id_mascota }}" {{ $grooming->id_mascota == $mascota->id_mascota ? 'selected' : '' }}>
                        {{ $mascota->nombre }} - {{ $mascota->raza ?? 'Sin raza' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row mb-3">
            {{-- Fecha --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-calendar me-2"></i>Fecha <span class="text-danger">*</span>
                </label>
                <input type="date" 
                       class="form-control" 
                       name="fecha" 
                       value="{{ $grooming->fecha }}" 
                       required>
            </div>

            {{-- Hora de Entrada --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-clock me-2"></i>Hora de Entrada <span class="text-danger">*</span>
                </label>
                <input type="time" 
                       class="form-control" 
                       name="hora_entrada" 
                       value="{{ \Carbon\Carbon::parse($grooming->hora_entrada)->format('H:i') }}" 
                       required>
            </div>
        </div>

        <div class="row mb-3">
            {{-- Hora de Salida --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-clock me-2"></i>Hora de Salida
                </label>
                <input type="time" 
                       class="form-control" 
                       name="hora_salida" 
                       value="{{ $grooming->hora_salida ? \Carbon\Carbon::parse($grooming->hora_salida)->format('H:i') : '' }}">
            </div>

            {{-- Estado --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="fas fa-flag me-2"></i>Estado <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="estado" required>
                    <option value="PENDIENTE" {{ $grooming->estado == 'PENDIENTE' ? 'selected' : '' }}>PENDIENTE</option>
                    <option value="EN_PROCESO" {{ $grooming->estado == 'EN_PROCESO' ? 'selected' : '' }}>EN PROCESO</option>
                    <option value="COMPLETADO" {{ $grooming->estado == 'COMPLETADO' ? 'selected' : '' }}>COMPLETADO</option>
                    <option value="CANCELADO" {{ $grooming->estado == 'CANCELADO' ? 'selected' : '' }}>CANCELADO</option>
                </select>
            </div>
        </div>

        {{-- Servicio --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-cut me-2"></i>Servicio <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="edit_id_servicio" name="id_servicio" required>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id_servicio }}" {{ $grooming->id_servicio == $servicio->id_servicio ? 'selected' : '' }}>
                        {{ $servicio->nombre }} - S/ {{ number_format($servicio->precio, 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Observaciones --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                <i class="fas fa-sticky-note me-2"></i>Observaciones
            </label>
            <textarea class="form-control" 
                      name="observaciones" 
                      rows="3">{{ $grooming->observaciones }}</textarea>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary" id="btnActualizar">
            <i class="fas fa-save me-1"></i> Actualizar
        </button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inicializar Select2 en el modal de edición
    $('#edit_id_cliente, #edit_id_servicio').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#editGroomingModal')
    });

    $('#edit_id_mascota').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#editGroomingModal')
    });

    // Cuando cambia el cliente en edición, cargar sus mascotas
    $('#edit_id_cliente').on('change', function() {
        const clienteId = $(this).val();
        const $mascotaSelect = $('#edit_id_mascota');
        
        $mascotaSelect.html('<option value="">Cargando...</option>').prop('disabled', true);

        $.get(`{{ route('grooming.mascotas-by-cliente') }}`, { id_cliente: clienteId })
            .done(function(mascotas) {
                let options = '<option value="">Seleccione una mascota...</option>';
                mascotas.forEach(mascota => {
                    options += `<option value="${mascota.id_mascota}">${mascota.nombre} - ${mascota.raza || 'Sin raza'}</option>`;
                });
                
                $mascotaSelect.html(options).prop('disabled', false);
                $('#edit_id_mascota').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#editGroomingModal')
                });
            });
    });

    // Envío del formulario de edición
    $('#formEditGrooming').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btnActualizar');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Actualizando...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#editGroomingModal').modal('hide');
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(btnText);
                const message = xhr.responseJSON?.message || 'Error al actualizar el turno';
                Swal.fire('Error', message, 'error');
            }
        });
    });
});
</script>
