<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class RegistroMovimientos extends Model
{
    protected $table = 'registro_movimientos';
    protected $primaryKey = 'id_registro_movimientos';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_empresa',
        'bodega',
        'fecha_registro',
        'fecha',
        'tipo',
        'concepto',
        'numero',
        'cantidad',
        'id_usuario',
        'descripcion',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
