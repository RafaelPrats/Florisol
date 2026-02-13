@extends('layouts.adminlte.master')
@section('titulo')
    Resumen Proyecciones
@endsection

@section('contenido')
    @include('adminlte.gestion.partials.breadcrumb')
    <section class="content">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Desde
                        </div>
                        <input type="number" class="form-control desde input-yura_default" id="filtro_predeterminado_desde"
                               name="filtro_predeterminado_desde"
                               style="" required value="{{$desde->codigo}}">
                    </div>
                </td>
                <td style="padding-left: 5px">
                    <div class="input-group">
                        <div class="input-group-addon span-input-group-yura-fixed bg-yura_dark">
                            <i class="fa fa-fw fa-calendar"></i> Hasta
                        </div>
                        <input type="number" class="form-control hasta input-yura_default" id="filtro_predeterminado_hasta"
                               name="filtro_predeterminado_hasta" required
                               value="{{$hasta->codigo}}" style="">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="listar_resumen_proyecciones()">
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-yura_primary" title="Exportar" onclick="exportar_reporte()">
                                <i class="fa fa-fw fa-file-excel-o"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="box-body" id="listado_proyecciones_resumen_total" style="width:100%;overflow-x: auto"></div>
    </section>
@endsection
@section('script_final')
    @include('adminlte.gestion.proyecciones.resumen_proyecciones.script')
@endsection
