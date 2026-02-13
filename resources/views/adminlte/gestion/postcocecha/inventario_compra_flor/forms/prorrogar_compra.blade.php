<div class="input-group">
    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
        Fecha
    </div>
    <input type="date" name="fecha_prorroga" id="fecha_prorroga" class="form-control text-center"
        value="{{ opDiasFecha('+', 1, hoy()) }}" min="{{ opDiasFecha('+', 1, hoy()) }}">
    <div class="input-group-addon bg-yura_dark">
        Cantidad
    </div>
    <input type="number" name="cantidad_prorroga" id="cantidad_prorroga" class="form-control text-center" value="1"
        min="1">
    <div class="input-group-btn">
        <button type="button" class="btn btn-yura_primary" onclick="store_prorroga()">
            <i class="fa fa-fw fa-save"></i> Grabar
        </button>
    </div>
</div>

<input type="hidden" id="proveedor_prorroga" value="{{ $proveedor }}">
<input type="hidden" id="variedad_prorroga" value="{{ $variedad }}">
<input type="hidden" id="longitud_prorroga" value="{{ $longitud }}">

<script>
    function store_prorroga() {
        datos = {
            _token: '{{ csrf_token() }}',
            fecha: $('#fecha_prorroga').val(),
            cantidad: $('#cantidad_prorroga').val(),
            proveedor: $('#proveedor_prorroga').val(),
            variedad: $('#variedad_prorroga').val(),
            longitud: $('#longitud_prorroga').val(),
        }
        post_jquery_m('{{ url('inventario_compra_flor/store_prorroga') }}', datos, function() {
            cerrar_modals();
            listar_inventario_compra_flor();
        });
    }
</script>
