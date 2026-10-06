$(document).ready(function() {
    let productosAntipulgas = [];
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

        if (!productoVal) return;

        const productos = productoVal.split(';').map(p => p.trim()).filter(p => p);
        const dosis = dosisVal.split(';').map(d => d.trim());
        const vias = viaVal.split(';').map(v => v.trim());

        // Limpiar la tabla
        $('#tablaAntipulgas').empty();
        productosAntipulgas = [];

        // Reconstruir cada fila
        for (let i = 0; i < productos.length; i++) {
            const antipulga = {
                nombre: productos[i],
                dosis: dosis[i] || '',
                via: vias[i] || ''
            };
            
            productosAntipulgas.push(antipulga);
            
            const fila = `
                <tr>
                    <td>${antipulga.nombre}</td>
                    <td><input type="text" class="form-control form-control-sm dosis" value="${antipulga.dosis}" placeholder="Ej: 1 pipeta"></td>
                    <td>
                        <select class="form-select form-select-sm via">
                            <option value="">Seleccione</option>
                            <option value="Oral" ${antipulga.via === 'Oral' ? 'selected' : ''}>Oral</option>
                            <option value="Tópico" ${antipulga.via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                            <option value="Inyectable" ${antipulga.via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAntipulgas(${i})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tablaAntipulgas').append(fila);
        }

        actualizarCamposAntipulgas();
    }

    // Cambio de tipo de búsqueda
    $('input[name="buscar_tipo"]').on('change', function() {
        buscarTipo = $(this).val();
        $('#buscar_antipulgas').val('');
        $('#resultados_antipulgas').hide().empty();
    });

    // Búsqueda de antipulgas
    $('#buscar_antipulgas').on('input', function() {
        let query = $(this).val();
        
        if (query.length < 1) {
            $('#resultados_antipulgas').hide().empty();
            return;
        }

        if (buscarTipo === 'catalogo') {
            $.ajax({
                url: '/buscar-items',
                method: 'GET',
                data: { query: query },
                success: function(productos) {
                    mostrarResultadosAntipulgas(productos);
                },
                error: function() {
                    $('#resultados_antipulgas').html('<div class="p-2 text-danger">Error al buscar productos</div>').show();
                }
            });
        }
    });

    // Enter para agregar como texto libre
    $('#buscar_antipulgas').on('keydown', function(e) {
        if (e.which === 13 || e.keyCode === 13) {
            e.preventDefault();
            const texto = $(this).val().trim();
            if (texto) {
                agregarAntipulgasTexto(texto);
                $(this).val('');
                $('#resultados_antipulgas').hide().empty();
                $('#btn_agregar_antipulgas').hide();
            }
        }
    });

    // Mostrar/ocultar botón en móviles según si hay texto
    $('#buscar_antipulgas').on('input', function() {
        const hayTexto = $(this).val().trim().length > 0;
        if (hayTexto) {
            $('#btn_agregar_antipulgas').show();
        } else {
            $('#btn_agregar_antipulgas').hide();
        }
    });

    // Botón para agregar en móviles
    $('#btn_agregar_antipulgas').on('click', function(e) {
        e.preventDefault();
        const texto = $('#buscar_antipulgas').val().trim();
        if (texto) {
            agregarAntipulgasTexto(texto);
            $('#buscar_antipulgas').val('');
            $('#resultados_antipulgas').hide().empty();
            $(this).hide();
        }
    });

    function mostrarResultadosAntipulgas(productos) {
        if (productos.length === 0) {
            $('#resultados_antipulgas').html('<div class="p-2 text-muted">No se encontraron productos</div>').show();
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

        $('#resultados_antipulgas').html(html).show();
    }

    // Click en resultado
    $(document).on('click', '.resultado-item', function() {
        const nombre = $(this).data('nombre');
        
        agregarAntipulgasTexto(nombre);
        $('#buscar_antipulgas').val('');
        $('#resultados_antipulgas').hide().empty();
    });

    // Cerrar resultados al hacer click fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#buscar_antipulgas, #resultados_antipulgas').length) {
            $('#resultados_antipulgas').hide();
        }
    });

    window.agregarAntipulgas = function(nombre, dosis = '', via = '') {
        const antipulga = {
            nombre: nombre,
            dosis: dosis,
            via: via
        };

        productosAntipulgas.push(antipulga);

        const fila = `
            <tr>
                <td>${nombre}</td>
                <td><input type="text" class="form-control form-control-sm dosis" value="${dosis}" placeholder="Ej: 1 pipeta"></td>
                <td>
                    <select class="form-select form-select-sm via">
                        <option value="">Seleccione</option>
                        <option value="Oral" ${via === 'Oral' ? 'selected' : ''}>Oral</option>
                        <option value="Tópico" ${via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                        <option value="Inyectable" ${via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAntipulgas(${productosAntipulgas.length - 1})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tablaAntipulgas').append(fila);
        actualizarCamposAntipulgas();
    };

    function agregarAntipulgasTexto(nombre) {
        agregarAntipulgas(nombre, '', '');
    }

    window.eliminarAntipulgas = function(index) {
        productosAntipulgas.splice(index, 1);
        $('#tablaAntipulgas').empty();
        
        productosAntipulgas.forEach((anti, idx) => {
            const fila = `
                <tr>
                    <td>${anti.nombre}</td>
                    <td><input type="text" class="form-control form-control-sm dosis" value="${anti.dosis}" placeholder="Ej: 1 pipeta"></td>
                    <td>
                        <select class="form-select form-select-sm via">
                            <option value="">Seleccione</option>
                            <option value="Oral" ${anti.via === 'Oral' ? 'selected' : ''}>Oral</option>
                            <option value="Tópico" ${anti.via === 'Tópico' ? 'selected' : ''}>Tópico</option>
                            <option value="Inyectable" ${anti.via === 'Inyectable' ? 'selected' : ''}>Inyectable</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarAntipulgas(${idx})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#tablaAntipulgas').append(fila);
        });

        actualizarCamposAntipulgas();
    };

    // Actualizar campos ocultos cuando cambien los inputs de la tabla
    $(document).on('change keyup', '#tablaAntipulgas input, #tablaAntipulgas select', function() {
        actualizarCamposAntipulgas();
    });

    function actualizarCamposAntipulgas() {
        const productos = [];
        const dosis = [];
        const vias = [];
        
        $('#tablaAntipulgas tr').each(function() {
            const nombre = $(this).find('td:first').text().trim();
            const dos = $(this).find('.dosis').val()?.trim() || '';
            const via = $(this).find('.via').val()?.trim() || '';
            
            if (nombre) {
                productos.push(nombre);
                dosis.push(dos);
                vias.push(via);
            }
        });
        
        $('#producto').val(productos.join(';'));
        $('#dosis').val(dosis.join(';'));
        $('#via_administracion').val(vias.join(';'));
    }

    // Envío del formulario
    $('#formAntipulgas').on('submit', function(e) {
        e.preventDefault();

        if (productosAntipulgas.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe agregar al menos un producto antipulgas',
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
                    text: response.message || 'Antipulgas actualizado correctamente',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = response.redirect || '/historia/' + response.mascota_id;
                });
            },
            error: function(xhr) {
                let mensaje = 'Error al guardar el antipulgas';
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
