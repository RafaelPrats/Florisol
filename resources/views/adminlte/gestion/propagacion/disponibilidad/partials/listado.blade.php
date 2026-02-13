<div style="overflow-x: scroll; width: 100%">
    <table class="table-bordered" style="width: 100%; border-radius: 18px 18px 0 0" id="tabla_disponibilidades">
        <tr>
            <th class="text-center th_yura_green columna_fija_1" style="border-radius: 18px 0 0 0">
                <div style="width: 150px">Semanas</div>
            </th>
            @foreach($listado as $item)
                <th class="text-center bg-yura_dark">
                    <div style="width: 180px">
                        {{$item->semana}}
                        <button type="button" class="btn btn-xs btn-yura_default pull-right"
                                title="Forzar actualización de esta semana"
                                onclick="update_semana('{{$item->semana}}', '{{$item->id_variedad}}')">
                            <i class="fa fa-fw fa-refresh"></i>
                        </button>
                    </div>
                </th>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1">Saldo Inicial</th>
            @foreach($listado as $item)
                <td class="text-center" style="border-color: #9d9d9d">
                    {{number_format($item->saldo_inicial)}}
                </td>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1">Plantas Sembradas</th>
            @foreach($listado as $item)
                @php
                    $destino_plantas_sembradas = '';
                    if($item->destino_plantas_sembradas != ''){
                        foreach(explode('|', $item->destino_plantas_sembradas) as $d){
                            if ($destino_plantas_sembradas != '')
                                $destino_plantas_sembradas .= '<br><em>Sem ' . explode('+', $d)[0] . ': ' . number_format(explode('+', $d)[1]) . '</em>';
                            else
                                $destino_plantas_sembradas = '<em>Sem: ' . explode('+', $d)[0] . ': ' . number_format(explode('+', $d)[1]) . '</em>';
                        }
                    }
                @endphp
                <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                    <span data-toggle="tooltip" data-placement="top" data-html="true" title="{{$destino_plantas_sembradas}}">
                        {{number_format($item->plantas_sembradas)}}
                    </span>
                    <input type="hidden" id="plantas_sembradas_{{$item->id_propag_disponibilidad}}" value="{{$item->plantas_sembradas}}">
                </td>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1">Desecho %</th>
            @foreach($listado as $item)
                <td class="text-center td_yura_default" style="border-color: #9d9d9d; background-color: #e9ecef">
                    <table class="table-bordered" style="width: 100%">
                        <tr>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <input type="number" style="width: 100%" value="{{$item->desecho()}}" class="text-center"
                                       onchange="calcular_desecho('{{$item->id_propag_disponibilidad}}')"
                                       onkeyup="calcular_desecho('{{$item->id_propag_disponibilidad}}')" min="0"
                                       id="calculo_desecho_{{$item->id_propag_disponibilidad}}" title="Cantidad de plantas">
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d">
                                <input type="number" readonly style="width: 100%" value="{{$item->desecho}}" class="text-center bg-yura_dark"
                                       id="desecho_{{$item->id_propag_disponibilidad}}" title="Desecho %">
                            </td>
                        </tr>
                    </table>
                </td>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1">Plantas Disponibles</th>
            @foreach($listado as $item)
                <td class="text-center" style="border-color: #9d9d9d">
                    {{number_format($item->plantas_disponibles)}}
                </td>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1">Requerimientos</th>
            @foreach($listado as $item)
                <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                    <table class="table-bordered" style="width: 100%">
                        <tr>
                            {{--<td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <button type="button" class="btn btn-xs btn-yura_dark"
                                        onclick="add_requerimiento('{{$item->id_propag_disponibilidad}}')">
                                    <i class="fa fa-fw fa-plus"></i>
                                </button>
                            </td>--}}
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                                <input type="number" id="requerimientos_{{$item->id_propag_disponibilidad}}" style="width: 100%"
                                       class="text-center" value="{{$item->calcular_requerimientos()}}">
                            </td>
                            <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; width: 25%">
                                <div class="btn-group dropup">
                                    <button type="button" class="btn btn-xs btn-yura_primary dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-fw fa-save"></i> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li title="Al guardar usando esta opción, prevalecerán estos cambios por encima del resto de operaciones en el sistema">
                                            <a href="javascript:void(0)"
                                               onclick="update_disponibilidad('{{$item->id_propag_disponibilidad}}', 1)">
                                                Mantener cambios
                                            </a>
                                        </li>
                                        <li title="Al guardar usando esta opción, los cambios del resto de operaciones en el sistema prevalecerán por encima de este formulario">
                                            <a href="javascript:void(0)"
                                               onclick="update_disponibilidad('{{$item->id_propag_disponibilidad}}', 0)">
                                                NO mantener cambios
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            @endforeach
        </tr>
        <tr>
            <th class="text-center th_yura_green columna_fija_1" style="border-radius: 0 0 0 18px">Saldo</th>
            @foreach($listado as $item)
                <th class="text-center {{$item->saldo >= 0 ? 'bg-yura_dark' : 'bg-red-active'}}" style="">
                    {{number_format($item->saldo)}}
                </th>
            @endforeach
        </tr>
    </table>
</div>

{{--<select class="hidden" id="modulos_adicionales">
    <option value="">...</option>
    @foreach($modulos as $mod)
        <option value="{{$mod->id_modulo.'+'.$mod->nombre.'+'.$mod->poda_siembra}}">{{$mod->nombre}}</option>
    @endforeach
</select>--}}

<style>
    #tabla_disponibilidades tr .columna_fija_1 {
        z-index: 8;
        position: sticky;
        left: 0;
    }
</style>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function add_requerimiento(id) {
        $('#table_requerimientos_' + id).append('<tr>' +
            '<td class="text-center" style="border-color: #9d9d9d; width: 80px">' +
            '<select style="width: 100%" class="modulo_adicional_' + id + '">' +
            $('#modulos_adicionales').html() +
            '</select>' +
            '</td>' +
            '<td class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" class="cantidad_plantas_adicionales_' + id + '" style="width: 100%" min="0">' +
            '</td>' +
            '</tr>');
    }

    function calcular_desecho(id) {
        total = $('#plantas_sembradas_' + id).val();
        parte = $('#calculo_desecho_' + id).val();
        porcentaje = Math.round(((parte * 100) / total) * 100) / 100;
        $('#desecho_' + id).val(porcentaje);
    }

    function update_disponibilidad(id, mantener_cambios) {
        /*/!* requerimientos *!/
        req_mod_adic = $('.modulo_requerimiento_' + id);
        req_ptas_adic = $('.cantidad_plantas_requerimiento_' + id);
        requerimientos = '';
        for (i = 0; i < req_ptas_adic.length; i++) {
            mod = req_mod_adic[i].value;
            ptas = req_ptas_adic[i].value;
            if (mod != '' && ptas >= 0) {
                if (requerimientos != '')
                    requerimientos += '|' + mod.split('+')[0] + '+' + mod.split('+')[1] + '+' + ptas + '+' + mod.split('+')[2];
                else
                    requerimientos = mod.split('+')[0] + '+' + mod.split('+')[1] + '+' + ptas + '+' + mod.split('+')[2];
            }
        }

        /!* requerimientos adicionales *!/
        req_mod_adic = $('.modulo_adicional_' + id);
        req_ptas_adic = $('.cantidad_plantas_adicionales_' + id);
        req_adicionales = '';
        for (i = 0; i < req_ptas_adic.length; i++) {
            mod = req_mod_adic[i].value;
            ptas = req_ptas_adic[i].value;
            if (mod != '' && ptas > 0) {
                if (req_adicionales != '')
                    req_adicionales += '|' + mod.split('+')[0] + '+' + mod.split('+')[1] + '+' + ptas + '+' + mod.split('+')[2];
                else
                    req_adicionales = mod.split('+')[0] + '+' + mod.split('+')[1] + '+' + ptas + '+' + mod.split('+')[2];
            }
        }*/

        datos = {
            _token: '{{csrf_token()}}',
            id: id,
            requerimientos: $('#requerimientos_' + id).val(),
            req_adicionales: '',
            desecho: $('#desecho_' + id).val(),
            mantener_cambios: mantener_cambios,
            semana_desde: $('#filtro_predeterminado_desde').val(),
            semana_hasta: $('#filtro_predeterminado_hasta').val(),
        };

        post_jquery('{{url('propag_disponibilidad/update_disponibilidad')}}', datos, function () {
            listar_disponibilidades();
        });
    }

    function update_semana(semana, variedad) {
        datos = {
            _token: '{{csrf_token()}}',
            semana: semana,
            variedad: variedad,
        };
        post_jquery('{{url('propag_disponibilidad/update_semana')}}', datos, function () {
            listar_disponibilidades();
        });
    }
</script>