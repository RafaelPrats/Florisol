<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleOrdenTrabajo extends Model
{
    protected $table = 'detalle_orden_trabajo';
    protected $primaryKey = 'id_detalle_orden_trabajo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_orden_trabajo',
        'id_variedad',
        'tallos',
    ];

    public function orden_trabajo()
    {
        return $this->belongsTo('\yura\Modelos\OrdenTrabajo', 'id_orden_trabajo');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
