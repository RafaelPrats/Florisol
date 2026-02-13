<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Tipo_rol extends Model
{
    protected $table = 'tipo_rol';
    protected $primaryKey = 'id_tipo_rol';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
