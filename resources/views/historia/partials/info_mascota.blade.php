{{-- Tarjeta de información de la mascota --}}
<div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
    <div class="card-body text-center">
        {{-- Ícono de mascota --}}
        <div class="mb-3">
            <i class="fas fa-paw fa-4x text-primary"></i>
        </div>
        
        {{-- Nombre --}}
        <h4 class="mb-2">{{ $mascota->nombre }}</h4>
        
        {{-- Badge de especie --}}
        <span class="badge bg-success mb-3">{{ strtoupper($mascota->especie) }}</span>
        
        {{-- Información detallada --}}
        <div class="text-start small">
            <p class="mb-1"><strong>Raza:</strong> {{ $mascota->raza }}</p>
            <p class="mb-1"><strong>Sexo:</strong> {{ $mascota->genero }}</p>
            <p class="mb-1"><strong>¿Esterilizado?:</strong> {{ $mascota->esterilizado ?? 'No' }}</p>
            <p class="mb-1"><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') }}</p>
            <p class="mb-1">
                <strong>Edad:</strong> {{ $mascota->edad_completa }}
            </p>
            <hr>
            <p class="mb-1">
                <i class="fab fa-whatsapp text-success me-1"></i>
                <strong>Propietario:</strong>
            </p>
            <p class="text-primary mb-0 small">{{ strtoupper($mascota->cliente->nombre ?? 'N/A') }} {{ strtoupper($mascota->cliente->apellido ?? '') }}</p>
            @if($mascota->cliente && $mascota->cliente->celular)
                <p class="mb-0 small text-muted">{{ $mascota->cliente->celular }}</p>
            @endif
        </div>
        
        {{-- Botón para volver a la historia --}}
        <div class="mt-3">
            <a href="{{ route('historia.show', $mascota->id_mascota) }}" class="btn btn-outline-primary btn-sm w-100">
                <i class="fas fa-arrow-left me-1"></i> Volver a Historia
            </a>
        </div>
    </div>
</div>
