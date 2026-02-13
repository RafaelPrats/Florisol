<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AplicacionMezcla extends Model
{
    protected $table = 'aplicacion_mezcla';
    protected $primaryKey = 'id_aplicacion_mezcla';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_aplicacion',
        'nombre',
        'litro_x_cama',
        'id_aplicacion_matriz',
    ];

    public function aplicacion()
    {
        return $this->belongsTo('\yura\Modelos\Aplicacion', 'id_aplicacion');
    }

    public function aplicacion_matriz()
    {
        return $this->belongsTo('\yura\Modelos\AplicacionMatriz', 'id_aplicacion_matriz');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleAplicacion', 'id_aplicacion_mezcla');
    }

    public function getDetalles()
    {
        return DB::table('detalle_aplicacion as da')
            ->leftJoin('mano_obra as mo', 'mo.id_mano_obra', '=', 'da.id_mano_obra')
            ->leftJoin('producto as p', 'p.id_producto', '=', 'da.id_producto')
            ->select('da.id_detalle_aplicacion', 'mo.nombre as mo', 'p.nombre as insumo')->distinct()
            ->where('da.id_aplicacion_mezcla', $this->id_aplicacion_mezcla)
            ->orderBy('mo.nombre')
            ->orderBy('p.nombre')
            ->get();
    }

    public function getModelDetalles()
    {
        return DetalleAplicacion::leftJoin('mano_obra as mo', 'mo.id_mano_obra', '=', 'detalle_aplicacion.id_mano_obra')
            ->leftJoin('producto as p', 'p.id_producto', '=', 'detalle_aplicacion.id_producto')
            ->select('detalle_aplicacion.*')->distinct()
            ->where('detalle_aplicacion.id_aplicacion_mezcla', $this->id_aplicacion_mezcla)
            ->orderBy('mo.nombre')
            ->orderBy('p.nombre')
            ->get();
    }
}