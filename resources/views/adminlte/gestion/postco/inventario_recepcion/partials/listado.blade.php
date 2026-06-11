<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="width: 120px">
                        Planta
                    </div>
                </th>
                <th class="padding_lateral_5 th_yura_green" rowspan="2">
                    <div style="width: 120px">
                        Variedad
                    </div>
                </th>
                <th class="text-center th_yura_green" colspan="10">
                    Dias
                </th>
                <th class="text-center th_yura_green" style="width: 90px" rowspan="2">
                    Total
                </th>
            </tr>
            <tr>
                @php
                    $total_fechas = [];
                    $total_tallos = 0;
                @endphp
                @for ($i = 0; $i <= 9; $i++)
                    <th class="text-center bg-yura_dark" style="width: 70px;"
                        title="{{ convertDateToText(opDiasFecha('-', $i, hoy())) }}">
                        {{ $i }}{{ $i == 9 ? '...' : '' }}
                    </th>
                    @php
                        $total_fechas[] = 0;
                    @endphp
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($variedades as $var)
                @php
                    $total_variedad = 0;
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5 text-sm" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $var->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5 text-sm" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $var->var_nombre }}
                    </th>
                    @for ($i = 0; $i <= 9; $i++)
                        @php
                            $fecha = opDiasFecha('-', $i, hoy());
                            $valor = 0;
                            foreach ($var->valores as $val) {
                                if ($i < 9) {
                                    if ($val->fecha == $fecha) {
                                        $valor += $val->tallos;
                                    }
                                } else {
                                    if ($val->fecha <= $fecha) {
                                        $valor += $val->tallos;
                                    }
                                }
                            }
                            $total_variedad += $valor;
                            $total_tallos += $valor;
                            $total_fechas[$i] += $valor;
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d" title="{{ convertDateToText($fecha) }}">
                            {{ $valor > 0 ? $valor : '' }}
                        </th>
                    @endfor
                    <th class="text-center" style="border-color: #9d9d9d; background-color: #dddddd">
                        {{ $total_variedad }}
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr class="tr_fija_bottom_0">
            <th class="padding_lateral_5 th_yura_green" colspan="2">
                TOTALES
            </th>
            @foreach ($total_fechas as $pos => $val)
                <th class="text-center bg-yura_dark" title="{{ convertDateToText(opDiasFecha('-', $pos, hoy())) }}">
                    {{ $val }}
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ $total_tallos }}
            </th>
        </tr>
    </table>
</div>

<script>
    estructura_tabla('table_listado');

    function mover_inventario(id) {
        datos = {
            id: id,
        }
        get_jquery('{{ url('ingreso_inventario/mover_inventario') }}', datos, function(retorno) {
            modal_view('modal_mover_inventario', retorno,
                '<i class="fa fa-fw fa-plus"></i> Mover Inventario',
                true, false, '{{ isPC() ? '75%' : '' }}',
                function() {});
        })
    }
</script>
