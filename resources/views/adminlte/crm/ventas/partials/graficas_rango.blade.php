<div class="nav-tabs-custom" style="cursor: move;">
    <!-- Tabs within a box -->
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#armados-chart" data-toggle="tab" aria-expanded="false">
                Armados
            </a>
        </li>
        <li class="">
            <a href="#comprados-chart" data-toggle="tab" aria-expanded="true">
                Comprados
            </a>
        </li>
        <li class="">
            <a href="#desechados-chart" data-toggle="tab" aria-expanded="true">
                Desechados
            </a>
        </li>
        <li class="">
            <a href="#recibidos-chart" data-toggle="tab" aria-expanded="true">
                Recibidos
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="armados-chart" style="position: relative">
            <canvas id="chart_armados" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="comprados-chart" style="position: relative">
            <canvas id="chart_comprados" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="desechados-chart" style="position: relative">
            <canvas id="chart_desechados" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane" id="recibidos-chart" style="position: relative">
            <canvas id="chart_recibidos" width="100%" height="40" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    construir_char('Armados', 'chart_armados');
    construir_char('Comprados', 'chart_comprados');
    construir_char('Desechados', 'chart_desechados');
    construir_char('Recibidos', 'chart_recibidos');

    function construir_char(label, id) {
        labels = [];
        datasets = [];
        data_list = [];
        data_tallos = [];
        @for ($i = 0; $i < count($labels); $i++)
            labels.push("{{ $labels[$i] }}");
            if (label == 'Armados')
                data_list.push("{{ $data[$i]->armados }}");
            if (label == 'Comprados')
                data_list.push("{{ $data[$i]->comprados }}");
            if (label == 'Desechados')
                data_list.push("{{ $data[$i]->desechados }}");
            if (label == 'Recibidos')
                data_list.push("{{ $data[$i]->recibidos }}");
        @endfor

        datasets = [{
            label: label + ' ',
            data: data_list,
            //backgroundColor: '#8c99ff54',
            borderColor: 'black',
            borderWidth: 1,
            fill: {{ $fill_grafica }},
        }];

        ctx = document.getElementById(id).getContext('2d');
        myChart = new Chart(ctx, {
            type: '{{ $tipo_grafica }}',
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
