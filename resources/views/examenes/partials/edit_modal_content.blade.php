<div class="modal-header bg-warning text-dark">
    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Examen</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <form id="formEditarExamen" data-id="{{ $examen->id_examen_lab }}" action="{{ route('examenes.update', $examen->id_examen_lab) }}" method="POST">
        @csrf
        @method('PUT')
        
        {{-- (Pega aquí el HTML del formulario de 'modules/editar_examen.php' [cite: 711-730]) --}}
        {{-- Ejemplo de campos migrados: --}}

        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label fw-semibold">Mascota Asignada</label>
                <div class="card bg-light border-0">
                    <div class="card-body p-3">
                        <h6 class="mb-0 fw-bold">{{ $examen->mascota->nombre }}</h6>
                        <p class="mb-0 text-muted">{{ $examen->mascota->cliente->nombre }} {{ $examen->mascota->cliente->apellido }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label for="edit_laboratorio_search" class="form-label fw-semibold">Laboratorio <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_laboratorio_search" autocomplete="off" 
                    placeholder="Buscar o escribir laboratorio..." autocomplete="off" value="{{ $examen->laboratorio }}">
                <input type="hidden" id="edit_laboratorio" name="laboratorio" value="{{ $examen->laboratorio }}" required>
                <div id="edit_laboratorio_results" class="list-group mt-1"
                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000;">
                </div>
            </div>
            <div class="col-sm-6">
                <label for="edit_tipo_analisis_search" class="form-label fw-semibold">Tipo de Análisis <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_tipo_analisis_search" autocomplete="off"
                    placeholder="Buscar o escribir tipo de análisis..." autocomplete="off" value="{{ $examen->tipo_analisis }}">
                <input type="hidden" id="edit_tipo_analisis" name="tipo_analisis" value="{{ $examen->tipo_analisis }}" required>
                <div id="edit_tipo_analisis_results" class="list-group mt-1"
                    style="max-height: 200px; overflow-y: auto; position: absolute; z-index: 1000;">
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label for="edit_costo" class="form-label fw-semibold">Costo (S/) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" class="form-control" id="edit_costo" name="costo" value="{{ $examen->costo }}" required>
            </div>
            <div class="col-sm-6">
                <label for="edit_fecha_envio" class="form-label fw-semibold">Fecha de Envío <span class="text-danger">*</span></label>
                <input type="text" class="form-control fecha-input" id="edit_fecha_envio" name="fecha_envio" autocomplete="off" 
                    value="{{ $examen->fecha_envio->format('d-m-Y') }}" required
                    inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                    maxlength="10">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="edit_pagado" name="pagado" value="1" @if($examen->pagado) checked @endif>
                    <label class="form-check-label" for="edit_pagado">
                        <i class="fas fa-money-bill-wave me-1"></i>¿Pagado?
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="edit_subido_historia" name="subido_historia" value="1" @if($examen->subido_historia) checked @endif>
                    <label class="form-check-label" for="edit_subido_historia">
                        <i class="fas fa-upload me-1"></i>¿Subido a Historia?
                    </label>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="edit_lectura_realizada" name="lectura_realizada" value="1" @if($examen->lectura_realizada) checked @endif>
                    <label class="form-check-label" for="edit_lectura_realizada">
                        <i class="fas fa-check-circle me-1"></i>¿Lectura Realizada?
                    </label>
                </div>
            </div>
        </div>
        
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
    <button type="button" class="btn btn-warning" id="btnActualizarExamen">Actualizar Examen</button>
</div>