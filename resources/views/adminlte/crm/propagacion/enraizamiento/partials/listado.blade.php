<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-bordered table-striped" id="table_disponibilidad" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-left th_yura_green" style="padding-left: 5px; border-right: 2px solid" rowspan="2">
                <div style="width: 180px;">
                    Semanas
                </div>
            </th>
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="border-right: 2px solid" colspan="2">
                    {{$sem->codigo}}
                </th>
            @endforeach
        </tr>
        <tr id="tr_fija_top_1">
            @php
                $totales_siembras = [];
                $totales_requerimientos = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    Siembras
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid white">
                    Requerimientos
                </th>
                @php
                    array_push($totales_siembras, 0);
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
                        {{number_format($val->cantidad_siembra, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid black">
                        {{number_format($item['requerimientos'][$pos_v], 2)}}
                    </td>
                    @php
                        $totales_siembras[$pos_v] += $val->cantidad_siembra;
                        $totales_requerimientos[$pos_v] += $item['requerimientos'][$pos_v];
                    @endphp
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                Totales
            </th>
            @foreach($totales_siembras as $pos_t => $val)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{number_format($val, 2)}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid white">
                    {{number_format($totales_requerimientos[$pos_t], 2)}}
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
            desde: $('#filtro_desde').val(),
            hasta: $('#filtro_hasta').val(),
        };
        $.LoadingOverlay('show');
        $.get('{{url('reporte_enraizamiento/select_desglose_planta')}}', datos, function (retorno) {
            $('.caca').remove();
            for (i = 0; i < retorno.data.length; i++) {
                var table = document.getElementById("table_disponibilidad");
                var row = table.insertRow(parseInt(pos) + 3);

                var celdas = '';
                for (c = 0; c < retorno.data[i].valores.length; c++) {
                    siembra = retorno.data[i].valores[c] != '' ? retorno.data[i].valores[c].cantidad_siembra : 0;
                    sem_fin = retorno.data[i].valores[c] != '' ? retorno.data[i].valores[c].semana_fin : 0;
                    sem_req = retorno.data[i].requerimientos[c].semana_req;
                    celdas += '<td class="text-center" style="border-color: #9d9d9d;">' +
                        '<span data-toggle="tooltip" data-placement="top" data-html="true" title="Disponible: ' + sem_fin + '">' +
                        new Intl.NumberFormat("en-US").format(siembra) +
                        '</span>' +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid black">' +
                        '<span data-toggle="tooltip" data-placement="top" data-html="true" title="Destino: ' + sem_req + '">' +
                        new Intl.NumberFormat("en-US").format(retorno.data[i].requerimientos[c].valor) +
                        '</span>' +
                        '</td>';
                }

                row.innerHTML = '<th style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.data[i].variedad.nombre +
                    '</th>' +
                    celdas;
                row.setAttribute('class', 'caca');
                $('[data-toggle="tooltip"]').tooltip()
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