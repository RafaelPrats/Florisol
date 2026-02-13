<?php

namespace yura\Http\Controllers\rrhh;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Actividad;
use yura\Modelos\Area;
use yura\Modelos\ControlPersonal;
use yura\Modelos\ManoObra;
use yura\Modelos\Submenu;

class rrhhDashboardController extends Controller
{
    public function dias($cantDias = 27, $desde =null)
    {
        $hoy = isset($desde) ? Carbon::parse($desde) : now();

        for($i = $cantDias; $i >= 0; $i--) {
            $dia = $cantDias == $i ? $hoy : $hoy->subDay(1);
            $dias[] = $dia->toDateString();
            $hoy=$dia;
        }

        return $dias;
    }

    public function inicio(Request $request)
    {
        $desde = opDiasFecha('-', 28, now()->toDateString());
        $hasta = hoy();

        $sqlCantPersonas= "SELECT
        (
            CASE WHEN (SELECT COUNT(*) FROM personal_detalle AS pd1 where pd1.id_personal = pd.id_personal) > 1
            THEN
            (
                SELECT (
                    (
                        CASE WHEN pd2.fecha_desvinculacion IS NULL
                        THEN pd2.fecha_ingreso <= CAST(NOW() AS DATE)
                        ELSE pd2.fecha_desvinculacion BETWEEN ? AND ?
                        END
                    )
                ) FROM personal_detalle AS pd2 WHERE pd2.id_personal = pd.id_personal ORDER BY id_personal_detalle DESC LIMIT 1
            ) = true
            ELSE ( CASE WHEN ( SELECT fecha_ingreso FROM personal_detalle AS pd2 WHERE pd2.id_personal = pd.id_personal LIMIT 1) <= CAST(NOW() AS DATE) THEN true ELSE false END)
            END
        )
        AS suma
        FROM personal_detalle AS pd GROUP BY id_personal";

        $finca_actual = getFincaActiva();
        $cantPer4Sem = collect(DB::select($sqlCantPersonas,[$desde,$hasta]))->where('suma','>=', 1)->count();

        $finca = getFincaActiva();
        $areaM2 = getIndicadorByName('D7-' . $finca);
        $HA = (isset($areaM2) ? $areaM2->valor : 0) / 10000;
        $persPorHA =  $HA == 0 ? 0 : ($cantPer4Sem / $HA);

        $horasExtras4Sem = ControlPersonal::whereBetWeen('fecha', [$desde,$hasta])
        ->select(
            DB::raw("(
                CASE WHEN (desde < '08:00:00' OR hasta > '17:00:00') AND WEEKDAY(fecha) <= 4 -- 4 = VIERNES
                THEN
                    (
                        CASE WHEN (desde < '08:00:00' AND hasta > '17:00:00')
                        THEN EXTRACT(HOUR FROM TIMEDIFF(desde,'08:00:00')) + ( MINUTE(TIMEDIFF(desde,'08:00:00')) / 60 ) + EXTRACT(HOUR FROM TIMEDIFF(hasta,'17:00:00')) + ( MINUTE(TIMEDIFF(hasta,'17:00:00')) / 60 )
                        ELSE
                            (
                                CASE WHEN (desde < '08:00:00')
                                THEN EXTRACT(HOUR FROM TIMEDIFF(desde,'08:00:00')) + ( MINUTE(TIMEDIFF(desde,'08:00:00')) / 60 )
                                WHEN hasta > '17:00:00'
                                THEN EXTRACT(HOUR FROM TIMEDIFF(hasta,'17:00:00')) + ( MINUTE(TIMEDIFF(hasta,'17:00:00')) / 60 )
                                ELSE 0
                                END
                            )
                        END
                    )
                ELSE 0
                END
            ) AS horas_50"),
            DB::raw("(
                CASE WHEN WEEKDAY(fecha) >= 5  -- 5 , 6 = SABADO O DOMINGO
                THEN EXTRACT(HOUR FROM TIMEDIFF(hasta,desde)) + ( MINUTE(TIMEDIFF(hasta,desde)) / 60 )
                ELSE 0
                END
            ) AS horas_100")
        )->whereNotNull('hasta')->get();

        $semanDesde = getSemanaByDate(opDiasFecha('-', 21, now()->toDateString()));
        $semanHasta = getSemanaByDate(hoy());

        $resumenCostos = DB::table('resumen_costos_semanal')
        ->where('id_empresa', $finca)
        ->where('codigo_semana', '>=', $semanDesde->codigo)
        ->where('codigo_semana', '<=', $semanHasta->codigo)
        ->sum('mano_obra');

        $actividad = Actividad::where('actividad.id_empresa', $finca_actual)
        ->join('actividad_mano_obra as amo','actividad.id_actividad','amo.id_actividad')
        ->join('mano_obra as mo',function($j){
            $j->on('amo.id_mano_obra','mo.id_mano_obra')->where('mo.estado',true);
        })->select('amo.id_mano_obra')->get();

        return view('adminlte.gestion.rrhh.dashboard.inicio',[
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', substr($request->getRequestUri(), 1))->get()[0],
            'manoObra' => ManoObra::whereIn('id_mano_obra',$actividad->pluck('id_mano_obra'))->orderBy('nombre','asc')->get(),
            'persPorHA' => number_format($persPorHA,2),
            'horas50' => number_format($horasExtras4Sem->sum('horas_50'),2),
            'horas100' => number_format($horasExtras4Sem->sum('horas_100'),2),
            'costo_por_persona_4_sem' => $cantPer4Sem  ?  number_format($resumenCostos / $cantPer4Sem,2) : 0
        ]);
    }

    public function filtrar_graficas(Request $request)
    {
        $finca_actual = getFincaActiva();
        $desde = opDiasFecha('-', 21, hoy());
        $hasta = hoy();
        $semanDesde = getSemanaByDate($desde);
        $semanHasta = getSemanaByDate($hasta);

        $actividad = Actividad::where('actividad.id_empresa', $finca_actual)
        ->join('actividad_mano_obra as amo','actividad.id_actividad','amo.id_actividad')
        ->join('mano_obra as mo',function($j){
            $j->on('amo.id_mano_obra','mo.id_mano_obra')
            ->where('mo.estado',true);
        })->select('amo.id_mano_obra')->get();

        $manosObra = ManoObra::where(function($w) use ($request,$actividad){

            if(isset($request->labor) && $request->labor != ''){
                $w->where('mano_obra.id_mano_obra', $request->labor);
            }else{
                $w->whereIn('mano_obra.id_mano_obra', $actividad->pluck('id_mano_obra')->toArray());
            }

        })->select(
            "mano_obra.id_mano_obra", "mano_obra.nombre as labor",
            DB::raw(
                "(
                    (
                        (
                            SELECT COUNT(*) FROM personal_detalle AS pd WHERE pd.id_mano_obra = mano_obra.id_mano_obra AND pd.estado=true
                            AND pd.id_personal IN
                            (
                                (
                                    SELECT DISTINCT pd2.id_personal FROM personal_detalle AS pd2 LEFT JOIN control_personal AS cp
                                    ON pd2.id_personal_detalle = cp.id_personal_detalle
                                    WHERE pd2.estado = true AND cp.id_control_personal IS NULL
                                    AND cp.fecha BETWEEN ".opDiasFecha('-', 28, hoy())." AND ".$hasta."
                                )
                            )
                        )
                        /
                        (SELECT COUNT(*) FROM personal_detalle AS pd WHERE pd.id_mano_obra = mano_obra.id_mano_obra AND pd.estado=true)
                    ) * 100
                ) AS ausentismo"
            ),
            DB::raw(
                "(
                    CASE WHEN
                        NOT EXISTS(SELECT * FROM personal_detalle AS pd4 WHERE pd4.estado = true AND pd4.id_mano_obra = mano_obra.id_mano_obra)
                    THEN 0
                    ELSE
                        (
                            (
                                (
                                    (  SELECT  COUNT(DISTINCT id_personal) AS alguna_vez_salieron FROM personal_detalle AS pd WHERE pd.estado = false AND pd.id_mano_obra = mano_obra.id_mano_obra  ) -
                                    (
                                        SELECT COUNT(*) AS alguna_vez_salieron_pero_volvieron FROM personal_detalle AS pd2 WHERE pd2.estado = true AND pd2.id_mano_obra = mano_obra.id_mano_obra
                                        AND pd2.id_personal IN ( SELECT DISTINCT pd3.id_personal FROM personal_detalle AS pd3 WHERE pd3.estado = false AND pd3.id_mano_obra = mano_obra.id_mano_obra)
                                    )
                                )
                                /
                                (SELECT COUNT(*) AS estan_activos FROM personal_detalle AS pd4 WHERE pd4.estado = true AND pd4.id_mano_obra = mano_obra.id_mano_obra)
                            ) * 100
                        )
                    END
                ) AS rot_personal"
            ),
            DB::raw(
                "(
                    (
                        (
                            SELECT SUM(csmo.valor) FROM costos_semana_mano_obra AS csmo WHERE codigo_semana BETWEEN ".$semanDesde->codigo." AND ".$semanHasta->codigo."
                            AND id_empresa = ".$finca_actual." AND id_actividad_mano_obra = (SELECT id_actividad_mano_obra FROM actividad_mano_obra WHERE id_mano_obra = mano_obra.id_mano_obra LIMIT 1)
                        )
                        /
                        ( SELECT SUM(csmo.valor) FROM costos_semana_mano_obra AS csmo WHERE codigo_semana BETWEEN ".$semanDesde->codigo." AND ".$semanHasta->codigo." AND id_empresa = ".$finca_actual.")
                    ) * 100
                ) AS costo_mano_obra_labor"
            )
        )->orderBy('mano_obra.nombre','asc')->groupBy('mano_obra.id_mano_obra','mano_obra.nombre')->get();

        return view('adminlte.gestion.rrhh.dashboard.partials.graficas',[
            'manosObra' => $manosObra,
        ]);
    }

    public function deglose_indicador(Request $request)
    {
        $finca_actual = getFincaActiva();
        $semanDesde = getSemanaByDate(opDiasFecha('-', 21, now()->toDateString()));
        $semanHasta = getSemanaByDate(hoy());
        $sqlCantPersonas= "SELECT
            (
                CASE WHEN (SELECT COUNT(*) FROM personal_detalle AS pd1 where pd1.id_personal = pd.id_personal) > 1
                THEN
                (
                    SELECT (
                        (
                            CASE WHEN pd2.fecha_desvinculacion IS NULL
                            THEN pd2.fecha_ingreso <= CAST(NOW() AS DATE)
                            ELSE pd2.fecha_desvinculacion BETWEEN ? AND ?
                            END
                        )
                    ) FROM personal_detalle AS pd2 WHERE pd2.id_personal = pd.id_personal ORDER BY id_personal_detalle DESC LIMIT 1
                ) = true
                ELSE ( CASE WHEN ( SELECT fecha_ingreso FROM personal_detalle AS pd2 WHERE pd2.id_personal = pd.id_personal LIMIT 1) <= CAST(NOW() AS DATE) THEN true ELSE false END)
                END
            )
            AS suma
            FROM personal_detalle AS pd WHERE pd.id_mano_obra = ? GROUP BY pd.id_personal";

        $manoObras =  Actividad::where('actividad.id_empresa', $finca_actual)
        ->join('actividad_mano_obra as amo','actividad.id_actividad','amo.id_actividad')
        ->join('mano_obra as mo',function($j){
            $j->on('amo.id_mano_obra','mo.id_mano_obra')->where('mo.estado',true);
        })->select('mo.id_mano_obra','mo.nombre','actividad.nombre as actividad','amo.id_actividad_mano_obra')->orderBy('mo.nombre','asc')->get();

        if($request->indicador === 'persona_ha'){

            $manoObras->map(function($obj) use($sqlCantPersonas, $finca_actual){

                $arrCantidadSemana = [];
                $diaDesde = '';
                $areaM2 = getIndicadorByName('D7-' . $finca_actual);
                $HA = (isset($areaM2) ? $areaM2->valor : 0) / 10000;

                foreach([6,6,6,6] as $i){

                    $dias = $this->dias($i, $diaDesde == '' ? null : $diaDesde);
                    $cantPerSem = collect(DB::select($sqlCantPersonas,[$dias[count($dias)-1],$dias[0],$obj->id_mano_obra]))->where('suma','>=', 1)->count();
                    $persPorHA =  $HA == 0 ? 0 : ($cantPerSem / $HA);
                    $arrCantidadSemana[getSemanaByDate($dias[0])->codigo] =number_format($persPorHA,2);
                    $diaDesde = Carbon::parse($dias[count($dias)-1])->subDay(1)->toDateString();

                }

                $obj->cantidades_x_semana = $arrCantidadSemana;

            });

            return view('adminlte.gestion.rrhh.dashboard.partials.costo_persona_mano_obra_labor',[
                'manoObras' => $manoObras
            ]);

        }elseif($request->indicador === 'horas_extras'){

            $manoObras->map(function($obj) {

                $diaDesde = '';
                $arrHe50Sem=[];
                $arrHe100Sem=[];

                foreach([6,6,6,6] as $i){

                    $dias = $this->dias($i, $diaDesde == '' ? null : $diaDesde);

                    $horasExtrasSem = ControlPersonal::whereIn('fecha', $dias)
                    ->where('id_mano_obra',$obj->id_mano_obra)
                    ->select(
                        DB::raw("(
                            CASE WHEN (desde < '08:00:00' OR hasta > '17:00:00') AND WEEKDAY(fecha) <= 4 -- 4 = VIERNES
                            THEN
                                (
                                    CASE WHEN (desde < '08:00:00' AND hasta > '17:00:00')
                                    THEN EXTRACT(HOUR FROM TIMEDIFF(desde,'08:00:00')) + ( MINUTE(TIMEDIFF(desde,'08:00:00')) / 60 ) + EXTRACT(HOUR FROM TIMEDIFF(hasta,'17:00:00')) + ( MINUTE(TIMEDIFF(hasta,'17:00:00')) / 60 )
                                    ELSE
                                        (
                                            CASE WHEN (desde < '08:00:00')
                                            THEN EXTRACT(HOUR FROM TIMEDIFF(desde,'08:00:00')) + ( MINUTE(TIMEDIFF(desde,'08:00:00')) / 60 )
                                            WHEN hasta > '17:00:00'
                                            THEN EXTRACT(HOUR FROM TIMEDIFF(hasta,'17:00:00')) + ( MINUTE(TIMEDIFF(hasta,'17:00:00')) / 60 )
                                            ELSE 0
                                            END
                                        )
                                    END
                                )
                            ELSE 0
                            END
                        ) AS horas_50"),
                        DB::raw("(
                            CASE WHEN WEEKDAY(fecha) >= 5  -- 5 , 6 = SABADO O DOMINGO
                            THEN EXTRACT(HOUR FROM TIMEDIFF(hasta,desde)) + ( MINUTE(TIMEDIFF(hasta,desde)) / 60 )
                            ELSE 0
                            END
                        ) AS horas_100")
                    )->whereNotNull('hasta')->get();

                    $semana = getSemanaByDate($dias[0])->codigo;
                    $arrHe50Sem[$semana]= number_format($horasExtrasSem->sum('horas_50'),2);
                    $arrHe100Sem[$semana]= number_format($horasExtrasSem->sum('horas_100'),2);

                    $diaDesde = Carbon::parse($dias[count($dias)-1])->subDay(1)->toDateString();
                }

                $obj->he_50_semana = $arrHe50Sem;
                $obj->he_100_semana = $arrHe100Sem;

            });

            return view('adminlte.gestion.rrhh.dashboard.partials.horas_extras_labor',[
                'manoObras' =>$manoObras
            ]);

        }else{

            $manoObras->map(function($obj) use($sqlCantPersonas){

                $arrCantidadSemana = [];
                $fecha = hoy();

                foreach(range(0,3) as $i){

                    $semana = getSemanaByDate($fecha)->codigo;

                    $res= DB::table('costos_semana_mano_obra')
                    ->where([
                        ['codigo_semana', $semana],
                        ['id_actividad_mano_obra', $obj->id_actividad_mano_obra]
                    ])->select(DB::raw("SUM(valor) as valor"))->first();

                    $cantPerSem = collect(DB::select($sqlCantPersonas,[opDiasFecha('-', 7, $fecha),$fecha,$obj->id_mano_obra]))->where('suma','>=', 1)->count();

                    $arrCantidadSemana[$semana] = $cantPerSem  ?  number_format( (isset($res) ? ($res->valor/$cantPerSem) : '0.00' ),2,'.','') : '0.00';
                    $fecha = opDiasFecha('-', 7, $fecha);

                }

                $obj->cantidades_x_semana = $arrCantidadSemana;

            });

            return view('adminlte.gestion.rrhh.dashboard.partials.persona_por_ha_labor',[
                'manoObras' => $manoObras
            ]);

        }

    }

}


