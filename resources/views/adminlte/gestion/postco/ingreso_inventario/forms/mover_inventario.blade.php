<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_add_inventario">
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Planta
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Variedad
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Longitud
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
            Tallos
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $model->fecha }}
        </th>
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $model->variedad->planta->nombre }}
        </th>
        <th class="padding_lateral_5" style="border-color: #9d9d9d">
            {{ $model->variedad->nombre }}
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%;" class="padding_lateral_5" id="original_longitud"
                value="{{ $model->longitud }}" readonly>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="original_bodega" class="padding_lateral_5" disabled
                style="width: 100%; height: 26px; background-color: #dddddd">
                <option value="V" {{ $model->bodega == 'V' ? 'selected' : '' }}>Ventas</option>
                <option value="P" {{ $model->bodega == 'P' ? 'selected' : '' }}>Producción</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="original_disponibles"
                value="{{ $model->disponibles }}" readonly>
        </th>
    </tr>
    <tr>
        <th class="text-center" colspan="5">
            <i class="fa fa-fw fa-caret-down"></i> mover a <i class="fa fa-fw fa-caret-down"></i>
        </th>
    </tr>
    <tr>
        <th class="padding_lateral_5 bg-yura_dark">
            Fecha
        </th>
        <th class="padding_lateral_5 bg-yura_dark" colspan="4">
            Bodega
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Tallos
        </th>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                value="{{ hoy() }}" id="mover_fecha">
        </th>
        <th class="text-center" style="border-color: #9d9d9d" colspan="4">
            <select id="mover_bodega" class="padding_lateral_5" disabled
                style="width: 100%; height: 26px; background-color: #dddddd">
                <option value="V" {{ $model->bodega == 'P' ? 'selected' : '' }}>Ventas</option>
                <option value="P" {{ $model->bodega == 'V' ? 'selected' : '' }}>Producción</option>
            </select>
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                id="mover_tallos" value="0">
        </th>
    </tr>
</table>

<input type="hidden" id="inventario_mover" value="{{ $model->id_inventario_recepcion }}">

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-yura_primary" onclick="store_movimiento()">
        <i class="fa fa-fw fa-save"></i> GRABAR MOVIMIENTO
    </button>
</div>

<script>
    function store_movimiento() {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>MOVER</b> la flor del inventario?</h3></div>";

        modal_quest('modal_store_movimiento', texto, 'Mover inventario', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id_inventario: $('#inventario_mover').val(),
                mover_tallos: $('#mover_tallos').val(),
                mover_bodega: $('#mover_bodega').val(),
                mover_fecha: $('#mover_fecha').val(),
            }
            post_jquery_m('{{ url('ingreso_inventario/store_movimiento') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        })
    }
</script>
