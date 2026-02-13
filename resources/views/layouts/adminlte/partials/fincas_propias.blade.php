@php
    $usuario = getUsuario(Session::get('id_usuario'));
@endphp
<li class="fincas_propias" id="li-master_fincas_propias">
    <select name="fincas_propias" id="fincas_propias" style="width: 100%; margin-top: 15px;"
            class="border-radius_18 input-yura_green" onchange="select_finca_propia($(this).val())" ondblclick="select_finca_propia($(this).val())">
        @if(count($fincas) == count(getAllFincas()))
            {{--<option value="T">Todas las fincas</option>--}}
        @endif
        @foreach($fincas as $f)
            <option value="{{$f->id_empresa}}" {{$f->id_empresa == $usuario->finca_activa ? 'selected' : ''}}>
                {{$f->empresa->nombre}}
            </option>
        @endforeach
    </select>
</li>

<li class="dropdown hidden" title="Fincas" id="li-master_fincas_propias_mini">
    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-industry text-color_yura"></i>
    </a>
    <ul class="dropdown-menu">
        @foreach($fincas as $f)
            <li class="{{$f->id_empresa == $usuario->finca_activa ? 'bg-yura_primary' : ''}} li-fincas_propias"
                id="li-fincas_propias_{{$f->id_empresa}}">
                <a href="javascript:void(0)" onclick="select_finca_propia('{{$f->id_empresa}}')"
                   class="{{$f->id_empresa == $usuario->finca_activa ? 'color_text-yura_white' : ''}} a-fincas_propias"
                   id="a-fincas_propias_{{$f->id_empresa}}">
                    {{$f->empresa->nombre}}
                </a>
            </li>
        @endforeach
    </ul>
</li>

<script>
    var vista_actual = $('#vista_actual').val();
    var finca_actual = $('#fincas_propias').val();

    if (vista_actual == 'dashboard') {
        $('#fincas_propias').val($('#finca_dashboard_inicial').val())
    }
    if (vista_actual == 'inicio_resumen') {
        /* =============== ACTUALIZAR FINCA_ACTIVA =========== */
        actualizar_finca_actual()
    }

    function actualizar_finca_actual(id) {
        datos = {
            _token: '{{csrf_token()}}',
            finca_actual: id,
        };
        $.post('{{url('update_finca_activa')}}', datos, function (retorno) {
            if (vista_actual == 'enraizamiento') {
                listar_enraizamientos();   // adminlte/gestion/propagacion/enraizamiento/script
            }
            if (vista_actual == 'propag_disponibilidad') {
                listar_disponibilidades();   // adminlte/gestion/propagacion/disponibilidad/script
            }
            if (vista_actual == 'resumen_plantas_madres') {
                listar_resumen_ptas_madres();   // adminlte/gestion/propagacion/resumen_ptas_madres/script
            }
            if (vista_actual == 'recepcion') {
                buscar_listado_recepcion();   // adminlte/gestion/postcocecha/recepciones/script
                buscar_cosecha_recepcion();   // adminlte/gestion/postcocecha/recepciones/script
            }
            if (vista_actual == 'sectores_modulos') {
                listar_sectores_modulos();  // adminlte/gestion/sectores_modulos/script
                listar_ciclos_sectores_modulos();
            }
            if (vista_actual == 'db_indicadores') {
                listar_indicadores();   // adminlte/gestion/db/indicadores
            }
            if (vista_actual == 'fenograma_ejecucion') {
                filtrar_ciclos_fenograma_ejecucion();   // adminlte/crm/fenograma_ejecucion/script
            }
            if (vista_actual == 'costos_generales') {
                listar_reporte_costos_generales();   // adminlte/gestion/costos/generales/inicio
            }
            if (vista_actual == 'proy_cosecha') {
                listar_proyecciones_cosecha();   // adminlte/gestion/proyecciones/cosecha/script
            }
            if (vista_actual == 'ciclo_luz') {
                listar_ciclo_luz();   // adminlte/gestion/campo/ciclo_luz/script
            }
            if (vista_actual == 'reporte_luz') {
                listar_reporte_luz();   // adminlte/gestion/campo/reporte_luz/script
            }
            if (vista_actual == 'camas_ciclos') {
                listar_camas_propag();   // adminlte/gestion/propagacion/camas_ciclos/script
                listar_ciclos_propag();   // adminlte/gestion/propagacion/camas_ciclos/script
                id != 'T' ? $('#id_empresa').val(id) : '';
            }
            if (vista_actual == 'fenograma_propag') {
                filtrar_ciclos_fenograma_propag();   // adminlte/crm/propagacion/fenograma/script
            }
            if (vista_actual == 'proy_resumen_total') {
                listar_proyecciones_proy_resumen_total();   // adminlte/gestion/proyecciones/resumen_total/script
            }
            if (vista_actual == 'resumen_proyecciones') {
                listar_resumen_proyecciones();   // adminlte/gestion/proyecciones/resumen_proyecciones/script
            }
            if (vista_actual == 'temperaturas') {
                listar_temperaturas();   // adminlte/gestion/proyecciones/temperaturas/script
            }
            if (vista_actual == 'proy_no_perennes') {
                listar_proyecciones_no_perennes();   // adminlte/gestion/proyecciones/no_perennes/script
            }
            if (vista_actual == 'ingreso_proyecciones') {
                listar_ingreso_proyecciones();   // adminlte/gestion/proyecciones/proyecciones/script
            }
            if (vista_actual == 'sectores_modulos_perennes') {
                listar_ciclos_sect_mod_perennes();   // adminlte/gestion/sectores_modulos_perennes/script
            }
            if (vista_actual == 'fenograma_perennes') {
                listar_fenograma_perennes();   // adminlte/gestion/proyecciones/fenograma_perennes/script
            }
            if (vista_actual == 'fenograma_no_perennes') {
                listar_fenograma_no_perennes();   // adminlte/gestion/proyecciones/fenograma_no_perennes/script
            }
            if (vista_actual == 'resumen_ebitda') {
                buscar_resumen_ebitda();   // adminlte/crm/resumen_ebitda/script
            }
            if (vista_actual == 'monitoreo_ciclos') {
                listar_ciclos_alturas();   // adminlte/gestion/proyecciones/monitoreo/script
            }
            if (vista_actual == 'ingreso_disponibilidad') {
                listar_ingreso_disponibilidad();   // adminlte/gestion/propagacion/ingreso_disponibilidad/script
            }
            if (vista_actual == 'inventario_enraizamiento') {
                listar_inventario_enraizamiento();   // adminlte/gestion/propagacion/inventario_enraizamiento/script
            }
            if (vista_actual == 'reporte_enraizamiento') {
                listar_reporte_enraizamiento();   // adminlte/crm/propagacion/enraizamiento/script
            }
            if (vista_actual == 'tabla_operaciones') {
                listado_operaciones();   // adminlte/gestion/costos/tabla_operaciones/script
            }
            if (vista_actual == 'ebitda_x_variedad') {
                listado_ebitda_x_variedad();   // adminlte/gestion/costos/ebitda_x_variedad/script
            }
            if (vista_actual == 'ejecucion_no_perennes') {
                listar_ejecucion_no_perennes();   // adminlte/gestion/proyecciones/ejecucion_no_perennes/script
            }
            if (vista_actual == 'ejecucion_no_perennes') {
                listar_ejecucion_no_perennes();   // adminlte/gestion/proyecciones/ejecucion_no_perennes/script
            }
            if (vista_actual == 'inventario_cosecha') {
                listar_inventario_cosecha();   // adminlte/gestion/postcocecha/inventario_cosecha/script
            }
            if (vista_actual == 'cosechadores') {
                buscar_listado_cosechadores();   // adminlte/gestion/cosechadores/script
            }
            if (vista_actual == 'clasificaciones') {
                listar_ramos();   // adminlte/gestion/postcocecha/clasificaciones/script
                listar_presentaciones();   // adminlte/gestion/postcocecha/clasificaciones/script
                listar_cajas();   // adminlte/gestion/postcocecha/clasificaciones/script
            }
            if (vista_actual == 'ingreso_clasificacion') {
                listar_blanco();   // adminlte/gestion/postcocecha/ingreso_clasificacion/script
            }
            if (vista_actual == 'dashboard') {
                $.LoadingOverlay('show');
                location.href = '{{url('dashboard')}}' + '?f=' + id;
            }
            if (vista_actual == 'intervalo_indicador') {
                $.LoadingOverlay('show');
                location.href = '{{url('intervalo_indicador')}}';
            }

            array_reload = [
                'dashboard_propagacion',
                'crm_area',
                'costos_gestion',
                'gestion_mano_obra',
                'cosecha_plantas_madres',
                'reporte_insumos',
                'reporte_mano_obra',
                'reporte_labores',
                'ejecucion_luz',
                'ejecucion_labores',
                'proy_perennes',
                'cosecha_diaria',
                'reporte_cuarto_frio',
                'reporte_postcosecha',
                'pedidos',
                'importar_pedidos',
                '/control_diario',
                '/dashboard_personal'
            ];
            console.log(vista_actual)
            if (array_reload.indexOf(vista_actual) != -1) {
                $.LoadingOverlay('show');
                location.reload();
            }
        }, 'json');
    }

    function select_finca_propia(id) {
        $('#fincas_propias').val(id);
        $('.li-fincas_propias').removeClass('bg-yura_primary');
        $('#li-fincas_propias_' + id).addClass('bg-yura_primary');
        $('.a-fincas_propias').removeClass('color_text-yura_white');
        $('#a-fincas_propias_' + id).addClass('color_text-yura_white');
        /* =============== ACTUALIZAR FINCA_ACTIVA =========== */
        actualizar_finca_actual(id);
    }
</script>
