<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class CorreccionRecepcion extends Model
{
    protected $table = 'correccion_recepcion';
    protected $primaryKey = 'id_correccion_recepcion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha',
        'id_variedad',
        'orden',
        'fecha_registro',
        'id_empresa',
        'bodega',
        'anterior',
        'actual',
        'diferencia',
        'id_usuario',
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
