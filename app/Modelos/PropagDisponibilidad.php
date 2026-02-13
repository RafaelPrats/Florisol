<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PropagDisponibilidad extends Model
{
    protected $table = 'propag_disponibilidad';
    protected $primaryKey = 'id_propag_disponibilidad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana',
        'id_variedad',
        'saldo_inicial',    // 	Saldo Sem -1
        'plantas_sembradas',    //  Ptas Sembradas Sem -7*
        'semana_disponible',    //  Sem +7*
        'plantas_disponibles',  //  (saldo_inicial + plantas_sembradas - desecho) Sem actual
        'requerimientos',   //  	"id_modulo+nombre_modulo+(plantas_muertas podas Sem -1 ó plantas_iniciales siembras Sem actual)|..."
        'requerimientos_adicionales',   //  	"id_modulo+nombre_modulo+(plantas_muertas podas Sem -1 ó plantas_iniciales siembras Sem actual)|..."
        'desecho',  //  % desecho de plantas_sembradas
        'saldo',    //  (plantas_disponibles - requerimientos) Sem actual
        'mantener_cambios',
        'destino_plantas_sembradas',    //  "semana+cantidad|..."
        'id_empresa',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function desecho()
    {
        return round(($this->desecho * $this->plantas_sembradas) / 100);
    }

    public function calcular_requerimientos()
    {
        /*$r = 0;
        if ($this->requerimientos != '')
            foreach (explode('|', $this->requerimientos) as $req) {
                $explode = explode('+', $req);
                $r += count($explode) >= 2 && $explode[2] > 0 ? $explode[2] : 0;
            }
        if ($this->requerimientos_adicionales != '')
            foreach (explode('|', $this->requerimientos_adicionales) as $req) {
                $explode = explode('+', $req);
                $r += count($explode) >= 2 && $explode[2] > 0 ? $explode[2] : 0;
            }
        return $r;*/
        return $this->requerimientos != '' ? $this->requerimientos : 0;
    }
}
