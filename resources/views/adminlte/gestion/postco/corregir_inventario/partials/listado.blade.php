<div class="input-group">
    <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
        Fecha
    </div>
    <input type="date" name="new_fecha" id="new_fecha" class="form-control input-yura_default"
        value="{{ hoy() }}">
    <div class="input-group-addon bg-yura_dark">
        N° de Orden
    </div>
    <input type="text" name="new_orden" id="new_orden" class="form-control input-yura_default"
        value="{{ $last_orden }}">
</div>
<div style="overflow-y: scroll; overflow-x: scroll; max-height: 600px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Planta
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos Actuales
                </th>
                <th class="padding_lateral_5 bg-yura_warning" style="width: 90px">
                    Corregir
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $pos => $item)
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->disponibles }}
                    </th>
                    <th style="border-color: #9d9d9d">
                        <input type="number" style="width: 100%; background-color: #ffd993"
                            class="text-center input_corregir" min="0"
                            id="tallos_corregir_{{ $item->id_variedad }}" data-id_variedad="{{ $item->id_variedad }}"
                            data-disponibles="{{ $item->disponibles }}">
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_correccion()">
        <i class="fa fa-fw fa-save"></i> GRABAR CORREGIR
    </button>
</div>

<script>
    estructura_tabla('table_listado');
    $('#table_listado_filter').addClass('hidden');

    function store_correccion() {
        data = [];
        input_corregir = $('.input_corregir');
        for (i = 0; i < input_corregir.length; i++) {
            id = input_corregir[i].id;
            id_variedad = $('#' + id).data('id_variedad');
            anterior = parseInt($('#' + id).data('disponibles'));
            actual = parseInt($('#' + id).val());
            diferencia = actual - anterior;
            if (actual > 0 && diferencia != 0) {
                data.push({
                    id_variedad: id_variedad,
                    anterior: anterior,
                    actual: actual,
                    diferencia: diferencia,
                });
            }
        }
        if (data.length > 0) {
            texto =
                '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>GRABAR</b> la correccion?</h3></div>' +
                '<div class="input-group">' +
                '<div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
                'Codigo de autorizacion' +
                '</div>' +
                '<input type="password" id="codigo_autorizacion" style="width: 100%" class="text-center form-control">' +
                '</div>';

            modal_quest('modal_store_correccion', texto, 'Desechar la flor', true, false, '40%',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        fecha: $('#new_fecha').val(),
                        orden: $('#new_orden').val(),
                        bodega: $('#bodega_filtro').val(),
                        data: JSON.stringify(data),
                        codigo: $('#codigo_autorizacion').val(),
                    }
                    post_jquery_m('{{ url('corregir_inventario/store_correccion') }}', datos, function() {
                        cerrar_modals();
                        listar_reporte();
                    });
                })
        }
    }
</script>
