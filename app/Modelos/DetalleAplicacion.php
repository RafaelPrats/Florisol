<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetalleAplicacion extends Model
{
    protected $table = 'detalle_aplicacion';
    protected $primaryKey = 'id_detalle_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion',
        'id_mano_obra',
        'id_producto',
        'id_aplicacion_mezcla',
    ];

    public function aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\Aplicacion', 'id_aplicacion');
    }

    public function mezcla()
    {
        return $this->belongsTo('\yura\Modelos\AplicacionMezcla', 'id_aplicacion_mezcla');
    }

    public function mano_obra()
    {
        return $this->belongsTo('\yura\Modelos\ManoObra', 'id_mano_obra');

    }

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');

    }

    public function parametros()
    {
        return $this->hasMany('\yura\Modelos\ParametroDetalleAplicacion', 'id_detalle_aplicacion');

    }
}
