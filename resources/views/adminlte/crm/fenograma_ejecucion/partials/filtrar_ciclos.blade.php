@if (count($ciclos) > 0)
    <div id="div_content_fixed">
        <table {{-- data-order='[[ 3, "desc" ]]' --}} class="table-striped table-bordered" width="100%"
            style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0;" id="table_fenograma_ejecucion">
            <thead>
                <tr style="color: white">
                    <th class="fila_fija1 columna_fija_left_0"
                        style="padding-left: 5px; border-radius: 18px 0 0 0; z-index: 20 !important;">
                        <div style="width: 150px">
                            Módulo
                        </div>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        <div style="width: 60px">Variedad</div>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        <div style="width: 60px">Tipo</div>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        <div style="width: 60px">Inicio</div>
                    </th>
                    @if ($estado == 0)
                        <th class="fila_fija1" style="padding-left: 5px">
                            <div style="width: 60px">Fin</div>
                        </th>
                    @endif
                    <th class="fila_fija1" style="width: 30px; padding-left: 5px">
                        Semana
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        P/S
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Días
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Área m<sup>2</sup>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Total x Semana m<sup>2</sup>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        1ra Flor
                    </th>
                    <th class="fila_fija1" style="background-color: #00B388; padding-left: 5px">
                        %<sup>M</sup>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Tallos Cosechados
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Real <br>
                        Tallos/m<sup>2</sup>
                    </th>
                    <th class="fila_fija1" style="padding-left: 5px">
                        Cosechado <sup>%</sup>
                    </th>
                    <th class="fila_fija1" style="background-color: #00B388; padding-left: 5px">
                        Proy <br>
                        Tallos/m<sup>2</sup>
                    </th>
                    <th class="fila_fija1" style="background-color: #00B388; padding-left: 5px">
                        Ptas Iniciales
                    </th>
                    <th class="fila_fija1" style="background-color: #00B388; padding-left: 5px">
                        Ptas Actuales
                    </th>
                    <th class="fila_fija1" style="background-color: #00B388; padding-left: 5px">
                        Dend P.Ini/m<sup>2</sup>
                    </th>
                    <th class="fila_fija1"
                        style="background-color: #00B388; padding-left: 5px; border-radius: 0 18px 0 0">
                        Conteo T/P
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_area = 0;
                    $ciclo = 0;
                    $total_tallos = 0;
                    $total_tallos_m2 = 0;
                    $positivos_tallos_m2 = 0;
                    $total_iniciales = 0;
                    $total_actuales = 0;
                    $total_mortalidad = [
                        'valor' => 0,
                        'positivos' => 0,
                    ];
                    $total_densidad = [
                        'valor' => 0,
                        'positivos' => 0,
                    ];
                    $total_tallos_m2_proy = [
                        'valor' => 0,
                        'positivos' => 0,
                    ];

                    $codigo_semana = $ciclos[0]->semana;
                    $area = 0;
                @endphp
                @foreach ($ciclos as $pos_item => $item)
                    @php
                        $semana = $item->semana;
                        $poda_siembra = $item->poda_siembra;
                        $tallos_cosechados = $item->tallos_cosechados;

                        $desecho = $item->desecho;

                        $conteo = $item->conteo;

                        $plantas_actuales = $item->plantas_actuales;
                        $getDensidadIniciales = $item->densidad_plantas_ini_m2;

                        $tallos_m2_cos = $item->real_tallos_m2;
                        $tallos_m2_proy = $item->proy_tallos_m2;

                    @endphp
                    <tr style="font-size: 0.8em; background-color: {{ $pos_item % 2 == 0 ? '#72ffe0' : 'white' }}">
                        <td style="border-color: #9d9d9d; padding-left: 5px; background-color: {{ $pos_item % 2 == 0 ? '#72ffe0' : 'white' }} !important;"
                            class="columna_fija_left_0">
                            {{ $item->sector_modulo }}:
                            <strong class="pull-right">{{ $item->nombre_modulo }}</strong>
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->pta_nombre }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->var_nombre }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->fecha_inicio_ciclo }}
                        </td>
                        @if ($estado == 0)
                            <td style="border-color: #9d9d9d; padding-left: 5px">
                                {{ $item->fecha_fin_ciclo }}
                            </td>
                        @endif
                        <td style="border-color: #9d9d9d;; padding-left: 5px">
                            {{ $item->semana }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->poda_siembra }}
                        </td>
                        <td style="border-color: #9d9d9d;; padding-left: 5px">
                            {{ difFechas($item->fecha_fin_ciclo, $item->fecha_inicio_ciclo)->days }}
                        </td>
                        <td style="border-color: #9d9d9d;; padding-left: 5px">
                            {{ number_format($item->area_m2, 2) }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            @php
                                if ($codigo_semana == $semana) {
                                    $area += $item->area_m2;
                                } else {
                                    $area = $item->area_m2;
                                    $codigo_semana = $semana;
                                }
                                if ($pos_item + 1 < count($ciclos)) {
                                    if ($ciclos[$pos_item + 1]->semana != $codigo_semana) {
                                        echo number_format($area, 2);
                                    }
                                } else {
                                    echo number_format($area, 2);
                                }
                            @endphp
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->primera_flor }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            @php
                                $mortalidad = $item->porciento_mortalidad;

                                $color = 'orange';
                                if ($mortalidad < 10) {
                                    $color = 'green';
                                }
                                if ($mortalidad > 20) {
                                    $color = 'red';
                                }
                            @endphp
                            <span style="color: {{ $color }}">{{ $mortalidad }}</span>
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ number_format($tallos_cosechados) }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $tallos_m2_cos }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $item->porciento_cosechado }}%
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            @php
                                $color = '#EF6E11';
                                if ($tallos_m2_proy < 35) {
                                    $color = '#D01C62';
                                }
                                if ($tallos_m2_proy > 45) {
                                    $color = '#00B388';
                                }
                            @endphp
                            <span style="color: {{ $color }}">{{ $tallos_m2_proy }}</span>
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ number_format($item->plantas_iniciales) }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ number_format($plantas_actuales) }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px">
                            {{ $getDensidadIniciales }}
                        </td>
                        <td style="border-color: #9d9d9d; padding-left: 5px"
                            title="{{ $item->conteo <= 0 ? 'semana' : '' }}">
                            {{ $conteo }}
                        </td>
                    </tr>
                    @php
                        $total_area += $item->area_m2;
                        $total_iniciales += $item->plantas_iniciales;
                        $total_actuales += $plantas_actuales;
                        if ($item->plantas_iniciales > 0 && $plantas_actuales > 0) {
                            $total_mortalidad['valor'] += $mortalidad;
                            $total_mortalidad['positivos']++;
                        }
                        if ($item->plantas_iniciales > 0 && $item->area_m2 > 0) {
                            $total_densidad['valor'] += $getDensidadIniciales;
                            $total_densidad['positivos']++;
                        }
                        if ($item->area_m2 > 0 && $tallos_m2_proy > 0) {
                            $total_tallos_m2_proy['valor'] += $tallos_m2_proy;
                            $total_tallos_m2_proy['positivos']++;
                        }
                        $ciclo += difFechas($item->fecha_fin_ciclo, $item->fecha_inicio_ciclo)->days;
                        $total_tallos += $tallos_cosechados;
                        $total_tallos_m2 += $tallos_m2_cos;
                        if ($tallos_cosechados > 0) {
                            $positivos_tallos_m2++;
                        }
                    @endphp
                @endforeach
            </tbody>
            <tr style="background-color: #00B388; color: white" class="tr_fija_bottom_0">
                <th style="border-color: #9d9d9d; padding: 0 5px 0 5px" class="columna_fija_left_0">
                    Totales
                    <span class="pull-right">{{ count($ciclos) }}
                        <small>resultados</small></span>
                </th>
                <th colspan="{{ $estado == 0 ? 6 : 5 }}" style="border-color: #9d9d9d;">
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                    {{ count($ciclos) > 0 ? round($ciclo / count($ciclos), 2) : 0 }}
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                    {{ number_format(round($total_area / 10000, 2), 2) }}
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    @if ($total_mortalidad['positivos'] > 0)
                        {{ round($total_mortalidad['valor'] / $total_mortalidad['positivos'], 2) }}
                    @endif
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                    {{ number_format($total_tallos, 2) }}
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                    @if ($positivos_tallos_m2 > 0)
                        {{ count($ciclos) > 0 ? round($total_tallos_m2 / $positivos_tallos_m2, 2) : 0 }}
                    @else
                        0
                    @endif
                </th>
                <th style="border-color: #9d9d9d; padding-left: 5px">
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    @if ($total_tallos_m2_proy['positivos'] > 0)
                        {{ round($total_tallos_m2_proy['valor'] / $total_tallos_m2_proy['positivos'], 2) }}
                    @endif
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    {{ number_format($total_iniciales) }}
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    {{ number_format($total_actuales) }}
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                    @if ($total_densidad['positivos'] > 0)
                        {{ round($total_densidad['valor'] / $total_densidad['positivos'], 2) }}
                    @endif
                </th>
                <th style="border-color: #9d9d9d; background-color: #00B388; padding-left: 5px">
                </th>
            </tr>
        </table>
    </div>

    <script>
        estructura_tabla('table_fenograma_ejecucion', false, false);
        $('#table_fenograma_ejecucion_filter label').addClass('text-color_yura');
        $('#table_fenograma_ejecucion_filter label input').addClass('input-yura_default');
    </script>

    <style>
        #div_content_fixed {
            overflow-x: scroll;
            overflow-y: scroll;
            width: 100%;
            max-height: 450px;
        }

        #table_fenograma_ejecucion {
            border-spacing: 0 !important;
            border: 1px solid #9d9d9d !important;
        }

        #table_fenograma_ejecucion th,
        #table_fenograma_ejecucion td {
            border-spacing: 0;
        }

        #table_fenograma_ejecucion thead .fila_fija1 {
            background-color: #00B388 !important;
            border: 1px solid white !important;
            z-index: 9;
            position: sticky;
            top: 0;
        }

        #table_fenograma_ejecucion thead .fila_fija2 {
            background-color: #0b3248 !important;
            border: 1px solid #9d9d9d !important;
            z-index: 9;
            position: sticky;
            top: 0;
        }

        #table_fenograma_ejecucion .columna_fija_left_0 {
            z-index: 10 !important;
            position: sticky;
            left: 0;
        }

        #table_fenograma_ejecucion tr.tr_fija_bottom_0 th {
            background-color: #00B388 !important;
            border: 1px solid white !important;
            color: white !important;
            position: sticky;
            bottom: 0;
        }
    </style>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif
