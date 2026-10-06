@php($cita = !empty($value) ? \Carbon\Carbon::parse($value) : null)
<div class="cita-proxima">
    <input type="hidden" name="proxima_cita" class="cita-fecha-hora" value="{{ $cita ? $cita->format('Y-m-d\TH:i') : '' }}">
    <input type="date" id="proxima_cita" class="form-control cita-fecha mb-2" value="{{ $cita ? $cita->toDateString() : '' }}">
    @include('eventos.partials.hora', [
        'inputId' => 'proxima_cita_hora',
        'inputName' => 'proxima_cita_hora',
        'initialTime' => $cita ? $cita->format('H:i') : '',
    ])
</div>
