<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CicloLuz extends Model
{
    protected $table = 'ciclo_luz';
    protected $primaryKey = 'id_ciclo_luz';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'tipo_luz',
        'lamparas',
        'inicio_luz',
        'dias_adicional',
        'fecha',
        'dias_proy',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }

    public function getHorasDia()
    {
        $horaInicio = new \DateTime('24:00');
        $horaTermino = new \DateTime($this->hora_ini);
        $inicio = $horaInicio->diff($horaTermino)->h;

        $horaInicio = new \DateTime($this->hora_fin);
        $horaTermino = new \DateTime('00:00');
        $fin = $horaInicio->diff($horaTermino)->h;
        return ($inicio + $fin);
    }
}
