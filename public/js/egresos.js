/**
 * public/js/egresos.js
 *
 * Lógica de la página de Egresos (resources/views/egresos/index.blade.php)
 * Reemplaza 'egresos.js' [cite: 868-877]
 */

$(document).ready(function() {

    // --- GUARDAR NUEVO EGRESO ---
    $('#formNuevoEgreso').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Registrando...');

        $.ajax({
            url: $form.attr('action'), // Ruta de EgresoController@store
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Recarga para ver el egreso en la lista
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar el egreso.', 'error');
                $btn.prop('disabled', false).html('Registrar Egreso');
            }
        });
    });

    // --- ELIMINAR EGRESO ---
    $('.btn-delete-egreso').on('click', function() {
        const egresoId = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar egreso?',
            text: "Esta acción no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/egresos/' + egresoId, // Ruta de EgresoController@destroy
                    method: 'DELETE',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo eliminar el egreso.', 'error');
                    }
                });
            }
        });
    });
});