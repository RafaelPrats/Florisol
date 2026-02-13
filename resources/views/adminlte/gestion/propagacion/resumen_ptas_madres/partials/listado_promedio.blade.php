<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table-bordered table-striped" id="table_resumen" style="width: 100%; border: 1px solid #9d9d9d">
        <tr id="tr_fija_top_0">
            <th class="text-left th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                <div style="width: 150px;">
                    Semanas
                </div>
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="border-right: 2px solid">
                    {{$sem->codigo}}
                </th>
                @php
                    array_push($totales, [
                        'cantidad' => 0,
                        'positivos' => 0,
                    ]);
                @endphp
            @endforeach
        </tr>
        <tbody>
        @foreach($listado as $pos_l => $item)
            <tr style="background-color: #00b3886b; color: black" class="mouse-hand" id="row_pta_{{$item['planta']->id_planta}}"
                onclick="select_desglose_planta('{{$item['planta']->id_planta}}', '{{$pos_l}}')">
                <th class="text-left" style="padding-left: 5px; border-color: #9d9d9d; border-right: 2px solid">
                    {{$item['planta']->nombre}}
                    <i class="fa fa-fw fa-caret-down pull-right" id="icon_caret_pta_{{$item['planta']->id_planta}}"></i>
                </th>
                @foreach($item['valores'] as $pos_c => $val)
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($val, 2)}}
                    </td>
                    @php
                        $totales[$pos_c]['cantidad'] += $val;
                        if ($val > 0)
                            $totales[$pos_c]['positivos']++;
                    @endphp
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="th_yura_green" style="padding-left: 5px; border-right: 2px solid">
                Promedio
            </th>
            @foreach($totales as $pos_t => $val)
                <th class="text-center bg-yura_dark" style="padding-left: 5px; padding-right: 5px">
                    {{$val['positivos'] > 0 ? number_format($val['cantidad'] / $val['positivos'], 2) : 0}}
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
        $.get('{{url('resumen_plantas_madres/select_desglose_planta')}}', datos, function (retorno) {
            $('.tr_desglose').remove();
            for (i = 0; i < retorno.listado.length; i++) {
                var table = document.getElementById("table_resumen");
                var row = table.insertRow(parseInt(pos) + 2);

                var celdas = '';
                for (c = 0; c < retorno.listado[i].valores.length; c++) {
                    celdas += '<td class="text-center" style="border-color: #9d9d9d;">' +
                        new Intl.NumberFormat("en-US").format(retorno.listado[i].valores[c]) +
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
    #tr_fija_top_0 th{
        position: sticky;
        top: 0;
        z-index: 8;
    }
    #tr_fija_bottom_0 th{
        position: sticky;
        bottom: 0;
        z-index: 8;
    }
</style>