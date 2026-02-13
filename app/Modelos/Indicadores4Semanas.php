<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Indicadores4Semanas extends Model
{
    protected $table = 'indicadores_4_semanas';
    protected $primaryKey = 'id_indicadores_4_semanas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana',
        'precio_x_tallo',
        'precio_x_tallo_bqt',
        'costos_finca_m2',
        'venta_m2',
        'costos_m2',
        'ebitda_m2',
    ];
}
