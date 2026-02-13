<legend style="font-size: 1.2em; margin-bottom: 5px" class="text-center">
    Seleccione la fecha del pedido <b>#{{ $pedido->codigo_ref }}</b>
</legend>
<div class="input-group">
    <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
        <i class="fa fa-fw fa-calendar"></i> Fecha
    </div>
    <input type="date" id="input_fecha_pedido" required class="form-control input-yura_default text-center"
        style="width: 100% !important;" value="{{ $pedido->fecha }}">
    <div class="input-group-btn">
        <button class="btn btn-primary btn-yura_primary"
            onclick="store_mover_fecha_pedido('{{ $pedido->id_import_pedido }}')">
            <i class="fa fa-fw fa-save"></i> GRABAR
        </button>
    </div>
</div>

<script>
    function store_mover_fecha_pedido(ped) {
        if ($('#input_fecha_pedido').val() != '') {
            texto =
                "<div class='alert alert-warning text-center'>¿Esta seguro de <b>CAMBIAR</b> la fecha del pedido?</div>";

            modal_quest('modal_store_mover_fecha_pedido', texto, 'Eliminar pedido', true, false, '40%', function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id_pedido: ped,
                    fecha: $('#input_fecha_pedido').val()
                };
                post_jquery_m('importar_pedidos/store_mover_fecha_pedido', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });

            })
        }
    }
</script>
