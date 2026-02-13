@extends('layouts.adminlte.master')

@section('titulo')
    Temperaturas
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Temperaturas
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
        <table style="width: 100%">
            <tr>
                <td class="padding_lateral_5">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="date" id="filtro_desde" class="form-control input-yura_default" value="{{$desde}}" max="{{hoy()}}">
                    </div>
                </td>
                <td class="padding_lateral_5">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="date" id="filtro_hasta" class="form-control input-yura_default" value="{{$hasta}}" max="{{hoy()}}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_graficas_temperaturas()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado_temperaturas" style="margin-top: 5px"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.proyecciones.dashboard_temperaturas.script')
@endsection