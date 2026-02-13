<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleEnraizamientoSemanal extends Model
{
    protected $table = 'detalle_enraizamiento_semanal';
    protected $primaryKey = 'id_detalle_enraizamiento_semanal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_enraizamiento_semanal',
        'fecha',
        'cantidad_siembra',
        'id_contenedor_propag',
    ];

    public function enraizamiento_semanal()
    {
        return $this->belongsTo('\yura\Modelos\EnraizamientoSemanal', 'id_enraizamiento_semanal');
    }

    public function contenedor_propag()
    {
        return $this->belongsTo('\yura\Modelos\ContenedorPropag', 'id_contenedor_propag');
    }
}
