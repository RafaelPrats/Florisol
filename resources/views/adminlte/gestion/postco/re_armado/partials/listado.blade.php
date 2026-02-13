<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px;">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    <div style="width: 190px">
                        RECETA
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    LONGITUD
                </th>
                @php
                    $totales = [];
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark th_fechas" data-fecha="{{ $f }}">
                        <div style="width: 90px">
                            {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}
                            <br>
                            <small>{{ convertDateToText($f) }}</small>
                        </div>
                    </th>
                    @php
                        $totales[] = [
                            'sobrantes' => 0,
                        ];
                    @endphp
                @endforeach
                <th class="text-center th_yura_green" style="width: 70px">
                    TOTAL
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $total_receta = 0;
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d; background-color: #eeeeee"
                        onclick="modal_receta('{{ $item['var']->id_variedad }}', '{{ $item['var']->longitud }}')">
                        {{ $item['var']->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                        {{ $item['var']->longitud }}cm
                    </th>
                    @foreach ($fechas as $pos_f => $fecha)
                        @php
                            $valor = '';
                            foreach ($item['valores'] as $val) {
                                if ($val->fecha == $fecha) {
                                    $valor = $val;
                                }
                            }
                            if ($valor != '') {
                                $total_receta += $valor != '' ? $valor->cantidad : 0;
                                $totales[$pos_f]['sobrantes'] += $valor != '' ? $valor->cantidad : 0;
                            }
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d;">
                            @if ($valor != '')
                                {{ $valor->cantidad }}
                            @endif
                        </th>
                    @endforeach
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #eeeeee">
                        {{ $total_receta }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="2">
                Totales
            </th>
            @php
                $total = 0;
            @endphp
            @foreach ($totales as $v)
                @php
                    $total += $v['sobrantes'];
                @endphp
                <th class="text-center bg-yura_dark" style="background-color: #eeeeee; border-color: #9d9d9d;">
                    {{ $v['sobrantes'] }}
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total }}
            </th>
        </tr>
    </table>
</div>

<style type="text/css">
    .tr_fija_top_1 {
        position: sticky;
        top: 23px;
        z-index: 8;
    }

    .tr_fija_bottom_0 {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }
</style>

<script type="text/javascript">
    estructura_tabla('table_listado')

    function modal_receta(variedad, longitud) {
        fechas = [];
        th_fechas = $('.th_fechas');
        for (i = 0; i < th_fechas.length; i++) {
            fechas.push(th_fechas[i].getAttribute('data-fecha'));
        }
        datos = {
            fechas: JSON.stringify(fechas),
            variedad: variedad,
            longitud: longitud,
        }
        get_jquery('{{ url('re_armado/modal_receta') }}', datos, function(retorno) {
            modal_view('modal_modal_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Pedidos de la Receta',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
    }
</script>
