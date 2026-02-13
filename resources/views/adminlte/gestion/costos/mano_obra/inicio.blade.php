@extends('layouts.adminlte.master')

@section('titulo')
    Costos - Mano de Obra
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('css_inicio')
    <style>
        tbody {
            font-size: 0.8em;
        }

        .tabla_master {
            border: 2px solid #9d9d9d;
        }

        .tabla_master thead tr th {
            background-color: #e9ecef;
        }

        .fila_fija th {
            position: sticky;
            top: 0;
        }
    </style>
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Mano de Obra
            <small>Gestión</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li>
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li>
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    <section class="content">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs nav-justified">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">
                        <i class="fa fa-fw fa-plus"></i> Ingresos
                    </a></li>
                <li class=""><a href="#tab_2" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-fw fa-edit"></i> Editar
                    </a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active"><a href="#tab_area" data-toggle="tab" aria-expanded="true">Area</a></li>
                            <li class=""><a href="#tab_actividad" data-toggle="tab" aria-expanded="true">Actividad</a></li>
                            <li class=""><a href="#tab_producto" data-toggle="tab" aria-expanded="true">Mano de Obra</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_area">
                                @include('adminlte.gestion.costos.mano_obra.partials.list_area')
                            </div>
                            <div class="tab-pane" id="tab_actividad">
                                @include('adminlte.gestion.costos.mano_obra.partials.list_actividad')
                            </div>
                            <div class="tab-pane" id="tab_producto">
                                @include('adminlte.gestion.costos.mano_obra.partials.list_producto')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab_2">
                    @include('adminlte.gestion.costos.mano_obra.partials.editar_costos')
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    <script>
        $('#vista_actual').val('gestion_mano_obra');
    </script>
@endsection