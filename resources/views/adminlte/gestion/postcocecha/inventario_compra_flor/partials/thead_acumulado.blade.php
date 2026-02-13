<tr class="tr_fija_top_0">
    <th class="text-center th_yura_green padding_lateral_5 columna_fija_left_0" rowspan="2"
        style="z-index: 9 !important">
        <div style="width: 150px">
            Planta
        </div>
        <input type="hidden" id="input_tipo_acumulado" value="{{ $tipo }}">
        <input type="hidden" id="input_negativas_acumulado" value="{{ $negativas }}">
    </th>
    <th class="text-center th_yura_green padding_lateral_5 columna_fija_left_1" rowspan="2"
        style="z-index: 9 !important">
        <div style="width: 200px">
            Variedad
        </div>
    </th>
    <th class="text-center th_yura_green" colspan="{{ count($fechas_compra_flor) + count($fechas_recepcion) }}">
        Dias
    </th>
    <th class="text-center th_yura_green padding_lateral_5" colspan="2">
        Inventario
    </th>
    @if (count($fechas_ventas) > 1)
        @foreach ($fechas_ventas as $f)
            <th class="text-center bg-yura_dark padding_lateral_5">
                <div style="width: 120px">
                    Ventas
                </div>
            </th>
            <th class="text-center bg-yura_dark padding_lateral_5">
                Armados
            </th>
            <th class="text-center bg-yura_dark padding_lateral_5" style="border-right-width: 2px">
                Invent.
            </th>
        @endforeach
    @endif
    <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
        Ventas
    </th>
    <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
        <div style="width: 100px">
            Armados
        </div>
    </th>
    <th class="text-center th_yura_green padding_lateral_5" rowspan="2">
        Necesidad
        <br>
        <div class="btn-group">
            <button type="button" class="btn btn-xs btn-yura_default" title="Exportar todo el reporte"
                onclick="exportar_listado_compra_flor_acumulado('{{ $tipo }}', '{{ $negativas }}')">
                <i class="fa fa-fw fa-file-excel-o"></i>
            </button>
            <button type="button" class="btn btn-xs btn-yura_dark" title="Exportar solo Compras"
                onclick="exportar_archivo_compras()">
                <i class="fa fa-fw fa-file-excel-o"></i>
            </button>
        </div>
    </th>
</tr>
<tr class="tr_fija_top_1">
    @foreach ($fechas_compra_flor as $pos_f => $f)
        <th class="text-center padding_lateral_5 bg-yura_dark" title="{{ convertDateToText($f) }}">
            <div style="width: 60px">
                @if ($pos_f == 0)
                    ...
                @endif
                -{{ difFechas(hoy(), $f)->d }}
            </div>
        </th>
    @endforeach
    @foreach ($fechas_recepcion as $pos_f => $f)
        <th class="text-center padding_lateral_5 bg-yura_dark" title="{{ convertDateToText($f) }}">
            <div style="width: 60px">
                {{ difFechas(hoy(), $f)->d }}
                @if ($pos_f == count($fechas_recepcion) - 1)
                    ...
                @endif
            </div>
        </th>
    @endforeach
    <th class="text-center padding_lateral_5 bg-yura_dark">
        Compra
    </th>
    <th class="text-center padding_lateral_5 bg-yura_dark">
        Recepcion
    </th>
    @if (count($fechas_ventas) > 1)
        @foreach ($fechas_ventas as $pos_f => $f)
            <th class="text-center bg-yura_dark padding_lateral_5" colspan="3" style="border-right-width: 2px"
                id="th_fecha_venta_{{ $pos_f }}" data-fecha="{{ $f }}">
                {{ convertDateToText($f) }}

                <button type="button" class="btn btn-xs btn-yura_warning" title="Actualizar datos del dia"
                    onclick="refrescar_all_ventas('{{ $pos_f }}')">
                    <i class="fa fa-fw fa-refresh"></i>
                </button>
            </th>
        @endforeach
    @endif
</tr>

