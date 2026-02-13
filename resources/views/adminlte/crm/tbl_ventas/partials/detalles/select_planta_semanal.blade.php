<div style="overflow-x: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_select_planta_semanal">
        <thead>
            <tr>
                <th class="text-center th_yura_green col_fija_left_0" rowspan="2"
                    style="left:0 !important; position: sticky !important;">
                    <div style="width: 180px">
                        Colores {{ $planta->nombre }}
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="exportar_planta_semanal('{{ $planta->id_planta }}')">
                            <i class="fa fa-fw fa-file-excel-o"></i> Exportar
                        </button>
                    </div>
                </th>
                @foreach ($listado_annos as $a)
                    <th class="text-center th_yura_green" colspan="{{ count($a['semanas']) + 1 }}">
                        {{ $a['anno'] }}
                    </th>
                @endforeach
            </tr>
            <tr>
                @php
                    $array_totales_vacio = [];
                @endphp
                @foreach ($listado_annos as $a)
                    @php
                        $array_totales_semanas_vacio = [];
                    @endphp
                    @foreach ($a['semanas'] as $sem)
                        @php
                            $array_totales_semanas_vacio[] = [
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
                        $array_totales_vacio[] = $array_totales_semanas_vacio;
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
            @php
                $totales_annos = $array_totales_vacio;
            @endphp
            @foreach ($listado as $var)
                @php
                    $totales_annos_long = $array_totales_vacio;
                @endphp
                <tr>
                    <th class="padding_lateral_5 col_fija_left_0"
                        style="background-color: #dddddd; border-color: #9d9d9d">
                        {{ $var['variedad']->nombre }}
                    </th>
                    @foreach ($var['valores_anno'] as $pos_a => $a)
                        @php
                            $total_anno_item = 0;
                        @endphp
                        @foreach ($a['valores_semanas'] as $pos_sem => $sem)
                            @php
                                $total_anno_item += $sem['valor'];
                                $totales_annos_long[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                                $totales_annos[$pos_a][$pos_sem]['suma'] += $sem['valor'];
                            @endphp
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($sem['valor']) }}
                            </th>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                            {{ number_format($total_anno_item) }}
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 th_yura_green col_fija_left_0">
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
                    <th class="text-center th_yura_green">
                        {{ number_format($val['suma']) }}
                    </th>
                @endforeach
                <th class="text-center th_yura_green">
                    {{ number_format($total_anno) }}
                </th>
            @endforeach
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_select_planta_semanal')
    $('#table_select_planta_semanal_filter>label>input').addClass('input-yura_default');
</script>
