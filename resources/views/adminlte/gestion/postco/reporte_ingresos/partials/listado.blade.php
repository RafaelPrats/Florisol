<div class="report-tabs-card">
    <ul class="nav nav-tabs report-tabs">
        <li class="active">
            <a href="#listado_compras" data-toggle="tab">
                <span><i class="fa fa-truck"></i> Compras</span>
                <span class="report-tab-count">{{ count($listado_compras) }}</span>
            </a>
        </li>
        <li>
            <a href="#documentos" data-toggle="tab">
                <span><i class="fa fa-file-text-o"></i> Internos</span>
                <span class="report-tab-count">{{ count($listado_documentos) }}</span>
            </a>
        </li>
        <li>
            <a href="#movimientos" data-toggle="tab">
                <span><i class="fa fa-exchange"></i> Movimientos</span>
                <span class="report-tab-count">{{ count($listado_movimientos) }}</span>
            </a>
        </li>
        <li>
            <a href="#correcciones" data-toggle="tab">
                <span><i class="fa fa-pencil-square-o"></i> Correcciones</span>
                <span class="report-tab-count">{{ count($listado_corregir) }}</span>
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="tab-pane active" id="listado_compras">
            @if (count($listado_compras))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_ingresos/partials/_compras')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay compras para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="documentos">
            @if (count($listado_documentos))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_ingresos/partials/_documentos')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay ingresos internos para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="movimientos">
            @if (count($listado_movimientos))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_ingresos/partials/_movimientos')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay movimientos para los filtros seleccionados.</div>
            @endif
        </div>
        <div class="tab-pane" id="correcciones">
            @if (count($listado_corregir))
                <div class="report-table-scroll">
                    @include('adminlte/gestion/postco/reporte_ingresos/partials/_correcciones')
                </div>
            @else
                <div class="report-empty"><i class="fa fa-inbox"></i> No hay correcciones para los filtros seleccionados.</div>
            @endif
        </div>
    </div>
</div>

<script>
    function habilitar_modificar() {
        texto =
            '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>MODIFICAR</b> la compra?</h3></div>' +
            '<div class="input-group">' +
            '<div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">' +
            'Codigo de autorizacion' +
            '</div>' +
            '<input type="password" id="codigo_autorizacion" style="width: 100%" class="text-center form-control">' +
            '</div>';

        modal_quest('modal_habilitar_modificar', texto, 'Desechar la flor', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    codigo: $('#codigo_autorizacion').val(),
                }
                post_jquery_m('{{ url('reporte_ingresos/habilitar_modificar') }}', datos, function() {
                    $('.input_compras_tallos').prop('readonly', false);
                });
            })
    }

    function update_compra(input) {
        if (input.val() != input.data('original') && input.val() >= 0) {
            texto =
                '<div class="alert alert-info text-center"><h3>¿Esta seguro de <b>MODIFICAR</b> la compra?</h3>' +
                '</div>';

            modal_quest('modal_update_compra', texto, 'Modificar la compra', true, false, '40%',
                function() {
                    datos = {
                        _token: '{{ csrf_token() }}',
                        id: input.data('id_ingreso_recepcion'),
                        tallos: input.val(),
                    }
                    post_jquery_m('{{ url('reporte_ingresos/update_compra') }}', datos, function() {
                        input.data('original', input.val());
                    });
                });
        }
    }
</script>
