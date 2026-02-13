<header class="main-header">
    <!-- Logo -->
    <a href="{{ url('') }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <img src="{{ url('images/Logo_Bench_Flow_B.png') }}" alt="" width="50px">
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <img src="{{ url('images/Logo_Bench_Flow_verde_negro.png') }}" alt="" width="80px">
        </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        @php
            $getSubmenusOfUser = getSubmenusOfUser(Session::get('id_usuario'));
        @endphp
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Menú</span>
        </a>
        <div class="dropdown hidden" style="padding: 15px 10px; float: left;" title="Reportes rápidos"
            id="div_submenu_crm_mini">
            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-list-alt text-color_yura"></i>
            </a>
            <ul class="dropdown-menu">
                <li title="Inicio">
                    <a href="javascript:void(0)" onclick="cargar_url('')">
                        Dashboards
                    </a>
                </li>
                @foreach ($getSubmenusOfUser as $item)
                    @if ($item->tipo == 'C')
                        <li title="{{ $item->nombre }}">
                            <a href="javascript:void(0)" onclick="cargar_url('{{ $item->url }}')">
                                {{ $item->nombre }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div id="div_submenu_crm" style="padding: 13px 10px; float: left;">
            @include('layouts.adminlte.partials.submenu_crm')
        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav" id="ul_navbar_superior">
                <li class="dropdown notifications-menu" id="li-master_cosecha">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="actualizar_cosecha_x_variedad('li_cosecha_hoy', false)">
                        <i class="fa fa-leaf text-color_yura"></i>
                    </a>
                    <ul class="dropdown-menu" style="">
                        <li>
                            <div id="li_cosecha_hoy" style="padding: 10px">

                            </div>
                        </li>
                    </ul>
                </li>
                <li class="dropdown notifications-menu" title="Notifícame" id="btn_notificaciones">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        onclick="buscar_notificaciones('S', false)">
                        <i class="fa fa-bell-o text-color_yura"></i>
                        <span class="label label-success" id="link_not"></span>
                    </a>
                    <ul class="dropdown-menu" style="width: 450px">
                        <li class="header text-center" id="header_not"></li>
                        <li>
                            <ul class="menu" id="list_not">
                            </ul>
                        </li>
                        {{-- <li class="footer"><a href="javascript:void(0)">Marcar todo como leído</a></li> --}}
                    </ul>
                </li>
                <li class="dropdown user user-menu" id="li-master_username">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        @php
                            $usuario = getUsuario(Session::get('id_usuario'));
                            $file_img = url('storage/imagenes') . '/' . $usuario->imagen_perfil;
                        @endphp
                        @if (file_exists($file_img))
                            <img src="{{ $file_img }}" class="user-image" alt="User Image"
                                id="img_perfil_menu_superior" title="{{ $usuario->nombre_completo }}">
                        @else
                            <i class="fa fa-fw fa-user" title="{{ $usuario->nombre_completo }}"></i>
                        @endif
                        <span class="hidden-xs text-color_yura" id="span-master_username">
                            {{ $usuario->nombre_completo }}
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-body text-center text-color_yura">
                            <small>Miembro desde {{ substr($usuario->fecha_registro, 0, 10) }}</small>
                        </li>
                        <li class="user-footer">
                            <div class="btn-group pull-left">
                                <button type="button" class="btn btn-yura_default" onclick="cargar_url('perfil')">
                                    Mi Perfil
                                </button>
                                @if (Session::get('tipo_rol') == 'P')
                                    <button type="button" class="btn btn-yura_dark" title="Reportes utiles"
                                        onclick="cargar_utiles()">
                                        <i class="fa fa-fw fa-code"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="pull-right">
                                <a href="javascript:void(0)" onclick="cargar_url('logout')"
                                    class="btn btn-yura_default">Salir</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" data-toggle="control-sidebar" onclick="cargar_chatbot()">
                        <i class="fa fa-comments text-color_yura"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>
