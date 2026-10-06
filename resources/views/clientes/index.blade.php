{{-- Hereda la plantilla principal 'app.blade.php' --}}
@extends('layouts.app')

{{-- Define el contenido que se insertará en el @yield('content') --}}
@section('content')
    <div class="container-fluid px-4 clientes">

        {{-- Encabezado y Estadísticas (de clientes.php [cite: 366-373]) --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-users me-2"></i>Gestión de Clientes</h2>
        </div>

        <div class="row mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total Clientes</h6>
                                {{-- $clientes viene del ClienteController --}}
                                <h4 class="mb-0">{{ $clientes->count() }}</h4>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Puedes añadir más tarjetas de estadísticas aquí --}}
        </div>

        {{-- Tarjeta de Búsqueda y Botón de Nuevo Cliente (de clientes.php [cite: 374-385]) --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Clientes</h5>
            </div>
            <div class="card-body">
                {{-- El formulario ahora usa la ruta 'clientes.index' (GET) --}}
                <form method="GET" action="{{ route('clientes.index') }}" id="formBuscarCliente">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar_nombre"
                                    placeholder="Buscar por nombre, apellido o DNI..."
                                    value="{{ request('buscar_nombre') }}" autocomplete="off">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>
                                @if(request('buscar_nombre'))
                                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#nuevoClienteModal">
                                <i class="fas fa-plus-circle me-1"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Resultados (Tabla de Clientes) (de clientes.php [cite: 387-417]) --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Clientes</h5>
                @if(auth()->user()->isAdmin() || auth()->user()->puede_exportar_clientes)
                    {{-- MVP_POSTERIOR: BEGIN Exportacion Excel
<a href="{{ route('clientes.exportar', ['buscar_nombre' => request('buscar_nombre')]) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                    </a>
MVP_POSTERIOR: END Exportacion Excel --}}
                @endif
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
                                    <th>Cliente</th>
                                    <th>Contacto</th>
                                    <th>DNI</th>
                                    <th>Dirección</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Bucle de Blade (reemplaza tu 'while ($row = ...)') --}}
                                @forelse ($clientes as $cliente)
                                    <tr>
                                        <td>
                                            <strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong>
                                        </td>
                                        <td>
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            {{ $cliente->celular }}
                                        </td>
                                        <td>
                                            {{ $cliente->dni ?? 'No registrado' }}
                                        </td>
                                        <td>
                                            {{ $cliente->direccion ?? 'No registrada' }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                {{-- Estos botones son detectados por clientes.js --}}
                                                <button class="btn btn-outline-primary btn-edit-cliente"
                                                    data-id="{{ $cliente->id_cliente }}" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                {{-- Corregido: Usa la ruta 'mascotas.index' (si existe) o 'historia.show' --}}
                                                {{-- Por ahora, lo enlazo a la lista de mascotas filtrada --}}
                                                <a href="{{ route('mascotas.index', ['cliente' => $cliente->id_cliente]) }}"
                                                    class="btn btn-outline-info" title="Ver Mascotas">
                                                    <i class="fas fa-paw"></i>
                                                </a>
                                                <button class="btn btn-outline-danger btn-delete-cliente"
                                                    data-id="{{ $cliente->id_cliente }}"
                                                    data-nombre="{{ $cliente->nombre }} {{ $cliente->apellido }}"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                            <h4>No hay clientes registrados</h4>
                                            @if(request('buscar_nombre'))
                                                <p class="text-muted">No se encontraron clientes que coincidan con la búsqueda.</p>
                                            @else
                                                <p class="text-muted">Comienza registrando tu primer cliente.</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $clientes->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL PARA NUEVO CLIENTE (de clientes.php [cite: 374-385]) --}}
        <div class="modal fade" id="nuevoClienteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Registrar Nuevo Cliente</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        {{-- Apunta a la ruta 'clientes.store' (ClienteController@store) --}}
                        <form id="formNuevoCliente" action="{{ route('clientes.store') }}" method="POST">
                            @csrf {{-- Token de seguridad OBLIGATORIO en Laravel --}}
                            <div class="row g-3">
                                {{-- MVP_POSTERIOR: BEGIN Nota de consulta RENIEC
                                <div class="col-12">
                                    <div class="alert alert-info mb-3" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Nota:</strong> Ingresa el DNI y haz clic en <i class="fas fa-search"></i>
                                        para autocompletar el nombre y apellido desde RENIEC.
                                    </div>
                                </div>
                                MVP_POSTERIOR: END Nota de consulta RENIEC --}}
                                <div class="col-12">
                                    <div class="alert alert-info mb-3" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Ingresa manualmente los datos del propietario. El DNI es opcional.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="dni" class="form-label">DNI</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dni" name="dni"
                                            placeholder="Ingresa 8 dígitos" maxlength="8" readonly autocomplete="off">
                                        {{-- MVP_POSTERIOR: BEGIN Consulta externa RENIEC
<button class="btn btn-primary" type="button" id="btnBuscarReniec"
                                            title="Buscar en RENIEC">
                                            <i class="fas fa-search"></i>
                                        </button>
MVP_POSTERIOR: END Consulta externa RENIEC --}}
                                    </div>
                                    <small class="text-muted d-block mt-1">{{-- MVP_POSTERIOR: BEGIN Ayuda de RENIEC
8 dígitos (opcional) - Consultar RENIEC
MVP_POSTERIOR: END Ayuda de RENIEC --}}8 dígitos (opcional)</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="celular" class="form-label">Celular <span
                                            class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="celular" name="celular"
                                        placeholder="987654321" required pattern="[0-9]{9}" maxlength="9" minlength="9"
                                        inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" autocomplete="off">
                                    <small class="text-muted">9 dígitos numéricos</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-light" id="nombre" name="nombre"
                                        placeholder="Haz clic para escribir" required readonly autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label">Apellido <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-light" id="apellido" name="apellido"
                                        placeholder="Haz clic para escribir" required readonly autocomplete="off">
                                </div>
                                <div class="col-12">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="direccion" name="direccion" rows="2"
                                        placeholder="Opcional"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btnGuardarCliente">Guardar
                            Cliente</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL PARA EDITAR CLIENTE (Contenedor vacío) --}}
        <div class="modal fade" id="editarClienteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" id="contenidoModalEditar">
                    {{-- El contenido se carga aquí vía AJAX --}}
                </div>
            </div>
        </div>
@endsection

    {{-- Estilos personalizados para el formulario de clientes --}}
    @push('styles')
        <style>
            /* Botón de búsqueda RENIEC - Ancho fijo e inamovible */
            #btnBuscarReniec {
                min-width: 45px;
                width: 45px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-left: none;
            }

            /* Asegurar que el input-group no cambie de tamaño */
            #dni {
                border-right: none;
            }

            #dni:focus {
                box-shadow: none;
                border-color: #86b7fe;
            }

            #btnBuscarReniec:hover {
                background-color: #0d6efd;
                color: white;
            }

            /* Campos readonly con fondo claro y clickeables */
            .form-control.bg-light:read-only {
                background-color: #e9ecef !important;
                cursor: pointer;
            }

            .form-control.bg-light:read-only:hover {
                background-color: #dee2e6 !important;
            }

            /* Campo DNI clickeable */
            #dni:read-only {
                background-color: white;
                cursor: pointer;
            }

            #dni:read-only:hover {
                background-color: #f8f9fa;
            }

            /* Alerta informativa */
            .alert-info {
                border-left: 4px solid #0dcaf0;
            }
        </style>
    @endpush

    {{-- Empuja el JS específico de clientes al stack 'scripts' de app.blade.php --}}
    @push('scripts')
        <script src="{{ asset('js/clientes.js') }}"></script>
    @endpush
