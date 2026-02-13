@extends('layouts.adminlte.master')

@section('titulo')
    Importar Unosoft
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <legend>
            <strong>Importar Unosoft</strong>
            <small>módulo de administrador</small>
        </legend>

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
                    <i class="fa fa-fw fa-refresh"></i> {{$submenu->nombre}}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <form id="form-importar_unosoft" action="{{url('importar_unosoft/importar')}}" method="POST">
            {!! csrf_field() !!}
            <div class="form-row">
                <div class="col-md-6 col-sm-12 col-xs-12 mt-2 mt-md-0">
                    <input type="file" name="file_importar" id="file_importar" class="form-control border-radius_18 input-yura_default">
                </div>
                <div class="col-md-3 col-sm-12 col-xs-12 mt-2 mt-md-0">
                    <select name="bouquetera" id="bouquetera" class="form-control border-radius_18 input-yura_default">
                        <option value="0">Normal</option>
                        <option value="1">Bouquetera</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-12 col-xs-12 mt-2 mt-md-0">
                    <div class="btn-group pull-right">
                        <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                            <i class="fa fa-fw fa-download"></i> Plantilla
                        </button>
                        <button type="button" class="btn btn-yura_primary" onclick="importar_unosoft()">
                            <i class="fa fa-fw fa-upload"></i> Importar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.importar_unosoft.script')
@endsection