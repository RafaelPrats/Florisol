<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsuariosEnvioReporte extends Model
{
    protected $table = 'usuarios_envio_reporte';
    protected $primaryKey = 'id_usuarios_envio_reporte';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_envio_reporte',
        'id_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }

    public function envio_reporte()
    {
        return $this->belongsTo('\yura\Modelos\EnvioReporte', 'id_envio_reporte');
    }
}
