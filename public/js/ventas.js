/**
 * public/js/ventas.js
 *
 * Lógica de la página de Ventas (resources/views/ventas/index.blade.php)
 * Reemplaza a 'ventas.js' [cite: 52-60]
 */

$(document).ready(function() {
    
    let carritoItems = [];
    let productoTemp = null;
    let pagosRealizados = [];
    let totalVenta = 0;
    let montoRestante = 0;

    // --- BÚSQUEDA ---

    $('#buscar_mascota').on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 3) {
            $('#resultados_mascotas').hide();
            return;
        }
        $.ajax({
            url: BASE_URL + '/buscar-mascotas', // Ruta de MascotaController
            method: 'GET',
            data: { query: query },
            success: function(mascotas) {
                let html = '';
                if (mascotas.length > 0) {
                    mascotas.forEach(function(m) {
                        html += `<a href="#" class="list-group-item list-group-item-action seleccionar-mascota" 
                                     data-id="${m.id}" data-nombre="${m.nombre}" data-propietario="${m.propietario}" data-especie="${m.especie}">
                                     <strong>${m.display}</strong>
                                 </a>`;
                    });
                } else {
                    html = '<div class="list-group-item text-muted">No se encontraron mascotas</div>';
                }
                $('#resultados_mascotas').html(html).show();
            }
        });
    });

    $('#buscar_producto').on('input', function() {
        const query = $(this).val().trim();
        if (query.length < 2) {
            $('#resultados_productos').hide();
            productoTemp = null;
            return;
        }
        $.ajax({
            url: BASE_URL + '/buscar-items', // Ruta de VentaController
            method: 'GET',
            data: { query: query },
            success: function(items) {
                let html = '';
                if (items.length > 0) {
                    items.forEach(function(item) {
                        const stockInfo = item.tipo === 'producto' ? ` (Stock: ${item.stock})` : ' (Servicio)';
                        html += `<a href="#" class="list-group-item list-group-item-action seleccionar-producto" 
                                     data-item='${JSON.stringify(item)}'>
                                     <strong>${item.nombre}</strong> - S/ ${parseFloat(item.precio).toFixed(2)}${stockInfo}
                                 </a>`;
                    });
                } else {
                    html = '<div class="list-group-item text-muted">No se encontraron items</div>';
                }
                $('#resultados_productos').html(html).show();
            }
        });
    });

    // --- SELECCIÓN ---

    $(document).on('click', '.seleccionar-mascota', function(e) {
        e.preventDefault();
        $('#id_mascota').val($(this).data('id'));
        $('#buscar_mascota').val($(this).data('nombre') + ' - ' + $(this).data('propietario'));
        $('#info_mascota').html(`
            <strong>Mascota:</strong> ${$(this).data('nombre')} | 
            <strong>Propietario:</strong> ${$(this).data('propietario')} | 
            <strong>Especie:</strong> ${$(this).data('especie')}
        `).show();
        $('#resultados_mascotas').hide();
        $('#btnLimpiarMascota').show();
        validarFormulario();
    });

    $('#btnLimpiarMascota').click(function() {
        $('#id_mascota').val('');
        $('#buscar_mascota').val('');
        $('#info_mascota').hide();
        $(this).hide();
        validarFormulario();
    });

    $(document).on('click', '.seleccionar-producto', function(e) {
        e.preventDefault();
        productoTemp = $(this).data('item');
        $('#buscar_producto').val(productoTemp.nombre);
        $('#resultados_productos').hide();
        $('#cantidad_item').focus();
    });

    // --- CARRITO ---

    // --- AÑADIR CON ENTER EN CANTIDAD ---
    // Esta es la parte nueva que solicitaste
    $('#cantidad_item').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Evita comportamientos extraños de form
            $('#btnAgregarItem').click(); // Ejecuta el botón añadir
        }
    });

    $('#btnAgregarItem').click(function() {
        if (!productoTemp) {
            Swal.fire('Error', 'Debe seleccionar un producto o servicio', 'error');
            return;
        }
        const cantidad = parseInt($('#cantidad_item').val()) || 1;
        
        if (productoTemp.tipo === 'producto' && cantidad > productoTemp.stock) {
            Swal.fire('Error', `Stock insuficiente. Disponible: ${productoTemp.stock}`, 'error');
            return;
        }

        const itemExistente = carritoItems.find(item => item.id === productoTemp.id && item.tipo === productoTemp.tipo);
        if (itemExistente) {
            itemExistente.cantidad += cantidad;
        } else {
            carritoItems.push({ ...productoTemp, cantidad: cantidad });
        }
        
        actualizarTablaCarrito();
        $('#buscar_producto').val('');
        $('#cantidad_item').val('1');
        productoTemp = null;
        // Opcional: Volver el foco al buscador de productos para seguir vendiendo rápido
        $('#buscar_producto').focus();
    });

    $(document).on('click', '.eliminar_venta', function() {
        const index = $(this).data('index');
        carritoItems.splice(index, 1);
        actualizarTablaCarrito();
    });

    function actualizarTablaCarrito() {
        totalVenta = 0;
        if (carritoItems.length === 0) {
            $('#carrito_body').html('<tr id="carrito_vacio"><td colspan="6" class="text-center text-muted py-4">No hay productos en el carrito</td></tr>');
            $('#btnCancelar').hide();
        } else {
            let html = '';
            carritoItems.forEach((item, index) => {
                const subtotal = item.cantidad * item.precio;
                totalVenta += subtotal;
                html += `
                    <tr>
                        <td>${item.nombre}</td>
                        <td><span class="badge bg-${item.tipo === 'producto' ? 'primary' : 'success'}">${item.tipo}</span></td>
                        <td>${item.cantidad}</td>
                        <td>S/ ${parseFloat(item.precio).toFixed(2)}</td>
                        <td>S/ ${subtotal.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm eliminar_venta" data-index="${index}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#carrito_body').html(html);
            $('#btnCancelar').show();
        }
        actualizarPagos();
    }

    // --- PAGOS ---

    function actualizarPagos() {
        const totalPagado = pagosRealizados.reduce((sum, pago) => sum + pago.cantidad, 0);
        montoRestante = totalVenta - totalPagado;
        
        $('#total_carrito').text('S/ ' + totalVenta.toFixed(2));
        $('#monto_restante').text('S/ ' + montoRestante.toFixed(2));
        $('#cantidad_pagar').val(montoRestante.toFixed(2)).attr('max', montoRestante.toFixed(2));

        if (montoRestante <= 0 && totalVenta > 0) {
            $('#cantidad_pagar, #medio_pago').prop('disabled', true);
            $('#btnRegistrarVenta').text('Finalizar Venta').removeClass('btn-primary').addClass('btn-success');
        } else {
            $('#cantidad_pagar, #medio_pago').prop('disabled', false);
            $('#btnRegistrarVenta').text('Registrar Pago').removeClass('btn-success').addClass('btn-primary');
        }
        validarFormulario();
    }

    $('#btnRegistrarVenta').click(function() {
        const mascotaId = $('#id_mascota').val();
        if (!mascotaId || carritoItems.length === 0) {
            Swal.fire('Error', 'Debe seleccionar una mascota y agregar items al carrito', 'error');
            return;
        }

        const cantidadPagar = parseFloat($('#cantidad_pagar').val()) || 0;
        const medioPago = $('#medio_pago').val();

        if (montoRestante > 0 && (cantidadPagar <= 0 || !medioPago)) {
            Swal.fire('Error', 'Debe ingresar una cantidad y medio de pago válidos', 'error');
            return;
        }

        if (cantidadPagar > montoRestante + 0.001) { // Pequeño margen de error
            Swal.fire('Error', `El pago no puede superar el monto restante (S/ ${montoRestante.toFixed(2)})`, 'error');
            return;
        }

        if (cantidadPagar > 0) {
            pagosRealizados.push({
                cantidad: cantidadPagar,
                medio_pago: medioPago,
                fecha: new Date().toLocaleString()
            });
            actualizarHistorialPagos();
        }
        
        actualizarPagos(); 

        if (montoRestante <= 0) {
            finalizarVenta();
        } else {
            $('#cantidad_pagar').val(montoRestante.toFixed(2));
            $('#medio_pago').val('');
            validarFormulario();
            Swal.fire('Pago Parcial Registrado', `Restan S/ ${montoRestante.toFixed(2)} por pagar.`, 'info');
        }
    });

    function actualizarHistorialPagos() {
        if (pagosRealizados.length > 0) {
            $('#historial_pagos').show();
            let html = '';
            pagosRealizados.forEach(pago => {
                html += `<tr>
                    <td>S/ ${pago.cantidad.toFixed(2)}</td>
                    <td><span class="badge bg-info">${pago.medio_pago}</span></td>
                    <td>${pago.fecha}</td>
                </tr>`;
            });
            $('#pagos_realizados').html(html);
        } else {
            $('#historial_pagos').hide();
        }
    }

    function finalizarVenta() {
        const $btn = $('#btnRegistrarVenta');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Finalizando...');

        let medioPagoPrincipal = 'Mixto';
        if (pagosRealizados.length === 1) {
            medioPagoPrincipal = pagosRealizados[0].medio_pago;
        }

        const datosVenta = {
            _token: CSRF_TOKEN,
            id_mascota: $('#id_mascota').val(),
            items: JSON.stringify(carritoItems),
            medio_pago: medioPagoPrincipal,
            total_general: totalVenta,
            pagos_realizados: JSON.stringify(pagosRealizados)
        };

        $.ajax({
            url: BASE_URL + '/ventas', // Ruta POST a VentaController@store
            method: 'POST',
            data: datosVenta,
            dataType: 'json',
            success: function(response) {
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
                const errorMsg = xhr.responseJSON?.message || 'Error al procesar la venta.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Finalizar Venta');
                // Revertir el último pago si falló
                pagosRealizados.pop();
                actualizarHistorialPagos();
                actualizarPagos();
            }
        });
    }

    $('#btnCancelar').click(function() {
        Swal.fire({
            title: '¿Cancelar Venta?',
            text: 'Se limpiará el carrito y los pagos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); 
            }
        });
    });

    function validarFormulario() {
        const mascotaId = $('#id_mascota').val();
        const tieneItems = carritoItems.length > 0;
        
        if (montoRestante <= 0) {
            $('#btnRegistrarVenta').prop('disabled', !(mascotaId && tieneItems));
        } else {
            const cantidadPagar = parseFloat($('#cantidad_pagar').val()) || 0;
            const medioPago = $('#medio_pago').val();
            $('#btnRegistrarVenta').prop('disabled', !(mascotaId && tieneItems && cantidadPagar > 0 && medioPago));
        }
    }

    $('#cantidad_pagar, #medio_pago').on('input change', validarFormulario);

    // --- HISTORIAL DE VENTAS ---
    window.imprimirTicket = function(id) {
        window.open(BASE_URL + '/ventas/' + id, '_blank');
    };

    // --- MODAL DETALLE DE VENTA ---
    let ventaIdActual = null;

    window.verDetalleVenta = function(id) {
        ventaIdActual = id;
        const modal = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));
        
        // Mostrar loading
        $('#contenidoDetalleVenta').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);
        
        modal.show();
        
        // Cargar contenido del ticket
        $.ajax({
            url: BASE_URL + '/ventas/' + id + '/detalle',
            method: 'GET',
            success: function(data) {
                let itemsHtml = '';
                data.items.forEach(function(item) {
                    itemsHtml += `
                        <div class="item-row">
                            <span>${item.descripcion}</span>
                            <span>${item.cantidad}</span>
                            <span>${parseFloat(item.precio).toFixed(2)}</span>
                            <span>${parseFloat(item.total).toFixed(2)}</span>
                        </div>
                    `;
                });

                // Generar HTML de métodos de pago
                let metodoPagoTitulo = '';
                let pagosHtml = '';
                
                if (data.pagos && data.pagos.length > 1) {
                    metodoPagoTitulo = '<p><strong>MÉTODOS DE PAGO: PAGO MIXTO</strong></p>';
                    data.pagos.forEach(function(pago) {
                        const metodoPago = pago.medio_pago ? pago.medio_pago.toUpperCase() : 'NO ESPECIFICADO';
                        pagosHtml += `<p>: ${metodoPago}: S/ ${parseFloat(pago.monto).toFixed(2)}</p>`;
                    });
                } else if (data.pagos && data.pagos.length === 1) {
                    metodoPagoTitulo = '<p><strong>MÉTODOS DE PAGO:</strong></p>';
                    const metodoPago = data.pagos[0].medio_pago ? data.pagos[0].medio_pago.toUpperCase() : 'NO ESPECIFICADO';
                    pagosHtml = `<p>: ${metodoPago}: S/ ${parseFloat(data.pagos[0].monto).toFixed(2)}</p>`;
                } else {
                    metodoPagoTitulo = '<p><strong>MÉTODOS DE PAGO:</strong></p>';
                    const metodoPago = data.venta.medio_pago ? data.venta.medio_pago.toUpperCase() : 'NO ESPECIFICADO';
                    pagosHtml = `<p>: ${metodoPago}: S/ ${parseFloat(data.venta.subtotal).toFixed(2)}</p>`;
                }

                const html = `
                    <div class="ticket-modal">
                        <div class="ticket-header">
                            <img src="${data.logo_src}" alt="Logo" style="max-width: 100px; max-height: 100px; margin-bottom: 10px;">
                            <h4>${data.configuracion.nombre_negocio.toUpperCase()}</h4>
                            <p>${data.configuracion.direccion.toUpperCase()}</p>
                            <p>RUC: ${data.configuracion.ruc}</p>
                            <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                        </div>
                        
                        <div class="ticket-header">
                            <h4>NOTA DE VENTA</h4>
                            <p>${String(data.venta.id_venta).padStart(6, '0')}</p>
                        </div>
                        
                        <div class="cliente-info">
                            <p>FECHA: ${data.fecha} &nbsp;&nbsp;&nbsp; HORA: ${data.hora}</p>
                            <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                            <p>CLIENTE: ${(data.cliente.nombre + ' ' + data.cliente.apellido).toUpperCase()}</p>
                            <p>DOCUMENTO: ${data.cliente.dni || 'N/A'}</p>
                            <p>MASCOTA: ${data.mascota.nombre.toUpperCase()}</p>
                        </div>
                        
                        <div class="items">
                            <div class="item-row">
                                <span><strong>DESC</strong></span>
                                <span><strong>CANT</strong></span>
                                <span><strong>P UNIT</strong></span>
                                <span><strong>TOTAL</strong></span>
                            </div>
                            <div style="border-bottom: 1px dashed #000; margin-bottom: 10px;"></div>
                            ${itemsHtml}
                        </div>
                        
                        <div class="totales">
                            <p><strong>SUBTOTAL:</strong> S/ ${parseFloat(data.venta.subtotal).toFixed(2)}</p>
                            <p><strong>DESCUENTO:</strong> S/ 0.00</p>
                            <p><strong>TOTAL:</strong> S/ ${parseFloat(data.venta.subtotal).toFixed(2)}</p>
                        </div>
                        
                        <div class="metodo-pago">
                            <p>SON: ${data.total_letras}</p>
                            <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                            ${metodoPagoTitulo}
                            ${pagosHtml}
                            <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                        </div>
                        
                        <div class="ticket-footer">
                            <p>GRACIAS POR SU COMPRA</p>
                        </div>
                    </div>
                `;
                
                $('#contenidoDetalleVenta').html(html);
            },
            error: function() {
                $('#contenidoDetalleVenta').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error al cargar el detalle de la venta.
                    </div>
                `);
            }
        });
    };

    // Imprimir desde el modal
    $('#btnImprimirModal').on('click', function() {
        if (ventaIdActual) {
            window.open(BASE_URL + '/ventas/' + ventaIdActual, '_blank');
        }
    });
});