<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class GrupoInterno extends Model
{
    protected $table = 'grupo_interno';
    protected $primaryKey = 'id_grupo_interno';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
