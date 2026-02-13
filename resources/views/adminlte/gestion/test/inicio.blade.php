@extends('layouts.adminlte.master')

@section('titulo')
    Proyecciones de Ganadería
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Proyecciones
            <small class="text-color_yura">Ganadería</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Desde
                    </span>
                    <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                           name="filtro_predeterminado_desde" required value="{{$desde}}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Hasta
                    </span>
                    <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                           name="filtro_predeterminado_hasta" required value="{{$hasta}}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-bomb"></i> Grupo
                    </span>
                    <select class="form-control input-yura_default" id="filtro_grupos">
                        <option value="">Seleccione...</option>
                        @for($g = 0; $g < 25; $g++)
                            <option value="{{$g}}">GRUPO - {{rand(0, 99)}}</option>
                        @endfor
                    </select>
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-yura_dark" onclick="buscar_test()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="div_listado_proyecciones">
        </div>
    </section>
@endsection

@section('script_final')
    <script>
        function buscar_test() {
            if ($('#filtro_grupos').val() != '') {
                datos = {
                    desde: $('#filtro_predeterminado_desde').val(),
                    hasta: $('#filtro_predeterminado_hasta').val(),
                };
                get_jquery('{{url('proy_ganaderia/test')}}', datos, function (retorno) {
                    $('#div_listado_proyecciones').html(retorno);
                })
            } else {
                alerta('<div class="alert alert-info text-center">Debe seleccionar un grupo</div>');
            }
        }
    </script>
@endsection
