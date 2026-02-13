<?php

namespace yura\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use yura\Modelos\Notificacion;
use yura\Modelos\UserNotification;

class NotificacionesSistema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificaciones:sistema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear las notificaciones de tipo Sistema';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ini = date('Y-m-d H:i:s');
        Log::info('<<<<< ! >>>>> Ejecutando comando "notificaciones:sistema" <<<<< ! >>>>>');
        dump('<<<<< ! >>>>> Ejecutando comando "notificaciones:sistema" <<<<< ! >>>>>');

        $notificaciones = Notificacion::All()
            ->where('estado', 1)
            ->where('tipo', 'S')
            ->where('automatica', 1);
        foreach ($notificaciones as $not) {
            $funcion = $not->nombre;
            $this->$funcion($not);
        }

        $time_duration = difFechas(date('Y-m-d H:i:s'), $ini)->h . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->m . ':' . difFechas(date('Y-m-d H:i:s'), $ini)->s;
        Log::info('<*> DURACION: ' . $time_duration . '  <*>');
        Log::info('<<<<< * >>>>> Fin satisfactorio del comando "notificaciones:sistema" <<<<< * >>>>>');
        dump('<*> DURACION: ' . $time_duration . '  <*>');
        dump('<<<<< * >>>>> Fin satisfactorio del comando "notificaciones:sistema" <<<<< * >>>>>');
    }

    public function flores_pasadas_cuarto_frio($not)
    {
        $sum = DB::table('inventario_frio')
            ->select(DB::raw('sum(disponibles) as cant'))
            ->where('estado', '=', 1)
            ->where('basura', '=', 0)
            ->where('disponibilidad', '=', 1)
            ->where('disponibles', '>', 0)
            ->where('fecha_ingreso', '<', opDiasFecha('-', 5, date('Y-m-d')))
            ->get()[0]->cant;

        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if ($sum > 0) { // crear las nuevas notificaciones
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Flores pasadas en cuarto frío';
                $model->texto = 'Hay ' . $sum . ' ramos en cuarto frío con más de 5 días';
                $model->url = 'cuarto_frio';
                $model->save();
            }
        }
    }

    public function pedidos_sin_empaquetar($not)
    {
        $pedidos = DB::table('pedido')
            ->select('*')
            ->where('estado', '=', 1)
            ->where('empaquetado', '=', 0)
            ->where('variedad', '!=', '')
            ->where('fecha_pedido', '<', date('Y-m-d'))
            ->get();

        $sum = 0;
        foreach ($pedidos as $ped) {
            if (!getFacturaAnulada($ped->id_pedido)) {
                $sum++;
            }
        }

        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if ($sum > 0) { // crear las nuevas notificaciones
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Pedidos pasados sin empaquetar';
                $model->texto = 'Hay ' . $sum . ' pedido(s) sin empaquetar de días pasados';
                $model->url = 'pedidos';
                $model->save();
            }
        }
    }

    public function botar_flores_cuarto_frio($not)
    {
        $sum = DB::table('inventario_frio')
            ->select(DB::raw('sum(disponibles) as cant'))
            ->where('estado', '=', 1)
            ->where('basura', '=', 0)
            ->where('disponibilidad', '=', 1)
            ->where('disponibles', '>', 0)
            ->where('fecha_ingreso', '<=', opDiasFecha('-', 9, date('Y-m-d')))
            ->get()[0]->cant;

        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if ($sum > 0) { // crear las nuevas notificaciones
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Flores pasadas en cuarto frío';
                $model->texto = 'Hay ' . $sum . ' ramos en cuarto frío con 9 o más días';
                $model->url = 'cuarto_frio';
                $model->save();
            }
        }
    }

    public function clasificacion_verde_sin_cerrar($not)
    {
        $query = DB::table('clasificacion_verde')
            ->select('*')
            ->where('estado', '=', 1)
            ->where('activo', '=', 1)
            ->where('fecha_ingreso', '<=', opDiasFecha('-', 1, date('Y-m-d')))
            ->get();

        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($query) > 0) { // crear las nuevas notificaciones
            foreach ($query as $q) {
                foreach ($not->usuarios as $not_user) {
                    $model = new UserNotification();
                    $model->id_notificacion = $not->id_notificacion;
                    $model->id_usuario = $not_user->id_usuario;
                    $model->titulo = 'Clasificación Verde por terminar';
                    $model->texto = 'La clasificación Verde del día: ' . $q->fecha_ingreso . ' no se ha terminado';
                    $model->url = 'clasificacion_verde';
                    $model->save();
                }
            }
        }
    }

    public function ciclos_programados($not)
    {
        $sem_actual = getSemanaByDate(date('Y-m-d'));
        $query = DB::table('proyeccion_modulo')
            ->select('*')
            ->where('estado', '=', 1)
            ->where('fecha_inicio', '>=', $sem_actual->fecha_inicial)
            ->where('fecha_inicio', '<=', $sem_actual->fecha_final)
            ->get();

        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($query) > 0) { // crear las nuevas notificaciones
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Nuevos ciclos';
                $model->texto = 'Hay ' . count($query) . ' nuevos ciclos programados para esta semana';
                $model->url = 'sectores_modulos';
                $model->save();
            }
        }
    }

    static function fallos_upload_unosoft_venta($faltantes)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_unosoft_venta')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($faltantes) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($faltantes as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de las ventas (unosoft)';
                $model->texto = $texto;
                $model->url = 'costos_importar';
                $model->save();
            }
        }
    }

    static function fallos_upload_unosoft_bouquetera($faltantes)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_unosoft_bouquetera')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($faltantes) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($faltantes as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de las ventas bouqueteras (unosoft)';
                $model->texto = $texto;
                $model->url = 'costos_importar';
                $model->save();
            }
        }
    }

    static function fallos_upload_ingreso_bouquetera($faltantes)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_ingreso_bouquetera')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($faltantes) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($faltantes as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de los ingresos de flor bouquetera';
                $model->texto = $texto;
                $model->url = 'ingreso_bouquetera';
                $model->save();
            }
        }
    }

    static function fallos_upload_insumos($faltantes)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_insumos')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($faltantes) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($faltantes as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de los costos de insumos';
                $model->texto = $texto;
                $model->url = 'costos_importar';
                $model->save();
            }
        }
    }

    static function fallos_upload_mano_obra($faltantes)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_mano_obra')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($faltantes) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($faltantes as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de los costos de mano de obra';
                $model->texto = $texto;
                $model->url = 'costos_importar';
                $model->save();
            }
        }
    }

    static function fallos_upload_recetas($fallos)
    {
        $not = Notificacion::All()
            ->where('nombre', 'fallos_upload_recetas')
            ->first();
        $models = UserNotification::All()
            ->where('estado', 1)
            ->where('id_notificacion', $not->id_notificacion);
        foreach ($models as $m) {   // desactivar las anteriores
            $m->delete();
        }
        if (count($fallos) > 0) { // crear las nuevas notificaciones
            $texto = '<ul>';
            foreach ($fallos as $f) {
                $texto .= '<li>' . $f . '</li>';
            }
            $texto .= '</ul>';
            foreach ($not->usuarios as $not_user) {
                $model = new UserNotification();
                $model->id_notificacion = $not->id_notificacion;
                $model->id_usuario = $not_user->id_usuario;
                $model->titulo = 'Fallos en la subida de las recetas';
                $model->texto = $texto;
                $model->url = 'plantas_variedades';
                $model->save();
            }
        }
    }
}
