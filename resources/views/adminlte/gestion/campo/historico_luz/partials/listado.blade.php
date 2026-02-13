@if(count($listado) > 0)
    <div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
        <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_activos">
            <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Variedad
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Módulo
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Poda
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Área
                </th>
                <th class="text-center th_yura_green">
                    <div class="text-center" style="width: 120px">
                        Fecha Poda
                    </div>
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Días Ciclo
                </th>
                <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                    Días Luz
                </th>
                <th class="text-center th_yura_green">
                    <div class="text-center" style="width: 60px">
                        Tipo Luz
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div class="text-center" style="width: 60px">
                        # Lamp.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    Horario
                </th>
                <th class="text-center th_yura_green">
                    <div class="text-center" style="width: 60px">
                        Hrs. Luz
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div class="text-center" style="width: 60px">
                        Hrs. Luz acum.
                    </div>
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo acum.
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo/m2
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                    Costo/m2 acum.
                </th>
            </tr>
            </thead>
            <tbody>
            @php
                $anterior = $listado[0]['ciclo'];
                $cant_dias_luz = 0;
                $cant_lamparas = 0;
                $cant_horas_luz = 0;
                $cant_costos = 0;

                $total_area = 0;
                $total_lamparas = 0;
                $total_horas_luz = 0;
                $total_costos = 0;
            @endphp
            @foreach($listado as $pos => $item)
                @php
                    $ciclo = $item['ciclo'];
                    $modulo = $ciclo->modulo;
                    $luz = $item['luz'];
                @endphp
                @if($anterior->id_ciclo != $ciclo->id_ciclo)
                    <tr class="mouse-hand bg-yura_dark"
                        onclick="$('.tr_desglose_{{$anterior->id_ciclo}}').toggleClass('hidden')">
                        <th class="text-center" style="border-color: white">
                            {{$anterior->variedad->siglas}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$anterior->modulo->nombre}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$anterior->modulo->getPodaSiembraByCiclo($anterior->id_ciclo)}}
                        </th>
                        <th class="text-center" style="border-color: white; padding-left: 5px; padding-right: 5px">
                            {{number_format($anterior->area, 2)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{convertDateToText($anterior->fecha_inicio)}}
                        </th>
                        <th class="text-center" style="border-color: white" colspan="2">
                            {{$cant_dias_luz}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$cant_lamparas}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{number_format($cant_horas_luz)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            ${{number_format($cant_costos, 2)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            ₵{{$anterior->area > 0 ? round($cant_costos / $anterior->area, 4) * 100 : 0}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                    </tr>
                    @php
                        $total_area += $anterior->area;
                        $total_lamparas += $cant_lamparas;
                        $total_horas_luz += $cant_horas_luz;
                        $total_costos += $cant_costos;

                        $anterior = $ciclo;
                        $cant_dias_luz = 0;
                        $cant_lamparas = 0;
                        $cant_horas_luz = 0;
                        $cant_costos = 0;
                    @endphp
                @endif
                @php
                    $cant_dias_luz++;
                    $cant_lamparas += $luz->lamparas;
                    $cant_horas_luz += $luz->getHorasDia();

                    $costo_x_tipo = $luz->tipo_luz / 1000;
                    $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                    $costo_x_lampara = $costo_x_lampara * $luz->getHorasDia();
                    $costo_luz = $costo_x_lampara * 0.10;

                    $cant_costos += $costo_luz;

                    $dias_ciclo = difFechas($luz->fecha, $ciclo->fecha_inicio)->days;
                @endphp
                <tr class="tr_desglose_{{$ciclo->id_ciclo}} hidden" style="background-color: {{$pos % 2 == 0 ? '#e9ecef' : ''}}">
                    <td class="text-left" style="border-color: #9d9d9d; padding-left: 5px" colspan="5">
                        {{convertDateToText($luz->fecha)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{$dias_ciclo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{$dias_ciclo - $luz->inicio_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{$luz->tipo_luz}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{$luz->lamparas}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{substr($luz->hora_ini, 0, 5).' - '.substr($luz->hora_fin, 0, 5)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{$luz->getHorasDia()}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        {{number_format($cant_horas_luz)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        ${{round($costo_luz, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        ${{number_format($cant_costos, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        ₵{{$ciclo->area > 0 ? round($costo_luz / $ciclo->area, 4) * 100 : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; padding-left: 5px; padding-right: 5px">
                        ₵{{$anterior->area > 0 ? round($cant_costos / $anterior->area, 4) * 100 : 0}}
                    </td>
                </tr>
                @if($pos == (count($listado) - 1))
                    <tr class="mouse-hand bg-yura_dark"
                        onclick="$('.tr_desglose_{{$anterior->id_ciclo}}').toggleClass('hidden')">
                        <th class="text-center" style="border-color: white">
                            {{$anterior->variedad->siglas}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$anterior->modulo->nombre}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$anterior->modulo->getPodaSiembraByCiclo($anterior->id_ciclo)}}
                        </th>
                        <th class="text-center" style="border-color: white; padding-left: 5px; padding-right: 5px">
                            {{number_format($anterior->area, 2)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{convertDateToText($anterior->fecha_inicio)}}
                        </th>
                        <th class="text-center" style="border-color: white" colspan="2">
                            {{$cant_dias_luz}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{$cant_lamparas}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            {{number_format($cant_horas_luz)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            ${{number_format($cant_costos, 2)}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                        <th class="text-center" style="border-color: white">
                            ₵{{$anterior->area > 0 ? round($cant_costos / $anterior->area, 4) * 100 : 0}}
                        </th>
                        <th class="text-center" style="border-color: white">
                        </th>
                    </tr>
                @endif
            @endforeach
            </tbody>
            <tfoot>
            <tr id="tr_fijo_bottom_0">
                <th class="text-center th_yura_green" style="padding-left: 5px" colspan="3">
                    TOTALES
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                    {{number_format($total_area, 2)}}
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px" colspan="4">
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                    {{number_format($total_lamparas)}}
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                    {{number_format($total_horas_luz)}}
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                    ${{number_format($total_costos, 2)}}
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                    ₵{{$total_area > 0 ? round($total_costos / $total_area, 4) * 100 : 0}}
                </th>
                <th class="text-center th_yura_green" style="padding-left: 5px">
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
@else
    <div class="text-center alert alert-info">No se han encontrado resultados</div>
@endif
<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    #tr_fijo_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 8;
    }
</style>