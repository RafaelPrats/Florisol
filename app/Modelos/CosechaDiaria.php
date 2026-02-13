<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CosechaDiaria extends Model
{
    protected $table = 'cosecha_diaria';
    protected $primaryKey = 'id_cosecha_diaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_planta',
        'id_variedad',
        'variedad_nombre',
        'planta_nombre',
        'fecha',
        'cosechados',
        'id_empresa',
    ];

    public function planta()
    {
        return $this->belongsTo('\yura\Modelos\Planta', 'id_planta');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}