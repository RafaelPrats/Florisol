@if (count($listado) > 0)
    <div style="max-height: 700px; overflow-x: scroll; overflow-y: scroll">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d;" id="table_listado">
            <thead>
                <tr>
                    <th class="text-left th_yura_green fila_fija1" rowspan="2" style="padding-left: 5px;">
                        <div style="width: 180px">
                            Planta
                        </div>
                    </th>
                    <th class="text-left th_yura_green fila_fija1 columna_fija_left_0" rowspan="2"
                        style="padding-left: 5px; z-index: 10 !important">
                        <div style="width: 180px">
                            Variedad
                        </div>
                    </th>
                    @php
                        $total_fechas = [];
                    @endphp
                    @foreach ($fechas as $f)
                        <th class="text-center th_yura_green fila_fija1" colspan="3">
                            {{ $f }}
                        </th>
                        @php
                            $total_fechas[] = [
                                'produccion' => 0,
                                'nacional' => 0,
                            ];
                        @endphp
                    @endforeach
                    <th class="text-center th_yura_green fila_fija1" rowspan="2">
                        <div style="width: 60px">
                            Total Produccion
                        </div>
                    </th>
                    <th class="text-center th_yura_green fila_fija1" rowspan="2">
                        <div style="width: 60px">
                            Total Nacional
                        </div>
                    </th>
                    <th class="text-center th_yura_green fila_fija1 columna_fija_right_0" rowspan="2"
                        style="z-index: 10 !important">
                        <div style="width: 60px;">
                            Total Porcentaje
                        </div>
                    </th>
                </tr>
                <tr class="tr_fija_top_1">
                    @foreach ($fechas as $f)
                        <th class="text-center bg-yura_dark">
                            <div style="width: 60px">
                                Prod.
                            </div>
                        </th>
                        <th class="text-center bg-yura_dark">
                            <div style="width: 60px">
                                Nac.
                            </div>
                        </th>
                        <th class="text-center bg-yura_dark">
                            <div style="width: 60px">
                                %
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($listado as $pos_i => $item)
                    <tr onmouseover="$(this).css('background-color', 'cyan');"
                        onmouseleave="$(this).css('background-color', '');">
                        <th class="padding_lateral_5 bg-yura_dark">
                            {{ $item['label']->pta_nombre }}
                        </th>
                        <th class="padding_lateral_5 columna_fija_left_0 bg-yura_dark">
                            {{ $item['label']->var_nombre }}
                        </th>
                        @php
                            $total_produccion_row = 0;
                            $total_nacional_row = 0;
                        @endphp
                        @foreach ($fechas as $pos_f => $fecha)
                            @php
                                $produccion = 0;
                                $nacional = 0;
                                foreach ($item['valores'] as $val) {
                                    if ($fecha == $val->fecha) {
                                        $produccion = $val->produccion;
                                        $nacional = $val->nacional;
                                        $total_produccion_row += $produccion;
                                        $total_nacional_row += $nacional;
                                    }
                                }
                                $total_fechas[$pos_f]['produccion'] += $produccion;
                                $total_fechas[$pos_f]['nacional'] += $nacional;
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $produccion }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $nacional }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ porcentaje($nacional, $produccion, 1) }}%
                            </th>
                        @endforeach
                        <th class="text-center bg-yura_dark">
                            {{ $total_produccion_row }}
                        </th>
                        <th class="text-center bg-yura_dark">
                            {{ $total_nacional_row }}
                        </th>
                        <th class="text-center columna_fija_right_0 bg-yura_dark">
                            {{ porcentaje($total_nacional_row, $total_produccion_row, 1) }}%
                        </th>
                    </tr>
                @endforeach
            </tbody>
            <tr class="tr_fijo_bottom_0">
                <th class="padding_lateral_5 th_yura_green">
                </th>
                <th class="padding_lateral_5 th_yura_green columna_fija_left_0">
                    TOTALES
                </th>
                @php
                    $total_produccion = 0;
                    $total_nacional = 0;
                @endphp
                @foreach ($total_fechas as $pos_f => $val)
                    <th class="text-center bg-yura_dark">
                        {{ $val['produccion'] }}
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ $val['nacional'] }}
                    </th>
                    <th class="text-center bg-yura_dark">
                        {{ porcentaje($val['nacional'], $val['produccion'], 1) }}%
                    </th>
                    @php
                        $total_produccion += $val['produccion'];
                        $total_nacional += $val['nacional'];
                    @endphp
                @endforeach
                <th class="text-center th_yura_green">
                    {{ $total_produccion }}
                </th>
                <th class="text-center th_yura_green">
                    {{ $total_nacional }}
                </th>
                <th class="text-center th_yura_green columna_fija_right_0">
                    {{ porcentaje($total_nacional, $total_produccion, 1) }}%
                </th>
            </tr>
        </table>
    </div>

    <style>
        #table_listado thead .fila_fija1 {
            z-index: 8;
            position: sticky;
            top: 0;
        }

        .tr_fija_top_1 {
            position: sticky;
            top: 22px;
            z-index: 7;
        }

        #table_listado tr#tr_fijo_bottom_0 th {
            z-index: 8;
            position: sticky;
            bottom: 0;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }

        .columna_fija_right_0 {
            position: sticky;
            right: 0;
            z-index: 9;
        }
    </style>

    <script>
        estructura_tabla('table_listado');
        $('#table_listado_filter>label>input').addClass('input-yura_default');
        //generar_grafica_tallos();

        function generar_canvas() {
            num_grafica++;
            $('#tallos-chart').html('');
            $('#tallos-chart').html(
                '<canvas id="chart_tallos_' + num_grafica +
                '" width="100%" height="40" style="margin-top: 5px"></canvas>');

            $('#porcentaje_reclamo-chart').html('');
            $('#porcentaje_reclamo-chart').html(
                '<canvas id="chart_porcentaje_reclamo_' + num_grafica +
                '" width="100%" height="40" style="margin-top: 5px"></canvas>');
        }

        function seleccionar_check(pos_c) {
            check_listado = $('.check_listado');
            activados = 0;
            for (i = 0; i < check_listado.length; i++) {
                id = check_listado[i].id;
                if ($('#' + id).prop('checked') == true)
                    activados++;
            }
            if (activados > 10) {
                alerta(
                    '<div class="alert alert-warning text-center"><h3>Seleccione hasta un <b>MAXIMO de 10 ITEMS</b></h3></div>'
                );
                $('#check_label_' + pos_c).prop('checked', false);
            }
        }
    </script>
@else
    <div class="text-center alert alert-info">
        <h3>No se han encontrado resultados</h3>
    </div>
@endif
