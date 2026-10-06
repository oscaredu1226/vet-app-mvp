/**
 * legacy-app.js
 * (Este es tu antiguo 'public/js/app.js')
 * Contiene la lógica global del sidebar, gestión de logo y notificaciones.
 * Ahora es importado por 'resources/js/app.js'
 */

$(document).ready(function() {

    // =================================================================
    // LÓGICA DEL SIDEBAR (BARRA LATERAL)
    // =================================================================
    const sidebar = $('#sidebar');
    const sidebarOverlay = $('#sidebarOverlay');
    const sidebarToggle = $('#sidebarToggle');

    function toggleSidebar() {
        sidebar.toggleClass('show');
        sidebarOverlay.toggleClass('show');
    }

    function openSidebar() {
        sidebar.addClass('show');
        if ($(window).width() < 992) {
            sidebarOverlay.addClass('show');
            $('body').css('overflow', 'hidden');
        }
    }

    function closeSidebar() {
        sidebar.removeClass('show');
        sidebarOverlay.removeClass('show');
        $('body').css('overflow', 'auto');
    }

    sidebarToggle.on('click', toggleSidebar);
    sidebarOverlay.on('click', closeSidebar);

    $('.sidebar .nav-link').on('click', function() {
        if ($(window).width() < 992) {
            closeSidebar();
        }
    });

    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            sidebar.addClass('show');
            sidebarOverlay.removeClass('show');
            $('body').css('overflow', 'auto');
        } else {
            sidebar.removeClass('show');
            sidebarOverlay.removeClass('show');
            $('body').css('overflow', 'auto');
        }
    });

    if ($(window).width() >= 992) {
        openSidebar();
    }

    // =================================================================
    // LÓGICA DE GESTIÓN DEL LOGO
    // =================================================================
    
    $('#logoImage').on('click', function(e) {
        e.stopPropagation();
        $('#logoDropdown').toggle();
    });

    $(document).on('click', function() {
        $('#logoDropdown').hide();
    });

    $('#logoDropdown').on('click', function(e) {
        e.stopPropagation();
    });

    $('#changeLogo').on('click', function(e) {
        e.preventDefault();
        $('#logoDropdown').hide();
        $('#logoForm')[0].reset();
        $('#logoPreview').addClass('d-none');
        $('#logoModal').modal('show');
    });

    $('#logoFile').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logoPreview').removeClass('d-none').find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    $('#btnGuardarLogo').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        const formData = new FormData($('#logoForm')[0]);

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
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
                $btn.prop('disabled', false).html('Guardar');
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al subir el logo.';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Guardar');
            }
        });
    });

    $('#deleteLogo').on('click', function(e) {
        e.preventDefault();
        $('#logoDropdown').hide();

        Swal.fire({
            title: '¿Eliminar logo?',
            text: 'Se restaurará la imagen por defecto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + '/logo/destroy', // Ruta del LogoController
                    type: 'POST',
                    data: { _token: CSRF_TOKEN },
                    dataType: 'json',
                    success: function(response) {
                        $('#logoImage').attr('src', response.default_image);
                        Swal.fire('¡Éxito!', response.message, 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    });

    // =================================================================
    // LÓGICA DE EVENTOS (CALENDARIO)
    // =================================================================
    function cargarContadorEventos() {
        // Solo ejecuta esto si el contenedor del contador existe (es decir, si el usuario está logueado)
        if ($('#contadorEventos').length) {
            $.ajax({
                url: BASE_URL + '/contador-eventos', // Ruta del CalendarioController
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const contador = response.contador;
                        $('#contadorEventos').text(contador);
                        if (contador > 0) {
                            $('#contadorEventos').show();
                        } else {
                            $('#contadorEventos').hide();
                        }
                    }
                }
                // No mostramos error, es una tarea de fondo
            });
        }
    }

    // Nota: El polling del contador de eventos ahora se gestiona desde app.blade.php
    // para evitar múltiples timers cuando hay múltiples cargas de este script
    // cargarContadorEventos();
    // setInterval(cargarContadorEventos, 60000);

    $('#btnCalendario').on('click', function() {
        window.location.href = BASE_URL + '/eventos';
    });
});