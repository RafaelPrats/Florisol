<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    Kardex de <b>{{ $variedad->nombre }}</b>
    desde <b>{{ explode(' del ', convertDateToText($desde))[0] }}</b>
    al <b>{{ explode(' del ', convertDateToText($hasta))[0] }}</b> en <b>{{ $bodega }}</b>
</legend>
<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark" style="width: 150px">
                    Fecha
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 100px">
                    Tipo
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Documento
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Detalle
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                    Entrada
                </th>
                <th class="padding_lateral_5 bg-yura_warning" style="width: 90px">
                    Salida
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Saldo <i class="fa fa-fw fa-caret-right"></i>{{ $saldo }}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ explode(' del ', convertDateToText($item->fecha))[0] }}
                    </th>
                    <th class="text-center text-sm" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'INGRESO')
                            <span class="badge bg-yura_primary">
                                {{ $item->tipo }}
                            </span>
                        @else
                            <span class="badge bg-yura_warning">
                                {{ $item->tipo }}
                            </span>
                        @endif
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->concepto }}
                        @if (!in_array($item->concepto, ['MOVIMIENTO', 'FLOR SOLIDA']))
                            - {{ $item->documento }}
                        @endif
                    </th>
                    <th class="padding_lateral_5 text-sm" style="border-color: #9d9d9d">
                        {{ $item->detalle }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'INGRESO')
                            {{ number_format($item->cantidad) }}
                            @php
                                $saldo += $item->cantidad;
                            @endphp
                        @endif
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'SALIDA')
                            {{ number_format($item->cantidad) }}
                            @php
                                $saldo -= $item->cantidad;
                            @endphp
                        @endif
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($saldo) }}
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
