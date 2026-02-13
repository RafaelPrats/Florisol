@if (count($listado) > 0)
    <div class="nav-tabs-custom">
        <!-- Tabs within a box -->
        <ul class="nav nav-pills nav-justified">
            <li class="active">
                <a href="#tabla-nav" data-toggle="tab" aria-expanded="true">
                    <i class="fa fa-fw fa-table"></i> Tabla
                </a>
            </li>
            <li>
                <a href="#tallos-chart" data-toggle="tab" aria-expanded="true">
                    <i class="fa fa-fw fa-line-chart"></i> Tallos
                </a>
            </li>
            <li class="">
                <a href="#porcentaje_nacional-chart" data-toggle="tab" aria-expanded="false">
                    <i class="fa fa-fw fa-pie-chart"></i> Porcentaje Nacional
                </a>
            </li>
        </ul>
        <div class="tab-content no-padding">
            <div class="chart tab-pane active" id="tabla-nav" style="position: relative">
                <div style="max-height: 700px; overflow-x: scroll; overflow-y: scroll">
                    <table class="table-bordered"
                        style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_listado">
                        <thead>
                            <tr>
                                <th class="text-left th_yura_green fila_fija1 columna_fija_left_0"
                                    style="border-radius: 18px 0 0 0; padding-left: 5px; z-index: 10 !important">
                                    <div style="width: 180px">
                                        Motivo
                                        <input type="checkbox" class="pull-right"
                                            onchange="$(this).prop('checked', false); $('.check_listado').prop('checked', false); generar_canvas(); generar_grafica_tallos(); generar_grafica_porcentaje_nacional()">
                                    </div>
                                </th>
                                @php
                                    $total_fechas = [];
                                @endphp
                                @foreach ($fechas as $f)
                                    <th class="text-center bg-yura_dark fila_fija1" style="color: white; padding: 5px;">
                                        <div style="width: 80px">
                                            {{ $f }}
                                        </div>
                                    </th>
                                    @php
                                        array_push($total_fechas, 0);
                                    @endphp
                                @endforeach
                                <th class="text-center th_yura_green fila_fija1 columna_fija_right_0">
                                    <div style="width: 60px">
                                        Total Nacional
                                    </div>
                                </th>
                                <th class="text-center th_yura_green fila_fija1">
                                    <div style="width: 60px">
                                        100 %
                                    </div>
                                </th>
                                <th class="padding_lateral_5 th_yura_green"
                                    style="width: 60px; border-radius: 0 18px 0 0">
                                    % Nacional
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_procentaje = 0;
                                $porcentaje_nacional = porcentaje($total_nacional, $total_cosecha, 1);
                            @endphp
                            @foreach ($listado as $pos_i => $item)
                                <tr onmouseover="$(this).addClass('bg-yura_dark')"
                                    onmouseleave="$(this).removeClass('bg-yura_dark')">
                                    <th class="padding_lateral_5 columna_fija_left_0 bg-yura_dark"
                                        style="border-color: #9d9d9d">
                                        <a href="javascript:void(0)" style="color: white"
                                            onmouseover="$(this).css('text-decoration', 'underline')"
                                            onmouseleave="$(this).css('text-decoration', 'none')"
                                            onclick="seleccionar_motivo('{{ $item['motivo']->id_motivos_nacional }}')">
                                            {{ $item['motivo']->nombre }}
                                        </a>
                                        <input type="checkbox" class="pull-right check_listado"
                                            id="check_mot_{{ $pos_i }}"
                                            onchange="seleccionar_check('{{ $pos_i }}'); generar_canvas(); generar_grafica_tallos(); generar_grafica_porcentaje_nacional()"
                                            {{ $pos_i < 10 ? 'checked' : '' }}>
                                    </th>
                                    @php
                                        $total_nacional_mot = 0;
                                    @endphp
                                    @foreach ($item['valores'] as $pos_f => $val)
                                        <th class="text-center" style="border-color: #9d9d9d">
                                            {{ number_format($val) }}
                                        </th>
                                        @php
                                            $total_nacional_mot += $val;
                                            $total_fechas[$pos_f] += $val;
                                        @endphp
                                    @endforeach
                                    @php
                                        $porcentaje_mot =
                                            $total_nacional > 0
                                                ? round(
                                                    ($total_nacional_mot * $porcentaje_nacional) / $total_nacional,
                                                    2,
                                                )
                                                : 0;
                                        $total_procentaje += $porcentaje_mot;
                                        $listado[$pos_i]['porcentaje'] = porcentaje(
                                            $total_nacional_mot,
                                            $total_nacional,
                                            1,
                                        );
                                        $listado[$pos_i]['porcentaje_nacional'] = $porcentaje_mot;
                                    @endphp
                                    <th class="text-center columna_fija_right_0 bg-yura_dark"
                                        style="border-color: #9d9d9d">
                                        {{ number_format($total_nacional_mot) }}
                                    </th>
                                    <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                                        {{ porcentaje($total_nacional_mot, $total_nacional, 1) }}%
                                    </th>
                                    <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                                        {{ $porcentaje_mot }}%
                                    </th>
                                </tr>
                            @endforeach
                        </tbody>
                        <tr class="tr_fijo_bottom_0">
                            <th class="padding_lateral_5 th_yura_green columna_fija_left_0">
                                TOTALES
                            </th>
                            @foreach ($total_fechas as $pos_f => $val)
                                <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                                    {{ number_format($val) }}
                                </th>
                            @endforeach
                            <th class="text-center th_yura_green columna_fija_right_0" style="border-color: #9d9d9d">
                                {{ number_format($total_nacional) }}
                            </th>
                            <th class="text-center th_yura_green" style="border-color: #9d9d9d">
                                100%
                            </th>
                            <th class="text-center th_yura_green" style="border-color: #9d9d9d">
                                {{ number_format($total_procentaje, 2) }}%
                            </th>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="chart tab-pane" id="tallos-chart" style="position: relative">
                <canvas id="chart_tallos_0" width="100%" height="40" style="margin-top: 5px"></canvas>
            </div>
            <div class="chart tab-pane" id="porcentaje_nacional-chart" style="position: relative">
                <canvas id="chart_porcentaje_nacional_0" width="100%" height="40" style="margin-top: 5px"></canvas>
            </div>
        </div>
    </div>

    <style>
        #table_listado thead .fila_fija1 {
            z-index: 8;
            position: sticky;
            top: 0;
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
        generar_grafica_tallos();
        generar_grafica_porcentaje_nacional();

        function generar_canvas() {
            num_grafica++;
            $('#tallos-chart').html('');
            $('#tallos-chart').html(
                '<canvas id="chart_tallos_' + num_grafica +
                '" width="100%" height="40" style="margin-top: 5px"></canvas>');

            $('#porcentaje_nacional-chart').html('');
            $('#porcentaje_nacional-chart').html(
                '<canvas id="chart_porcentaje_nacional_' + num_grafica +
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
                    '<div class="alert alert-warning text-center"><h3>Seleccione hasta un <b>MAXIMO de 10 MOTIVOS</b></h3></div>'
                );
                $('#check_mot_' + pos_c).prop('checked', false);
            }
        }

        function generar_grafica_tallos() {
            labels = [];
            datasets = [];
            @foreach ($fechas as $f)
                labels.push("{{ $f }}");
            @endforeach

            {{-- Data_list --}}
            pos = 0;
            pos_color = 0;
            @foreach ($listado as $pos => $item)
                if ($('#check_mot_' + pos).prop('checked') == true) {
                    data_list = [];
                    @foreach ($item['valores'] as $val)
                        data_list.push("{{ $val }}");
                    @endforeach

                    datasets.push({
                        label: '{{ $item['motivo']->nombre }}' + ': ' + '{{ $item['porcentaje_nacional'] }}%',
                        data: data_list,
                        backgroundColor: getListColores()[pos_color],
                        borderColor: getListColores()[pos_color],
                        borderWidth: 2,
                        fill: false,
                    });
                    pos_color++;
                }
                pos++;
            @endforeach

            ctx = document.getElementById("chart_tallos_" + num_grafica).getContext('2d');
            myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: false
                            }
                        }]
                    },
                    elements: {
                        line: {
                            tension: 0.2, // disables bezier curves
                        }
                    },
                    tooltips: {
                        mode: 'point' // nearest, point, index, dataset, x, y
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        fullWidth: false,
                        /*onClick: function() {},
                        onHover: function() {},*/
                        reverse: true,
                    },
                    showLines: true, // for all datasets
                    borderCapStyle: 'round', // "butt" || "round" || "square"
                }
            });
        }

        function generar_grafica_porcentaje_nacional() {
            labels = [];
            data_list = [];
            colores = [];

            {{-- Data_list --}}
            pos = 0;
            pos_color = 0;
            @foreach ($listado as $pos => $item)
                if ($('#check_mot_' + pos).prop('checked') == true) {
                    labels.push("{{ $item['motivo']->nombre }}: {{ $item['porcentaje_nacional'] }}%");
                    data_list.push("{{ $item['porcentaje_nacional'] }}");
                    colores.push(getListColores()[pos_color]);
                    pos_color++;
                }
                pos++;
            @endforeach

            datasets = [{
                data: data_list,
                backgroundColor: colores,
                borderColor: 'black',
                borderWidth: 1,
            }];

            ctx2 = document.getElementById("chart_porcentaje_nacional_" + num_grafica).getContext('2d');
            myChart2 = new Chart(ctx2, {
                type: 'polarArea',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: false
                            }
                        }]
                    },
                    elements: {
                        line: {
                            tension: 0.2, // disables bezier curves
                        }
                    },
                    tooltips: {
                        mode: 'point' // nearest, point, index, dataset, x, y
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        fullWidth: false,
                        /*onClick: function() {},
                        onHover: function() {},*/
                        reverse: true,
                    },
                    showLines: true, // for all datasets
                    borderCapStyle: 'round', // "butt" || "round" || "square"
                }
            });
        }
    </script>
@else
    <div class="text-center alert alert-info">
        <h3>No se han encontrado resultados</h3>
    </div>
@endif
