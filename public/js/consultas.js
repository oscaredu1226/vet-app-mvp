$(document).ready(function() {
    // Cargar datos existentes si estamos en modo edición
    cargarDatosExistentes();
    
    // Validación y envío del formulario con AJAX
    $('#formConsulta').on('submit', function(e) {
        e.preventDefault();
        
        const diagnostico = $('#diagnostico').val().trim();
        const peso = $('input[name="peso"]').val();
        
        // Validaciones
        if (!diagnostico) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta información',
                text: 'El diagnóstico es obligatorio'
            });
            return false;
        }
        
        if (!peso || peso <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Peso requerido',
                text: 'Debe ingresar el peso actual de la mascota'
            });
            return false;
        }

        // Si todo está OK, enviar con AJAX
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
        
        // Actualizar campos hidden antes de enviar
        // MVP_POSTERIOR: Examenes y recetas fuera del MVP
// MVP_POSTERIOR | actualizarCampoHidden('tablaExamenes', 'examenes');
        actualizarCampoHidden('tablaTratamientos', 'plan_tratamiento');
        // MVP_POSTERIOR: Examenes y recetas fuera del MVP
// MVP_POSTERIOR | actualizarCampoHidden('tablaRecetas', 'receta');
        
        // Enviar formulario con AJAX
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Actualizar el historiaId para archivos
                    if (typeof historiaIdActual !== 'undefined' && response.consulta_id) {
                        historiaIdActual = response.consulta_id;
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Consulta registrada',
                        text: 'La consulta médica se ha registrado correctamente',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect || document.referrer;
                    });
                } else {
                    throw new Error(response.message || 'Error al guardar');
                }
            },
            error: function(xhr) {
                let errorMsg = 'No se pudo guardar la consulta. Por favor, inténtelo de nuevo.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
        
        return false;
    });
    
    // Manejo de búsquedas de productos (examenes, tratamientos, recetas)
    // MVP_POSTERIOR: Examenes y recetas fuera del MVP
// MVP_POSTERIOR | setupBusqueda('examen', 'tablaExamenes', 'examenes');
    setupBusqueda('tratamiento', 'tablaTratamientos', 'tratamiento');
    // MVP_POSTERIOR: Examenes y recetas fuera del MVP
// MVP_POSTERIOR | setupBusqueda('receta', 'tablaRecetas', 'receta');
    
    // Manejo del diagnóstico con búsqueda en catálogo
    let timeoutDiagnostico;
    
    $('#buscar_diagnostico').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const texto = $(this).val().trim();
            if (texto) {
                const currentVal = $('#diagnostico').val();
                if (currentVal) {
                    $('#diagnostico').val(currentVal + '\n' + texto);
                } else {
                    $('#diagnostico').val(texto);
                }
                $(this).val('');
                $('#resultados_diagnostico').hide();
            }
        } else if (e.key === 'Escape') {
            $('#resultados_diagnostico').hide();
            $(this).blur();
        }
    });
    
    $('#buscar_diagnostico').on('keyup', function(e) {
        if (e.key === 'Enter' || e.key === 'Escape') {
            return;
        }
        
        const query = $(this).val().trim();
        
        if (query.length < 1) {
            $('#resultados_diagnostico').hide();
            return;
        }
        
        clearTimeout(timeoutDiagnostico);
        timeoutDiagnostico = setTimeout(function() {
            buscarDiagnosticos(query);
        }, 300);
    });
    
    $(document).on('click', '.diagnostico-item', function() {
        const nombre = $(this).data('nombre');
        const currentVal = $('#diagnostico').val();
        if (currentVal) {
            $('#diagnostico').val(currentVal + '\n' + nombre);
        } else {
            $('#diagnostico').val(nombre);
        }
        $('#buscar_diagnostico').val('');
        $('#resultados_diagnostico').hide();
    });
    
    function setupBusqueda(tipo, tablaId, hiddenId) {
        let timeout;
        
        $(`#buscar_${tipo}`).on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = $(this).val().trim();
                if (query.length > 0) {
                    agregarItem(tipo, {
                        nombre: query,
                        tipo: 'texto_libre'
                    }, tablaId, hiddenId);
                    $(this).val('');
                    $(`#resultados_${tipo}`).hide();
                    $(`#btn_agregar_${tipo}`).hide();
                }
                return false;
            } else if (e.key === 'Escape') {
                $(`#resultados_${tipo}`).hide();
                $(this).blur();
            }
        });

        // Mostrar/ocultar botón en móviles según si hay texto
        $(`#buscar_${tipo}`).on('input', function() {
            const hayTexto = $(this).val().trim().length > 0;
            const btnId = `#btn_agregar_${tipo}`;
            if ($(btnId).length > 0) {
                if (hayTexto) {
                    $(btnId).show();
                } else {
                    $(btnId).hide();
                }
            }
        });

        // Botón para agregar en móviles
        $(document).on('click', `#btn_agregar_${tipo}`, function(e) {
            e.preventDefault();
            const query = $(`#buscar_${tipo}`).val().trim();
            if (query.length > 0) {
                agregarItem(tipo, {
                    nombre: query,
                    tipo: 'texto_libre'
                }, tablaId, hiddenId);
                $(`#buscar_${tipo}`).val('');
                $(`#resultados_${tipo}`).hide();
                $(this).hide();
            }
        });
        
        $(`#buscar_${tipo}`).on('keyup', function(e) {
            if (e.key === 'Enter' || e.key === 'Escape') {
                return;
            }
            
            const query = $(this).val().trim();
            
            if (query.length < 1) {
                $(`#resultados_${tipo}`).hide();
                return;
            }
            
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                buscarEnCatalogo(query, tipo);
            }, 300);
        });
        
        // Crear div para resultados si no existe
        if ($(`#resultados_${tipo}`).length === 0) {
            $(`#buscar_${tipo}`).parent().css('position', 'relative');
            $(`#buscar_${tipo}`).after(`<div id="resultados_${tipo}" class="border rounded mt-1" style="display:none; position:absolute; background:white; z-index:1000; width:100%; max-height:200px; overflow-y:auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>`);
        }
    }
    
    function buscarDiagnosticos(query) {
        $.ajax({
            url: '/buscar-diagnosticos',
            method: 'GET',
            data: { query: query },
            success: function(diagnosticos) {
                mostrarResultadosDiagnostico(diagnosticos, query);
            },
            error: function() {
                mostrarResultadosDiagnostico([], query);
            }
        });
    }
    
    function mostrarResultadosDiagnostico(diagnosticos, query) {
        let html = '';
        
        if (diagnosticos && diagnosticos.length > 0) {
            diagnosticos.forEach(function(diagnostico) {
                html += `<div class="diagnostico-item p-2 border-bottom" style="cursor:pointer;" data-nombre="${diagnostico.nombre}">
                    <i class="fas fa-stethoscope me-2 text-primary"></i><strong>${diagnostico.nombre}</strong>
                </div>`;
            });
        }
        
        if (html) {
            $('#resultados_diagnostico').html(html).show();
        } else {
            $('#resultados_diagnostico').hide();
        }
    }
    
    function buscarEnCatalogo(query, tipo) {
        let url = '';
        if (tipo === 'examen') {
            url = '/buscar-examenes-catalogo';
        } else if (tipo === 'tratamiento') {
            url = '/buscar-tratamientos';
        } else if (tipo === 'receta') {
            url = '/buscar-recetas';
        }
        
        $.ajax({
            url: url,
            method: 'GET',
            data: { query: query },
            success: function(resultados) {
                mostrarResultados(resultados, tipo, query);
            },
            error: function() {
                mostrarResultados([], tipo, query);
            }
        });
    }
    
    function mostrarResultados(productos, tipo, query) {
        let html = '';
        
        if (productos && productos.length > 0) {
            productos.forEach(function(producto) {
                html += `<div class="resultado-item p-2 border-bottom" style="cursor:pointer;" data-producto='${JSON.stringify(producto)}' data-tipo="${tipo}">
                    <i class="fas fa-check-circle me-2 text-success"></i><strong>${producto.nombre}</strong>
                </div>`;
            });
        }
        
        if (html) {
            $(`#resultados_${tipo}`).html(html).show();
        } else {
            $(`#resultados_${tipo}`).hide();
        }
    }
    
    $(document).on('click', '.resultado-item', function() {
        const producto = JSON.parse($(this).attr('data-producto'));
        const tipo = $(this).attr('data-tipo');
        const tablaId = tipo === 'examen' ? 'tablaExamenes' : (tipo === 'tratamiento' ? 'tablaTratamientos' : 'tablaRecetas');
        const hiddenId = tipo === 'examen' ? 'examenes' : (tipo === 'tratamiento' ? 'tratamiento' : 'receta');
        
        agregarItem(tipo, producto, tablaId, hiddenId);
        $(`#buscar_${tipo}`).val('');
        $(`#resultados_${tipo}`).hide();
        $(`#btn_agregar_${tipo}`).hide();
    });
    
    function agregarItem(tipo, producto, tablaId, hiddenId) {
        const id = `${tipo}_` + Date.now();
        const placeholderEspec = tipo === 'examen' ? 'Especificaciones del examen' : (tipo === 'tratamiento' ? 'Dosis y frecuencia' : 'Dosis y frecuencia');
        
        const html = `
        <div id="${id}" class="badge bg-light text-dark p-3 position-relative" style="white-space: normal; min-width: 150px; border: 1px solid #ddd; display: flex; flex-direction: column; gap: 8px;">
            <div style="font-weight: bold; word-break: break-word;">${producto.nombre}</div>
            <input type="text" class="form-control form-control-sm item-especificaciones" placeholder="${placeholderEspec}" value="">
            <input type="number" class="form-control form-control-sm item-cantidad" value="1" min="1" placeholder="Cantidad">
            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="eliminarItem('${id}', '${hiddenId}', '${tablaId}')">
                <i class="fas fa-trash"></i>
            </button>
        </div>`;
        
        $(`#${tablaId}`).append(html);
        actualizarCampoHidden(tablaId, hiddenId);
    }
    
    window.eliminarItem = function(id, hiddenId, tablaId) {
        $(`#${id}`).remove();
        actualizarCampoHidden(tablaId, hiddenId);
    };
    
    function actualizarCampoHidden(tablaId, hiddenId) {
        const items = [];
        $(`#${tablaId} > div`).each(function() {
            const nombre = $(this).find('div:first').text().trim();
            const especificaciones = $(this).find('.item-especificaciones').val() || '';
            const cantidad = $(this).find('.item-cantidad').val() || '1';
            
            if (nombre) {
                items.push(`${nombre} - ${especificaciones} (${cantidad})`);
            }
        });
        
        $(`input[name="${hiddenId}"]`).val(items.join('; '));
    }
    
    // Actualizar campos hidden cuando cambian los inputs
    $(document).on('change', '.item-especificaciones, .item-cantidad', function() {
        const tablaId = $(this).closest('table').find('tbody').attr('id');
        let hiddenId = '';
        if (tablaId === 'tablaExamenes') hiddenId = 'examenes';
        else if (tablaId === 'tablaTratamientos') hiddenId = 'plan_tratamiento';
        else if (tablaId === 'tablaRecetas') hiddenId = 'receta';
        
        if (hiddenId) {
            actualizarCampoHidden(tablaId, hiddenId);
        }
    });
    
    // Cerrar resultados al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('[id^="buscar_"], [id^="resultados_"]').length) {
            $('[id^="resultados_"]').hide();
        }
    });
    
    // Función para cargar datos existentes en modo edición
    function cargarDatosExistentes() {
        // Cargar exámenes
        const examenesData = $('#examenes').val();
        if (examenesData && examenesData.trim() !== '') {
            const examenes = examenesData.split(';').map(e => e.trim()).filter(e => e);
            examenes.forEach(function(examen) {
                // Formato: "Nombre - Especificaciones (Cantidad)"
                const match = examen.match(/(.+?)\s*-\s*(.+?)\s*\((\d+)\)/);
                if (match) {
                    const id = 'examen_' + Date.now() + Math.random();
                    const html = `
                    <div id="${id}" class="badge bg-light text-dark p-3 position-relative" style="white-space: normal; min-width: 150px; border: 1px solid #ddd; display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-weight: bold; word-break: break-word;">${match[1].trim()}</div>
                        <input type="text" class="form-control form-control-sm item-especificaciones" placeholder="Especificaciones del examen" value="${match[2].trim()}">
                        <input type="number" class="form-control form-control-sm item-cantidad" value="${match[3]}" min="1" placeholder="Cantidad">
                        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="eliminarItem('${id}', 'examenes', 'tablaExamenes')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                    $('#tablaExamenes').append(html);
                }
            });
        }
        
        // Cargar tratamientos
        const tratamientosData = $('#plan_tratamiento').val();
        if (tratamientosData && tratamientosData.trim() !== '') {
            const tratamientos = tratamientosData.split(';').map(t => t.trim()).filter(t => t);
            tratamientos.forEach(function(tratamiento) {
                const match = tratamiento.match(/(.+?)\s*-\s*(.+?)\s*\((\d+)\)/);
                if (match) {
                    const id = 'tratamiento_' + Date.now() + Math.random();
                    const html = `
                    <div id="${id}" class="badge bg-light text-dark p-3 position-relative" style="white-space: normal; min-width: 150px; border: 1px solid #ddd; display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-weight: bold; word-break: break-word;">${match[1].trim()}</div>
                        <input type="text" class="form-control form-control-sm item-especificaciones" placeholder="Dosis y frecuencia" value="${match[2].trim()}">
                        <input type="number" class="form-control form-control-sm item-cantidad" value="${match[3]}" min="1" placeholder="Cantidad">
                        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="eliminarItem('${id}', 'plan_tratamiento', 'tablaTratamientos')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                    $('#tablaTratamientos').append(html);
                }
            });
        }
        
        // Cargar recetas
        const recetasData = $('#receta').val();
        if (recetasData && recetasData.trim() !== '') {
            const recetas = recetasData.split(';').map(r => r.trim()).filter(r => r);
            recetas.forEach(function(receta) {
                const match = receta.match(/(.+?)\s*-\s*(.+?)\s*\((\d+)\)/);
                if (match) {
                    const id = 'receta_' + Date.now() + Math.random();
                    const html = `
                    <div id="${id}" class="badge bg-light text-dark p-3 position-relative" style="white-space: normal; min-width: 150px; border: 1px solid #ddd; display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-weight: bold; word-break: break-word;">${match[1].trim()}</div>
                        <input type="text" class="form-control form-control-sm item-especificaciones" placeholder="Dosis y frecuencia" value="${match[2].trim()}">
                        <input type="number" class="form-control form-control-sm item-cantidad" value="${match[3]}" min="1" placeholder="Cantidad">
                        <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: 5px; right: 5px; padding: 2px 6px;" onclick="eliminarItem('${id}', 'receta', 'tablaRecetas')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`;
                    $('#tablaRecetas').append(html);
                }
            });
        }
    }
});
