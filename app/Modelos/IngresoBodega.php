<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class IngresoBodega extends Model
{
    protected $table = 'ingreso_bodega';
    protected $primaryKey = 'id_ingreso_bodega';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'fecha',
        'fecha_registro',
        'cantidad',
        'precio_costo',
    ];

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
    }
}
