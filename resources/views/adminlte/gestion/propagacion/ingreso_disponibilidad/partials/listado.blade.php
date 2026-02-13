<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-bordered table-striped" id="table_disponibilidad" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-left th_yura_green" style="padding-left: 5px; border-right: 2px solid" rowspan="2">
                <div style="width: 150px;">
                    Semanas
                </div>
            </th>
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" colspan="2" style="border-right: 2px solid">
                    {{$sem->codigo}}
                </th>
                <input type="hidden" class="codigos_semana" value="{{$sem->codigo}}">
            @endforeach
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 150px;">
                </div>
            </th>
        </tr>
        <tr id="tr_fija_top_1">
            @php
                $totales_disponibles = [];
                $totales_requerimientos = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    Disponibles
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    Requerimientos
                </th>
                @php
                    array_push($totales_disponibles, 0);
                    array_push($totales_requerimientos, 0);
                @endphp
            @endforeach
        </tr>
        <tbody>
        @foreach($data as $pos_d => $item)
            <tr style="background-color: #00b3886b; color: black" class="mouse-hand" id="row_pta_{{$item['planta']->id_planta}}"
                onclick="select_desglose_planta('{{$item['planta']->id_planta}}', '{{$pos_d}}')">
                <th class="text-left" style="padding-left: 5px; border-color: #9d9d9d; border-right: 2px solid">
                    {{$item['planta']->nombre}}
                    <i class="fa fa-fw fa-caret-down pull-right" id="icon_caret_pta_{{$item['planta']->id_planta}}"></i>
                </th>
                @foreach($item['valores'] as $pos_v => $val)
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($val->plantas_disponibles, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">
                        {{$val->requerimientos > 0 ? number_format($val->requerimientos) : 0}}
                    </td>@php
                        $totales_disponibles[$pos_v] += $val->plantas_disponibles;
                        $totales_requerimientos[$pos_v] += $val->requerimientos > 0 ? $val->requerimientos : 0;
                    @endphp
                @endforeach
                <th class="text-right" style="padding-right: 5px; border-color: #9d9d9d;">
                    <i class="fa fa-fw fa-caret-down pull-left" id="icon_caret_pta_{{$item['planta']->id_planta}}"></i>
                    {{$item['planta']->nombre}}
                </th>
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                Totales
            </th>
            @foreach($totales_disponibles as $pos_t => $val)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{number_format($val, 2)}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    {{number_format($totales_requerimientos[$pos_t])}}
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function select_desglose_planta(id_pta, pos) {
        datos = {
            id_pta: id_pta,
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        $.LoadingOverlay('show');
        $.get('{{url('ingreso_disponibilidad/select_desglose_planta')}}', datos, function (retorno) {
            $('.caca').remove();
            for (i = 0; i < retorno.data.length; i++) {
                var table = document.getElementById("table_disponibilidad");
                var row = table.insertRow(parseInt(pos) + 3);

                var celdas = '';
                for (c = 0; c < retorno.data[i].valores.length; c++) {
                    destino_plantas_sembradas = '';
                    retorno_destinos = retorno.data[i].valores[c].destino_plantas_sembradas;
                    if (retorno_destinos != '')
                        for (z = 0; z < retorno_destinos.split('|').length; z++) {
                            if (destino_plantas_sembradas != '')
                                destino_plantas_sembradas += '<br><em>Sem ' + retorno_destinos.split('|')[z].split('+')[0] + ': ' + retorno_destinos.split('|')[z].split('+')[1] + '</em>';
                            else
                                destino_plantas_sembradas = '<em>Sem ' + retorno_destinos.split('|')[z].split('+')[0] + ': ' + retorno_destinos.split('|')[z].split('+')[1] + '</em>';
                        }

                    celdas += '<td class="text-center" style="border-color: #9d9d9d;">' +
                        '<span data-toggle="tooltip" data-placement="top" data-html="true" title="' + destino_plantas_sembradas + '">' +
                        new Intl.NumberFormat("en-US").format(retorno.data[i].valores[c].plantas_disponibles) +
                        '</span>' +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">' +
                        '<input type="number" style="width: 100%" value="' + retorno.data[i].valores[c].requerimientos + '" ' +
                        'class="text-center" title="Doble click para actualizar" min="0" ' +
                        'ondblclick="update_requerimiento(' + retorno.data[i].variedad.id_variedad + ', ' + retorno.semanas[c].codigo + ')" ' +
                        'id="input_requerimiento_' + retorno.data[i].variedad.id_variedad + '_' + retorno.semanas[c].codigo + '">' +
                        '</td>';
                }

                row.innerHTML = '<th style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.data[i].variedad.nombre +
                    '</th>' +
                    celdas +
                    '<th style="background-color: #e9ecef; border-color: #9d9d9d;" class="text-center">' +
                    '<button type="button" class="btn btn-xs btn-yura_primary" title="Actualizar requerimientos de la variedad" ' +
                    'onclick="update_requerimientos_by_variedad(' + retorno.data[i].variedad.id_variedad + ')">' +
                    '<i class="fa fa-fw fa-save"></i>' +
                    '</button>' +
                    '</th>';
                row.setAttribute('class', 'caca');
                $('[data-toggle="tooltip"]').tooltip()
            }
        }, 'json').always(function () {
            $.LoadingOverlay('hide')
        });
    }

    function update_requerimiento(variedad, semana) {
        datos = {
            _token: '{{csrf_token()}}',
            variedad: variedad,
            semana: semana,
            valor: $('#input_requerimiento_' + variedad + '_' + semana).val(),
        };
        $('#input_requerimiento_' + variedad + '_' + semana).LoadingOverlay('show');
        $.post('{{url('ingreso_disponibilidad/update_requerimiento')}}', datos, function (retorno) {
            if (!retorno.success)
                alerta(retorno.mensaje);
        }, 'json').fail(function (retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function () {
            $('#input_requerimiento_' + variedad + '_' + semana).LoadingOverlay('hide');
        });
    }

    function update_requerimientos_by_variedad(variedad) {
        semanas = $('.codigos_semana');
        for (i = 0; i < semanas.length; i++) {
            sem = semanas[i].value;
            update_requerimiento(variedad, sem);
        }
    }
</script>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 20px;
        z-index: 9;
    }

    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>