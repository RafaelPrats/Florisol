@extends('layouts.adminlte.master')

@section('titulo')
    Clientes
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    @include('adminlte.gestion.partials.breadcrumb')
    <!-- Main content -->
    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    Administración de los clientes
                </h3>
            </div>
            <div class="box-body" id="div_content_clientes">
                <table width="100%" style="margin-bottom: 0">
                    <tr>
                        <td>
                            <div class="input-group">
                                <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                    Fecha
                                </div>
                                <input type="text" id="busqueda_clientes" name="busqueda_clientes" required
                                    placeholder="Búsqueda" class="form-control input-yura_default text-center"
                                    onchange="listar_reporte()" style="width: 100% !important;">
                                <div class="input-group-btn">
                                    <button class="btn btn-yura_dark" onclick="buscar_listado()">
                                        <i class="fa fa-fw fa-search"></i>
                                    </button>
                                    <button class="btn btn-yura_primary" onclick="add_cliente()">
                                        <i class="fa fa-fw fa-plus"></i>
                                    </button>
                                    <button class="btn btn-primary btn-yura_dark" onclick="add_importar_clientes()">
                                        <i class="fa fa-fw fa-upload"></i>
                                    </button>
                                    <button class="btn btn-yura_default" onclick="exportar_clientes()">
                                        <i class="fa fa-fw fa-file-excel-o"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div id="div_listado_clientes" style="margin-top: 5px"></div>
            </div>
        </div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postcocecha.clientes.script')
@endsection
