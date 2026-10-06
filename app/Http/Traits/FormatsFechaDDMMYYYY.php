<?php

namespace App\Http\Traits;

use Carbon\Carbon;

trait FormatsFechaDDMMYYYY
{
    /**
     * Convierte fechas en formato DD-MM-YYYY a YYYY-MM-DD
     *
     * @param array $data
     * @param array $camposFecha
     * @return array
     */
    protected function convertirFechasAISO(array $data, array $camposFecha)
    {
        foreach ($camposFecha as $campo) {
            if (isset($data[$campo]) && !empty($data[$campo])) {
                // Si ya está en formato ISO (YYYY-MM-DD), no convertir
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$campo])) {
                    continue;
                }
                
                // Si está en formato DD-MM-YYYY, convertir a YYYY-MM-DD
                if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $data[$campo], $matches)) {
                    $dia = $matches[1];
                    $mes = $matches[2];
                    $anio = $matches[3];
                    $data[$campo] = "{$anio}-{$mes}-{$dia}";
                }
            }
        }
        
        return $data;
    }

    /**
     * Convierte una fecha individual de DD-MM-YYYY a YYYY-MM-DD
     *
     * @param string|null $fecha
     * @return string|null
     */
    protected function convertirFechaAISO($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        // Si ya está en formato ISO
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }

        // Si está en formato DD-MM-YYYY
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $fecha, $matches)) {
            $dia = $matches[1];
            $mes = $matches[2];
            $anio = $matches[3];
            return "{$anio}-{$mes}-{$dia}";
        }

        return $fecha;
    }

    /**
     * Convierte fechas en formato YYYY-MM-DD a DD-MM-YYYY
     *
     * @param array $data
     * @param array $camposFecha
     * @return array
     */
    protected function convertirFechasALocal(array $data, array $camposFecha)
    {
        foreach ($camposFecha as $campo) {
            if (isset($data[$campo]) && !empty($data[$campo])) {
                // Si ya está en formato DD-MM-YYYY, no convertir
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $data[$campo])) {
                    continue;
                }
                
                // Si está en formato ISO (YYYY-MM-DD), convertir a DD-MM-YYYY
                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data[$campo], $matches)) {
                    $anio = $matches[1];
                    $mes = $matches[2];
                    $dia = $matches[3];
                    $data[$campo] = "{$dia}-{$mes}-{$anio}";
                }
            }
        }
        
        return $data;
    }

    /**
     * Convierte una fecha individual de YYYY-MM-DD a DD-MM-YYYY
     *
     * @param string|null $fecha
     * @return string|null
     */
    protected function convertirFechaALocal($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        // Si ya está en formato DD-MM-YYYY
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $fecha)) {
            return $fecha;
        }

        // Si está en formato ISO (YYYY-MM-DD)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $matches)) {
            $anio = $matches[1];
            $mes = $matches[2];
            $dia = $matches[3];
            return "{$dia}-{$mes}-{$anio}";
        }

        return $fecha;
    }
}
