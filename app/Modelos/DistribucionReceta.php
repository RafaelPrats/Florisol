<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DistribucionReceta extends Model
{
    protected $table = 'distribucion_receta';
    protected $primaryKey = 'id_distribucion_receta';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_import_pedido',
        'id_variedad',
        'longitud',
        'unidades',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function detalle_import_pedido()
    {
        return $this->belongsTo('\yura\Modelos\DetalleImportPedido', 'id_detalle_import_pedido');
    }
}
