@extends('layouts.adminlte.master')

@section('titulo')
    Codigos Autorizacion
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Codigos Autorizacion
            <small class="text-color_yura">módulo de administracion</small>
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
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <table class="table table-bordered" style="border: 1px solid #9d9d9d; width: 100%">
            <tr>
                <th class="text-center th_yura_green">
                    ACCION
                </th>
                <th class="text-center th_yura_green" style="width: 50%">
                    CODIGO
                </th>
            </tr>
            @foreach ($codigos as $item)
                <tr>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->descripcion }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <input type="text" value="{{ $item->valor }}" style="width: 100%"
                            data-id="{{ $item->id_codigo_autorizacion }}" class="text-center form-control input_valor"
                            id="valor_{{ $item->id_codigo_autorizacion }}">
                    </th>
                </tr>
            @endforeach
        </table>
        <div class="text-center" style="margin-top: 5px">
            <button type="button" class="btn btn-yura_primary" onclick="store_codigos()">
                <i class="fa fa-fw fa-save"></i> GRABAR CODIGOS
            </button>
        </div>
    </section>

    <style>
        #tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .columna_fija_left_0 {
            position: sticky;
            left: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.codigo_autorizacion.script')
@endsection
