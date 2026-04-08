<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionReceta extends Model
{
    protected $table = 'distribucion_receta';
    protected $primaryKey = 'id_distribucion_receta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_caja_proyecto',
        'id_variedad',
        'longitud',
        'unidades',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function detalle_caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\DetalleCajaProyecto', 'id_detalle_caja_proyecto');
    }
}
