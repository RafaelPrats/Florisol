<?php

namespace yura\Http\Controllers\Campo;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\AplicacionCampo;
use yura\Modelos\AplicacionMatriz;
use yura\Modelos\Sector;
use yura\Modelos\Submenu;

class EjecucionLaboresController extends Controller
{
    public function inicio(Request $request)
    {
        $semana_actual = getSemanaByDate(hoy());
        $sectores = Sector::where('estado', 1)
            ->where('id_empresa', getFincaActiva())
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.campo.ejecucion_labores.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'semana_actual' => $semana_actual,
            'sectores' => $sectores,
        ]);
    }

    /* ----------------------- LISTAR -------------------------- */
    public function listar_reporte(Request $request)
    {
        $app_matriz = AplicacionMatriz::find($request->labor);
        if ($app_matriz->nombre == 'ACIDO GIBERELICO')
            return view(
                'adminlte.gestion.campo.ejecucion_labores.partials.listado_giberelico',
                self::listar_acido_giberelico($request, $app_matriz)
            );
        if ($app_matriz->nombre == 'DESBROTE')
            return view(
                'adminlte.gestion.campo.reporte_labores.partials.listado_desbrote',
                //self::listar_desbrote($request, $app_matriz)
            );
    }

    static function listar_acido_giberelico($request, $app_matriz)
    {
        $semana_req = getObjSemana($request->semana);
        $ids_app = [];
        foreach ($app_matriz->aplicaciones as $app)
            array_push($ids_app, $app->id_aplicacion);
        $ejecutados = AplicacionCampo::join('ciclo as c', 'c.id_ciclo', '=', 'aplicacion_campo.id_ciclo')
            ->select('aplicacion_campo.*')->distinct()
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->whereIn('aplicacion_campo.id_aplicacion', $ids_app)
            ->where('m.id_sector', $request->sector)
            ->where('aplicacion_campo.ejecutado', 1)
            ->where('aplicacion_campo.id_empresa', getFincaActiva())
            ->where('aplicacion_campo.fecha', '>=', $semana_req->fecha_inicial)
            ->where('aplicacion_campo.fecha', '<=', $semana_req->fecha_final)
            ->orderBy('aplicacion_campo.fecha')
            ->get();
        $sin_ejecutar = AplicacionCampo::join('ciclo as c', 'c.id_ciclo', '=', 'aplicacion_campo.id_ciclo')
            ->select('aplicacion_campo.*')->distinct()
            ->join('modulo as m', 'm.id_modulo', '=', 'c.id_modulo')
            ->whereIn('aplicacion_campo.id_aplicacion', $ids_app)
            ->where('m.id_sector', $request->sector)
            ->where('aplicacion_campo.ejecutado', 0)
            ->where('aplicacion_campo.id_empresa', getFincaActiva())
            ->where('aplicacion_campo.fecha', '>=', $semana_req->fecha_inicial)
            ->where('aplicacion_campo.fecha', '<=', $semana_req->fecha_final)
            ->orderBy('aplicacion_campo.fecha')
            ->get();

        return [
            'ejecutados' => $ejecutados,
            'sin_ejecutar' => $sin_ejecutar,
            'semana_req' => $semana_req,
            'app_matriz' => $app_matriz,
        ];
    }
}
