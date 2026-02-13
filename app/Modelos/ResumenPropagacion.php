<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenPropagacion extends Model
{
    protected $table = 'resumen_propagacion';
    protected $primaryKey = 'id_resumen_propagacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'semana',
        'esquejes_cosechados',
        'plantas_sembradas',
        'esquejes_x_planta',
        'costo_x_esqueje',
        'costo_x_planta',
        'requerimientos',
        'porcentaje_requerimiento',
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
