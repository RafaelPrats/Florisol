<div style="height: 450px; overflow-x: scroll; overflow-y: scroll">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
        id="table_cosecha_diaria">
        <thead>
            <tr>
                <th class="text-left th_yura_green fila_fija1 columna_fija_left_0" style="border-radius: 18px 0 0 0; padding-left: 5px; z-index: 10 !important">
                    Variedad/Tipo
                </th>
                @php
                    $total_fechas = [];
                @endphp
                @foreach ($fechas as $f)
                    <th class="text-center bg-yura_dark fila_fija1" style="color: white; padding: 5px;">
                        <div style="width: 80px">
                            {{ $f->fecha }}
                        </div>
                    </th>
                    @php
                        array_push($total_fechas, 0);
                    @endphp
                @endforeach
                <th class="text-center th_yura_green fila_fija1">
                    Total
                </th>
                <th class="text-center th_yura_green fila_fija1">
                    Plantas Iniciales
                </th>
                <th class="text-center th_yura_green fila_fija1">
                    Productividad
                </th>
                <th class="text-center th_yura_green fila_fija1" style="border-radius: 0 18px 0 0">
                    %
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $total_plantas_iniciales = 0;
            @endphp
            @foreach ($data as $d)
                <input type="hidden" class="ids_variedad" value="{{ $d['variedad']->id_variedad }}">
                <input type="hidden" class="ids_planta" value="{{ $d['variedad']->id_planta }}">
                @if ($d['tipo'] == 'P')
                    <tr class="mouse-hand" style="background-color: #00b3886b"
                        onmouseover="$(this).css('color', '#007309');" onmouseleave="$(this).css('color', 'black')"
                        onclick="$('.tr_planta_{{ $d['variedad']->id_planta }}').toggleClass('hidden'); $('#icon_planta_{{ $d['variedad']->id_planta }}').toggleClass('fa-caret-down').toggleClass('fa-caret-left')">
                        <th class="text-left columna_fija_left_0" style="border-color: #9d9d9d; padding-left: 5px; background-color: #00b3886b !important">
                            {{ $d['variedad']->planta_nombre }}
                            <i class="fa fa-fw fa-caret-down pull-right"
                                id="icon_planta_{{ $d['variedad']->id_planta }}"></i>
                        </th>
                        @php
                            $total_fila = 0;
                        @endphp
                        @foreach ($fechas as $pos_f => $f)
                            @php
                                $valor = 0;
                            @endphp
                            @foreach ($d['resumen'] as $pos => $v)
                                @php
                                    if ($f->fecha == $v->fecha) {
                                        $valor = $v->cantidad;
                                    }
                                @endphp
                            @endforeach
                            @php
                                $total_fila += $valor;
                                $total += $valor;
                                $total_fechas[$pos_f] += $valor;
                            @endphp
                            <td class="text-center" style="border-color: #9d9d9d">
                                <span style="margin-left: 5px; margin-right: 5px">{{ number_format($valor) }}</span>
                            </td>
                        @endforeach
                        @php
                            $total_plantas_iniciales += $d['plantas_iniciales_resumen'];
                        @endphp
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span style="margin-left: 5px; margin-right: 5px">
                                {{ number_format($total_fila) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ number_format($d['plantas_iniciales_resumen']) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ $d['plantas_iniciales_resumen'] > 0 ? number_format($total_fila / $d['plantas_iniciales_resumen'], 2) : 0 }}
                            </span>
                        </th>
                        <input type="hidden" id="total_planta_{{ $d['variedad']->id_planta }}"
                            value="{{ $total_fila }}">
                        <th class="text-center" style="border-color: #9d9d9d">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_porcentaje_planta_{{ $d['variedad']->id_planta }}"></span>
                        </th>
                    </tr>
                    <tr id="tr_variedad_{{ $d['variedad']->id_variedad }}"
                        class="tr_planta_{{ $d['variedad']->id_planta }}">
                        <th class="text-right columna_fija_left_0"
                            style="border-color: #9d9d9d; padding-right: 5px; background-color: #e9ecef">
                            {{-- @include('adminlte.crm.cosecha_diaria.partials._dropdown_opciones') --}}
                            {{ $d['variedad']->variedad_nombre }}
                        </th>
                        @php
                            $total_fila = 0;
                        @endphp
                        @foreach ($fechas as $pos_f => $f)
                            @php
                                $valor = 0;
                            @endphp
                            @foreach ($d['lista'] as $v)
                                @php
                                    if ($f->fecha == $v->fecha) {
                                        $valor = $v->cantidad;
                                    }
                                @endphp
                            @endforeach
                            @php
                                $total_fila += $valor;
                            @endphp
                            <td class="text-center" style="border-color: #9d9d9d" title="{{ $v->fecha }}">
                                <span style="margin-left: 5px; margin-right: 5px">{{ number_format($valor) }}</span>
                            </td>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ number_format($total_fila) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ number_format($d['plantas_iniciales']) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ $d['plantas_iniciales'] > 0 ? number_format($total_fila / $d['plantas_iniciales'], 2) : 0 }}
                            </span>
                        </th>
                        <input type="hidden" id="total_variedad_{{ $d['variedad']->id_variedad }}"
                            value="{{ $total_fila }}">
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_porcentaje_variedad_{{ $d['variedad']->id_variedad }}"></span>
                        </th>
                    </tr>
                @else
                    <tr id="tr_variedad_{{ $d['variedad']->id_variedad }}"
                        class="tr_planta_{{ $d['variedad']->id_planta }}">
                        <th class="text-right columna_fija_left_0"
                            style="border-color: #9d9d9d; padding-right: 5px; background-color: #e9ecef">
                            {{-- @include('adminlte.crm.cosecha_diaria.partials._dropdown_opciones') --}}
                            {{ $d['variedad']->variedad_nombre }}
                        </th>
                        @php
                            $total_fila = 0;
                        @endphp
                        @foreach ($fechas as $pos_f => $f)
                            @php
                                $valor = 0;
                            @endphp
                            @foreach ($d['lista'] as $v)
                                @php
                                    if ($f->fecha == $v->fecha) {
                                        $valor = $v->cantidad;
                                    }
                                @endphp
                            @endforeach
                            @php
                                $total_fila += $valor;
                            @endphp
                            <td class="text-center" style="border-color: #9d9d9d" title="{{ $v->fecha }}">
                                <span style="margin-left: 5px; margin-right: 5px">{{ number_format($valor) }}</span>
                            </td>
                        @endforeach
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ number_format($total_fila) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ number_format($d['plantas_iniciales']) }}
                            </span>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_total_fila_variedad_{{ $d['variedad']->id_variedad }}">
                                {{ $d['plantas_iniciales'] > 0 ? number_format($total_fila / $d['plantas_iniciales'], 2) : 0 }}
                            </span>
                        </th>
                        <input type="hidden" id="total_variedad_{{ $d['variedad']->id_variedad }}"
                            value="{{ $total_fila }}">
                        <th class="text-center" style="border-color: #9d9d9d; background-color: #e9ecef">
                            <span style="margin-left: 5px; margin-right: 5px"
                                id="th_porcentaje_variedad_{{ $d['variedad']->id_variedad }}"></span>
                        </th>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tr id="tr_fijo_bottom_0">
            <th class="text-right th_yura_green columna_fija_left_0" style="padding-right: 5px; z-index: 9 !important">
                Totales
            </th>
            @foreach ($total_fechas as $v)
                <th class="text-center bg-yura_dark" style="color: white">
                    {{ number_format($v) }}
                </th>
            @endforeach
            <th class="text-center th_yura_green">
                {{ number_format($total) }}
                <input type="hidden" id="input_total" value="{{ $total }}">
            </th>
            <th class="text-center th_yura_green">
                {{ number_format($total_plantas_iniciales) }}
                </span>
            <th class="text-center th_yura_green">
                {{ $total_plantas_iniciales > 0 ? number_format($total / $total_plantas_iniciales, 2) : 0 }}
            </th>
            <th class="text-center th_yura_green">
                100%
            </th>
        </tr>
    </table>
</div>

<script>
    calcular_porcentajes_variedad();
    calcular_porcentajes_planta();

    function calcular_porcentajes_variedad() {
        total = $('#input_total').val();
        variedades = $('.ids_variedad');
        for (i = 0; i < variedades.length; i++) {
            id = variedades[i].value;
            parcial = $('#total_variedad_' + id).val();
            porcentaje = total > 0 ? (parcial * 100) / total : 0;
            $('#th_porcentaje_variedad_' + id).html(parseFloat(porcentaje).toFixed(2) + '%')
        }
    }

    function calcular_porcentajes_planta() {
        total = $('#input_total').val();
        variedades = $('.ids_planta');
        for (i = 0; i < variedades.length; i++) {
            id = variedades[i].value;
            parcial = $('#total_planta_' + id).val();
            porcentaje = total > 0 ? (parcial * 100) / total : 0;
            $('#th_porcentaje_planta_' + id).html(parseFloat(porcentaje).toFixed(2) + '%')
        }
    }

    function actualizar_fecha(variedad, fecha, hasta = '') {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: fecha,
            hasta: hasta,
            variedad: variedad,
        };
        $('#tr_variedad_' + variedad).LoadingOverlay('show');
        $.post('{{ url('cosecha_diaria/actualizar_fecha') }}', datos, function() {

        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_variedad_' + variedad).LoadingOverlay('hide');
        });
    }

    function actualizar_all_fechas(fecha, hasta = '') {
        ids_variedad = $('.ids_variedad');
        array_ids = [];
        for (i = 0; i < ids_variedad.length; i++) {
            id = ids_variedad[i].value;
            array_ids.push(id);
        }
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: fecha,
            hasta: hasta,
            variedades: array_ids,
        };
        $('#table_cosecha_diaria').LoadingOverlay('show');
        $.post('{{ url('cosecha_diaria/actualizar_all_fechas') }}', datos, function(retorno) {
            if (retorno.success)
                buscar_cosecha_diaria();
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#table_cosecha_diaria').LoadingOverlay('hide');
        });
    }
</script>

<style>
    #table_cosecha_diaria thead .fila_fija1 {
        z-index: 8;
        position: sticky;
        top: 0;
    }

    #table_cosecha_diaria tr#tr_fijo_bottom_0 th {
        z-index: 8;
        position: sticky;
        bottom: 0;
    }

    .columna_fija_left_0{
        position: sticky;
        left: 0;
        z-index: 9;
    }
</style>
