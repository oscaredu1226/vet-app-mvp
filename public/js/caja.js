/**
 * public/js/caja.js
 *
 * Lógica de la página de Caja (resources/views/caja/index.blade.php)
 * Reemplaza JS de 'caja.php' [cite: 315-325]
 */

$(document).ready(function() {
    
    // --- CERRAR CAJA Y EXPORTAR EXCEL ---
    // Reemplaza 'modules/cerrar_caja.php' [cite: 265-268]
    $('#btnCerrarCaja').on('click', function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esto cerrará la caja, generará un reporte Excel y lo descargará automáticamente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar spinner mientras se procesa
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Generando reporte y cerrando la caja...',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                // Cerrar la caja (genera el reporte automáticamente)
                $.ajax({
                    url: BASE_URL + '/caja/cerrar',
                    method: 'POST',
                    data: { _token: CSRF_TOKEN },
                    success: function(response) {
                        if (response.success && response.download_url) {
                            // Descargar el archivo generado
                            window.location.href = response.download_url;
                            
                            // Resetear el Total en Caja Manual a 0
                            $.ajax({
                                url: BASE_URL + '/caja/actualizar-total',
                                method: 'POST',
                                data: { 
                                    _token: CSRF_TOKEN,
                                    nuevo_total_caja: 0
                                },
                                success: function() {
                                    Swal.close();
                                    Swal.fire({
                                        title: '¡Caja Cerrada!',
                                        html: 'Se generó el reporte <strong>' + response.filename + '</strong> y se reinició la caja.',
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                },
                                error: function() {
                                    Swal.close();
                                    Swal.fire('¡Caja Cerrada!', 'Reporte generado: ' + response.filename, 'success').then(() => {
                                        location.reload();
                                    });
                                }
                            });
                        } else {
                            Swal.close();
                            Swal.fire('Error', 'No se pudo generar el reporte.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire('Error', xhr.responseJSON?.message || 'No se pudo cerrar la caja.', 'error');
                    }
                });
            }
        });
    });
});