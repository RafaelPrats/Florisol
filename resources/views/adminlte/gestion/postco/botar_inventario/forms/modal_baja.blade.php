<div class="text-center">
    <div class="input-group">
        <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
            Fecha
        </div>
        <input type="date" name="new_fecha" id="new_fecha" class="form-control input-yura_default"
            value="{{ hoy() }}">
        <div class="input-group-addon bg-yura_dark">
            N° de Orden
        </div>
        <input type="text" name="new_orden_basura" id="new_orden_basura" class="form-control input-yura_default"
            value="{{ $last_orden + 1 }}">
    </div>
</div>

<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px" id="table_new_orden">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 bg-yura_dark">
                    Planta
                </th>
                <th class="padding_lateral_5 bg-yura_dark">
                    Variedad
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 120px">
                    Fecha
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
                    Longitud
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 30px">
                    TxR
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos Disponibles
                </th>
                <th class="text-center bg-yura_dark" style="width: 45px">
                    Botar
                </th>
                <th class="text-center bg-yura_dark" style="width: 60px">
                    Motivos
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).css('background-color', 'cyan')"
                    onmouseleave="$(this).css('background-color', '')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->pta_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->var_nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->fecha }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->longitud }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->tallos_x_ramo }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->disponibles }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="text" style="width: 100%" class="text-center input_botar"
                            data-id_inv="{{ $item->id_inventario_recepcion }}"
                            onkeyup="keyup_botar('{{ $item->id_inventario_recepcion }}')"
                            id="botar_{{ $item->id_inventario_recepcion }}" max="{{ $item->disponibles }}">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <select id="motivo_{{ $item->id_inventario_recepcion }}" style="width: 100%; height: 26px;"
                            onchange="keyup_botar('{{ $item->id_inventario_recepcion }}')">
                            <option value="">Seleccione...</option>
                            @foreach ($motivos as $mot)
                                <option value="{{ $mot->id_motivo_baja }}">
                                    {{ $mot->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_orden_basura()">
        <i class="fa fa-fw fa-save"></i> GRABAR ORDEN de DESECHO
    </button>
</div>

<script>
    estructura_tabla('table_new_orden');

    function keyup_botar(id) {
        botar = parseInt($('#botar_' + id).val());
        motivo = parseInt($('#motivo_' + id).val());
        $('#botar_' + id).removeClass('bg-yura_warning');
        $('#motivo_' + id).removeClass('bg-yura_warning');
        if (botar > 0 && botar <= parseInt($('#botar_' + id).prop('max'))) {
            $('#botar_' + id).addClass('bg-yura_warning');
        } else {
            $('#botar_' + id).val(0);
        }
        if (motivo > 0) {
            $('#motivo_' + id).addClass('bg-yura_warning');
        }
    }

    function store_orden_basura() {
        if ($('#table_new_orden_filter>label>input').val() != '') {
            alerta_errores(
                "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>Antes de grabar <b>DEBE DEJAR en BLANCO el FILTRO del LISTADO</b></h3></div>"
            );
            return false;
        } else {
            texto =
                "<div class='alert alert-info text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>CREAR la ORDEN de FLOR de BAJA</b>?</h3></div>";
        }

        input_botar = $('.input_botar');
        data = [];
        for (i = 0; i < input_botar.length; i++) {
            id = input_botar[i].id;
            id_inv = $('#' + id).data('id_inv');
            botar = parseInt(input_botar[i].value);
            motivo = $('#motivo_' + id_inv).val();
            if (botar > 0 && motivo != '') {
                data.push({
                    id_inv: id_inv,
                    botar: botar,
                    motivo: motivo,
                });
            }
        }
        if (data.length > 0)
            modal_quest('modal_store_orden_basura', texto, 'Crear Orden', true, false, '40%', function() {

                datos = {
                    _token: '{{ csrf_token() }}',
                    data: JSON.stringify(data),
                    fecha: $('#new_fecha').val(),
                    orden: $('#new_orden_basura').val(),
                }
                post_jquery_m('{{ url('botar_inventario/store_orden_basura') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
            })
    }
</script>
