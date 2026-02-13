<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DespachoProveedor extends Model
{
    protected $table = 'despacho_proveedor';
    protected $primaryKey = 'id_despacho_proveedor';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'id_variedad',
        'fecha_ingreso',
        'cantidad',
        'disponibles',
        'tallos_x_ramo',
        'longitud',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function proveedor()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
