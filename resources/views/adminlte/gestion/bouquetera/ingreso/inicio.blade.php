@extends('layouts.adminlte.master')

@section('titulo')
    Ingreso Bouquetera
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Ingreso Bouquetera
            <small class="text-color_yura">módulo de postcosecha</small>
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
        <div class="row">
            <div class="col-md-12" id="div_listado">
                <div class="input-group">
                    <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-calendar"></i> Fecha
                    </div>
                    <input type="date" id="fecha_search" class="form-control text-center input-yura_default" value="{{hoy()}}"
                           onchange="listar_ingresos_bqt()" required>

                    <div class="input-group-addon bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Variedad
                    </div>
                    <select name="filtro_predeterminado_planta" id="filtro_predeterminado_planta" class="form-control input-yura_default"
                            onchange="select_planta($(this).val(), 'filtro_predeterminado_variedad', 'filtro_predeterminado_variedad', '<option value=T selected>Todos los tipos</option>')">
                        <option value="T">Todas las variedades</option>
                        @foreach($plantas as $p)
                            <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                        @endforeach
                    </select>

                    <div class="input-group-addon bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Tipo
                    </div>
                    <select name="filtro_predeterminado_variedad" id="filtro_predeterminado_variedad" class="form-control input-yura_default">
                        <option value="T" selected>Seleccione una variedad</option>
                    </select>

                    <div class="input-group-addon bg-yura_dark">
                        <i class="fa fa-fw fa-leaf"></i> Finca
                    </div>
                    <select name="filtro_predeterminado_empresa" id="filtro_predeterminado_empresa" class="form-control input-yura_default">
                        <option value="T">Todas las fincas</option>
                        <option value="-1">Comprada</option>
                        @foreach($fincas as $f)
                            <option value="{{$f->id_configuracion_empresa}}">{{$f->nombre}}</option>
                        @endforeach
                    </select>

                    <div class="input-group-btn">
                        <button type="button" class="btn btn-yura_primary" onclick="listar_ingresos_bqt()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                        <button type="button" id="btn_mostrar_formulario" class="btn btn-yura_default" onclick="mostrar_formulario()"
                                title="Mostrar formulario de ingreso">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                        <button type="button" id="btn_ocultar_formulario" class="btn btn-yura_default hidden" onclick="ocultar_formulario()"
                                title="Ocultar formulario de ingreso">
                            <i class="fa fa-fw fa-list"></i>
                        </button>
                        <button type="button" class="btn btn-yura_dark" onclick="importar_file_bqt()" title="Importar archivo">
                            <i class="fa fa-fw fa-upload"></i>
                        </button>
                    </div>
                </div>
                <div id="div_listado_ingreso_bqt" style="margin-top: 5px"></div>
            </div>
            <div class="col-md-7 hidden" id="div_formulario">
                @include('adminlte.gestion.bouquetera.ingreso.forms.add')
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.bouquetera.ingreso.script')
@endsection