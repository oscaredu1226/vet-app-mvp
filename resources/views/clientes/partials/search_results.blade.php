@forelse ($clientes as $cliente)
    <a href="#" class="list-group-item list-group-item-action seleccionar-propietario" 
       data-id="{{ $cliente->id_cliente }}"
       data-nombre="{{ $cliente->nombre }}"
       data-apellido="{{ $cliente->apellido }}"
       data-dni="{{ $cliente->dni ?? '' }}"
       data-telefono="{{ $cliente->telefono ?? '' }}"
       data-direccion="{{ $cliente->direccion ?? '' }}">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong>
                @if($cliente->dni)
                    <br><small class="text-muted">DNI: {{ $cliente->dni }}</small>
                @endif
            </div>
            @if($cliente->telefono)
                <small class="text-muted"><i class="fas fa-phone"></i> {{ $cliente->telefono }}</small>
            @endif
        </div>
    </a>
@empty
    <div class="list-group-item text-muted">No se encontraron propietarios</div>
@endforelse
