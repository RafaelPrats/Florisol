<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetallePropuesta extends Model
{
    protected $table = 'detalle_propuesta';
    protected $primaryKey = 'id_detalle_propuesta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_propuesta',
        'id_variedad',
        'unidades',
    ];

    public function propuesta()
    {
        return $this->belongsTo('\yura\Modelos\Propuesta', 'id_propuesta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
