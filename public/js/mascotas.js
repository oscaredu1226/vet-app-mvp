$(document).ready(function() {

    $('#btnNuevaMascota').on('click', function() {
        // Limpiar el formulario antes de mostrar
        $('#formNuevaMascota')[0].reset();
        $('#id_cliente').val('');
        $('#resultados_propietario').empty().hide();
        
        const modalElement = document.getElementById('mascotaModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    });

    // Búsqueda de propietarios (reemplaza buscar_propietario.php [cite: 269-281])
    $(document).on('input', '#buscar_propietario', function() {
        const query = $(this).val();
        const resultsContainer = $('#resultados_propietario');

        if (query.length < 2) {
            resultsContainer.empty().hide();
            return;
        }

        $.ajax({
            url: BASE_URL + '/buscar-propietarios', // Ruta del ClienteController
            method: 'GET',
            data: { q: query, modo: 'modal' }, // 'modo' es un param inventado, tu HTML lo genera
            success: function(responseHtml) {
                // El controlador devuelve HTML listo
                resultsContainer.html(responseHtml).show();
            },
            error: function() {
                 resultsContainer.html('<div class="alert alert-danger">Error al buscar</div>').show();
            }
        });
    });

    // Búsqueda de propietarios en modal de edición
    $(document).on('input', '#edit_buscar_propietario', function() {
        const query = $(this).val();
        const resultsContainer = $('#edit_resultados_propietario');

        if (query.length < 2) {
            resultsContainer.empty().hide();
            return;
        }

        $.ajax({
            url: BASE_URL + '/buscar-propietarios',
            method: 'GET',
            data: { q: query, modo: 'modal' },
            success: function(responseHtml) {
                resultsContainer.html(responseHtml).show();
            },
            error: function() {
                 resultsContainer.html('<div class="alert alert-danger">Error al buscar</div>').show();
            }
        });
    });

    // Seleccionar propietario de los resultados
    $(document).on('click', '.seleccionar-propietario', function(e) {
        e.preventDefault();
        const $this = $(this);
        
        const idCliente = $this.data('id');
        const nombre = $this.data('nombre');
        const apellido = $this.data('apellido');
        const dni = $this.data('dni');
        
        const $nuevoModal = $('#mascotaModal');
        const $editModal = $('#mascotaEditModal');
        
        if ($nuevoModal.hasClass('show')) {
            $('#id_cliente').val(idCliente);
            $('#buscar_propietario').val(`${nombre} ${apellido}${dni ? ' - DNI: ' + dni : ''}`);
            $('#resultados_propietario').empty().hide();
        } else if ($editModal.hasClass('show')) {
            $('#edit_id_cliente').val(idCliente);
            $('#edit_buscar_propietario').val(`${nombre} ${apellido}${dni ? ' - DNI: ' + dni : ''}`);
            $('#edit_resultados_propietario').empty().hide();
        }
    });

    // Guardar NUEVA mascota
    $('#btnGuardarMascota').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: BASE_URL + '/mascotas', // Ruta POST a MascotaController@store
            method: 'POST',
            data: $('#formNuevaMascota').serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('mascotaModal'));
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
                $btn.prop('disabled', false).html('Guardar Mascota');
            }
        });
    });

    // --- LÓGICA DEL MODAL EDITAR MASCOTA ---

    $(document).on('click', '.btn-edit', function() {
        const mascotaId = $(this).data('id');
        const modalElement = document.getElementById('mascotaEditModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        $.ajax({
            url: BASE_URL + '/mascotas/' + mascotaId + '/edit', // Ruta GET a MascotaController@edit
            method: 'GET',
            success: function(responseHtml) {
                modalContent.html(responseHtml);
                
                const especieEl = document.getElementById('edit_especie');
                const razaEl = document.getElementById('edit_raza');
                
                if (especieEl && razaEl) {
                    const selectedRaza = razaEl.getAttribute('data-selected') || '';
                    poblarSelectRazas(razaEl, null, selectedRaza);
                    
                    especieEl.addEventListener('change', function() {
                        const razaSelect = document.getElementById('edit_raza');
                        if (razaSelect) {
                            poblarSelectRazas(razaSelect, null);
                        }
                    });
                }
            },
            error: function() {
                modalContent.html('<div class="modal-body text-center p-5"><p class="text-danger">Error al cargar.</p></div>');
            }
        });
    });

    // Actualizar mascota
    $(document).on('click', '#btnActualizarMascota', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        const form = $('#formEditarMascota');
        const mascotaId = form.data('id');

        $.ajax({
            url: BASE_URL + '/mascotas/' + mascotaId, // Ruta PUT a MascotaController@update
            method: 'PUT',
            data: form.serialize(), 
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('mascotaEditModal'));
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
                $btn.prop('disabled', false).html('Actualizar Mascota');
            }
        });
    });

    // --- ELIMINAR MASCOTA ---
    $(document).on('click', '.btn-delete', function() {
        const mascotaId = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/mascotas/' + mascotaId, // Ruta DELETE
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

    // --- EDITAR MASCOTA (clase alternativa para por-cliente.blade.php) ---
    $(document).on('click', '.btn-edit-mascota', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const mascotaId = $(this).data('id');
        const modalElement = document.getElementById('mascotaEditModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        $.ajax({
            url: BASE_URL + '/mascotas/' + mascotaId + '/edit',
            method: 'GET',
            success: function(html) {
                modalContent.html(html);
                
                setTimeout(function() {
                    const razaEl = modalElement.querySelector('#edit_raza');
                    if (razaEl && typeof window.poblarSelectRazas === 'function') {
                        const selectedRaza = razaEl.getAttribute('data-selected') || '';
                        window.poblarSelectRazas(razaEl, null, selectedRaza);
                    }
                }, 100);
            },
            error: function() {
                modalContent.html('<div class="alert alert-danger m-3">Error al cargar los datos</div>');
            }
        });
    });

    // --- ELIMINAR MASCOTA (clase alternativa para por-cliente.blade.php) ---
    $(document).on('click', '.btn-delete-mascota', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const mascotaId = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará la mascota "${nombre}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/mascotas/' + mascotaId,
                    method: 'DELETE',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    });

});

// Función global para seleccionar propietario
// Reemplaza la de tu JS [cite: 25-28]
function seleccionarPropietario(id, nombre) {
    // Detecta en qué modal se está (nuevo o edición)
    const modalActivo = $('.modal.show').attr('id');
    
    if (modalActivo === 'mascotaEditModal') {
        $('#edit_id_cliente').val(id);
        $('#edit_buscar_propietario').val(nombre);
        $('#edit_resultados_propietario').empty().hide();
    } else {
        $('#id_cliente').val(id);
        $('#buscar_propietario').val(nombre);
        $('#resultados_propietario').empty().hide();
    }
}