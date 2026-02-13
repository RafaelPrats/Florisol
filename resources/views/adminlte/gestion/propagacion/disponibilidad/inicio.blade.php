@extends('layouts.adminlte.master')

@section('titulo')
    Disponibilidad
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Disponibilidad
            <small>módulo de propagación</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="text-color_yura">
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li class="text-color_yura">
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Desde
                    </span>
                    <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                           name="filtro_predeterminado_desde" required
                           value="{{getSemanaByDate(opDiasFecha('-', 7, date('Y-m-d')))->codigo}}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Hasta
                    </span>
                    <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                           name="filtro_predeterminado_hasta" required
                           value="{{$semana_hasta->codigo}}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </span>
                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'div_cargar_variedades', '<option value=T selected>Seleccione</option>')">
                        <option value="" selected>Seleccione</option>
                        @foreach($plantas as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group input-group" id="div_cargar_variedades">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </span>
                    <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default">
                        <option value="T" selected>Seleccione</option>
                    </select>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary" onclick="listar_disponibilidades()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <div id="listado_disponibilidad"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.propagacion.disponibilidad.script')
@endsection