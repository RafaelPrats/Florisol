<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class IngresoRecepcion extends Model
{
    protected $table = 'ingreso_recepcion';
    protected $primaryKey = 'id_ingreso_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha',
        'fecha_registro',
        'tallos_x_ramo',
        'ramos',
        'longitud',
        'id_empresa',
        'bodega',
        'id_proveedor',
        'id_api_store_cajas',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function proveedor()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_proveedor');
    }

    public function api_store_cajas()
    {
        return $this->belongsTo('\yura\Modelos\ApiStoreCajas', 'id_api_store_cajas');
    }
}
