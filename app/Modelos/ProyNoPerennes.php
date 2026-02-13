<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyNoPerennes extends Model
{
    protected $table = 'proy_no_perennes';
    protected $primaryKey = 'id_proy_no_perennes';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_semana',
        'area_produccion',
        'area_semana',
        'proyectados',
        'id_empresa',
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
