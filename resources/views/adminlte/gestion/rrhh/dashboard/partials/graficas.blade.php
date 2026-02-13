<div class="col-md-7" id="div_graficas">
    <div class="nav-tabs-custom" style="cursor: move;">
        <ul class="nav nav-pills nav-justified">
            <li class="active"><a href="#area-chart" class="border-radius_18" data-toggle="tab"
                    aria-expanded="false">% Ausentismo</a></li>
            <li class=""><a href="#ciclo-chart" class="border-radius_18" data-toggle="tab"
                    aria-expanded="false">% Rot. personal</a></li>
            <li class=""><a href="#cost-lab-pers-chart" class="border-radius_18"
                    data-toggle="tab" aria-expanded="true">% Cost. mano obra x lab.</a></li>
        </ul>
        <div class="tab-content no-padding">
            <div class="chart tab-pane active" id="area-chart" style="position: relative">
                <canvas id="chart_area" width="100%" height="40"
                    style="margin-top: 5px; background-color: white"></canvas>
            </div>
            <div class="chart tab-pane" id="ciclo-chart" style="position: relative">
                <canvas id="chart_ciclo" width="100%" height="40"
                    style="margin-top: 5px; background-color: white"></canvas>
            </div>
            <div class="chart tab-pane" id="cost-lab-pers-chart" style="position: relative">
                <canvas id="chart_cost-lab-pers-chart" width="100%" height="40"
                    style="margin-top: 5px; background-color: white"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="col-md-5" id="div_today">
    <table class="table box box-solid box-success" style="font-size:12px">
        <thead>
            <tr class="bg-success" style="">
                <td class="text-center" style="padding: 2px;font-size:14px;vertical-align:middle">Labor</td>
                <td class="text-center" style="padding: 2px;font-size:14px">% Ausentismo</td>
                <td class="text-center" style="padding: 2px;font-size:14px">% Rot. personal</td>
                <td class="text-center" style="padding: 2px;font-size:14px">% Cost. mano obra x lab.</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($manosObra as $mo)
                <tr>
                    <td style="vertical-align:middle" class="text-center">
                        {{$mo->labor}}
                    </td>
                    <td style="vertical-align:middle" class="text-center">
                        <span class="badge background-color_yura">{{isset($mo->ausentismo) ? number_format($mo->ausentismo,2) : '0.00'}}</span>
                    </td>
                    <td style="vertical-align:middle" class="text-center">
                        <span class="badge background-color_yura">{{isset($mo->rot_personal) ? number_format($mo->rot_personal,2) : '0.00'}}</span>
                    </td>
                    <td style="vertical-align:middle" class="text-center">
                        <span class="badge background-color_yura">{{isset($mo->costo_mano_obra_labor) ? number_format($mo->costo_mano_obra_labor,2) : '0.00'}}</span>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
<script>
    construir_char('Ausentismo', 'chart_area');

    construir_char('Rotación del personal', 'chart_ciclo');

    construir_char('Costo por labor por persona', 'chart_cost-lab-pers-chart');

    function construir_char(label, id) {

        labels = ['2124', '2125', '2126', '2127', '2128', '2129', '2130', '2131', '2132', '2133', '2134', '2135',
            '2136', '2137', '2138', '2139', '2140', '2141', '2142', '2143', '2144', '2145', '2146', '2147', '2148',
            '2149', '2150', '2151', '2152', '2201', '2202', '2203', '2204', '2205', '2206', '2207', '2208', '2209',
            '2210', '2211', '2212', '2213', '2214', '2215', '2216', '2217', '2218', '2219', '2220', '2221', '2222',
            '2223', '2224'
        ];
        data_list = ['748.9', '867.24', '868.82', '1069.24', '843.36', '880.19', '738.97', '865.39', '729.6', '615.69',
            '545.2', '620.05', '709.2', '1260.48', '985.72', '894.29', '1259.62', '2167.52', '1286.82', '1239.83',
            '966.24', '792.64', '781.74', '754.97', '792.17', '773.61', '814.54', '725.81', '592.53', '660.07',
            '904.96', '974.85', '788.58', '910.82', '815.44', '699.65', '458.6', '434.85', '728.25', '795.44',
            '756.15', '715.21', '746.53', '558.39', '792.03', '825.69', '0', '0', '0', '0', '0', '0', '0'
        ];

        datasets = [{
            label: label + ' ',
            data: data_list,
            borderColor: 'black',
            borderWidth: 2,
            fill: false,
        }];

        ctx = document.getElementById(id).getContext('2d');
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
                        tension: 0.3, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'x' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function() {},
                    onHover: function() {},
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round', // "butt" || "round" || "square"
            }
        });
    }
</script>