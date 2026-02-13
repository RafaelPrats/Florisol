<div style="overflow-y: scroll; max-height: 550px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
        <tr class="tr_fija_top_0">
            <th class="text-center th_yura_green">
                CODIGO
            </th>
            <th class="text-center th_yura_green">
                VARIEDAD
            </th>
            <th class="text-center th_yura_green">
                PLANTA
            </th>
            <th class="text-center th_yura_green">
                COLOR
            </th>
        </tr>
        @foreach ($listado as $pos => $item)
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['variedad'] != '')
                        @if (
                            $item['variedad']->siglas != espacios(mb_strtoupper($item['row']['A'])) ||
                                $item['variedad']->nombre != espacios(mb_strtoupper($item['row']['B'])) ||
                                $item['variedad']->color != espacios(mb_strtoupper($item['row']['D'])))
                            <input type="hidden" class="pos_variedad" value="{{ $pos }}">
                            <input type="hidden" id="import_id_variedad_{{ $pos }}"
                                value="{{ $item['variedad']->id_variedad }}">
                        @endif
                        @if ($item['variedad']->siglas == espacios(mb_strtoupper($item['row']['A'])))
                            {{ espacios(mb_strtoupper($item['row']['A'])) }}
                        @else
                            <span style="color:#ef6e11">
                                {{ espacios(mb_strtoupper($item['row']['A'])) }}
                                <input type="hidden" id="edit_import_siglas_{{ $pos }}"
                                    value="{{ espacios(mb_strtoupper($item['row']['A'])) }}">
                            </span>
                            @php
                                $fallos = true;
                            @endphp
                        @endif
                    @else
                        <span class="error">
                            {{ espacios(mb_strtoupper($item['row']['A'])) }}
                            <input type="hidden" class="new_pos_variedad" value="{{ $pos }}">
                            <input type="hidden" id="new_import_siglas_{{ $pos }}"
                                value="{{ espacios(mb_strtoupper($item['row']['A'])) }}">
                        </span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['variedad'] != '')
                        @if ($item['variedad']->nombre == espacios(mb_strtoupper($item['row']['B'])))
                            {{ espacios(mb_strtoupper($item['row']['B'])) }}
                        @else
                            <span style="color:#ef6e11">
                                {{ espacios(mb_strtoupper($item['row']['B'])) }}
                                <input type="hidden" id="edit_import_nombre_{{ $pos }}"
                                    value="{{ espacios(mb_strtoupper($item['row']['B'])) }}">
                            </span>
                            @php
                                $fallos = true;
                            @endphp
                        @endif
                    @else
                        <span class="error">
                            {{ espacios(mb_strtoupper($item['row']['B'])) }}
                            <input type="hidden" id="new_import_nombre_{{ $pos }}"
                                value="{{ espacios(mb_strtoupper($item['row']['B'])) }}">
                            <input type="hidden" id="new_import_nombre_planta_{{ $pos }}"
                                value="{{ espacios(mb_strtoupper($item['row']['C'])) }}">
                        </span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['planta'] != '')
                        @if ($item['planta']->nombre == espacios(mb_strtoupper($item['row']['C'])))
                            {{ espacios(mb_strtoupper($item['row']['C'])) }}
                        @else
                            <span style="color:#ef6e11">
                                {{ espacios(mb_strtoupper($item['row']['C'])) }}
                                <input type="hidden" class="pos_planta" value="{{ $pos }}">
                                <input type="hidden" id="edit_import_planta_{{ $pos }}"
                                    value="{{ espacios(mb_strtoupper($item['row']['C'])) }}">
                            </span>
                            @php
                                $fallos = true;
                            @endphp
                        @endif
                    @else
                        <span class="error">
                            {{ espacios(mb_strtoupper($item['row']['C'])) }}
                            <input type="hidden" class="new_pos_planta" value="{{ $pos }}">
                            <input type="hidden" id="new_import_planta_{{ $pos }}"
                                value="{{ espacios(mb_strtoupper($item['row']['C'])) }}">
                        </span>
                    @endif
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    @if ($item['variedad'] != '')
                        @if ($item['variedad']->color == espacios(mb_strtoupper($item['row']['D'])))
                            {{ espacios(mb_strtoupper($item['row']['D'])) }}
                        @else
                            <span style="color:#ef6e11">
                                {{ espacios(mb_strtoupper($item['row']['D'])) }}
                                <input type="hidden" id="edit_import_color_{{ $pos }}"
                                    value="{{ espacios(mb_strtoupper($item['row']['D'])) }}">
                            </span>
                            @php
                                $fallos = true;
                            @endphp
                        @endif
                    @else
                        <span class="error">
                            {{ espacios(mb_strtoupper($item['row']['D'])) }}
                            <input type="hidden" id="new_import_color_{{ $pos }}"
                                value="{{ espacios(mb_strtoupper($item['row']['D'])) }}">
                        </span>
                    @endif
                </th>
            </tr>
        @endforeach
    </table>
</div>

<div class="text-center">
    @if ($fallos)
        <button type="button" class="btn btn-yura_primary" onclick="store_importar_variedades()">
            <i class="fa fa-fw fa-save"></i> Grabar
        </button>
    @endif
</div>

<script>
    function store_importar_variedades() {
        new_pos_planta = $('.new_pos_planta');
        data_new_planta = [];
        for (i = 0; i < new_pos_planta.length; i++) {
            pos = new_pos_planta[i].value;
            nombre = $('#new_import_planta_' + pos).val();
            data_new_planta.push({
                nombre: nombre
            });
        }
        pos_planta = $('.pos_planta');
        data_edit_planta = [];
        for (i = 0; i < pos_planta.length; i++) {
            pos = pos_planta[i].value;
            nombre = $('#edit_import_planta_' + pos).val();
            data_edit_planta.push({
                nombre: nombre
            });
        }
        new_pos_variedad = $('.new_pos_variedad');
        data_new_variedad = [];
        for (i = 0; i < new_pos_variedad.length; i++) {
            pos = new_pos_variedad[i].value;
            siglas = $('#new_import_siglas_' + pos).val();
            color = $('#new_import_color_' + pos).val();
            nombre = $('#new_import_nombre_' + pos).val();
            planta = $('#new_import_nombre_planta_' + pos).val();
            data_new_variedad.push({
                planta: planta,
                nombre: nombre,
                siglas: siglas,
                color: color,
            });
        }
        pos_variedad = $('.pos_variedad');
        data_edit_variedad = [];
        for (i = 0; i < pos_variedad.length; i++) {
            pos = pos_variedad[i].value;
            id = $('#import_id_variedad_' + pos).val();
            siglas = $('#edit_import_siglas_' + pos).val();
            color = $('#edit_import_color_' + pos).val();
            nombre = $('#edit_import_nombre_' + pos).val();
            data_edit_variedad.push({
                id: id,
                nombre: nombre,
                siglas: siglas,
                color: color,
            });
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data_new_planta: JSON.stringify(data_new_planta),
            data_edit_planta: JSON.stringify(data_edit_planta),
            data_new_variedad: JSON.stringify(data_new_variedad),
            data_edit_variedad: JSON.stringify(data_edit_variedad),
        }
        post_jquery_m('{{ url('plantas_variedades/store_importar_variedades') }}', datos, function() {
            cerrar_modals();
            cargar_url('plantas_variedades');
        })
    }
</script>
