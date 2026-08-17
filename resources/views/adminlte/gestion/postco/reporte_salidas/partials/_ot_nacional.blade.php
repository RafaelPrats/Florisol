<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
    <tr class="tr_fija_top_0">
        <th class="padding_lateral_5 bg-yura_dark">
            OT
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha Salida
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Cliente
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Ramos
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha Inventario
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Planta
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Variedad
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Longitud
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            TxR
        </th>
        <th class="padding_lateral_5 bg-yura_dark" colspan="2">
            Tallos
        </th>
    </tr>
    @foreach ($listado_ot_nacional as $ot)
        @php
            $total_tallos_ot = 0;
            foreach ($ot['detalles'] as $pos_i => $item) {
                $total_tallos_ot += $item->cantidad;
            }
        @endphp
        @foreach ($ot['detalles'] as $pos_i => $item)
            <tr class="ot_{{ $ot['numero'] }}"
                onmouseover="$('.ot_{{ $ot['numero'] }}').css('background-color', 'cyan')"
                onmouseleave="$('.ot_{{ $ot['numero'] }}').css('background-color', '')">
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="exportar_ot_nacional('{{ $item->id_detalle_caja_proyecto }}')">
                            #{{ $ot['numero'] }}
                        </button>
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['fecha'] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['cli_nombre'] }}
                        <br>
                        <small><em>{{ $ot['bqt_nombre'] }}</em></small>
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $ot['ramos_x_caja'] * $ot['cajas'] }}
                    </th>
                @endif
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->fecha_inventario }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->bodega == 'V' ? 'Ventas' : 'Produccion' }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->pta_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->var_nombre }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->longitud }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->tallos_x_ramo }}
                </th>
                <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $item->cantidad }}
                </th>
                @if ($pos_i == 0)
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" rowspan="{{ count($ot['detalles']) }}">
                        {{ $total_tallos_ot }}
                    </th>
                @endif
            </tr>
        @endforeach
    @endforeach
</table>

<script>
    function exportar_ot_nacional(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('preproduccion/exportar_ot_nacional') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }
</script>
