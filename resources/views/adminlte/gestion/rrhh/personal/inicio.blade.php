@extends('layouts.adminlte.master')

@section('titulo')
    Personal
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Personal
            <small>módulo de rrhh</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i>
                    Inicio</a></li>
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
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">
                Administración del personal
            </h3>
        </div>
        <div class="box-body" id="div_content_clientes">
            <table width="100%">
                <div class="row">
              
                   
                    <div class="col-md-3">
                        <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-user"></i>
                         </span>
                            <input minlength="3" type="text" class="form-control input-yura_default"
                                   placeholder="Búsqueda" id="busqueda_personal"
                                   name="busqueda_personal">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group input-group">
                    <span class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                        <i class="fa fa-fw fa-circle-o-notch"></i> estado
                         </span>
                            <select name="estado" id="estado" class="form-control input-yura_default">
                                <option value='1'>Activo</option>
                                <option value='0'>Inactivo</option>
                            </select>
                            <span class="input-group-btn">
                                       <button type="button" class="btn btn-yura_primary" onclick="trabajador()">
                            <i class="fa fa-fw fa-search"></i>
                        </button>
                        </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-yura_primary pull-right" onclick="add_personal()">
                            <i class="fa fa-fw fa-plus" style="color: #0c0c0c"></i> Añadir
                        </button>
                    </div>
                    <div class="col-md-2">
                    <button class="btn btn-yura_primary" onclick="exportar_personal()"
                            onmouseover="$('#title_btn_exportar').html('Exportar')"
                            onmouseleave="$('#title_btn_exportar').html('')">
                        <i class="fa fa-fw fa-file-excel-o" style="color: #ffff" ></i> <em id="title_btn_exportar"></em>
                    </button>
                    </div>

            </table>

            <div id="div_listado_personal" style="margin-top: 10px"></div>
        </div>
    </div>
@endsection
@section('script_final')
    @include('adminlte.gestion.rrhh.personal.script')
@endsection
