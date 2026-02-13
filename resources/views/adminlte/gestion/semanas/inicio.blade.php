@extends('layouts.adminlte.master')

@section('titulo')
    Semanas
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Semanas
            <small class="text-color_yura">módulo de administrador</small>
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
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Administración de las semanas de cultivo
                </h3>

                <div class="pull-right">
                    <select name="accion" id="accion" class="input-yura_default" onchange="select_accion($(this).val())">
                        <option value="1">Filtrar</option>
                        @if(getUsuario(Session::get('id_usuario'))->rol()->tipo == 'P')
                            <option value="2">Procesar</option>
                        @endif
                        <option value="3">Copiar semanas</option>
                    </select>
                </div>
            </div>
            <div class="box-body">
                <form id="form-accions">
                    <div id="div_content_form_accions"></div>
                </form>
                <div id="div_content_semanas" style="margin-top: 10px; overflow-y: scroll; overflow-x: scroll; max-height: 450px"></div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.semanas.script')
@endsection