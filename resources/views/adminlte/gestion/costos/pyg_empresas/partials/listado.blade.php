<table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
       id="table_costos_generales">
    <tr id="tr_fijo_top_0">
        <th class="text-center th_fijo_left_0 th_yura_green" style="border-radius: 18px 0 0 0; background-color: #00B388 !important;">

        </th>
        @foreach($semanas as $sem)
            <th class="text-center th_yura_green">
                {{$sem->codigo}}
            </th>
        @endforeach
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
            Total
        </th>
    </tr>
    {{-- AREA m2 --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                ÁREA m<sup>2</sup>
            </span>
        </th>
        @php
            $total_area_m2 = 0;
        @endphp
        @foreach($resumen_area as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item, 2)}}
                </div>
            </th>
            @php
                $total_area_m2 += $item;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_area_m2, 2)}}
            </div>
        </th>
    </tr>
    {{-- TALLOS COSECHADOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                TALLOS COSECHADOS
            </span>
        </th>
        @php
            $total_tallos_cosechados = 0;
        @endphp
        @foreach($resumen_semanal as $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_cosechados, 2)}}
                </div>
            </th>
            @php
                $total_tallos_cosechados += $item->tallos_cosechados;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_cosechados, 2)}}
            </div>
        </th>
    </tr>
    {{-- TALLOS PRODUCIDOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_tallos').toggleClass('hidden')">
                TALLOS PRODUCIDOS
            </span>
        </th>
        @php
            $total_tallos_producidos = 0;
        @endphp
        @foreach($resumen_semanal as $pos_s => $item)
            <th class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_exportables + $compra_flor[$pos_s]->tallos_bqt, 2)}}
                </div>
            </th>
            @php
                $total_tallos_producidos += $item->tallos_exportables + $compra_flor[$pos_s]->tallos_bqt;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_producidos, 2)}}
            </div>
        </th>
    </tr>
    {{-- EXPORTABLES --}}
    <tr class="tr_tallos hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                EXPORTABLES
            </span>
        </td>
        @php
            $total_tallos_exportables = 0;
        @endphp
        @foreach($resumen_semanal as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_exportables, 2)}}
                </div>
            </td>
            @php
                $total_tallos_exportables += $item->tallos_exportables;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_exportables, 2)}}
            </div>
        </th>
    </tr>
    {{-- BOUQUETERA --}}

    <tr class="tr_tallos hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                BOUQUETERA
            </span>
        </td>
        @php
            $total_tallos_bouquetera = 0;
        @endphp
        @foreach($compra_flor as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    {{number_format($item->tallos_bqt, 2)}}
                </div>
            </td>
            @php
                $total_tallos_bouquetera += $item->tallos_bqt;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                {{number_format($total_tallos_bouquetera, 2)}}
            </div>
        </th>
    </tr>
    {{-- VENTA TOTAL --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_venta').toggleClass('hidden')">
                VENTA TOTAL
            </span>
        </th>
        @php
            $total_valor_venta = 0;
        @endphp
        @foreach($resumen_semanal as $pos => $item)
            @php
                //$precio_x_tallo_bqt = $tallos_bqt_total[$pos]->tallos_bqt > 0 ? $venta_bqt_total[$pos]->venta_bouquetera / $tallos_bqt_total[$pos]->tallos_bqt : 0;
                //$venta_bqt = $compra_flor[$pos]->tallos_bqt * $precio_x_tallo_bqt;
                //$value = $item->venta + $venta_bqt;
                $value = $item->venta + $item->venta_bouquetera;
                $total_valor_venta += $value;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #3cf7ff">
                <div style="width: 100px">
                    ${{number_format($value, 2)}}
                </div>
            </th>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_valor_venta, 2)}}
            </div>
        </th>
    </tr>
    {{-- VENTA --}}
    <tr class="tr_venta hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                VENTA
            </span>
        </td>
        @php
            $total_venta = 0;
        @endphp
        @foreach($resumen_semanal as $item)
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($item->venta, 2)}}
                </div>
            </td>
            @php
                $total_venta += $item->venta;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_venta, 2)}}
            </div>
        </td>
    </tr>
    {{-- VENTA BOUQUETERA --}}
    <tr class="tr_venta hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                VENTA BOUQUETERA
            </span>
        </td>
        @php
            $total_valor_bqt = 0;
        @endphp
        @foreach(/*$compra_flor*/$resumen_semanal as $pos => $item)
            @php
                //$precio_x_tallo = $tallos_bqt_total[$pos]->tallos_bqt > 0 ? $venta_bqt_total[$pos]->venta_bouquetera / $tallos_bqt_total[$pos]->tallos_bqt : 0;
                //$venta = $item->tallos_bqt * $precio_x_tallo;
                $venta = $item->venta_bouquetera;
                $total_valor_bqt += $venta;
            @endphp
            <td class="text-center" style="border-color: #9d9d9d">
                <div style="width: 100px">
                    ${{number_format($venta, 2)}}
                </div>
            </td>
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_valor_bqt, 2)}}
            </div>
        </td>
    </tr>
    {{-- TOTAL COSTOS --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_costos_total').toggleClass('hidden')">
                TOTAL COSTOS
            </span>
        </th>
        @php
            $total_costos_operativos = 0;
        @endphp
        @foreach($resumen_costos as $pos_item => $item)
            @php
                $costos_operativos = $item->mano_obra + $item->insumos + $item->fijos + $item->regalias + ($compra_flor[$pos_item]->tallos + $compra_flor[$pos_item]->exportada);
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                <div style="width: 100px">
                    ${{number_format($costos_operativos, 2)}}
                </div>
            </th>
            @php
                $total_costos_operativos += $costos_operativos;
            @endphp
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_operativos, 2)}}
            </div>
        </th>
    </tr>
    {{-- COSTOS MO --}}
    <tr class="tr_costos_total hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                MANO OBRA
            </span>
        </td>
        @php
            $total_costos_mo = 0;
        @endphp
        @foreach($resumen_costos as $item)
            <td class="text-center" style="border-color: #9d9d9d;">
                <div style="width: 100px">
                    ${{number_format($item->mano_obra, 2)}}
                </div>
            </td>
            @php
                $total_costos_mo += $item->mano_obra;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_mo, 2)}}
            </div>
        </td>
    </tr>
    {{-- COSTOS INSUMOS --}}
    <tr class="tr_costos_total hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                INSUMOS
            </span>
        </td>
        @php
            $total_costos_insumos = 0;
        @endphp
        @foreach($resumen_costos as $item)
            <td class="text-center" style="border-color: #9d9d9d;">
                <div style="width: 100px">
                    ${{number_format($item->insumos, 2)}}
                </div>
            </td>
            @php
                $total_costos_insumos += $item->insumos;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_insumos, 2)}}
            </div>
        </td>
    </tr>
    {{-- COSTOS FIJOS --}}
    <tr class="tr_costos_total hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                FIJOS
            </span>
        </td>
        @php
            $total_costos_fijos = 0;
        @endphp
        @foreach($resumen_costos as $item)
            <td class="text-center" style="border-color: #9d9d9d;">
                <div style="width: 100px">
                    ${{number_format($item->fijos, 2)}}
                </div>
            </td>
            @php
                $total_costos_fijos += $item->fijos;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_fijos, 2)}}
            </div>
        </td>
    </tr>
    {{-- COSTOS REGALIAS --}}
    <tr class="tr_costos_total hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                REGALÍAS
            </span>
        </td>
        @php
            $total_costos_regalias = 0;
        @endphp
        @foreach($resumen_costos as $item)
            <td class="text-center" style="border-color: #9d9d9d;">
                <div style="width: 100px">
                    ${{number_format($item->regalias, 2)}}
                </div>
            </td>
            @php
                $total_costos_regalias += $item->regalias;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_regalias, 2)}}
            </div>
        </td>
    </tr>
    {{-- COSTOS COMPRA de FLOR --}}
    <tr class="tr_costos_total hidden">
        <td class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-size: 0.85em">
                COMPRA de FLOR
            </span>
        </td>
        @php
            $total_costos_compra_flor = 0;
        @endphp
        @foreach($compra_flor as $item)
            <td class="text-center" style="border-color: #9d9d9d;">
                <div style="width: 100px">
                    ${{number_format($item->tallos + $item->exportada, 2)}}
                </div>
            </td>
            @php
                $total_costos_compra_flor += $item->tallos + $item->exportada;
            @endphp
        @endforeach
        <td class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            <div style="width: 110px">
                ${{number_format($total_costos_compra_flor, 2)}}
            </div>
        </td>
    </tr>
    {{-- EBITDA --}}
    <tr id="tr_fijo_bottom_0">
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
                EBITDA
            </span>
        </th>
        @foreach($semanas as $i => $item)
            @php
                //$precio_x_tallo_bqt = $tallos_bqt_total[$i]->tallos_bqt > 0  ? $venta_bqt_total[$i]->venta_bouquetera / $tallos_bqt_total[$i]->tallos_bqt : 0;
                //$venta_bqt = $compra_flor[$i]->tallos_bqt * $precio_x_tallo_bqt;
                //$ventas = $resumen_semanal[$i]->venta + $venta_bqt;
                $ventas = $resumen_semanal[$i]->venta + $resumen_semanal[$i]->venta_bouquetera;
                $costos = $resumen_costos[$i]->mano_obra + $resumen_costos[$i]->insumos + $resumen_costos[$i]->fijos + $resumen_costos[$i]->regalias + ($compra_flor[$i]->tallos + $compra_flor[$i]->exportada);
                $ebitda = $ventas - $costos;
            @endphp
            <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef; color: {{$ebitda < 0 ? '#d01c62' : '#00b388'}}">
                <div style="width: 100px">
                    ${{number_format($ebitda, 2)}}
                </div>
            </th>
        @endforeach
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            @php
                $ebitda = $total_valor_venta - ($total_costos_operativos);
            @endphp
            <div style="width: 110px; color: {{$ebitda < 0 ? '#d01c62' : '#00b388'}}">
                ${{number_format($ebitda, 2)}}
            </div>
        </th>
    </tr>
    {{-- OTROS INDICADORES --}}
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_otros_indicadores').toggleClass('hidden')">
                INDICADORES SEMANALES
            </span>
        </th>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.pyg_empresas.partials._row_otros_indicadores')
    <tr>
        <th class="text-center th_fijo_left_0" style="background-color: #c4c4ff; border-color: #9d9d9d">
            <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em" class="btn btn-xs btn-link"
                  onclick="$('.tr_unosoft').toggleClass('hidden')">
                UNOSOFT
            </span>
        </th>
        <th class="text-center" style="background-color: #c4c4ff; border-color: #9d9d9d" colspan="{{count($semanas) + 1}}">
        </th>
    </tr>
    @include('adminlte.gestion.costos.pyg_empresas.partials._row_unosoft')
</table>

<style>
    .th_fijo_left_0 {
        position: sticky;
        left: 0;
        z-index: 1;
        border-color: #9d9d9d;
        background-color: #e9ecef;
    }

    #table_costos_generales #tr_fijo_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #table_costos_generales #tr_fijo_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>