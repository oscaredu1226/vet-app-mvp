/**
 * app.js
 * * Este archivo contiene toda la lógica JavaScript global para la plantilla
 * principal (layouts/app.blade.php).
 * Reemplaza la lógica inline <script> de tu antiguo index.php.
 */
$(document).ready(function() {

    // =================================================================
    // LÓGICA DEL SIDEBAR (BARRA LATERAL)
    // Basado en tu index.php [cite: 1691-1714]
    // =================================================================
    const sidebar = $('#sidebar');
    const sidebarOverlay = $('#sidebarOverlay');
    const sidebarToggle = $('#sidebarToggle');

    // Función para abrir/cerrar sidebar
    function toggleSidebar() {
        if (sidebar.hasClass('active')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Función para abrir sidebar
    function openSidebar() {
        sidebar.addClass('active');
        if ($(window).width() < 992) {
            sidebarOverlay.addClass('active');
            $('body').css('overflow', 'hidden');
        }
    }

    // Función para cerrar sidebar
    function closeSidebar() {
        sidebar.removeClass('active');
        sidebarOverlay.removeClass('active');
        $('body').css('overflow', 'auto');
    }

    // Toggle sidebar al hacer clic en el botón
    sidebarToggle.on('click', function() {
        toggleSidebar();
    });

    // Cerrar sidebar al hacer clic en el overlay
    sidebarOverlay.on('click', function() {
        closeSidebar();
    });

    // Cerrar sidebar al hacer clic en un enlace en mobile/tablet
    $('.sidebar .nav-link').on('click', function() {
        if ($(window).width() < 992) {
            closeSidebar();
        }
    });

    // Manejar redimensionamiento de ventana
    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            // En desktop, el sidebar siempre está visible (sin clase active)
            sidebar.removeClass('active');
            sidebarOverlay.removeClass('active');
            $('body').css('overflow', 'auto');
        } else {
            // En mobile/tablet, cerrar sidebar por defecto
            sidebar.removeClass('active');
            sidebarOverlay.removeClass('active');
            $('body').css('overflow', 'auto');
        }
    });

    // Inicializar estado del sidebar basado en el tamaño de pantalla
    // En desktop (>= 992px) el sidebar está siempre visible por CSS
    // En mobile/tablet está oculto por defecto


    // =================================================================
    // LÓGICA DE GESTIÓN DEL LOGO
    // Basado en tu index.php [cite: 1729-1770]
    // =================================================================
    
    // Mostrar/ocultar menú del logo
    $('#logoImage').on('click', function(e) {
        e.stopPropagation();
        $('#logoDropdown').toggle();
    });

    // Cerrar menú al hacer clic fuera
    $(document).on('click', function() {
        $('#logoDropdown').hide();
    });

    // Prevenir que el menú se cierre al hacer clic dentro
    $('#logoDropdown').on('click', function(e) {
        e.stopPropagation();
    });

    // Botón "Cambiar" logo
    $('#changeLogo').on('click', function(e) {
        e.preventDefault();
        $('#logoDropdown').hide();
        // Limpiar formulario antes de mostrar
        $('#logoForm')[0].reset();
        $('#logoPreview').addClass('d-none');
        $('#logoModal').modal('show');
    });

    // Vista previa del archivo de logo
    $('#logoFile').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').removeClass('d-none');
                $('#logoPreview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            $('#logoPreview').addClass('d-none');
        }
    });

    // Botón "Guardar" logo (AJAX)
    // Reemplaza 'modules/cambiar_logo.php'
    $('#btnGuardarLogo').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        const formData = new FormData($('#logoForm')[0]);
        // FormData ya incluye el _token CSRF del formulario

        $.ajax({
            url: BASE_URL + '/logo/update', // Ruta del LogoController
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#logoImage').attr('src', response.image_data);
                    $('#logoModal').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                $btn.prop('disabled', false).html('Guardar');
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al subir el logo. Verifique el tamaño y tipo de archivo.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Guardar');
            }
        });
    });

    // Botón "Eliminar" logo (AJAX)
    // Reemplaza 'modules/eliminar_logo.php'
    $('#deleteLogo').on('click', function(e) {
        e.preventDefault();
        $('#logoDropdown').hide();

        Swal.fire({
            title: '¿Eliminar logo?',
            text: 'Se restaurará la imagen por defecto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/logo/destroy', // Ruta del LogoController
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN // Token global
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#logoImage').attr('src', response.default_image);
                            Swal.fire('¡Éxito!', response.message, 'success');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al comunicar con el servidor';
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    });

    // =================================================================
    // LÓGICA DE EVENTOS (CALENDARIO)
    // =================================================================

    // Función para cargar el contador de eventos pendientes
    // Reemplaza 'modules/contador_eventos.php'
    function cargarContadorEventos() {
        $.ajax({
            url: BASE_URL + '/contador-eventos', // Ruta del CalendarioController
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const contador = response.contador;
                    $('#contadorEventos').text(contador);
                    
                    if (contador === 0) {
                        $('#contadorEventos').hide();
                    } else {
                        $('#contadorEventos').show();
                    }
                }
            },
            error: function() {
                console.log('Error al cargar contador de eventos');
            }
        });
    }

    // Cargar contador al inicio y establecer un intervalo de 1 minuto
    // Nota: El polling ahora se gestiona desde app.blade.php para evitar múltiples timers
    // cargarContadorEventos();
    // setInterval(cargarContadorEventos, 60000);

    // Botón de la campana para ir al calendario
    // Reemplaza 'abrirCalendario()'
    $('#btnCalendario').on('click', function() {
        window.location.href = BASE_URL + '/eventos';
    });

});