@if(count($listado) > 0)
    <div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
        <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
            <tr id="tr_fijo_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        SEMANA
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Area m<sup>2</sup>
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Ptas. Iniciales
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 70px">
                        Densidad
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/anual
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 100px">
                        Tallos/m<sup>2</sup>/sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Tallos Proy.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy. Acum. Anual
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Proy. Acum. 52
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Tallos Cos.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos. Acum. Anual
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        Tallos Cos. Acum. 52
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        % Cump. Sem.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px">
                        % Cump. Sem. Acum.
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
                        Tallos/m<sup>2</sup>/anual (52 sem.)
                    </div>
                </th>
            </tr>
            @php
                $proy_acum_anno = 0;
                $cos_acum = 0;
                $prom_tallos_m2_ejec = 0;
                $positivo_tallos_m2_ejec = 0;
                $prom_flor_m2_anno_52 = 0;
                $positivo_flor_m2_anno_52 = 0;
            @endphp
            @foreach($listado as $pos => $item)
                @php
                    $densidad = $area > 0 ? $item['valores']->plantas_iniciales / $area : 0;
                    $proy_acum_anno += $item['valores']->proyectados;
                    $cos_acum += $item['valores']->cosechados;
                    $prom_tallos_m2_ejec += $item['valores']->tallos_m2_ejecutado;
                    $flor_m2_anno_52 = $area > 0 && ($pos + 1) > 0 ? round((($cos_acum / $area) / ($pos + 1)) * 52, 2) : 0;
                    $prom_flor_m2_anno_52 += $flor_m2_anno_52;
                    if ($item['valores']->tallos_m2_ejecutado > 0)
                        $positivo_tallos_m2_ejec++;
                    if ($flor_m2_anno_52 > 0)
                        $positivo_flor_m2_anno_52++;
                @endphp
                <tr style="background-color: {{$pos % 2 == 0 ? '#e9ecef' : ''}}">
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{$item['semana']->codigo}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{number_format($area, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{number_format($item['valores']->plantas_iniciales)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{round($densidad, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                        {{number_format($tallos_m2_anno, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$item['valores']->curva_perenne}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->proyectados, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($proy_acum_anno, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->proyectados_acum, 2)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->cosechados)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($cos_acum)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->cosechados_acum)}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->porcentaje_cumplimiento, 2)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->porcentaje_cumplimiento_acum, 2)}}%
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item['valores']->tallos_m2_ejecutado, 2)}}
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
                <th class="text-left th_yura_green" style="padding-left: 10px" colspan="6">
                    Totales
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($proy_acum_anno, 2)}}
                </th>
                <th class="text-center th_yura_green" colspan="2">
                </th>
                <th class="text-center th_yura_green">
                    {{number_format($cos_acum)}}
                </th>
                <th class="text-center th_yura_green" colspan="3">
                </th>
                <th class="text-center th_yura_green">
                    {{$proy_acum_anno > 0 ? round(($cos_acum * 100) / $proy_acum_anno, 2) : 0}}%
                </th>
                <th class="text-center th_yura_green">
                    {{$positivo_tallos_m2_ejec > 0 ? number_format($prom_tallos_m2_ejec / $positivo_tallos_m2_ejec, 2) : 0}}
                </th>
                <th class="text-center th_yura_green">
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 0 18px 0">
                    {{$positivo_flor_m2_anno_52 > 0 ? number_format($prom_flor_m2_anno_52 / $positivo_flor_m2_anno_52, 2) : 0}}
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">Faltan datos por ingresar para esta variedad</div>
@endif
<style>
    tr#tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>