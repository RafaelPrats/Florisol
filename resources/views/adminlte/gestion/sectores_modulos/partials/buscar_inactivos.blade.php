<div class="input-group">
    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
        Sector
    </div>
    <select id="search_sector" class="form-control input-yura_default">
        <option value="">Todos</option>
        @foreach($sectores as $item)
            <option value="{{$item->id_sector}}">{{$item->nombre}}</option>
        @endforeach
    </select>
    <div class="input-group-addon bg-yura_dark">
        Módulo
    </div>
    <input type="text" id="search_inactivos" class="form-control input-yura_default text-center" placeholder="Módulo">
    <div class="input-group-btn">
        <button type="button" class="btn btn-yura_dark" onclick="buscar_modulos_inactivos()">
            <i class="fa fa-fw fa-search"></i>
        </button>
    </div>
</div>

<div id="div_listado_modulos_inactivos" style="margin-top: 10px"></div>

<script>
    function buscar_modulos_inactivos() {
        datos = {
            sector: $('#search_sector').val(),
            nombre: $('#search_inactivos').val(),
        };
        if (datos['sector'] != '' || datos['nombre'] != '') {
            get_jquery('{{url('sectores_modulos/buscar_modulos_inactivos')}}', datos, function (retorno) {
                $('#div_listado_modulos_inactivos').html(retorno);
                estructura_tabla('table_listado_ciclos', false, true);
                $('#table_listado_ciclos_length label').addClass('text-color_yura');
                $('#table_listado_ciclos_length label select').addClass('input-yura_white');
                //$('#table_listado_ciclos_length label select').val(50);
                $('#table_listado_ciclos_filter label').addClass('text-color_yura');
                $('#table_listado_ciclos_filter label input').addClass('input-yura_white');
            });
        } else {
            alerta('<div class="alert alert-info text-center">Faltan los valores de búsqueda</div>');
        }
    }
</script>