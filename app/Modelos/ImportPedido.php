<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImportPedido extends Model
{
    protected $table = 'import_pedido';
    protected $primaryKey = 'id_import_pedido';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'fecha',
        'codigo',
    ];

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleImportPedido', 'id_import_pedido');
    }

    public function getTotales()
    {
        return DB::table('detalle_import_pedido as d')
            ->select(DB::raw('sum(d.ramos * d.caja) as ramos'))
            ->where('d.id_import_pedido', $this->id_import_pedido)
            ->get()[0];
    }
}
