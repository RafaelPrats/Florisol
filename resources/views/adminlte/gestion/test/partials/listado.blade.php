<div style="overflow-y: scroll; max-height: 450px; overflow-x: scroll">
    <table class="table table-striped table-bordered" style="width: 100%" id="tabla_proyecciones">
        <thead>
        <tr id="tr_fija_top_0">
            <th class="text-center bg-yura_dark col_fija_left_0" style="z-index: 10 !important;">
                <div style="width: 80px">Días</div>
            </th>
            @for($d = 0; $d <= difFechas($hasta, $desde)->days; $d++)
                @php
                    $fecha = opDiasFecha('+', $d, $desde);
                    $dia = date('m', strtotime($fecha)).date('d', strtotime($fecha));
                @endphp
                <th class="text-center th_yura_green" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : ''}}">
                    <div style="width: 80px">{{$dia}}</div>
                </th>
            @endfor
        </tr>
        </thead>
        <tbody>
        @for($p = 0; $p <= 20; $p++)
            <tr style="background-color: {{$p % 2 == 0 ? '#e9ecef' : ''}}">
                <td class="text-center col_fija_left_0" style="border-color: #9d9d9d; background-color: {{$p % 2 == 0 ? '#e9ecef' : 'white'}} !important;">
                    POTRERO-{{rand(0, 99)}}
                </td>
                @for($d = 0; $d <= difFechas($hasta, $desde)->days; $d++)
                    @php
                        $fecha = opDiasFecha('+', $d, $desde);
                        $c1 = 2 * $p;
                    @endphp
                    @if($d == $c1 || $d == $c1 + 1)
                        <th class="text-center" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : '1px solid #9d9d9d'}}; background-color: cyan">
                            50%
                        </th>
                    @else
                        @php
                            $num_crec = $d - ($p * 2) - 1;
                        @endphp
                        @if($num_crec > 0)
                            @if($num_crec == 29 || $num_crec == 30)
                                <th class="text-center" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : '1px solid #9d9d9d'}}; background-color: cyan">
                                    50%
                                </th>
                            @else
                                <th class="text-center" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : '1px solid #9d9d9d'}}">
                                    @if($num_crec > 30)
                                        {{$num_crec - 30}}
                                    @else
                                        {{$num_crec}}
                                    @endif
                                </th>
                            @endif
                        @else
                            @php
                                $num_crec = 30 - ($num_crec * (-1));
                            @endphp
                            @if($num_crec == -1 || $num_crec == 0)
                                <th class="text-center" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : '1px solid #9d9d9d'}}; background-color: cyan">
                                    50%
                                </th>
                            @else
                                <th class="text-center" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : '1px solid #9d9d9d'}}">
                                    {{$num_crec < 0 ? 30 - ($num_crec * (-1)) : $num_crec}}
                                </th>
                            @endif
                        @endif
                    @endif
                @endfor
            </tr>
        @endfor
        </tbody>
        <tfoot>
        <tr>
            <th class="text-center bg-yura_dark col_fija_left_0">
                TOTALES <sup>Ltrs</sup>
            </th>
            @for($d = 0; $d <= difFechas($hasta, $desde)->days; $d++)
                @php
                    $fecha = opDiasFecha('+', $d, $desde);
                @endphp
                <th class="text-center th_yura_green" style="border: {{$fecha == hoy() ? '2px solid #d01c62' : ''}}">
                    {{number_format(rand(1000, 2000))}}
                </th>
            @endfor
        </tr>
        </tfoot>
    </table>
</div>

<style>
    #tr_fija_top_0 th{
        position: sticky;
        top: 0;
        z-index: 9;
    }

    .col_fija_left_0{
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>