<div id="table_usuarios">
    @if(sizeof($listado)>0)
        <table width="100%" class="table table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d"
               id="table_content_usuarios">
            <thead>
            <tr style="background-color: #dd4b39; color: white">
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    NOMBRE COMPLETO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    CORREO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    USUARIO
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    ROL
                </th>
                <th class="text-center table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}" style="border-color: #9d9d9d">
                    OPCIONES
                </th>
            </tr>
            </thead>
            @foreach($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                    class="{{$item->estado == 'A'?'':'error'}}" id="row_usuarios_{{$item->id_usuario}}">
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->nombre_completo}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->correo}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->username}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">{{$item->rol}}</td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Detalles"
                                    onclick="ver_usuario('{{$item->id_usuario}}')" id="btn_view_usuario_{{$item->id_usuario}}">
                                <i class="fa fa-fw fa-eye" style="color: black"></i>
                            </button>
                            @if(getUsuario($item->id_usuario)->rol()->tipo == 'S')
                                <button type="button" class="btn btn-xs btn-yura_danger"
                                        title="{{$item->estado == 'A' ? 'Desactivar' : 'Activar'}}"
                                        onclick="eliminar_usuario('{{$item->id_usuario}}', '{{$item->estado}}')"
                                        id="btn_usuarios_{{$item->id_usuario}}">
                                    <i class="fa fa-fw {{$item->estado == 'A' ? 'fa-lock' : 'fa-unlock'}}" style="color: black"
                                       id="icon_usuarios_{{$item->id_usuario}}"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-xs btn-yura_primary text-white" title="Fincas"
                                    onclick="config_user_finca('{{$item->id_usuario}}')">
                                <i class="fa fa-fw fa-leaf"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>

<script>
    function config_user_finca(user) {
        datos = {
            user: user
        };
        $.LoadingOverlay('show');
        get_jquery('{{url('usuarios/config_user_finca')}}', datos, function (retorno) {
            modal_view('modal-view_config_user_finca', retorno, '<i class="fa fa-fw fa-leaf"></i> Configurar las fincas del usuario', true, false, '45%');
        });
        $.LoadingOverlay('hide');
    }
</script>