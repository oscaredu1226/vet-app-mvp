// Selectores compartidos por calendario, historial y próxima cita de consulta.
(function () {
    function label(minutes) {
        const hour = Math.floor(minutes / 60);
        return (hour % 12 || 12) + ':' + String(minutes % 60).padStart(2, '0') + (hour < 12 ? ' a. m.' : ' p. m.');
    }

    function fill(group, turno) {
        const input = group.querySelector('.cita-hora');
        input.replaceChildren(new Option(turno ? 'Selecciona la hora' : 'Selecciona primero el turno', ''));
        // No se deshabilita: el valor vacío permite guardar una cita sin hora confirmada.
        if (!turno || turno === 'anterior') return;
        const start = turno === 'manana' ? 480 : 720;
        const end = turno === 'manana' ? 690 : 1200;
        for (let minutes = start; minutes <= end; minutes += 30) {
            const value = String(Math.floor(minutes / 60)).padStart(2, '0') + ':' + String(minutes % 60).padStart(2, '0');
            input.add(new Option(label(minutes), value));
        }
    }

    function sync(group) {
        const proxima = group.closest('.cita-proxima');
        if (!proxima) return;
        const date = proxima.querySelector('.cita-fecha');
        const time = group.querySelector('.cita-hora').value;
        proxima.querySelector('.cita-fecha-hora').value = date.value && time ? date.value + 'T' + time : '';
        const message = Boolean(date.value) !== Boolean(time) ? 'Selecciona la fecha, el turno y la hora de la próxima cita, o deja todos vacíos.' : '';
        date.setCustomValidity(message);
        if (date._flatpickr?.altInput) date._flatpickr.altInput.setCustomValidity(message);
    }

    function setTime(input, value) {
        const group = input.closest('.cita-horario');
        const turno = group.querySelector('.cita-turno');
        turno.querySelector('option[value="anterior"]')?.remove();
        const time = (value || '').slice(0, 5);
        const parts = time.split(':').map(Number);
        const minutes = parts[0] * 60 + parts[1];
        const valid = /^\d{2}:(00|30)$/.test(time) && minutes >= 480 && minutes <= 1200;
        const period = time ? (valid ? (minutes < 720 ? 'manana' : 'tarde') : 'anterior') : '';
        if (period === 'anterior') turno.add(new Option('Horario anterior (conservado)', 'anterior'));
        turno.value = period;
        fill(group, period);
        if (period === 'anterior') input.add(new Option(time + ' (horario anterior)', time));
        input.value = time;
        sync(group);
    }

    window.CitasHorario = { setTime };
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.cita-horario').forEach(function (group) {
            setTime(group.querySelector('.cita-hora'), group.dataset.horaInicial);
            group.querySelector('.cita-turno').addEventListener('change', function () {
                fill(group, this.value);
                sync(group);
            });
            group.querySelector('.cita-hora').addEventListener('change', () => sync(group));
            group.closest('.cita-proxima')?.querySelector('.cita-fecha').addEventListener('change', () => sync(group));
            group.closest('form')?.addEventListener('reset', function () {
                setTimeout(() => setTime(group.querySelector('.cita-hora'), group.dataset.horaInicial), 0);
            });
        });
    });
})();
