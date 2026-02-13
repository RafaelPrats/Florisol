<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PreOrdenParcial extends Model
{
    protected $table = 'pre_orden_parcial';
    protected $primaryKey = 'id_pre_orden_parcial';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_pre_orden_trabajo',
        'id_orden_trabajo',
        'ramos',
    ];

    public function pre_orden_trabajo()
    {
        return $this->belongsTo('\yura\Modelos\PreOrdenTrabajo', 'id_pre_orden_trabajo');
    }

    public function orden_trabajo()
    {
        return $this->belongsTo('\yura\Modelos\OrdenTrabajo', 'id_orden_trabajo');
    }
}
