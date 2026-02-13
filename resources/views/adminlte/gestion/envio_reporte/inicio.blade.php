@extends('layouts.adminlte.master')

@section('titulo')
    Envío de reportes
@endsection

@section('script_inicio')
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Envío de reportes
            <small class="text-color_yura">módulo administración</small>
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
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                @include('adminlte.gestion.envio_reporte.partials.listado_reportes')
            </div>
            <div class="col-md-6" id="div_listado_usuarios">
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.envio_reporte.script')
@endsection