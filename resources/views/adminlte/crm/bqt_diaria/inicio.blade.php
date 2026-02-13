@extends('layouts.adminlte.master')

@section('titulo')
    Bouquetera diaria
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Reporte
            <small class="text-color_yura">Bouquetera diaria</small>
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
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </span>
                        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                                onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad', '<option value=T selected>Todos los tipos</option>')">
                            <option value="">Todas las variedades</option>
                            @foreach($plantas as $p)
                                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </span>
                        <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad"
                                class="form-control input-yura_default">
                            <option value="T" selected>Todos los tipos</option>
                        </select>
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        <i class="fa fa-fw fa-calendar"></i> Desde
                    </span>
                        <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde" required value="{{$desde}}">
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                    <span class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                        <i class="fa fa-fw fa-calendar"></i> Hasta
                    </span>
                        <input type="date" class="form-control input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required value="{{$hasta}}">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="buscar_bqt_diaria()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div id="div_listado_bqt_diaria" style="overflow-x: scroll; overflow-y: scroll; max-height: 450px"></div>
    </section>
@endsection

@section('script_final')

    @include('adminlte.crm.bqt_diaria.script')
@endsection