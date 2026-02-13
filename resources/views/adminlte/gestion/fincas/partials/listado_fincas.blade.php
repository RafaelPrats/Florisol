<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_fincas">
    <thead>
    <tr>
        <th class="text-center bg-yura_dark" style="border-radius: 18px 18px 0 0" colspan="3">
            Fincas
        </th>
    </tr>
    <tr>
        <th class="th_yura_green" style="padding-left: 5px">
            Nombre
        </th>
        <th class="th_yura_green" style="padding-left: 5px">
            Empresa
        </th>
        <th class="text-center th_yura_green" style="width: 80px">
            Opciones
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($listado as $item)
        <tr id="tr_finca_{{$item->id_configuracion_empresa}}">
            <td style="border-color: #9d9d9d; padding-left: 5px">
                {{$item->nombre}}
            </td>
            <td style="border-color: #9d9d9d;">
                <select id="id_super_finca_{{$item->id_configuracion_empresa}}" style="width: 100%">
                    <option value="">Seleccione</option>
                    @foreach($super_fincas as $sf)
                        <option value="{{$sf->id_super_finca}}" {{$sf->id_super_finca == $item->id_super_finca ? 'selected' : ''}}>
                            {{$sf->nombre}}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_primary btn-xs" onclick="update_finca('{{$item->id_configuracion_empresa}}')">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    function update_finca(finca) {
        datos = {
            _token: '{{csrf_token()}}',
            finca: finca,
            super_finca: $('#id_super_finca_' + finca).val(),
        };
        post_jquery('{{url('fincas/update_finca')}}', datos, function () {
        }, 'tr_finca_' + finca);
    }
</script>