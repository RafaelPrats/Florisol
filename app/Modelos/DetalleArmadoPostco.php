<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleArmadoPostco extends Model
{
    protected $table = 'detalle_armado_postco';
    protected $primaryKey = 'id_detalle_armado_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_armado_postco   ',
        'unidades',
        'id_item',
    ];

    public function armado_postco ()
    {
        return $this->belongsTo('\yura\Modelos\ArmadoPostco', 'id_armado_postco   ');
    }

    public function item()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_item');
    }
}
