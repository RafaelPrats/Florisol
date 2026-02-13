<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_reporte">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Siglas
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Nombre
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        Longitud
                    </div>
                </th>
                @php
                    $totales_fecha = [];
                @endphp
                @foreach ($fechas as $pos_f => $f)
                    <th class="text-center bg-yura_dark">
                        <div style="width: 90px">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}<br>
                            {{ explode(' del ', convertDateToText($f))[0] }}

                            <button type="button" class="btn btn-xs btn-yura_default pull-left"
                                onclick="exportar_excel_fecha('{{ $f }}')"
                                title="Descargar excel de la fecha">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </th>
                    @php
                        $totales_fecha[] = 0;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">
                    <div style="width: 60px">
                        <button type="button" class="btn btn-yura_default" onclick="exportar_excel_total()"
                            title="Descargar reporte">
                            <i class="fa fa-fw fa-file-excel-o"></i> Total
                        </button>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->siglas }}
                        <input type="hidden" id="id_variedad_{{ $pos }}"
                            value="{{ $item['item']->id_variedad }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item['item']->longitud }}<sup>cm</sup>
                        <input type="hidden" id="longitud_{{ $pos }}" value="{{ $item['item']->longitud }}">
                    </th>
                    @php
                        $total_receta = 0;
                    @endphp
                    @foreach ($item['valores'] as $pos_f => $val)
                        @php
                            $totales_fecha[$pos_f] += $val;
                            $total_receta += $val;
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d;">
                            @if ($val > 0)
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-yura_default mouse-hand" style="color: black"
                                        onclick="modal_planificacion('{{ $pos }}', '{{ $fechas[$pos_f] }}')">
                                        {{ $val }}
                                    </button>
                                    @if ($item['bloqueado'][$pos_f])
                                        <button class="btn btn-xs btn-yura_danger mouse-hand">
                                            <i class="fa fa-fw fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $total_receta }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="3">
                TOTALES
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales_fecha as $val)
                @php
                    $total += $val;
                @endphp
                <th class="text-center bg-yura_dark">
                    {{ number_format($val) }}
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total) }}
            </th>
        </tr>
    </table>
</div>

<script>
    function modal_planificacion(pos, fecha) {
        datos = {
            variedad: $('#id_variedad_' + pos).val(),
            longitud: $('#longitud_' + pos).val(),
            fecha: fecha,
            pos: pos,
        }
        get_jquery('{{ url('planificacion/modal_planificacion') }}', datos, function(retorno) {
            modal_view('modal_modal_planificacion', retorno,
                '<i class="fa fa-fw fa-plus"></i> Armar ramos de la Receta',
                true, false, '{{ isPC() ? '98%' : '' }}',
                function() {});
        });
    }

    function exportar_excel_fecha(fecha) {
        $.LoadingOverlay('show');
        window.open('{{ url('planificacion/exportar_excel_fecha') }}?fecha=' + fecha, '_blank');
        $.LoadingOverlay('hide');
    }

    function exportar_excel_total() {
        $.LoadingOverlay('show');
        window.open('{{ url('planificacion/exportar_excel_total') }}?desde=' + $('#desde_filtro').val() +
            '&hasta=' + $('#hasta_filtro').val() +
            '&variedad=' + $('#variedad_filtro').val(), '_blank');
        $.LoadingOverlay('hide');
    }
</script>
