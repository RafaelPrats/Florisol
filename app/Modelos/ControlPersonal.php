<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ControlPersonal extends Model
{
    protected $table = 'control_personal';
    protected $primaryKey = 'id_control_personal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'id_personal_detalle',
        'id_mano_obra',
        'desde',
        'hasta',
        'fecha',
        'observaciones',
    ];


    public function personal_detalle()
    {
        return $this->belongsTo('\yura\Modelos\PersonalDetalle', 'id_personal_detalle');
    }

    public function mano_obra()
    {
        return $this->belongsTo('\yura\Modelos\ManoObra', 'id_mano_obra');
    }


}
