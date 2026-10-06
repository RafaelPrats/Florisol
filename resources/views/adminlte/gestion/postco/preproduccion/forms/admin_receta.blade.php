@include('adminlte.gestion.postco.preproduccion.forms._estilos_receta')
<div class="receta-ui">
<div class="receta-hero">
    <div class="receta-hero-title">
        <span class="receta-hero-icon"><i class="fa fa-fw fa-sliders"></i></span>
        <div>
            <h4>Administrar distribución · {{ $det_caja->variedad->nombre }}</h4>
            <small>{{ convertDateToText($proyecto->fecha) }} · {{ $det_caja->longitud_ramo }} cm</small>
        </div>
    </div>
    <div class="receta-hero-metrics">
        <span class="receta-metric">Ramos del pedido
            <strong>{{ $caja->cantidad * $det_caja->ramos_x_caja }}</strong></span>
    </div>
</div>
<div class="receta-toolbar">
    <div class="receta-toolbar-field">
        <label for="select_numero_receta">Distribución guardada</label>
        <select id="select_numero_receta" class="form-control">
            @foreach ($numeros_receta as $n)
                <option value="{{ $n }}">{{ $n }}</option>
            @endforeach
        </select>
    </div>
    <button type="button" class="btn btn-yura_primary" onclick="cargar_receta()">
        <i class="fa fa-fw fa-refresh"></i> Cargar distribución
    </button>
</div>
<input type="hidden" id="id_detalle_seleccionado" value="{{ $det_caja->id_detalle_caja_proyecto }}">
<input type="hidden" id="ramos_pedido" value="{{ $caja->cantidad * $det_caja->ramos_x_caja }}">
<input type="hidden" id="longitud_pedido" value="{{ $det_caja->longitud_ramo }}">
<input type="hidden" id="postco_fecha" value="{{ $proyecto->fecha }}">
<div class="receta-editor-layout">
    <section id="listado_productos" class="receta-editor-panel">
        <div class="receta-editor-panel-header">
            <span><i class="fa fa-fw fa-search"></i> Catálogo de variedades</span>
            <span>1 · Seleccionar</span>
        </div>
        <div class="receta-editor-panel-body">
            <div class="receta-catalog-filter" id="td_cargar_longitudes">
                <div class="receta-catalog-filter-field">
                    <label for="receta_planta_filtro">Filtrar por planta</label>
                    <select id="receta_planta_filtro" class="form-control"
                        onchange="buscar_variedades()">
                        <option value="">Seleccione una flor</option>
                        @foreach ($plantas as $p)
                            <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-yura_dark" title="Buscar variedades"
                    aria-label="Buscar variedades" onclick="buscar_variedades()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
            <div id="div_listado_variedades">
            </div>
        </div>
    </section>

    <div class="receta-editor-actions">
        <button type="button" class="btn btn-yura_dark" onclick="agregar_variedades()">
            <i class="fa fa-fw fa-arrow-right"></i> Agregar
        </button>
        <button type="button" class="btn btn-yura_primary receta-save-action"
            onclick="store_distribucion_receta()">
            <i class="fa fa-fw fa-save"></i> Grabar receta
        </button>
    </div>

    <section id="listado_seleccionados" class="receta-editor-panel">
        <div class="receta-editor-panel-header">
            <span><i class="fa fa-fw fa-list-ul"></i> Distribución seleccionada</span>
            <span>2 · Ajustar unidades</span>
        </div>
        <div class="receta-editor-panel-body">
            <div class="receta-table-scroll" style="max-height: 500px">
                <table class="table-striped table-bordered receta-table" style="width: 100%; border: 1px solid #9d9d9d"
                    id="table_variedades_seleccionados">
                    <thead>
                        <tr class="tr_fija_top_0">
                            <th class="text-center th_yura_green" style="width: 25%">
                                FLOR
                            </th>
                            <th class="text-center th_yura_green" style="width: 25%">
                                VARIEDAD
                            </th>
                            <th class="text-center th_yura_green">
                                LONGITUD
                            </th>
                            <th class="text-center th_yura_green">
                                UNIDADES
                            </th>
                            <th class="text-center th_yura_green">
                            </th>
                        </tr>
                    </thead>
                    @php
                        $pos = 0;
                    @endphp
                    <tbody id="tbody_variedades_seleccionados">
                        @foreach ($det_caja->distribuciones as $pos => $item)
                            <tr id="tr_variedad_seleccionado_{{ $pos + 1 }}">
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ $item->variedad->planta->nombre }}
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    {{ $item->variedad->nombre }}
                                    <input type="hidden" class="cant_variedad_seleccionado"
                                        value="{{ $pos + 1 }}">
                                    <input type="hidden" id="id_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->id_variedad }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" class="text-center" style="width: 100%"
                                        id="longitud_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->longitud }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <input type="number" class="text-center" style="width: 100%"
                                        id="cantidad_variedad_seleccionado_{{ $pos + 1 }}"
                                        value="{{ $item->unidades }}">
                                </td>
                                <td class="text-center" style="border-color: #9d9d9d">
                                    <button type="button" class="btn btn-xs btn-yura_danger" title="Quitar"
                                        onclick="quitar_variedad_seleccionado('{{ $pos + 1 }}')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
    cant_variedad_seleccionado = {{ $pos + 1 }};

    function buscar_variedades() {
        datos = {
            planta: $('#receta_planta_filtro').val(),
            ramos_pedido: $('#ramos_pedido').val(),
            longitud_pedido: $('#longitud_pedido').val(),
            postco_fecha: $('#postco_fecha').val(),
            segmento: $('#segmento_selected').val(),
        };
        get_jquery('{{ url('preproduccion/buscar_variedades') }}', datos, function(retorno) {
            $('#div_listado_variedades').html(retorno);
        }, 'div_listado_variedades');
    }

    function agregar_variedades() {
        variedades_listados = $('.variedades_listados');
        for (i = 0; i < variedades_listados.length; i++) {
            id = variedades_listados[i].value;
            if ($('#cantidad_' + id).val() > 0) {
                cant_variedad_seleccionado++;
                nombre = $('#nombre_variedad_' + id).val();
                nombre_planta = $('#nombre_planta_' + id).val();
                longitud = $('#longitud_' + id).val();
                cantidad = $('#cantidad_' + id).val();
                $('#tbody_variedades_seleccionados').append('<tr id="tr_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '">' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre_planta +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    nombre +
                    '<input type="hidden" class="cant_variedad_seleccionado" value="' + cant_variedad_seleccionado +
                    '">' +
                    '<input type="hidden" id="id_variedad_seleccionado_' + cant_variedad_seleccionado +
                    '" value="' + id + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" class="text-center" style="width: 100%" id="longitud_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '" value="' + longitud + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<input type="number" class="text-center" style="width: 100%" id="cantidad_variedad_seleccionado_' +
                    cant_variedad_seleccionado + '" value="' + cantidad + '">' +
                    '</td>' +
                    '<td class="text-center" style="border-color: #9d9d9d">' +
                    '<button type="button" class="btn btn-xs btn-yura_danger" title="Quitar" onclick="quitar_variedad_seleccionado(' +
                    cant_variedad_seleccionado + ')">' +
                    '<i class="fa fa-fw fa-trash"></i>' +
                    '</button>' +
                    '</td>' +
                    '</tr>');
            }
        }
    }

    function quitar_variedad_seleccionado(cant_variedad_seleccionado) {
        $('#tr_variedad_seleccionado_' + cant_variedad_seleccionado).remove();
    }

    function store_distribucion_receta() {
        cant_variedad_seleccionado = $('.cant_variedad_seleccionado');
        data = [];
        id_detalle = $('#id_detalle_seleccionado').val();
        for (i = 0; i < cant_variedad_seleccionado.length; i++) {
            pos = cant_variedad_seleccionado[i].value;
            unidades = $('#cantidad_variedad_seleccionado_' + pos).val();
            longitud = $('#longitud_variedad_seleccionado_' + pos).val();
            id_variedad = $('#id_variedad_seleccionado_' + pos).val();
            if (unidades > 0 && longitud != '')
                data.push({
                    id_variedad: id_variedad,
                    longitud: longitud,
                    unidades: unidades,
                })
        }
        if (data.length > 0) {
            datos = {
                _token: '{{ csrf_token() }}',
                id_detalle: id_detalle,
                data: JSON.stringify(data)
            };
            post_jquery_m('{{ url('preproduccion/store_distribucion_receta') }}', datos, function(retorno) {
                cerrar_modals();
                modal_receta('{{ $det_caja->id_variedad }}', '{{ $det_caja->longitud_ramo }}');
            });
        }
    }

    function cargar_receta() {
        id_detalle = $('#id_detalle_seleccionado').val();
        datos = {
            id_detalle: id_detalle,
            numero_receta: $('#select_numero_receta').val(),
            ramos_pedido: $('#ramos_pedido').val(),
            modo: 'admin',
        };
        get_jquery('{{ url('preproduccion/cargar_receta') }}', datos, function(retorno) {
            $('#tbody_variedades_seleccionados').html(retorno);
            cant_variedad_seleccionado = $('#tbody_variedades_seleccionados .cant_variedad_seleccionado').length;
        }, 'tbody_variedades_seleccionados');
    }
</script>
</div>
