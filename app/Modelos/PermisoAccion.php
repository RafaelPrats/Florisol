<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PermisoAccion extends Model
{

    protected $table = 'permiso_accion';
    protected $primaryKey = 'id_permiso_accion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'estado',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
