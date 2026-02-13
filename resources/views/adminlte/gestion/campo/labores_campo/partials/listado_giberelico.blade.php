<div style="overflow-y: scroll; overflow-x: scroll; height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_labores">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    <input type="checkbox" id="check_all_ciclos" class="mouse-hand"
                        onchange="$('.check_ciclo').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        Var.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    Mód.
                </th>
                <th class="text-center th_yura_green">
                    P/S
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Ini.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    Días
                </th>
                <th class="text-center th_yura_green">
                    Fecha
                </th>
                <th class="text-center th_yura_green">
                    Rep.
                </th>
                <th class="text-center th_yura_green">
                    Camas
                </th>
                <th class="text-center th_yura_green">
                    Densidad/m<sup>2</sup>
                </th>
                <th class="text-center th_yura_green">
                    CC x Planta
                </th>
                <th class="text-center th_yura_green">
                    Litros x cama
                </th>
                @php
                    $totales_prod = [];
                    $totales_mo = [];
                @endphp
                @foreach ($productos as $p)
                    <th class="text-center bg-yura_dark th_detalles hidden">
                        <div style="width: 120px">
                            {{ $p->nombre }}
                        </div>
                    </th>
                    @php
                        $totales_prod[] = 0;
                    @endphp
                @endforeach
                @foreach ($mano_obras as $mo)
                    <th class="text-center bg-yura_dark th_detalles hidden">
                        <div style="width: 120px">
                            {{ $mo->nombre }}
                        </div>
                    </th>
                    @php
                        $totales_mo[] = 0;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default dropdown-toggle"
                            data-toggle="dropdown">
                            <i class="fa fa-fw fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="add_adicional()">
                                    <i class="fa fa-fw fa-plus"></i> Adicional
                                </a>
                            </li>
                            @if ($semana_req->codigo == $semana_actual->codigo || true)
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="aplicar_mezclas()">
                                        <i class="fa fa-fw fa-plus"></i> Aplicar Mezclas
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)"
                                    onclick="$('.th_detalles').toggleClass('hidden')">
                                    <i class="fa fa-fw fa-eye-slash"></i> Ver-Ocultar mezclas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="exportar_reporte()">
                                    <i class="fa fa-fw fa-file-excel-o"></i> Exportar a Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $pos = 0;
                $min_fecha = opDiasFecha('-', 7, $semana_req->fecha_inicial);
                $max_fecha = opDiasFecha('+', 7, $semana_req->fecha_final);
                $total_camas = 0;
                $total_litros = 0;
                $total_cc_x_planta = 0;
            @endphp
            @foreach ($listado as $item)
                @php
                    $modulo = $item['ciclo']->modulo;
                    
                    $aplicacion = $item['aplicacion'];
                    $dia_ini = $aplicacion->semana_ini * 7;
                    $dia_fin = ($aplicacion->semana_ini + $aplicacion->repeticiones) * 7 - 1;
                    $rangos = [];
                    for ($i = 0; $i < $aplicacion->repeticiones; $i++) {
                        $rangos[] = [
                            'desde' => $dia_ini + 7 * $i,
                            'hasta' => $dia_ini + 7 * ($i + 1) - 1,
                        ];
                    }
                    $count = count($item['labores']) > 0 ? count($item['labores']) : 1;
                @endphp
                @for ($i = 0; $i < $count; $i++)
                    @php
                        $show = true;
                        $labor = count($item['labores']) > 0 ? $item['labores'][$i] : '';
                        $repeticion = '';
                        $fecha = '';
                        if ($labor != '') {
                            $dias_ciclo = difFechas($labor->fecha, $item['ciclo']->fecha_inicio)->days;
                            $fecha = $labor->fecha;
                            $repeticion = $labor->repeticion;
                            $camas = $labor->camas;
                            $cc_x_planta = $labor->cc_x_planta;
                            $litro_x_cama = round($labor->litro_x_cama);
                        } else {
                            if ($semana_req->codigo == $semana_actual->codigo) {
                                $dias_ciclo = difFechas(hoy(), $item['ciclo']->fecha_inicio)->days;
                            } else {
                                $dias_ciclo = difFechas($semana_req->fecha_inicial, $item['ciclo']->fecha_inicio)->days;
                            }
                            $dias_ciclo_ini_sem = difFechas($semana_req->fecha_inicial, $item['ciclo']->fecha_inicio)->days;
                            $dias_ciclo_fin_sem = difFechas($semana_req->fecha_final, $item['ciclo']->fecha_inicio)->days;
                        
                            $camas = $item['ciclo']->getCamas();
                            $cc_x_planta = $aplicacion->litro_x_cama;
                            $litro_x_cama = round($item['fenograma']->densidad_plantas_ini_m2 * 45 * ($cc_x_planta / 1000), 2);
                            $lastLabor = $item['ciclo']->getLastLaborByFecha($aplicacion->id_aplicacion, $semana_req->fecha_final);
                            for ($y = 0; $y < count($rangos); $y++) {
                                if (($dias_ciclo_ini_sem >= $rangos[$y]['desde'] && $dias_ciclo_ini_sem <= $rangos[$y]['hasta']) || ($dias_ciclo_fin_sem >= $rangos[$y]['desde'] && $dias_ciclo_fin_sem <= $rangos[$y]['hasta'])) {
                                    if (isset($lastLabor)) {
                                        $fecha = opDiasFecha('+', 7, $lastLabor->fecha);
                                        $repeticion = $lastLabor->repeticion + 1;
                                        if ($lastLabor->repeticion >= $aplicacion->repeticiones) {
                                            $show = false;
                                        }
                                    } else {
                                        $fecha = opDiasFecha('+', $rangos[$y]['desde'], $item['ciclo']->fecha_inicio);
                                        $repeticion = $y + 1;
                                    }
                                    break;
                                }
                            }
                        }
                        $total_camas += $camas;
                        $total_cc_x_planta += $cc_x_planta;
                        $total_litros += $litro_x_cama;
                    @endphp
                    @if ($show)
                        <tr id="tr_ciclo_{{ $pos }}">
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="checkbox" id="check_ciclo_{{ $pos }}"
                                    class="check_ciclo mouse-hand">
                                <input type="hidden" id="id_ciclo_{{ $pos }}"
                                    value="{{ $item['ciclo']->id_ciclo }}">
                                <input type="hidden" id="id_variedad_{{ $pos }}"
                                    value="{{ $item['ciclo']->id_variedad }}">
                                <input type="hidden" id="id_aplicacion_{{ $pos }}"
                                    value="{{ $aplicacion->id_aplicacion }}">
                                <input type="hidden" value="{{ $pos }}" class="pos">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                {{ $item['ciclo']->variedad->nombre }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                {{ $modulo->nombre }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                {{ $item['ciclo']->poda_siembra }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                {{ convertDateToText($item['ciclo']->fecha_inicio) }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                {{ $dias_ciclo }}
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="date" id="fecha_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="{{ $min_fecha }}" value="{{ $fecha }}"
                                    max="{{ $max_fecha }}">
                                <span class="hidden" id="span_fecha_{{ $pos }}">{{ $fecha }}</span>
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="repeticion_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="{{ $repeticion }}" value="{{ $repeticion }}"
                                    onchange="change_repeticion('{{ $pos }}')"
                                    onkeyup="change_repeticion('{{ $pos }}')">
                                <span class="hidden"
                                    id="span_repeticion_{{ $pos }}">{{ $repeticion }}</span>
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="camas_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="1" value="{{ $camas }}">
                                <span class="hidden" id="span_camas_{{ $pos }}">{{ $camas }}</span>
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="densidad_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="1" readonly
                                    value="{{ $item['fenograma']->densidad_plantas_ini_m2 }}">
                                <span class="hidden" id="span_densidad_{{ $pos }}">
                                    {{ $item['fenograma']->densidad_plantas_ini_m2 }}
                                </span>
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="cc_x_planta_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="1" value="{{ $cc_x_planta }}"
                                    onchange="calcular_litros('densidad_{{ $pos }}', 'cc_x_planta_{{ $pos }}', 'litros_x_cama_{{ $pos }}')">
                                <span class="hidden"
                                    id="span_cc_x_planta_{{ $pos }}">{{ $cc_x_planta }}</span>
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="litros_x_cama_{{ $pos }}" style="width: 100%;"
                                    class="text-center" min="1" value="{{ $litro_x_cama }}">
                                <span class="hidden"
                                    id="span_litros_x_cama_{{ $pos }}">{{ $litro_x_cama }}</span>
                            </td>
                            @foreach ($productos as $pos_p => $p)
                                @php
                                    $detalle = $labor != '' ? $labor->getDetalleByProducto($p->id_producto) : '';
                                    $totales_prod[$pos_p] += $detalle != '' ? $detalle->dosis : 0;
                                @endphp
                                <td class="text-center th_detalles hidden" style="border-color: #9d9d9d">
                                    {{ $detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' ' . $detalle->unidad_medida->siglas : '' }}
                                </td>
                            @endforeach
                            @foreach ($mano_obras as $pos_p => $mo)
                                @php
                                    $detalle = $labor != '' ? $labor->getDetalleByManoObra($mo->id_mano_obra) : '';
                                    $totales_mo[$pos_p] += $detalle != '' ? $detalle->dosis : 0;
                                @endphp
                                <td class="text-center th_detalles hidden" style="border-color: #9d9d9d">
                                    {{ $detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' ' . $detalle->unidad_medida->siglas : '' }}
                                </td>
                            @endforeach
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <div class="btn-group">
                                    @if ($labor == '')
                                        <button type="button" class="btn btn-xs btn-yura_primary"
                                            onclick="store_labor('{{ $pos }}', '{{ $aplicacion->id_aplicacion }}')">
                                            <i class="fa fa-fw fa-plus"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-xs btn-yura_primary dropdown-toggle"
                                            data-toggle="dropdown">
                                            <i class="fa fa-fw fa-gears"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="ver_labores_by_ciclo('{{ $item['ciclo']->id_ciclo }}', '{{ $aplicacion->id_aplicacion }}')">
                                                    <i class="fa fa-fw fa-eye"></i> Ver labores del ciclo
                                                </a>
                                            </li>
                                            <li class="list-seperator"></li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="update_labor('{{ $pos }}', '{{ $labor->id_aplicacion_campo }}', '{{ $aplicacion->id_aplicacion }}')">
                                                    <i class="fa fa-fw fa-pencil"></i> Editar
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)"
                                                    onclick="delete_labor('{{ $labor->id_aplicacion_campo }}')">
                                                    <i class="fa fa-fw fa-trash"></i> Eliminar
                                                </a>
                                            </li>
                                        </ul>
                                        <input type="hidden" id="id_aplicacion_campo_{{ $pos }}"
                                            value="{{ $labor->id_aplicacion_campo }}">
                                    @endif
                                </div>
                            </td>
                        </tr>
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
                <th class="text-center th_yura_green">
                    {{ number_format($total_camas, 2) }}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_cc_x_planta, 2) }}
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_litros, 2) }}
                </th>
                @foreach ($totales_prod as $p)
                    <td class="text-center th_detalles hidden" style="border-color: #9d9d9d">
                        {{ number_format($p, 2) }}
                    </td>
                @endforeach
                @foreach ($totales_mo as $mo)
                    <td class="text-center th_detalles hidden" style="border-color: #9d9d9d">
                        {{ number_format($mo, 2) }}
                    </td>
                @endforeach
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
    function store_labor(pos, aplicacion) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: $('#id_ciclo_' + pos).val(),
            aplicacion: aplicacion,
            fecha: $('#fecha_' + pos).val(),
            repeticion: $('#repeticion_' + pos).val(),
            camas: $('#camas_' + pos).val(),
            cc_x_planta: $('#cc_x_planta_' + pos).val(),
            litros_x_cama: $('#litros_x_cama_' + pos).val(),
        };
        post_jquery_m('{{ url('ingreso_labores/store_labor') }}', datos, function() {
            listar_labores();
        }, 'tr_ciclo_' + pos);
    }

    function update_labor(pos, app_campo, aplicacion) {
        datos = {
            _token: '{{ csrf_token() }}',
            ciclo: $('#id_ciclo_' + pos).val(),
            aplicacion: aplicacion,
            app_campo: app_campo,
            fecha: $('#fecha_' + pos).val(),
            repeticion: $('#repeticion_' + pos).val(),
            camas: $('#camas_' + pos).val(),
            litros_x_cama: $('#litros_x_cama_' + pos).val(),
            cc_x_planta: $('#cc_x_planta_' + pos).val(),
            check_luz: $('#check_luz_' + pos).prop('checked'),
        };
        post_jquery_m('{{ url('ingreso_labores/update_labor') }}', datos, function() {}, 'tr_ciclo_' + pos);
    }

    function delete_labor(app_campo) {
        modal_quest('modal-quest_delete_labor',
            '<div class="text-center alert alert-warning">¿Desea <strong>ELIMINAR</strong> la labor?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Mensaje de confirmación', true, false, '50%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    app_campo: app_campo,
                };
                post_jquery_m('{{ url('ingreso_labores/delete_labor') }}', datos, function() {
                    cerrar_modals();
                    listar_labores();
                });
            });
    }

    function add_adicional() {
        datos = {
            semana: $('#filtro_semana').val(),
            labor: $('#filtro_labor').val(),
            sector: $('#filtro_sector').val(),
        };
        get_jquery('{{ url('ingreso_labores/add_adicional') }}', datos, function(retorno) {
            modal_view('modal-view_add_adicional', retorno, '<i class="fa fa-fw fa-eye"></i> Adicional', true,
                false, '90%');
        });
    }

    function aplicar_mezclas() {
        array_pos = $('.pos');
        data = [];
        for (i = 0; i < array_pos.length; i++) {
            pos = array_pos[i].value;
            if ($('#check_ciclo_' + pos).prop('checked') == true) {
                data.push({
                    ciclo: $('#id_ciclo_' + pos).val(),
                    variedad: $('#id_variedad_' + pos).val(),
                    app_campo: $('#id_aplicacion_campo_' + pos).val(),
                    aplicacion: $('#id_aplicacion_' + pos).val(),
                    fecha: $('#fecha_' + pos).val(),
                    repeticion: $('#repeticion_' + pos).val(),
                    camas: $('#camas_' + pos).val(),
                    litros_x_cama: $('#litros_x_cama_' + pos).val(),
                });
            }
        }
        datos = {
            data: data,
            labor: $('#filtro_labor').val(),
        };
        get_jquery('{{ url('ingreso_labores/aplicar_mezclas') }}', datos, function(retorno) {
            modal_view('modal_view-aplicar_mezclas', retorno, '<i class="fa fa-fw fa-eye"></i> Aplicar mezclas',
                true, false, '90%');
        });
    }

    function change_repeticion(pos) {
        if ($('#repeticion_' + pos).val() == 1) {
            $('#check_luz_' + pos).removeClass('hidden');
        } else {
            $('#check_luz_' + pos).addClass('hidden');
        }
    }

    function exportar_reporte() {
        $.LoadingOverlay('show');
        window.open('{{ url('ingreso_labores/exportar_reporte') }}?labor=' + $('#filtro_labor').val() +
            '&semana=' + $('#filtro_semana').val() +
            '&sector=' + $('#filtro_sector').val(), '_blank');
        $.LoadingOverlay('hide');
    }

    function ver_labores_by_ciclo(ciclo, aplicacion) {
        datos = {
            ciclo: ciclo,
            aplicacion: aplicacion,
        };
        get_jquery('{{ url('ingreso_labores/ver_labores_by_ciclo') }}', datos, function(retorno) {
            modal_view('modal-view_ver_labores_by_ciclo', retorno,
                '<i class="fa fa-fw fa-eye"></i> Labores del ciclo', true, false, '80%');
        });
    }
</script>
