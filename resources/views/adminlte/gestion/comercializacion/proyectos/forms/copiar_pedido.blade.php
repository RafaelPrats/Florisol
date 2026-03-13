<legend style="font-size: 1.2em; margin-bottom: 2px" class="text-center">
    Seleccione la(s) fecha(s) a copiar del pedido de: "<b>{{ $pedido->cliente->detalle()->nombre }}</b>"
</legend>

<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_copiar_pedido">
    <tr>
        <th class="text-center th_yura_green">
            Fecha
        </th>
        <th class="text-center th_yura_green" style="width: 60px">
            <button type="button" class="btn btn-xs btn-yura_dark" onclick="add_fecha()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </th>
    </tr>
    <tr id="tr_copiar_fecha_1">
        <td style="border-color: #9d9d9d">
            <input type="date" class="text-center" style="width: 100%" id="copiar_fecha_1" min="{{ hoy() }}">
        </td>
        <td style="border-color: #9d9d9d" class="text-center">
            <button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fecha(1)">
                <i class="fa fa-fw fa-trash"></i>
            </button>
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_copiar_pedido('{{ $pedido->id_proyecto }}')">
        <i class="fa fa-fw fa-copy"></i> GRABAR
    </button>
</div>

<script>
    var cantidad_fechas = 1;

    function add_fecha() {
        cantidad_fechas++;
        $('#table_copiar_pedido').append('<tr id="tr_copiar_fecha_' + cantidad_fechas + '">' +
            '<td style="border-color: #9d9d9d">' +
            '<input type="date" class="text-center" style="width: 100%" id="copiar_fecha_' + cantidad_fechas +
            '" min="{{ hoy() }}">' +
            '</td>' +
            '<td style="border-color: #9d9d9d" class="text-center">' +
            '<button type="button" class="btn btn-xs btn-yura_danger" onclick="eliminar_fecha(' + cantidad_fechas +
            ')">' +
            '<i class="fa fa-fw fa-trash"></i>' +
            '</button>' +
            '</td>' +
            '</tr>');
    }

    function eliminar_fecha(p) {
        $('#tr_copiar_fecha_' + p).remove();
    }

    function store_copiar_pedido(id_ped) {
        data = [];
        for (var i = 1; i <= cantidad_fechas; i++) {
            if ($('#copiar_fecha_' + i).length) {
                if ($('#copiar_fecha_' + i).val() !== '')
                    data.push(
                        $('#copiar_fecha_' + i).val()
                    );
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(data),
            id_ped: id_ped
        }
        post_jquery_m('{{ url('proyectos/store_copiar_pedido') }}', datos, function() {
            listar_reporte();
            cerrar_modals();
        })
    }
</script>
