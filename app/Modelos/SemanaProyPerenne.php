<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class SemanaProyPerenne extends Model
{
    protected $table = 'semana_proy_perenne';
    protected $primaryKey = 'id_semana_proy_perenne';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'curva',
        'id_empresa',
        'id_semana',
        'proyectados',
        'proyectados_acum',
        'cosechados',
        'cosechados_acum',
        'porcentaje_cumplimiento',
        'porcentaje_cumplimiento_acum',
        'tallos_m2_ejecutado',
        'tallos_m2_ejecutado_acum',
        'plantas_iniciales',
        'sum_ejec_4_sem',   // sumatoria de tallos/m2 ejecutado de las 4 semanas anteriores (sin tener en cuenta la semana actual)
        'sum_ejec_13_sem',   // sumatoria de tallos/m2 ejecutado de las 13 semanas anteriores (sin tener en cuenta la semana actual)
        'sum_ejec_52_sem',   // sumatoria de tallos/m2 ejecutado de las 52 semanas anteriores (sin tener en cuenta la semana actual)
    ];

    public function semana()
    {
        return $this->belongsTo('\yura\Modelos\Semana', 'id_semana');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
