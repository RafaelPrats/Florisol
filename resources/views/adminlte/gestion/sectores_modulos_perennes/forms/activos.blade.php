<table class="table-bordered" style="width: 100%; border-radius: 18px 18px 0 0">
    <tr>
        <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
            Módulo
        </th>
        <th class="text-center th_yura_green">
            Inicio
        </th>
        <th class="text-center th_yura_green">
            Área
        </th>
        <th class="text-center th_yura_green">
            Ptas Iniciales
        </th>
        <th class="text-center th_yura_green">
            Final
        </th>
        <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0">
            Opciones
        </th>
    </tr>
    @foreach($ciclos as $c)
        <tr id="tr_ciclo_{{$c->id_ciclo}}">
            <td class="text-center" style="border-color: #9d9d9d">
                {{$c->modulo->nombre}}
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="date" id="fecha_inicio_{{$c->id_ciclo}}" style="width: 100%" value="{{$c->fecha_inicio}}" class="text-center"
                       required>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="area_{{$c->id_ciclo}}" style="width: 100%" value="{{$c->area}}" class="text-center" required>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="plantas_iniciales_{{$c->id_ciclo}}" style="width: 100%" value="{{$c->plantas_iniciales}}" required
                       class="text-center">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="date" id="fecha_fin_{{$c->id_ciclo}}" style="width: 100%" value="{{$c->fecha_fin}}" class="text-center" required>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_ciclo('{{$c->id_ciclo}}')">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_dark" onclick="ver_ciclos_historicos('{{$c->id_modulo}}')">
                        <i class="fa fa-fw fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_danger" onclick="reiniciar_ciclo('{{$c->id_ciclo}}')">
                        <i class="fa fa-fw fa-times"></i>
                    </button>
                </div>
            </td>
        </tr>
    @endforeach
</table>

<script>
    function update_ciclo(id) {
        datos = {
            _token: '{{csrf_token()}}',
            ciclo: id,
            fecha_inicio: $('#fecha_inicio_' + id).val(),
            area: $('#area_' + id).val(),
            variedad: $('#filtro_predeterminado_variedad').val(),
            plantas_iniciales: $('#plantas_iniciales_' + id).val(),
            fecha_fin: $('#fecha_fin_' + id).val(),
        };
        post_jquery('{{url('sectores_modulos_perennes/update_ciclo')}}', datos, function () {

        }, 'tr_ciclo_' + id);
    }

    function reiniciar_ciclo(id) {
        modal_quest('modal-quest_reiniciar_ciclo',
            '<div class="alert alert-info text-center">¿Está seguro de <strong>REINICIAR</strong> el ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false, '50%', function () {
                datos = {
                    _token: '{{csrf_token()}}',
                    ciclo: id,
                    fecha_fin: $('#fecha_fin_' + id).val(),
                    variedad: $('#filtro_predeterminado_variedad').val(),
                };
                post_jquery('{{url('sectores_modulos_perennes/reiniciar_ciclo')}}', datos, function () {
                    listar_ciclos_sect_mod_perennes();
                    cerrar_modals();
                }, 'tr_ciclo_' + id);
                cerrar_modals();
            });
    }

    function ver_ciclos_historicos(mod) {
        datos = {
            modulo: mod,
        };
        get_jquery('{{url('sectores_modulos_perennes/ver_ciclos_historicos')}}', datos, function (retorno) {
            modal_view('modal-view_ver_ciclos_historicos', retorno, '<i class="fa fa-fw fa-refresh"></i> Ciclos', true, false, '{{isPC() ? '95%' : ''}}');
        });
    }
</script>