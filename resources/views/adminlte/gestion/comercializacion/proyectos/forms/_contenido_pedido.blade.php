<table style="width: 100%; border: 1px solid #9d9d9d; font-size: 0.9em" class="table-bordered"
    id="table_form_contenido_pedido">
    <thead>
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green" style="min-width: 60px">
                PIEZAS
            </th>
            <th class="text-center th_yura_green" style="min-width: 160px">
                BQT
            </th>
            <th class="text-center th_yura_green" style="min-width: 30px">
                TIPO CAJA
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                R. X CAJA
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                TOTAL RAMOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                T. X RAMOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                TOTAL TALLOS
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                LONGITUD
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                PRECIO
            </th>
            <th class="text-center th_yura_green" style="min-width: 60px">
                PRECIO CAJA
            </th>
            @foreach ($datos_exportacion as $dat_exp)
                <th class="text-center bg-yura_dark" style="min-width: 100px">
                    {{ $dat_exp->nombre }}
                    <input type="hidden" class="ids_marcaciones" value="{{ $dat_exp->id_dato_exportacion }}">
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="width: 40px">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_danger" title="Vaciar Pedido"
                        onclick="$('#tbody_form_contenido_pedido').html(''); form_cant_detalles = 0; calcular_totales_pedido();">
                        <i class="fa fa-fw fa-trash"></i>
                    </button>
                </div>
            </th>
        </tr>
    </thead>
    <tbody id="tbody_form_contenido_pedido"></tbody>
</table>
