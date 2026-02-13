<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px">
    <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d" id="table_labores">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Var.
                </th>
                <th class="text-center th_yura_green">
                    Tipo
                </th>
                <th class="text-center th_yura_green">
                    Mód.
                </th>
                <th class="text-center th_yura_green">
                    P/S
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 120px">
                        Ini.
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    Días
                </th>
                <th class="text-center th_yura_green">
                    <div style="width: 80px"></div>
                </th>
                <th class="text-center th_yura_green">
                    Rep.
                </th>
                <th class="text-center th_yura_green">
                    Camas
                </th>
                <th class="text-center th_yura_green">
                    Litros x cama
                </th>
                @php
                    $totales_prod = [];
                    $totales_mo = [];
                @endphp
                @foreach ($productos as $p)
                    <th class="text-center bg-yura_dark th_detalles">
                        <div style="width: 120px">
                            {{ $p->nombre }}
                        </div>
                    </th>
                    @php
                        $totales_prod[] = 0;
                    @endphp
                @endforeach
                @foreach ($mano_obras as $mo)
                    <th class="text-center bg-yura_dark th_detalles">
                        <div style="width: 120px">
                            (MO)
                            {{ $mo->nombre }}
                        </div>
                    </th>
                    @php
                        $totales_mo[] = 0;
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">

                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_camas = 0;
                $total_litros = 0;
            @endphp
            @foreach ($listado as $pos => $labor)
                @php
                    $ciclo = $labor->ciclo;
                    $modulo = $ciclo->modulo;
                    $dias_ciclo = difFechas($labor->fecha, $ciclo->fecha_inicio)->days;
                    $repeticion = $labor->repeticion;
                    $fecha = $labor->fecha;
                    $camas = $labor->camas;
                    $litro_x_cama = $labor->litro_x_cama;
                    $total_camas += $camas;
                    $total_litros += $litro_x_cama;
                    $variedad = $ciclo->variedad;
                    $bg_color = $pos % 2 == 0 ? '#e9ecef' : '';
                    $bg_color = $labor->ejecutado == 1 ? '#b7fbaf' : $bg_color;
                @endphp
                <tr style="background-color: {{ $bg_color }}">
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $variedad->planta->siglas }}
                    </td>
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $variedad->nombre }}
                    </td>
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        <a href="javascript:void(0)"
                            onclick="ver_labores_by_ciclo('{{ $ciclo->id_ciclo }}', '{{ $labor->id_aplicacion }}')">
                            {{ $modulo->nombre }}
                        </a>
                    </td>
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $modulo->getPodaSiembraByCiclo($ciclo->id_ciclo) }}
                    </td>
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ convertDateToText($ciclo->fecha_inicio) }}
                    </td>
                    <td class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $dias_ciclo }}
                    </td>
                    <th class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $fecha }}
                    </th>
                    <th class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $repeticion }}
                    </th>
                    <th class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $camas }}
                    </th>
                    <th class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        {{ $litro_x_cama }}
                    </th>
                    @foreach ($productos as $pos_p => $p)
                        @php
                            $detalle = $labor->getDetalleByProducto($p->id_producto);
                            $totales_prod[$pos_p] += $detalle != '' ? $detalle->dosis : 0;
                        @endphp
                        <td class="text-center th_detalles bg_color_{{ $labor->id_aplicacion_campo }}"
                            style="border-color: #9d9d9d">
                            {{ $detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' ' . $detalle->unidad_medida->siglas : '' }}
                        </td>
                    @endforeach
                    @foreach ($mano_obras as $pos_p => $mo)
                        @php
                            $detalle = $labor->getDetalleByManoObra($mo->id_mano_obra);
                            $totales_mo[$pos_p] += $detalle != '' ? $detalle->dosis : 0;
                        @endphp
                        <td class="text-center th_detalles bg_color_{{ $labor->id_aplicacion_campo }}"
                            style="border-color: #9d9d9d">
                            {{ $detalle != '' && $detalle->id_unidad_medida != '' ? $detalle->dosis . ' ' . $detalle->unidad_medida->siglas : '' }}
                        </td>
                    @endforeach
                    <th class="text-center bg_color_{{ $labor->id_aplicacion_campo }}" style="border-color: #9d9d9d;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Ejecutar"
                                onclick="ejecutar_labor_campo('{{ $labor->id_aplicacion_campo }}')">
                                <i class="fa fa-fw fa-check"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th class="th_yura_green" colspan="8" style="padding-left: 10px">
                    TOTALES
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_camas, 2) }}
                </th>
                <th class="text-center th_yura_green">
                    {{ number_format($total_litros, 2) }}
                </th>
                @foreach ($totales_prod as $p)
                    <td class="text-center bg-yura_dark">
                        {{ number_format($p, 2) }}
                    </td>
                @endforeach
                @foreach ($totales_mo as $mo)
                    <td class="text-center bg-yura_dark">
                        {{ number_format($mo, 2) }}
                    </td>
                @endforeach
                <th class="text-center th_yura_green">
                </th>
            </tr>
        </tfoot>
    </table>
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>

<script>
    function ver_labores_by_ciclo(ciclo, aplicacion) {
        datos = {
            ciclo: ciclo,
            aplicacion: aplicacion,
        };
        get_jquery('{{ url('ingreso_labores/ver_labores_by_ciclo') }}', datos, function(retorno) {
            modal_view('modal-view_ver_labores_by_ciclo', retorno,
                '<i class="fa fa-fw fa-eye"></i> Labores del ciclo', true, false, '80%');
        });
    }

    function ejecutar_labor_campo(aplicacion) {
        datos = {
            _token: '{{ csrf_token() }}',
            aplicacion: aplicacion,
        };
        post_jquery_m('{{ url('reporte_labores/ejecutar_labor_campo') }}', datos, function(retorno) {
            $('.bg_color_' + aplicacion).css('background-color', '#b7fbaf');
        });
    }
</script>
