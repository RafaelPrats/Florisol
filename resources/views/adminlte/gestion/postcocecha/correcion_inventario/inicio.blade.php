@extends('layouts.adminlte.master')

@section('titulo')
    Correcion de Inventario
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Correcion de Inventario
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" onclick="cargar_url('')" class="text-color_yura">
                    <i class="fa fa-home text-color_yura"></i>
                    Inicio
                </a>
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{ $submenu->url }}')" class="text-color_yura">
                    <i class="fa fa-fw fa-refresh text-color_yura"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="div_content_recepciones">
            <table width="100%" style="margin-bottom: 0;">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                <i class="fa fa-fw fa-barcode"></i> Escanear Codigo
                            </div>
                            <input type="text" id="filtro_codigo_barra" required
                                class="form-control input-yura_default text-center" autofocus
                                style="width: 100% !important;" onchange="escanear_codigo()">
                        </div>
                    </td>
                </tr>
            </table>
            <div style="overflow-x: scroll; width: 100%">
                <table style="width:100%; margin-top: 5px">
                    <tr>
                        <td style="vertical-align: top; padding-right: 5px" id="td_inventarios">
                            <div class="panel panel-success" style="margin-bottom: 0px; min-width: 650px;"
                                id="panel_inventarios">
                                <div class="panel-heading"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <b> <i class="fa fa-gift"></i> INVENTARIO </b>
                                    <div class="btn-group">
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-yura_primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                Acciones <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right sombra_pequeña">
                                                <li>
                                                    <a href="javascript:void(0)" onclick="corregir_inventario(1)">
                                                        Corregir todo el inventario
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="javascript:void(0)" onclick="corregir_inventario(2)">
                                                        Corregir solo lo seleccionado
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body" id="body_contenido_caja" style="max-height: 500px">
                                    <table class="table-bordered"
                                        style="width: 100%; border: 1px solid #9d9d9d; margin-top: 5px">
                                        <tr>
                                            <th class="text-center th_yura_green">
                                                Variedad
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Longitud
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Tallos
                                            </th>
                                            <th class="text-center th_yura_green">
                                                Edad
                                            </th>
                                            <th class="text-center th_yura_green" style="width: 80px">
                                                Tallos Correccion
                                            </th>
                                            <th class="text-center th_yura_green" style="width: 80px">
                                            </th>
                                        </tr>
                                        <tbody id="table_caja"></tbody>
                                        <tr>
                                            <th class="text-center bg-yura_dark" colspan="2">
                                                Totales
                                            </th>
                                            <th class="text-center bg-yura_dark" id="td_total_tallos_caja">
                                            </th>
                                            <th class="text-center bg-yura_dark">
                                            </th>
                                            <th class="text-center bg-yura_dark" id="td_total_ramos_caja">
                                            </th>
                                            <th class="text-center bg-yura_dark">
                                            </th>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                        <td style="vertical-align: top; padding-left: 5px;" id="td_seleccionados">
                            <div class="panel panel-success" style="margin-bottom:0px; min-width: 340px;"
                                id="panel_seleccionados">
                                <div class="panel-heading"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <b> <i class="fa fa-th"></i> DETALLE DE LA LECTURA</b>
                                </div>
                                <div class="panel-body" style="max-height: 500px; overflow:auto" id="body_escaneado">
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
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
    @include('adminlte.gestion.postcocecha.correcion_inventario.script')
@endsection
