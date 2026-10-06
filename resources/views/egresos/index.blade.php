@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 egresos">
    
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Gestión de Egresos</h2>
    </div>

    <div class="row">
        {{-- Formulario de Creación --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Registrar Egreso</h5>
                </div>
                <div class="card-body">
                    <form id="formNuevoEgreso" action="{{ route('egresos.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Motivo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                        </div>
                        <div class="mb-3">
                            <label for="monto" class="form-label">Monto (S/.) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                            <input type="text" class="form-control fecha-input" id="fecha" name="fecha" 
                                value="{{ today()->format('d-m-Y') }}" required
                                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                maxlength="10">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrar Egreso</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lista de Egresos --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Egresos Registrados</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Motivo</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($egresos as $egreso)
                                <tr>
                                    <td>{{ $egreso->descripcion }}</td>
                                    <td>S/ {{ number_format($egreso->monto, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($egreso->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm btn-delete-egreso" data-id="{{ $egreso->id_egreso }}" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">No hay egresos registrados.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Paginación --}}
                    <div class="d-flex justify-content-center">
                        {{ $egresos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Crea 'public/js/egresos.js' con el JS de 'egresos.php' [cite: 868-877] --}}
<script src="{{ asset('js/egresos.js') }}"></script>
@endpush