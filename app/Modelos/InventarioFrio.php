<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class InventarioFrio extends Model
{
    protected $table = 'inventario_frio';
    protected $primaryKey = 'id_inventario_frio';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'estado',
        'fecha',
        'cantidad',
        'disponibles',
        'id_variedad',
        'id_clasificacion_ramo',
        'id_empaque',
        'finca_destino',
        'tallos_x_ramo',
        'disponibilidad',
        'basura',
        'id_empresa',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function get_finca_destino()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'finca_destino');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function modulo()
    {
        return $this->belongsTo('\yura\Modelos\Modulo', 'id_modulo');
    }

    public function clasificacion_ramo()
    {
        return $this->belongsTo('\yura\Modelos\ClasificacionRamo', 'id_clasificacion_ramo');
    }

    public function empaque()
    {
        return $this->belongsTo('\yura\Modelos\Empaque', 'id_empaque');
    }
}
