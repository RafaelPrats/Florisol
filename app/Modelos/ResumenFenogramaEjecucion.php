<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenFenogramaEjecucion extends Model
{
    protected $table = 'resumen_fenograma_ejecucion';
    protected $primaryKey = 'id_resumen_fenograma_ejecucion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'id_modulo',
        'nombre_modulo',
        'fecha_inicio',
        'fecha_fin',
        'semana',
        'id_variedad',
        'siglas_variedad',
        'nombre_variedad',
        'poda_siembra',
        'dias',
        'area_m2',
        'total_x_semana_m2',
        'primera_flor',
        'porciento_mortalidad',
        'tallos_cosechados',
        'real_tallos_m2',
        'porciento_cosechado',
        'proy_tallos_m2',
        'plantas_iniciales',
        'plantas_actuales',
        'plantas_muertas',
        'densidad_plantas_ini_m2',
        'conteo',
        'id_planta',
        'siglas_planta',
        'nombre_planta',
        'desecho',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}