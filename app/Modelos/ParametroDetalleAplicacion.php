<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ParametroDetalleAplicacion extends Model
{
    protected $table = 'parametro_detalle_aplicacion';
    protected $primaryKey = 'id_parametro_detalle_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_aplicacion',
        'id_variedad',
        'dosis',
        'cantidad_mo',
        'id_unidad_medida',
        'factor_conversion',
        'id_unidad_conversion',
    ];

    public function detalle_aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\DetalleAplicacion', 'id_detalle_aplicacion');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }

    public function unidad_conversion()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_conversion');
    }
}
