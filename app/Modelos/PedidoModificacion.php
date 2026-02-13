<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class PedidoModificacion extends Model
{

    protected $table = 'pedido_modificacion';
    protected $primaryKey = 'id_pedido_modificacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_cliente',
        'estado',
        'fecha_anterior',
        'fecha_nueva',
        'tallos',
        'operador',
        'fecha_registro',
        'cambio_fecha',
        'id_variedad',
        'longitud',
    ];

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
