<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login', 'YuraController@login');
Route::post('login', 'YuraController@verificaUsuario');
Route::get('logout', 'YuraController@logout');

Route::get('configuracion/inputs_dinamicos_detalle_empaque', 'ConfiguracionEmpresaController@vistaInputsDetallesEmpaque')->name('view.inputs_detalle_empaque');
Route::get('configuracion/campos_empaques', 'ConfiguracionEmpresaController@campos_empaque')->name('view.campos_empaque');

Route::group(['middleware' => 'autenticacion'], function () {

    Route::group(['middleware' => 'controlsession'], function () {
        Route::get('/', 'YuraController@inicio');
        Route::get('dashboard', 'YuraController@listar_dashboard_inicial');
        Route::get('select_filtro_variedad', 'YuraController@select_filtro_variedad');
        Route::post('update_finca_activa', 'YuraController@update_finca_activa');
        Route::get('detallar_indicador', 'YuraController@detallar_indicador');
        Route::get('mostrar_indicadores_claves', 'YuraController@mostrar_indicadores_claves');
        Route::get('cargar_accesos_directos', 'YuraController@cargar_accesos_directos');
        Route::get('cargar_fincas_propias', 'YuraController@cargar_fincas_propias');
        Route::get('cargar_submenu_crm', 'YuraController@cargar_submenu_crm');
        Route::post('save_config_user', 'YuraController@save_config_user');
        Route::get('perfil', 'YuraController@perfil');
        Route::get('perfil/admin_accesos', 'YuraController@admin_accesos');
        Route::post('perfil/seleccionar_submenu', 'YuraController@seleccionar_submenu');
        Route::post('perfil/update_usuario', 'YuraController@update_usuario');
        Route::post('perfil/update_image_perfil', 'YuraController@update_image_perfil');
        Route::post('perfil/update_password', 'YuraController@update_password');

        Route::post('usuarios/get_usuario_json', 'UsuarioController@get_usuario_json');

        Route::get('select_planta', 'YuraController@select_planta');
        Route::get('select_plantaByFinca', 'YuraController@select_plantaByFinca');
        Route::get('select_planta_global', 'YuraController@select_planta_global');

        include 'documento/rutas.php';
        include 'crm/dashboard.php';
        Route::get('pedidos/crear_packing_list/{id_pedido}/{despacho?}', 'PedidoController@crear_packing_list');

        Route::get('crm_postcosecha/actualizar_cosecha_x_variedad', 'CRM\crmPostocechaController@actualizar_cosecha_x_variedad');

        Route::group(['middleware' => 'permiso'], function () {
            /* ========================== GUARDE ========================*/
            include "ai/chatbot.php";

            /* ========================== GUARDE ========================*/
            include "guarde/guarde_flor.php";

            /* ========================== POSTCO ========================*/
            include "postco/importar_postco.php";
            include "postco/preproduccion.php";
            include "postco/ot_postco.php";
            include "postco/reporte_disponibilidad.php";
            include "postco/motivos_reclamos.php";
            include "postco/reporte_reclamos.php";
            include "postco/ingresos_proveedor.php";
            include "postco/ingreso_flor_nacional.php";
            include "postco/reporte_flor_nacional.php";
            include "postco/re_armado.php";
            include "postco/propuestas.php";

            /* ========================== POSTCOSECHA ========================*/
            include "postcosecha/clasificaciones.php";
            include "postcosecha/ingreso_clasificacion.php";
            include "postcosecha/exportadores.php";
            include "postcosecha/despachos_preproduccion.php";
            include "postcosecha/planificacion.php";
            include "postcosecha/despacho_proveedor.php";
            include "postcosecha/tallos_preproduccion.php";
            include "postcosecha/correcion_inventario.php";

            /* ========================== POSTCPCECHA ========================*/
            include 'postcocecha/lotes.php';
            include 'postcocecha/clasificacion_blanco.php';
            include 'postcocecha/cuarto_frio.php';
            include 'postcocecha/despachos.php';
            include 'postcocecha/apertura.php';
            include 'postcocecha/clasificacion_verde.php';
            include 'postcocecha/recepcion.php';
            include 'postcocecha/clientes.php';
            include 'postcocecha/consignatario.php';
            include 'postcocecha/cosechadores.php';
            include 'postcocecha/despachadores.php';
            include 'postcocecha/reporte_cuarto_frio.php';
            include 'postcocecha/reporte_postcosecha.php';
            include 'postcocecha/inventario_cosecha.php';
            include 'postcocecha/compra_flor.php';
            include 'postcocecha/inventario_compra_flor.php';
            include 'postcocecha/resumen_recepcion.php';
            include 'postcocecha/orden_trabajo.php';
            include 'postcocecha/salidas_recepcion.php';
            include 'postcocecha/disponibilidad_flor.php';

            include 'sectores_modulos/rutas.php';
            include 'sectores_modulos/perennes.php';
            include 'semanas/rutas.php';
            include 'plantas_variedades/rutas.php';

            include 'menu_sistema/rutas.php';
            include 'permisos/rutas.php';
            include 'usuarios/rutas.php';

            include 'configuracion_empresa/rutas.php';
            include 'postcocecha/agencias_carga.php';
            include 'postcocecha/marcas.php';
            //include 'postcocecha/pedidos_ventas.php';
            //include 'postcocecha/envios.php';
            include 'postcocecha/aerolinea.php';
            include 'postcocecha/especificacion.php';
            include 'postcocecha/cajas_presentaciones.php';
            include 'postcocecha/precio.php';
            include 'postcocecha/dato_exportacion.php';
            include 'postcocecha/transportista.php';
            include 'postcocecha/etiqueta.php';
            include 'postcocecha/etiqueta_factura.php';
            include 'bouquetera/ingreso_bouquetera.php';
            include 'bouquetera/distribucion_bqt.php';

            /* ========================== BODEGA ========================*/
            include 'bodega/productos.php';
            include 'bodega/movimientos_bodega.php';
            include 'bodega/categorias_producto.php';
            include 'bodega/proveedores.php';

            /* ========================== COMERCIALIZACION ========================*/
            //include 'pedidos/pedido.php';
            include 'comercializacion/pedidos.php';
            include 'comercializacion/importar_pedidos.php';

            /* ========================== CRM ========================*/
            include 'crm/postcosecha.php';
            include 'crm/ventas.php';
            include 'crm/ventas_m2.php';
            include 'crm/crm_area.php';
            include 'crm/rendimiento_desecho.php';
            include 'crm/tbl_postcosecha.php';
            include 'crm/fue.php';
            include 'crm/regalias_semanas.php';
            include 'crm/tbl_ventas.php';
            include 'crm/tbl_rendimiento.php';
            include 'crm/fenograma_ejecucion.php';
            include 'crm/crm_proyeccion.php';
            include 'crm/propagacion.php';
            include 'crm/resumen_ebitda.php';
            include 'crm/bqt_diaria.php';
            include 'crm/tabla_bqt.php';

            include 'facturacion/tipo_impuesto.php';

            /* ========================== FACTURACIÓN ========================*/
            /*include 'facturacion/tipo_comprobante.php';
            include 'facturacion/tipo_identificacion.php';

            include 'facturacion/emision_comprobante.php';
            include 'facturacion/codigo_dae.php';
            include 'facturacion/producto_venture.php';
            include 'facturacion/orden_factura.php';*/

            /* ================== IMPORTAR DATA =================== */
            include 'importar_data/rutas.php';
            include 'importar_data/importar_unosoft.php';

            /* ================== NOTIFICACIONES =================== */
            include 'notificaciones/rutas.php';

            /* ================== PROYECCIONES =================== */
            include 'proyecciones/cosecha.php';
            include 'proyecciones/ventas_x_cliente.php';
            include 'proyecciones/resumen_total.php';
            include 'proyecciones/mano_obra.php';
            include 'proyecciones/monitoreo_ciclos.php';
            include 'proyecciones/curva_estandar.php';
            include 'proyecciones/temperaturas.php';
            include 'proyecciones/perennes.php';
            include 'proyecciones/no_perennes.php';
            include 'proyecciones/fenograma_perennes.php';
            include 'proyecciones/proyecciones.php';
            include 'proyecciones/resumen_proyecciones.php';
            include 'proyecciones/fenograma_no_perennes.php';
            include 'proyecciones/dashboard_temperaturas.php';
            include 'proyecciones/ejecucion_no_perennes.php';

            /* ================== COSTOS =================== */
            include 'costos/insumo.php';
            include 'costos/mano_obra.php';
            include 'costos/importar.php';
            include 'costos/generales.php';
            include 'costos/pyg_semanal_empresas.php';
            include 'costos/tabla_operaciones.php';
            include 'costos/ebitda_x_variedad.php';

            /* ================== PROPAGACION =============== */
            include 'propagacion/camas_ciclos.php';
            include 'propagacion/configuraciones.php';
            include 'propagacion/cosecha_plantas_madres.php';
            include 'propagacion/fenograma.php';
            include 'propagacion/enraizamiento.php';
            include 'propagacion/disponibilidad.php';
            include 'propagacion/resumen_ptas_madres.php';
            include 'propagacion/ingreso_disponibilidad.php';
            include 'propagacion/inventario_enraizamiento.php';
            include 'propagacion/reporte_enraizamiento.php';

            /* ================== COSECHA_DIARIA =============== */
            include 'crm/cosecha_diaria.php';

            /* ================== DB =================== */
            include 'db/rutas.php';
            include 'db/envio_reporte.php';

            /* ================== FINCAS =================== */
            include 'fincas/fincas.php';

            /* ================== RRHH =================== */
            include 'rrhh/parametros.php';
            include 'rrhh/personal.php';
            include 'rrhh/control_diario.php';

            /* ================== CAMPO =================== */
            include 'campo/aplicaciones.php';
            include 'campo/ciclo_luz.php';
            include 'campo/reporte_luz.php';
            include 'campo/ingreso_labores.php';
            include 'campo/reporte_labores.php';
            include 'campo/historico_luz.php';
            include 'campo/ejecucion_luz.php';
            include 'campo/ejecucion_labores.php';

            /* ================== ADMINISTRACION =================== */
            include 'administracion/permisos_acciones.php';
            include 'administracion/codigo_autorizacion.php';

            /* ================== TEST =================== */
            Route::get('proy_ganaderia', 'TestController@inicio');
            Route::get('proy_ganaderia/test', 'TestController@test');
        });

        /* ================== HELP =================== */
        include 'help/rutas.php';

        include 'colores/rutas.php';
        include 'codigo_barra/rutas.php';
        //include 'facturacion/comprobante.php';
        Route::get('cargar_utiles', 'YuraController@cargar_utiles');
    });
});
include 'notificaciones/otras.php';

Route::get('rectificar_semanas', 'YuraController@rectificar_semanas');
Route::get('test', 'YuraController@test');


/*Route::get('list-face', function () {

    $aws = AWS::createClient('rekognition');
    $result = $aws->listFaces([
        'CollectionId' => 'exportcalas-cotacachi',

    ]);
    dd($result);
});*/
