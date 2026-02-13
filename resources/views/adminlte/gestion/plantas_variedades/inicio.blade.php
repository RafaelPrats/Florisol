@extends('layouts.adminlte.master')

@section('titulo')
    Plantas y variedades
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Plantas y variedades
            <small class="text-color_yura">módulo de administrador</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura"><i class="fa fa-home"></i>
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
        <div class="box box-success">
            <div class="box-body" id="div_content_menu_sistema">
                <input type="hidden" id="planta_seleccionada">
                <input type="hidden" id="proveedor_seleccionada">
                <div class="row">
                    <div class="col-md-3" id="div_content_proveedores" style="overflow-y: scroll; max-height: 650px">
                        @include('adminlte.gestion.plantas_variedades.partials.listado_proveedores')
                    </div>
                    <div class="col-md-3" id="div_content_grupos_menu" style="overflow-y: scroll; max-height: 650px">
                        @include('adminlte.gestion.plantas_variedades.partials.listado_plantas')
                    </div>
                    <div class="col-md-6" id="div_content_menus" style="overflow-y: scroll; height: 650px">
                        <table width="100%" class="table-responsive table-bordered"
                            style="font-size: 0.8em; border-color: #9d9d9d" id="table_content_variedades">
                            <thead>
                                <tr>
                                    <th class="text-center th_yura_green">VARIEDAD</th>
                                    <th class="text-center th_yura_green">
                                        @if (count($plantas) > 0)
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-xs btn-yura_default"
                                                    title="Añadir Planta" onclick="add_variedad()">
                                                    <i class="fa fa-fw fa-plus"></i>
                                                </button>
                                                <button class="btn btn-xs btn-yura_dark" onclick="importar_recetas()">
                                                    <i class="fa fa-fw fa-upload"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tr>
                                <td class="text-center" style="border-color: #9d9d9d" colspan="2">
                                    No se ha seleccionado ninguna planta
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }
        .tr_fija_top_1 {
            position: sticky;
            top: 20px;
            z-index: 8;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.plantas_variedades.script')
@endsection
