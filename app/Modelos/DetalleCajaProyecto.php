<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleCajaProyecto extends Model
{
    protected $table = 'detalle_caja_proyecto';
    protected $primaryKey = 'id_detalle_caja_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_caja_proyecto',
        'id_variedad',
        'ramos_x_caja',
        'tallos_x_ramo',
        'precio',
        'longitud_ramo',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\CajaProyecto', 'id_caja_proyecto');
    }
}
