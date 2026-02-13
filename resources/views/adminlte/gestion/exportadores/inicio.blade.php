@extends('layouts.adminlte.master')

@section('titulo')
    Exportadores
@endsection

@section('contenido')
    @include('adminlte.gestion.partials.breadcrumb')
    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-plane"></i> Administración de exportadores
                </h3>
                <table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
                    <tr>
                        <th class="text-center th_yura_green">
                            Nombre
                        </th>
                        <th class="text-center th_yura_green">
                            Identificación
                        </th>
                        <th class="text-center th_yura_green">
                            Código externo
                        </th>
                        <th class="text-center th_yura_green">
                        </th>
                    </tr>               
                    <tr>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="text" class="text-center form-control" style="width: 100%" id="nombre">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" class="text-center form-control" style="width: 100%" id="identificacion">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" class="text-center form-control" style="width: 100%" id="codigo_externo">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <button class="btn btn-yura_primary" onclick="store_exportador()">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    <tbody id="div_exportadores"></tbody>
                </table>
            </div>
            
        </div>
    </section>
@endsection


@section('script_final')
    @include('adminlte.gestion.exportadores.script')
@endsection