<?php

namespace yura\Http\Controllers\RRHH;

use Illuminate\Http\Request;
use yura\Http\Controllers\Controller;
use yura\Modelos\Ausentismos;
use yura\Modelos\Banco;
use yura\Modelos\Cargo;
use yura\Modelos\Profesion;
use yura\Modelos\Tipo_rol;
use yura\Modelos\CausaDesvinculacion;
use yura\Modelos\TipoPago;
use yura\Modelos\TipoContrato;
use yura\Modelos\EstructuraOrganizativa;
use yura\Modelos\Grupo;
use yura\Modelos\Departamento;
use yura\Modelos\Sucursal;
use yura\Modelos\GrupoInterno;
use yura\Modelos\GradoInstruccion;
use yura\Modelos\Agrupacion;
use yura\Modelos\Plantilla;
use yura\Modelos\Personal;
use yura\Modelos\PersonalDetalle;

use yura\Modelos\Submenu;

class rrhhParametrosController extends Controller
{
    public function inicio(Request $request)
    {
        return view('adminlte.gestion.rrhh.parametros.inicio', [
            'url' => $request->getRequestUri(),
            'submenu' => Submenu::Where('url', '=', substr($request->getRequestUri(), 1))->get()[0],
        ]);
    }

    public function listar_parametro(Request $request)
    {
        if ($request->tipo == 'ausentismos') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_ausentismos', [
                'listado' => Ausentismos::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'banco') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_bancos', [
                'listado' => Banco::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'cargo') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_cargos', [
                'listado' => Cargo::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'profesion') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_profesiones', [
                'listado' => Profesion::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'tipo_rol') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_tipo_rol', [
                'listado' => Tipo_rol::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'causa_desvinculacion') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_causa_desvinculacion', [
                'listado' => CausaDesvinculacion::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'tipo_pago') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_tipo_pago', [
                'listado' => TipoPago::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'tipo_contrato') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_tipo_contrato', [
                'listado' => TipoContrato::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'estructura_organizativa') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_estructura_organizativa', [
                'listado' => EstructuraOrganizativa::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'grupo') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_grupo', [
                'listado' => Grupo::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'departamento') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_departamento', [
                'listado' => Departamento::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'sucursal') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_sucursal', [
                'listado' => Sucursal::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'grupo_interno') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_grupo_interno', [
                'listado' => GrupoInterno::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'grado_instruccion') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_grado_instruccion', [
                'listado' => GradoInstruccion::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'agrupacion') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_agrupacion', [
                'listado' => Agrupacion::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'plantilla') {
            return view('adminlte.gestion.rrhh.parametros.partials.listado_plantilla', [
                'listado' => Plantilla::All()->sortBy('nombre')
            ]);
        }
        if ($request->tipo == 'sueldo') {
            return view('adminlte.gestion.rrhh.parametros.partials.salario', [
                'listado' => PersonalDetalle::All()
            ]);
        }
        
        
        
        
        return '';
    }
   
   
    /* /////////////////   B A N C O //////////////////////////////*/



    public function store_banco(Request $request)
    {
 //dd($request->all());
        $model = new Banco();
        $model->nombre = $request->nombre;
        if ($model->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado el banco satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function actualizarEstadoBanco(Request $request)
    {
        $model = Banco::find($request->id_banco);
        if ($model != '') {
            $model->estado = $model->estado == 1 ? 0 : 1;
            if ($model->save()) {
                bitacora('banco', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

                return [
                    'success' => true,
                    'estado' => $model->estado == 1 ? true : false,
                    'mensaje' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => '',
                    'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
                ];
            }
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
            ];
        }
    }

    public function editar_banco(Request $request){
        dd($request->all());
       
        $banco = Banco::find($request->id_banco);
        $banco->nombre = $request->nombre;
        if ($banco->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha modificado el banco satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
    
    
      /* /////////////////  AUSENTISMO //////////////////////////////*/
   
   public function store_ausentismo(Request $request)
   {
//dd($request->all());
       $model = new Ausentismos();
       $model->nombre = $request->nombre;
       if ($model->save()) {
           $msg = '<div class="alert alert-success text-center">Se ha guardado la ausentismo satisfactoriamente</div>';
           $success = true;
       } else {
           $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
           $success = false;
       }
       return [
           'success' => $success,
           'mensaje' => $msg,
       ];
   }

   public function actualizarEstadoausentismo(Request $request)
   {
       $model = Ausentismos::find($request->id_ausentismo);
       if ($model != '') {
           $model->estado = $model->estado == 1 ? 0 : 1;
           if ($model->save()) {
               bitacora('ausentismo', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

               return [
                   'success' => true,
                   'estado' => $model->estado == 1 ? true : false,
                   'mensaje' => '',
               ];
           } else {
               return [
                   'success' => false,
                   'estado' => '',
                   'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
               ];
           }
       } else {
           return [
               'success' => false,
               'estado' => '',
               'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
           ];
       }
   }

   public function editar_ausentismo(Request $request){
      
       $ausentismo = Ausentismos::find($request->id_ausentismo);
       $ausentismo->nombre = $request->nombre;
       if ($ausentismo->save()) {
           $msg = '<div class="alert alert-success text-center">Se ha modificado la ausentismo satisfactoriamente</div>';
           $success = true;
       } else {
           $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
           $success = false;
       }
       return [
           'success' => $success,
           'mensaje' => $msg,
       ];
   }
    /* /////////////////   C A R G O //////////////////////////////*/



    public function store_cargo(Request $request)
    {
        $model = new Cargo();
        $model->nombre = $request->nombre;
        if ($model->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado el cargo satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    

    public function form_add_cargo(Request $request){
        return view('adminlte.gestion.rrhh.partials.form_add_cargoi',[
            'cargo' => Cargo::where('id_cargo',$request->id_cargo)->select('nombre','id_cargo')->first()
        ]);
    }


    public function actualizarEstadoCargo(Request $request)
    {
        $model = Cargo::find($request->id_cargo);
        if ($model != '') {
            $model->estado = $model->estado == 1 ? 0 : 1;
            if ($model->save()) {
                bitacora('cargo', $model->id_cargo, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

                return [
                    'success' => true,
                    'estado' => $model->estado == 1 ? true : false,
                    'mensaje' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => '',
                    'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
                ];
            }
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
            ];
        }
    }
    public function editar_cargo(Request $request){
     //  dd($request->all()); 
        $cargo = Cargo::find($request->id_cargo);
        $cargo->nombre = $request->nombre;
        if ($cargo->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha modificado el cargo satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    /* /////////////////   P R O F E S I O N //////////////////////////////*/


    public function store_profesion(Request $request)
    {
 //dd($request->all());
        $model = new Profesion();
        $model->nombre = $request->nombre;
        if ($model->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado la profesion satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function actualizarEstadoProfesion(Request $request)
    {
        $model = Profesion::find($request->id_profesion);
        if ($model != '') {
            $model->estado = $model->estado == 1 ? 0 : 1;
            if ($model->save()) {
                bitacora('profesion', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

                return [
                    'success' => true,
                    'estado' => $model->estado == 1 ? true : false,
                    'mensaje' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => '',
                    'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
                ];
            }
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
            ];
        }
    }

    public function editar_profesion(Request $request){
       
        $profesion = Profesion::find($request->id_profesion);
        $profesion->nombre = $request->nombre;
        if ($profesion->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha modificado la profesion satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

     /* /////////////////   T I P O   D E   R O L //////////////////////////////*/

 
 public function store_tipo_rol(Request $request)
 {
//dd($request->all());
     $model = new Tipo_rol();
     $model->nombre = $request->nombre;
     if ($model->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha guardado el rol satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 public function actualizarEstadoTipo_rol(Request $request)
 {
     $model = Tipo_rol::find($request->id_tipo_rol);
     if ($model != '') {
         $model->estado = $model->estado == 1 ? 0 : 1;
         if ($model->save()) {
             bitacora('tipo_rol', $model->id_, 'U', 'Actualización satisfactoria del estado del rol'. $model->nombre);

             return [
                 'success' => true,
                 'estado' => $model->estado == 1 ? true : false,
                 'mensaje' => '',
             ];
         } else {
             return [
                 'success' => false,
                 'estado' => '',
                 'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
             ];
         }
     } else {
         return [
             'success' => false,
             'estado' => '',
             'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
         ];
     }
 }

 public function editar_tipo_rol(Request $request){
    
     $tipo_rol = Tipo_rol::find($request->id_tipo_rol);
     $tipo_rol->nombre = $request->nombre;
     if ($tipo_rol->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha modificado el tipo de rol satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }
 



    /* /////////////////   C A U S A    D E S V I N C U L A C I O N  //////////////////////////////*/


    public function store_causa_desvinculacion(Request $request)
    {
 //dd($request->all());
        $model = new CausaDesvinculacion();
        $model->nombre = $request->nombre;
        if ($model->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha guardado el causa_desvinculacion satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }

    public function actualizarEstadoCausa_desvinculacion(Request $request)
    {
        //dd($request->all());
        $model = CausaDesvinculacion::find($request->id_causa_desvinculacion);
        if ($model != '') {
            $model->estado = $model->estado == 1 ? 0 : 1;
            if ($model->save()) {
                bitacora('causa_desvinculacion', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

                return [
                    'success' => true,
                    'estado' => $model->estado == 1 ? true : false,
                    'mensaje' => '',
                ];
            } else {
                return [
                    'success' => false,
                    'estado' => '',
                    'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
                ];
            }
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
            ];
        }
    }

    public function editar_causa_desvinculacion(Request $request){
       
        $causa_desvinculacion = CausaDesvinculacion::find($request->id_causa_desvinculacion);
        $causa_desvinculacion->nombre = $request->nombre;
        if ($causa_desvinculacion->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha modificado el causa_desvinculacion satisfactoriamente</div>';
            $success = true;
        } else {
            $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
            $success = false;
        }
        return [
            'success' => $success,
            'mensaje' => $msg,
        ];
    }
    
    
/* /////////////////   FORMA DE PAGO //////////////////////////////*/

public function store_tipo_pago(Request $request)
{
//dd($request->all());
    $model = new TipoPago();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el tipo de pago satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoTipo_pago(Request $request)
{
    $model = TipoPago::find($request->id_tipo_pago);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('tipo_pago', $model->id_, 'U', 'Actualización satisfactoria del estado del tipo de pago'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_tipo_pago(Request $request){
   
    $tipo_pago = TipoPago::find($request->id_tipo_pago);
    $tipo_pago->nombre = $request->nombre;
    if ($tipo_pago->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el tipo de pago satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}



 /* /////////////////   TIPO DE CONTRATO //////////////////////////////*/


 public function store_tipo_contrato(Request $request)
 {
//dd($request->all());
     $model = new TipoContrato();
     $model->nombre = $request->nombre;
     if ($model->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha guardado el tipo de contrato satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 public function actualizarEstadoTipo_contrato(Request $request)
 {
     $model = TipoContrato::find($request->id_tipo_contrato);
     if ($model != '') {
         $model->estado = $model->estado == 1 ? 0 : 1;
         if ($model->save()) {
             bitacora('tipo_contrato', $model->id_, 'U', 'Actualización satisfactoria del estado del contrato'. $model->nombre);

             return [
                 'success' => true,
                 'estado' => $model->estado == 1 ? true : false,
                 'mensaje' => '',
             ];
         } else {
             return [
                 'success' => false,
                 'estado' => '',
                 'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
             ];
         }
     } else {
         return [
             'success' => false,
             'estado' => '',
             'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
         ];
     }
 }

 public function editar_tipo_contrato(Request $request){
    
     $tipo_contrato = TipoContrato::find($request->id_tipo_contrato);
     $tipo_contrato->nombre = $request->nombre;
     if ($tipo_contrato->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha modificado el tipo de contrato satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 /* /////////////////   ESTRUCTURA ORGANIZATIVA//////////////////////////////*/


 public function store_estructura_organizativa(Request $request)
 {
//dd($request->all());
     $model = new EstructuraOrganizativa();
     $model->nombre = $request->nombre;
     if ($model->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha guardado el estructura_organizativa satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 public function actualizarEstadoEstructura_organizativa(Request $request)
 {
     $model = EstructuraOrganizativa::find($request->id_estructura_organizativa);
     if ($model != '') {
         $model->estado = $model->estado == 1 ? 0 : 1;
         if ($model->save()) {
             bitacora('estructura_organizativa', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

             return [
                 'success' => true,
                 'estado' => $model->estado == 1 ? true : false,
                 'mensaje' => '',
             ];
         } else {
             return [
                 'success' => false,
                 'estado' => '',
                 'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
             ];
         }
     } else {
         return [
             'success' => false,
             'estado' => '',
             'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
         ];
     }
 }

 public function editar_estructura_organizativa(Request $request){
    
     $estructura_organizativa = EstructuraOrganizativa::find($request->id_estructura_organizativa);
     $estructura_organizativa->nombre = $request->nombre;
     if ($estructura_organizativa->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha modificado el estructura_organizativa satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 /* /////////////////   GRUPO   //////////////////////////////*/


 public function store_grupo(Request $request)
 {
//dd($request->all());
     $model = new Grupo();
     $model->nombre = $request->nombre;
     if ($model->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha guardado el grupo satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }

 public function actualizarEstadoGrupo(Request $request)
 {
     $model = Grupo::find($request->id_grupo);
     if ($model != '') {
         $model->estado = $model->estado == 1 ? 0 : 1;
         if ($model->save()) {
             bitacora('grupo', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

             return [
                 'success' => true,
                 'estado' => $model->estado == 1 ? true : false,
                 'mensaje' => '',
             ];
         } else {
             return [
                 'success' => false,
                 'estado' => '',
                 'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
             ];
         }
     } else {
         return [
             'success' => false,
             'estado' => '',
             'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
         ];
     }
 }

 public function editar_grupo(Request $request){
    
     $grupo = Grupo::find($request->id_grupo);
     $grupo->nombre = $request->nombre;
     if ($grupo->save()) {
         $msg = '<div class="alert alert-success text-center">Se ha modificado el grupo satisfactoriamente</div>';
         $success = true;
     } else {
         $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
         $success = false;
     }
     return [
         'success' => $success,
         'mensaje' => $msg,
     ];
 }


/* /////////////////   DEPARTAMENTO   //////////////////////////////*/


public function store_departamento(Request $request)
{
//dd($request->all());
    $model = new Departamento();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el departamento satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoDepartamento(Request $request)
{
    $model = Departamento::find($request->id_departamento);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('departamento', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_departamento(Request $request){
   
    $departamento = Departamento::find($request->id_departamento);
    $departamento->nombre = $request->nombre;
    if ($departamento->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el departamento satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}


/* /////////////////   SUCURSAL //////////////////////////////*/


public function store_sucursal(Request $request)
{
//dd($request->all());
    $model = new Sucursal();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el sucursal satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoSucursal(Request $request)
{
    $model = Sucursal::find($request->id_sucursal);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('sucursal', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_sucursal(Request $request){
   
    $sucursal = Sucursal::find($request->id_sucursal);
    $sucursal->nombre = $request->nombre;
    if ($sucursal->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el sucursal satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

/* /////////////////   GRUPO_INTERNO //////////////////////////////*/


public function store_grupo_interno(Request $request)
{
//dd($request->all());
    $model = new GrupoInterno();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el grupo_interno satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoGrupo_interno(Request $request)
{
    $model = GrupoInterno::find($request->id_grupo_interno);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('grupo_interno', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_grupo_interno(Request $request){
   
    $grupo_interno = GrupoInterno::find($request->id_grupo_interno);
    $grupo_interno->nombre = $request->nombre;
    if ($grupo_interno->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el grupo_interno satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

/* /////////////////   GRADO_INSTRUCCION //////////////////////////////*/


public function store_grado_instruccion(Request $request)
{
//dd($request->all());
    $model = new GradoInstruccion();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el grado_instruccion satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoGrado_instruccion(Request $request)
{
    $model = GradoInstruccion::find($request->id_grado_instruccion);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('grado_instruccion', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_grado_instruccion(Request $request){
   
    $grado_instruccion = GradoInstruccion::find($request->id_grado_instruccion);
    $grado_instruccion->nombre = $request->nombre;
    if ($grado_instruccion->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el grado_instruccion satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

/* /////////////////   AGRUPACION //////////////////////////////*/


public function store_agrupacion(Request $request)
{
//dd($request->all());
    $model = new Agrupacion();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el agrupacion satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoAgrupacion(Request $request)
{
    $model = Agrupacion::find($request->id_agrupacion);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('agrupacion', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_agrupacion(Request $request){
   
    $agrupacion = Agrupacion::find($request->id_agrupacion);
    $agrupacion->nombre = $request->nombre;
    if ($agrupacion->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el agrupacion satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

/* /////////////////  PLANTILLA //////////////////////////////*/


public function store_plantilla(Request $request)
{
//dd($request->all());
    $model = new Plantilla();
    $model->nombre = $request->nombre;
    if ($model->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha guardado el plantilla satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

public function actualizarEstadoPlantilla(Request $request)
{
    $model = Plantilla::find($request->id_plantilla);
    if ($model != '') {
        $model->estado = $model->estado == 1 ? 0 : 1;
        if ($model->save()) {
            bitacora('plantilla', $model->id_, 'U', 'Actualización satisfactoria del estado del cargo'. $model->nombre);

            return [
                'success' => true,
                'estado' => $model->estado == 1 ? true : false,
                'mensaje' => '',
            ];
        } else {
            return [
                'success' => false,
                'estado' => '',
                'mensaje' => '<div class="alert alert-info text-center">Ha ocurrido un problema al guardar en el sistema</div>',
            ];
        }
    } else {
        return [
            'success' => false,
            'estado' => '',
            'mensaje' => '<div class="alert alert-info text-center">No se ha encontrado en el sistema el parámetro</div>',
        ];
    }
}

public function editar_plantilla(Request $request){
   
    $plantilla = Plantilla::find($request->id_plantilla);
    $plantilla->nombre = $request->nombre;
    if ($plantilla->save()) {
        $msg = '<div class="alert alert-success text-center">Se ha modificado el plantilla satisfactoriamente</div>';
        $success = true;
    } else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];
}

/*public function actualizar_salario(Request $request)
{
// dd($request->all());
    $detalle = PersonalDetalle::ALL()->where('estado', 1);
    $resultados = [];
    foreach ($listado as $per) {
    if ($per != '') {
        $detalle->sueldo = $request->sueldo;
        if ($detalle->save()) {
            $msg = '<div class="alert alert-success text-center">Se ha actualizado el salario satisfactoriamente</div>';
            $success = true;
        } 
    }else {
        $msg = '<div class="alert alert-danger text-center">Ha ocurrido un error al guardar la informacion</div>';
        $success = false;
    }
    return [
        'success' => $success,
        'mensaje' => $msg,
    ];

}*/


        

}
