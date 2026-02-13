@if(count($listado) > 0)
    <div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border-radius: 18px 18px 0 0">
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Variedad
                    </div>
                </th>
                <th class="text-center" style="width: 80px; background-color: #0b3248; color: white">
                    <div style="width: 80px">
                        Área Prod.
                    </div>
                </th>
                <th class="text-center" style="width: 80px; background-color: #0b3248; color: white">
                    <div style="width: 80px">
                        Área Cult.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Proy.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Proy. Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Cosechados
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Cos. Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump. Acum.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejec.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos/m<sup>2</sup> Ejec. Acum.
                    </div>
                </th>
            </tr>
            @php
                $area_total = 0;
                $proy_total = 0;
                $cos_total = 0;
                $proy_acum_total = 0;
                $cos_acum_total = 0;
            @endphp
            @foreach($listado as $pos => $s)
                @php
                    $area_produccion = $s['area_produccion'];
                    $area_semana = $s['area_semana'];
                    $area_total += $area_produccion;
                    $proy_acum_total += $s['proyectados_acum'];
                    $proyectados = $s['proyectados'];
                    $proy_total += $proyectados;
                    $cosechados = $s['tallos_cosechados'];
                    $cos_acum_total += $s['tallos_cosechados_acum'];
                    $cos_total += $cosechados;
                @endphp
                <tr>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{$s['variedad']->nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($area_produccion, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($area_semana, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($proyectados, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($s['proyectados_acum'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($cosechados, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($s['tallos_cosechados_acum'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{porcentaje($cosechados, $proyectados, 1)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{porcentaje($s['tallos_cosechados_acum'], $s['proyectados_acum'], 1)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$area_semana > 0 ? round($cosechados / $area_semana, 2) : 0}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$area_semana > 0 ? round($s['tallos_cosechados_acum'] / $area_semana, 2) : 0}}
                    </td>
                </tr>
            @endforeach
            <tr>
                <th class="text-center th_yura_green">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($area_total / count($listado), 2)}}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($proy_total, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($proy_acum_total, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($cos_total, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($cos_acum_total, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{porcentaje($cos_total, $proy_total, 1)}}%
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{($area_total / count($listado)) > 0 ? round($cos_total / ($area_total / count($listado)), 2) : 0}}
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados que mostrar</div>
@endif

<style>
    tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>