<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ControlDiario extends Model
{
    protected $table = 'control_diario';
    protected $primaryKey = 'id_control_diario';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'numero_semana',
        'fecha',
        'id_actividad',
        'inicio_hora_ordinario',
        'fin_hora_ordinario',
        'inicio_hora_50',
        'fin_hora_50',
        'inicio_hora_100',
        'fin_hora_100',
        'inicio_hora_nocturno',
        'fin_hora_nocturno',
    ];
    public function actividad()
    {
        return $this->belongsTo('\yura\Modelos\Actividad', 'id_actividad');
    }
}
