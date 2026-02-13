<?php

namespace yura\Http\Controllers\Postco;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use yura\Http\Controllers\Controller;
use yura\Modelos\ColorPropuesta;
use yura\Modelos\Planta;
use yura\Modelos\SeasonPropuesta;
use yura\Modelos\Submenu;
use Validator;
use yura\Modelos\CajaPropuesta;
use yura\Modelos\ClientePropuesta;
use yura\Modelos\DetallePropuesta;
use yura\Modelos\Propuesta;

class PropuestaController extends Controller
{
    public function inicio(Request $request)
    {
        $colores = DB::table('color_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $seasons = DB::table('season_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $plantas = Planta::where('estado', 1)
            ->where('id_planta', '!=', 151)
            ->where('id_planta', '!=', 128)
            ->orderBy('nombre')
            ->get();
        return view('adminlte.gestion.postco.propuestas.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
            'colores' => $colores,
            'seasons' => $seasons,
            'plantas' => $plantas,
        ]);
    }

    public function listar_reporte(Request $request)
    {
        $listado = Propuesta::join('detalle_propuesta as d', 'd.id_propuesta', '=', 'propuesta.id_propuesta')
            ->join('variedad as v', 'v.id_variedad', '=', 'd.id_variedad')
            ->leftJoin('color_propuesta as c', 'c.id_propuesta', '=', 'propuesta.id_propuesta')
            ->leftJoin('season_propuesta as s', 's.id_propuesta', '=', 'propuesta.id_propuesta')
            ->select('propuesta.*')->distinct();
        $busqueda = $request->has('busqueda') ? espacios($request->busqueda) : '';
        $bus = mb_strtoupper(str_replace(' ', '%%', $busqueda));
        if ($request->busqueda != '')
            $listado = $listado->Where(function ($q) use ($bus) {
                $q->Where('nombre', 'like', '%' . $bus . '%');
            });
        if ($request->color != '')
            $listado = $listado->where('c.nombre', $request->color);
        if ($request->season != '')
            $listado = $listado->where('s.nombre', $request->season);
        if ($request->planta != '')
            $listado = $listado->where('v.id_planta', $request->planta);
        if ($request->variedad != '')
            $listado = $listado->where('d.id_variedad', $request->variedad);
        $listado = $listado->orderBy('id_propuesta')->get();

        return view('adminlte.gestion.postco.propuestas.partials.listado', [
            'listado' => $listado
        ]);
    }

    public function add_propuesta(Request $request)
    {
        $plantas = Planta::where('estado', '=', 1)->orderBy('nombre')->get();
        $colores = DB::table('color_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $seasons = DB::table('season_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $clientes = DB::table('cliente_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $cajas = DB::table('caja_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        return view('adminlte.gestion.postco.propuestas.forms.add_propuesta', [
            'plantas' => $plantas,
            'colores' => $colores,
            'seasons' => $seasons,
            'clientes' => $clientes,
            'cajas' => $cajas,
        ]);
    }

    public function store_propuesta(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha' => 'required',
            'mo' => 'required',
            'longitud' => 'required',
            'packing' => 'required',
            'new_imagen' => 'mimes:jpg,jpeg,png|max:2048',
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'longitud.required' => 'La longitud es obligatoria',
            'mo.required' => 'El costo de mano de obra es obligatorio',
            'packing.required' => 'El packing es obligatorio',
            'new_imagen.mimes' => 'La imagen debe ser .jpg .jpeg o .png',
            'new_imagen.max' => 'La imagen debe pesar menos de 2MB',
        ]);
        if (!$valida->fails()) {
            try {
                DB::beginTransaction();
                $propuesta = new Propuesta();
                $propuesta->fecha = $request->fecha;
                $propuesta->costo_mano_obra = $request->mo;
                $propuesta->longitud = $request->longitud;
                $propuesta->packing = $request->packing;
                $propuesta->save();
                $id = DB::table('propuesta')
                    ->select(DB::raw('max(id_propuesta) as id'))
                    ->get()[0]->id;
                $propuesta->id_propuesta = $id;
                bitacora('propuesta', $id, 'I', 'Nueva Propuesta');

                foreach (json_decode($request->data_variedades) as $det) {
                    $detalle = new DetallePropuesta();
                    $detalle->id_variedad = $det->id_variedad;
                    $detalle->unidades = $det->unidades;
                    $detalle->precio = $det->precio;
                    $detalle->id_propuesta = $id;
                    $detalle->save();
                }

                foreach (json_decode($request->data_colores) as $c) {
                    $color = new ColorPropuesta();
                    $color->nombre = mb_strtoupper($c);
                    $color->id_propuesta = $id;
                    $color->save();
                }

                foreach (json_decode($request->data_seasons) as $c) {
                    $season = new SeasonPropuesta();
                    $season->nombre = mb_strtoupper($c);
                    $season->id_propuesta = $id;
                    $season->save();
                }

                foreach (json_decode($request->data_clientes) as $c) {
                    $cliente = new ClientePropuesta();
                    $cliente->nombre = mb_strtoupper($c);
                    $cliente->id_propuesta = $id;
                    $cliente->save();
                }

                foreach (json_decode($request->data_cajas) as $c) {
                    $caja = new CajaPropuesta();
                    $caja->nombre = mb_strtoupper($c);
                    $caja->id_propuesta = $id;
                    $caja->save();
                }

                if ($request->hasFile('new_imagen')) {
                    $archivo = $request->file('new_imagen');
                    $extension = $archivo->getClientOriginalExtension();
                    $imagen = "propuesta_" . $id . "." . $extension;
                    $path = \public_path('images/propuesta');
                    $r1 = $request->file('new_imagen')->move($path, $imagen);
                    if (!$r1) {
                        DB::rollBack();
                        return [
                            'mensaje' => '<div class="alert alert-danger text-center">' .
                                '<p>¡No se pudo subir la imagen!</p>' .
                                '</div>',
                            'success' => false
                        ];
                    } else {
                        $propuesta->imagen = $imagen;
                        $propuesta->nombre = 'P-' . $id;
                        $propuesta->save();
                    }
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la propuesta correctamente';
            } catch (\Exception $e) {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                    '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function update_propuesta(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fecha' => 'required',
            'mo' => 'required',
            'longitud' => 'required',
            'packing' => 'required',
            'edit_imagen' => 'mimes:jpg,jpeg,png|max:2048',
        ], [
            'fecha.required' => 'La fecha es obligatoria',
            'longitud.required' => 'La longitud es obligatoria',
            'mo.required' => 'El costo de mano de obra es obligatorio',
            'packing.required' => 'El packing es obligatorio',
            'edit_imagen.mimes' => 'La imagen debe ser .jpg .jpeg o .png',
            'edit_imagen.max' => 'La imagen debe pesar menos de 2MB',
        ]);
        if (!$valida->fails()) {
            try {
                DB::beginTransaction();
                $propuesta = Propuesta::find($request->id_propuesta);
                $id = $request->id_propuesta;
                DB::select('delete from detalle_propuesta where id_propuesta = ' . $id);
                DB::select('delete from color_propuesta where id_propuesta = ' . $id);
                DB::select('delete from season_propuesta where id_propuesta = ' . $id);
                DB::select('delete from cliente_propuesta where id_propuesta = ' . $id);
                DB::select('delete from caja_propuesta where id_propuesta = ' . $id);

                $propuesta->costo_mano_obra = $request->mo;
                $propuesta->longitud = $request->longitud;
                $propuesta->fecha = $request->fecha;
                $propuesta->packing = $request->packing;
                $propuesta->save();
                bitacora('propuesta', $id, 'U', 'Modificar Propuesta');

                foreach (json_decode($request->data_variedades) as $det) {
                    $detalle = new DetallePropuesta();
                    $detalle->id_variedad = $det->id_variedad;
                    $detalle->unidades = $det->unidades;
                    $detalle->precio = $det->precio;
                    $detalle->id_propuesta = $id;
                    $detalle->save();
                }

                foreach (json_decode($request->data_colores) as $c) {
                    $color = new ColorPropuesta();
                    $color->nombre = mb_strtoupper($c);
                    $color->id_propuesta = $id;
                    $color->save();
                }

                foreach (json_decode($request->data_seasons) as $c) {
                    $season = new SeasonPropuesta();
                    $season->nombre = mb_strtoupper($c);
                    $season->id_propuesta = $id;
                    $season->save();
                }

                foreach (json_decode($request->data_clientes) as $c) {
                    $cliente = new ClientePropuesta();
                    $cliente->nombre = mb_strtoupper($c);
                    $cliente->id_propuesta = $id;
                    $cliente->save();
                }

                foreach (json_decode($request->data_cajas) as $c) {
                    $caja = new CajaPropuesta();
                    $caja->nombre = mb_strtoupper($c);
                    $caja->id_propuesta = $id;
                    $caja->save();
                }

                if ($request->hasFile('edit_imagen')) {
                    // borar imagen anterior
                    $nombre = $propuesta->imagen;
                    $ruta = \public_path('images/propuesta/' . $nombre);
                    if (file_exists($ruta) && $nombre != '') {
                        unlink($ruta);
                    }

                    $archivo = $request->file('edit_imagen');
                    $extension = $archivo->getClientOriginalExtension();
                    $imagen = "propuesta_" . $id . "." . $extension;
                    $path = \public_path('images/propuesta');
                    $r1 = $request->file('edit_imagen')->move($path, $imagen);
                    if (!$r1) {
                        DB::rollBack();
                        return [
                            'mensaje' => '<div class="alert alert-danger text-center">' .
                                '<p>¡No se pudo subir la imagen!</p>' .
                                '</div>',
                            'success' => false
                        ];
                    } else {
                        $propuesta->imagen = $imagen;
                        $propuesta->nombre = 'P-' . $id;
                        $propuesta->save();
                    }
                }

                DB::commit();
                $success = true;
                $msg = 'Se ha <strong>GRABADO</strong> la propuesta correctamente';
            } catch (\Exception $e) {
                DB::rollBack();
                $success = false;
                $msg = '<div class="alert alert-danger text-center">' .
                    '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                    '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                    . '</div>';
            }
        } else {
            $success = false;
            $errores = '';
            foreach ($valida->errors()->all() as $mi_error) {
                if ($errores == '') {
                    $errores = '<li>' . $mi_error . '</li>';
                } else {
                    $errores .= '<li>' . $mi_error . '</li>';
                }
            }
            $msg = '<div class="alert alert-danger">' .
                '<p class="text-center">¡Por favor corrija los siguientes errores!</p>' .
                '<ul>' .
                $errores .
                '</ul>' .
                '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function delete_propuesta(Request $request)
    {
        try {
            DB::beginTransaction();
            $propuesta = Propuesta::find($request->id);
            $propuesta->delete();

            DB::commit();
            $success = true;
            $msg = 'Se ha <strong>ELIMINADO</strong> la propuesta correctamente';
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $msg = '<div class="alert alert-danger text-center">' .
                '<p> Ha ocurrido un problema al guardar la informacion al sistema</p>' .
                '<p>' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . '</p>'
                . '</div>';
        }

        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function editar_propuesta(Request $request)
    {
        $propuesta = Propuesta::find($request->id);
        $plantas = Planta::where('estado', '=', 1)->orderBy('nombre')->get();
        $colores = DB::table('color_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $seasons = DB::table('season_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $clientes = DB::table('cliente_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        $cajas = DB::table('caja_propuesta')
            ->select('nombre')->distinct()
            ->orderBy('nombre')
            ->get()->pluck('nombre')->toArray();
        return view('adminlte.gestion.postco.propuestas.forms.editar_propuesta', [
            'propuesta' => $propuesta,
            'detalles' => $propuesta->detalles,
            'plantas' => $plantas,
            'colores' => $colores,
            'seasons' => $seasons,
            'clientes' => $clientes,
            'cajas' => $cajas,
        ]);
    }

    public function abrirGaleria(Request $request)
    {
        $propuesta = Propuesta::find($request->id);
        return view('adminlte.gestion.postco.propuestas.partials.abrirGaleria', [
            'propuesta' => $propuesta,
        ]);
    }
}
