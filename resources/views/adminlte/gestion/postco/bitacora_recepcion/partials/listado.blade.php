<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em">
    Bitacora de <b>{{ $variedad->nombre }}</b>
    desde <b>{{ explode(' del ', convertDateToText($desde))[0] }}</b>
    al <b>{{ explode(' del ', convertDateToText($hasta))[0] }}</b> en <b>{{ $bodega }}</b>
</legend>
<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark" style="width: 150px">
                    Fecha Registro
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 100px">
                    Tipo
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 150px">
                    Fecha
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Documento
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Usuario
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
                        {{ $item->fecha_registro }}
                    </th>
                    <th class="text-center text-sm" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'I')
                            <span class="badge bg-yura_primary">
                                INGRESO
                            </span>
                        @else
                            <span class="badge bg-yura_warning">
                                SALIDA
                            </span>
                        @endif
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ explode(' del ', convertDateToText($item->fecha))[0] }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->concepto }}
                        - {{ $item->numero }}
                    </th>
                    <th class="padding_lateral_5 text-sm" style="border-color: #9d9d9d">
                        {{ $item->usuario->username }}
                    </th>
                    <th class="padding_lateral_5 text-sm" style="border-color: #9d9d9d">
                        {{ $item->descripcion }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'I')
                            {{ number_format($item->cantidad) }}
                            @php
                                $saldo += $item->cantidad;
                            @endphp
                        @endif
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        @if ($item->tipo == 'S')
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
