@extends('layouts.adminlte.master')

@section('titulo')
    Proyecciones
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
            <small class="text-color_yura">administración</small>
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
                <div class="input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Año
                    </span>
                    <select name="filtro_predeterminado_anno" id="filtro_predeterminado_anno" class="form-control input-yura_default">
                        @foreach($annos as $a)
                            <option value="{{$a->anno}}" {{$a->anno == date('Y') ? 'selected' : ''}}>{{$a->anno}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </span>
                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad', '<option value=T selected>Seleccione</option>')">
                        <option value="">Seleccione</option>
                        @foreach($plantas as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </span>
                    <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default">
                        <option value="T" selected>Seleccione</option>
                    </select>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary" onclick="listar_ingreso_proyecciones()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                        <button type="button" class="btn btn-yura_dark" onclick="copiar_semanas()" title="Copiar Semanas">
                            <i class="fa fa-fw fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-yura_default" onclick="generar_semanas()" title="Generar Semanas">
                            <i class="fa fa-fw fa-calendar-plus-o"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <div id="div_listado_proyecciones"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.proyecciones.proyecciones.script')
@endsection