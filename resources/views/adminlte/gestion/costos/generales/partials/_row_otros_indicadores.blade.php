<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio TOTAL x Tallo
    </span>
    </th>
    @php
        $total_precio_x_tallo = $total_tallos_producidos > 0 ? $total_valor_venta / $total_tallos_producidos : 0;
    @endphp
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $tallos = $item->tallos_exportables + $compra_flor[$pos_s]->tallos_bqt;
            //$precio_x_tallo_bqt = $tallos_bqt_total[$pos]->tallos_bqt > 0 ? $venta_bqt_total[$pos]->venta_bouquetera / $tallos_bqt_total[$pos]->tallos_bqt : 0;
            //$venta_bqt = $compra_flor[$pos_s]->tallos_bqt * $precio_x_tallo_bqt;
            //$venta = ($item->venta + $venta_bqt);
            $venta = ($item->venta + $item->venta_bouquetera);
            $value = $tallos > 0 ? $venta / $tallos : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_precio_x_tallo, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Precio x Tallo Bqt
    </span>
    </th>
    @php
        $total_precio_x_tallo_bqt = $total_tallos_bouquetera > 0 ? $total_valor_bqt / $total_tallos_bouquetera : 0;
    @endphp
    @foreach($compra_flor as $pos_s => $item)
        @php
            //$precio_x_tallo = $tallos_bqt_total[$pos_s]->tallos_bqt > 0 ? $venta_bqt_total[$pos_s]->venta_bouquetera / $tallos_bqt_total[$pos_s]->tallos_bqt : 0;
            //$venta = $item->tallos_bqt * $precio_x_tallo;
            //$value = $item->tallos_bqt > 0 ? $venta / $item->tallos_bqt : 0;
            $value = $item->tallos_bqt > 0 ? $resumen_semanal[$pos_s]->venta_bouquetera / $item->tallos_bqt : 0;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_precio_x_tallo_bqt, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Venta/m<sup>2</sup>
    </span>
    </th>
    @php
        $total_venta_m2 = 0;
    @endphp
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $value = $resumen_area[$pos_s] > 0 ? ($item->venta + $item->venta_bouquetera) / $resumen_area[$pos_s] : 0;
            $total_venta_m2 += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_venta_m2, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos Totales/m<sup>2</sup>
    </span>
    </th>
    @php
        $total_costos_m2 = 0;
    @endphp
    @foreach($resumen_costos as $pos_s => $item)
        @php
            $value = $resumen_area[$pos_s] > 0 ? ($item->mano_obra + $item->insumos + $item->fijos + $item->regalias + ($compra_flor[$pos_s]->tallos + $compra_flor[$pos_s]->exportada)) / $resumen_area[$pos_s] : 0;
            $total_costos_m2 += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_costos_m2, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        EBITDA/m<sup>2</sup>
    </span>
    </th>
    @php
        $ebitda_m2 = $total_venta_m2 - $total_costos_m2;
    @endphp
    @foreach($semanas as $pos_s => $item)
        @php
            $venta = $resumen_area[$pos_s] > 0 ? ($resumen_semanal[$pos_s]->venta + $resumen_semanal[$pos_s]->venta_bouquetera) / $resumen_area[$pos_s] : 0;
            $costo = $resumen_area[$pos_s] > 0 ? ($resumen_costos[$pos_s]->mano_obra + $resumen_costos[$pos_s]->insumos + $resumen_costos[$pos_s]->fijos + $resumen_costos[$pos_s]->regalias + ($compra_flor[$pos_s]->tallos + $compra_flor[$pos_s]->exportada)) / $resumen_area[$pos_s] : 0;
            $value = $venta - $costo;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px; color: {{$value < 0 ? '#D01C62' : '#00B388'}}">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px; color: {{$ebitda_m2 < 0 ? '#D01C62' : '#00B388'}}">
            ${{number_format($ebitda_m2, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_otros_indicadores hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Costos Finca/m<sup>2</sup>
    </span>
    </th>
    @php
        $total_costos_finca_m2 = 0;
    @endphp
    @foreach($resumen_costos as $pos_s => $item)
        @php
            $value = $resumen_area[$pos_s] > 0 ? ($item->mano_obra + $item->insumos + $item->fijos + $item->regalias) / $resumen_area[$pos_s] : 0;
            $total_costos_finca_m2 += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_costos_finca_m2, 2)}}
        </div>
    </th>
</tr>