<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetalleImportPedido extends Model
{
    protected $table = 'detalle_import_pedido';
    protected $primaryKey = 'id_detalle_import_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_import_pedido',
    ];

    public function pedido()
    {
        return $this->belongsTo('\yura\Modelos\ImportPedido', 'id_import_pedido');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function detalles_receta()
    {
        return $this->hasMany('\yura\Modelos\DistribucionReceta', 'id_detalle_import_pedido');
    }

    public function ordenes_trabajo()
    {
        return $this->hasMany('\yura\Modelos\OrdenTrabajo', 'id_detalle_import_pedido');
    }

    public function getCantidadRamosOT()
    {
        return DB::table('orden_trabajo')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_detalle_import_pedido', $this->id_detalle_import_pedido)
            ->get()[0]->cantidad;
    }
}
