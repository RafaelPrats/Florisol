<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OtReclamo extends Model
{
    protected $table = 'ot_reclamo';
    protected $primaryKey = 'id_ot_reclamo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ot_postco',
        'fecha',
        'cantidad',
        'id_motivo_reclamo',
        'fecha',
    ];

    public function ot_postco()
    {
        return $this->belongsTo('\yura\Modelos\OtPostco', 'id_ot_postco');
    }

    public function motivo_reclamo()
    {
        return $this->belongsTo('\yura\Modelos\MotivoReclamo', 'id_motivo_reclamo');
    }
}
