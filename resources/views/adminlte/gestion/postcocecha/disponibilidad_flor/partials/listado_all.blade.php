@php
    $all_ids = '';
    foreach ($combinaciones as $pos_item => $item) {
        if ($pos_item == 0) {
            $all_ids .= $item->id_variedad;
        } else {
            $all_ids .= '|' . $item->id_variedad;
        }
    }
@endphp
<input type="hidden" value="{{ $all_ids }}" id="input_all_ids">

<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_listado_all">
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2" style="width: 30px">
                Total:{{ count($combinaciones) }}
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                <div style="width: 130px">
                    Planta
                </div>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                <div style="width: 170px">
                    Variedad
                </div>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Venta Total
            </th>
            @foreach ($fechas as $f)
                <th class="text-center th_yura_green celda_cargando" colspan="2">
                    {{ $f->fecha }}
                </th>
            @endforeach
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Negativos Total
                <button type="button" class="btn btn-xs btn-yura_default btn_listar_reporte"
                    title="Ver solo los negativos" onclick="mostrar_solo_negativos()">
                    <i class="fa fa-fw fa-filter"></i>
                </button>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Perdida Total
                <button type="button" class="btn btn-xs btn-yura_default btn_listar_reporte"
                    title="Ver solo las perdidas" onclick="mostrar_solo_perdidas()">
                    <i class="fa fa-fw fa-filter"></i>
                </button>
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            @foreach ($fechas as $f)
                <th class="padding_lateral_5 text-center bg-yura_dark">
                    Saldo
                </th>
                <th class="padding_lateral_5 text-center bg-yura_dark">
                    Perdida
                </th>
            @endforeach
        </tr>
    </table>
</div>

<input type="hidden" value="{{ $pos_listado }}" id="pos_listado">
<input type="hidden" value="{{ count($combinaciones) - 1 }}" id="count_listado">
<input type="hidden" value="0" id="num_filas">

<script>
    var mostar_negativo = 0;
    var mostar_perdida = 0;
    setTimeout(() => {
        cargar_tabla();
    }, 1000);

    function cargar_tabla() {
        $('.btn_listar_reporte').prop('disabled', true);
        pos_listado = $('#pos_listado').val();
        input_all_ids = $('#input_all_ids').val();
        input_all_ids = input_all_ids.split('|');
        ids_listado = [];
        for (i = 0; i < input_all_ids.length; i++) {
            if (i >= pos_listado && ids_listado.length < 10) {
                ids_listado.push(input_all_ids[i]);
                pos_listado = i + 1;
            }
            if (ids_listado.length >= 10) {
                break;
            }
        }
        datos = {
            ids_listado: JSON.stringify(ids_listado),
            hasta: $('#hasta_filtro').val(),
            num_filas: parseInt($('#num_filas').val()),
        }
        $('.celda_cargando').LoadingOverlay('show')
        $.get('{{ url('disponibilidad_flor/cargar_tabla') }}', datos, function(retorno) {
            $('#pos_listado').val(pos_listado);
            $('#table_listado_all').append(retorno);
        }).always(function() {
            $('.celda_cargando').LoadingOverlay('hide');
        });
    }

    function mostrar_solo_negativos() {
        mostar_negativo = !mostar_negativo;
        if (mostar_negativo == 1) {
            $('.tr_listado').addClass('hidden');
            $('.tr_negativo').removeClass('hidden');
        } else {
            $('.tr_listado').removeClass('hidden');
        }
    }

    function mostrar_solo_perdidas() {
        mostar_perdida = !mostar_perdida;
        if (mostar_perdida == 1) {
            $('.tr_listado').addClass('hidden');
            $('.tr_perdida').removeClass('hidden');
        } else {
            $('.tr_listado').removeClass('hidden');
        }
    }
</script>
