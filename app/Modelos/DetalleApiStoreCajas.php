<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleApiStoreCajas extends Model
{
    protected $table = 'detalle_api_store_cajas';
    protected $primaryKey = 'id_detalle_api_store_cajas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'tallos_x_ramo',
        'ramos',
        'longitud',
        'id_empresa',
        'estado',
        'id_api_store_cajas',
    ];

    public function api_store_cajas()
    {
        return $this->belongsTo('\yura\Modelos\ApiStoreCajas', 'id_api_store_cajas');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
