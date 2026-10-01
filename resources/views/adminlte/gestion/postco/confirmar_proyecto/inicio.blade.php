@extends('layouts.adminlte.master')

@section('titulo')
    Confirmar Pedidos
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Confirmar Pedidos
            <small class="text-color_yura">módulo de postcosecha</small>
        </h1>

        <ol class="breadcrumb">
            <li>
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('')">
                    <i class="fa fa-home"></i> Inicio
                </a>
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->grupo_menu->nombre }}
            </li>
            <li class="text-color_yura">
                {{ $submenu->menu->nombre }}
            </li>
            <li class="active">
                <a href="javascript:void(0)" class="text-color_yura" onclick="location.reload()">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div style="overflow-x: scroll">
            <table style="width: 100%">
                <tr>
                    <td>
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark span-input-group-yura-fixed">
                                Codigo Pedido
                            </div>
                            <input type="text" name="codigo_pedido" id="codigo_pedido"
                                class="form-control padding_lateral_5" style="width: 100%" value="{{ $proyecto }}">
                        </div>
                    </td>
                    <td id="div_filtro_variedad">
                        <div class="input-group">
                            <div class="input-group-addon bg-yura_dark">
                                Finca
                            </div>
                            <select name="finca_proveedor" id="finca_proveedor" class="form-control" style="width: 100%"
                                onchange="listar_reporte()">
                                @if (count($fincas) > 1)
                                    <option value="">Seleccione...</option>
                                @endif
                                @foreach ($fincas as $f)
                                    <option value="{{ $f->id_finca_proveedor }}">
                                        {{ $f->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-yura_primary" onclick="listar_reporte()">
                                    <i class="fa fa-fw fa-search"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-yura_default" onclick="exportar_reporte()">
                                    <i class="fa fa-fw fa-file-excel-o"></i>
                                </button> --}}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div style="margin-top: 5px;" id="div_listado"></div>
    </section>

    <style>
        .tr_fija_top_0 {
            position: sticky;
            top: 0;
            z-index: 9;
        }

        .tr_fija_top_1 {
            position: sticky !important;
            top: 21px !important;
            z-index: 9 !important;
        }

        .tr_fija_bottom_0 {
            position: sticky;
            bottom: 0;
            z-index: 9;
        }

        .select2-selection {
            height: 34px !important;
        }
    </style>
@endsection

@section('script_final')
    @include('adminlte.gestion.postco.confirmar_proyecto.script')
@endsection
