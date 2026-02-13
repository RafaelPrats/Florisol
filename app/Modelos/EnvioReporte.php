<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class EnvioReporte extends Model
{
    protected $table = 'envio_reporte';
    protected $primaryKey = 'id_envio_reporte';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nombre_funcion',
        'nombre_reporte',   //  Propagación: Esquejes cosechados - 1; Proyección: Resumen semanal - 2; P y G semanal - 3;
        'dia_semana',   // 01 (lunes)
        'hora', // 08:15
    ];

    public function usuarios()
    {
        return $this->hasMany('\yura\Modelos\UsuariosEnvioReporte', 'id_envio_reporte');
    }
}
