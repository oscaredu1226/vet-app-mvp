/**
 * public/js/productos.js
 *
 * Lógica de la página de Productos (resources/views/productos/index.blade.php)
 * Reemplaza a 'productos.js' [cite: 32-41]
 */

$(document).ready(function() {
    
    // --- GUARDAR NUEVO PRODUCTO ---
    $('#btnGuardarProducto').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: BASE_URL + '/productos', // Ruta POST a ProductoController@store
            method: 'POST',
            data: $('#formNuevoProducto').serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoProductoModal'));
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
                $btn.prop('disabled', false).html('Guardar Producto');
            }
        });
    });

    // --- ABRIR MODAL DE EDICIÓN ---
    $(document).on('click', '.btn-edit', function() {
        const productoId = $(this).data('id');
        const modalElement = document.getElementById('editarProductoModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        $.ajax({
            url: BASE_URL + '/productos/' + productoId + '/edit', // Ruta GET a ProductoController@edit
            method: 'GET',
            success: function(responseHtml) {
                modalContent.html(responseHtml);
            },
            error: function() {
                modalContent.html('<div class="modal-body text-center p-5"><p class="text-danger">Error al cargar los datos.</p></div>');
            }
        });
    });

    // --- ACTUALIZAR PRODUCTO ---
    $(document).on('click', '#btnActualizarProducto', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        const form = $('#formEditarProducto');
        const productoId = form.data('id');

        $.ajax({
            url: BASE_URL + '/productos/' + productoId, // Ruta PUT a ProductoController@update
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editarProductoModal'));
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
                $btn.prop('disabled', false).html('Actualizar Producto');
            }
        });
    });

    // --- ELIMINAR PRODUCTO ---
    $(document).on('click', '.btn-delete', function() {
        const productoId = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar producto?',
            text: `Estás a punto de eliminar "${nombre}". ¡No podrás revertir esto!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/productos/' + productoId, // Ruta DELETE
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