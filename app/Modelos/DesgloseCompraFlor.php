<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DesgloseCompraFlor extends Model
{
    protected $table = 'desglose_compra_flor';
    protected $primaryKey = 'id_desglose_compra_flor';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'cantidad_mallas',
        'tallos_x_malla',
        'disponibles',
        'longitud',
        'fecha',
        'id_proveedor',
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

    public function proveedor()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_proveedor');
    }
}
