@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Calendario y citas</h2>
            <p class="text-muted mb-0">Gestiona tus citas y recordatorios</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoEventoModal">
            <i class="fas fa-plus me-2"></i>Nueva cita
        </button>
    </div>

    <div class="row">
        {{-- Calendario --}}
        <div class="col-12 col-lg-5 mb-4">
            <div class="calendario-container card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-3">Calendario</h4>
                    <p class="text-center text-muted d-none d-md-block">Seleccione una fecha</p>
                    {{-- MVP_POSTERIOR: Entrada de fecha con máscara
<input type="text" class="form-control fecha-input mb-3" id="fechaCalendario" 
                        value="{{ \Carbon\Carbon::parse($fecha_seleccionada)->format('d-m-Y') }}"
                        inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                        maxlength="10">
--}}
<input type="date" class="form-control mb-3" id="fechaCalendario" value="{{ $fecha_seleccionada }}">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        {{-- Eventos del día --}}
        <div class="col-12 col-lg-7">
            <div class="eventos-container">
                <h5 class="mb-3">
                    Eventos del <span id="fechaSeleccionada">{{ \Carbon\Carbon::parse($fecha_seleccionada)->format('d/m/Y') }}</span>
                    <span class="badge bg-success ms-2" id="contadorEventosDia">{{ $eventos->count() }}</span>
                </h5>
                
                {{-- La lista de eventos se carga en un parcial --}}
                <div id="listaEventos">
                    @include('eventos.partials.lista_eventos', ['eventos_list' => $eventos])
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales (reemplaza nuevo_evento.php y editar_evento.php) --}}
@include('eventos.partials.modals')
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
    .fc-day.selected-date {
        background-color: rgba(40, 167, 69, 0.2) !important;
    }
    .fc-day-today.selected-date {
        background-color: rgba(40, 167, 69, 0.3) !important;
    }
    
    /* Responsive calendar */
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    
    /* Mobile adjustments */
    @media (max-width: 768px) {
        .fc .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.2rem;
        }
        
        .fc .fc-button {
            padding: 0.3rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .fc-daygrid-day-number {
            font-size: 0.875rem;
        }
    }
    
    @media (max-width: 576px) {
        .fc .fc-toolbar-title {
            font-size: 1rem;
        }
        
        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem;
            padding: 0.25rem;
        }
        
        .fc-daygrid-day-number {
            font-size: 0.75rem;
            padding: 0.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialDate: '{{ $fecha_seleccionada }}',
        initialView: window.innerWidth < 768 ? 'dayGridWeek' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: window.innerWidth < 576 ? '' : 'dayGridMonth,dayGridWeek'
        },
        height: 'auto',
        contentHeight: 'auto',
        aspectRatio: window.innerWidth < 768 ? 1 : 1.35,
        handleWindowResize: true,
        windowResize: function(view) {
            if (window.innerWidth < 768) {
                calendar.changeView('dayGridWeek');
            } else {
                calendar.changeView('dayGridMonth');
            }
        },
        dateClick: function(info) {
            // Remover la clase de todas las celdas
            document.querySelectorAll('.fc-day').forEach(el => el.classList.remove('selected-date'));
            // Agregar la clase a la celda seleccionada
            info.dayEl.classList.add('selected-date');
            
            const input = document.getElementById('fechaCalendario');
            if (input._flatpickr) input._flatpickr.setDate(info.dateStr, false);
            else input.value = info.dateStr;
            $(input).trigger('change');
        },
        /* Código original conservado:
        events: function(info, successCallback, failureCallback) {
            // Aquí puedes cargar eventos desde el servidor si lo deseas
            successCallback([]);
        } */
        events: { url: '{{ route("eventos.index") }}', extraParams: { feed: 1 } },
        eventClick: function(info) {
            const fecha = info.event.startStr.slice(0, 10);
            const input = document.getElementById('fechaCalendario');
            if (input._flatpickr) input._flatpickr.setDate(fecha, false);
            else input.value = fecha;
            $(input).trigger('change');
        }
    });
    calendar.render();
    $(document).on('citas:actualizadas', function() { calendar.refetchEvents(); });

    // Sincronizar el input de fecha con el calendario
    $('#fechaCalendario').on('change', function(e) {
        calendar.gotoDate(e.target.value);
        // La lista se carga en public/js/calendario.js.
        
        // Marcar el día seleccionado
        setTimeout(() => {
            document.querySelectorAll('.fc-day').forEach(el => el.classList.remove('selected-date'));
            const selectedCell = document.querySelector(`[data-date="${e.target.value}"]`);
            if (selectedCell) {
                selectedCell.classList.add('selected-date');
            }
        }, 100);
    });

// MVP_POSTERIOR |     function cargarEventos(fecha) {
// MVP_POSTERIOR |         const fechaObj = new Date(fecha + 'T00:00:00');
// MVP_POSTERIOR |         const fechaFormateada = fechaObj.toLocaleDateString('es-ES');
// MVP_POSTERIOR |         document.getElementById('fechaSeleccionada').textContent = fechaFormateada;
// MVP_POSTERIOR |         
// MVP_POSTERIOR |         $.ajax({
// MVP_POSTERIOR |             url: '{{ route("eventos.index") }}',
// MVP_POSTERIOR |             method: 'GET',
// MVP_POSTERIOR |             data: { fecha: fecha },
// MVP_POSTERIOR |             success: function(html) {
// MVP_POSTERIOR |                 $('#listaEventos').html(html);
// MVP_POSTERIOR |                 const count = $(html).filter('.evento-card').length;
// MVP_POSTERIOR |                 $('#contadorEventosDia').text(count);
// MVP_POSTERIOR |             },
// MVP_POSTERIOR |             error: function() {
// MVP_POSTERIOR |                 $('#listaEventos').html('<div class="alert alert-danger">Error al cargar eventos.</div>');
// MVP_POSTERIOR |             }
// MVP_POSTERIOR |         });
// MVP_POSTERIOR |     }
});
</script>
<script src="{{ asset('js/calendario.js') }}"></script>
@endpush
