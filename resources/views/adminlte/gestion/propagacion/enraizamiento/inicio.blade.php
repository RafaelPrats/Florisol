@extends('layouts.adminlte.master')

@section('titulo')
    Enraizamiento
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Enraizamiento
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
        <div class="form-group input-group">
            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                <i class="fa fa-fw fa-calendar"></i>
            </div>
            <input type="date" id="fecha_search" name="fecha_search" value="{{date('Y-m-d')}}" required
                   class="form-control input-yura_default text-center" onchange="listar_enraizamientos();"
                   style="width: 100% !important;" max="{{date('Y-m-d')}}">
            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_enraizamientos()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs-justified nav-justified">
                <li><a href="#tab_listado" data-toggle="tab" aria-expanded="true">Resumen Enraizamiento</a></li>
                <li class="active"><a href="#tab_formulario" data-toggle="tab" aria-expanded="true">Ingresar</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane" id="tab_listado">
                    <div id="listado_siembras"></div>
                </div>
                <div class="tab-pane active" id="tab_formulario">
                    @include('adminlte.gestion.propagacion.enraizamiento.form.add_siembra')
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.propagacion.enraizamiento.script')
@endsection