<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyecto';
    protected $primaryKey = 'id_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'fecha',
        'tipo',
        'fecha_registro',
        'estado',
        'packing',
        'orden_fija',
        'id_consignatario',
        'id_agencia_carga',
        'dae',
        'codigo_pais',
        'guia_madre',
        'guia_hija',
        'id_aerolinea',
        'id_empresa',
        'impreso',
    ];

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function consignatario()
    {
        return $this->belongsTo('\yura\Modelos\Consignatario', 'id_consignatario');
    }

    public function agencia_carga()
    {
        return $this->belongsTo('\yura\Modelos\AgenciaCarga', 'id_agencia_carga');
    }

    public function aerolinea()
    {
        return $this->belongsTo('\yura\Modelos\Aerolinea', 'id_aerolinea');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function cajas()
    {
        return $this->hasMany('\yura\Modelos\CajaProyecto', 'id_proyecto');
    }
}
