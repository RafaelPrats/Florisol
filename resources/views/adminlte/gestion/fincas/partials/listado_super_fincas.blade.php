<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_super_fincas">
    <thead>
    <tr>
        <th class="text-center bg-yura_dark" style="border-radius: 18px 18px 0 0" colspan="2">
            Empresas
        </th>
    </tr>
    <tr>
        <th class="th_yura_green" style="padding-left: 5px">
            Nombre
        </th>
        <th class="text-center th_yura_green" style="width: 80px">
            Opciones
        </th>
    </tr>
    </thead>
    <tr>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="nombre_sf_new" placeholder="Nombre" style="width: 100%; padding-left: 5px">
        </td>
        <td class="text-center" style="border-color: #9d9d9d">
            <button type="button" class="btn btn-yura_primary btn-xs" onclick="store_super_finca()">
                <i class="fa fa-fw fa-plus"></i>
            </button>
        </td>
    </tr>
    <tbody>
    @foreach($listado as $item)
        <tr>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="text" id="nombre_sf_{{$item->id_super_finca}}" value="{{$item->nombre}}" style="width: 100%; padding-left: 5px">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-yura_primary btn-xs" onclick="update_super_finca('{{$item->id_super_finca}}')">
                        <i class="fa fa-fw fa-edit"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<script>
    function store_super_finca() {
        datos = {
            _token: '{{csrf_token()}}',
            nombre: $('#nombre_sf_new').val().toUpperCase(),
        };
        post_jquery('{{url('fincas/store_super_finca')}}', datos, function () {
            listar_fincas();
            listar_super_fincas();
        });
    }

    function update_super_finca(sf) {
        datos = {
            _token: '{{csrf_token()}}',
            super_finca: sf,
            nombre: $('#nombre_sf_' + sf).val().toUpperCase(),
        };
        post_jquery('{{url('fincas/update_super_finca')}}', datos, function () {
            listar_fincas();
        });
    }
</script>