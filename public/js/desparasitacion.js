$(document).ready(function() {
    let productosDesparasitantes = [];
    let buscarTipo = 'catalogo';

    // Si estamos editando, cargar los datos existentes
    if ($('#producto').val()) {
        cargarDatosExistentes();
    }

    // Función para cargar datos existentes en modo edición
    function cargarDatosExistentes() {
        const productoVal = $('#producto').val() || '';
        const dosisVal = $('#dosis').val() || '';
        const viaVal = $('#via_administracion').val() || '';
        const tipoParasitoVal = $('#tipo_parasito').val() || '';

        if (!productoVal) return;

        const productos = productoVal.split(';').map(p => p.trim()).filter(p => p);
        const dosis = dosisVal.split(';').map(d => d.trim());
        const vias = viaVal.split(';').map(v => v.trim());
        const tiposParasito = tipoParasitoVal.split(';').map(t => t.trim());

        // Limpiar la tabla
        $('#tablaDesparasitantes').empty();
        productosDesparasitantes = [];

        // Reconstruir cada fila
        for (let i = 0; i < productos.length; i++) {
            const desparasitante = {
                nombre: productos[i],
                dosis: dosis[i] || '',
                via: vias[i] || '',
                tipo_parasito: tiposParasito[i] || ''
            };
            
            productosDesparasitantes.push(desparasitante);
            
            const fila = `
                <tr>
                    <td>${desparasitante.nombre}</td>
                    <td><input type="text" class="form-control form-control-sm dosis" value="${desparasitante.dosis}" placeholder="Ej: 1 comprimido"></td>
                    <td>
                        <select class="form-select form-select-sm via">
                            <option value="">Seleccione</option>
                            <option value="Oral" ${desparasitante.via === 'Oral' ? 'selected' : ''}>Oral</option>
                            <option value="Tópico" ${desparasitante.via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                            <option value="Inyectable" ${desparasitante.via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm tipo_parasito" value="${desparasitante.tipo_parasito}" placeholder="Ej: Interno"></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarDesparasitante(${i})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tablaDesparasitantes').append(fila);
        }

        actualizarCamposDesparasitante();
    }

    // Cambio de tipo de búsqueda
    $('input[name="buscar_tipo"]').on('change', function() {
        buscarTipo = $(this).val();
        $('#buscar_desparasitante').val('');
        $('#resultados_desparasitante').hide().empty();
    });

    // Búsqueda de desparasitantes
    $('#buscar_desparasitante').on('input', function() {
        let query = $(this).val();
        
        if (query.length < 1) {
            $('#resultados_desparasitante').hide().empty();
            return;
        }

        if (buscarTipo === 'catalogo') {
            $.ajax({
                url: '/buscar-items',
                method: 'GET',
                data: { query: query },
                success: function(productos) {
                    mostrarResultadosDesparasitante(productos);
                },
                error: function() {
                    $('#resultados_desparasitante').html('<div class="p-2 text-danger">Error al buscar productos</div>').show();
                }
            });
        }
    });

    // Enter para agregar como texto libre
    $('#buscar_desparasitante').on('keydown', function(e) {
        if (e.which === 13 || e.keyCode === 13) {
            e.preventDefault();
            const texto = $(this).val().trim();
            if (texto) {
                agregarDesparasitanteTexto(texto);
                $(this).val('');
                $('#resultados_desparasitante').hide().empty();
                $('#btn_agregar_desparasitante').hide();
            }
        }
    });

    // Mostrar/ocultar botón en móviles según si hay texto
    $('#buscar_desparasitante').on('input', function() {
        const hayTexto = $(this).val().trim().length > 0;
        if (hayTexto) {
            $('#btn_agregar_desparasitante').show();
        } else {
            $('#btn_agregar_desparasitante').hide();
        }
    });

    // Botón para agregar en móviles
    $('#btn_agregar_desparasitante').on('click', function(e) {
        e.preventDefault();
        const texto = $('#buscar_desparasitante').val().trim();
        if (texto) {
            agregarDesparasitanteTexto(texto);
            $('#buscar_desparasitante').val('');
            $('#resultados_desparasitante').hide().empty();
            $(this).hide();
        }
    });

    function mostrarResultadosDesparasitante(productos) {
        if (productos.length === 0) {
            $('#resultados_desparasitante').html('<div class="p-2 text-muted">No se encontraron productos</div>').show();
            return;
        }

        let html = '';
        productos.forEach(producto => {
            html += `
                <div class="p-2 border-bottom resultado-item" style="cursor:pointer;" 
                     data-nombre="${producto.nombre}" 
                     data-id="${producto.id_producto}">
                    <strong>${producto.nombre}</strong>
                </div>
            `;
        });

        $('#resultados_desparasitante').html(html).show();
    }

    // Click en resultado
    $(document).on('click', '.resultado-item', function() {
        const nombre = $(this).data('nombre');
        
        agregarDesparasitanteTexto(nombre);
        $('#buscar_desparasitante').val('');
        $('#resultados_desparasitante').hide().empty();
    });

    // Cerrar resultados al hacer click fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscar_desparasitante, #resultados_desparasitante').length) {
            $('#resultados_desparasitante').hide();
        }
    });

    window.agregarDesparasitante = function(nombre, dosis = '', via = '', tipo_parasito = '') {
        const desparasitante = {
            nombre: nombre,
            dosis: dosis,
            via: via,
            tipo_parasito: tipo_parasito
        };

        productosDesparasitantes.push(desparasitante);

        const fila = `
            <tr>
                <td>${nombre}</td>
                <td><input type="text" class="form-control form-control-sm dosis" value="${dosis}" placeholder="Ej: 1 comprimido"></td>
                <td>
                    <select class="form-select form-select-sm via">
                        <option value="">Seleccione</option>
                        <option value="Oral" ${via === 'Oral' ? 'selected' : ''}>Oral</option>
                        <option value="Tópico" ${via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                        <option value="Inyectable" ${via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm tipo_parasito" value="${tipo_parasito}" placeholder="Ej: Interno"></td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarDesparasitante(${productosDesparasitantes.length - 1})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tablaDesparasitantes').append(fila);
        actualizarCamposDesparasitante();
    };

    function agregarDesparasitanteTexto(nombre) {
        agregarDesparasitante(nombre, '', '', '');
    }

    window.eliminarDesparasitante = function(index) {
        productosDesparasitantes.splice(index, 1);
        $('#tablaDesparasitantes').empty();
        
        productosDesparasitantes.forEach((desp, idx) => {
            const fila = `
                <tr>
                    <td>${desp.nombre}</td>
                    <td><input type="text" class="form-control form-control-sm dosis" value="${desp.dosis}" placeholder="Ej: 1 comprimido"></td>
                    <td>
                        <select class="form-select form-select-sm via">
                            <option value="">Seleccione</option>
                            <option value="Oral" ${desp.via === 'Oral' ? 'selected' : ''}>Oral</option>
                            <option value="Tópico" ${desp.via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                            <option value="Inyectable" ${desp.via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm tipo_parasito" value="${desp.tipo_parasito}" placeholder="Ej: Interno"></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarDesparasitante(${idx})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tablaDesparasitantes').append(fila);
        });

        actualizarCamposDesparasitante();
    };

    // Actualizar campos ocultos cuando cambien los inputs de la tabla
    $(document).on('change keyup', '#tablaDesparasitantes input, #tablaDesparasitantes select', function() {
        actualizarCamposDesparasitante();
    });

    function actualizarCamposDesparasitante() {
        const productos = [];
        const dosis = [];
        const vias = [];
        const tipos = [];
        
        $('#tablaDesparasitantes tr').each(function() {
            const nombre = $(this).find('td:first').text().trim();
            const dos = $(this).find('.dosis').val()?.trim() || '';
            const via = $(this).find('.via').val()?.trim() || '';
            const tipo = $(this).find('.tipo_parasito').val()?.trim() || '';
            
            if (nombre) {
                productos.push(nombre);
                dosis.push(dos);
                vias.push(via);
                tipos.push(tipo);
            }
        });
        
        $('#producto').val(productos.join(';'));
        $('#dosis').val(dosis.join(';'));
        $('#via_administracion').val(vias.join(';'));
        $('#tipo_parasito').val(tipos.join(';'));
    }

    // Envío del formulario
    $('#formDesparasitacion').on('submit', function(e) {
        e.preventDefault();

        if (productosDesparasitantes.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe agregar al menos un desparasitante',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const formData = new FormData(this);
        const url = $(this).attr('action');
        const method = $(this).find('input[name="_method"]').length ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': method
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message || 'Desparasitación actualizada correctamente',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect || '/historia/' + response.mascota_id;
                });
            },
            error: function(xhr) {
                let mensaje = 'Error al guardar la desparasitación';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    mensaje = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: mensaje,
                    confirmButtonColor: '#d33'
                });
            }
        });
    });
});
