<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleProyectoConfirmacion extends Model
{
    protected $table = 'detalle_proyecto_confirmacion';
    protected $primaryKey = 'id_detalle_proyecto_confirmacion';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_variedad',
        'id_proyecto_confirmacion ',
        'tallos_pedido',
        'confirmados',
        'cambios_pedido',
    ];

    public function proyecto_confirmacion()
    {
        return $this->belongsTo('\yura\Modelos\ProyectoConfirmacion', 'id_proyecto_confirmacion');
    }

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }
}
