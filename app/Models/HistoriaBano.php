<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaBano extends Model
{
    use HasFactory;

    protected $table = 'historia_banos';
    protected $primaryKey = 'id_historia_bano';
    public $timestamps = true;

    protected $fillable = [
        'id_mascota',
        'fecha_registro',
        'muerde',
        'agresivo',
        'no_recibir',
        'tener_cuidado',
        'observaciones',
    ];

    protected $casts = [
        'muerde' => 'boolean',
        'agresivo' => 'boolean',
        'no_recibir' => 'boolean',
        'tener_cuidado' => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    /**
     * Una Historia de Baño pertenece a una Mascota
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }
}
