<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class MonitoreoCalibre extends Model
{
    protected $table = 'monitoreo_calibre';
    protected $primaryKey = 'id_monitoreo_calibre';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_ciclo',
        'id_empresa',
        'fecha',
        'calibre',
        'num_malla',
        'peso',
        'desecho',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}