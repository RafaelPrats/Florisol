<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleOrdenCompra extends Model
{
    protected $table = 'detalle_orden_compra';
    protected $primaryKey = 'id_detalle_orden_compra';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_orden_compra  ',
        'id_variedad',
        'tallos',
    ];

    public function orden_compra()
    {
        return $this->belongsTo('\yura\Modelos\OrdeCompra', 'id_orden_compra  ');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function proveedores()
    {
        return $this->hasMany('\yura\Modelos\ProveedorDetalleOrdenCompra', 'id_detalle_orden_compra');
    }
}
