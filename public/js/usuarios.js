// Archivo: public/js/usuarios.js
// Gestión de usuarios del sistema

$(document).ready(function() {
    const BASE_URL = window.location.origin;

    // Guardar nuevo usuario
    $('#formNuevoUsuario').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#btnGuardarUsuario');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
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
                const errorMsg = xhr.responseJSON?.message || 'Error al registrar usuario';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Registrar Usuario');
            }
        });
    });

    // Abrir modal de edición (usando delegación de eventos)
    $(document).on('click', '.btn-edit-usuario', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const name = $btn.data('name');
        const apellido = $btn.data('apellido');
        const cargo = $btn.data('cargo');
        const role = $btn.data('role');
        const usuario = $btn.data('usuario');
        const email = $btn.data('email');
        
        // Permisos de exportación (jQuery convierte data-puede-exportar-clientes a puedeExportarClientes)
        const puedeExportarClientes = $btn.data('puedeExportarClientes');
        const puedeExportarMascotas = $btn.data('puedeExportarMascotas');
        const puedeExportarVentas = $btn.data('puedeExportarVentas');
        const puedeExportarProductos = $btn.data('puedeExportarProductos');
        const puedeExportarServicios = $btn.data('puedeExportarServicios');
        const puedeExportarCaja = $btn.data('puedeExportarCaja');
        
        console.log('Permisos cargados:', {
            clientes: puedeExportarClientes,
            mascotas: puedeExportarMascotas,
            ventas: puedeExportarVentas,
            productos: puedeExportarProductos,
            servicios: puedeExportarServicios,
            caja: puedeExportarCaja
        });
        
        $('#edit_usuario_id').val(id);
        $('#edit_name').val(name);
        $('#edit_apellido').val(apellido);
        $('#edit_cargo').val(cargo);
        $('#edit_role').val(role);
        $('#edit_usuario').val(usuario);
        $('#edit_email').val(email || '');
        $('#edit_password').val('');
        $('#edit_password_confirmation').val('');
        
        // Establecer switches de permisos (convertir a boolean)
        $('#edit_puede_exportar_clientes').prop('checked', puedeExportarClientes == 1 || puedeExportarClientes == '1');
        $('#edit_puede_exportar_mascotas').prop('checked', puedeExportarMascotas == 1 || puedeExportarMascotas == '1');
        $('#edit_puede_exportar_ventas').prop('checked', puedeExportarVentas == 1 || puedeExportarVentas == '1');
        $('#edit_puede_exportar_productos').prop('checked', puedeExportarProductos == 1 || puedeExportarProductos == '1');
        $('#edit_puede_exportar_servicios').prop('checked', puedeExportarServicios == 1 || puedeExportarServicios == '1');
        $('#edit_puede_exportar_caja').prop('checked', puedeExportarCaja == 1 || puedeExportarCaja == '1');
        
        const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
        modal.show();
    });

    // Actualizar usuario
    $('#btnActualizarUsuario').on('click', function() {
        const id = $('#edit_usuario_id').val();
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        $.ajax({
            url: `${BASE_URL}/usuarios/${id}`,
            method: 'POST',
            data: $('#formEditarUsuario').serialize() + '&_method=PUT',
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
                modal.hide();
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
                const errorMsg = xhr.responseJSON?.message || 'Error al actualizar usuario';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Actualizar Usuario');
            }
        });
    });

    // Eliminar usuario
    $('.btn-delete-usuario').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        Swal.fire({
            title: '¿Eliminar usuario?',
            text: `¿Estás seguro de eliminar a ${name}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${BASE_URL}/usuarios/${id}`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al eliminar usuario';
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }
        });
    });
});
