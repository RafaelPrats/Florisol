<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class FlorNacional extends Model
{
    protected $table = 'flor_nacional';
    protected $primaryKey = 'id_flor_nacional';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id_variedad',
        'id_motivo_flor_nacional',
        'finca_origen',
        'tallos',
        'porcentaje',
        'nacional',
    ];

    public function variedad()
    {
        return $this->belongsTo('yura\Modelos\Variedad', 'id_variedad');
    }

    public function motivo_flor_nacional()
    {
        return $this->belongsTo('yura\Modelos\MotivoFlorNacional', 'id_motivo_flor_nacional');
    }
}
