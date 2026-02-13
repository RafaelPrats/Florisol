<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class ProductoVariedad extends Model
{
    protected $table = 'producto_variedad';
    protected $primaryKey = 'id_producto_variedad';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_producto',
        'unidades',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function producto()
    {
        return $this->belongsTo('\yura\Modelos\Producto', 'id_producto');
    }
}
