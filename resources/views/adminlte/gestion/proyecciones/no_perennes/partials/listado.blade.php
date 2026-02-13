@if(count($listado) > 0)
    <div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
        <table class="table-bordered table-striped" style="width: 100%; border-radius: 18px 18px 0 0">
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Semana
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
                        Tallos Proy.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos Proy. Acum. Año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos. Acum. Año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump. Acum. Año
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
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Tallos/m<sup>2</sup>/año (52 sem.)
                    </div>
                </th>
            </tr>
            @php
                $area_total = 0;
                $proy_total = 0;
                $cos_total = 0;
                $prom_tallos_m2_ejec = 0;
                $positivo_tallos_m2_ejec = 0;
                $prom_flor_m2_anno_52 = 0;
                $positivo_flor_m2_anno_52 = 0;
            @endphp
            @foreach($listado as $pos => $s)
                @php
                    $area_produccion = $s['area_produccion'];
                    $area_semana = $s['area_semana'];
                    $area_total += $area_produccion;
                    $proyectados = $s['proyectados'];
                    $proy_total += $proyectados;
                    $cosechados = $s['tallos_cosechados'];
                    $cos_total += $cosechados;

                    $tallos_m2_ejec = $area_semana > 0 ? round($cosechados / $area_semana, 2) : 0;
                    $prom_tallos_m2_ejec += $tallos_m2_ejec;
                    $flor_m2_anno_52 = $area_semana > 0 && ($pos + 1) > 0 ? round((($cos_total / $area_semana) / ($pos + 1)) * 52, 2) : 0;
                    $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                    if ($tallos_m2_ejec > 0)
                        $positivo_tallos_m2_ejec++;
                    if ($flor_m2_anno_52 > 0)
                        $positivo_flor_m2_anno_52++;
                @endphp
                <tr>
                    <td class="text-center td_yura_default" style="border-color: #9d9d9d">
                        {{$s['codigo']}}
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
                        {{number_format($proy_total, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($cosechados, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($cos_total, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{porcentaje($cosechados, $proyectados, 1)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{porcentaje($cos_total, $proy_total, 1)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($tallos_m2_ejec, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($prom_tallos_m2_ejec, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($flor_m2_anno_52, 2)}}
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
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($cos_total, 2)}}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{porcentaje($cos_total, $proy_total, 1)}}%
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2)}}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 0 18px 0">
                    {{number_format($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2)}}
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