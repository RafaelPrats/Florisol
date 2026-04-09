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
                                    Búsqueda
                                </div>
                                <input type="text" id="busqueda_clientes" name="busqueda_clientes" required
                                    placeholder="Búsqueda" class="form-control text-center"
                                    onchange="listar_reporte()" style="width: 100% !important;">
                            </div>
                        </td>
                        <td style="width: 350px">
                            <div class="input-group">
                                <div class="input-group-addon bg-yura_dark">
                                    Segmento
                                </div>
                                <select id="filtro_segmento" name="filtro_segmento" required
                                    class="form-control input-yura_default" onchange="buscar_listado()"
                                    style="width: 100% !important;">
                                    <option value="">Todos</option>
                                    @foreach ($segmentos as $segmento)
                                        <option value="{{ $segmento }}">
                                            {{ $segmento }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="input-group-btn">
                                    <button class="btn btn-yura_dark" onclick="buscar_listado()">
                                        <i class="fa fa-fw fa-search"></i>
                                    </button>
                                    <button class="btn btn-yura_primary" onclick="add_cliente()">
                                        <i class="fa fa-fw fa-plus"></i>
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
