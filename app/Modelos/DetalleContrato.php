<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleContrato extends Model
{
    protected $table = 'detalle_contrato';
    protected $primaryKey = 'id_detalle_contrato';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_registro',
    ];
}
