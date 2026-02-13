<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                <input type="checkbox" class="mouse-hand"
                    onchange="$('.check_import_compras').prop('checked', $(this).prop('checked'))">
            </th>
            <th class="text-center th_yura_green">
                PLANTA
            </th>
            <th class="text-center th_yura_green">
                VARIEDAD
            </th>
            @foreach ($fechas as $pos_f => $f)
                <th class="text-center bg-yura_dark" style="width: 130px">
                    {{ convertDateToText($f) }}
                    <input type="hidden" class="fechas_compras" data-fecha="{{ $f }}"
                        id="fecha_compra_{{ $pos_f }}" data-pos_fecha="{{ $pos_f }}">
                </th>
            @endforeach
            <th class="text-center th_yura_green" style="width: 80px">
                LONGITUD
                <input type="number" id="input_all_longitud" style="width: 100%; color: black" class="text-center"
                    onchange="select_all_longitud()" onkeyup="select_all_longitud()">
            </th>
        </tr>
        @foreach ($listado as $item)
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <th class="text-center" style="border-color:#9d9d9d">
                    <input type="checkbox" class="mouse-hand check_import_compras"
                        id="check_import_{{ $item['variedad']->id_variedad }}"
                        data-id_variedad="{{ $item['variedad']->id_variedad }}">
                </th>
                <th class="text-center" style="border-color:#9d9d9d">
                    {{ $item['variedad']->planta->nombre }}
                </th>
                <th class="text-center" style="border-color:#9d9d9d">
                    {{ $item['variedad']->nombre }}
                </th>
                @foreach ($item['valores'] as $pos_v => $val)
                    <th class="text-center" style="border-color:#9d9d9d">
                        <input type="number" id="necesidad_{{ $item['variedad']->id_variedad }}_{{ $pos_v }}"
                            value="{{ $val != '' ? abs($val) : '' }}" class="text-center"
                            style="width: 100%; color: black">
                    </th>
                @endforeach
                <th class="text-center" style="border-color:#9d9d9d">
                    <input type="number" id="longitud_compra_{{ $item['variedad']->id_variedad }}"
                        style="width: 100%; color: black" class="text-center">
                </th>
            </tr>
        @endforeach
    </table>
</div>

<div style="margin-top: 5px" class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="store_import_compras()">
        <i class="fa fa-fw fa-save"></i> GRABAR COMPRAS
    </button>
</div>

<script>
    function select_all_longitud() {
        valor = $('#input_all_longitud').val();
        check_import_compras = $('.check_import_compras');
        for (i = 0; i < check_import_compras.length; i++) {
            id_check = check_import_compras[i].id;
            variedad = $('#' + id_check).attr('data-id_variedad');
            if ($('#' + id_check).prop('checked') == true) {
                $('#longitud_compra_' + variedad).val(valor);
            }
        }
    }

    function store_import_compras() {
        check_import_compras = $('.check_import_compras');
        data = [];
        for (i = 0; i < check_import_compras.length; i++) {
            id_check = check_import_compras[i].id;
            variedad = $('#' + id_check).attr('data-id_variedad');
            fechas_compras = $('.fechas_compras');
            necesidades = [];
            for (f = 0; f < fechas_compras.length; f++) {
                id_pos = fechas_compras[f].id;
                pos_f = $('#' + id_pos).attr('data-pos_fecha');
                necesidades.push($('#necesidad_' + variedad + '_' + pos_f).val());
            }
            data.push({
                variedad: variedad,
                necesidades: necesidades,
                longitud: $('#longitud_compra_' + variedad).val(),
            });
        }

        fechas = [];
        for (f = 0; f < fechas_compras.length; f++) {
            id_fecha = fechas_compras[f].id;
            fecha = $('#' + id_fecha).attr('data-fecha');
            fechas.push(fecha);
        }

        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            fechas: JSON.stringify(fechas),
        }
        post_jquery_m('{{ url('compra_flor/store_import_compras') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        });
    }
</script>
