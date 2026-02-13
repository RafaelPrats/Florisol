<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PreOrdenTrabajo extends Model
{
    protected $table = 'pre_orden_trabajo';
    protected $primaryKey = 'id_pre_orden_trabajo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_import_pedido',
        'fecha',
        'estado',
        'longitud',
        'ramos',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetallePreOrdenTrabajo', 'id_pre_orden_trabajo');
    }

    public function detalle_import_pedido()
    {
        return $this->belongsTo('\yura\Modelos\DetalleImportPedido', 'id_detalle_import_pedido');
    }

    public function getTotalRamosParcial()
    {
        return DB::table('pre_orden_parcial')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_pre_orden_trabajo', $this->id_pre_orden_trabajo)
            ->get()[0]->cantidad;
    }
}