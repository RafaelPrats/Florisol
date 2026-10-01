<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'orden_compra';
    protected $primaryKey = 'id_orden_compra';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_proyecto',
        'estado',
        'fecha',
        'id_usuario',
    ];

    public function proyecto()
    {
        return $this->belongsTo('\yura\Modelos\Proyecto', 'id_proyecto');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleOrdenCompra', 'id_orden_compra');
    }

    public function usuario()
    {
        return $this->belongsTo('\yura\Modelos\Usuario', 'id_usuario');
    }
}
