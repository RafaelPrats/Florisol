<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetallePreOrdenTrabajo extends Model
{
    protected $table = 'detalle_pre_orden_trabajo';
    protected $primaryKey = 'id_detalle_pre_orden_trabajo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pre_orden_trabajo',
        'id_variedad',
        'tallos',
    ];

    public function pre_orden_trabajo()
    {
        return $this->belongsTo('\yura\Modelos\PreOrdenTrabajo', 'id_pre_orden_trabajo');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
