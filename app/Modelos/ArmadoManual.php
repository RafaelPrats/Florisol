<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ArmadoManual extends Model
{
    protected $table = 'armado_manual';
    protected $primaryKey = 'id_armado_manual';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_caja_proyecto',
        'ramos',
    ];

    public function detalle_caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\DetalleCajaProyecto', 'id_detalle_caja_proyecto');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleArmadoManual', 'id_armado_manual');
    }
}
