<legend style="margin-bottom: 5px"></legend>

<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active"><a href="#tab-total" data-toggle="tab">Total</a></li>
        <li><a href="#tab-mo" data-toggle="tab">MO</a></li>
        <li><a href="#tab-insumos" data-toggle="tab">Insumos</a></li>
        <li><a href="#tab-fijos" data-toggle="tab">Fijos</a></li>
        <li><a href="#tab-regalias" data-toggle="tab">Regalías</a></li>
        <li><a href="#tab-flor_bqt" data-toggle="tab">Flor bqt</a></li>
        <li><a href="#tab-flor_exportable" data-toggle="tab">Flor Export.</a></li>
    </ul>
    <div class="tab-content no-padding">
        <div class="chart tab-pane active" id="tab-total">
            <canvas id="chart_total" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-mo">
            <canvas id="chart_mo" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-insumos">
            <canvas id="chart_insumos" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-fijos">
            <canvas id="chart_fijos" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-regalias">
            <canvas id="chart_regalias" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-flor_bqt">
            <canvas id="chart_flor_bqt" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
        <div class="chart tab-pane active default" id="tab-flor_exportable">
            <canvas id="chart_flor_exportable" width="100%" height="25" style="margin-top: 5px"></canvas>
        </div>
    </div>
</div>

<script>
    setTimeout(function () {
        $('.default').removeClass('active');
    }, 1);

    construir_char('Total', 'chart_total');
    construir_char('MO', 'chart_mo');
    construir_char('Insumos', 'chart_insumos');
    construir_char('Fijos', 'chart_fijos');
    construir_char('Regalías', 'chart_regalias');
    construir_char('Flor bqt', 'chart_flor_bqt');
    construir_char('Flor Export.', 'chart_flor_exportable');

    function construir_char(label, id) {
        labels = [];
        data_list = [];

        if (label == 'Flor bqt') {
            @foreach($compra_flor as $pos_l => $item)
            labels.push('{{$item['semana']}}');
            data_list.push('{{round($item['query']->tallos, 2)}}');
            @endforeach
        } else if (label == 'Flor Export.') {
            @foreach($compra_flor as $pos_l => $item)
            labels.push('{{$item['semana']}}');
            data_list.push('{{round($item['query']->exportada, 2)}}');
            @endforeach
        } else {
            @foreach($resumen_costos as $pos_l => $item)
            labels.push('{{$item->codigo_semana}}');
            if (label == 'MO')
                data_list.push('{{$item->mano_obra}}');
            if (label == 'Insumos')
                data_list.push('{{$item->insumos}}');
            if (label == 'Fijos')
                data_list.push('{{$item->fijos}}');
            if (label == 'Regalías')
                data_list.push('{{$item->regalias}}');
            if (label == 'Total')
                data_list.push('{{$item->mano_obra + $item->insumos + $item->fijos + $item->regalias + $compra_flor[$pos_l]['query']->tallos + $compra_flor[$pos_l]['query']->exportada}}');
            @endforeach
        }

        datasets = [{
            label: label + ' ',
            data: data_list,
            //backgroundColor: '#8c99ff54',
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
                        tension: 0, // disables bezier curves
                    }
                },
                tooltips: {
                    mode: 'point' // nearest, point, index, dataset, x, y
                },
                legend: {
                    display: true,
                    position: 'bottom',
                    fullWidth: false,
                    onClick: function () {
                    },
                    onHover: function () {
                    },
                    reverse: true,
                },
                showLines: true, // for all datasets
                borderCapStyle: 'round',    // "butt" || "round" || "square"
            }
        });
    }

</script>