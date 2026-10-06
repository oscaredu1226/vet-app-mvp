<div class="cita-horario" data-hora-inicial="{{ $initialTime ?? '' }}">
    <label class="form-label" for="{{ $inputId }}_turno">Turno</label>
    <select class="form-select cita-turno mb-2" id="{{ $inputId }}_turno">
        <option value="">Selecciona mañana o tarde</option>
        <option value="manana">Mañana (8:00 a. m. – 11:30 a. m.)</option>
        <option value="tarde">Tarde (12:00 p. m. – 8:00 p. m.)</option>
    </select>
    <label class="form-label" for="{{ $inputId }}">Hora</label>
    <select class="form-select cita-hora" id="{{ $inputId }}" name="{{ $inputName ?? 'hora' }}">
        <option value="">Selecciona primero el turno</option>
    </select>
    <small class="text-muted">Horarios cada 30 minutos.</small>
</div>
