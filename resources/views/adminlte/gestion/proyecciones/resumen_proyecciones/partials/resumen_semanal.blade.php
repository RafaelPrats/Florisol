<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-bordered table-striped" id="table_proyecciones" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-left th_yura_green" style="padding-left: 5px; border-right: 2px solid" rowspan="2">
                <div style="width: 150px;">
                    Semanas
                </div>
            </th>
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" colspan="2" style="border-right: 2px solid">
                    {{$sem->codigo}}
                </th>
            @endforeach
        </tr>
        <tr>
            @php
                $totales_cosechados = [];
                $totales_proyectados = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    Cosechados
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    Proyectados
                </th>
                @php
                    array_push($totales_cosechados, 0);
                    array_push($totales_proyectados, 0);
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
                @foreach($item['cosechados'] as $pos_c => $cos)
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($cos)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">
                        {{number_format($item['proyectados'][$pos_c], 2)}}
                    </td>
                    @php
                        $totales_cosechados[$pos_c] += $cos;
                        $totales_proyectados[$pos_c] += $item['proyectados'][$pos_c];
                    @endphp
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th class="th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                Totales
            </th>
            @foreach($totales_cosechados as $pos_t => $cos)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{number_format($cos)}}
                </th>
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px; border-right: 2px solid">
                    {{number_format($totales_proyectados[$pos_t], 2)}}
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    function select_desglose_planta(id_pta, pos) {
        datos = {
            id_pta: id_pta,
            hasta: $("#filtro_predeterminado_hasta").val(),
            desde: $("#filtro_predeterminado_desde").val(),
        };
        $.LoadingOverlay('show');
        $.get('{{url('resumen_proyecciones/select_desglose_planta')}}', datos, function (retorno) {
            $('.caca').remove();
            for (i = 0; i < retorno.data.length; i++) {
                var table = document.getElementById("table_proyecciones");
                var row = table.insertRow(parseInt(pos) + 3);

                var celdas = '';
                for (c = 0; c < retorno.data[i].cosechados.length; c++) {
                    celdas += '<td class="text-center" style="border-color: #9d9d9d;">' +
                        new Intl.NumberFormat("en-US").format(retorno.data[i].cosechados[c]) +
                        '</td>' +
                        '<td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid">' +
                        new Intl.NumberFormat("en-US").format(retorno.data[i].proyectados[c]) +
                        '</td>';
                }

                row.innerHTML = '<th style="background-color: #e9ecef; border-color: #9d9d9d; padding-left: 5px">' +
                    retorno.data[i].variedad.nombre +
                    '</th>' +
                    celdas;
                row.setAttribute('class', 'caca');
            }
        }, 'json').always(function () {
            $.LoadingOverlay('hide')
        });
    }
</script>