<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Exportador extends Model
{
    protected $table = 'exportador';
    protected $primaryKey = 'id_exportador';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'identificacion',
        'estado',
        'id_empresa',
        'codigo_externo',
        'fecha_registro',
    ];
}
