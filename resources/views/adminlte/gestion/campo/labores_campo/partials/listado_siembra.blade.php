<div style="overflow-y: scroll; overflow-x: scroll; height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_labores">
        <thead>
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green" rowspan="2">
                <input type="checkbox" id="check_all_ciclos" class="mouse-hand"
                       onchange="$('.check_ciclo').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Var.
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Mód.
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                P/S
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">
                    Ini.
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Días
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Fecha
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Rep.
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Plantas
            </th>
            <th class="text-center th_yura_green">
                <div style="width: 80px">
                    Horas día
                </div>
            </th>
            @foreach($mano_obras as $mo)
                <th class="text-center bg-yura_dark th_detalles" rowspan="2">
                    <div style="width: 120px">
                        {{$mo->nombre}}
                    </div>
                    <input type="hidden" class="ids_mano_obras" value="{{$mo->id_mano_obra}}">
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                <div style="width: 80px">
                    Hombres día
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                Horas Necesarias
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-fw fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)"
                               onclick="$('.sin_labor').toggleClass('hidden'); calcular_totales_desbrote()">
                                <i class="fa fa-fw fa-plus"></i> Mostrar/ocultar módulos sin hacer
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="add_adicional()">
                                <i class="fa fa-fw fa-plus"></i> Adicional
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="store_all_labor()">
                                <i class="fa fa-fw fa-save"></i> Grabar Seleccionados
                            </a>
                        </li>
                        {{--<li>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="exportar_reporte()">
                                <i class="fa fa-fw fa-file-excel-o"></i> Exportar a Excel
                            </a>
                        </li>--}}
                    </ul>
                </div>
            </th>
        </tr>
        <tr>
            <th class="text-center th_yura_green">
                <input type="number" class="text-center" style="width: 100%; background-color: #00b388" id="input_horas_dia"
                       onchange="set_all_inputs('input_horas_dia')" onkeyup="set_all_inputs('input_horas_dia')">
            </th>
            <th class="text-center th_yura_green">
                <input type="number" class="text-center" style="width: 100%; background-color: #00b388" id="input_hombres_dia"
                       onchange="set_all_inputs('input_hombres_dia')" onkeyup="set_all_inputs('input_hombres_dia')">
            </th>
        </tr>
        </thead>
        <tbody>
        @php
            $pos = 0;
            $min_fecha = opDiasFecha('-', 7, $semana_req->fecha_inicial);
            $max_fecha = opDiasFecha('+', 7, $semana_req->fecha_final);
            $total_plantas = 0;
            $total_horas_dias = 0;
            $total_horas_necesarias = 0;
            $total_personas = 0;
        @endphp
        @foreach($listado as $item)
            @php
                $modulo = $item['ciclo']->modulo;
                $plantas = $item['ciclo']->plantas_actuales();

                $aplicacion = $item['aplicacion'];
                $count = count($item['labores']) > 0 ? count($item['labores']) : 1;
            @endphp
            @for($i = 0; $i < $count; $i++)
                @php
                    $show = true;
                    $labor = count($item['labores']) > 0 ? $item['labores'][$i] : '';
                    $repeticion = '';
                    $fecha = '';
                    $horas_dia = 8;
                    $hombres_dia = 0;
                    $horas_necesarias = 8;
                    if ($labor != ''){
                        $dias_ciclo = difFechas($labor->fecha, $item['ciclo']->fecha_inicio)->days;
                        $fecha = $labor->fecha;
                        $repeticion = $labor->repeticion;
                        $horas_dia = $labor->horas_dia;
                        $plantas = $labor->plantas;
                        $hombres_dia = $labor->hombres_dia;
                        $horas_necesarias = $labor->horas_necesarias;
                    } else {
                        if ($semana_req->codigo == $semana_actual->codigo){
                            $dias_ciclo = difFechas(hoy(), $item['ciclo']->fecha_inicio)->days;
                        } else {
                            $dias_ciclo = difFechas($semana_req->fecha_inicial, $item['ciclo']->fecha_inicio)->days;
                        }

                        $lastLabor = $item['ciclo']->getLastLaborByFecha($aplicacion->id_aplicacion, $semana_req->fecha_final);
                        if (isset($lastLabor)){
                            $fecha = $aplicacion->frecuencia != '' ? opDiasFecha('+', $aplicacion->frecuencia, $lastLabor->fecha) : opDiasFecha('+', 7, $lastLabor->fecha);
                            $repeticion = $lastLabor->repeticion + 1;
                            if ($lastLabor->repeticion >= $aplicacion->repeticiones)
                                $show = false;
                        } else {
                            $fecha = $item['fecha'];
                            $repeticion = $item['repeticion'];
                        }
                    }
                    $total_plantas += $plantas;
                    $total_horas_dias += $horas_dia;
                    $total_horas_necesarias += $horas_necesarias;
                @endphp
                @if($show)
                    <tr id="tr_ciclo_{{$pos}}" class="{{$labor == '' ? 'sin_labor' : ''}}">
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="checkbox" id="check_ciclo_{{$pos}}" class="check_ciclo mouse-hand">
                            <input type="hidden" id="id_ciclo_{{$pos}}" value="{{$item['ciclo']->id_ciclo}}">
                            <input type="hidden" id="id_aplicacion_{{$pos}}" value="{{$aplicacion->id_aplicacion}}">
                            <input type="hidden" id="id_aplicacion_campo_{{$pos}}"
                                   value="{{$labor != '' ? $labor->id_aplicacion_campo : ''}}">
                            <input type="hidden" value="{{$pos}}" class="pos">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            {{$item['ciclo']->variedad->siglas}}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            {{$modulo->nombre}}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            {{$modulo->getPodaSiembraByCiclo($item['ciclo']->id_ciclo)}}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            {{convertDateToText($item['ciclo']->fecha_inicio)}}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            {{$dias_ciclo}}
                        </th>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="date" id="fecha_{{$pos}}" style="width: 100%;"
                                   class="text-center" min="{{$min_fecha}}" value="{{$fecha}}"
                                   max="{{$max_fecha}}">
                            <span class="hidden" id="span_fecha_{{$pos}}">{{$fecha}}</span>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="number" id="repeticion_{{$pos}}" style="width: 100%;"
                                   class="text-center" min="{{$repeticion}}" value="{{$repeticion}}">
                            <span class="hidden" id="span_repeticion_{{$pos}}">{{$repeticion}}</span>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="number" id="plantas_{{$pos}}" style="width: 100%;"
                                   class="text-center" min="0" value="{{$plantas}}"
                                   onclick="calcular_hombres_dia('{{$pos}}')"
                                   onchange="calcular_hombres_dia('{{$pos}}')"
                                   onkeyup="calcular_hombres_dia('{{$pos}}')">
                            <input type="hidden" id="span_plantas_{{$pos}}" value="{{$plantas}}">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="number" id="horas_dia_{{$pos}}" style="width: 100%;"
                                   class="text-center input_horas_dia_{{$pos}}" min="0" value="{{$horas_dia}}"
                                   onclick="calcular_hombres_dia('{{$pos}}')"
                                   onchange="calcular_hombres_dia('{{$pos}}')"
                                   onkeyup="calcular_hombres_dia('{{$pos}}')">
                            <input type="hidden" id="span_horas_dia_{{$pos}}" value="{{$horas_dia}}">
                        </td>
                        @foreach($mano_obras as $mo)
                            @php
                                $detalle = $labor != '' ? $labor->getDetalleByManoObra($mo->id_mano_obra) : '';
                                if ($detalle != ''){
                                    $dosis = $detalle->dosis;
                                } else {
                                    $dosis = '';
                                    if ($mezcla != ''){
                                        // buscar por el parametro del detalle
                                        foreach ($detalles_mezcla as $det_m){
                                            if ($det_m->id_mano_obra == $mo->id_mano_obra){
                                                foreach ($det_m->parametros as $par) {
                                                    if ($par->tipo == 'E') { // Estandar
                                                        $dosis = $par->cantidad_mo;
                                                    }
                                                }
                                            }
                                        }
                                        if ($mezcla->repeticiones != '0'){  // esta parametrizada por repeticiones
                                            $array_repeticiones = $item['ciclo']->poda_siembra == 'S' ? explode('-', $mezcla->repeticiones) : explode('-', $mezcla->repeticiones_poda);
                                            if (in_array($repeticion, $array_repeticiones)){    // el numero de la repeticion esta dentro de los parametros
                                                for($r = 0; $r < count($array_repeticiones); $r++){
                                                    if ($repeticion == $array_repeticiones[$r]){
                                                        $dosis = $item['ciclo']->poda_siembra == 'S' ? explode('-', $mezcla->litros_x_repeticiones)[$r] : explode('-', $mezcla->litros_x_repeticiones_poda)[$r];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <td class="text-center th_detalles" style="border-color: #9d9d9d">
                                <input type="number" id="dosis_{{$mo->id_mano_obra}}_{{$pos}}" style="width: 100%;"
                                       class="text-center dosis_{{$pos}}" value="{{$dosis}}" placeholder="Rend. x plantas"
                                       onclick="calcular_hombres_dia('{{$pos}}')"
                                       onchange="calcular_hombres_dia('{{$pos}}')"
                                       onkeyup="calcular_hombres_dia('{{$pos}}')">
                                <span class="hidden" id="span_dosis_{{$mo->id_mano_obra}}_{{$pos}}">{{$dosis}}</span>
                            </td>
                        @endforeach
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="number" id="hombres_dia_{{$pos}}" style="width: 100%;"
                                   class="text-center input_hombres_dia_{{$pos}}" min="0" value="{{$hombres_dia}}"
                                   onclick="calcular_horas_necesarias('{{$pos}}')"
                                   onchange="calcular_horas_necesarias('{{$pos}}')"
                                   onkeyup="calcular_horas_necesarias('{{$pos}}')">
                            <input type="hidden" id="span_hombres_dia_{{$pos}}" value="{{$hombres_dia}}">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <input type="number" id="horas_necesarias_{{$pos}}" style="width: 100%; background-color: #e9ecef" readonly
                                   class="text-center" min="0" value="{{$horas_necesarias}}">
                            <input type="hidden" id="span_horas_necesarias_{{$pos}}" value="{{$horas_necesarias}}">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <div class="btn-group">
                                @if($labor == '')
                                    <button type="button" class="btn btn-xs btn-yura_primary"
                                            onclick="store_labor('{{$pos}}', '{{$aplicacion->id_aplicacion}}')">
                                        <i class="fa fa-fw fa-plus"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-xs btn-yura_dark dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-fw fa-gears"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               onclick="ver_labores_by_ciclo('{{$item['ciclo']->id_ciclo}}', '{{$aplicacion->id_aplicacion}}')">
                                                <i class="fa fa-fw fa-eye"></i> Ver labores del ciclo
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               onclick="duplicar_labor('{{$pos}}', '{{$labor->id_aplicacion_campo}}')">
                                                <i class="fa fa-fw fa-pencil"></i> Duplicar labor
                                            </a>
                                        </li>
                                        <li class="list-seperator"></li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               onclick="update_labor('{{$pos}}', '{{$labor->id_aplicacion_campo}}', '{{$aplicacion->id_aplicacion}}')">
                                                <i class="fa fa-fw fa-pencil"></i> Grabar
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               onclick="delete_labor('{{$labor->id_aplicacion_campo}}')">
                                                <i class="fa fa-fw fa-trash"></i> Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if($labor == '')
                        <script>
                            calcular_hombres_dia('{{$pos}}');
                            calcular_horas_necesarias('{{$pos}}');
                        </script>
                    @endif
                    @php
                        $pos++;
                    @endphp
                @endif
            @endfor
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th class="th_yura_green" colspan="8" style="padding-left: 10px">
                TOTALES
            </th>
            <th class="text-center th_yura_green" id="th_total_plantas">
                {{number_format($total_plantas)}}
            </th>
            <th class="text-center th_yura_green" id="th_prom_horas_dia">
                {{$pos > 0 ? round($total_horas_dias / $pos) : 0}}
            </th>
            <td class="text-center th_detalles th_yura_green" colspan="{{count($mano_obras)}}">
            </td>
            <th class="text-center th_yura_green" id="th_total_hombres_dia">
                {{$pos > 0 ? round($total_horas_necesarias / ($total_horas_dias / $pos)) : 0}}
            </th>
            <th class="text-center th_yura_green" id="th_total_horas_necesarias">
                {{number_format($total_horas_necesarias)}}
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
        </tfoot>
    </table>
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>

<script>
    function duplicar_labor(pos, app_campo) {
        datos = {
            _token: '{{csrf_token()}}',
            app_campo: app_campo,
        };
        post_jquery_m('{{url('ingreso_labores/duplicar_labor')}}', datos, function () {
            listar_labores();
        }, 'tr_ciclo_' + pos);
    }

    function store_labor(pos, aplicacion) {
        data = [];
        ids_mano_obras = $('.ids_mano_obras');
        for (i = 0; i < ids_mano_obras.length; i++) {
            id_mo = ids_mano_obras[i].value;
            dosis = $('#dosis_' + id_mo + '_' + pos).val();
            if (dosis > 0) {
                data.push({
                    mo: id_mo,
                    dosis: dosis,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                ciclo: $('#id_ciclo_' + pos).val(),
                aplicacion: aplicacion,
                fecha: $('#fecha_' + pos).val(),
                repeticion: $('#repeticion_' + pos).val(),
                horas_dia: $('#horas_dia_' + pos).val(),
                plantas: $('#plantas_' + pos).val(),
                hombres_dia: $('#hombres_dia_' + pos).val(),
                horas_necesarias: $('#horas_necesarias_' + pos).val(),
                data: data,
            };
            post_jquery_m('{{url('ingreso_labores/store_labor')}}', datos, function () {
                listar_labores();
            }, 'tr_ciclo_' + pos);
        } else {
            alerta('<div class="alert alert-danger text-center">Faltan datos de rendimiento</div>');
        }
    }

    function store_all_labor() {
        array_pos = $('.pos');
        data = [];
        for (i = 0; i < array_pos.length; i++) {
            pos = array_pos[i].value;
            if ($('#check_ciclo_' + pos).prop('checked') == true) {
                detalles = [];
                ids_mano_obras = $('.ids_mano_obras');
                for (y = 0; y < ids_mano_obras.length; y++) {
                    id_mo = ids_mano_obras[y].value;
                    dosis = $('#dosis_' + id_mo + '_' + pos).val();
                    if (dosis > 0) {
                        detalles.push({
                            mo: id_mo,
                            dosis: dosis,
                        });
                    }
                }
                if (detalles.length > 0)
                    data.push({
                        ciclo: $('#id_ciclo_' + pos).val(),
                        aplicacion: $('#id_aplicacion_' + pos).val(),
                        fecha: $('#fecha_' + pos).val(),
                        repeticion: $('#repeticion_' + pos).val(),
                        horas_dia: $('#horas_dia_' + pos).val(),
                        plantas: $('#plantas_' + pos).val(),
                        hombres_dia: $('#hombres_dia_' + pos).val(),
                        horas_necesarias: $('#horas_necesarias_' + pos).val(),
                        detalles: detalles,
                    });
                else
                    alerta('<div class="alert alert-danger text-center">Faltan datos de rendimiento</div>');
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                data: data,
                labor: $('#filtro_labor').val(),
            };
            post_jquery_m('{{url('ingreso_labores/store_all_labor')}}', datos, function () {
                listar_labores();
            });
        }
    }

    function update_labor(pos, app_campo, aplicacion) {
        data = [];
        ids_mano_obras = $('.ids_mano_obras');
        for (i = 0; i < ids_mano_obras.length; i++) {
            id_mo = ids_mano_obras[i].value;
            dosis = $('#dosis_' + id_mo + '_' + pos).val();
            if (dosis > 0) {
                data.push({
                    mo: id_mo,
                    dosis: dosis,
                });
            }
        }
        if (data.length > 0) {
            datos = {
                _token: '{{csrf_token()}}',
                ciclo: $('#id_ciclo_' + pos).val(),
                aplicacion: aplicacion,
                app_campo: app_campo,
                fecha: $('#fecha_' + pos).val(),
                repeticion: $('#repeticion_' + pos).val(),
                horas_dia: $('#horas_dia_' + pos).val(),
                plantas: $('#plantas_' + pos).val(),
                hombres_dia: $('#hombres_dia_' + pos).val(),
                horas_necesarias: $('#horas_necesarias_' + pos).val(),
                data: data,
            };
            post_jquery_m('{{url('ingreso_labores/update_labor')}}', datos, function () {
                listar_labores();
            }, 'tr_ciclo_' + pos);
        }
    }

    function delete_labor(app_campo) {
        modal_quest('modal-quest_delete_labor',
            '<div class="text-center alert alert-warning">¿Desea <strong>ELIMINAR</strong> la labor?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '50%', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    app_campo: app_campo,
                };
                post_jquery_m('{{url('ingreso_labores/delete_labor')}}', datos, function () {
                    listar_labores();
                });
            });
    }

    function add_adicional() {
        datos = {
            semana: $('#filtro_semana').val(),
            labor: $('#filtro_labor').val(),
        };
        get_jquery('{{url('ingreso_labores/add_adicional')}}', datos, function (retorno) {
            modal_view('modal-view_add_adicional', retorno, '<i class="fa fa-fw fa-eye"></i> Adicional', true, false, '90%');
        });
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{url('ingreso_labores/exportar_reporte')}}?labor=' + $('#filtro_labor').val() +
            '&semana=' + $('#filtro_semana').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function ver_labores_by_ciclo(ciclo, aplicacion) {
        datos = {
            ciclo: ciclo,
            aplicacion: aplicacion,
        };
        get_jquery('{{url('ingreso_labores/ver_labores_by_ciclo')}}', datos, function (retorno) {
            modal_view('modal-view_ver_labores_by_ciclo', retorno, '<i class="fa fa-fw fa-eye"></i> Labores del ciclo', true, false, '80%');
        });
    }

    function set_all_inputs(campo) {
        array_pos = $('.pos');
        for (y = 0; y < array_pos.length; y++) {
            x = array_pos[y].value;
            var input = $('#' + campo).val();
            if ($('#check_ciclo_' + x).prop('checked') == true) {
                $('.' + campo + '_' + x).val(input);
                if (campo == 'input_horas_dia')
                    calcular_hombres_dia(x);
                else
                    calcular_horas_necesarias(x);
            }
        }
    }
</script>