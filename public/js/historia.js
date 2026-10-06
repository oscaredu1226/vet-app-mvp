/**
 * public/js/historia.js
 *
 * Lógica para los formularios de la historia clínica
 * (Consultas, Vacunas, Desparasitación, Antipulgas)
 * Reemplaza 'historia_clinica.js', 'vacuna.js', 'desparasitacion.js', 'antipulgas.js'
 */

$(document).ready(function() {

    /**
     * Manejador genérico para todos los formularios de historia.
     * Detecta el formulario por su ID y envía a la ruta correcta.
     */
    function manejarSubmitFormHistoria(formId, boton) {
        const $form = $(formId);
        if (!$form.length) return;

        const $btn = $(boton);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: $form.attr('action'), // La URL se define en el 'action' del form
            method: 'POST',
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    // Redirige al dashboard de la historia de la mascota
                    window.location.hash = $form.data('redirect-url');
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error desconocido. Revise los campos.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html($btn.data('original-text'));
            }
        });
    }

    // --- FORMULARIO DE CONSULTAS ---
    // (Reemplaza 'historia_clinica.js' [cite: 7-16])
    $(document).on('submit', '#formConsulta', function(e) {
        e.preventDefault();
        manejarSubmitFormHistoria(
            '#formConsulta', 
            $(this).find('button[type="submit"]')
        );
    });

    // --- FORMULARIO DE VACUNAS ---
    // (Reemplaza el JS de 'vacuna.php')
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('submit', '#formVacuna', function(e) {
// MVP_POSTERIOR |         e.preventDefault();
// MVP_POSTERIOR |         manejarSubmitFormHistoria(
// MVP_POSTERIOR |             '#formVacuna', 
// MVP_POSTERIOR |             $(this).find('button[type="submit"]')
// MVP_POSTERIOR |         );
// MVP_POSTERIOR |     });

    // --- FORMULARIO DE DESPARASITACIÓN ---
    // (Reemplaza el JS de 'desparasitacion.php')
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('submit', '#formDesparasitacion', function(e) {
// MVP_POSTERIOR |         e.preventDefault();
// MVP_POSTERIOR |         manejarSubmitFormHistoria(
// MVP_POSTERIOR |             '#formDesparasitacion', 
// MVP_POSTERIOR |             $(this).find('button[type="submit"]')
// MVP_POSTERIOR |         );
// MVP_POSTERIOR |     });

    // --- FORMULARIO DE ANTIPULGAS ---
    // (Reemplaza el JS de 'antipulgas.php')
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('submit', '#formAntipulgas', function(e) {
// MVP_POSTERIOR |         e.preventDefault();
// MVP_POSTERIOR |         manejarSubmitFormHistoria(
// MVP_POSTERIOR |             '#formAntipulgas', 
// MVP_POSTERIOR |             $(this).find('button[type="submit"]')
// MVP_POSTERIOR |         );
// MVP_POSTERIOR |     });

    // ====================
    // FUNCIONES DE EDITAR
    // ====================

    // Modal universal para editar registros
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditarRegistro') || document.createElement('div'));

    // Función genérica para cargar modal de edición
    window.editarRegistro = function(tipo, id) {
        const rutas = {
            'consulta': `/consultas/${id}/edit`,
            'vacuna': `/vacunas/${id}/edit`,
            'desparasitacion': `/desparasitaciones/${id}/edit`,
            'antipulga': `/antipulgas/${id}/edit`
        };

        $.ajax({
            url: rutas[tipo],
            method: 'GET',
            success: function(html) {
                $('#modalEditarRegistroContent').html(html);
                const modal = new bootstrap.Modal(document.getElementById('modalEditarRegistro'));
                modal.show();
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar el formulario de edición', 'error');
            }
        });
    };

    // --- ACTUALIZAR CONSULTA ---
    $(document).on('click', '#btnActualizarConsulta', function() {
        const $form = $('#formEditarConsulta');
        const id = $form.data('id');

        $.ajax({
            url: `/consultas/${id}`,
            method: 'PUT',
            data: $form.serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRegistro'));
                if (modal) modal.hide();
                Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al actualizar';
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    // --- ACTUALIZAR VACUNA ---
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('click', '#btnActualizarVacuna', function() {
// MVP_POSTERIOR |         const $form = $('#formEditarVacuna');
// MVP_POSTERIOR |         const id = $form.data('id');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         $.ajax({
// MVP_POSTERIOR |             url: `/vacunas/${id}`,
// MVP_POSTERIOR |             method: 'PUT',
// MVP_POSTERIOR |             data: $form.serialize(),
// MVP_POSTERIOR |             success: function(response) {
// MVP_POSTERIOR |                 const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRegistro'));
// MVP_POSTERIOR |                 if (modal) modal.hide();
// MVP_POSTERIOR |                 Swal.fire({
// MVP_POSTERIOR |                     title: '¡Éxito!',
// MVP_POSTERIOR |                     text: response.message,
// MVP_POSTERIOR |                     icon: 'success',
// MVP_POSTERIOR |                     timer: 1000,
// MVP_POSTERIOR |                     showConfirmButton: false
// MVP_POSTERIOR |                 }).then(() => {
// MVP_POSTERIOR |                     location.reload();
// MVP_POSTERIOR |                 });
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             error: function(xhr) {
// MVP_POSTERIOR |                 const errorMsg = xhr.responseJSON?.message || 'Error al actualizar';
// MVP_POSTERIOR |                 Swal.fire('Error', errorMsg, 'error');
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |         });
// MVP_POSTERIOR |     });

    // --- ACTUALIZAR DESPARASITACIÓN ---
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('click', '#btnActualizarDesparasitacion', function() {
// MVP_POSTERIOR |         const $form = $('#formEditarDesparasitacion');
// MVP_POSTERIOR |         const id = $form.data('id');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         $.ajax({
// MVP_POSTERIOR |             url: `/desparasitaciones/${id}`,
// MVP_POSTERIOR |             method: 'PUT',
// MVP_POSTERIOR |             data: $form.serialize(),
// MVP_POSTERIOR |             success: function(response) {
// MVP_POSTERIOR |                 const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRegistro'));
// MVP_POSTERIOR |                 if (modal) modal.hide();
// MVP_POSTERIOR |                 Swal.fire({
// MVP_POSTERIOR |                     title: '¡Éxito!',
// MVP_POSTERIOR |                     text: response.message,
// MVP_POSTERIOR |                     icon: 'success',
// MVP_POSTERIOR |                     timer: 1000,
// MVP_POSTERIOR |                     showConfirmButton: false
// MVP_POSTERIOR |                 }).then(() => {
// MVP_POSTERIOR |                     location.reload();
// MVP_POSTERIOR |                 });
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             error: function(xhr) {
// MVP_POSTERIOR |                 const errorMsg = xhr.responseJSON?.message || 'Error al actualizar';
// MVP_POSTERIOR |                 Swal.fire('Error', errorMsg, 'error');
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |         });
// MVP_POSTERIOR |     });

    // --- ACTUALIZAR ANTIPULGA ---
    // MVP_POSTERIOR: Formulario especializado fuera del MVP
// MVP_POSTERIOR | $(document).on('click', '#btnActualizarAntipulga', function() {
// MVP_POSTERIOR |         const $form = $('#formEditarAntipulga');
// MVP_POSTERIOR |         const id = $form.data('id');
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         $.ajax({
// MVP_POSTERIOR |             url: `/antipulgas/${id}`,
// MVP_POSTERIOR |             method: 'PUT',
// MVP_POSTERIOR |             data: $form.serialize(),
// MVP_POSTERIOR |             success: function(response) {
// MVP_POSTERIOR |                 const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRegistro'));
// MVP_POSTERIOR |                 if (modal) modal.hide();
// MVP_POSTERIOR |                 Swal.fire({
// MVP_POSTERIOR |                     title: '¡Éxito!',
// MVP_POSTERIOR |                     text: response.message,
// MVP_POSTERIOR |                     icon: 'success',
// MVP_POSTERIOR |                     timer: 1000,
// MVP_POSTERIOR |                     showConfirmButton: false
// MVP_POSTERIOR |                 }).then(() => {
// MVP_POSTERIOR |                     location.reload();
// MVP_POSTERIOR |                 });
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             error: function(xhr) {
// MVP_POSTERIOR |                 const errorMsg = xhr.responseJSON?.message || 'Error al actualizar';
// MVP_POSTERIOR |                 Swal.fire('Error', errorMsg, 'error');
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |         });
// MVP_POSTERIOR |     });

    // ====================
    // FUNCIONES DE ELIMINAR
    // ====================

    // Función genérica para eliminar registros
    window.eliminarRegistro = function(tipo, id, nombre) {
        const rutas = {
            'consulta': `/consultas/${id}`,
            'vacuna': `/vacunas/${id}`,
            'desparasitacion': `/desparasitaciones/${id}`,
            'antipulga': `/antipulgas/${id}`
        };

        const titulos = {
            'consulta': 'Consulta',
            'vacuna': 'Vacuna',
            'desparasitacion': 'Desparasitación',
            'antipulga': 'Antipulgas'
        };

        Swal.fire({
            title: `¿Eliminar ${titulos[tipo]}?`,
            text: nombre ? `Se eliminará: ${nombre}` : `Esta acción no se puede deshacer`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: rutas[tipo],
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al eliminar';
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    };

});