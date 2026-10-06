@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            @include('historia.partials.info_mascota', ['mascota' => $mascota])
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Historial de Vacunas
                    </h6>
                </div>
                <div class="card-body">
                    @if ($historial_vacunas->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Peso</th>
                                        <th>Vacuna</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historial_vacunas as $registro)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $registro->peso ? $registro->peso . ' kg' : '-' }}</td>
                                        <td>{{ $registro->vacuna_aplicada }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No hay registros de vacunas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Vacuna
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formVacuna" 
                          action="{{ route('vacunas.update', ['mascota' => $mascota->id_mascota, 'id' => $vacuna->id_vacuna]) }}" 
                          method="POST"
                          data-redirect-url="{{ route('historia.show', $mascota->id_mascota) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Fecha y Hora de Atención <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                    class="form-control" 
                                    name="fecha" 
                                    value="{{ \Carbon\Carbon::parse($vacuna->fecha)->format('Y-m-d\TH:i') }}" 
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="anamnesis_vacuna" class="form-label fw-bold">Anamnesis</label>
                            <textarea class="form-control" id="anamnesis_vacuna" name="anamnesis" rows="3" placeholder="(Opcional)">{{ $vacuna->anamnesis }}</textarea>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">Constantes Fisiológicas</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Peso (kg)</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-balance-scale text-muted"></i></span>
                                            <input type="number" step="0.01" class="form-control" name="peso" value="{{ $vacuna->peso }}" placeholder="0.00">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Temperatura (°C) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-thermometer-half"></i></span>
                                            <input type="number" step="0.1" class="form-control" name="temperatura" value="{{ $vacuna->temperatura }}" placeholder="38.5" required>
                                            <span class="input-group-text">°C</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-info bg-opacity-25">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-syringe me-2"></i>Vacuna Aplicada <span class="text-danger">*</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscar_vacuna" placeholder="Buscar o agregar vacuna..." autocomplete="off">
                                    <button type="button" class="btn btn-outline-primary d-md-none" id="btn_agregar_vacuna" 
                                            style="display:none;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="resultados_vacuna" class="dropdown-menu w-100"></div>
                                
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Vacuna</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaVacunas">
                                        <tr>
                                            <td>{{ $vacuna->vacuna_aplicada }}</td>
                                            <td><button type="button" class="btn btn-sm btn-danger" onclick="limpiarVacuna()">X</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <input type="hidden" id="vacuna_aplicada" name="vacuna_aplicada" value="{{ $vacuna->vacuna_aplicada }}">
                                <input type="hidden" id="laboratorio" name="laboratorio" value="{{ $vacuna->laboratorio }}">
                                <input type="hidden" id="lote" name="lote" value="{{ $vacuna->lote }}">
                                <input type="hidden" id="vencimiento" name="vencimiento" value="{{ $vacuna->vencimiento }}">
                                <input type="hidden" id="via_administracion" name="via_administracion" value="{{ $vacuna->via_administracion }}">
                                <input type="hidden" id="dosis" name="dosis" value="{{ $vacuna->dosis }}">
                            </div>
                        </div>
                        
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2"></i>Próxima Vacuna (Opcional)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha Próxima Dosis</label>
                                        <input type="text" class="form-control fecha-input" name="proxima_dosis" 
                                            value="{{ $vacuna->proxima_dosis ? \Carbon\Carbon::parse($vacuna->proxima_dosis)->format('d-m-Y') : '' }}"
                                            inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                            maxlength="10">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo de vacuna siguiente</label>
                                        <input type="text" class="form-control" name="tipo_proxima_vacuna" value="{{ $vacuna->tipo_proxima_vacuna }}" placeholder="Ej: Refuerzo anual">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones_vacuna" class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="observaciones_vacuna" name="observaciones" rows="3" placeholder="Reacciones, recomendaciones...">{{ $vacuna->observaciones }}</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('historia.show', $mascota->id_mascota) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-save me-2"></i>Actualizar Vacuna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function limpiarVacuna() {
    document.getElementById('tablaVacunas').innerHTML = '';
    document.getElementById('vacuna_aplicada').value = '';
    document.getElementById('laboratorio').value = '';
    document.getElementById('lote').value = '';
    document.getElementById('vencimiento').value = '';
    document.getElementById('via_administracion').value = '';
    document.getElementById('dosis').value = '';
}
</script>

@push('scripts')
<script src="{{ asset('js/vacuna.js') }}"></script>
@endpush
@endsection
