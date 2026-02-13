@extends('layouts.adminlte.master')

@section('titulo')
    Distribución Bqt
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Distribución
            <small class="text-color_yura">bouquetera</small>
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
        <div class="input-group">
            <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                <i class="fa fa-fw fa-calendar"></i> Desde
            </div>
            <input type="number" id="desde_search" class="form-control text-center input-yura_default" value="{{$desde->codigo}}"
                   required>
            <div class="input-group-addon bg-yura_dark">
                <i class="fa fa-fw fa-calendar"></i> Hasta
            </div>
            <input type="number" id="hasta_search" class="form-control text-center input-yura_default" value="{{$hasta->codigo}}"
                   required>

            <div class="input-group-addon bg-yura_dark">
                <i class="fa fa-fw fa-leaf"></i> Variedad
            </div>
            <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default">
                <option value="T">Todas las variedades</option>
                @foreach($plantas as $p)
                    <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                @endforeach
            </select>

            <div class="input-group-btn">
                <button type="button" class="btn btn-yura_primary" onclick="listar_distribucion_bqt()">
                    <i class="fa fa-fw fa-search"></i>
                </button>
            </div>
        </div>

        <div id="div_listado_distribucion_bqt" style="margin-top: 5px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.bouquetera.distribucion_bqt.script')
@endsection