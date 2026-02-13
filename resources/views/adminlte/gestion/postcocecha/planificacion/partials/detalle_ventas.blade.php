<legend style="font-size: 1em; margin-bottom: 2px" class="text-center">
    Pedidos de la variedad "<b>{{ $variedad->nombre }}</b>"
</legend>
<div style="overflow-y: scroll; max-height: 700px" id="div_detalles_venta">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                FECHA
            </th>
            <th class="text-center th_yura_green">
                PEDIDO
            </th>
            <th class="text-center th_yura_green">
                CLIENTE
            </th>
            <th class="text-center th_yura_green">
                RECETA
            </th>
            <th class="text-center th_yura_green">
                TOTAL VENTA
            </th>
            <th class="text-center bg-yura_dark">
                OT
            </th>
            <th class="text-center bg-yura_dark">
                Pre-OT
            </th>
            <th class="text-center bg-yura_dark">
                Restante
            </th>
        </tr>
        @php
            $total_venta = 0;
            $total_ot = 0;
            $total_pre_ot = 0;
            $total_restantes = 0;
        @endphp
        @foreach ($listado as $pos_p => $item)
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ convertDateToText($item['pedido']->fecha) }}
                </th>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    #{{ $item['pedido']->codigo }}
                </td>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['pedido']->cliente->detalle()->nombre }}
                </th>
                <td class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item['det_ped']->variedad->nombre }}
                </td>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ number_format($item['tallos_venta']) }}
                </th>
                <td class="text-center" style="border-color: #9d9d9d; color: black; background-color: #dddddd;">
                    <em>{{ number_format($item['tallos_ot']) }}</em>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; color: black; background-color: #dddddd;">
                    <em>{{ number_format($item['tallos_pre_ot']) }}</em>
                </td>
                <td class="text-center" style="border-color: #9d9d9d; color: black; background-color: #dddddd;">
                    <em>{{ number_format($item['tallos_restantes']) }}</em>
                </td>
            </tr>
            @php
                $total_venta += $item['tallos_venta'];
                $total_ot += $item['tallos_ot'];
                $total_pre_ot += $item['tallos_pre_ot'];
                $total_restantes += $item['tallos_restantes'];
            @endphp
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="4">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_venta) }}
            </th>
            <th class="text-center bg-yura_dark">
                {{ number_format($total_ot) }}
            </th>
            <th class="text-center bg-yura_dark">
                {{ number_format($total_pre_ot) }}
            </th>
            <th class="text-center bg-yura_dark">
                {{ number_format($total_restantes) }}
            </th>
        </tr>
    </table>
</div>

<input type="hidden" id="variedad_selected" value="{{ $variedad->id_variedad }}">
<style>
    .tr_fija_top_1 {
        position: sticky;
        top: 20px;
        z-index: 9;
    }
</style>
