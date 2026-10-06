@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 caja">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-cash-register me-2"></i>Reporte de Caja</h2>
        </div>

        {{-- Tarjeta de Total en Caja Manual [cite: 300-314] --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-3"><i class="fas fa-cash-register me-2 text-danger"></i>Inicio de Caja
                            (Efectivo)</h5>
                        <h3 class="mb-0">S/ {{ number_format($total_caja_manual, 2) }}</h3>
                        <small class="text-muted">Último valor establecido manualmente</small>

                        <form method="POST" action="{{ route('caja.updateTotal') }}" class="row g-2 mt-3">
                            @csrf
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="nuevo_total_caja" step="0.01" min="0"
                                    placeholder="Nuevo valor" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Actualizar</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-calculator me-2"></i>Cálculo Automático (Hoy)</h6>
                                <div class="row mb-2">
                                    <div class="col-6"><small>Efectivo:</small>
                                        <div class="fw-bold">S/
                                            {{ number_format($ingresos['Efectivo'] + $total_caja_manual, 2) }}</div>
                                    </div>
                                    <div class="col-6"><small>Egresos:</small>
                                        <div class="fw-bold">S/ {{ number_format($total_egresos, 2) }}</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Total calculado:</span>
                                    <h5 class="mb-0">S/ {{ number_format($total_caja_calculado, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros y Acciones [cite: 315-325] --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Reporte de Transacciones</h5>
                <div>
                    <button type="button" class="btn btn-danger" id="btnCerrarCaja">
                        <i class="fas fa-door-closed me-2"></i> Cerrar Caja
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Aquí puedes añadir los filtros de fecha (hoy, semana, mes) si lo deseas --}}
            </div>
        </div>

        {{-- Tabla de Ventas (Historial) --}}
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Registro de Ventas (Hoy)</h5>
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
                <p>Total de ventas: <strong>{{ $total_ventas_dia_count }}</strong> | Monto total: <strong>S/
                        {{ number_format($total_ventas_dia_sum, 2) }}</strong></p>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Mascota / Propietario</th>
                                <th>Producto/Servicio</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Medio de Pago</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historial_ventas as $venta)
                                <tr class="align-middle">
                                    <td>{{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $venta->mascota->nombre ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $venta->mascota->cliente->nombre ?? '' }}
                                            {{ $venta->mascota->cliente->apellido ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if(count($venta->items_nombres) > 0)
                                            {{ implode(', ', $venta->items_nombres) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-success">S/ {{ number_format($venta->subtotal, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($venta->medio_pago == 'Mixto' && $venta->pagos->count() > 0)
                                            @foreach($venta->pagos as $pago)
                                                <span
                                                    class="badge bg-{{ $pago->medio_pago == 'Efectivo' ? 'success' : ($pago->medio_pago == 'Tarjeta' ? 'primary' : ($pago->medio_pago == 'Yape' ? 'purple' : 'secondary')) }}">
                                                    {{ $pago->medio_pago }}: S/ {{ number_format($pago->monto, 2) }}
                                                </span>
                                                @if(!$loop->last)<br>@endif
                                            @endforeach
                                        @else
                                            <span
                                                class="badge bg-{{ $venta->medio_pago == 'Efectivo' ? 'success' : ($venta->medio_pago == 'Tarjeta' ? 'primary' : ($venta->medio_pago == 'Yape' ? 'purple' : 'secondary')) }}">
                                                {{ $venta->medio_pago }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info"
                                            onclick="verDetalleVenta({{ $venta->id_venta }})" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay ventas registradas hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Paginación --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $historial_ventas->links('vendor.pagination.custom') }}
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
    </div>
@endsection

@push('styles')
    <style>
        .ticket-modal {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-width: 350px;
            margin: 0 auto;
            padding: 15px;
            border: 1px dashed #000;
        }

        .ticket-modal .ticket-header,
        .ticket-modal .ticket-footer {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .ticket-modal .ticket-footer {
            border-top: 1px dashed #000;
            border-bottom: none;
            padding-top: 10px;
            margin-top: 10px;
        }

        .ticket-modal .cliente-info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .ticket-modal .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .ticket-modal .totales {
            margin-top: 10px;
            text-align: right;
        }

        .ticket-modal h4 {
            font-size: 16px;
            margin: 0;
        }

        .ticket-modal p {
            margin: 3px 0;
        }
    </style>
@endpush

@push('scripts')
    {{-- Carga el JS de caja y ventas --}}
    <script src="{{ asset('js/caja.js') }}"></script>
    <script src="{{ asset('js/ventas.js') }}"></script>
@endpush