<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ClasificacionUnitaria extends Model
{
    protected $table = 'clasificacion_unitaria';
    protected $primaryKey = 'id_clasificacion_unitaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_empresa',
        'id_unidad_medida',
        'nombre',
        'fecha_registro',
        'estado',
        'factor_conversion',
        'tallos_x_ramo',
        'bg_color',
        'tx_color',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function unidad_medida()
    {
        return $this->belongsTo('\yura\Modelos\UnidadMedida', 'id_unidad_medida');
    }
}
