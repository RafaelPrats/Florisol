<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;

class AplicacionCampo extends Model
{
    protected $table = 'aplicacion_campo';
    protected $primaryKey = 'id_aplicacion_campo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion',
        'id_ciclo',
        'fecha',
        'repeticion',
        'litro_x_cama',
        'camas',
        'horas_dia',
        'plantas',
        'hombres_dia',
        'horas_necesarias',
    ];

    public function ciclo()
    {
        return $this->belongsTo('\yura\Modelos\Ciclo', 'id_ciclo');
    }

    public function aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\Aplicacion', 'id_aplicacion');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleAplicacionCampo', 'id_aplicacion_campo');
    }

    public function getDetalleByProducto($prod)
    {
        return DetalleAplicacionCampo::All()
            ->where('id_aplicacion_campo', $this->id_aplicacion_campo)
            ->where('id_producto', $prod)
            ->first();
    }

    public function getDetalleByManoObra($mo)
    {
        return DetalleAplicacionCampo::All()
            ->where('id_aplicacion_campo', $this->id_aplicacion_campo)
            ->where('id_mano_obra', $mo)
            ->first();
    }
}
