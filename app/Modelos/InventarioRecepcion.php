<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class InventarioRecepcion extends Model
{
    protected $table = 'inventario_recepcion';
    protected $primaryKey = 'id_inventario_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'fecha',
        'fecha_registro',
        'id_api_store_cajas',
        'tallos_x_ramo',
        'ramos',
        'longitud',
        'disponibles',
        'id_empresa',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
