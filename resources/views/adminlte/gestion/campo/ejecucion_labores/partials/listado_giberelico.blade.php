<div style="overflow-y: scroll; overflow-x: scroll; max-height: 450px" class="text-center">
    <div class="alert alert-info text-center mouse-hand" onclick="$('#table_labores').toggleClass('hidden')">
        <span class="badge">{{ count($ejecutados) }}</span> <strong>EJECUTADOS</strong> de
        <span class="badge">{{ count($ejecutados) + count($sin_ejecutar) }}</span> <strong>PROGRAMADOS</strong>
    </div>
    <table class="table-bordered table-striped hidden" style="width: 100%; border: 1px solid #9d9d9d" id="table_labores">
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
            </tr>
        </thead>
        <tbody>
            @php
                $total_camas = 0;
                $total_litros = 0;
            @endphp
            @foreach ($ejecutados as $pos => $labor)
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
                </tr>
            @endforeach
            @foreach ($sin_ejecutar as $pos => $labor)
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
</script>
