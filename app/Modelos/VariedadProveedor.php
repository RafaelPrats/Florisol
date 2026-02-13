<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class VariedadProveedor extends Model
{
    protected $table = 'variedad_proveedor';
    protected $primaryKey = 'id_variedad_proveedor';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
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
