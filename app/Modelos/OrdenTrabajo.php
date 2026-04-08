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
        'id_detalle_caja_proyecto',
        'fecha',
        'longitud',
        'ramos',
        'estado',
        'armados',
        'id_despachador',
        'id_cliente',
        'observacion',
    ];

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleOrdenTrabajo', 'id_orden_trabajo');
    }

    public function detalle_caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\DetalleCajaProyecto', 'id_detalle_caja_proyecto');
    }

    public function despachador()
    {
        return $this->belongsTo('\yura\Modelos\Despachador', 'id_despachador');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function getEstado()
    {
        if ($this->estado == 'P') {
            return [
                'estado' => 'Pendiente',
                'html' => '<span class="badge" style="background-color: #ef6e11">Pendiente</span>',
            ];
        } else if ($this->estado == 'D') {
            return [
                'estado' => 'Despachado',
                'html' => '<span class="badge bg-yura_dark"><i class="fa fa-fw fa-check"></i> Despachado</span>',
            ];
        } else if ($this->estado == 'A') {
            return [
                'estado' => 'Armado',
                'html' => '<span class="badge bg-yura_primary">Armado</span>',
            ];
        }
    }
}
