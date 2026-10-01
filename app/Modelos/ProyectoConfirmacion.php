<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProyectoConfirmacion extends Model
{
    protected $table = 'proyecto_confirmacion';
    protected $primaryKey = 'id_proyecto_confirmacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyecto ',
        'id_finca_proveedor ',
        'fecha',
        'fecha_registro',
        'id_usuario',
    ];

    public function proyecto()
    {
        return $this->belongsTo('\yura\Modelos\Proyecto', 'id_proyecto');
    }

    public function finca_proveedor()
    {
        return $this->belongsTo('\yura\Modelos\FincaProveedor', 'id_finca_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
