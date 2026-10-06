$(document).ready(function() {
    let timeoutVacuna;
    let itemsSeleccionados = [];

    // Búsqueda de items (productos/servicios)
    $('#buscar_vacuna').on('input', function() {
        const query = $(this).val().trim();
        const resultsDiv = $('#resultados_vacuna');

        if (query.length < 2) {
            resultsDiv.hide();
            return;
        }

        resultsDiv.html('<div class="dropdown-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();

        clearTimeout(timeoutVacuna);
        timeoutVacuna = setTimeout(() => {
            $.ajax({
                url: BASE_URL + '/buscar-items', // Ruta de VentaController
                method: 'GET',
                data: { query: query },
                success: function(items) {
                    let html = '';
                    if (items.length > 0) {
                        items.forEach(function(item) {
                            html += `<a href="#" class="dropdown-item seleccionar-vacuna" data-item='${JSON.stringify(item)}'>
                                         <strong>${item.nombre}</strong> (S/ ${parseFloat(item.precio).toFixed(2)})
                                         ${item.tipo === 'producto' ? `<span class="badge bg-primary ms-2">Producto</span>` : `<span class="badge bg-success ms-2">Servicio</span>`}
                                     </a>`;
                        });
                    }
                    
                    if (html) {
                        resultsDiv.html(html).show();
                    } else {
                        resultsDiv.hide();
                    }
                },
                error: function() {
                    resultsDiv.html('<div class="dropdown-item text-danger">Error al buscar</div>').show();
                }
            });
        }, 300);
    });

    // Al presionar Enter, agregar como texto libre
    $('#buscar_vacuna').on('keydown', function(e) {
        if (e.which === 13 || e.keyCode === 13) { // Enter key
            e.preventDefault();
            const query = $(this).val().trim();
            
            if (query) {
                // Agregar como texto libre
                itemsSeleccionados.push({nombre: query, tipo: "texto_libre"});
                actualizarTablaVacunas();
                
                // Limpiar búsqueda
                $(this).val('');
                $('#resultados_vacuna').hide();
                $('#btn_agregar_vacuna').hide();
            }
        }
    });

    // Mostrar/ocultar botón en móviles según si hay texto
    $('#buscar_vacuna').on('input', function() {
        const hayTexto = $(this).val().trim().length > 0;
        if (hayTexto) {
            $('#btn_agregar_vacuna').show();
        } else {
            $('#btn_agregar_vacuna').hide();
        }
    });

    // Botón para agregar en móviles
    $('#btn_agregar_vacuna').on('click', function(e) {
        e.preventDefault();
        const query = $('#buscar_vacuna').val().trim();
        if (query) {
            itemsSeleccionados.push({nombre: query, tipo: "texto_libre"});
            actualizarTablaVacunas();
            $('#buscar_vacuna').val('');
            $('#resultados_vacuna').hide();
            $(this).hide();
        }
    });

    // Al seleccionar un item
    $(document).on('click', '.seleccionar-vacuna', function(e) {
        e.preventDefault();
        const item = $(this).data('item');
        
        // Añadir a la lista interna y a la tabla
        itemsSeleccionados.push(item);
        actualizarTablaVacunas();
        
        // Limpiar búsqueda
        $('#buscar_vacuna').val('');
        $('#resultados_vacuna').hide();
    });

    // Al eliminar un item
    $(document).on('click', '.btn-eliminar-vacuna', function() {
        const index = $(this).data('index');
        itemsSeleccionados.splice(index, 1);
        actualizarTablaVacunas();
    });

    // Función para redibujar la tabla y actualizar campos ocultos
    function actualizarTablaVacunas() {
        const tbody = $('#tablaVacunas');
        tbody.empty();

        if (itemsSeleccionados.length === 0) {
            $('#vacuna_aplicada').val('');
            return;
        }
        
        let nombresVacunas = [];

        itemsSeleccionados.forEach((item, index) => {
            nombresVacunas.push(item.nombre);
            
            // Fila para la tabla
            const row = `
                <tr id="vacuna-item-${index}">
                    <td>${item.nombre}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-eliminar-vacuna" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            tbody.append(row);
        });
        
        // Actualiza el campo oculto principal
        $('#vacuna_aplicada').val(nombresVacunas.join('; '));
        
        // Actualiza los otros campos ocultos (Aunque el controlador ahora maneja esto, es bueno tenerlo)
        actualizarCamposOcultos();
    }
    
    // Actualizar campos ocultos cuando se escribe en la tabla
    $(document).on('input', '#tablaVacunas input', function() {
        actualizarCamposOcultos();
    });
    
    function actualizarCamposOcultos() {
        // Esta función es opcional si el controlador maneja campos individuales,
        // pero la mantenemos por si la lógica de "vacuna_aplicada" (texto) la necesita.
        // Por simplicidad, el controlador que hicimos solo usa 'vacuna_aplicada' (el nombre).
        // Si quieres guardar lote, vencimiento, etc., el controlador necesita ser ajustado.
        
        // Ejemplo de cómo actualizarías campos individuales si el form los tuviera:
        // const primerLote = $('#tablaVacunas tr:first').find('input[data-field="lote"]').val();
        // $('#lote').val(primerLote); 
    }

    // --- LÓGICA DEL FORMULARIO ---
    
    // Envío del formulario con AJAX
    $('#formVacuna').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const originalText = $btn.data('original-text') || $btn.html();
        $btn.data('original-text', originalText); // Guardar texto original
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        // Asegurarse de que el campo 'vacuna_aplicada' esté actualizado
        actualizarTablaVacunas();
        
        if (itemsSeleccionados.length === 0) {
             Swal.fire('Error', 'Debe agregar al menos una vacuna.', 'error');
             $btn.prop('disabled', false).html(originalText);
             return;
        }
        
        // Recoger datos de la tabla (lote, vencimiento, etc.)
        const formData = new FormData(this);
        
        // Añadir datos de la tabla al FormData (si el controlador los espera)
        // Ejemplo:
        // itemsSeleccionados.forEach((item, index) => {
        //     formData.append(`items[${index}][nombre]`, item.nombre);
        //     formData.append(`items[${index}][lote]`, $(`#vacuna-item-${index} input[data-field="lote"]`).val());
        //     ...
        // });
        // (Por ahora, nuestro controlador solo usa 'vacuna_aplicada', así que no es necesario)

        $.ajax({
            url: $form.attr('action'), // Ruta de VacunaController@store
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    // Redirigir al dashboard de la historia
                    window.location.href = $form.data('redirect-url');
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al guardar la vacuna.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Cerrar resultados de búsqueda al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscar_vacuna, #resultados_vacuna').length) {
            $('#resultados_vacuna').hide();
        }
    });
});
