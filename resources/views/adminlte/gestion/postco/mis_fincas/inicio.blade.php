@extends('layouts.adminlte.master')

@section('titulo')
    Fincas
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Fincas
            <small class="text-color_yura">módulo de postcosecha</small>
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
                <a href="javascript:void(0)" class="text-color_yura" onclick="cargar_url('{{ $submenu->url }}')">
                    <i class="fa fa-fw fa-refresh"></i> {{ $submenu->nombre }}
                </a>
            </li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div style="margin-top: 5px;" id="div_listado">
            @include('adminlte.gestion.postco.mis_fincas.partials.listado')
        </div>
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
    @include('adminlte.gestion.postco.mis_fincas.script')
@endsection
