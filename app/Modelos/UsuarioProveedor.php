<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class UsuarioProveedor extends Model
{
    protected $table = 'usuario_proveedor';
    protected $primaryKey = 'id_usuario_proveedor';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_finca_proveedor',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }

    public function finca_proveedor()
    {
        return $this->belongsTo('\yura\Modelos\FincaProveedor', 'id_finca_proveedor');
    }
}
