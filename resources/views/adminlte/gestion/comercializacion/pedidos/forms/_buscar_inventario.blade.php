<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
    <tr class="tr_fija_top_0">
        <th class="text-center th_yura_green padding_lateral_5">
            Origen
        </th>
        <th class="text-center th_yura_green">
            Planta
        </th>
        <th class="text-center th_yura_green">
            Variedad
        </th>
        <th class="text-center th_yura_green">
            Long.
        </th>
        <th class="text-center th_yura_green">
            Tallos
        </th>
        <th class="text-center th_yura_green">
            Edad
        </th>
        <th class="text-center th_yura_green" style="width: 10%">
            Disp.
        </th>
        <th class="text-center th_yura_green">
        </th>
    </tr>
    @foreach ($listado as $item)
        <tr>
            <th class="text-center bg-yura_dark" colspan="8">
                {{ $item['finca']->nombre }}
            </th>
        </tr>
        @foreach ($item['inventarios'] as $inv)
            <tr>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $inv->finca_destino_nombre }}
                    <input type="hidden" id="id_finca_destino_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->finca_destino }}">
                    <input type="hidden" id="finca_destino_nombre_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->finca_destino_nombre }}">
                    <input type="hidden" id="id_finca_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $item['finca']->id_configuracion_empresa }}">
                    <input type="hidden" id="nombre_finca_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $item['finca']->nombre }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $inv->planta_nombre }}
                    <input type="hidden" id="planta_nombre_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->planta_nombre }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $inv->variedad_nombre }}
                    <input type="hidden" id="variedad_nombre_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->variedad_nombre }}">
                    <input type="hidden" id="id_variedad_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->id_variedad }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $inv->longitud }}<sup>cm</sup>
                    <input type="hidden" id="longitud_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->longitud }}">
                    <input type="hidden" id="id_clasificacion_ramo_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->id_clasificacion_ramo }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ $inv->tallos_x_ramo }}
                    <input type="hidden" id="tallos_x_ramo_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->tallos_x_ramo }}">
                </th>
                <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                    {{ difFechas(hoy(), $inv->fecha)->days }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" style="width: 100%" class="text-center" max="{{ $inv->disponibles }}"
                        min="0" id="disponibles_inv_{{ $inv->id_inventario_frio }}"
                        value="{{ $inv->disponibles }}">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <button type="button" class="btn btn-xs btn-yura_dark"
                        onclick="agregar_inventario('{{ $inv->id_inventario_frio }}')">
                        <i class="fa fa-fw fa-arrow-right"></i>
                    </button>
                </th>
            </tr>
        @endforeach
    @endforeach
</table>

<script>
    function agregar_inventario(inv) {
        id_finca = $('#id_finca_inv_' + inv).val();
        nombre_finca = $('#nombre_finca_inv_' + inv).val();
        id_finca_destino = $('#id_finca_destino_inv_' + inv).val();
        finca_destino_nombre = $('#finca_destino_nombre_inv_' + inv).val();
        planta_nombre = $('#planta_nombre_inv_' + inv).val();
        variedad_nombre = $('#variedad_nombre_inv_' + inv).val();
        id_variedad = $('#id_variedad_inv_' + inv).val();
        longitud = $('#longitud_inv_' + inv).val();
        id_clasificacion_ramo = $('#id_clasificacion_ramo_inv_' + inv).val();
        tallos_x_ramo = $('#tallos_x_ramo_inv_' + inv).val();
        disponibles = $('#disponibles_inv_' + inv).val();

        $('#droppable').addClass('hidden');
        $('#div_seleccionados').removeClass('hidden');

        cant_seleccionados++;
        $('#table_seleccionados').append('<tr id="tr_seleccionado_' + cant_seleccionados + '">' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="checkbox" id="check_selec_' + cant_seleccionados + '" class="check_selec">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="cajas_selec_' + cant_seleccionados +
            '" value="1">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            nombre_finca +
            '<input type="hidden" style="width: 100%" class="text-center" id="finca_selec_' + cant_seleccionados +
            '" value="' + id_finca + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            finca_destino_nombre +
            '<input type="hidden" style="width: 100%" class="text-center" id="finca_destino_selec_' +
            cant_seleccionados +
            '" value="' + id_finca_destino + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            planta_nombre +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            variedad_nombre +
            '<input type="hidden" style="width: 100%" class="text-center" id="id_variedad_selec_' +
            cant_seleccionados +
            '" value="' + id_variedad + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="longitud_selec_' +
            cant_seleccionados + '" value="' + longitud + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="tallos_x_ramo_selec_' +
            cant_seleccionados + '" value="' + tallos_x_ramo + '">' +
            '</th>' +
            '<th class="text-center" style="border-color: #9d9d9d">' +
            '<input type="number" style="width: 100%" class="text-center" id="ramos_x_caja_selec_' +
            cant_seleccionados + '" value="' + disponibles + '">' +
            '</th>' +
            '</tr>');
    }
</script>
