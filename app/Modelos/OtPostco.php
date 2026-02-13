<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class OtPostco extends Model
{
    protected $table = 'ot_postco';
    protected $primaryKey = 'id_ot_postco';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_postco',
        'fecha',
        'longitud',
        'ramos',
        'id_despachador',
        'armados',
        'estado',
        'id_cliente',
    ];

    public function postco()
    {
        return $this->belongsTo('\yura\Modelos\Postco', 'id_postco');
    }

    public function cliente()
    {
        return $this->belongsTo('\yura\Modelos\Cliente', 'id_cliente');
    }

    public function despachador()
    {
        return $this->belongsTo('\yura\Modelos\Despachador', 'id_despachador');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleOtPostco', 'id_ot_postco');
    }

    public function reclamos()
    {
        return $this->hasMany('\yura\Modelos\OtReclamo', 'id_ot_postco');
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
