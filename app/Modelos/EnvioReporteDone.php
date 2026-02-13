<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class EnvioReporteDone extends Model
{
    protected $table = 'envio_reporte_done';
    protected $primaryKey = 'id_envio_reporte_done';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_envio_reporte',
        'fecha',
    ];

    public function usuarios()
    {
        return $this->belongsTo('\yura\Modelos\EnvioReporte', 'id_envio_reporte');
    }
}
