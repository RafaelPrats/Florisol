<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsuarioFinca extends Model
{
    protected $table = 'usuario_finca';
    protected $primaryKey = 'id_usuario_finca';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_empresa',
        'fecha_registro',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }
}
