<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class EnraizamientoSemanal extends Model
{
    protected $table = 'enraizamiento_semanal';
    protected $primaryKey = 'id_enraizamiento_semanal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana_ini',
        'id_variedad',
        'cantidad_siembra',
        'semana_fin',
        'cantidad_semanas',
        'id_empresa',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleEnraizamientoSemanal', 'id_enraizamiento_semanal');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function semana_ini()
    {
        return Semana::All()
            ->where('codigo', $this->semana_ini)
            ->where('id_variedad', $this->id_variedad)
            ->first();
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
