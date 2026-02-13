<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-bordered table-striped" id="table_resumen" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-left th_yura_green" style="padding-left: 5px; border-right: 2px solid" rowspan="2">
                <div style="width: 150px;">
                    Semanas
                </div>
            </th>
            @php
                $totales1 = [];
                $totales2 = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="border-right: 2px solid" colspan="2">
                    {{$sem->codigo}}
                </th>
                @php
                    array_push($totales1, 0);
                    array_push($totales2, 0);
                @endphp
            @endforeach
        </tr>
        <tr id="tr_fija_top_1">
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="border-left: 2px solid; padding-left: 5px; padding-right: 5px">
                    {{$labels[0]}}
                </th>
                <th class="text-center bg-yura_dark" style="border-right: 2px solid; padding-left: 5px; padding-right: 5px">
                    {{$labels[1]}}
                </th>
            @endforeach
        </tr>
        <tbody>
        @foreach($listado as $pos_l => $item)
            <tr style="background-color: #00b3886b; color: black" class="mouse-hand" id="row_pta_{{$item['planta']->id_planta}}"
                onclick="select_desglose_planta('{{$item['planta']->id_planta}}', '{{$pos_l}}')">
                <th class="text-left"
                    style="padding-left: 5px; border-color: #9d9d9d; border-right: 2px solid; border-top: 2px solid;"
                    rowspan="2">
                    {{$item['planta']->nombre}}
                    <i class="fa fa-fw fa-caret-down pull-right" id="icon_caret_pta_{{$item['planta']->id_planta}}"></i>
                </th>
                @foreach($item['valores1'] as $pos_c => $val)
                    <th class="text-center"
                        style="border-color: #9d9d9d; border-left: 2px solid; border-right: 2px solid; border-top: 2px solid;"
                        colspan="2">
                        {{number_format($val + $item['valores2'][$pos_c], $number_format)}}
                    </th>
                @endforeach
            </tr>
            <tr style="background-color: #00b3886b; color: black" class="mouse-hand"
                onclick="select_desglose_planta('{{$item['planta']->id_planta}}', '{{$pos_l}}')">
                @foreach($item['valores1'] as $pos_c => $val)
                    <td class="text-center" style="border-color: #9d9d9d; border-left: 2px solid">
                        {{number_format($val, $number_format)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">
                        {{number_format($item['valores2'][$pos_c], $number_format)}}
                    </td>
                    @php
                        $totales1[$pos_c] += $val;
                        $totales2[$pos_c] += $item['valores2'][$pos_c];
                    @endphp
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                Totales
            </th>
            @foreach($totales1 as $pos_t => $val)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-left: 2px solid">
                    {{number_format($val, $number_format)}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    {{number_format($totales2[$pos_t], $number_format)}}
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    function select_desglose_planta(id_pta, pos) {
        datos = {
            id_pta: id_pta,
            reporte: $('#filtro_predeterminado_reporte').val(),
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        $.LoadingOverlay('show');
        $.get('{{url('resumen_ebitda/select_desglose_planta')}}', datos, function (retorno) {
            $('.tr_desglose').remove();
            for (i = 0; i < retorno.listado.length; i++) {
                var table = document.getElementById("table_resumen");
                var row = table.insertRow(parseInt(pos) + 4 + parseInt(pos));

                var celdas = '';
                for (c = 0; c < retorno.listado[i].valores1.length; c++) {
                    celdas += '<td class="text-center" style="border-color: #9d9d9d; border-left: 2px solid">' +
                        new Intl.NumberFormat("en-US").format(retorno.listado[i].valores1[c]) +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">' +
                        new Intl.NumberFormat("en-US").format(retorno.listado[i].valores2[c]) +
                        '</td>';
                }

                row.innerHTML = '<th style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.listado[i].variedad.nombre +
                    '</th>' +
                    celdas;
                row.setAttribute('class', 'tr_desglose');
            }
        }, 'json').always(function () {
            $.LoadingOverlay('hide')
        });
    }
</script>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 8;
    }

    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 8;
    }
</style>