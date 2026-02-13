<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ResumenTotalSemanalExportcalas extends Model
{
    protected $table = 'resumen_total_semanal_exportcalas';
    protected $primaryKey = 'id_resumen_total_semanal_exportcalas';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'semana',
        'id_variedad',
        'tallos_cosechados',
        'tallos_proyectados',
        'tallos_exportables',
        'nacional',
        'bajas',
        'tallos_vendidos',
        'venta',
        'id_empresa',
        'bouquetera',
        'venta_bouquetera',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}