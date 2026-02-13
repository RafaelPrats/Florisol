<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CompraParcialDiaria extends Model
{
    protected $table = 'compra_parcial_diaria';
    protected $primaryKey = 'id_compra_parcial_diaria';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'tallos',
        'longitud',
        'fecha',
        'id_proveedor',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function proveedor()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_proveedor');
    }
}
