@if(count($listado) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_fenograma_perennes">
            <thead>
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                    <div style="width: 150px">
                        Variedad
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Área m<sup>2</sup>
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Ptas Iniciales
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 90px">
                        Densidad/m<sup>2</sup>
                    </div>
                </th>

                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy/m<sup>2</sup>/año
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy/m<sup>2</sup>/sem.
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
                    <div style="width: 90px">
                        Tallos Proy. Acum. 52
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
                    <div style="width: 80px">
                        Tallos Cos. Acum. 52
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        % Cump. Sem.
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
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/año (52 sem.)
                    </div>
                </th>
            </tr>
            </thead>
            @php
                $total_area = 0;
                $total_ptas_iniciales = 0;
                $prom_total_m2_ano = 0;
                $prom_total_m2_semana = 0;
                $total_proyectados = 0;
                $total_proyectados_acum = 0;
                $total_cosechados = 0;
                $total_cosechados_acum = 0;
                $prom_tallos_m2_ejec = 0;
                $prom_tallos_m2_ejec_acum = 0;
                $prom_flor_m2_anno_4 = 0;
                $prom_flor_m2_anno_13 = 0;
                $prom_flor_m2_anno_52 = 0;
                $positivo_flor_m2_anno_52 = 0;
                $total_proy_acum_anual = 0;
                $total_cos_acum_anual = 0;
            @endphp
            <tbody>
            @foreach($listado as $pos => $item)
                @php
                    $total_area += $item['area'];
                    $total_ptas_iniciales += $item['valores']->plantas_iniciales;
                    $densidad = $item['area'] > 0 ? $item['valores']->plantas_iniciales / $item['area'] : 0;
                    $prom_total_m2_ano += $item['tallos_m2_anno'];
                    $prom_total_m2_semana += $item['valores']->curva_perenne;
                    $total_proyectados += $item['valores']->proyectados;
                    $total_proyectados_acum += $item['valores']->proyectados_acum;
                    $total_cosechados += $item['valores']->cosechados;
                    $total_cosechados_acum += $item['valores']->cosechados_acum;
                    $prom_tallos_m2_ejec += $item['valores']->tallos_m2_ejecutado;
                    $prom_tallos_m2_ejec_acum += $item['proy_eje_acum_anual'];
                    $flor_m2_anno_52 = $item['area'] > 0 && intval(substr($semana, 2, 2)) > 0 ? round((($item['cos_acum_anual'] / $item['area']) / intval(substr($semana, 2, 2))) * 52, 2) : 0;
                    $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                    if ($flor_m2_anno_52 > 0)
                        $positivo_flor_m2_anno_52++;
                    $total_proy_acum_anual += $item['proy_acum_anual'];
                    $total_cos_acum_anual += $item['cos_acum_anual'];
                @endphp
                <tr style="background-color: {{$pos % 2 == 0 ? '#e9ecef' : ''}}">
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{$item['variedad']->nombre}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['area'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->plantas_iniciales)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{round($densidad, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['tallos_m2_anno'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$item['valores']->curva_perenne}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->proyectados, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['proy_acum_anual'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->proyectados_acum, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->cosechados)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['cos_acum_anual'])}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->cosechados_acum)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->porcentaje_cumplimiento, 2)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{porcentaje($item['cos_acum_anual'], $item['proy_acum_anual'], 1)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->tallos_m2_ejecutado, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['proy_eje_acum_anual'], 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$flor_m2_anno_52}}
                    </td>
                    {{--<td class="text-center" style="border-color: #9d9d9d">
                        {{round($item['valores']->sum_ejec_52_sem * 1, 2)}}
                    </td>--}}
                </tr>
            @endforeach
            </tbody>
            {{-- TOTALES --}}
            <tr>
                <th class="text-center th_yura_green" style="border-radius: 0 0 0 18px">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_area, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_ptas_iniciales)}}
                </th>
                <th class="text-center th_yura_green">
                    {{$total_area > 0 ? round($total_ptas_iniciales / $total_area, 2) : 0}}
                </th>
                <th class="text-center th_yura_green">
                    {{round($prom_total_m2_ano / count($listado), 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{round($prom_total_m2_semana / count($listado), 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_proyectados, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_proy_acum_anual, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_proyectados_acum, 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_cosechados)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_cos_acum_anual)}}
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($total_cosechados_acum)}}
                </th>
                <th class="text-center th_yura_green">
                    {{$total_proyectados > 0 ? round(($total_cosechados * 100) / $total_proyectados, 2) : 0}}%
                </th>
                <th class="text-center th_yura_green">
                    {{$total_proyectados_acum > 0 ? round(($total_cos_acum_anual * 100) / $total_proy_acum_anual, 2) : 0}}%
                </th>
                <th class="text-center th_yura_green">
                    {{round($prom_tallos_m2_ejec / count($listado), 2)}}
                </th>
                <th class="text-center th_yura_green">
                    {{round($prom_tallos_m2_ejec_acum / count($listado), 2)}}
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 0 18px 0">
                    {{round($prom_flor_m2_anno_52 / count($listado), 2)}}
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif
<style>
    #table_fenograma_perennes tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>