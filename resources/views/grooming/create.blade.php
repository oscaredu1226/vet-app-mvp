@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cut me-2"></i>Crear Nuevo Servicio de Grooming
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form id="formGrooming" action="{{ route('grooming.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            {{-- Seleccionar Cliente --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i>Buscar Cliente <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="buscar_cliente" 
                                       placeholder="Escriba mínimo 3 letras del nombre del cliente..."
                                       autocomplete="off">
                                <input type="hidden" id="id_cliente" name="id_cliente" required>
                                <div id="resultados_clientes" class="list-group mt-2" style="max-height: 300px; overflow-y: auto;"></div>
                                <div id="cliente_seleccionado" class="mt-2"></div>
                            </div>

                            {{-- Seleccionar Mascota --}}
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-paw me-2"></i>Mascota <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="id_mascota" name="id_mascota" required disabled>
                                    <option value="">Primero seleccione un cliente</option>
                                </select>
                                <small class="text-muted">Las mascotas se cargarán al seleccionar un cliente</small>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Fecha --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar me-2"></i>Fecha <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha"
                                       name="fecha" 
                                       value="{{ now()->format('Y-m-d') }}" 
                                       required>
                            </div>

                            {{-- Hora de Entrada --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clock me-2"></i>Hora de Entrada <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       class="form-control" 
                                       id="hora_entrada"
                                       name="hora_entrada" 
                                       value="{{ now()->format('H:i') }}" 
                                       required>
                            </div>

                            {{-- Servicio --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-cut me-2"></i>Servicio <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="buscar_servicio" 
                                       placeholder="Escriba mínimo 3 letras del servicio..."
                                       autocomplete="off">
                                <input type="hidden" id="id_servicio" name="id_servicio" required>
                                <div id="resultados_servicios" class="list-group mt-2" style="max-height: 300px; overflow-y: auto;"></div>
                                <div id="servicio_seleccionado" class="mt-2"></div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-sticky-note me-2"></i>Observaciones
                                </label>
                                <textarea class="form-control" 
                                          id="observaciones"
                                          name="observaciones" 
                                          rows="3" 
                                          placeholder="Observaciones adicionales sobre el servicio..."></textarea>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('grooming.turnos-hoy') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" id="btnGuardar">
                                <i class="fas fa-save me-1"></i> Guardar Turno
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .list-group-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    .cliente-badge {
        display: inline-block;
        padding: 8px 15px;
        background-color: #198754;
        color: white;
        border-radius: 5px;
        font-size: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let clienteSeleccionado = null;

    // Buscador de clientes (mínimo 3 letras)
    $('#buscar_cliente').on('keyup', function() {
        const buscar = $(this).val().trim();
        const $resultados = $('#resultados_clientes');
        
        if (buscar.length < 3) {
            $resultados.empty();
            return;
        }

        // Buscar clientes
        const clientes = @json($clientes);
        const resultados = clientes.filter(cliente => {
            const nombreCompleto = `${cliente.nombre} ${cliente.apellido}`.toLowerCase();
            const dni = cliente.dni || '';
            return nombreCompleto.includes(buscar.toLowerCase()) || dni.includes(buscar);
        });

        // Mostrar resultados
        if (resultados.length === 0) {
            $resultados.html('<div class="list-group-item text-muted">No se encontraron clientes</div>');
            return;
        }

        let html = '';
        resultados.forEach(cliente => {
            html += `
                <a href="#" class="list-group-item list-group-item-action seleccionar-cliente" 
                   data-id="${cliente.id_cliente}"
                   data-nombre="${cliente.nombre} ${cliente.apellido}"
                   data-dni="${cliente.dni || 'N/A'}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${cliente.nombre} ${cliente.apellido}</strong>
                            <br>
                            <small class="text-muted">DNI: ${cliente.dni || 'N/A'} | Celular: ${cliente.celular}</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </a>
            `;
        });
        $resultados.html(html);
    });

    // Buscador de servicios (mínimo 3 letras)
    $('#buscar_servicio').on('keyup', function() {
        const buscar = $(this).val().trim();
        const $resultados = $('#resultados_servicios');
        
        if (buscar.length < 3) {
            $resultados.empty();
            return;
        }

        // Buscar servicios
        const servicios = @json($servicios);
        const resultados = servicios.filter(servicio => {
            const nombre = servicio.nombre.toLowerCase();
            return nombre.includes(buscar.toLowerCase());
        });

        // Mostrar resultados
        if (resultados.length === 0) {
            $resultados.html('<div class="list-group-item text-muted">No se encontraron servicios</div>');
            return;
        }

        let html = '';
        resultados.forEach(servicio => {
            html += `
                <a href="#" class="list-group-item list-group-item-action seleccionar-servicio" 
                   data-id="${servicio.id_servicio}"
                   data-nombre="${servicio.nombre}"
                   data-precio="${servicio.precio}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${servicio.nombre}</strong>
                            <br>
                            <small class="text-muted">Precio: S/ ${parseFloat(servicio.precio).toFixed(2)}</small>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </a>
            `;
        });
        $resultados.html(html);
    });

    // Seleccionar cliente
    $(document).on('click', '.seleccionar-cliente', function(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const dni = $(this).data('dni');
        
        $('#id_cliente').val(id);
        $('#buscar_cliente').val('');
        $('#resultados_clientes').empty();
        $('#cliente_seleccionado').html(`
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-check me-2"></i><strong>Cliente:</strong> ${nombre} (DNI: ${dni})</span>
                <button type="button" class="btn btn-sm btn-danger btn-cambiar-cliente">
                    <i class="fas fa-times"></i> Cambiar
                </button>
            </div>
        `);
        
        // Cargar mascotas del cliente
        cargarMascotas(id);
    });

    // Seleccionar servicio
    $(document).on('click', '.seleccionar-servicio', function(e) {
        e.preventDefault();
        
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const precio = $(this).data('precio');
        
        $('#id_servicio').val(id);
        $('#buscar_servicio').val('');
        $('#resultados_servicios').empty();
        $('#servicio_seleccionado').html(`
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span><i class="fas fa-check-circle me-2"></i><strong>Servicio:</strong> ${nombre} - S/ ${parseFloat(precio).toFixed(2)}</span>
                <button type="button" class="btn btn-sm btn-danger btn-cambiar-servicio">
                    <i class="fas fa-times"></i> Cambiar
                </button>
            </div>
        `);
    });

    // Cambiar cliente
    $(document).on('click', '.btn-cambiar-cliente', function() {
        $('#id_cliente').val('');
        $('#cliente_seleccionado').empty();
        $('#id_mascota').html('<option value="">Primero seleccione un cliente</option>').prop('disabled', true);
        $('#buscar_cliente').focus();
    });

    // Cambiar servicio
    $(document).on('click', '.btn-cambiar-servicio', function() {
        $('#id_servicio').val('');
        $('#servicio_seleccionado').empty();
        $('#buscar_servicio').focus();
    });

    // Función para cargar mascotas
    function cargarMascotas(clienteId) {
        const $mascotaSelect = $('#id_mascota');
        
        $mascotaSelect.html('<option value="">Cargando mascotas...</option>').prop('disabled', true);

        $.get(`{{ route('grooming.mascotas-by-cliente') }}`, { id_cliente: clienteId })
            .done(function(mascotas) {
                if (mascotas.length === 0) {
                    $mascotaSelect.html('<option value="">Este cliente no tiene mascotas registradas</option>');
                    Swal.fire('Aviso', 'Este cliente no tiene mascotas registradas', 'info');
                    return;
                }

                let options = '<option value="">Seleccione una mascota...</option>';
                mascotas.forEach(mascota => {
                    options += `<option value="${mascota.id_mascota}">${mascota.nombre} - ${mascota.raza || 'Sin raza'}</option>`;
                });
                
                $mascotaSelect.html(options).prop('disabled', false);
            })
            .fail(function() {
                $mascotaSelect.html('<option value="">Error al cargar mascotas</option>');
                Swal.fire('Error', 'No se pudieron cargar las mascotas del cliente', 'error');
            });
    }

    // Validar antes de enviar
    $('#formGrooming').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#id_cliente').val()) {
            Swal.fire('Error', 'Debe seleccionar un cliente', 'error');
            return;
        }

        if (!$('#id_mascota').val()) {
            Swal.fire('Error', 'Debe seleccionar una mascota', 'error');
            return;
        }

        if (!$('#id_servicio').val()) {
            Swal.fire('Error', 'Debe seleccionar un servicio', 'error');
            return;
        }
        
        const $btn = $('#btnGuardar');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route("grooming.turnos-hoy") }}';
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(btnText);
                const message = xhr.responseJSON?.message || 'Error al guardar el turno';
                Swal.fire('Error', message, 'error');
            }
        });
    });
});
</script>
@endpush
