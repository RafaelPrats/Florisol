<div class="text-center">
    <table class="table-bordered table-striped" style="width: 100%; border: 2px solid #9d9d9d">
        <tr>
            <th class="text-center th_yura_green" style="border-color: white">
                Finca
            </th>
            <th class="text-center th_yura_green" style="border-color: white">
            </th>
        </tr>
        @foreach ($fincas as $f)
            <tr>
                <td class="text-center" style="border-color: #9d9d9d">
                    {{ $f->nombre }}
                </td>
                <td class="text-center" style="border-color: #9d9d9d">
                    <input type="checkbox" id="check_finca_proveedor_{{ $f->id_finca_proveedor }}"
                        class="mouse-hand checkbox_config_finca_proveedor {{ $f->id_finca_proveedor }}"
                        {{ in_array($f->id_finca_proveedor, $mis_fincas) ? 'checked' : '' }}>
                </td>
            </tr>
        @endforeach
    </table>
    <button type="button" class="btn btn-sm btn-yura_primary" style="margin-top: 10px"
        onclick="store_user_proveedor('{{ $usuario }}')">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function store_user_proveedor(id_usuario) {
        data = [];
        checkboxes = $('.checkbox_config_finca_proveedor');
        for (i = 0; i < checkboxes.length; i++) {
            if ($('#' + checkboxes[i].id).prop('checked') == true) {
                var id_emp = document.getElementById(checkboxes[i].id).classList[2];
                data.push(id_emp);
            }
        }
        datos = {
            _token: '{{ csrf_token() }}',
            user: id_usuario,
            data: JSON.stringify(data)
        };
        post_jquery_m('{{ url('usuarios/store_user_proveedor') }}', datos, function(retorno) {
            //cerrar_modals();
        });
    }
</script>
