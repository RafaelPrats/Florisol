<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Siglas
                </th>
                <th class="text-center th_yura_green">
                    Nombre
                </th>
                <th class="text-center th_yura_green">
                    Longitud
                </th>
                <th class="text-center bg-yura_dark">
                    {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($fecha)))] }}<br>
                    <small>{{ convertDateToText($fecha) }}</small>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_pedidos = 0;
                $total_armados = 0;
            @endphp
            @foreach ($listado as $pos_i => $item)
                @php
                    $armados = $item['ramos_armados'];
                    $por_armar = $item['venta'] - $armados;
                    $total_pedidos += $item['venta'];
                    $total_armados += $item['ramos_armados'];
                @endphp
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->siglas }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->nombre }}
                        <input type="hidden" class="pos_combinaciones" value="{{ $pos_i }}">
                        <input type="hidden" id="pos_id_variedad_{{ $pos_i }}"
                            value="{{ $item['item']->id_variedad }}">
                        <input type="hidden" id="pos_longitud_{{ $pos_i }}"
                            value="{{ $item['item']->longitud }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->longitud }}<sup>cm</sup>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: white" colspan="2">
                        @if ($item['venta'] > 0)
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_dark" title="Tallos Pedidos">
                                    {{ number_format($item['venta']) }}
                                </button>
                                <button type="button"
                                    class="btn btn-xs btn-yura_{{ $por_armar > 0 ? 'danger' : 'default' }}"
                                    title="Por Armar"
                                    onclick="modal_combinacion('{{ $item['item']->id_variedad }}', '{{ $item['item']->longitud }}', '{{ $fecha }}')">
                                    {{ number_format($por_armar > 0 ? $por_armar : 0) }}
                                </button>
                                {{-- <button type="button" class="btn btn-xs btn-yura_info" title="Ramos Disponibles">
                                {{ number_format($por_armar > 0 ? $v['ramos_disponibles'] : 0) }}
                            </button> --}}
                            </div>
                        @endif
                    </th>
                </tr>
            @endforeach
        </tbody>
        @php
            $por_armar = $total_pedidos - $total_armados;
        @endphp
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="3">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_dark" title="Tallos Pedidos">
                        {{ number_format($total_pedidos) }}
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_{{ $por_armar > 0 ? 'danger' : 'default' }}"
                        title="Por Armar">
                        {{ number_format($por_armar > 0 ? $por_armar : 0) }}
                    </button>
                </div>
            </th>
        </tr>
    </table>
</div>

<legend style="font-size: 1.1em; margin-bottom: 5px" class="text-center">
    <b>RESUMEN FALTANTES</b>
</legend>
<div class="row">
    <div class="col-md-8" style="overflow-y: scroll; max-height: 500px">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_resumen_faltantes">
            <thead>
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green">
                        PLANTA
                    </th>
                    <th class="text-center th_yura_green">
                        VARIEDAD
                    </th>
                    <th class="text-center th_yura_green">
                        FAlTANTES
                    </th>
                    <th class="text-center th_yura_green">
                        INVENTARIO
                    </th>
                </tr>
            </thead>
            @php
                $total_faltantes = 0;
                $total_inventario = 0;
            @endphp
            <tbody>
                @foreach ($resumen_variedades as $r)
                    @php
                        $total_faltantes += $r['faltantes'];
                        $inventario = getTotalInventarioByVariedad($r['variedad']->id_variedad);
                        $total_inventario += $inventario;
                    @endphp
                    <tr onmouseover="$(this).addClass('bg-yura_dark')"
                        onmouseleave="$(this).removeClass('bg-yura_dark')">
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $r['variedad']->nombre_planta }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $r['variedad']->nombre_variedad }}
                        </th>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($r['faltantes']) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($inventario) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tr class="tr_fija_bottom_0">
                <th class="text-center th_yura_green" colspan="2">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_faltantes) }}
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_inventario) }}
                </th>
            </tr>
        </table>
    </div>
</div>

<script>
    estructura_tabla('table_listado');
    estructura_tabla('table_resumen_faltantes');
    //$('#table_listado_filter').addClass('hidden');

    function modal_combinacion(variedad, longitud, fecha) {
        datos = {
            variedad: variedad,
            longitud: longitud,
            fecha: fecha,
            fecha_trabajo: $('#fecha_filtro').val(),
        }
        get_jquery('{{ url('tallos_preproduccion/modal_combinacion') }}', datos, function(retorno) {
            modal_view('modal_modal_combinacion', retorno,
                '<i class="fa fa-fw fa-plus"></i> Tallos de la Receta',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        });
    }
</script>
