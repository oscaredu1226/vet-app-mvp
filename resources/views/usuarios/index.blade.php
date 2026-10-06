@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
        </div>

        {{-- Mensajes de éxito o error --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Formulario de Nuevo Usuario --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form id="formNuevoUsuario" action="{{ route('usuarios.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cargo" name="cargo"
                                placeholder="Ej: Veterinario, Recepcionista" required>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Seleccionar...</option>
                                <option value="administrador">Administrador</option>
                                <option value="usuario">Usuario</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="usuario" class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8"
                                autocomplete="new-password">
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña <span
                                    class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required minlength="8" autocomplete="new-password">
                        </div>

                        {{-- Permisos de Exportación --}}
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-primary mb-3"><i class="fas fa-file-excel me-2"></i>Permisos de Exportación a
                                Excel</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_clientes"
                                    name="puede_exportar_clientes" value="1">
                                <label class="form-check-label" for="puede_exportar_clientes">
                                    <i class="fas fa-users me-1"></i>Clientes
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_mascotas"
                                    name="puede_exportar_mascotas" value="1">
                                <label class="form-check-label" for="puede_exportar_mascotas">
                                    <i class="fas fa-paw me-1"></i>Mascotas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_ventas"
                                    name="puede_exportar_ventas" value="1">
                                <label class="form-check-label" for="puede_exportar_ventas">
                                    <i class="fas fa-shopping-cart me-1"></i>Ventas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_productos"
                                    name="puede_exportar_productos" value="1">
                                <label class="form-check-label" for="puede_exportar_productos">
                                    <i class="fas fa-box me-1"></i>Productos
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_servicios"
                                    name="puede_exportar_servicios" value="1">
                                <label class="form-check-label" for="puede_exportar_servicios">
                                    <i class="fas fa-stethoscope me-1"></i>Servicios
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_exportar_caja"
                                    name="puede_exportar_caja" value="1">
                                <label class="form-check-label" for="puede_exportar_caja">
                                    <i class="fas fa-cash-register me-1"></i>Caja
                                </label>
                            </div>
                        </div>

                        {{-- Permisos de Acceso a Módulos --}}
                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="text-success mb-3"><i class="fas fa-lock-open me-2"></i>Permisos de Acceso a Módulos</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_acceder_caja"
                                    name="puede_acceder_caja" value="1" checked>
                                <label class="form-check-label" for="puede_acceder_caja">
                                    <i class="fas fa-cash-register me-1"></i>Caja
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_acceder_ventas"
                                    name="puede_acceder_ventas" value="1" checked>
                                <label class="form-check-label" for="puede_acceder_ventas">
                                    <i class="fas fa-shopping-cart me-1"></i>Ventas
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="puede_acceder_egresos"
                                    name="puede_acceder_egresos" value="1" checked>
                                <label class="form-check-label" for="puede_acceder_egresos">
                                    <i class="fas fa-money-bill-wave me-1"></i>Egresos
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-success" id="btnGuardarUsuario">
                                <i class="fas fa-save me-2"></i>Registrar Usuario
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lista de Usuarios --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Usuarios Registrados</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="me-2">Mostrar</label>
                        <select class="form-select form-select-sm d-inline-block w-auto" id="per_page"
                            onchange="window.location.href=this.value">
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => 100]) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="ms-2">registros</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Cargo</th>
                                <th>Rol</th>
                                <th>Email</th>
                                <th>Permisos de Exportación</th>
                                <th>Acceso a Módulos</th>
                                <th>Fecha de Inscripción</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td><strong>{{ $usuario->usuario }}</strong></td>
                                    <td>{{ $usuario->name }} {{ $usuario->apellido }}</td>
                                    <td>{{ $usuario->cargo }}</td>
                                    <td>
                                        @if($usuario->role === 'administrador')
                                            <span class="badge bg-danger"><i class="fas fa-crown me-1"></i>Administrador</span>
                                        @else
                                            <span class="badge bg-info"><i class="fas fa-user me-1"></i>Usuario</span>
                                        @endif
                                    </td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($usuario->puede_exportar_clientes)
                                                <span class="badge bg-success" title="Clientes"><i class="fas fa-users"></i></span>
                                            @endif
                                            @if($usuario->puede_exportar_mascotas)
                                                <span class="badge bg-success" title="Mascotas"><i class="fas fa-paw"></i></span>
                                            @endif
                                            @if($usuario->puede_exportar_ventas)
                                                <span class="badge bg-success" title="Ventas"><i
                                                        class="fas fa-shopping-cart"></i></span>
                                            @endif
                                            @if($usuario->puede_exportar_productos)
                                                <span class="badge bg-success" title="Productos"><i class="fas fa-box"></i></span>
                                            @endif
                                            @if($usuario->puede_exportar_servicios)
                                                <span class="badge bg-success" title="Servicios"><i
                                                        class="fas fa-stethoscope"></i></span>
                                            @endif
                                            @if($usuario->puede_exportar_caja)
                                                <span class="badge bg-success" title="Caja"><i
                                                        class="fas fa-cash-register"></i></span>
                                            @endif
                                            @if(!$usuario->puede_exportar_clientes && !$usuario->puede_exportar_mascotas && !$usuario->puede_exportar_ventas && !$usuario->puede_exportar_productos && !$usuario->puede_exportar_servicios && !$usuario->puede_exportar_caja)
                                                <span class="badge bg-secondary">Sin permisos</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($usuario->puede_acceder_caja)
                                                <span class="badge bg-primary" title="Caja"><i class="fas fa-cash-register"></i></span>
                                            @endif
                                            @if($usuario->puede_acceder_ventas)
                                                <span class="badge bg-primary" title="Ventas"><i class="fas fa-shopping-cart"></i></span>
                                            @endif
                                            @if($usuario->puede_acceder_egresos)
                                                <span class="badge bg-primary" title="Egresos"><i class="fas fa-money-bill-wave"></i></span>
                                            @endif
                                            @if(!$usuario->puede_acceder_caja && !$usuario->puede_acceder_ventas && !$usuario->puede_acceder_egresos)
                                                <span class="badge bg-secondary">Sin acceso</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning btn-edit-usuario"
                                                data-id="{{ $usuario->id }}" data-name="{{ $usuario->name }}"
                                                data-apellido="{{ $usuario->apellido }}" data-cargo="{{ $usuario->cargo }}"
                                                data-role="{{ $usuario->role }}" data-usuario="{{ $usuario->usuario }}"
                                                data-email="{{ $usuario->email }}"
                                                data-puede-exportar-clientes="{{ $usuario->puede_exportar_clientes ? '1' : '0' }}"
                                                data-puede-exportar-mascotas="{{ $usuario->puede_exportar_mascotas ? '1' : '0' }}"
                                                data-puede-exportar-ventas="{{ $usuario->puede_exportar_ventas ? '1' : '0' }}"
                                                data-puede-exportar-productos="{{ $usuario->puede_exportar_productos ? '1' : '0' }}"
                                                data-puede-exportar-servicios="{{ $usuario->puede_exportar_servicios ? '1' : '0' }}"
                                                data-puede-exportar-caja="{{ $usuario->puede_exportar_caja ? '1' : '0' }}"
                                                data-puede-acceder-caja="{{ $usuario->puede_acceder_caja ? '1' : '0' }}"
                                                data-puede-acceder-ventas="{{ $usuario->puede_acceder_ventas ? '1' : '0' }}"
                                                data-puede-acceder-egresos="{{ $usuario->puede_acceder_egresos ? '1' : '0' }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($usuario->id !== auth()->id())
                                                <button class="btn btn-outline-danger btn-delete-usuario"
                                                    data-id="{{ $usuario->id }}"
                                                    data-name="{{ $usuario->name }} {{ $usuario->apellido }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                        No hay usuarios registrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-3">
                    {{ $usuarios->appends(['per_page' => request('per_page')])->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Editar Usuario --}}
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_usuario_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_apellido" class="form-label">Apellido <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_cargo" name="cargo" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_role" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="administrador">Administrador</option>
                                    <option value="usuario">Usuario</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_usuario" class="form-label">Usuario <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_usuario" name="usuario" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="edit_password" name="password" minlength="8"
                                    autocomplete="new-password">
                                <small class="text-muted">Dejar en blanco para no cambiar</small>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="edit_password_confirmation"
                                    name="password_confirmation" minlength="8" autocomplete="new-password">
                            </div>

                            {{-- Permisos de Exportación --}}
                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-file-excel me-2"></i>Permisos de Exportación
                                    a Excel</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_clientes" name="puede_exportar_clientes" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_clientes">
                                        <i class="fas fa-users me-1"></i>Clientes
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_mascotas" name="puede_exportar_mascotas" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_mascotas">
                                        <i class="fas fa-paw me-1"></i>Mascotas
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_ventas" name="puede_exportar_ventas" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_ventas">
                                        <i class="fas fa-shopping-cart me-1"></i>Ventas
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_productos" name="puede_exportar_productos" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_productos">
                                        <i class="fas fa-box me-1"></i>Productos
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_servicios" name="puede_exportar_servicios" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_servicios">
                                        <i class="fas fa-stethoscope me-1"></i>Servicios
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_exportar_caja" name="puede_exportar_caja" value="1">
                                    <label class="form-check-label" for="edit_puede_exportar_caja">
                                        <i class="fas fa-cash-register me-1"></i>Caja
                                    </label>
                                </div>
                            </div>

                            {{-- Permisos de Acceso a Módulos --}}
                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="text-success mb-3"><i class="fas fa-lock-open me-2"></i>Permisos de Acceso a Módulos</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_acceder_caja" name="puede_acceder_caja" value="1">
                                    <label class="form-check-label" for="edit_puede_acceder_caja">
                                        <i class="fas fa-cash-register me-1"></i>Caja
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_acceder_ventas" name="puede_acceder_ventas" value="1">
                                    <label class="form-check-label" for="edit_puede_acceder_ventas">
                                        <i class="fas fa-shopping-cart me-1"></i>Ventas
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="edit_puede_acceder_egresos" name="puede_acceder_egresos" value="1">
                                    <label class="form-check-label" for="edit_puede_acceder_egresos">
                                        <i class="fas fa-money-bill-wave me-1"></i>Egresos
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarUsuario">
                        <i class="fas fa-save me-2"></i>Actualizar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            // Guardar nuevo usuario
            $('#formNuevoUsuario').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#btnGuardarUsuario');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al registrar usuario';
                        Swal.fire('Error', errorMsg, 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Registrar Usuario');
                    }
                });
            });

            // Abrir modal de edición
            $('.btn-edit-usuario').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const apellido = $(this).data('apellido');
                const cargo = $(this).data('cargo');
                const role = $(this).data('role');
                const usuario = $(this).data('usuario');
                const email = $(this).data('email');

                $('#edit_usuario_id').val(id);
                $('#edit_name').val(name);
                $('#edit_apellido').val(apellido);
                $('#edit_cargo').val(cargo);
                $('#edit_role').val(role);
                $('#edit_usuario').val(usuario);
                $('#edit_email').val(email);
                $('#edit_password').val('');
                $('#edit_password_confirmation').val('');

                // Permisos de exportación
                $('#edit_puede_exportar_clientes').prop('checked', $(this).data('puede-exportar-clientes') == '1');
                $('#edit_puede_exportar_mascotas').prop('checked', $(this).data('puede-exportar-mascotas') == '1');
                $('#edit_puede_exportar_ventas').prop('checked', $(this).data('puede-exportar-ventas') == '1');
                $('#edit_puede_exportar_productos').prop('checked', $(this).data('puede-exportar-productos') == '1');
                $('#edit_puede_exportar_servicios').prop('checked', $(this).data('puede-exportar-servicios') == '1');
                $('#edit_puede_exportar_caja').prop('checked', $(this).data('puede-exportar-caja') == '1');

                // Permisos de acceso a módulos
                $('#edit_puede_acceder_caja').prop('checked', $(this).data('puede-acceder-caja') == '1');
                $('#edit_puede_acceder_ventas').prop('checked', $(this).data('puede-acceder-ventas') == '1');
                $('#edit_puede_acceder_egresos').prop('checked', $(this).data('puede-acceder-egresos') == '1');

                const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
                modal.show();
            });

            // Actualizar usuario
            $('#btnActualizarUsuario').on('click', function () {
                const id = $('#edit_usuario_id').val();
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

                $.ajax({
                    url: `/usuarios/${id}`,
                    method: 'POST',
                    data: $('#formEditarUsuario').serialize() + '&_method=PUT',
                    success: function (response) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
                        modal.hide();
                        Swal.fire('¡Éxito!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al actualizar usuario';
                        Swal.fire('Error', errorMsg, 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Actualizar Usuario');
                    }
                });
            });

            // Eliminar usuario
            $('.btn-delete-usuario').on('click', function () {
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
                            url: `/usuarios/${id}`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function (response) {
                                Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                const errorMsg = xhr.responseJSON?.message || 'Error al eliminar usuario';
                                Swal.fire('Error', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // Auto-cerrar alertas
            setTimeout(function () {
                $('.alert').fadeOut();
            }, 5000);
        });
    </script>
@endpush