/**
 * public/js/calendario.js
 *
 * Lógica de la página de Calendario (resources/views/eventos/index.blade.php)
 * Reemplaza 'calendario.js', 'acciones_evento.js', 'nuevo_evento.js'
 */

$(document).ready(function() {

    // Función para convertir DD-MM-YYYY a YYYY-MM-DD
    function convertirFechaParaServidor(fecha) {
        if (!fecha) return '';
        const partes = fecha.split('-');
        if (partes.length === 3 && partes[0].length === 2) {
            // DD-MM-YYYY -> YYYY-MM-DD
            return partes[2] + '-' + partes[1] + '-' + partes[0];
        }
        return fecha; // Ya está en formato correcto
    }

    // --- CARGAR EVENTOS POR FECHA ---
    $('#fechaCalendario').on('change', function() {
        const fecha = $(this).val();
        const fechaServidor = convertirFechaParaServidor(fecha);
        
        // Actualiza el hash de la URL sin recargar la página
        history.pushState(null, '', '#/eventos?fecha=' + fechaServidor);
        
        // Carga solo la lista de eventos con AJAX
        cargarListaEventos(fechaServidor);
    });

    function cargarListaEventos(fecha) {
        const url = BASE_URL + '/eventos?fecha=' + fecha;
        
        $('#listaEventos').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(responseHtml) {
                $(document).trigger('citas:actualizadas');
                $('#listaEventos').html(responseHtml);
                const count = $('#listaEventos .evento-card').length;
                $('#contadorEventosDia').text(count);
                
                // Convertir fecha de DD-MM-YYYY o YYYY-MM-DD a formato Date
                let fechaParseada;
                if (fecha.includes('-')) {
                    const partes = fecha.split('-');
                    if (partes[0].length === 4) {
                        // Formato YYYY-MM-DD
                        fechaParseada = new Date(fecha + 'T00:00:00');
                    } else {
                        // Formato DD-MM-YYYY
                        fechaParseada = new Date(partes[2] + '-' + partes[1] + '-' + partes[0] + 'T00:00:00');
                    }
                } else {
                    fechaParseada = new Date(fecha + 'T00:00:00');
                }
                
                $('#fechaSeleccionada').text(fechaParseada.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' }));
            },
            error: function() {
                 $('#listaEventos').html('<div class="alert alert-danger">Error al cargar eventos.</div>');
            }
        });
    }

    // --- LÓGICA DE MODALES (NUEVO Y EDITAR) ---

    // Búsqueda de mascotas en MODAL
    let timeoutModalMascota;
    $(document).on('input', '#buscar_mascota_modal, #edit_buscar_mascota', function() {
        const $input = $(this);
        const query = $input.val().trim();
        const $resultsContainer = $input.nextAll('.dropdown-menu').first();

        if (query.length < 2) {
            $resultsContainer.hide();
            return;
        }
        
        $resultsContainer.html('<div class="dropdown-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();

        clearTimeout(timeoutModalMascota);
        timeoutModalMascota = setTimeout(() => {
            $.ajax({
                url: BASE_URL + '/buscar-mascotas',
                method: 'GET',
                data: { query: query },
                success: function(mascotas) {
                    let html = '';
                    if (mascotas.length > 0) {
                        mascotas.forEach(m => {
                            html += `<a href="#" class="dropdown-item seleccionar-mascota-modal" data-id="${m.id}" data-nombre="${m.display}">${m.display}</a>`;
                        });
                    } else {
                        html = '<div class="dropdown-item text-muted">No se encontraron mascotas</div>';
                    }
                    $resultsContainer.html(html).show();
                }
            });
        }, 300);
    });

    // Seleccionar mascota en MODAL
    $(document).on('click', '.seleccionar-mascota-modal', function(e) {
        e.preventDefault();
        const $link = $(this);
        const $form = $link.closest('form');
        
        $form.find('input[name="id_mascota"]').val($link.data('id'));
        $form.find('input[placeholder*="Buscar mascota..."]').val($link.data('nombre'));
        $link.closest('.dropdown-menu').hide();
    });

    // Resetear el formulario cuando se abre el modal
    $('#nuevoEventoModal').on('show.bs.modal', function() {
        const $form = $('#formNuevoEvento');
        const $btn = $form.find('button[type="submit"]');
        $form[0].reset();
        $btn.prop('disabled', false).html('Crear Evento');
        const fechaInput = document.getElementById('fecha_evento');
        if (fechaInput._flatpickr) fechaInput._flatpickr.setDate($('#fechaCalendario').val(), false);
        else fechaInput.value = $('#fechaCalendario').val();
        $('#id_mascota_modal').val('');
        $('#buscar_mascota_modal').val('');
    });

    // --- GUARDAR NUEVO EVENTO ---
    $('#formNuevoEvento').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');

        $.ajax({
            url: BASE_URL + '/eventos', // Ruta POST a CalendarioController@store
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $btn.prop('disabled', false).html('Crear Evento');
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoEventoModal'));
                if (modal) modal.hide();
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });
                cargarListaEventos($('#fechaCalendario').val());
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error.', 'error');
                $btn.prop('disabled', false).html('Crear Evento');
            }
        });
    });
    
    // --- ABRIR MODAL DE EDICIÓN ---
    $(document).on('click', '.btn-editar-evento', function() {
        const eventoId = $(this).data('id');
        const modal = $('#editarEventoModal');
        const form = $('#formEditarEvento');

        $.ajax({
            url: BASE_URL + '/eventos/' + eventoId, // Ruta GET a CalendarioController@show
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const ev = response.evento;
                    form.find('#edit_id_evento').val(ev.id_evento);
                    form.find('#edit_buscar_mascota').val(ev.mascota.nombre + ' - ' + ev.mascota.cliente.nombre);
                    form.find('#edit_id_mascota').val(ev.id_mascota);
                    const fechaInput = document.getElementById('edit_fecha_evento');
                    if (fechaInput._flatpickr) fechaInput._flatpickr.setDate(ev.fecha, false);
                    else fechaInput.value = ev.fecha;
                    CitasHorario.setTime(document.getElementById('edit_fecha_evento_hora'), ev.hora);
                    form.find('#edit_titulo_evento').val(ev.titulo);
                    form.find('#edit_descripcion_evento').val(ev.descripcion);
                    
                    // Usar setTimeout para evitar el problema de aria-hidden
                    setTimeout(() => {
                        const modalInstance = new bootstrap.Modal(document.getElementById('editarEventoModal'));
                        modalInstance.show();
                    }, 100);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });

    // --- ACTUALIZAR EVENTO ---
    $('#formEditarEvento').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const eventoId = $form.find('#edit_id_evento').val();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

        $.ajax({
            url: BASE_URL + '/eventos/' + eventoId, // Ruta PUT a CalendarioController@update
            method: 'PUT',
            data: $form.serialize(),
            success: function(response) {
                $btn.prop('disabled', false).html('Actualizar Evento');
                
                // Cerrar modal usando Bootstrap 5 API
                const modalEl = document.getElementById('editarEventoModal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }
                
                // Limpiar backdrops y estilos
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.paddingRight = '';
                }, 300);
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                });
                cargarListaEventos(convertirFechaParaServidor($('#fechaCalendario').val()));
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error.', 'error');
                $btn.prop('disabled', false).html('Actualizar Evento');
            }
        });
    });

    // --- COMPLETAR EVENTO ---
    $(document).on('click', '.btn-completar', function() {
        const eventoId = $(this).data('id');
        
        $.ajax({
            url: BASE_URL + '/eventos/' + eventoId, // Ruta PUT
            method: 'PUT',
            data: {
                _token: CSRF_TOKEN,
                accion: 'completar'
            },
            success: function(response) {
                // Cerrar cualquier modal abierto usando Bootstrap 5 API
                document.querySelectorAll('.modal.show').forEach(modalEl => {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                });
                // Remover backdrops que puedan quedar
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    // Recargar solo la lista de eventos sin redirigir
                    cargarListaEventos(convertirFechaParaServidor($('#fechaCalendario').val()));
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error.', 'error');
            }
        });
    });

    // --- ELIMINAR EVENTO ---
    $(document).on('click', '.btn-eliminar-evento', function() {
        const eventoId = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar evento?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/eventos/' + eventoId, // Ruta DELETE
                    method: 'DELETE',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        cargarListaEventos($('#fechaCalendario').val());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error.', 'error');
                    }
                });
            }
        });
    });

});
