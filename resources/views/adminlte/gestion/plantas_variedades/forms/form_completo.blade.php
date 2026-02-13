<div style="overflow-y: scroll; overflow-x: scroll; max-height: 650px">
    <table class="table-bordered table-striped" style="width: 100%">
        @foreach ($plantas as $p)
            <tr id="tr_planta_{{ $p->id_planta }}">
                <th class="text-center" colspan="2">
                    <input type="text" value="{{ $p->nombre }}" id="nombre_planta_{{ $p->id_planta }}" title="Nombre"
                        style="width: 100%" class="bg-yura_dark" placeholder="Nombre">
                </th>
                <th class="text-center">
                    <input type="text" value="{{ $p->siglas }}" id="siglas_planta_{{ $p->id_planta }}"
                        title="Siglas" style="width: 100%" class="bg-yura_dark" placeholder="Siglas">
                </th>
                <th class="text-center">
                    <select id="tipo_planta_{{ $p->id_planta }}" style="width: 100%; height: 26px;"
                        class="bg-yura_dark">
                        <option value="N" {{ $p->tipo == 'N' ? 'selected' : '' }}>Normal</option>
                        <option value="P" {{ $p->tipo == 'P' ? 'selected' : '' }}>Perenne</option>
                    </select>
                </th>
                <th class="text-center bg-yura_dark">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="actualizar_planta('{{ $p->id_planta }}')" title="Grabar">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="$('.tr_var_{{ $p->id_planta }}').toggleClass('hidden')"
                            title="Desplegar variedades">
                            <i class="fa fa-fw fa-caret-down"></i>
                        </button>
                    </div>
                </th>
            </tr>
            @foreach ($p->variedades_activos as $v)
                <tr class="tr_var_{{ $p->id_planta }} hidden" id="tr_variedad_{{ $v->id_variedad }}">
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" value="{{ $v->nombre }}" id="nombre_var_{{ $v->id_variedad }}"
                            style="width: 100%; height: 26px" placeholder="Nombre" title="Nombre">
                        <input type="hidden" class="ids_variedad_{{ $p->id_planta }}" value="{{ $v->id_variedad }}">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" value="{{ $v->siglas }}" id="siglas_var_{{ $v->id_variedad }}"
                            style="width: 100%; height: 26px" placeholder="Siglas" title="Siglas">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d" colspan="2">
                        <div class="input-group" style="width: 100%">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-xs btn-yura_dark dropdown-toggle"
                                    style="height: 26px; border-radius: 0 !important" data-toggle="dropdown"
                                    aria-expanded="false">
                                    Rotacion <i class="fa fa-fw fa-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left sombra_pequeña" role="menu"
                                    style="z-index: 10 !important">
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="copiar_campo('dias_rotacion_recepcion', '{{ $v->id_variedad }}', '{{ $v->id_planta }}')">
                                            <i class="fa fa-fw fa-copy"></i>
                                            Copiar para el resto de variedades de la Planta
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <input type="number" value="{{ $v->dias_rotacion_recepcion }}"
                                id="dias_rotacion_recepcion_var_{{ $v->id_variedad }}"
                                class="dias_rotacion_recepcion_{{ $v->id_planta }}" style="width: 100%; height: 26px;"
                                placeholder="Dias de Rotacion">
                        </div>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary"
                                onclick="actualizar_variedad('{{ $v->id_variedad }}')">
                                <i class="fa fa-fw fa-save"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>

<script>
    function actualizar_planta(id_pta) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_planta_' + id_pta).val(),
            id_planta: id_pta,
            siglas: $('#siglas_planta_' + id_pta).val(),
            tipo: $('#tipo_planta_' + id_pta).val(),
        };
        post_jquery_m('{{ url('plantas_variedades/actualizar_planta') }}', datos, function() {}, 'tr_planta_' +
            id_pta);
    }

    function actualizar_variedad(id_var) {
        datos = {
            _token: '{{ csrf_token() }}',
            nombre: $('#nombre_var_' + id_var).val(),
            id_variedad: id_var,
            siglas: $('#siglas_var_' + id_var).val(),
            rotacion: $('#dias_rotacion_recepcion_var_' + id_var).val(),
        };
        post_jquery_m('{{ url('plantas_variedades/actualizar_variedad') }}', datos, function() {

        }, 'tr_variedad_' + id_var);
    }

    function copiar_campo(campo, id_var, planta) {
        valor = $('#' + campo + '_var_' + id_var).val();
        $('.' + campo + '_' + planta).val(valor);
        listado = $('.ids_variedad_' + planta);
        for (i = 0; listado.length; i++) {
            id_variedad = listado[i].value;
            actualizar_variedad(id_variedad);
        }
    }
</script>
