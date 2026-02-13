<td colspan="3">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green">
                Fecha
            </th>
            <th class="text-center th_yura_green">
                Proveedor
            </th>
            <th class="text-center th_yura_green">
                Factura
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Longitud
            </th>
            <th class="text-center th_yura_green">
                Edad
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Ramos
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Tallos x Ramo
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Total Tallos
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Tallos Disponibles
            </th>
            <th class="text-center th_yura_green" style="width: 90px">
            </th>
        </tr>
        @php
            $total_inventario_planta = 0;
        @endphp
        @foreach ($listado as $pos => $item)
            @php
                $total_inventario_planta += $item->disponibles;
            @endphp
            <tr style="background-color: {{ $pos % 2 == 0 ? '#dddddd' : '' }}">
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($item->fecha)))] }}
                    <small>{{ convertDateToText($item->fecha) }}</small>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->proveedor_nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d; width: 100px">
                    <input type="number" id="factura_inv_{{ $item->id_desglose_recepcion }}" min="0"
                        value="{{ $item->factura }}" style="width: 100%" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->variedad_nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->longitud }} <sup>cm</sup>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ difFechas(hoy(), $item->fecha)->d }} <sup><small><b>dias</b></small></sup>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="ramos_inv_{{ $item->id_desglose_recepcion }}" min="0"
                        value="{{ $item->cantidad_mallas }}" style="width: 100%" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="tallos_x_malla_inv_{{ $item->id_desglose_recepcion }}" min="0"
                        value="{{ $item->tallos_x_malla }}" style="width: 100%" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="total_tallos_inv_{{ $item->id_desglose_recepcion }}" min="0"
                        value="{{ $item->cantidad_mallas * $item->tallos_x_malla }}" style="width: 100%"
                        class="text-center" readonly>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $item->disponibles }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_dark" title="Ver Pdf de Etiquetas"
                            onclick="view_pdf_inventario('{{ $item->id_desglose_recepcion }}')">
                            <i class="fa fa-fw fa-file-pdf-o"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_inventario('{{ $item->id_desglose_recepcion }}', '{{ $item->id_planta }}')">
                            <i class="fa fa-fw fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_inventario('{{ $item->id_desglose_recepcion }}', '{{ $item->id_planta }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </table>
</td>

<script>
    $('#span_inventario_planta_{{ $id_planta }}').html('{{ $total_inventario_planta }}');
</script>
