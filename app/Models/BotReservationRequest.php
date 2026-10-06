<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotReservationRequest extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'chat_id', 'payload_hash', 'payload', 'status', 'expires_at', 'id_evento'];
    protected $casts = ['payload' => 'encrypted:array', 'expires_at' => 'datetime'];
}
