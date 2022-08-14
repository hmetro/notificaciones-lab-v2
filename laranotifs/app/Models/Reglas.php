<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reglas extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ene',
        'tipo_validacion',
        'x_servicio',
        'x_origen',
        'x_motivo',
        'x_especialidad',
        'x_medico',
        'x_idprueba',
        'add_json',
        'created_at',
        'updated_at',
    ];
}
