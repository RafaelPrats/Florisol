<?php

namespace yura\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CosechaPersonal extends Model
{
    protected $table = 'cosecha_personal';
    protected $primaryKey = 'id_cosecha_personal';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_cosecha',
        'nombre_grupo',
        'personal',
        'id_empresa',
        'hora_inicio',
        'variedades',
    ];

    public function cosecha()
    {
        return $this->belongsTo('\yura\Modelos\Cosecha', 'id_cosecha');
    }

    public function empresa()
    {
        return $this->belongsTo('\yura\Modelos\ConfiguracionEmpresa', 'id_empresa');
    }

    public function getRendimiento()
    {
        if ($this->getCantidadHorasTrabajo() > 0) {
            $r = $this->personal > 0 ? $this->getTotalTallos() / $this->personal : 0;
            return round($r / $this->getCantidadHorasTrabajo(), 2);
        } else
            return 0;
    }

    public function getTotalTallos()
    {
        $ids_plantas = explode('|', $this->variedades);
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->whereIn('v.id_planta', $ids_plantas)
            ->where('dr.id_empresa', $this->id_empresa)
            ->get()[0]->cantidad;
        return $r > 0 ? $r : 0;
    }

    public function getCantidadHorasTrabajo()
    {
        $ids_plantas = explode('|', $this->variedades);
        $last_ingreso = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(DB::raw('max(dr.fecha_registro) as fecha'))
            ->where('dr.estado', '=', 1)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->whereIn('v.id_planta', $ids_plantas)
            ->where('dr.id_empresa', $this->id_empresa)
            ->get();

        if ($last_ingreso[0]->fecha != '') {
            $horas = difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio)->h;
            $horas += round(difFechas($last_ingreso[0]->fecha, $this->fecha_ingreso . ' ' . $this->hora_inicio, 2)->i / 60);

            return $horas;
        } else {
            return 0;
        }
    }

    public function getIntervalosHoras()
    {
        $ids_plantas = explode('|', $this->variedades);
        $r = [];
        $listado_fechas = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select('r.fecha_ingreso as fecha')->distinct()
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $this->id_empresa)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->whereIn('v.id_planta', $ids_plantas)
            ->orderBy('r.fecha_ingreso')
            ->get();

        $listado = [];
        foreach ($listado_fechas as $item)
            array_push($listado, $item->fecha);

        foreach ($listado as $item) {
            $intervalo = [
                'fecha_inicio' => substr($item, 0, 10),
                'fecha_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 0, 10),
                'fecha_inicio_full' => substr($item, 0, 13) . ':00',
                'fecha_fin_full' => opHorasFecha('+', 1, substr($item, 0, 13) . ':00'),
                'hora_inicio' => substr($item, 11, 2) . ':00',
                'hora_fin' => substr(opHorasFecha('+', 1, substr($item, 0, 13) . ':00'), 11, 2) . ':00',
            ];
            if (!in_array($intervalo, $r)) {
                array_push($r, $intervalo);
            }
        }

        return $r;
    }

    public function getDetallesByIntervalo($inicio, $fin)
    {
        $ids_plantas = explode('|', $this->variedades);
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'), 'dr.id_variedad', 'dr.id_modulo')
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $this->id_empresa)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->whereIn('v.id_planta', $ids_plantas)
            ->groupBy('dr.id_variedad', 'dr.id_modulo')
            ->get();

        return $r;
    }

    public function getTotalTallosByIntervalo($inicio, $fin)
    {
        $ids_plantas = explode('|', $this->variedades);
        $r = DB::table('desglose_recepcion as dr')
            ->join('recepcion as r', 'r.id_recepcion', '=', 'dr.id_recepcion')
            ->join('variedad as v', 'v.id_variedad', '=', 'dr.id_variedad')
            ->select(DB::raw('sum(dr.cantidad_mallas * dr.tallos_x_malla) as cantidad'))
            ->where('r.estado', '=', 1)
            ->where('dr.estado', '=', 1)
            ->where('dr.id_empresa', '=', $this->id_empresa)
            ->where('r.id_cosecha', '=', $this->id_cosecha)
            ->where('r.fecha_ingreso', '>=', $inicio)
            ->where('r.fecha_ingreso', '<', $fin)
            ->whereIn('v.id_planta', $ids_plantas)
            ->get()[0];

        return $r->cantidad;
    }

}
