@if(count($data) > 0)
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 18px"
           id="table_listado_bqt_diaria">
        <thead>
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0" rowspan="2">
                <div style="width: 140px">
                    Finca
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 120px">
                    Variedad
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                <div style="width: 200px">
                    Tipo
                </div>
            </th>
            <th class="text-center th_yura_green" rowspan="2" style="padding: 0 5px 0 5px">
                Precio
            </th>
            @php
                $totales = [];
            @endphp
            @foreach($fechas as $f)
                <th class="text-center bg-yura_dark" id="th_fecha_{{$f->fecha}}"
                    style="border-color: #9d9d9d; border-left: 2px solid black; border-right: 2px solid black"
                    colspan="2">
                    {{$f->fecha}}
                    <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="mostrar_ocultar_costos('{{$f->fecha}}')"
                            id="btn_fecha_{{$f->fecha}}" title="Mostrar costos">
                        <i class="fa fa-fw fa-eye"></i> -<i class="fa fa-fw fa-usd"></i>
                    </button>
                </th>
                @php
                    array_push($totales, [
                        'tallos' => 0,
                        'exportada' => 0,
                        'costos_tallos' => 0,
                        'costos_exportada' => 0,
                    ]);
                @endphp
            @endforeach
            <th class="text-center th_yura_green" colspan="2" style="padding: 0 5px 0 5px; border-radius: 0 18px 0 0" id="th_fecha_total">
                Totales
                <button type="button" class="btn btn-xs btn-yura_default pull-right" onclick="mostrar_ocultar_costos('total')"
                        id="btn_fecha_total" title="Mostrar costos">
                    <i class="fa fa-fw fa-eye"></i> -<i class="fa fa-fw fa-usd"></i>
                </button>
            </th>
        </tr>
        <tr id="tr_fija_top_1">
            @foreach($fechas as $f)
                <th class="text-center th_yura_green" style="padding: 0 5px 0 5px; border-left: 2px solid black">
                    <div style="width: 60px">
                        Bqt
                    </div>
                </th>
                <th class="text-center th_yura_green" style="padding: 0 5px 0 5px; border-right: 2px solid black">
                    <div style="width: 60px">
                        Exp.
                    </div>
                </th>
                <th class="text-center td_costos_fecha_{{$f->fecha}} hidden" title="Costos"
                    style="background-color: #e9ecef; border-right: 1px solid #9d9d9d">
                    <div style="width: 60px">
                        Bqt
                    </div>
                </th>
                <th class="text-center td_costos_fecha_{{$f->fecha}} hidden"
                    style="background-color: #e9ecef; padding: 0 5px 0 5px; border-right: 3px solid black" title="Costos">
                    <div style="width: 60px">
                        Exp.
                    </div>
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="padding: 0 5px 0 5px; border-left: 2px solid black">
                <div style="width: 60px">
                    Bqt
                </div>
            </th>
            <th class="text-center th_yura_green" style="padding: 0 5px 0 5px; border-right: 2px solid black">
                <div style="width: 60px">
                    Exp.
                </div>
            </th>
            <th class="text-center td_costos_fecha_total hidden" title="Costos"
                style="background-color: #e9ecef; border-right: 1px solid #9d9d9d">
                <div style="width: 60px">
                    Bqt
                </div>
            </th>
            <th class="text-center td_costos_fecha_total hidden"
                style="background-color: #e9ecef; padding: 0 5px 0 5px; border-right: 3px solid black" title="Costos">
                <div style="width: 60px">
                    Exp.
                </div>
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($data as $d)
            <tr{{-- onmouseover="$(this).css('background-color', '#70ccf9')" onmouseleave="$(this).css('background-color', '')"--}}>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$d['comb']->planta_nombre}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$d['comb']->var_nombre}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{$d['comb']->precio}}
                </th>
                @php
                    $total_tallos_comb = 0;
                    $total_exportada_comb = 0;
                @endphp
                @foreach($d['valores'] as $pos_v => $v)
                    @php
                        $total_tallos_comb += $v['valor']->tallos;
                        $total_exportada_comb += $v['valor']->exportada;
                        $totales[$pos_v]['tallos'] += $v['valor']->tallos;
                        $totales[$pos_v]['exportada'] += $v['valor']->exportada;
                        $totales[$pos_v]['costos_tallos'] += $v['valor']->tallos > 0 ? round(($d['comb']->precio * $v['valor']->tallos), 2) : 0;
                        $totales[$pos_v]['costos_exportada'] += $v['valor']->exportada > 0 ? round(($d['comb']->precio * $v['valor']->exportada), 2) : 0;
                    @endphp
                    <td class="text-center" style="border-color: #9d9d9d; border-left: 2px solid black">
                        <div data-toggle="tooltip" data-placement="top"
                             title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                            {{$v['valor']->tallos > 0 ? number_format($v['valor']->tallos) : 0}}
                        </div>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; border-right: 2px solid black">
                        <div data-toggle="tooltip" data-placement="top"
                             title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                            {{$v['valor']->exportada > 0 ? number_format($v['valor']->exportada) : 0}}
                        </div>
                    </td>
                    <td class="text-center td_costos_fecha_{{$v['fecha']}} hidden" style="border-color: #9d9d9d;">
                        <div data-toggle="tooltip" data-placement="top"
                             title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                            {{$v['valor']->tallos > 0 ? number_format(($d['comb']->precio * $v['valor']->tallos), 2) : 0}}
                        </div>
                    </td>
                    <td class="text-center td_costos_fecha_{{$v['fecha']}} hidden" style="border-color: #9d9d9d; border-right: 3px solid black">
                        <div data-toggle="tooltip" data-placement="top"
                             title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                            {{$v['valor']->exportada > 0 ? number_format(($d['comb']->precio * $v['valor']->exportada), 2) : 0}}
                        </div>
                    </td>
                @endforeach
                <th class="text-center" style="border-color: #9d9d9d; border-left: 2px solid black">
                    <div data-toggle="tooltip" data-placement="top"
                         title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                        {{number_format($total_tallos_comb)}}
                    </div>
                </th>
                <th class="text-center" style="border-color: #9d9d9d; border-right: 2px solid black">
                    <div data-toggle="tooltip" data-placement="top"
                         title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                        {{number_format($total_exportada_comb)}}
                    </div>
                </th>
                <th class="text-center td_costos_fecha_total hidden" style="border-color: #9d9d9d;">
                    <div data-toggle="tooltip" data-placement="top"
                         title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                        {{number_format(($d['comb']->precio * $total_tallos_comb), 2)}}
                    </div>
                </th>
                <th class="text-center td_costos_fecha_total hidden" style="border-color: #9d9d9d; border-right: 3px solid black">
                    <div data-toggle="tooltip" data-placement="top"
                         title="{{$d['comb']->empresa != '' ? $d['comb']->empresa : 'COMPRADA'}} - {{$d['comb']->var_nombre}}">
                        {{number_format(($d['comb']->precio * $total_exportada_comb), 2)}}
                    </div>
                </th>
            </tr>
        @endforeach
        </tbody>
        <tr id="tr_fijo_bottom_0">
            <th class="text-right th_yura_green" style="border-radius: 0 0 0 18px; padding-right: 10px" colspan="4">
                Totales
            </th>
            @php
                $total_tallos = 0;
                $total_exportada = 0;
                $total_costos_tallos = 0;
                $total_costos_exportada = 0;
            @endphp
            @foreach($totales as $pos_t => $t)
                @php
                    $total_tallos += $t['tallos'];
                    $total_exportada += $t['exportada'];
                    $total_costos_tallos += $t['costos_tallos'];
                    $total_costos_exportada += $t['costos_exportada'];
                @endphp
                <th class="text-center th_yura_green" style="border-left: 2px solid black">
                    {{number_format($t['tallos'])}}
                </th>
                <th class="text-center th_yura_green" style="border-right: 2px solid black">
                    {{number_format($t['exportada'])}}
                </th>
                <th class="text-center td_costos_fecha_{{$fechas[$pos_t]->fecha}} hidden"
                    style="border-color: #9d9d9d; background-color: #e9ecef">
                    {{number_format($t['costos_tallos'], 2)}}
                </th>
                <th class="text-center td_costos_fecha_{{$fechas[$pos_t]->fecha}} hidden"
                    style="border-color: #9d9d9d; background-color: #e9ecef; border-right: 3px solid black">
                    {{number_format($t['costos_exportada'], 2)}}
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="border-color: #9d9d9d; border-left: 2px solid black">
                {{number_format($total_tallos)}}
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d; border-right: 2px solid black">
                {{number_format($total_exportada)}}
            </th>
            <th class="text-center td_costos_fecha_total hidden" style="border-color: #9d9d9d;  background-color: #e9ecef">
                {{number_format($total_costos_tallos, 2)}}
            </th>
            <th class="text-center td_costos_fecha_total hidden"
                style="border-color: #9d9d9d; border-right: 3px solid black; background-color: #e9ecef">
                {{number_format($total_costos_exportada, 2)}}
            </th>
        </tr>
    </table>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif

<style>
    #table_listado_bqt_diaria tr#tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #table_listado_bqt_diaria tr#tr_fija_top_1 th {
        position: sticky;
        top: 23px;
        z-index: 9;
    }

    #table_listado_bqt_diaria tr#tr_fijo_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    function mostrar_ocultar_costos(fecha) {
        if ($('#th_fecha_' + fecha).attr('colspan') == 2) {
            $('#th_fecha_' + fecha).attr('colspan', 4);
            $('#btn_fecha_' + fecha).attr('title', 'Ocultar costos');
        } else {
            $('#th_fecha_' + fecha).attr('colspan', 2);
            $('#btn_fecha_' + fecha).attr('title', 'Mostrar costos');
        }
        $('.td_costos_fecha_' + fecha).toggleClass('hidden');
    }
</script>