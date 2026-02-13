<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenFenogramaPropagacion extends Model
{
    protected $table = 'resumen_fenograma_propagacion';
    protected $primaryKey = 'id_resumen_fenograma_propagacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo_cama',
        'id_cama',
        'id_variedad',
        'id_planta',
        'id_empresa',
        'planta_nombre',
        'variedad_nombre',
        'fecha_inicio',
        'fecha_fin',
        'cama_nombre',
        'semana_siembra',
        'semana_actual',
        'plantas_iniciales',
        'cosecha',
        'semana_cosecha',
        'esq_x_sem',
        'esq_x_sem_acum',
        'esq_x_planta',
        'fin_produccion',
        'conteo',
        'planta_siglas',
        'variedad_siglas',
    ];

    public function ciclo_cama()
    {
        return $this->belongsTo('\yura\Modelos\CicloCama', 'id_ciclo_cama');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function cama()
    {
        return $this->belongsTo('\yura\Modelos\Cama', 'id_cama');
    }
}
