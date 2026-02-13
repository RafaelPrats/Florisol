<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_global">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" rowspan="2">
                    <div style="width: 250px" class="text-center">
                        Clientes / Años
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
                    <th class="padding_lateral_5 bg-yura_dark">
                        {{ $item['cliente']->nombre }}
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
        <tr id="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green">
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
    #tr_fija_bottom_0 th {
        position: sticky;
        bottom: 0;
        z-index: 9;
    }

    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    #tr_fija_top_1 th {
        position: sticky;
        top: 21px;
        z-index: 9;
    }
</style>

<script>
    estructura_tabla('table_global');
    $('#table_global_filter>label>input').addClass('input-yura_default')
</script>
