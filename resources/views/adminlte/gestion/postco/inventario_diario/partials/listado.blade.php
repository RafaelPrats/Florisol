<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark">
                    <div style="min-width: 220px">
                        Planta
                    </div>
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    <div style="min-width: 220px">
                        Variedad
                    </div>
                </th>
                @foreach ($fechas as $f)
                    <th class="padding_lateral_5 bg-yura_dark" style="width: 190px">
                        {{ explode(' del ', convertDateToText($f))[0] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                @if ($item->total_saldo > 0)
                    <tr onmouseover="$(this).css('background-color', 'cyan')"
                        onmouseleave="$(this).css('background-color', '')">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $item->pta_nombre }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $item->var_nombre }}
                        </th>
                        @php
                            $anterior = $item->valores[0]['saldo'];
                        @endphp
                        @foreach ($item->valores as $val)
                            @php
                                $icon = '';
                                if ($anterior > $val['saldo']) {
                                    $icon = '<i class="fa fa-fw fa-caret-down error"></i>';
                                } elseif ($anterior < $val['saldo']) {
                                    $icon = '<i class="fa fa-fw fa-caret-up text-color_yura"></i>';
                                }
                                $anterior = $val['saldo'];
                            @endphp
                            <th class="padding_lateral_5" style="border-color: #9d9d9d">
                                {{ number_format($val['saldo']) }} {!! $icon !!}
                            </th>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>

<script>
    estructura_tabla('table_listado')
</script>
