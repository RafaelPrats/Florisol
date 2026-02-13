<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ParametroAplicacion extends Model
{
    protected $table = 'parametro_aplicacion';
    protected $primaryKey = 'id_parametro_aplicacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion',
        'campo',
        'desde',
        'hasta',
        'tipo',
        'valor',
    ];

    public function aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\Aplicacion', 'id_aplicacion');
    }

    public function getTipo()
    {
        $tipos = [
            'E' => 'Estandar',
            'T' => 'Temperatura',
            'D' => 'Delta Acum. 10 días',
            'L' => 'Lluvia Acum. 21 días',
            'A' => 'Altura',
        ];
        return $tipos[$this->tipo];
    }

    public function getCampo()
    {
        $tipos = [
            'dia_ini' => 'Día de inicio',
            'semana_ini' => 'Semana de inicio',
            'repeticiones' => 'Repeticiones',
            'veces_x_semana' => 'Veces x semana',
        ];
        return $tipos[$this->campo];
    }
}
