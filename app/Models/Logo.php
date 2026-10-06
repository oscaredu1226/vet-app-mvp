<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    use HasFactory;
    
    protected $table = 'logo';
    // Timestamps es true por defecto

    protected $fillable = [
        'imagen',
    ];
}