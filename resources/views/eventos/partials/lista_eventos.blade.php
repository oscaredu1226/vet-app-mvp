{{-- Este parcial se recarga con AJAX --}}

{{-- Productos próximos a vencer --}}
{{-- MVP_POSTERIOR: Productos próximos a vencer
@if(isset($productosProximosAVencer) && $productosProximosAVencer->count() > 0)
<div class="alert alert-warning mb-4">
    <h6 class="alert-heading mb-3">
        <i class="fas fa-exclamation-triangle me-2"></i>Productos Próximos a Vencer
    </h6>
    <div class="list-group">
        @foreach($productosProximosAVencer as $producto)
            @php
                $diasRestantes = \Carbon\Carbon::today()->diffInDays($producto->fecha_vencimiento);
                $badgeClass = $diasRestantes <= 7 ? 'bg-danger' : ($diasRestantes <= 15 ? 'bg-warning' : 'bg-info');
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                <div>
                    <strong>{{ $producto->nombre }}</strong>
                    <small class="text-muted d-block">Vence: {{ $producto->fecha_vencimiento->format('d/m/Y') }}</small>
                </div>
                <span class="badge {{ $badgeClass }}">
                    {{ $diasRestantes }} día{{ $diasRestantes != 1 ? 's' : '' }}
                </span>
            </div>
        @endforeach
    </div>
</div>
@endif
--}}

{{-- Lista de eventos --}}
@forelse ($eventos_list as $evento)
<div class="card evento-card {{ $evento->estado == 'completado' ? 'evento-completado' : '' }} mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="card-title mb-2">
                    {{ $evento->titulo }}
                    @if ($evento->estado == 'completado')
                        <i class="fas fa-check-circle text-success ms-2"></i>
                    @endif
                </h6>
                <p class="card-text text-muted mb-2">
                    <i class="fas fa-user me-2"></i>
                    {{ $evento->mascota->nombre ?? 'N/A' }} ({{ $evento->mascota->cliente->nombre ?? 'N/A' }})
                </p>
                <p class="card-text text-muted mb-2"><i class="fas fa-clock me-2"></i>{{ $evento->hora ? substr($evento->hora, 0, 5) : 'Hora por confirmar' }}</p>
                @if ($evento->descripcion)
                    <p class="card-text">{{ $evento->descripcion }}</p>
                @endif
            </div>
            <div class="d-flex flex-column gap-1 ms-3">
                @if ($evento->estado == 'pendiente')
                <button class="btn btn-success btn-accion btn-completar" data-id="{{ $evento->id_evento }}" title="Completar">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-warning btn-accion btn-editar-evento" data-id="{{ $evento->id_evento }}" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-accion btn-eliminar-evento" data-id="{{ $evento->id_evento }}" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
                @else
                <span class="badge bg-success">Completado</span>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-5">
    <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
    <p class="text-muted mt-3">No hay eventos para esta fecha</p>
</div>
@endforelse