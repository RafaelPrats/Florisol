<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Fecha
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    TxR
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Longitud
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos Disponibles
                </th>
                <th class="text-center bg-yura_dark" style="width: 60px">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="modal_baja()">
                        <i class="fa fa-fw fa-plus"></i> Nueva Orden
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $tallos_pta = 0;
                    foreach ($item['variedades'] as $var) {
                        $tallos_pta += $var->disponibles;
                    }
                @endphp
                <tr style="background-color: #dddddd" class="mouse-hand"
                    onclick="$('.tr_planta_{{ $item['planta']->id_planta }}').toggleClass('hidden'); $('.tr_all_detApi_{{ $item['planta']->id_planta }}').addClass('hidden')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" colspan="2">
                        {{ $item['planta']->nombre }} <i class="fa fa-fw fa-caret-down"></i>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" colspan="2">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($tallos_pta) }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                    </th>
                </tr>
                @foreach ($item['variedades'] as $pos_v => $var)
                    <tr onmouseover="$(this).css('background-color', 'cyan')"
                        onmouseleave="$(this).css('background-color', '')"
                        class="tr_planta_{{ $item['planta']->id_planta }} hidden">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->fecha }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->nombre }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->tallos_x_ramo }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->longitud }}
                        </th>
                        <th style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center"
                                id="tallos_disponibles_{{ $var->id_inventario_recepcion }}"
                                value="{{ $var->disponibles }}" readonly>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                        </th>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function modal_baja() {
        datos = {
            bodega: $('#bodega_filtro').val()
        }
        get_jquery('{{ url('botar_inventario/modal_baja') }}', datos, function(retorno) {
            modal_view('modal_modal_baja', retorno,
                '<i class="fa fa-fw fa-plus"></i> Orden de salida',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        });
    }
</script>
