<div style="overflow-y: scroll; max-height: 400px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_desglose_ciclo">
        <tr id="tr_fijo_top_0">
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                <div style="width: 100%">Módulo</div>
            </th>
            <th class="text-center th_yura_green">
                Inicio
            </th>
            <th class="text-center th_yura_green">
                Poda/Siembra
            </th>
            <th class="text-center th_yura_green">
                Área m<sup>2</sup>
            </th>
            <th class="text-center th_yura_green">
                Días
            </th>
            <th class="text-center th_yura_green">
                1ra Flor
            </th>
            <th class="text-center th_yura_green">
                Tallos Cosechados
            </th>
            <th class="text-center th_yura_green">
                Tallos/m<sup>2</sup>
            </th>
            <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                Final
            </th>
        </tr>
        @foreach($data as $pos_data => $d)
            @php
                $total_area = 0;
                $total_dias = 0;
                $total_cosechados = 0;
                $total_tallos_m2 = 0;
            @endphp
            @foreach($d['ciclos'] as $pos_c => $c)
                @php
                    $total_area += $c->area_m2;
                    $total_dias += $c->dias;
                    $total_cosechados += $c->tallos_cosechados;
                    $total_tallos_m2 += $c->real_tallos_m2;
                @endphp
                <tr style="background-color: {{$colores_semana[$pos_data]}}">
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->nombre_modulo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->fecha_inicio}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->poda_siembra}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($c->area_m2, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->dias}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->primera_flor}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{number_format($c->tallos_cosechados)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->real_tallos_m2}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d;">
                        {{$c->fecha_fin}}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="text-center" style="border-color: #9d9d9d" colspan="3">
                    Semana: <strong>{{$semanas[$pos_data]->codigo}}</strong>
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{number_format($total_area, 2)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{count($d['ciclos']) > 0 ? round($total_dias / count($d['ciclos']), 2) : 0}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{number_format($total_cosechados)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{count($d['ciclos']) > 0 ? round($total_tallos_m2 / count($d['ciclos']), 2) : 0}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                </th>
            </tr>
        @endforeach
    </table>
</div>

<style>
    #table_desglose_ciclo tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>