<div class="nav-tabs-custom">
    <ul class="nav nav-pills nav-justified">
        <li class="active">
            <a href="#listado_compras" data-toggle="tab" aria-expanded="false">
                Compras
            </a>
        </li>
        <li class="">
            <a href="#documentos" data-toggle="tab" aria-expanded="true">
                Internos
            </a>
        </li>
        <li class="">
            <a href="#movimientos" data-toggle="tab" aria-expanded="true">
                Movimientos
            </a>
        </li>
        <li class="">
            <a href="#correcciones" data-toggle="tab" aria-expanded="true">
                Correcciones
            </a>
        </li>
    </ul>
    <div class="tab-content no-padding">
        <div class="tab-pane active" id="listado_compras" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_compras')
            </div>
        </div>
        <div class="tab-pane" id="documentos" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_documentos')
            </div>
        </div>
        <div class="tab-pane" id="movimientos" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_movimientos')
            </div>
        </div>
        <div class="tab-pane" id="correcciones" style="position: relative">
            <div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px;">
                @include('adminlte/gestion/postco/reporte_ingresos/partials/_correcciones')
            </div>
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
        texto =
            '<div class="alert alert-warning text-center"><h3>¿Esta seguro de <b>MODIFICAR</b> el inventario?</h3></div>';

        modal_quest('modal_update_compra', texto, 'Modificar el inventario', true, false, '40%',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: input.data('id_ingreso_recepcion'),
                    tallos: input.val(),
                }
                post_jquery_m('{{ url('reporte_ingresos/update_compra') }}', datos, function() {});
            });
    }
</script>
