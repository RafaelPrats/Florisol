<table class="w-100">
    <tr>
        <td>
            <div class="input-group">
                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                    Precio
                </div>
                <input type="number" id="objetivo_precio" value="{{ $empresa->objetivo_precio }}" required
                    class="form-control input-yura_default text-center" style="width: 100% !important;">
                <div class="input-group-addon bg-yura_dark">
                    <i class="fa fa-fw fa-leaf"></i> Tallos
                </div>
                <input type="number" id="objetivo_tallos" value="{{ $empresa->objetivo_tallos }}" required
                    class="form-control input-yura_default text-center" style="width: 100% !important;">
                <div class="input-group-addon bg-yura_dark">
                    Costos Fijos
                </div>
                <input type="number" id="objetivo_costos_fijos" value="{{ $empresa->objetivo_costos_fijos }}" required
                    class="form-control input-yura_default text-center" style="width: 100% !important;">
                <div class="input-group-addon bg-yura_dark">
                    Costos Variables
                </div>
                <input type="number" id="objetivo_costos_variables" value="{{ $empresa->objetivo_costos_variables }}"
                    required class="form-control input-yura_default text-center" style="width: 100% !important;">
                <div class="input-group-addon bg-yura_dark">
                    Flor Comprada
                </div>
                <input type="number" id="objetivo_flor_comprada" value="{{ $empresa->objetivo_flor_comprada }}"
                    required class="form-control input-yura_default text-center" style="width: 100% !important;">
                <div class="input-group-btn">
                    <button type="button" class="btn btn-yura_primary" onclick="update_objetivos()">
                        <i class="fa fa-fw fa-save"></i> Grabar
                    </button>
                </div>
            </div>
        </td>
    </tr>
</table>

<script>
    function update_objetivos() {
        datos = {
            _token: '{{ csrf_token() }}',
            objetivo_precio: $('#objetivo_precio').val(),
            objetivo_tallos: $('#objetivo_tallos').val(),
            objetivo_costos_fijos: $('#objetivo_costos_fijos').val(),
            objetivo_costos_variables: $('#objetivo_costos_variables').val(),
            objetivo_flor_comprada: $('#objetivo_flor_comprada').val(),
        };
        post_jquery_m('{{ url('intervalo_indicador/update_objetivos') }}', datos, function(retorno) {

        });
    }
</script>
