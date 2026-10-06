<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascota extends Model
{
    use HasFactory;

    protected $table = 'mascotas';
    protected $primaryKey = 'id_mascota';
    public $timestamps = false; // La migración no incluyó created_at/updated_at

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'id_cliente',
        'nombre',
        'especie',
        'raza',
        'genero',
        'fecha_nacimiento',
        'estado',
        'esterilizado',
    ];

    /**
     * Una Mascota pertenece a un Cliente.
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Una Mascota tiene muchas Historias Clínicas.
     */
    public function historiasClinicas()
    {
        return $this->hasMany(HistoriaClinica::class, 'id_mascota', 'id_mascota');
    }
    
    /**
     * Una Mascota tiene muchas Consultas.
     */
    public function consultas()
    {
        return $this->hasMany(Consulta::class, 'id_mascota', 'id_mascota');
    }
    
    /**
     * Una Mascota tiene muchas Vacunas.
     */
    public function vacunas()
    {
        return $this->hasMany(Vacuna::class, 'id_mascota', 'id_mascota');
    }
    
    /**
     * Una Mascota tiene muchas Desparasitaciones.
     */
    public function desparasitaciones()
    {
        return $this->hasMany(Desparasitacion::class, 'id_mascota', 'id_mascota');
    }
    
    /**
     * Una Mascota tiene muchos registros de Antipulgas.
     */
    public function antipulgas()
    {
        return $this->hasMany(Antipulga::class, 'id_mascota', 'id_mascota');
    }
    
    /**
     * Una Mascota tiene muchas Cirugías.
     */
    public function cirugias()
    {
        return $this->hasMany(Cirugia::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Una Mascota tiene muchas Historias de Baño.
     */
    public function historiasBanos()
    {
        return $this->hasMany(HistoriaBano::class, 'id_mascota', 'id_mascota');
    }

    /**
     * Obtiene la última Historia de Baño de la mascota.
     */
    public function ultimaHistoriaBano()
    {
        return $this->hasOne(HistoriaBano::class, 'id_mascota', 'id_mascota')
            ->latest('fecha_registro');
    }

    /**
     * Calcula la edad completa en años, meses y días
     */
    public function getEdadCompletaAttribute()
    {
        if (!$this->fecha_nacimiento) {
            return 'N/A';
        }

        $fechaNacimiento = \Carbon\Carbon::parse($this->fecha_nacimiento);
        $ahora = \Carbon\Carbon::now();

        $años = (int) $fechaNacimiento->diffInYears($ahora);
        $fechaDespuesDeAños = $fechaNacimiento->copy()->addYears($años);
        
        $meses = (int) $fechaDespuesDeAños->diffInMonths($ahora);
        $fechaDespuesDeAñosYMeses = $fechaDespuesDeAños->copy()->addMonths($meses);
        
        $dias = (int) $fechaDespuesDeAñosYMeses->diffInDays($ahora);

        $resultado = [];
        
        if ($años > 0) {
            $resultado[] = "$años " . ($años == 1 ? 'año' : 'años');
        }
        
        if ($meses > 0) {
            $resultado[] = "$meses " . ($meses == 1 ? 'mes' : 'meses');
        }
        
        if ($dias > 0 || empty($resultado)) {
            $resultado[] = "$dias " . ($dias == 1 ? 'día' : 'días');
        }

        return implode(', ', $resultado);
    }
}