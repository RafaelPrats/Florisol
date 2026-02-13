<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_global">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green col_fija_left_0" rowspan="2"
                    style="z-index: 10 !important; left:0 !important; position: sticky !important;">
                    <div style="width: 220px" class="text-center">
                        Flores / Años
                    </div>
                </th>
                @foreach ($listado_annos as $a)
                    <th class="text-center th_yura_green" colspan="{{ count($a['semanas']) + 1 }}">
                        {{ $a['anno'] }}
                    </th>
                @endforeach
            </tr>
            <tr id="tr_fija_top_1">
                @php
                    $totales_annos = [];
                @endphp
                @foreach ($listado_annos as $a)
                    @php
                        $totales_semanas = [];
                    @endphp
                    @foreach ($a['semanas'] as $sem)
                        @php
                            $totales_semanas[] = [
                                'suma' => 0,
                            ];
                        @endphp
                        <th class="text-center bg-yura_dark">
                            <div style="width: 80px" class="text-center">
                                {{ $sem->codigo }}
                            </div>
                        </th>
                    @endforeach
                    @php
                        $totales_annos[] = $totales_semanas;
                    @endphp
                    <th class="text-center bg-yura_dark">
                        <div style="width: 90px" class="text-center">
                            TOTAL <sup>{{ $a['anno'] }}</sup>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                <tr>
                    <th class="padding_lateral_5 bg-yura_dark col_fija_left_0">
                        <a href="javascript:void(0)" style="color: white"
                            onclick="select_planta_semanal('{{ $item['planta']->id_planta }}')">
                            {{ $item['planta']->nombre }} <i class="fa fa-fw fa-caret-right"></i>
                        </a>
                    </th>
                    @foreach ($item['valores_anno'] as $pos_a => $a)
                        @php
                            $total_anno_item = 0;
                        @endphp
                        @foreach ($a['valores_semanas'] as $pos_sem => $sem)
                            @php
                                $total_anno_item += $sem['valor'];
                                $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d"
                                onmouseover="$(this).css('background-color', '#00ffa1')"
                                onmouseleave="$(this).css('background-color', '')">
                                <a href="javascript:void(0)" style="color: black"
                                    onclick="select_planta_diario('{{ $item['planta']->id_planta }}', '{{ $sem['semana'] }}')">
                                    {{ number_format($sem['valor']) }}
                                </a>
                            </th>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            {{ number_format($total_anno_item) }}
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr id="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green col_fija_left_0" style="z-index: 10 !important">
                TOTALES
            </th>
            @foreach ($totales_annos as $t)
                @php
                    $total_anno = 0;
                @endphp
                @foreach ($t as $val)
                    @php
                        $total_anno += $val['suma'];
                    @endphp
                    <th class="text-center bg-yura_dark">
                        {{ number_format($val['suma']) }}
                    </th>
                @endforeach
                <th class="text-center bg-yura_dark">
                    {{ number_format($total_anno) }}
                </th>
            @endforeach
        </tr>
    </table>
</div>

<style>
    .col_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 8;
    }

    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 8;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 8;
    }
</style>

<script>
    estructura_tabla('table_global')
    $('#table_global_filter>label>input').addClass('input-yura_default');

    function select_planta_semanal(planta) {
        datos = {
            planta: planta,
            desde_semanal: parseInt($('#desde_semanal').val()),
            hasta_semanal: parseInt($('#hasta_semanal').val()),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            criterio: $('#criterio').val(),
        }
        if (datos['desde_semanal'] <= datos['hasta_semanal']) {
            get_jquery('{{ url('tbl_ventas/select_planta_semanal') }}', datos, function(retorno) {
                modal_view('modal_select_planta_semanal', retorno,
                    '<i class="fa fa-fw fa-plus"></i> Desglose Flor Semanal',
                    true, false, '{{ isPC() ? '95%' : '' }}',
                    function() {});
            });
        }
    }

    function exportar_planta_semanal(planta) {
        datos = {
            planta: planta,
            desde_semanal: parseInt($('#desde_semanal').val()),
            hasta_semanal: parseInt($('#hasta_semanal').val()),
            annos: $('#annos').val(),
            cliente: $('#cliente').val(),
            criterio: $('#criterio').val(),
        };
        if (datos['desde_semanal'] <= datos['hasta_semanal']) {
            datos = JSON.stringify(datos);
            $.LoadingOverlay('show');
            window.open('{{ url('tbl_ventas/exportar_planta_semanal') }}' + '?datos=' + datos, '_blank');
            $.LoadingOverlay('hide');
        }
    }

    function select_planta_diario(planta, semana) {
        datos = {
            planta: planta,
            semana: semana,
            cliente: $('#cliente').val(),
            criterio: $('#criterio').val(),
        }
        get_jquery('{{ url('tbl_ventas/select_planta_diario') }}', datos, function(retorno) {
            modal_view('modal_select_planta_diario', retorno,
                '<i class="fa fa-fw fa-plus"></i> Desglose Flor Diario',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        });
    }
</script>
