@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            @include('historia.partials.info_mascota', ['mascota' => $mascota])
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Cirugías
                    </h6>
                </div>
                <div class="card-body">
                    @if ($historial->count() > 0)
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Peso</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historial as $registro)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $registro->peso_pre ? $registro->peso_pre . ' kg' : '-' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($registro->tipo_cirugia, 30) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No hay registros de cirugías.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            @include('cirugias.partials.edit_modal_content', ['cirugia' => $cirugia, 'mascota' => $mascota])
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/cirugia.js') }}"></script>

<style>
    .card {
        border: 1px solid #dee2e6;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .badge {
        font-size: 0.875rem;
    }
</style>
@endsection
