<legend class="text-center" style="margin-bottom: 5px; font-size: 1.3em"> Listado de flores del pedido
    <b>#{{ $proyecto->id_proyecto }}</b> de <b>{{ $proyecto->cliente->detalle()->nombre }}</b>, el
    <b>{{ convertDateToText($proyecto->fecha) }}</b>
    <button type="button" class="btn btn-xs btn-yura_default">
        <i class="fa fa-fw fa-file-excel-o"></i> Exportar
    </button>
</legend>
<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                    N°
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Planta
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Variedad
                </th>
                <th class="padding_lateral_5 th_yura_green" style="width: 90px">
                    Tallos
                </th>
                <th class="padding_lateral_5 bg-yura_warning" style="width: 90px">
                    Confirmar
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Diferencia
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_pedido = 0;
                $total_confirmado = 0;
            @endphp
            @foreach ($listado as $pos => $item)
                @php
                    $item->tallos += intval(porcentaje($finca_proveedor->margen, $item->tallos, 2));
                    $total_pedido += $item->tallos;
                    $total_confirmado += $item->confirmados;
                @endphp
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')"
                    class="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'error' : '' }}"
                    title="{{ $item->tipo_variedad == 'CONFIRMACION' ? 'Esta variedad ya no se encuentra en el pedido' : '' }}">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $pos + 1 }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->var_nombre }}
                    </th>
                    {{-- TALLOS TOTALES --}}
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="padding_lateral_5" readonly disabled
                            id="tallos_{{ $item->id_variedad }}" value="{{ $item->tallos }}">
                    </th>
                    {{-- CONFIRMAR --}}
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="padding_lateral_5 input_confirmar"
                            min="0" max="{{ $item->tallos }}" step="1"
                            value="{{ $item->confirmados > 0 ? $item->confirmados : '' }}"
                            data-id_variedad="{{ $item->id_variedad }}" id="confirmar_{{ $item->id_variedad }}"
                            onkeyup="calcular_totales(this)" onchange="calcular_totales(this)">
                    </th>
                    {{-- DIFERENCIA --}}
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%" class="padding_lateral_5" readonly disabled
                            id="diferencia_{{ $item->id_variedad }}" value="{{ $item->tallos - $item->confirmados }}">
                    </th>
                </tr>
            @endforeach
        </tbody>
        <tr>
            <th class="padding_lateral_5 bg-yura_dark" colspan="3">
                TOTALES
            </th>
            <th class="padding_lateral_5 th_yura_green">
                {{ $total_pedido }}
            </th>
            <th class="padding_lateral_5 bg-yura_warning">
                {{ $total_confirmado }}
            </th>
            <th class="padding_lateral_5 bg-yura_dark">
                {{ $total_pedido - $total_confirmado }}
            </th>
        </tr>
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    @if ($confirmacion == '')
        <button type="button" class="btn btn-yura_primary"
            onclick="store_confirmar_pedido('{{ $proyecto->id_proyecto }}', '{{ $finca_proveedor->id_finca_proveedor }}')">
            <i class="fa fa-fw fa-save"></i> CONFIRMAR PEDIDO
        </button>
    @elseif($confirmacion->estado == 'P')
        <button type="button" class="btn btn-yura_default" title="Actualizar cantidades"
            onclick="store_confirmar_pedido('{{ $proyecto->id_proyecto }}', '{{ $finca_proveedor->id_finca_proveedor }}')">
            <i class="fa fa-fw fa-check"></i> DOCUMENTO ENVIADO
            <b style="font-size: 1.3em; color: black">
                #{{ $confirmacion->id_proyecto_confirmacion }}
            </b>
        </button>
    @elseif($confirmacion->estado == 'C')
        <button type="button" class="btn btn-yura_primary" disabled>
            <i class="fa fa-fw fa-check"></i> DOCUMENTO CONFIRMADO
            <b style="font-size: 1.3em; color: black">
                #{{ $confirmacion->id_proyecto_confirmacion }}
            </b>
        </button>
    @elseif($confirmacion->estado == 'R')
        <button type="button" class="btn btn-yura_primary" disabled>
            <i class="fa fa-fw fa-check"></i> DOCUMENTO RECIBIDO
            <b style="font-size: 1.3em; color: black">
                #{{ $confirmacion->id_proyecto_confirmacion }}
            </b>
        </button>
    @endif
</div>

<script>
    function calcular_totales(input) {
        let id_variedad = $(input).data('id_variedad');
        let tallos = parseInt($('#tallos_' + id_variedad).val()) || 0;
        let confirmar = parseInt($(input).val()) || 0;

        // ==========================================
        // NO PERMITIR VALORES NEGATIVOS
        // ==========================================
        if (confirmar < 0) {
            confirmar = 0;
            $(input).val(0);
        }
        // ==========================================
        // NO PERMITIR CONFIRMAR MÁS DE LOS TALLOS
        // ==========================================
        if (confirmar > tallos) {
            confirmar = tallos;
            $(input).val(tallos);
        }

        // ==========================================
        // CALCULAR DIFERENCIA
        // ==========================================
        let diferencia = tallos - confirmar;

        // ==========================================
        // MOSTRAR DIFERENCIA
        // ==========================================
        $('#diferencia_' + id_variedad).val(diferencia);
    }

    function store_confirmar_pedido(proy, finca) {
        data = [];
        faltantes = false;
        input_confirmar = $('.input_confirmar');
        for (i = 0; i < input_confirmar.length; i++) {
            id = input_confirmar[i].id;
            variedad = $('#' + id).data('id_variedad');
            tallos = $('#tallos_' + variedad).val();
            confirmar = $('#confirmar_' + variedad).val();
            diferencia = parseInt($('#diferencia_' + variedad).val());
            if (diferencia > 0) {
                faltantes = true;
            }
            data.push({
                variedad: variedad,
                confirmar: confirmar,
                tallos: tallos,
                diferencia: diferencia,
            });
        }

        if (data.length > 0) {
            texto =
                '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>CONFIRMAR</b> el pedido?</h3></div>';

            modal_quest('modal_store_confirmar_pedido', texto, 'Desechar la flor', true, false, '40%',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        proy: proy,
                        finca: finca,
                        faltantes: faltantes,
                        data: JSON.stringify(data),
                    }
                    post_jquery_m('{{ url('confirmar_proyecto/store_confirmar_pedido') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                });
        }
    }
</script>
