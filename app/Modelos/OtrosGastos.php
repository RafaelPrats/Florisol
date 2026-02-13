<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OtrosGastos extends Model
{
    protected $table = 'otros_gastos';
    protected $primaryKey = 'id_otros_gastos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_area',
        'codigo_semana',
        'gip',
        'ga',
        'regalias',
        'id_empresa',
    ];

    public function area()
    {
        return $this->belongsTo('\yura\Modelos\Area', 'id_area');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}