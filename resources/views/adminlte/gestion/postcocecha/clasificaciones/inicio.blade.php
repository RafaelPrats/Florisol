@extends('layouts.adminlte.master')

@section('titulo')
    Clasificaciones
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Clasificaciones
            <small class="text-color_yura">parametrización</small>
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
        <ul class="nav nav-pills nav-justified" id="custom-tabs-three-tab" role="tablist">
            <li class="nav-item active">
                <a class="nav-link" data-toggle="pill" href="#tab_ramos" role="tab" onclick="listar_ramos()"
                   aria-controls="custom-tabs-three-profile" aria-selected="false">
                    RAMOS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#tab_presentaciones" role="tab" onclick="listar_presentaciones()"
                   aria-controls="custom-tabs-three-profile" aria-selected="false">
                    PRESENTACIONES
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#tab_cajas" role="tab" onclick="listar_cajas()"
                   aria-controls="custom-tabs-three-profile" aria-selected="false">
                    CAJAS
                </a>
            </li>
        </ul>
        <div class="tab-content" id="custom-tabs-three-tabContent">
            <div class="tab-pane active in" id="tab_ramos" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab"></div>
            <div class="tab-pane fade" id="tab_presentaciones" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab"></div>
            <div class="tab-pane fade" id="tab_cajas" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab"></div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.clasificaciones.script')
@endsection