<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Nacional
    </span>
    </th>
    @php
        $total_nacionales = 0;
    @endphp
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $value = $item->nacionales;
            $total_nacionales += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_nacionales, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        % Nacional
    </span>
    </th>
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $value = porcentaje($item->nacionales, ($item->tallos_exportables + $compra_flor[$pos_s]->tallos_bqt), 1);
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}%
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            {{number_format(porcentaje($total_nacionales, $total_tallos_producidos, 1), 2)}}%
        </div>
    </th>
</tr>

<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Bajas
    </span>
    </th>
    @php
        $total_bajas = 0;
    @endphp
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $value = $item->bajas;
            $total_bajas += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            {{number_format($total_bajas, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Compra Flor Bqt
    </span>
    </th>
    @php
        $total_compra_flor_bqt = 0;
    @endphp
    @foreach($compra_flor as $pos_s => $item)
        @php
            $value = $item->tallos;
            $total_compra_flor_bqt += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_compra_flor_bqt, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
        Compras Flor Export
    </span>
    </th>
    @php
        $total_compra_flor_export = 0;
    @endphp
    @foreach($compra_flor as $pos_s => $item)
        @php
            $value = $item->exportada;
            $total_compra_flor_export += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                ${{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            ${{number_format($total_compra_flor_export, 2)}}
        </div>
    </th>
</tr>

<tr class="tr_unosoft hidden">
    <th class="text-center th_fijo_left_0" style="background-color: #e9ecef; border-color: #9d9d9d">
    <span style="margin: auto 5px; color: black; font-weight: bold; font-size: 0.85em">
       Tallos Vendidos
    </span>
    </th>
    @php
        $total_tallos_vendidos = 0;
    @endphp
    @foreach($resumen_semanal as $pos_s => $item)
        @php
            $value = $item->tallos_vendidos;
            $total_tallos_vendidos += $value;
        @endphp
        <th class="text-center" style="border-color: #9d9d9d;">
            <div style="width: 100px">
                {{number_format($value, 2)}}
            </div>
        </th>
    @endforeach
    <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
        <div style="width: 110px;">
            {{number_format($total_tallos_vendidos, 2)}}
        </div>
    </th>
</tr>