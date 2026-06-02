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
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            TxR
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Ramos
        </th>
        <th class="padding_lateral_5 bg-yura_dark" style="width: 60px">
            Tallos
        </th>
        <th class="padding_lateral_5 bg-yura_dark">
            Bodega
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
                value="{{ $model->longitud }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="original_tallos_x_ramo"
                value="{{ $model->tallos_x_ramo }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="original_ramos"
                value="{{ $model->ramos }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%;" class="padding_lateral_5" id="original_disponibles"
                value="{{ $model->disponibles }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="original_bodega" class="padding_lateral_5" style="width: 100%; height: 26px;">
                <option value="V" {{ $model->bodega == 'V' ? 'selected' : '' }}>Ventas</option>
                <option value="P" {{ $model->bodega == 'P' ? 'selected' : '' }}>Producción</option>
            </select>
        </th>
    </tr>
    <tr>
        <th class="text-center" colspan="8">
            <i class="fa fa-fw fa-caret-down"></i> mover a <i class="fa fa-fw fa-caret-down"></i>
        </th>
    </tr>
    <tr>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="date" style="width: 100%; background-color: #dddddd" readonly class="padding_lateral_5"
                value="{{ $model->fecha }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%; background-color: #dddddd" class="padding_lateral_5" readonly
                value="{{ $model->variedad->planta->nombre }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%; background-color: #dddddd" class="padding_lateral_5" readonly
                value="{{ $model->variedad->nombre }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="text" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                id="mover_longitud" value="{{ $model->longitud }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                id="mover_tallos_x_ramo" value="{{ $model->tallos_x_ramo }}">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                id="mover_ramos" value="0">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <input type="number" style="width: 100%; background-color: #dddddd" class="padding_lateral_5"
                id="mover_disponibles" value="0">
        </th>
        <th class="text-center" style="border-color: #9d9d9d">
            <select id="mover_bodega" class="padding_lateral_5"
                style="width: 100%; height: 26px; background-color: #dddddd">
                <option value="V" {{ $model->bodega == 'P' ? 'selected' : '' }}>Ventas</option>
                <option value="P" {{ $model->bodega == 'V' ? 'selected' : '' }}>Producción</option>
            </select>
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
        datos = {
            _token: '{{ csrf_token() }}',
            id_inventario: $('#inventario_mover').val(),
            original_longitud: $('#original_longitud').val(),
            original_tallos_x_ramo: $('#original_tallos_x_ramo').val(),
            original_ramos: $('#original_ramos').val(),
            original_disponibles: $('#original_disponibles').val(),
            original_bodega: $('#original_bodega').val(),
            mover_longitud: $('#mover_longitud').val(),
            mover_tallos_x_ramo: $('#mover_tallos_x_ramo').val(),
            mover_ramos: $('#mover_ramos').val(),
            mover_disponibles: $('#mover_disponibles').val(),
            mover_bodega: $('#mover_bodega').val(),
        }
        post_jquery_m('{{ url('ingreso_inventario/store_movimiento') }}', datos, function() {
            cerrar_modals();
            listar_reporte();
        })
    }
</script>
