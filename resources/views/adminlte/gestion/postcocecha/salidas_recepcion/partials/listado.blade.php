<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5">
                    <div style="width: 100px">
                        Planta
                    </div>
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    <div style="width: 230px">
                        Variedad
                    </div>
                </th>
                @php
                    $totales_tallos = [];
                @endphp
                @foreach ($fechas as $f)
                    @php
                        $totales_tallos[] = 0;
                    @endphp
                    <th class="text-center bg-yura_dark padding_lateral_5">
                        <div style="width: 100px">
                            {{ convertDateToText($f) }}
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos_o => $item)
                <tr onmouseover="$(this).addClass('bg-aqua')" onmouseleave="$(this).removeClass('bg-aqua')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['variedad']->nombre_planta }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item['variedad']->nombre_variedad }}
                    </th>
                    @foreach ($item['valores_fechas'] as $pos_f => $v)
                        @php
                            $totales_tallos[$pos_f] += $v;
                        @endphp
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ number_format($v) }}
                        </th>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="text-center th_yura_green" colspan="2">
                Totales
            </th>
            @foreach ($totales_tallos as $v)
                <th class="text-center bg-yura_dark padding_lateral_5" style="border-color: #9d9d9d">
                    {{ number_format($v) }}
                </th>
            @endforeach
        </tr>
    </table>
</div>
