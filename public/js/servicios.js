/**
 * public/js/servicios.js
 *
 * Lógica de la página de Servicios (resources/views/servicios/index.blade.php)
 * Reemplaza a 'servicios.js' [cite: 42-51]
 */

$(document).ready(function() {
    
    // --- GUARDAR NUEVO SERVICIO ---
    $('#btnGuardarServicio').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: BASE_URL + '/servicios', // Ruta POST a ServicioController@store
            method: 'POST',
            data: $('#formNuevoServicio').serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoServicioModal'));
                if (modal) modal.hide();
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error desconocido.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Guardar Servicio');
            }
        });
    });

    // --- ABRIR MODAL DE EDICIÓN ---
    $(document).on('click', '.btn-edit-servicio', function() {
        const servicioId = $(this).data('id');
        const modalElement = document.getElementById('editarServicioModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        $.ajax({
            url: BASE_URL + '/servicios/' + servicioId + '/edit', // Ruta GET a ServicioController@edit
            method: 'GET',
            success: function(responseHtml) {
                modalContent.html(responseHtml);
            },
            error: function() {
                modalContent.html('<div class="modal-body text-center p-5"><p class="text-danger">Error al cargar los datos.</p></div>');
            }
        });
    });

    // --- ACTUALIZAR SERVICIO ---
    $(document).on('click', '#btnActualizarServicio', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        const form = $('#formEditarServicio');
        const servicioId = form.data('id');

        $.ajax({
            url: BASE_URL + '/servicios/' + servicioId, // Ruta PUT a ServicioController@update
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editarServicioModal'));
                if (modal) modal.hide();
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error desconocido.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Actualizar Servicio');
            }
        });
    });

    // --- ELIMINAR SERVICIO ---
    $(document).on('click', '.btn-delete-servicio', function() {
        const servicioId = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar servicio?',
            text: `Estás a punto de eliminar "${nombre}". ¡No podrás revertir esto!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/servicios/' + servicioId, // Ruta DELETE
                    method: 'DELETE',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message, 'error');
                    }
                });
            }
        });
    });
});