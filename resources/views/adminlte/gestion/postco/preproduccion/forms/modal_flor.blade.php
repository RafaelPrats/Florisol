<legend class="text-center" style="margin-bottom: 5px; font-size: 1.1em;">
    Pedidos de la FLOR "<b>{{ $variedad->nombre }}</b>"
</legend>
<table class="table-bordered" style="border-color: #9d9d9d; width: 100%">
    <tr class="tr_fija_top_0">
        <th class="text-center th_yura_green">
            FECHA / PEDIDO
        </th>
        <th class="text-center th_yura_green" style="width: 70px">
            RAMOS
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            TxR
        </th>
        <th class="text-center th_yura_green" style="width: 70px">
            INV. DISP.
        </th>
        <th class="text-center th_yura_green" style="width: 70px">
            ARMADOS
        </th>
        <th class="text-center th_yura_green" style="width: 70px">
            RAMOS Disp.
        </th>
        <th class="text-center th_yura_green" style="width: 100px">
            ARMAR
        </th>
    </tr>
    @php
        $total_ramos = 0;
        $total_tallos = 0;
        $total_armados = 0;
        $total_disponibles = 0;
    @endphp
    @foreach ($listado as $item)
        @php
            $inventarioDisponible = getInventarioDisponibleByVariedadFecha($variedad, $item->fecha);
            $por_armar = $item->ramos - $item->armados;
            $disponibles = 0;
            if ($por_armar > 0) {
                $invRamosDisponible = intVal($inventarioDisponible / $item->tallos_x_ramo);
                if ($invRamosDisponible >= $por_armar) {
                    $disponibles = $por_armar;
                } else {
                    $disponibles = $invRamosDisponible;
                }
            }
            $total_ramos += $item->ramos;
            $total_tallos += $item->ramos * $item->tallos_x_ramo;
            $total_armados += $item->armados;
            $total_disponibles += $disponibles;
        @endphp
        <tr onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')">
            <th class="text-center" style="border-color: #9d9d9d">
                {{ convertDateToText($item->fecha) }}
                <br>
                <em>{{ $item->cliente_nombre }}</em>
                @if ($item->packing != '')
                    <br>
                    #{{ $item->packing }}
                @endif
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item->ramos }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item->tallos_x_ramo }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ number_format($inventarioDisponible) }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{ $item->armados }}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button class="btn btn-xs btn-yura_info" title="Disponibles">
                    {{ $disponibles }}
                </button>
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" max="{{ $disponibles }}" min="0" style="width: 100%" class="text-center"
                    id="ramos_armar_{{ $item->id_detalle_caja_proyecto }}">
                <button type="button" class="btn btn-xs btn-block btn-yura_dark"
                    onclick="store_armar_flor('{{ $item->id_detalle_caja_proyecto }}')">
                    Armar
                </button>
            </th>
        </tr>
    @endforeach
    <tr>
        <th class="text-center th_yura_green">
            TOTALES
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_ramos) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_tallos) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($inventario) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_armados) }}
        </th>
        <th class="text-center th_yura_green">
            {{ number_format($total_disponibles) }}
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>
</table>

<input type="hidden" id="variedad_selected" value="{{ $variedad->id_variedad }}">
<input type="hidden" id="fechas_selected" value="{{ $fechas }}">

<script>
    function store_armar_flor(id) {
        armar = parseInt($('#ramos_armar_' + id).val());
        if (armar <= parseInt($('#ramos_armar_' + id).prop('max'))) {
            texto =
                "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>ARMAR</b> los ramos?</h3></div>";

            modal_quest('modal_store_armar_flor', texto, 'Eliminar inventario', true, false, '40%', function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    armar: armar,
                }
                post_jquery_m('{{ url('preproduccion/store_armar_flor') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                    modal_flor($('#variedad_selected').val(), $('#fechas_selected').val());
                });
            })
        } else {
            alerta(
                '<div class="alert alert-warning text-center">No hay flor disponible en el inventario para armar los ramos indicados</div>'
            );
        }
    }
</script>
