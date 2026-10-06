@extends('layouts.app') {{-- Asumiendo que tienes un layout principal --}}

@section('content')
    <div class="container-fluid px-4 productos">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-boxes me-2"></i>Gestión de Productos</h2>
        </div>

        {{-- Tarjetas de Estadísticas --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <h6>TOTAL PRODUCTOS</h6>
                        <h2>{{ $stats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <h6>STOCK ALTO</h6>
                        <h2>{{ $stats['stock_alto'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <h6>STOCK BAJO</h6>
                        <h2>{{ $stats['stock_bajo'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <h6>SIN STOCK</h6>
                        <h2>{{ $stats['sin_stock'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta de Búsqueda y Filtros --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Productos</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('productos.index') }}" class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Buscar por nombre</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="buscar_producto" autocomplete="off"
                                placeholder="Buscar por nombre..." value="{{ request('buscar_producto') }}">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>

                            @if(request('buscar_producto'))
                                <a href="{{ route('productos.index', ['filtro' => request('filtro')]) }}"
                                    class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-filter me-1"></i>Filtros</label>
                        <select class="form-select" name="filtro" onchange="this.form.submit()">
                            <option value="">Todos los productos</option>
                            <option value="stock_alto" {{ request('filtro') == 'stock_alto' ? 'selected' : '' }}>Stock Alto
                            </option>
                            <option value="stock_minimo" {{ request('filtro') == 'stock_minimo' ? 'selected' : '' }}>Stock
                                Bajo</option>
                            <option value="sin_stock" {{ request('filtro') == 'sin_stock' ? 'selected' : '' }}>Sin Stock
                            </option>
                        </select>
                    </div>
                </form>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#nuevoProductoModal">
                        <i class="fas fa-plus-circle me-1"></i> Nuevo Producto
                    </button>
                </div>
            </div>
        </div>

        {{-- Tarjeta de Lista de Productos --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Productos</h5>
                @if(auth()->user()->isAdmin() || auth()->user()->puede_exportar_productos)
                    <a href="{{ route('productos.exportar', ['buscar_producto' => request('buscar_producto'), 'filtro' => request('filtro')]) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                    </a>
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
                                <th>Producto</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">FV</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productos as $producto)
                                <tr>
                                    <td>
                                        <strong>{{ $producto->nombre }}</strong>
                                        <small class="d-block text-muted">{{ $producto->tipo }}</small>
                                    </td>
                                    <td class="text-center">S/ {{ number_format($producto->precio, 2) }}</td>
                                    <td class="text-center">{{ $producto->stock }}</td>
                                    <td class="text-center">
                                        @if($producto->fecha_vencimiento)
                                            @php
                                                $diasRestantes = \Carbon\Carbon::today()->diffInDays($producto->fecha_vencimiento);
                                                $badgeClass = $diasRestantes <= 7 ? 'bg-danger' : ($diasRestantes <= 15 ? 'bg-warning text-dark' : 'bg-info');
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" title="{{ $diasRestantes }} días restantes">
                                                {{ $producto->fecha_vencimiento->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($producto->stock == 0)
                                            <span class="badge bg-danger">Agotado</span>
                                        @elseif ($producto->stock <= $producto->stock_minimo)
                                            <span class="badge bg-warning">Stock bajo</span>
                                        @else
                                            <span class="badge bg-success">Disponible</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary btn-edit"
                                                data-id="{{ $producto->id_producto }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger btn-delete"
                                                data-id="{{ $producto->id_producto }}" data-nombre="{{ $producto->nombre }}"
                                                title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                        <h4>No hay productos registrados</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="d-flex justify-content-center">
                    {{ $productos->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA NUEVO PRODUCTO (de productos.php) [cite: 1329-1353] --}}
    <div class="modal fade" id="nuevoProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoProducto">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nombre_producto" class="form-label fw-semibold">Nombre del producto <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre" required autocomplete="off">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="precio" class="form-label fw-semibold">Precio (S/.) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" autocomplete="off"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="precio_compra" class="form-label fw-semibold">Precio de compra (S/.)</label>
                                <input type="number" class="form-control" id="precio_compra" name="precio_compra" autocomplete="off"
                                    step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label fw-semibold">Stock inicial <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required autocomplete="off">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label fw-semibold">Tipo <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="" disabled selected>Seleccione un tipo...</option>
                                    <option value="clinica">Clínica</option>
                                    <option value="farmacia">Farmacia</option>
                                    <option value="petshop">PetShop</option>
                                    <option value="spa">Spa</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock_minimo" class="form-label fw-semibold">Stock mínimo <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="1" autocomplete="off"
                                    value="2" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_vencimiento" class="form-label fw-semibold">Fecha de Vencimiento (FV)</label>
                                <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" 
                                    autocomplete="off" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <small class="text-muted">Opcional. Si se vence pronto, aparecerá en el calendario.</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarProducto">Guardar Producto</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA EDITAR PRODUCTO (Cargará contenido AJAX) --}}
    <div class="modal fade" id="editarProductoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{-- El contenido se carga dinámicamente desde el controlador --}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Crea este archivo 'public/js/productos.js' con el JS de 'modules/productos.php' --}}
    <script src="{{ asset('js/productos.js') }}"></script>
@endpush