<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HalfHourAppointment implements ValidationRule
{
    public function __construct(private ?string $unchangedValue = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $time = Carbon::parse($value);
            // Permite conservar una cita histórica al editar otros datos, sin redondearla.
            if ($this->unchangedValue && $time->equalTo(Carbon::parse($this->unchangedValue))) return;
            $minutes = $time->hour * 60 + $time->minute;
            if ($minutes >= 480 && $minutes <= 1200 && $time->minute % 30 === 0
                && $time->second === 0 && $time->micro === 0) {
                return;
            }
        } catch (\Throwable $exception) {
            // El formato también lo comprueban las reglas date/date_format.
        }

        $fail('Selecciona una hora entre las 8:00 a. m. y las 8:00 p. m., en intervalos de 30 minutos.');
    }
}
