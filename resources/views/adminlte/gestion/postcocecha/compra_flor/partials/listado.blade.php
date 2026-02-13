<div style="overflow-y: scroll; overflow-x: scroll; max-height: 500px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="th_yura_green padding_lateral_5">
                Planta
            </th>
            <th class="text-center th_yura_green padding_lateral_5" style="width: 10%">
                Inventario
            </th>
            <th class="text-center th_yura_green padding_lateral_5" style="width: 10%">
                Opciones
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr>
                <th class="padding_lateral_5 bg-yura_dark">
                    {{ $item['planta']->nombre }}
                    <span class="pull-right {{ count($item['proveedores']) - 1 == 0 ? 'error' : '' }}">
                        <sup>
                            {{ count($item['proveedores']) - 1 }}
                            Proveedor{{ count($item['proveedores']) - 1 > 1 ? 'es' : '' }}
                        </sup>
                    </span>
                </th>
                <th class="text-center bg-yura_dark">
                    <button type="button" class="btn btn-xs btn-yura_default btn-block"
                        onmouseover="$('#icon_inventario_planta_{{ $item['planta']->id_planta }}').removeClass('hidden')"
                        onmouseleave="$('#icon_inventario_planta_{{ $item['planta']->id_planta }}').addClass('hidden')"
                        onclick="listar_inventario_planta('{{ $item['planta']->id_planta }}')">
                        <span
                            id="span_inventario_planta_{{ $item['planta']->id_planta }}">{{ number_format($item['inventario']) }}
                        </span>
                        <i class="fa fa-fw fa-eye hidden"
                            id="icon_inventario_planta_{{ $item['planta']->id_planta }}"></i>
                    </button>
                </th>
                <th class="text-center padding_lateral_5 bg-yura_dark">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="add_formulario_planta('{{ $item['planta']->id_planta }}')">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_formulario_planta('{{ $item['planta']->id_planta }}')">
                            <i class="fa fa-fw fa-minus"></i>
                        </button>
                    </div>
                </th>
            </tr>
            <tr id="tr_formulario_planta_{{ $item['planta']->id_planta }}" class="hidden">
                <td colspan="3">
                    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d"
                        id="table_formulario_planta_{{ $item['planta']->id_planta }}">
                    </table>
                    <div class="text-center" style="margin-top: 1px">
                        <button type="button" class="btn btn-xs btn-yura_primary"
                            onclick="store_compra_flor_planta('{{ $item['planta']->id_planta }}')">
                            <i class="fa fa-fw fa-save"></i> GRABAR TODA LA PLANTA
                        </button>
                    </div>
                </td>
            </tr>
            <tr id="tr_inventario_planta_{{ $item['planta']->id_planta }}" class="hidden">
            </tr>
            <input type="hidden" id="cant_formulario_planta_{{ $item['planta']->id_planta }}" value="0">
        @endforeach
    </table>
</div>

<style>
    .tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>

@foreach ($listado as $pos => $item)
    <select id="select_proveedores_{{ $item['planta']->id_planta }}" class="hidden">
        @foreach ($item['proveedores'] as $p)
            <option value="{{ $p->id_proveedor }}">{{ $p->nombre }}</option>
        @endforeach
    </select>
@endforeach
<select id="select_longitudes" class="hidden">
    <option value="">Longitudes</option>
    @foreach ($longitudes as $l)
        <option value="{{ $l->nombre }}" {{ $l->nombre == 60 ? 'selected' : '' }}>{{ $l->nombre }} cm</option>
    @endforeach
</select>

<script>
    function add_formulario_planta(pta) {
        select_proveedores = $('#select_proveedores_' + pta).html();
        select_longitudes = $('#select_longitudes').html();
        cant_formulario_planta = $('#cant_formulario_planta_' + pta).val();
        cant_formulario_planta++;
        $('#cant_formulario_planta_' + pta).val(cant_formulario_planta);
        $('#tr_formulario_planta_' + pta).removeClass('hidden');
        $('#tr_inventario_planta_' + pta).addClass('hidden');
        $('#table_formulario_planta_' + pta).append('<tr id="tr_add_planta_' + pta + '_' + cant_formulario_planta +
            '" class="tr_add_planta_' + pta + '">' +
            '<td style="width: 40%">' +
            '<select id="add_finca_origen_' + pta + '_' + cant_formulario_planta +
            '" style="width: 100%" onchange="seleccionar_finca_origen(' + cant_formulario_planta + ', ' + pta +
            ');">' +
            select_proveedores +
            '</select>' +
            '</td>' +
            '<td style="width: 25%">' +
            '<select id="add_variedad_' + pta + '_' + cant_formulario_planta +
            '" style="width: 100%; height: 28px" onchange="buscar_inventario(' + cant_formulario_planta + ', ' +
            pta + ')">' +
            '<option value="">Variedad</option>' +
            '</select>' +
            '</td>' +
            '<td style="width: 10%">' +
            '<select id="add_longitud_' + pta + '_' + cant_formulario_planta +
            '" style="width: 100%; height: 28px" onchange="buscar_inventario(' + cant_formulario_planta + ', ' +
            pta + ')">' +
            select_longitudes +
            '</select>' +
            '</td>' +
            '<td>' +
            '<input type="number" min="0" id="add_tallos_x_malla_' + pta + '_' + cant_formulario_planta +
            '" placeholder="Tallos x Malla" title="Tallos x Malla" class="text-center" style="width: 100%; height: 28px">' +
            '</td>' +
            '<td>' +
            '<input id="add_inventario_' + pta + '_' + cant_formulario_planta +
            '" class="text-center" style="width: 100%; height: 28px" placeholder="Inventario" readonly disabled>' +
            '</td>' +
            '</tr>');
        $('#add_finca_origen_' + pta + '_' + cant_formulario_planta).focus();
        $('#add_finca_origen_' + pta + '_' + cant_formulario_planta).select2();
        seleccionar_finca_origen(cant_formulario_planta, pta);
    }

    function seleccionar_finca_origen(pos, pta) {
        datos = {
            _token: '{{ csrf_token() }}',
            proveedor: $('#add_finca_origen_' + pta + '_' + pos).val(),
            planta: pta,
        }
        $('#tr_add_planta_' + pta + '_' + pos).LoadingOverlay('show');
        $.post('{{ url('compra_flor/seleccionar_finca_origen') }}', datos, function(retorno) {
            $('#add_variedad_' + pta + '_' + pos).html(retorno.variedades);
            buscar_inventario(pos, pta);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_add_planta_' + pta + '_' + pos).LoadingOverlay('hide');
        });
    }

    function buscar_inventario(pos, pta) {
        datos = {
            _token: '{{ csrf_token() }}',
            proveedor: $('#add_finca_origen_' + pta + '_' + pos).val(),
            variedad: $('#add_variedad_' + pta + '_' + pos).val(),
            longitud: $('#add_longitud_' + pta + '_' + pos).val(),
            planta: pta,
        }
        $('#tr_add_planta_' + pta + '_' + pos).LoadingOverlay('show');
        $.post('{{ url('compra_flor/buscar_inventario') }}', datos, function(retorno) {
            $('#add_inventario_' + pta + '_' + pos).val(retorno.inventario);
        }, 'json').fail(function(retorno) {
            console.log(retorno);
            alerta_errores(retorno.responseText);
        }).always(function() {
            $('#tr_add_planta_' + pta + '_' + pos).LoadingOverlay('hide');
        });
    }

    function delete_formulario_planta(pta) {
        cant_formulario_planta = $('#cant_formulario_planta_' + pta).val();
        $('#tr_add_planta_' + pta + '_' + cant_formulario_planta).remove();
        cant_formulario_planta--;
        $('#cant_formulario_planta_' + pta).val(cant_formulario_planta);
        if (cant_formulario_planta == 0) {
            $('#tr_formulario_planta_' + pta).addClass('hidden');
        }
        $('#tr_inventario_planta_' + pta).addClass('hidden');
    }

    function store_compra_flor_planta(pta) {
        cant_formulario_planta = $('#cant_formulario_planta_' + pta).val();
        data = [];
        for (i = 1; i <= cant_formulario_planta; i++) {
            if ($('#add_finca_origen_' + pta + '_' + i).val() != '' && $('#add_variedad_' + pta + '_' + i).val() !=
                '' && $('#add_longitud_' + pta + '_' + i).val() != '' && $('#add_tallos_x_malla_' + pta + '_' + i)
                .val() > 0)
                data.push({
                    proveedor: $('#add_finca_origen_' + pta + '_' + i).val(),
                    variedad: $('#add_variedad_' + pta + '_' + i).val(),
                    longitud: $('#add_longitud_' + pta + '_' + i).val(),
                    cantidad_mallas: 1,
                    tallos_x_malla: $('#add_tallos_x_malla_' + pta + '_' + i).val(),
                })
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                fecha: $('#fecha_filtro').val(),
                data: JSON.stringify(data),
            }
            post_jquery_m('{{ url('compra_flor/store_compra_flor_planta') }}', datos, function() {
                listar_inventario_planta(pta);
                $('.tr_add_planta_' + pta).remove();
                $('#cant_formulario_planta_' + pta).val(0);
            });
        }
    }

    function listar_inventario_planta(pta) {
        datos = {
            planta: pta,
        };
        get_jquery('{{ url('compra_flor/listar_inventario_planta') }}', datos, function(retorno) {
            $('#tr_inventario_planta_' + pta).html(retorno);
            $('#tr_formulario_planta_' + pta).addClass('hidden');
            $('#tr_inventario_planta_' + pta).removeClass('hidden');
        });
    }

    function update_inventario(id, pta) {
        datos = {
            _token: '{{ csrf_token() }}',
            id: id,
            cantidad_mallas: 1,
            tallos_x_malla: parseInt($('#tallos_x_malla_inv_' + id).val()),
        };
        if (datos['tallos_x_malla'] > 0) {
            post_jquery_m('{{ url('compra_flor/update_inventario') }}', datos, function(retorno) {
                listar_inventario_planta(pta)
            });
        } else {
            alerta(
                '<div class="alert alert-warning text-center">Los tallos <b>DISPONIBLES</b> no pueden ser mayores a los <b>INGRESADOS</b></div>'
            )
        }
    }

    function delete_inventario(id, pta) {
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>ELIMINAR</b> el inventario?</div>";

        modal_quest('modal_delete_inventario', texto, 'Eliminar inventario', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            };
            post_jquery_m('{{ url('compra_flor/delete_inventario') }}', datos, function(retorno) {
                cerrar_modals();
                listar_inventario_planta(pta)
            });
        })
    }
</script>
