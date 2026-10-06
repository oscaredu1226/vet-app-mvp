@extends('layouts.app') {{-- Asumiendo layout principal --}}

@section('content')
    <div class="container-fluid px-4">
        <div class="ventas">
            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Gestión de Ventas</h2>
            </div>

            {{-- Tarjetas de Resumen [cite: 1507-1512] --}}
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">Ventas Hoy <h4 class="mb-0">{{ $stats['ventas_hoy'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario de Nueva Venta [cite: 1512-1529] --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cart-plus me-2"></i>Nueva Venta</h5>
                </div>
                <div class="card-body">
                    {{-- Formulario de búsqueda de items --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">1. Buscar Mascota <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="buscar_mascota"
                                    placeholder="Nombre de la mascota..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="btnLimpiarMascota"
                                    style="display: none;"><i class="fas fa-times"></i></button>
                            </div>
                            <div id="resultados_mascotas" class="list-group mt-2 position-absolute"
                                style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                            <input type="hidden" id="id_mascota">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">2. Buscar Producto/Servicio <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="buscar_producto"
                                placeholder="Nombre del producto..." autocomplete="off">
                            <div id="resultados_productos" class="list-group mt-2 position-absolute"
                                style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidad_item" value="1" min="1">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" type="button" id="btnAgregarItem">
                                <i class="fas fa-plus"></i> Añadir
                            </button>
                        </div>
                    </div>

                    {{-- Info Mascota --}}
                    <div id="info_mascota" class="alert alert-info mb-4" style="display: none;"></div>

                    {{-- Carrito de Compras --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto/Servicio</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="carrito_body">
                                <tr id="carrito_vacio">
                                    <td colspan="6" class="text-center text-muted py-4">No hay productos en el carrito</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th class="text-center" id="total_carrito">S/ 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Sección de Pago --}}
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">3. Cantidad a Pagar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="cantidad_pagar" placeholder="0.00" min="0"
                                step="0.01">
                            <small class="text-muted">Restante: <span id="monto_restante">S/ 0.00</span></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Medio de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="medio_pago">
                                <option value="">--Seleccione--</option>
                                <option value="Efectivo" data-color="green">Efectivo</option>
                                <option value="Yape" data-color="purple">Yape</option>
                                <option value="Tarjeta" data-color="primary">Tarjeta</option>
                                <option value="Transferencia" data-color="gray">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-danger" id="btnCancelar" style="display: none;">
                                <i class="fas fa-times me-1"></i> Cancelar Venta
                            </button>
                            <button type="button" class="btn btn-success" id="btnRegistrarVenta" disabled>
                                <i class="fas fa-check me-1"></i> Registrar Pago
                            </button>
                        </div>
                    </div>

                    {{-- Historial de Pagos (para pagos mixtos) --}}
                    <div id="historial_pagos" style="display: none;">
                        <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i>Pagos Realizados</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Medio de Pago</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="pagos_realizados">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Lista de Todas las Ventas (Historial) [cite: 1529-1559] --}}
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Ventas</h4>
                    <div class="d-flex gap-2 align-items-center">
                        <form method="GET" action="{{ route('ventas.index') }}" class="d-flex gap-2 flex-grow-1">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" placeholder="Buscar..."
                                    value="{{ request('buscar') }}">
                                <input type="text" class="form-control fecha-input" name="filtro_fecha"
                                    value="{{ request('filtro_fecha') ? \Carbon\Carbon::parse(request('filtro_fecha'))->format('d-m-Y') : '' }}" 
                                    style="max-width: 150px;"
                                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                    maxlength="10">

                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search me-1"></i> Buscar
                                </button>

                                @if(request('buscar') || request('filtro_fecha'))
                                    <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                        @if(auth()->user()->isAdmin() || auth()->user()->puede_exportar_ventas)
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportarVentas">
                                <i class="fas fa-file-excel me-1"></i>Exportar a Excel
                            </button>
                        @endif
                    </div>
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
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Mascota / Propietario</th>
                                    <th class="text-center">Subtotal</th>
                                    <th class="text-center">Medio de Pago</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ventas as $venta)
                                    <tr class="align-middle">
                                        <td>
                                            <strong>{{ $venta->mascota->nombre ?? 'N/A' }}</strong>
                                            <br><small class="text-muted">{{ $venta->mascota->cliente->nombre ?? '' }}
                                                {{ $venta->mascota->cliente->apellido ?? '' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success fs-5">S/
                                                {{ number_format($venta->subtotal_real, 2) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-{{ $venta->medio_pago == 'Efectivo' ? 'green' : ($venta->medio_pago == 'Tarjeta' ? 'primary' : ($venta->medio_pago == 'Yape' ? 'purple' : 'gray')) }} fs-6">
                                                {{ $venta->medio_pago }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-sm"
                                                onclick="verDetalleVenta({{ $venta->id_venta }})" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info btn-sm"
                                                onclick="imprimirTicket({{ $venta->id_venta }})" title="Imprimir ticket">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay ventas registradas que coincidan con la búsqueda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $ventas->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ver Detalle de Venta --}}
    <div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-labelledby="modalDetalleVentaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleVentaLabel">
                        <i class="fas fa-receipt me-2"></i>Detalle de Venta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="contenidoDetalleVenta">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnImprimirModal">
                        <i class="fas fa-print me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Exportar Ventas --}}
    <div class="modal fade" id="modalExportarVentas" tabindex="-1" aria-labelledby="modalExportarVentasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalExportarVentasLabel">
                        <i class="fas fa-file-excel me-2"></i>Exportar Ventas a Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formExportarVentas" action="{{ route('ventas.exportar') }}" method="GET">
                    <div class="modal-body">
                        <p class="text-muted mb-3">Seleccione el rango de fechas para exportar las ventas:</p>
                        
                        <div class="mb-3">
                            <label for="fecha_inicio_export" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha de Inicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_inicio_export" 
                                   name="fecha_inicio" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_fin_export" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i>Fecha Final <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="fecha_fin_export" 
                                   name="fecha_fin" 
                                   required>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Se exportarán todas las ventas registradas en el rango de fechas seleccionado.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Exportar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Estilos personalizados (de ventas.php) --}}
    @push('styles')
        <style>
            .bg-purple {
                background-color: #6f42c1 !important;
                color: white !important;
            }

            .bg-green {
                background-color: #28a745 !important;
                color: white !important;
            }

            .bg-light-blue {
                background-color: #17a2b8 !important;
                color: white !important;
            }

            .bg-gray {
                background-color: #6c757d !important;
                color: white !important;
            }

            #resultados_mascotas,
            #resultados_productos {
                position: absolute;
                z-index: 1000;
                width: calc(100% - 1.5rem);
                /* Ajustar al ancho del input group */
            }

            .ticket-modal {
                font-family: 'Courier New', Courier, monospace;
                font-size: 14px;
                max-width: 400px;
                margin: 0 auto;
                padding: 20px;
                border: 2px dashed #000;
            }

            .ticket-modal .ticket-header,
            .ticket-modal .ticket-footer {
                text-align: center;
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px dashed #000;
            }

            .ticket-modal .ticket-footer {
                border-top: 2px dashed #000;
                border-bottom: none;
                padding-top: 12px;
                margin-top: 12px;
            }

            .ticket-modal .cliente-info {
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px dashed #000;
            }

            .ticket-modal .item-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 6px;
                font-size: 13px;
            }

            .ticket-modal .totales {
                margin-top: 12px;
                text-align: right;
            }

            .ticket-modal h4 {
                font-size: 17px;
                margin: 5px 0;
                font-weight: bold;
            }

            .ticket-modal p {
                margin: 4px 0;
                line-height: 1.4;
            }
        </style>
    @endpush

    {{-- JS específico para Ventas (de ventas.php) --}}
    @push('scripts')
        <script>
            // Manejar envío del formulario de exportación y cerrar modal después
            $('#formExportarVentas').on('submit', function() {
                setTimeout(function() {
                    const modalElement = document.getElementById('modalExportarVentas');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }, 500);
            });
        </script>
        <script src="{{ asset('js/ventas.js') }}"></script>
    @endpush

@endsection