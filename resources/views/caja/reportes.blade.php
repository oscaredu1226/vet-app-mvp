@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-file-excel me-2"></i>Reportes de Caja</h2>
            <a href="{{ route('caja.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a Caja
            </a>
        </div>

        {{-- Información --}}
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Los reportes se generan automáticamente al cerrar la caja. Se eliminan después de 1 mes y medio.
        </div>

        {{-- Lista de Reportes --}}
        @if(count($reportes) > 0)
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Reportes Guardados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="45%">Nombre del Archivo</th>
                                <th width="25%">Fecha de Generación</th>
                                <th width="15%">Tamaño</th>
                                <th width="10%" class="text-center">Descargar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportes as $index => $reporte)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="fas fa-file-excel text-success"></i>
                                    {{ $reporte['nombre'] }}
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt"></i>
                                    {{ $reporte['fecha']->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    {{ number_format($reporte['tamaño'] / 1024, 2) }} KB
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('caja.descargarReporte', ['filename' => $reporte['nombre']]) }}" 
                                       class="btn btn-success btn-sm"
                                       title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-success mt-3 mb-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Total de reportes:</strong> {{ count($reportes) }}
                </div>
            </div>
        </div>
        @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No hay reportes disponibles</h4>
                <p class="text-muted">Los reportes se generan automáticamente al cerrar la caja.</p>
                <a href="{{ route('caja.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-cash-register me-2"></i>Ir a Caja
                </a>
            </div>
        </div>
        @endif

    </div>
@endsection
