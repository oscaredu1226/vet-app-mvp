/**
 * public/js/examenes.js
 *
 * Lógica de la página de Exámenes (resources/views/examenes/index.blade.php)
 * Reemplaza 'examenes.js' y la lógica de 'editar_examen.php'
 */

$(document).ready(function() {

    // --- BÚSQUEDA DE MASCOTA EN MODAL ---
    let timeoutMascota;
    $(document).on('input', '#mascota_search', function() {
        const query = $(this).val().trim();
        const resultsDiv = $('#mascota_results');

        if (query.length < 2) {
            resultsDiv.hide();
            return;
        }

        resultsDiv.html('<div class="dropdown-item p-3 text-center"><i class="fas fa-spinner fa-spin"></i></div>').show();

        clearTimeout(timeoutMascota);
        timeoutMascota = setTimeout(() => {
            $.ajax({
                url: BASE_URL + '/buscar-mascotas', // Ruta de MascotaController
                method: 'GET',
                data: { query: query },
                success: function(mascotas) {
                    let html = '';
                    if (mascotas.length > 0) {
                        mascotas.forEach(function(m) {
                            html += `<a href="#" class="dropdown-item seleccionar-mascota-examen" 
                                         data-id="${m.id}" data-nombre="${m.display}">
                                         ${m.display}
                                     </a>`;
                        });
                    } else {
                        html = '<div class="dropdown-item text-muted">No se encontraron mascotas</div>';
                    }
                    resultsDiv.html(html).show();
                }
            });
        }, 300);
    });

    // Seleccionar mascota en modal
    $(document).on('click', '.seleccionar-mascota-examen', function(e) {
        e.preventDefault();
        $('#mascota_search').val($(this).data('nombre'));
        $('#mascota_select').val($(this).data('id'));
        $('#mascota_results').hide();
    });

    // --- GUARDAR NUEVO EXAMEN ---
    $('#btnGuardarExamen').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: $('#formNuevoExamen').attr('action'), // Ruta de ExamenLaboratorioController@store
            method: 'POST',
            data: $('#formNuevoExamen').serialize(),
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoExamenModal'));
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
                $btn.prop('disabled', false).html('Guardar Examen');
            }
        });
    });

    // --- ABRIR MODAL DE EDICIÓN ---
    $(document).on('click', '.btn-edit-examen', function() {
        const examenId = $(this).data('id');
        const modalElement = document.getElementById('editarExamenModal');
        const modal = new bootstrap.Modal(modalElement);
        const modalContent = $(modalElement).find('.modal-content');

        modalContent.html('<div class="modal-body text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        modal.show();

        $.ajax({
            url: BASE_URL + '/examenes/' + examenId + '/edit', // Ruta GET
            method: 'GET',
            success: function(responseHtml) {
                modalContent.html(responseHtml);
                // Inicializar búsqueda de laboratorio y tipo de análisis en modal de edición
                initEditExamenSearchHandlers();
            },
            error: function() {
                modalContent.html('<div class="modal-body text-center p-5"><p class="text-danger">Error al cargar datos.</p></div>');
            }
        });
    });

    // Función para inicializar handlers de búsqueda en modal de edición
    function initEditExamenSearchHandlers() {
        // Búsqueda de laboratorio en edición
        $(document).off('input', '#edit_laboratorio_search').on('input', '#edit_laboratorio_search', function () {
            const termino = $(this).val().toLowerCase();
            const resultados = $('#edit_laboratorio_results');

            if (termino.length === 0) {
                resultados.empty().hide();
                $('#edit_laboratorio').val('');
                return;
            }

            let laboratorios = window.laboratorios || [];
            filterAndDisplayLaboratorios(termino, laboratorios, resultados);
        });

        // Búsqueda de tipo de análisis en edición
        $(document).off('input', '#edit_tipo_analisis_search').on('input', '#edit_tipo_analisis_search', function () {
            const termino = $(this).val().toLowerCase();
            const resultados = $('#edit_tipo_analisis_results');

            if (termino.length === 0) {
                resultados.empty().hide();
                $('#edit_tipo_analisis').val('');
                return;
            }

            let tiposAnalisis = window.tiposAnalisis || [];
            filterAndDisplayTipos(termino, tiposAnalisis, resultados);
        });

        // Seleccionar laboratorio en edición
        $(document).off('click', '.edit-select-laboratorio').on('click', '.edit-select-laboratorio', function (e) {
            e.preventDefault();
            const nombre = $(this).data('nombre');
            $('#edit_laboratorio_search').val(nombre);
            $('#edit_laboratorio').val(nombre);
            $('#edit_laboratorio_results').empty().hide();
        });

        // Seleccionar tipo de análisis en edición
        $(document).off('click', '.edit-select-tipo-analisis').on('click', '.edit-select-tipo-analisis', function (e) {
            e.preventDefault();
            const nombre = $(this).data('nombre');
            $('#edit_tipo_analisis_search').val(nombre);
            $('#edit_tipo_analisis').val(nombre);
            $('#edit_tipo_analisis_results').empty().hide();
        });

        // Cerrar resultados al hacer clic fuera
        $(document).off('click.editModal').on('click.editModal', function (e) {
            if (!$(e.target).closest('#edit_laboratorio_search, #edit_laboratorio_results').length) {
                $('#edit_laboratorio_results').empty().hide();
            }
            if (!$(e.target).closest('#edit_tipo_analisis_search, #edit_tipo_analisis_results').length) {
                $('#edit_tipo_analisis_results').empty().hide();
            }
        });
    }

    // Funciones auxiliares para filtrar y mostrar resultados
    function filterAndDisplayLaboratorios(termino, laboratorios, resultados) {
        const coincidencias = laboratorios.filter(lab =>
            lab.toLowerCase().includes(termino)
        );

        if (coincidencias.length > 0) {
            resultados.empty().show();
            coincidencias.forEach(lab => {
                resultados.append(`
                    <a href="#" class="list-group-item list-group-item-action edit-select-laboratorio" data-nombre="${lab}">
                        ${lab}
                    </a>
                `);
            });
        } else {
            resultados.empty().hide();
        }
        $('#edit_laboratorio').val(termino);
    }

    function filterAndDisplayTipos(termino, tipos, resultados) {
        const coincidencias = tipos.filter(tipo =>
            tipo.toLowerCase().includes(termino)
        );

        if (coincidencias.length > 0) {
            resultados.empty().show();
            coincidencias.forEach(tipo => {
                resultados.append(`
                    <a href="#" class="list-group-item list-group-item-action edit-select-tipo-analisis" data-nombre="${tipo}">
                        ${tipo}
                    </a>
                `);
            });
        } else {
            resultados.empty().hide();
        }
        $('#edit_tipo_analisis').val(termino);
    }

    // --- ACTUALIZAR EXAMEN ---
    $(document).on('click', '#btnActualizarExamen', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        const form = $('#formEditarExamen');
        const examenId = form.data('id');

        $.ajax({
            url: BASE_URL + '/examenes/' + examenId, // Ruta PUT
            method: 'POST', // Usamos POST y _method="PUT"
            data: form.serialize(),
            success: function(response) {
                const modalEl = document.getElementById('editarExamenModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modalEl.addEventListener('hidden.bs.modal', function () {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }, { once: true });
                    modal.hide();
                } else {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error desconocido.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Actualizar Examen');
            }
        });
    });

    // --- ELIMINAR EXAMEN ---
    $(document).on('click', '.btn-delete-examen', function() {
        const examenId = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar examen?',
            text: "Esta acción no se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/examenes/' + examenId, // Ruta DELETE
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