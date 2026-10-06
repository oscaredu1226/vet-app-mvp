<div class="modal-header bg-info text-white">
    <h5 class="modal-title">
        <i class="fas fa-info-circle me-2"></i>Detalles del Turno #{{ $grooming->numero_turno }}
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-muted mb-3"><i class="fas fa-calendar-alt me-2"></i>Información del Turno</h6>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Código de Sistema:</th>
                    <td>{{ $grooming->id_grooming }}</td>
                </tr>
                <tr>
                    <th>Número de Turno:</th>
                    <td>{{ $grooming->numero_turno }}</td>
                </tr>
                <tr>
                    <th>Fecha:</th>
                    <td>{{ \Carbon\Carbon::parse($grooming->fecha)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Hora de Entrada:</th>
                    <td>{{ \Carbon\Carbon::parse($grooming->hora_entrada)->format('h:i A') }}</td>
                </tr>
                <tr>
                    <th>Hora de Salida:</th>
                    <td>{{ $grooming->hora_salida ? \Carbon\Carbon::parse($grooming->hora_salida)->format('h:i A') : 'Pendiente' }}</td>
                </tr>
                <tr>
                    <th>Estado:</th>
                    <td>
                        @if($grooming->estado == 'PENDIENTE')
                            <span class="badge bg-warning text-dark">PENDIENTE</span>
                        @elseif($grooming->estado == 'EN_PROCESO')
                            <span class="badge bg-info">EN PROCESO</span>
                        @elseif($grooming->estado == 'COMPLETADO')
                            <span class="badge bg-success">COMPLETADO</span>
                        @elseif($grooming->estado == 'CANCELADO')
                            <span class="badge bg-danger">CANCELADO</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3"><i class="fas fa-user me-2"></i>Información del Cliente y Mascota</h6>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Cliente:</th>
                    <td>{{ $grooming->cliente->nombre }} {{ $grooming->cliente->apellido }}</td>
                </tr>
                <tr>
                    <th>Celular:</th>
                    <td>{{ $grooming->cliente->celular }}</td>
                </tr>
                <tr>
                    <th>Mascota:</th>
                    <td>{{ $grooming->mascota->nombre }}</td>
                </tr>
                <tr>
                    <th>Raza:</th>
                    <td>{{ $grooming->mascota->raza ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Especie:</th>
                    <td>{{ $grooming->mascota->especie ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Edad:</th>
                    <td>{{ $grooming->mascota->edad ? $grooming->mascota->edad . ' años' : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-muted mb-3"><i class="fas fa-cut me-2"></i>Servicio</h6>
            <div class="alert alert-secondary">
                <strong>{{ $grooming->servicio->nombre }}</strong>
                <span class="float-end">S/ {{ number_format($grooming->servicio->precio, 2) }}</span>
            </div>
        </div>
    </div>

    @if($grooming->observaciones)
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-muted mb-3"><i class="fas fa-sticky-note me-2"></i>Observaciones</h6>
                <div class="alert alert-info">
                    {{ $grooming->observaciones }}
                </div>
            </div>
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> Cerrar
    </button>
</div>
