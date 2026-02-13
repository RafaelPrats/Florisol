<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenAgrogana extends Model
{
    protected $table = 'resumen_agrogana';
    protected $primaryKey = 'id_resumen_agrogana';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha',
        'semana',
        'mes',
        'anno',
        'tallos_venta',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }
}
