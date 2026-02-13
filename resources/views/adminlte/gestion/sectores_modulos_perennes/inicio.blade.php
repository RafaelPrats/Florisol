@extends('layouts.adminlte.master')

@section('titulo')
    Sectores y módulos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Sectores y módulos
            <small class="text-color_yura">perennes</small>
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
        <table style="width: 100%">
            <tr>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Variedad
                        </span>
                        <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                                onchange="select_planta_global($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad', '<option value=T selected>Seleccione</option>')">
                            <option value="">Seleccione</option>
                            @foreach($plantas as $p)
                                <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-leaf"></i> Tipo
                        </span>
                        <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad"
                                class="form-control input-yura_default">
                            <option value="T" selected>Seleccione</option>
                        </select>
                    </div>
                </td>
                <td style="padding: 5px">
                    <div class="form-group input-group">
                        <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-check"></i> Estado
                        </span>
                        <select name="filtro_activos" id="filtro_activos" class="form-control input-yura_default">
                            <option value="1" selected>Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-yura_primary" onclick="listar_ciclos_sect_mod_perennes()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <div id="div_listado_ciclos"></div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.sectores_modulos_perennes.script')
@endsection