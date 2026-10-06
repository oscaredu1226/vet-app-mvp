{{-- Modal Nuevo Evento --}}
<div class="modal fade" id="nuevoEventoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Nuevo Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formNuevoEvento">
                @csrf
                <div class="modal-body">
                    {{-- (Pega el HTML del form de 'nuevo_evento.php' [cite: 1253-1257]) --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="buscar_mascota_modal" class="form-label">Mascota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="buscar_mascota_modal" placeholder="Buscar mascota..." autocomplete="off">
                            <input type="hidden" id="id_mascota_modal" name="id_mascota" required>
                            <div id="resultados_mascota_modal" class="dropdown-menu w-100"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_evento" class="form-label">Fecha <span class="text-danger">*</span></label>
                            {{-- MVP_POSTERIOR: Entrada de fecha con máscara
<input type="text" class="form-control fecha-input" id="fecha_evento" name="fecha" required
                                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                maxlength="10">
--}}
<input type="date" class="form-control" id="fecha_evento" name="fecha" required>
<div class="form-label mt-2">Horario (opcional)</div>
@include('eventos.partials.hora', ['inputId' => 'fecha_evento_hora'])
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="titulo_evento" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo_evento" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_evento" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_evento" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Crear Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Editar Evento --}}
<div class="modal fade" id="editarEventoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarEvento">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id_evento" name="id_evento">
                <div class="modal-body">
                    {{-- (Pega el HTML del form de 'editar_crear_evento.php' [cite: 703-706]) --}}
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_buscar_mascota" class="form-label">Mascota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_buscar_mascota" placeholder="Buscar mascota..." autocomplete="off">
                            <input type="hidden" id="edit_id_mascota" name="id_mascota" required>
                            <div id="edit_resultados_mascota" class="dropdown-menu w-100"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_fecha_evento" class="form-label">Fecha <span class="text-danger">*</span></label>
                            {{-- MVP_POSTERIOR: Entrada de fecha con máscara
<input type="text" class="form-control fecha-input" id="edit_fecha_evento" name="fecha" required
                                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                maxlength="10">
--}}
<input type="date" class="form-control" id="edit_fecha_evento" name="fecha" required>
<div class="form-label mt-2">Horario (opcional)</div>
@include('eventos.partials.hora', ['inputId' => 'edit_fecha_evento_hora'])
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_titulo_evento" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_titulo_evento" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcion_evento" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_descripcion_evento" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Evento</button>
                </div>
            </form>
        </div>
    </div>
</div>
