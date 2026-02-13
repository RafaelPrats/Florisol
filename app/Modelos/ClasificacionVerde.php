<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClasificacionVerde extends Model
{
    protected $table = 'clasificacion_verde';
    protected $primaryKey = 'id_clasificacion_verde';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fecha_registro',
        'estado',
        'fecha',
        'id_empresa',
    ];

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function detalles()
    {
        return $this->hasMany('\yura\Modelos\DetalleClasificacionVerde', 'id_clasificacion_verde');
    }

    public function tallosByVariedad($variedad){
        $r = DB::table('detalle_clasificacion_verde')
            ->select(DB::raw('sum(cantidad_ramos * tallos_x_ramos) as cantidad'))
            ->where('id_clasificacion_verde', $this->id_clasificacion_verde)
            ->where('id_variedad', $variedad)
            ->get()[0]->cantidad;
        return $r;
    }
}