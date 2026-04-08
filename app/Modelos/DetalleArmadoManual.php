<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleArmadoManual extends Model
{
    protected $table = 'detalle_armado_manual';
    protected $primaryKey = 'id_detalle_armado_manual';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_armado_manual',
        'unidades',
        'id_variedad',
    ];

    public function armado_manual()
    {
        return $this->belongsTo('\yura\Modelos\ArmadoManual', 'id_armado_manual');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
