/**
 * public/js/clientes.js
 *
 * Lógica de la página de Clientes (resources/views/clientes/index.blade.php)
 * Reemplaza a 'clientes.js' [cite: 7-106]
 */

// Asegúrate de que este script se carga *después* de app.js
// y de que las variables BASE_URL y CSRF_TOKEN están disponibles.

$(document).ready(function() {

    // --- GUARDAR NUEVO CLIENTE ---
    $('#btnGuardarCliente').on('click', function() {
        const $btn = $(this);
        const $form = $('#formNuevoCliente');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: $form.attr('action'), // La URL viene del <form>
            method: 'POST',
            data: $form.serialize(), // Incluye el _token CSRF
            success: function(response) {
                // Bootstrap 5: Usar la API nativa en lugar de jQuery
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoClienteModal'));
                if (modal) modal.hide();
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Recarga la página para ver al nuevo cliente
                });
            },
            error: function(xhr) {
                // Muestra el primer error de validación
                let errorMsg = 'Error desconocido. Revise los campos.';
                if (xhr.responseJSON?.errors) {
                    // Mostrar el primer error de validación
                    const firstError = Object.values(xhr.responseJSON.errors)[0][0];
                    errorMsg = firstError;
                } else if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Guardar Cliente');
            }
        });
    });

    // --- ABRIR MODAL DE EDICIÓN ---
    $(document).on('click', '.btn-edit-cliente', function() {
        const clienteId = $(this).data('id');
        const modalElement = document.getElementById('editarClienteModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        // Muestra el modal con un spinner
        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        // Carga el contenido del formulario de edición
        $.ajax({
            url: BASE_URL + '/clientes/' + clienteId + '/edit', // Ruta GET a ClienteController@edit
            method: 'GET',
            success: function(responseHtml) {
                modalContent.html(responseHtml);
            },
            error: function() {
                modalContent.html('<div class="modal-body text-center p-5"><p class="text-danger">Error al cargar los datos.</p></div>');
            }
        });
    });

    // --- ACTUALIZAR CLIENTE ---
    $(document).on('click', '#btnActualizarCliente', function() {
        const $btn = $(this);
        const $form = $('#formEditarCliente');
        const clienteId = $form.data('id');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: BASE_URL + '/clientes/' + clienteId, // Ruta PUT a ClienteController@update
            method: 'PUT',
            data: $form.serialize(), // Incluye _token y _method
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editarClienteModal'));
                if (modal) modal.hide();
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error desconocido. Revise los campos.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Actualizar Cliente');
            }
        });
    });

    // --- ELIMINAR CLIENTE ---
    $(document).on('click', '.btn-delete-cliente', function() {
        const clienteId = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar Cliente?',
            text: `Estás a punto de eliminar a "${nombre}". ¡No podrás revertir esto!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/clientes/' + clienteId, // Ruta DELETE a ClienteController@destroy
                    method: 'DELETE',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'No se pudo eliminar el cliente.';
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    });
    
    // --- PERMITIR EDICIÓN DEL DNI ---
    // Hacer que el campo DNI sea editable al hacer click
    $(document).on('click', '#dni', function() {
        $(this).prop('readonly', false);
    });

    // Validar que solo se ingresen números en el DNI
    $(document).on('input', '#dni', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });

    // --- PERMITIR EDICIÓN DE NOMBRE Y APELLIDO ---
    // Hacer que los campos sean editables al hacer clic
    $(document).on('click', '#nombre, #apellido', function() {
        const $field = $(this);
        $field.prop('readonly', false).removeClass('bg-light');
        if ($field.val() === 'Se completará automáticamente' || $field.val() === '') {
            $field.val('');
        }
    });

// MVP_POSTERIOR: Consulta externa RENIEC
// MVP_POSTERIOR |     // --- BUSCAR DNI EN RENIEC ---
// MVP_POSTERIOR |     $(document).on('click', '#btnBuscarReniec', function() {
// MVP_POSTERIOR |         const $btn = $(this);
// MVP_POSTERIOR |         const $dniInput = $('#dni');
// MVP_POSTERIOR |         const dni = $dniInput.val().trim();
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         // Validar DNI
// MVP_POSTERIOR |         if (!dni) {
// MVP_POSTERIOR |             Swal.fire({
// MVP_POSTERIOR |                 title: 'DNI Requerido',
// MVP_POSTERIOR |                 text: 'Por favor, ingrese un número de DNI',
// MVP_POSTERIOR |                 icon: 'warning',
// MVP_POSTERIOR |                 confirmButtonText: 'Entendido'
// MVP_POSTERIOR |             });
// MVP_POSTERIOR |             $dniInput.focus();
// MVP_POSTERIOR |             return;
// MVP_POSTERIOR |         }
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         if (dni.length !== 8) {
// MVP_POSTERIOR |             Swal.fire({
// MVP_POSTERIOR |                 title: 'DNI Inválido',
// MVP_POSTERIOR |                 text: 'El DNI debe tener exactamente 8 dígitos',
// MVP_POSTERIOR |                 icon: 'warning',
// MVP_POSTERIOR |                 confirmButtonText: 'Entendido'
// MVP_POSTERIOR |             });
// MVP_POSTERIOR |             $dniInput.focus();
// MVP_POSTERIOR |             return;
// MVP_POSTERIOR |         }
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         // Deshabilitar botón y mostrar spinner
// MVP_POSTERIOR |         $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
// MVP_POSTERIOR |         $dniInput.prop('readonly', true);
// MVP_POSTERIOR | 
// MVP_POSTERIOR |         // Realizar consulta AJAX
// MVP_POSTERIOR |         $.ajax({
// MVP_POSTERIOR |             url: BASE_URL + '/consultar-reniec',
// MVP_POSTERIOR |             method: 'POST',
// MVP_POSTERIOR |             data: { 
// MVP_POSTERIOR |                 _token: CSRF_TOKEN,
// MVP_POSTERIOR |                 dni: dni 
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             success: function(response) {
// MVP_POSTERIOR |                 if(response.success) {
// MVP_POSTERIOR |                     // Completar automáticamente los campos pero permitir edición
// MVP_POSTERIOR |                     $('#nombre').val(response.nombres || '').prop('readonly', false).removeClass('bg-light');
// MVP_POSTERIOR |                     $('#apellido').val((response.apellido_paterno + ' ' + response.apellido_materno).trim()).prop('readonly', false).removeClass('bg-light');
// MVP_POSTERIOR |                 } else {
// MVP_POSTERIOR |                     Swal.fire({
// MVP_POSTERIOR |                         title: 'No Encontrado',
// MVP_POSTERIOR |                         text: response.message || 'No se encontró información para este DNI',
// MVP_POSTERIOR |                         icon: 'error',
// MVP_POSTERIOR |                         confirmButtonText: 'Entendido'
// MVP_POSTERIOR |                     });
// MVP_POSTERIOR |                     // Permitir edición manual si no se encuentra
// MVP_POSTERIOR |                     $('#nombre').prop('readonly', false);
// MVP_POSTERIOR |                     $('#apellido').prop('readonly', false);
// MVP_POSTERIOR |                 }
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             error: function(xhr) {
// MVP_POSTERIOR |                 const errorMsg = xhr.responseJSON?.message || 'No se pudo conectar con el servicio de RENIEC';
// MVP_POSTERIOR |                 Swal.fire({
// MVP_POSTERIOR |                     title: 'Error de Conexión',
// MVP_POSTERIOR |                     text: errorMsg,
// MVP_POSTERIOR |                     icon: 'error',
// MVP_POSTERIOR |                     confirmButtonText: 'Entendido'
// MVP_POSTERIOR |                 });
// MVP_POSTERIOR |                 // Permitir edición manual en caso de error
// MVP_POSTERIOR |                 $('#nombre').prop('readonly', false);
// MVP_POSTERIOR |                 $('#apellido').prop('readonly', false);
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             complete: function() {
// MVP_POSTERIOR |                 $btn.prop('disabled', false).html('<i class="fas fa-search"></i>');
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |         });
// MVP_POSTERIOR |     });
// MVP_POSTERIOR | 
    // --- LIMPIAR FORMULARIO AL CERRAR MODAL ---
    $('#nuevoClienteModal').on('hidden.bs.modal', function() {
        $('#formNuevoCliente')[0].reset();
        $('#dni').prop('readonly', true);
        $('#nombre, #apellido').prop('readonly', false);
    });

});