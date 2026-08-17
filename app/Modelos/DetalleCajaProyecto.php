<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetalleCajaProyecto extends Model
{
    protected $table = 'detalle_caja_proyecto';
    protected $primaryKey = 'id_detalle_caja_proyecto';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_caja_proyecto',
        'id_variedad',
        'ramos_x_caja',
        'tallos_x_ramo',
        'precio',
        'longitud_ramo',
    ];

    public function variedad()
    {
        return $this->belongsTo('\yura\Modelos\Variedad', 'id_variedad');
    }

    public function caja_proyecto()
    {
        return $this->belongsTo('\yura\Modelos\CajaProyecto', 'id_caja_proyecto');
    }

    public function distribuciones()
    {
        return $this->hasMany('\yura\Modelos\DistribucionReceta', 'id_detalle_caja_proyecto');
    }

    public function getFecha()
    {
        return $this->caja_proyecto->proyecto->fecha;
    }

    public function ot_nacional()
    {
        return $this->hasMany('\yura\Modelos\OtNacional', 'id_detalle_caja_proyecto');
    }

    public function getRamosOt()
    {
        $r = DB::table('orden_trabajo')
            ->select(DB::raw('sum(ramos) as cantidad'))
            ->where('id_detalle_caja_proyecto', $this->id_detalle_caja_proyecto)
            ->get()[0]->cantidad;
        return $r > 0 ? $r : 0;
    }

    public function getOtNacional()
    {
        return DB::table('ot_nacional as ot')
            ->join('variedad as var_dist', 'var_dist.id_variedad', '=', 'ot.id_variedad_dist')
            ->join('planta as pta_dist', 'pta_dist.id_planta', '=', 'var_dist.id_planta')
            ->join('variedad as var', 'var.id_variedad', '=', 'ot.id_variedad')
            ->join('planta as pta', 'pta.id_planta', '=', 'var.id_planta')
            ->select(
                'ot.id_ot_nacional',
                'ot.distribucion_pos',
                'ot.id_variedad_dist',
                'var_dist.nombre as var_dist_nombre',
                'pta_dist.nombre as pta_dist_nombre',
                'ot.longitud_dist',
                'ot.unidades_dist',
                'ot.total_tallos_dist',
                'ot.id_variedad',
                'var.nombre as var_nombre',
                'pta.nombre as pta_nombre',
                'ot.longitud',
                'ot.tallos',
                'ot.fecha',
            )
            ->distinct()
            ->where('ot.id_detalle_caja_proyecto', $this->id_detalle_caja_proyecto)
            ->orderBy('ot.distribucion_pos')
            ->orderBy('pta.nombre')
            ->orderBy('var.nombre')
            ->get()
            ->groupBy('distribucion_pos')
            ->map(function ($items, $pos) {
                return [
                    'pos' => $pos,
                    'fecha' => $items->first()->fecha,
                    'id_variedad_dist' => $items->first()->id_variedad_dist,
                    'pta_dist_nombre' => $items->first()->pta_dist_nombre,
                    'var_dist_nombre' => $items->first()->var_dist_nombre,
                    'longitud_dist' => $items->first()->longitud_dist,
                    'unidades_dist' => $items->first()->unidades_dist,
                    'total_tallos_dist' => $items->first()->total_tallos_dist,
                    'detalles' => $items->values(),
                ];
            })
            ->values();
    }
}
