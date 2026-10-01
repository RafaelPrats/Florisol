<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProveedorDetalleOrdenCompra extends Model
{
    protected $table = 'proveedor_detalle_orden_compra';
    protected $primaryKey = 'id_proveedor_detalle_orden_compra';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_orden_compra',
        'id_proveedor',
        'cantidad',
        'id_usuario',
    ];

    public function detalle_orden_compra()
    {
        return $this->belongsTo('\yura\Modelos\DetalleOrdenCompra', 'id_detalle_orden_compra');
    }

    public function proveedor()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
