<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PlantaFinca extends Model
{
    protected $table = 'planta_finca';
    protected $primaryKey = 'id_planta_finca';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'id_finca_proveedor',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function finca_proveedor()
    {
        return $this->belongsTo('\yura\Modelos\FincaProveedor', 'id_finca_proveedor');
    }
}
