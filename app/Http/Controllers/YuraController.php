<?php

namespace yura\Http\Controllers;

use Greggilbert\Recaptcha\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use phpseclib\Crypt\RSA;
use yura\Modelos\AccessoDirecto;
use yura\Modelos\Ciclo;
use yura\Modelos\ClasificacionBlanco;
use yura\Modelos\ClasificacionVerde;
use yura\Modelos\ConfiguracionUser;
use yura\Modelos\Cosecha;
use yura\Modelos\GrupoMenu;
use yura\Modelos\HistoricoVentas;
use yura\Modelos\Icon;
use yura\Modelos\Indicadores4Semanas;
use yura\Modelos\Pedido;
use yura\Modelos\ProyeccionModuloSemana;
use yura\Modelos\Rol;
use yura\Modelos\Semana;
use yura\Modelos\StockApertura;
use yura\Modelos\Submenu;
use yura\Modelos\SuperFinca;
use yura\Modelos\Usuario;
use Validator;
use Storage as Almacenamiento;
use yura\Modelos\ConfiguracionEmpresa;
use yura\Modelos\Planta;
use yura\Modelos\Variedad;

class YuraController extends Controller
{
    public function inicio(Request $request)
    {
        $empresas = SuperFinca::All()->sortBy('nombre');
        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $semana_1mes = $semana_pasada->last_4_semana;

        $data = [];
        /*foreach ($empresas as $sf) {
            $data[] = [
                'sf' => $sf,
                'ventas_1_mes' => getIndicadorByName('SF1-' . $sf->id_super_finca),
                'ventas_4_mes' => getIndicadorByName('SF2-' . $sf->id_super_finca),
                'ventas_1_anno' => getIndicadorByName('SF3-' . $sf->id_super_finca),
                'costos_1_mes' => getIndicadorByName('SF4-' . $sf->id_super_finca),
                'costos_4_mes' => getIndicadorByName('SF5-' . $sf->id_super_finca),
                'costos_1_anno' => getIndicadorByName('SF6-' . $sf->id_super_finca),
            ];
        }*/
        return view('adminlte.inicio_resumen', [
            'fincas_propias' => getFincasPropias(),
            'data' => $data
        ]);
    }

    public function listar_dashboard_inicial(Request $request)
    {
        $finca = $request->f != 'T' ? $request->f : getFincaActiva();
        $empresa = ConfiguracionEmpresa::find($finca);
        $usuario = getUsuario(Session::get('id_usuario'));
        $usuario->finca_activa = $finca;
        $usuario->save();

        $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));
        $tallos_producidos = DB::table('resumen_total_semanal_exportcalas')
            ->select(DB::raw('sum(tallos_exportables + bouquetera) as tallos_producidos'), DB::raw('sum(nacional) as nacional'))
            ->where('id_empresa', $finca)
            ->where('semana', $semana_pasada->codigo)
            ->get()[0];

        return view('adminlte.inicio', [
            'tallos_exportables' => getIndicadorByName('D2-' . $finca)->valor,
            'tallos_anno' => getIndicadorByName('D22-' . $finca)->valor,
            //'precio_x_ramo' => getIndicadorByName('D3-' . $finca)->valor,
            'precio_x_tallo' => getIndicadorByName('D14-' . $finca)->valor,
            'precio_x_tallo_normal' => getIndicadorByName('D23-' . $finca)->valor,
            'precio_x_tallo_bqt' => getIndicadorByName('D24-' . $finca)->valor,
            'venta' => getIndicadorByName('D4-' . $finca)->valor,
            'porcent_venta_normal' => getIndicadorByName('D20-' . $finca)->valor,
            'porcent_venta_bqt' => getIndicadorByName('D21-' . $finca)->valor,
            //'rendimiento' => getIndicadorByName('D5-' . $finca)->valor,
            //'desecho' => getIndicadorByName('D6-' . $finca)->valor,
            'area_produccion' => getIndicadorByName('D7-' . $finca)->valor,
            'ciclo' => getIndicadorByName('DA1-' . $finca)->valor,
            'ramos_m2_anno' => getIndicadorByName('D8-' . $finca)->valor,
            'venta_m2_anno_4_semanas' => getIndicadorByName('D18-' . $finca)->valor,
            'venta_m2_anno_13_semanas' => getIndicadorByName('D9-' . $finca)->valor,
            'venta_m2_anno_anual' => getIndicadorByName('D10-' . $finca)->valor,
            'tallos_cosechados' => getIndicadorByName('D11-' . $finca)->valor,
            //'cajas_exportadas' => getIndicadorByName('D13-' . $finca)->valor,
            'tallos_m2' => getIndicadorByName('D12-' . $finca)->valor,
            'costos_mano_obra' => getIndicadorByName('C1-' . $finca)->valor,
            'costos_insumos' => getIndicadorByName('C2-' . $finca)->valor,
            'costos_propagacion_x_tallo' => getIndicadorByName('C3-' . $finca)->valor,
            'costos_cultivo_x_tallo' => getIndicadorByName('C4-' . $finca)->valor,
            'costos_postcosecha_x_tallo' => getIndicadorByName('C5-' . $finca)->valor,
            'costos_total_x_tallo' => getIndicadorByName('C6-' . $finca)->valor,
            'costos_fijos' => getIndicadorByName('C7-' . $finca)->valor,
            'costos_regalias' => getIndicadorByName('C8-' . $finca)->valor,
            'costos_m2_13_semanas' => getIndicadorByName('C9-' . $finca)->valor,
            'costos_m2_anual' => getIndicadorByName('C10-' . $finca)->valor,
            'costos_m2_4_semanas' => getIndicadorByName('C13-' . $finca)->valor,
            'costo_x_planta' => getIndicadorByName('C12-' . $finca)->valor,
            'rentabilidad_m2_13_semanas' => getIndicadorByName('R1-' . $finca)->valor,
            'rentabilidad_m2_anual' => getIndicadorByName('R2-' . $finca)->valor,
            'rentabilidad_m2_4_semanas' => getIndicadorByName('R3-' . $finca)->valor,
            'nacional' => getIndicadorByName('D15-' . $finca)->valor,
            'bajas' => getIndicadorByName('D16-' . $finca)->valor,
            'porcentaje_cumplimiento' => getIndicadorByName('D17-' . $finca)->valor,
            'venta_bqt' => getIndicadorByName('B1-' . $finca)->valor,
            'costos_bqt' => getIndicadorByName('B2-' . $finca)->valor,
            'ebitda_bqt' => getIndicadorByName('B3-' . $finca)->valor,
            'compra_flor_bqt' => getIndicadorByName('B4-' . $finca)->valor,
            'compra_flor_export' => getIndicadorByName('B5-' . $finca)->valor,
            'venta_comprada_anno' => getIndicadorByName('FC1-' . $finca)->valor,
            'venta_comprada_4_semana' => getIndicadorByName('FC2-' . $finca)->valor,
            'venta_comprada_13_semana' => getIndicadorByName('FC3-' . $finca)->valor,

            'tallos_producidos' => $tallos_producidos,
            'finca' => $finca,
            'empresa' => $empresa,
        ]);
    }

    public function login(Request $request)
    {
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_RAW);
        $k = $rsa->createKey();

        Session::put('key_publica', $k['publickey']);
        Session::put('key_privada', $k['privatekey']);

        if (!$request->session()->has('logeado')) { // Si no tiene variable logeado la session? logeado = false;
            Session::put('logeado', false);
        };

        if (!$request->session()->get('logeado')) { // Si no está logeado

            $rsa->loadKey(Session::get('key_privada'));
            $raw = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_RAW);

            return view('login.login', [
                'key' => $raw['n']->toHex(),
            ]);
        };

        return redirect('/');   // Si está logeado redirect a inicio
    }

    public function verificaUsuario(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'username' => 'required|max:250',
            'h_clave' => 'required',
            //'g-recaptcha-response' => 'required|captcha'
            //'captcha' => 'required|captcha',
        ], [
            'username.max' => 'El nombre de usuario es muy grande',
            'username.required' => 'El nombre de usuario es obligatorio',
            'h_clave.required' => 'La contraseña es obligatoria',
            'g-recaptcha-response.required' => 'Haga clic en el captcha de seguridad y espere a que verifique que no es un robot',
            'g-recaptcha-response.captcha' => 'El código de verificación es incorrecto',
            //'captcha.required' => 'El código de verificación es obligatorio',
            //'captcha.captcha' => 'El código de verificación es incorrecto',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {
            $correo = '' . espacios(strtolower($request->usuario));
            $clave = $this->decrypt(Session::get('key_privada'), $request->h_clave);
            $usuario = Usuario::All()->where('username', '=', $request->username)->first();
            $err_usr = true;
            $err_pss = true;

            if ($usuario != '') {
                $err_usr = false;
                if ($usuario->estado == 'A') {
                    if (Hash::check($clave, $usuario->password)) {
                        if ($usuario->configuracion == '') {
                            $configuracion = new ConfiguracionUser();
                            $configuracion->id_usuario = $usuario->id_usuario;
                            $configuracion->save();
                            $configuracion = ConfiguracionUser::All()->last();
                            bitacora('configuracion_user', $configuracion->id_configuracion_user, 'I', 'Creación satisfactoria de una nueva configuracion de usuario');
                        }

                        $err_pss = false;
                        Session::put('logeado', true);
                        Session::put('last_quest', date('Y-m-d H:i:s'));
                        Session::put('id_usuario', $usuario->id_usuario);
                        Session::put('tipo_rol', $usuario->rol()->tipo);

                        bitacora('usuario', $usuario->id_usuario, 'L', 'Inicio de sesión satisfactorio. Usuario:' . $usuario->nombre_completo);
                    }
                } else {
                    $err_usr = false;
                    $err_pss = false;
                    $msg = '<div class="alert alert-danger text-center">Su usario ha sido desactivado.
                            Póngase en contacto con el administrador al correo <strong>' . env('MAIL_ADMIN') . '</strong></div>';
                    $success = false;
                }
            }
            if ($err_usr) {
                $msg = 'Fallo de inicio de sesión con el usuario . Error de contraseña o de usuario';
                $success = false;
                bitacora('usuario', -1, 'E', 'Fallo de inicio de sesión con el usuario ' . $correo . '. No existe el usuario en el sistema');
            } elseif ($err_pss) {
                $msg = 'Fallo de inicio de sesión con el usuario . Error de contraseña o de usuario';
                $success = false;
                bitacora('usuario', $usuario[0]->id_usuario, 'E', 'Fallo de inicio de sesión con el usuario ' . $correo . '. Contraseña incorrecta');
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

    function decrypt($privatekey, $encrypted)
    {
        $rsa = new RSA();

        $encrypted = pack('H*', $encrypted);

        $rsa->loadKey($privatekey);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        return $rsa->decrypt($encrypted);
    }

    public function logout(Request $request)
    {
        if (!$request->session()->has('logeado')) {
            Session::put('logeado', false);
        };
        if (Session::has('id_usuario')) bitacora('usuario', Session::get('id_usuario'), 'C', 'Cerrado de session satisfactorio');
        Session::put('logeado', false);
        Session::flush();
        DB::disconnect();
        return redirect('');
    }

    public function save_config_user(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'fixed_layout' => 'required|',
            'boxed_layout' => 'required',
            'color_config' => 'required|',
            'config_online' => 'required|',
            'skin' => 'required|max:25',
        ], [
            'skin.max' => 'El tema es muy grande',
            'config_online.required' => 'Visibilidad online es obligatorio',
            'fixed_layout.required' => 'Diseño compacto es obligatorio',
            'boxed_layout.required' => 'Diseño en caja es obligatorio',
            'color_config.required' => 'El color del panel de control es obligatorio',
            'skin.captcha' => 'El tema es obligatorio',
        ]);
        $msg = '';
        $success = true;
        if (!$valida->fails()) {
            $config = ConfiguracionUser::find(getUsuario(Session::get('id_usuario'))->configuracion->id_configuracion_user);
            $config->fixed_layout = $request->fixed_layout == 'true' ? 'S' : 'N';
            $config->boxed_layout = $request->boxed_layout == 'true' ? 'S' : 'N';
            $config->toggle_color_config = $request->color_config == 'true' ? 'S' : 'N';
            $config->config_online = $request->config_online == 'true' ? 'S' : 'N';
            $config->skin = $request->skin;
            if ($config->save()) {
                $msg = '<div class="alert alert-success text-center">Se ha guardado satisfactoriamente la configuración</div>';

                bitacora('configuracion_user', $config->id_configuracion_user, 'U', 'Actualización satisfactoria de la configuración');
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">No se ha podido guardar la configuración en el sistema</div>';
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

    public function perfil(Request $request)
    {
        $rsa = new RSA();

        $rsa->loadKey(Session::get('key_privada'));
        $raw = $rsa->getPublicKey(RSA::PUBLIC_FORMAT_RAW);

        $usuario = Usuario::find(Session::get('id_usuario'));
        $ids_submenu_ad = $usuario->getIdSubmenusAccesoDirecto();
        return view('perfil.inicio', [
            'usuario' => $usuario,
            'key' => $raw['n']->toHex(),
            'roles' => Rol::All(),
            'iconos' => Icon::All(),
            'ids_submenu_ad' => $ids_submenu_ad,
            'grupos_menu' => getGrupoMenusOfUser(Session::get('id_usuario')),
        ]);
    }

    public function seleccionar_submenu(Request $request)
    {
        $valida = Validator::make($request->all(), [
            'submenu' => 'required',
            'check' => 'required',
        ], [
            'submenu.required' => 'El submenu es obligatorio',
            'check.required' => 'El check es obligatorio',
        ]);
        if (!$valida->fails()) {
            $usuario = Usuario::find(Session::get('id_usuario'));
            $model = AccessoDirecto::All()
                ->where('id_usuario', $usuario->id_usuario)
                ->where('id_submenu', $request->submenu)
                ->first();
            if ($model != '') { // ya existe
                $model->delete();
                $success = true;
                $msg = '<div class="alert alert-success text-center">' .
                    '<p> Se ha eliminado el acceso directo satisfactoriamente</p>'
                    . '</div>';
            } else {    // es nuevo
                $model = new AccessoDirecto();
                $model->id_usuario = $usuario->id_usuario;
                $model->id_submenu = $request->submenu;
                $model->id_icono = $request->icono;

                if ($model->save()) {
                    $model = AccessoDirecto::All()->last();
                    $success = true;
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha creado el acceso directo satisfactoriamente</p>'
                        . '</div>';
                    bitacora('acceso_directo', $model->id_acceso_directo, 'I', 'Inserción satisfactoria del acceso directo');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
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
            'mensaje' => $msg,
            'success' => $success
        ];
    }

    public function update_usuario(Request $request)
    {
        $msg = '';
        $success = true;

        $valida = Validator::make($request->all(), [
            'nombre_completo' => 'required|min:3|max:250',
            'correo' => 'required|email|max:250',
            'username' => 'required|max:250',
            'id_rol' => 'required|',
            'id_usuario' => 'required|',
        ], [
            'nombre_completo.required' => 'El nombre es obligatorio',
            'correo.required' => 'El correo es obligatorio',
            'username.required' => 'El nombre de usuario es obligatorio',
            'id_rol.required' => 'El rol es obligatorio',
            'id_usuario.required' => 'El usuario es obligatorio',
            'correo.email' => 'El correo es inválido',
            'correo.max' => 'El correo es muy grande',
            'username.max' => 'El nombre de usuario es muy grande',
            'nombre_completo.max' => 'El nombre es muy grande',
            'nombre_completo.min' => 'El nombre es muy corto',
        ]);
        if (!$valida->fails()) {
            if (count(
                Usuario::All()
                    ->where('nombre_completo', '=', str_limit(mb_strtoupper(espacios($request->nombre_completo)), 250))
                    ->where('username', '=', str_limit(mb_strtolower(espacios($request->username)), 250))
                    ->where('correo', '=', str_limit(mb_strtolower(espacios($request->correo)), 250))
                    ->where('id_usuario', '!=', $request->id_usuario)
            ) == 0) {

                $model = Usuario::find($request->id_usuario);

                $model->id_rol = $request->id_rol;
                $model->correo = str_limit(mb_strtolower(espacios($request->correo)), 250);
                $model->nombre_completo = str_limit(mb_strtoupper(espacios($request->nombre_completo)), 250);
                $model->username = str_limit(mb_strtolower(espacios($request->username)), 250);

                if ($model->save()) {
                    $msg = '<div class="alert alert-success text-center">' .
                        '<p> Se ha guardado el usuario satisfactoriamente</p>'
                        . '</div>';
                    bitacora('usuario', $model->id_usuario, 'U', 'Actualización satisfactoria de un usuario');
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Ha ingresado datos de usuario que ya existen</p>'
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

    public function update_image_perfil(Request $request)
    {
        $model = Usuario::find($request->id_usuario);
        //------------------------------    GRABAR LA IMAGEN DE PERFIL  -----------------------------------------
        try {
            if ($request->hasFile('imagen_perfil')) {
                $archivo = $request->file('imagen_perfil');
                $input = array('image' => $archivo);
                $reglas = array('image' => 'required|image|mimes:jpeg,jpeg|max:2000');
                $validacion = Validator::make($input, $reglas);

                if ($validacion->fails()) {
                    return [
                        'mensaje' => '<div class="alert alert-danger text-center">' .
                            '<p>¡Imagen no válida!</p>' .
                            '</div>',
                        'success' => false
                    ];
                } else {
                    $nombre_original = $archivo->getClientOriginalName();
                    $extension = $archivo->getClientOriginalExtension();
                    $imagen = "imagen_perfil_" . date('Y_d_m_H_i_s') . "-" . $model->username . "." . $extension;
                    $r1 = Almacenamiento::disk('imagenes')->put($imagen, \File::get($archivo));
                    if (!$r1) {
                        return [
                            'mensaje' => '<div class="alert alert-danger text-center">' .
                                '<p>¡No se pudo subir la imagen!</p>' .
                                '</div>',
                            'success' => false
                        ];
                    } else {
                        if ($model->imagen_perfil != 'logo_usuario.png') {
                            $r1 = Almacenamiento::disk('imagenes')->delete($model->imagen_perfil);
                            if (!$r1) {
                                return [
                                    'mensaje' => '<div class="alert alert-danger text-center">' .
                                        '<p>¡No se pudo eliminar la imagen anterior!</p>' .
                                        '</div>',
                                    'success' => false
                                ];
                            }
                        }
                        $model->imagen_perfil = $imagen;
                    }
                }
            } else {
                if ($model->imagen_perfil != 'logo_usuario.png') {
                    $r1 = Almacenamiento::disk('imagenes')->delete($model->imagen_perfil);
                    if (!$r1) {
                        return [
                            'mensaje' => '<div class="alert alert-danger text-center">' .
                                '<p>¡No se pudo eliminar la imagen anterior!</p>' .
                                '</div>',
                            'success' => false
                        ];
                    }
                }
                $model->imagen_perfil = 'logo_usuario.png';
            }

            if ($model->save()) {
                bitacora('usuario', $model->id_usuario, 'U', 'Actualización de la imagen de un usuario');
                return [
                    'mensaje' => '<div class="alert alert-success text-center">' .
                        '<p>Se ha actualizado satisfactoriamente la imagen de perfil</p>' .
                        '</div>',
                    'success' => true
                ];
            } else {
                return [
                    'mensaje' => '<div class="alert alert-danger text-center">' .
                        '<p>No se ha podido actualizar la imagen de perfil</p>' .
                        '</div>',
                    'success' => false
                ];
            }
        } catch (\Exception $e) {
            return [
                'mensaje' => '<div class="alert alert-danger text-center">' .
                    '<p>¡Ha ocurrido un problema al guardar la imagen en el sistema! **</p>' .
                    $e->getMessage() .
                    '</div>',
                'success' => false
            ];
        }
    }

    public function get_usuario_json(Request $request)
    {
        $user = Usuario::find($request->id_usuario);
        return [
            'user' => $user
        ];
    }

    public function update_password(Request $request)
    {
        $msg = '';
        $success = true;

        $valida = Validator::make($request->all(), [
            'passw' => 'required',
            'passw_current' => 'required',
            'id_usuario' => 'required',
        ], [
            'passw_current.required' => 'La contraseña actual es obligatoria',
            'passw.required' => 'La contraseña es obligatoria',
            'id_usuario.required' => 'El usuario es obligatoria',
        ]);
        if (!$valida->fails()) {
            $model = Usuario::find($request->id_usuario);

            $pwd = $this->decrypt(Session::get('key_privada'), $request->passw_current);

            if (Hash::check($pwd, $model->password)) {
                $pwd1 = $this->decrypt(Session::get('key_privada'), $request->passw);

                $patron = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/";
                if (trim($pwd1) != '' && preg_match($patron, $pwd1)) {
                    $pwd1 = Hash::make($pwd1);
                    $model->password = $pwd1;

                    if ($model->save()) {
                        $msg = '<div class="alert alert-success text-center">' .
                            '<p> Se ha guardado la nueva contraseña satisfactoriamente</p>'
                            . '</div>';
                        bitacora('usuario', $model->id_usuario, 'U', 'Actualización satisfactoria de la contraseña de un usuario');
                    } else {
                        $success = false;
                        $msg = '<div class="alert alert-warning text-center">' .
                            '<p> Ha ocurrido un problema al guardar la información al sistema</p>'
                            . '</div>';
                    }
                } else {
                    $success = false;
                    $msg = '<div class="alert alert-warning text-center">' .
                        '<p> Contraseña mal estructurada. (Mínimo 6 caracteres, incluyendo 1 dígito, 1 letra minúscula y 1 letra mayúscula)</p>'
                        . '</div>';
                }
            } else {
                $success = false;
                $msg = '<div class="alert alert-warning text-center">' .
                    '<p> Contraseña incorrecta</p>'
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

    public function buscar_saldos(Request $request)
    {
        $arreglo = [];
        $antes = $request->antes != '' ? $request->antes : 3;
        $despues = $request->despues != '' ? $request->despues : 3;
        if ($request->fecha >= date('Y-m-d')) {
            for ($i = 1; $i <= $antes; $i++) {
                $fecha = opDiasFecha('-', $i, $request->fecha);
                array_push($arreglo, $fecha);
            }
            array_push($arreglo, $request->fecha);
            for ($i = 1; $i <= $despues; $i++) {
                $fecha = opDiasFecha('+', $i, $request->fecha);
                array_push($arreglo, $fecha);
            }
        }
        $arreglo = array_sort($arreglo);
        return view('adminlte.gestion.postcocecha.pedidos.forms.paritals.saldos', [
            'fechas' => $arreglo,
            'fecha' => $request->fecha,
            'antes' => $antes,
            'despues' => $despues,
        ]);
    }

    public function select_planta_global(Request $request)
    {
        $p_variedades = DB::table('variedad as v')
            ->select('v.id_variedad', 'v.nombre')->distinct()
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->orderBy('v.nombre')
            ->get();
        $r = '';
        foreach ($p_variedades as $v) {
            $r .= '<option value="' . $v->id_variedad . '">' . $v->nombre . '</option>';
        }
        return $r;
    }

    public function select_planta(Request $request)
    {
        $finca = getFincaActiva();
        $p_variedades = DB::table('ciclo as c')
            ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
            ->select('v.id_variedad', 'v.nombre')->distinct()
            ->where('c.estado', 1)
            ->where('v.estado', 1)
            ->where('v.id_planta', $request->planta)
            ->where('c.activo', 1)
            ->where('c.id_empresa', $finca)
            ->orderBy('nombre')
            ->get();
        $r = '';
        foreach ($p_variedades as $v) {
            $r .= '<option value="' . $v->id_variedad . '">' . $v->nombre . '</option>';
        }
        return $r;
    }

    public function select_plantaByFinca(Request $request)
    {
        $finca = ConfiguracionEmpresa::find($request->finca);
        if ($finca->proveedor == 0) {
            $p_variedades = DB::table('ciclo as c')
                ->join('variedad as v', 'v.id_variedad', '=', 'c.id_variedad')
                ->select('v.id_variedad', 'v.nombre')->distinct()
                ->where('c.estado', 1)
                ->where('v.estado', 1)
                ->where('v.id_planta', $request->planta)
                ->where('c.activo', 1)
                ->where('c.id_empresa', $finca->id_configuracion_empresa)
                ->orderBy('nombre')
                ->get();
        } else {
            $p_variedades = DB::table('variedad_proveedor as vp')
                ->join('variedad as v', 'v.id_variedad', '=', 'vp.id_variedad')
                ->select('vp.id_variedad', 'v.nombre')->distinct()
                ->where('v.id_planta', $request->planta)
                ->where('vp.id_proveedor', $finca->id_configuracion_empresa)
                ->get();
        }
        $r = '';
        foreach ($p_variedades as $v) {
            $r .= '<option value="' . $v->id_variedad . '">' . $v->nombre . '</option>';
        }
        return $r;
    }

    public function mostrar_indicadores_claves(Request $request)
    {
        $finca = getFincaActiva();
        if (count(getUsuario(Session::get('id_usuario'))->rol()->getSubmenusByTipo('C')) > 0) {
            if ($request->view == 'indicadores_rentabilidad_m2') {
                $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));   // 1 semana atras
                $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $sem_hasta->fecha_inicial));   // 52 semanas atras

                $indicadores_4_semanas = DB::table('indicadores_4_semanas')
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $sem_desde->codigo)
                    ->where('semana', '<=', $sem_hasta->codigo)
                    ->orderBy('semana')
                    ->get();
                $datos = [
                    'finca' => $finca,
                    'rentabilidad_m2_mensual' => getIndicadorByName('R1-' . $finca)->valor,
                    'rentabilidad_m2_anual' => getIndicadorByName('R2-' . $finca)->valor,
                    'rentabilidad_m2_mes' => getIndicadorByName('R3-' . $finca)->valor,
                    'indicadores_4_semanas' => $indicadores_4_semanas,
                ];
            }
            if ($request->view == 'indicadores_costos_datos_importantes') {
                $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));   // 1 semana atras
                $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $sem_hasta->fecha_inicial));   // 52 semanas atras

                $resumen_costos = DB::table('resumen_costos_semanal')
                    ->where('id_empresa', $finca)
                    ->where('codigo_semana', '>=', $sem_desde->codigo)
                    ->where('codigo_semana', '<=', $sem_hasta->codigo)
                    ->orderBy('codigo_semana')
                    ->get();

                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('codigo', '>=', $sem_desde->codigo)
                    ->where('codigo', '<=', $sem_hasta->codigo)
                    ->orderBy('codigo')
                    ->get();

                $fincas = [$finca];
                if ($finca == 2)
                    array_push($fincas, -1);
                $compra_flor = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('bouquetera')
                        ->select(
                            DB::raw('sum(precio * (tallos)) as tallos'),
                            DB::raw('sum(precio * (exportada)) as exportada')
                        )
                        ->where('fecha', '>=', $sem->fecha_inicial)
                        ->where('fecha', '<=', $sem->fecha_final)
                        ->whereIn('id_empresa', $fincas)
                        ->get()[0];
                    array_push($compra_flor, [
                        'semana' => $sem->codigo,
                        'query' => $cant,
                    ]);
                }

                $datos = [
                    'finca' => $finca,
                    'resumen_costos' => $resumen_costos,
                    'compra_flor' => $compra_flor,
                ];
            }
            if ($request->view == 'indicadores_ventas_m2') {
                dd('En actualizaciones');
            }
            if ($request->view == 'indicadores_datos_importantes') {
                $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));   // 1 semana atras
                $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $sem_hasta->fecha_inicial));   // 52 semanas atras

                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
                    ->select(
                        'semana',
                        DB::raw('sum(bajas) as bajas'),
                        DB::raw('sum(venta) as venta'),
                        DB::raw('sum(venta_bouquetera) as venta_bouquetera'),
                        DB::raw('sum(tallos_cosechados) as tallos_cosechados'),
                        DB::raw('sum(tallos_exportables) as tallos_exportables')
                    )
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $sem_desde->codigo)
                    ->where('semana', '<=', $sem_hasta->codigo)
                    ->groupBy('semana')
                    ->orderBy('semana')
                    ->get();

                $semanas = DB::table('semana')
                    ->select('codigo', 'fecha_inicial', 'fecha_final')->distinct()
                    ->where('codigo', '>=', $sem_desde->codigo)
                    ->where('codigo', '<=', $sem_hasta->codigo)
                    ->orderBy('codigo')
                    ->get();

                $resumen_area = [];
                foreach ($semanas as $sem) {
                    $cant = DB::table('ciclo')
                        ->select(DB::raw('sum(area) as area'))
                        ->where('estado', '=', 1)
                        ->where('id_empresa', $finca)
                        ->Where(function ($q) use ($sem) {
                            $q->where('fecha_fin', '>=', $sem->fecha_inicial)
                                ->where('fecha_fin', '<=', $sem->fecha_final)
                                ->orWhere(function ($q) use ($sem) {
                                    $q->where('fecha_inicio', '>=', $sem->fecha_inicial)
                                        ->where('fecha_inicio', '<=', $sem->fecha_final);
                                })
                                ->orWhere(function ($q) use ($sem) {
                                    $q->where('fecha_inicio', '<', $sem->fecha_inicial)
                                        ->where('fecha_fin', '>', $sem->fecha_final);
                                });
                        })
                        ->get()[0]->area;
                    array_push($resumen_area, [
                        'semana' => $sem->codigo,
                        'area' => $cant,
                    ]);
                }

                $datos = [
                    'finca' => $finca,
                    'resumen_semanal' => $resumen_semanal,
                    'resumen_area' => $resumen_area,
                ];
            }
            if ($request->view == 'indicadores_costos_m2') {
                dd('En actualizaciones');
            }
            if ($request->view == 'indicadores_claves') {
                $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));   // 1 semana atras
                $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $sem_hasta->fecha_inicial));   // 52 semanas atras
                $semana_pasada = getSemanaByDate(opDiasFecha('-', 7, hoy()));

                $indicadores_4_semanas = DB::table('indicadores_4_semanas')
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $sem_desde->codigo)
                    ->where('semana', '<=', $sem_hasta->codigo)
                    ->orderBy('semana')
                    ->get();

                $resumen_semanal = DB::table('resumen_total_semanal_exportcalas')
                    ->select(
                        'semana',
                        DB::raw('sum(tallos_exportables + bouquetera) as tallos_producidos'),
                        DB::raw('sum(nacional) as nacional')
                    )
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $sem_desde->codigo)
                    ->where('semana', '<=', $sem_hasta->codigo)
                    ->groupBy('semana')
                    ->orderBy('semana')
                    ->get();

                $tallos_producidos = DB::table('resumen_total_semanal_exportcalas')
                    ->select(DB::raw('sum(tallos_exportables + bouquetera) as tallos_producidos'), DB::raw('sum(nacional) as nacional'))
                    ->where('id_empresa', $finca)
                    ->where('semana', $semana_pasada->codigo)
                    ->get()[0];

                $datos = [
                    'finca' => $finca,
                    'indicadores_4_semanas' => $indicadores_4_semanas,
                    'resumen_semanal' => $resumen_semanal,
                    'tallos_producidos' => $tallos_producidos,
                    'precio_x_tallo' => getIndicadorByName('D14-' . $finca)->valor,
                    'porcentaje_cumplimiento' => getIndicadorByName('D17-' . $finca)->valor,
                    'tallos_m2' => getIndicadorByName('D12-' . $finca)->valor,
                    'ciclo' => getIndicadorByName('DA1-' . $finca)->valor,
                ];
            }
            if ($request->view == 'indicadores_claves_costos') {
                $sem_hasta = getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')));   // 1 semana atras
                $sem_desde = getSemanaByDate(opDiasFecha('-', 364, $sem_hasta->fecha_inicial));   // 52 semanas atras

                $indicadores_4_semanas = DB::table('indicadores_4_semanas')
                    ->where('id_empresa', $finca)
                    ->where('semana', '>=', $sem_desde->codigo)
                    ->where('semana', '<=', $sem_hasta->codigo)
                    ->orderBy('semana')
                    ->get();
                $datos = [
                    'finca' => $finca,
                    'costos_propagacion_x_tallo' => getIndicadorByName('C3-' . $finca)->valor,
                    'costos_cultivo_x_tallo' => getIndicadorByName('C4-' . $finca)->valor,
                    'costos_postcosecha_x_tallo' => getIndicadorByName('C5-' . $finca)->valor,
                    'costos_total_x_tallo' => getIndicadorByName('C6-' . $finca)->valor,
                    'indicadores_4_semanas' => $indicadores_4_semanas,
                ];
            }

            return view('adminlte.crm.' . $request->view, $datos);
        } else
            return view('adminlte.inicio');
    }

    public function select_filtro_variedad(Request $request)
    {
        $finca = getFincaActiva();
        $variedad = Variedad::find($request->variedad);
        return view('adminlte.partials.dashboard_x_variedad', [
            'variedad' => $variedad,
            'calibre' => getIndicadorByName('D1-1')->getVariedad($variedad->id_variedad)->valor,
            'tallos' => getIndicadorByName('D2-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'precio_x_ramo' => getIndicadorByName('D3-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'precio_x_tallo' => getIndicadorByName('D14-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'valor' => getIndicadorByName('D4-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'rendimiento' => getIndicadorByName('D5')->valor,
            'desecho' => getIndicadorByName('D6-1')->valor,
            'area_produccion' => getIndicadorByName('D7-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'ciclo' => getIndicadorByName('DA1-1')->getVariedad($variedad->id_variedad)->valor,
            'ramos_m2_anno' => getIndicadorByName('D8-1')->getVariedad($variedad->id_variedad)->valor,
            'venta_m2_anno_4_semanas' => getIndicadorByName('D18-1')->getVariedad($variedad->id_variedad)->valor,
            'venta_m2_anno_mensual' => getIndicadorByName('D9-1')->getVariedad($variedad->id_variedad)->valor,
            'venta_m2_anno_anual' => getIndicadorByName('D10-1')->valor,
            'tallos_cosechados' => getIndicadorByName('D11-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'cajas_exportadas' => getIndicadorByName('D13-1')->getVariedad($variedad->id_variedad)->valor,
            'tallos_m2' => getIndicadorByName('D12-1')->getVariedad($variedad->id_variedad)->valor,
            'costos_mano_obra' => getIndicadorByName('C1-1')->valor,
            'costos_insumos' => getIndicadorByName('C2-1')->valor,
            'costos_campo_semana' => getIndicadorByName('C3-1')->valor,
            'costos_cosecha_x_tallo' => getIndicadorByName('C4-1')->valor,
            'costos_postcosecha_x_tallo' => getIndicadorByName('C5-1')->valor,
            'costos_total_x_tallo' => getIndicadorByName('C6-1')->valor,
            'costos_fijos' => getIndicadorByName('C7-1')->valor,
            'costos_regalias' => getIndicadorByName('C8-1')->valor,
            'costos_m2_mensual' => getIndicadorByName('C9-1')->valor,
            'costos_m2_anual' => getIndicadorByName('C10-1')->valor,
            'costo_x_planta' => getIndicadorByName('C12-1')->valor,
            'rentabilidad_m2_mensual' => getIndicadorByName('R1-1')->getVariedad($variedad->id_variedad)->valor,
            'rentabilidad_m2_anual' => getIndicadorByName('R2-1')->valor,
            'nacional' => getIndicadorByName('D15-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'bajas' => getIndicadorByName('D16-' . $finca)->getVariedad($variedad->id_variedad)->valor,
            'porcentaje_cumplimiento' => getIndicadorByName('D17-' . $finca)->getVariedad($variedad->id_variedad)->valor,
        ]);
    }

    public function cargar_accesos_directos(Request $request)
    {
        $usuario = Usuario::find(Session::get('id_usuario'));

        return view('layouts.adminlte.partials.accesos_directos', [
            'usuarios' => $usuario,
            'accesos' => $usuario->accesos_directos,
        ]);
    }

    public function cargar_fincas_propias(Request $request)
    {
        $usuario = Usuario::find(Session::get('id_usuario'));

        return view('layouts.adminlte.partials.fincas_propias', [
            'usuarios' => $usuario,
            'fincas' => $usuario->empresas,
        ]);
    }

    public function cargar_submenu_crm(Request $request)
    {
        return view('layouts.adminlte.partials.submenu_crm', [
            'submenus' => Submenu::where('tipo', 'C')->orderBy('nombre')->get(),
        ]);
    }

    public function detallar_indicador(Request $request)
    {
        if ($request->ind == 'D2') {
            $view = 'listado_verdes';
            $verdes = ClasificacionVerde::where('estado', 1)
                ->where('fecha_ingreso', '>=', opDiasFecha('-', 7, date('Y-m-d')))
                ->where('fecha_ingreso', '<=', opDiasFecha('-', 1, date('Y-m-d')))
                ->get();
            $datos = [
                'verdes' => $verdes,
            ];
        }

        return view('adminlte.partials.detalles_indicador.' . $view, $datos);
    }

    public function test(Request $request)
    {
        Artisan::call('otros_gastos:update', [
            'desde' => 2101,
            'hasta' => 2115,
        ]);
    }

    public function update_finca_activa(Request $request)
    {
        if ($request->finca_actual != 'T') {
            $usuario = getUsuario(Session::get('id_usuario'));
            $usuario->finca_activa = $request->finca_actual;
            $usuario->save();
        }
        return [
            'success' => true
        ];
    }

    /* ------------------------------------------------------------------ */
    public function rectificar_semanas(Request $request)
    {
        /*$semanas_originales = Semana::where('id_variedad', 1)->where('anno', 2020)->orderBy('codigo')->get();
        foreach ($semanas_originales as $orig) {
            $otras = Semana::where('codigo', $orig->codigo)
                ->where('fecha_inicial', '!=', $orig->fecha_inicial)
                ->where('fecha_final', '!=', $orig->fecha_final)
                ->get();
            foreach ($otras as $item) {
                $item->fecha_inicial = $orig->fecha_inicial;
                $item->fecha_final = $orig->fecha_final;
                $item->save();
            }
        }*/

        /*$proy = ProyeccionModuloSemana::where('id_variedad', 2)->get();
        foreach ($proy as $item)
            $item->delete();*/

        $semana_actual = getSemanaByDate(date('Y-m-d'));
        $fecha_ini = Ciclo::where('estado', '=', 1)
            ->where('activo', '=', 1)
            ->where('id_variedad', '=', 70)
            ->where('fecha_fin', '>=', $semana_actual->fecha_inicial)
            ->orderBy('fecha_inicio')->get();
        if (count($fecha_ini) > 0) {
            $ciclos = $fecha_ini;
            $fecha_ini = $fecha_ini[0]->fecha_inicio;
            $semana_desde = getSemanaByDate($fecha_ini);

            foreach ($ciclos as $pos => $c) {
                $semana_hasta = $c->semana();
                $semanas = DB::table('semana as s')
                    ->select('s.codigo', 's.fecha_inicial', 's.fecha_final')->distinct()
                    ->where('s.codigo', '>=', $semana_desde->codigo)
                    ->where('s.codigo', '<', $semana_hasta->codigo)
                    ->where('s.estado', 1)
                    ->get();
                foreach ($semanas as $sem) {
                    $proy = new ProyeccionModuloSemana();
                    $proy->id_modulo = $c->id_modulo;
                    $proy->id_variedad = $c->id_variedad;
                    $proy->semana = $sem->codigo;
                    $proy->tipo = 'F';
                    $proy->info = '-';
                    $proy->save();
                }
            }
        }

        dd('fin');
    }

    public function cargar_utiles(Request $request)
    {
        /* ------- resumen_jobs ------ */
        $resumen_jobs = DB::table('jobs')
            ->select('queue', 'attempts', DB::raw('count(*) as cant'))
            ->groupBy('queue')
            ->groupBy('attempts')
            ->get();
        /* ------- archivos_subidos------- */
        $archivos_subidos = Almacenamiento::disk('pdf_loads')->files('');
        return view('layouts.adminlte.partials.cargar_utiles', [
            'resumen_jobs' => $resumen_jobs,
            'archivos_subidos' => $archivos_subidos,
        ]);
    }
}
