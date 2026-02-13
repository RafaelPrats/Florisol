<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleReceta extends Model
{
    protected $table = 'detalle_receta';
    protected $primaryKey = 'id_detalle_receta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_item',
        'longitud',
        'unidades',
        'precio',
    ];

    public function item()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_item');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
