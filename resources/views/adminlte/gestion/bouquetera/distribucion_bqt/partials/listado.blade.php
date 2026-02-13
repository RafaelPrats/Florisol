<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
    <table id="table_listado_distribucion_bqt" class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
        <thead>
        <tr>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">Finca</div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">Planta</div>
            </th>
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" colspan="3" style="border-left: 2px solid">
                    {{$sem->codigo}}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach($semanas as $sem)
                <th class="text-center bg-yura_dark" style="border-left: 2px solid">
                    Bqt
                </th>
                <th class="text-center bg-yura_dark">
                    Expt.
                </th>
                <th class="text-center bg-yura_dark">
                    Venta
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($listado as $item)
            <tr>
                <td class="text-center bg-yura_dark">
                    {{$item['empresa']}}
                </td>
                <td class="text-center bg-yura_dark">
                    {{$item['pta_nombre']}}
                </td>
                @foreach($item['valores'] as $val)
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; border-left: 2px solid">
                        {{$val['tallos'] != '' ? number_format($val['tallos']) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$val['exportada'] != '' ? number_format($val['exportada']) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{--{{$val['indicadores_4_semanas']->precio_x_tallo_bqt}}--}}
                        {{number_format(($val['tallos'] + $val['exportada']) * $val['indicadores_4_semanas']->precio_x_tallo_bqt, 2)}}
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>