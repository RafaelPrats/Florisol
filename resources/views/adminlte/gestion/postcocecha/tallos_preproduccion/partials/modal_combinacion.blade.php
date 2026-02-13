<legend style="font-size: 1em; margin-bottom: 2px" class="text-center">
    Pedidos de la RECETA "<b>{{ $variedad->nombre }}</b>" de "<b>{{ $longitud }}<sup>cm</sup></b>", del dia
    "<b>{{ convertDateToText($fecha) }}</b>"
    <button type="button" class="btn btn-xs btn-yura_primary"
        onclick="exportar_receta('{{ $variedad->id_variedad }}', '{{ $longitud }}', '{{ $fecha }}')">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>
<input type="hidden" id="id_receta_armar" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="longitud_receta_armar" value="{{ $longitud }}">
<input type="hidden" id="fecha_receta_armar" value="{{ $fecha }}">

<div style="overflow-y: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" rowspan="2">
                PEDIDO
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                CLIENTE
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                RAMOS
            </th>
            <th class="text-center th_yura_green" colspan="4">
                DISTRIBUCION RECETA
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                TALLOS
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                ARMADOS
            </th>
            <th class="text-center th_yura_green" rowspan="2">
                FALTANTES
            </th>
        </tr>
        <tr class="tr_fija_top_1" style="position: sticky; top: 21px; z-index: 9">
            <th class="text-center bg-yura_dark">
                PLANTA
            </th>
            <th class="text-center bg-yura_dark">
                VARIEDAD
            </th>
            <th class="text-center bg-yura_dark">
                UNIDADES
            </th>
            <th class="text-center bg-yura_dark" style="width: 40px">
                TxR
            </th>
        </tr>
        @php
            $total_ramos = 0;
            $total_ramos_armados = 0;
            $total_tallos = 0;
            $resumen_variedades = [];
        @endphp
        @foreach ($listado as $pos_p => $item)
            @php
                $total_ramos += $item['ramos_venta'];
                $total_ramos_armados += $item['ramos_armados_orden'] + $item['pedido']->ramos_armados;

                $tallos_x_ramo = 0;
                foreach ($item['distribucion'] as $pos_d => $dist) {
                    $tallos_x_ramo += $dist->unidades;
                }
            @endphp
            @foreach ($item['distribucion'] as $pos_d => $dist)
                @php
                    $total_tallos += $dist->unidades * $item['ramos_venta'];
                @endphp
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            #{{ $item['pedido']->codigo }}
                            :<em>{{ $item['pedido']->codigo_ref }}</em>
                            <input type="hidden" class="id_pedido_armar"
                                value="{{ $item['pedido']->id_detalle_import_pedido }}">
                            <input type="hidden" class="ids_detalle_pedido_armar"
                                id="id_detalle_pedido_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                value="{{ $item['pedido']->id_detalle_import_pedido }}">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $item['pedido']->nombre_cliente }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $item['ramos_venta'] }}
                            <input type="hidden" id="ramos_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                                value="{{ $item['ramos_venta'] }}">
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->nombre_planta }}
                        <input type="hidden" class="id_item_armar_{{ $item['pedido']->id_detalle_import_pedido }}"
                            value="{{ $dist->id_variedad }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->nombre_variedad }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->unidades }}
                        <input type="hidden"
                            id="id_unidades_armar_{{ $dist->id_variedad }}_{{ $item['pedido']->id_detalle_import_pedido }}"
                            value="{{ $dist->unidades }}">
                    </th>
                    @if ($pos_d == 0)
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            {{ $tallos_x_ramo }}
                        </th>
                    @endif
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $dist->unidades * $item['ramos_venta'] }}
                    </th>
                    @if ($pos_d == 0)
                        @php
                            $armados = $item['ramos_armados_orden'] + $item['pedido']->ramos_armados;
                            $faltantes = $item['ramos_venta'] - $armados;
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d"
                            rowspan="{{ count($item['distribucion']) }}">
                            @if ($item['ramos_armados_orden'] + $item['pedido']->ramos_armados > 0)
                                {{ $item['ramos_armados_orden'] + $item['pedido']->ramos_armados }}
                            @endif
                        </th>
                    @endif
                    @if ($faltantes > 0)
                        <th class="text-center" style="border-color: #ffffff; background-color: #ff7b7b">
                            {{ $faltantes * $dist->unidades }}
                        </th>
                        @php
                            $pos_en_resumen = -1;
                            foreach ($resumen_variedades as $pos => $r) {
                                if ($r['variedad']->id_variedad == $dist->id_variedad) {
                                    $pos_en_resumen = $pos;
                                }
                            }
                            if ($pos_en_resumen != -1) {
                                $resumen_variedades[$pos_en_resumen]['faltantes'] += $faltantes * $dist->unidades;
                            } else {
                                $resumen_variedades[] = [
                                    'variedad' => $dist,
                                    'faltantes' => $faltantes * $dist->unidades,
                                ];
                            }
                        @endphp
                    @endif
                </tr>
            @endforeach
        @endforeach
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="2">
                TOTALES
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_ramos) }}
            </th>
            <th class="text-center th_yura_green" colspan="4">
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_tallos) }}
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_ramos_armados) }}
            </th>
            <th class="text-center th_yura_green">
            </th>
        </tr>
    </table>
</div>

<div class="row">
    <div class="col-md-7 col-md-offset-5">
        <legend class="text-center" style="font-size: 1em; margin-bottom: 1px">
            <b>RESUMEN</b>
        </legend>
        <div style="overflow-y: scroll; max-height: 250px; margin-top: 5px;">
            <table class="table-bordered pull-right" style="width: 100%; border: 1px solid #9d9d9d"
                id="table_resumen_variedades">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="text-center th_yura_green">
                            Planta
                        </th>
                        <th class="text-center th_yura_green">
                            Variedad
                        </th>
                        <th class="text-center th_yura_green">
                            Faltantes
                        </th>
                        <th class="text-center th_yura_green">
                            Inventario
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
</div>
