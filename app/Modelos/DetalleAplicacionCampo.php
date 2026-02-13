<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleAplicacionCampo extends Model
{
    protected $table = 'detalle_aplicacion_campo';
    protected $primaryKey = 'id_detalle_aplicacion_campo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion_campo',
        'id_mano_obra',
        'id_producto',
        'dosis',
        'id_unidad_medida',
        'factor_conversion',
        'id_unidad_conversion',
    ];

    public function aplicacion_campo()
    {
        return $this->belongsTo('\yura\Modelos\AplicacionCampo', 'id_aplicacion_campo');
    }

    public function mano_obra()
    {
        return $this->belongsTo('\yura\Modelos\ManoObra', 'id_mano_obra');
    }

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
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
