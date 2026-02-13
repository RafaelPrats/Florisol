<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class RelacionLaboral extends Model
{
    protected $table = 'relacion_laboral';
    protected $primaryKey = 'id_relacion_laboral';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
