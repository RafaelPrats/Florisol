@extends('layouts.adminlte.master')

@section('titulo')
    Sectores y módulos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Sectores y módulos
            <small class="text-color_yura">módulo de administrador</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')"><i class="fa fa-home"></i>
                    Inicio</a></li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="nav-tabs-custom">
            <ul class="nav nav-pills nav-pills-justified nav-justified">
                <li class="active">
                    <a href="#tab_sectores" data-toggle="tab" aria-expanded="true">
                        Sectores y Módulos
                    </a>
                </li>
                <li>
                    <a href="#tab_ciclos" data-toggle="tab" aria-expanded="true">
                        Ciclos
                    </a>
                </li>
                <li>
                    <a href="#tab_new_ciclos" data-toggle="tab" aria-expanded="true" onclick="nuevos_ciclos()">
                        Nuevos Ciclos
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_sectores">
                </div>
                <div class="tab-pane" id="tab_ciclos">
                    <table style="width: 100%">
                        <tr>
                            <td>
                                <div class="input-group">
                                    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                        <i class="fa fa-fw fa-tree"></i> Sectores
                                    </div>
                                    <select id="filtro_ciclos_sector" class="form-control input-yura_default">
                                        <option value="T">Todos</option>
                                        @foreach ($sectores as $s)
                                            @if ($s->estado == 1)
                                                <option value="{{ $s->id_sector }}">{{ $s->nombre }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td class="td_filtro_planta" id="td_filtro_planta_N">
                                <div class="input-group">
                                    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                        <i class="fa fa-fw fa-leaf"></i> Variedad
                                    </div>
                                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta"
                                        class="form-control input-yura_default"
                                        onchange="select_planta($(this).val(), 'variedad_ciclos', 'variedad_ciclos',
                                        '<option value=>Seleccione</option>')">
                                        <option value="">Seleccione</option>
                                        @foreach ($plantas as $p)
                                            <option value="{{ $p->id_planta }}">{{ $p->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                        <i class="fa fa-fw fa-leaf"></i> Tipo
                                    </div>
                                    <select name="variedad_ciclos" id="variedad_ciclos"
                                        class="form-control input-yura_default">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                                        <i class="fa fa-fw fa-tree"></i> Tipo
                                    </div>
                                    <select name="tipo_ciclos" id="tipo_ciclos" class="form-control input-yura_default">
                                        <option value="1">Activos</option>
                                        <option value="0">Inactivos</option>
                                    </select>
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-yura_dark"
                                            onclick="listar_ciclos_sectores_modulos()">
                                            <i class="fa fa-fw fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div id="div_ciclos" style="margin-top: 10px;"></div>
                </div>
                <div class="tab-pane" id="tab_new_ciclos">
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.sectores_modulos.script')
@endsection
