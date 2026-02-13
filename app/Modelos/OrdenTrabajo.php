<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    protected $table = 'orden_trabajo';
    protected $primaryKey = 'id_orden_trabajo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_detalle_import_pedido',
        'fecha',
        'longitud',
        'ramos',
        'entregado',
        'armado',
        'id_empresa',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleOrdenTrabajo', 'id_orden_trabajo');
    }

    public function detalle_import_pedido()
    {
        return $this->belongsTo('\yura\Modelos\DetalleImportPedido', 'id_detalle_import_pedido');
    }

    public function despachador()
    {
        return $this->belongsTo('\yura\Modelos\Despachador', 'id_despachador');
    }

    public function getEstado()
    {
        if ($this->entregado == 0 && $this->armado == 0) {
            return [
                'estado' => 'Pendiente',
                'html' => '<span class="badge" style="background-color: #ef6e11">Pendiente</span>',
            ];
        } else if ($this->entregado == 1 && $this->armado == 0) {
            return [
                'estado' => 'Despachado',
                'html' => '<span class="badge bg-yura_dark"><i class="fa fa-fw fa-check"></i> Despachado</span>',
            ];
        } else if (($this->entregado == 1 && $this->armado == 1) || ($this->entregado == 0 && $this->armado == 1)) {
            return [
                'estado' => 'Armado',
                'html' => '<span class="badge bg-yura_primary">Armado</span>',
            ];
        }
    }
}
