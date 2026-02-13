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
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Desde
                    </span>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" required
                               value="{{$semana_desde->codigo}}">
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Hasta
                    </span>
                        <input type="number" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required
                               value="{{$semana_hasta->codigo}}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_ingreso_disponibilidad()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="listado_disponibilidad"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.propagacion.ingreso_disponibilidad.script')
@endsection