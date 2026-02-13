<?php

namespace yura\Http\Controllers;

use Illuminate\Http\Request;
use yura\Modelos\EnvioReporte;
use yura\Modelos\Submenu;
use yura\Modelos\Usuario;
use yura\Modelos\UsuariosEnvioReporte;

class EnvioReporteController extends Controller
{
    public function inicio(Request $request)
    {
        $reportes = EnvioReporte::orderBy('nombre_reporte')->get();
        return view('adminlte.gestion.envio_reporte.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'reportes' => $reportes,
        ]);
    }

    public function seleccionar_reporte(Request $request)
    {
        $usuarios = Usuario::where('estado', 'A')
            ->where('id_rol', '>', 2)
            ->orderBy('nombre_completo')
            ->get();
        $envio_reporte = EnvioReporte::find($request->reporte);
        $envio_reporte->dia_semana = $request->dia;
        $envio_reporte->hora = $request->hora;
        $envio_reporte->save();
        $usuarios_envio = [];
        foreach ($envio_reporte->usuarios as $u)
            $usuarios_envio[] = $u->id_usuario;
        return view('adminlte.gestion.envio_reporte.partials.listado_usuarios', [
            'usuarios' => $usuarios,
            'envio_reporte' => $envio_reporte,
            'usuarios_envio' => $usuarios_envio,
        ]);
    }

    public function seleccionar_usuario(Request $request)
    {
        $model = UsuariosEnvioReporte::All()
            ->where('id_usuario', $request->usuario)
            ->where('id_envio_reporte', $request->reporte)
            ->first();
        if ($model == '') {
            $model = new UsuariosEnvioReporte();
            $model->id_usuario = $request->usuario;
            $model->id_envio_reporte = $request->reporte;
            $model->save();
        } else
            $model->delete();

        return [
            'success' => true,
        ];
    }
}
