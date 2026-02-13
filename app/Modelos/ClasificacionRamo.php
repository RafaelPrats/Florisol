<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ClasificacionRamo extends Model
{
    protected $table = 'clasificacion_ramo';
    protected $primaryKey = 'id_clasificacion_ramo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_clasificacion_ramo',
        'id_empresa',
        'nombre',
        'fecha_registro',
        'estado',
        'id_unidad_medida',
        'estandar',
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
