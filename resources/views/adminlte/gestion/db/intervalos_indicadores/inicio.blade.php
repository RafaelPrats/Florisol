@extends('layouts.adminlte.master')

@section('titulo')
    Semaforización
@endsection

@section('contenido')
    <!-- Content Header (Page header) -->
    @include('adminlte.gestion.partials.breadcrumb')
    <!-- Main content -->
    <section class="content">
        <div class="nav-tabs-custom">
            <ul class="nav nav-pills nav-pills-justified nav-justified">
                <li class="active"><a href="#tab_intervalos" data-toggle="tab" aria-expanded="true">Indicadores</a></li>
                <li><a href="#tab_objetivos" data-toggle="tab" aria-expanded="true">Objetivos</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_intervalos">
                    @include('adminlte.gestion.db.intervalos_indicadores.partials.tab_intervalos')
                </div>
                <div class="tab-pane" id="tab_objetivos">
                    @include('adminlte.gestion.db.intervalos_indicadores.partials.tab_objetivos')
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    @include('adminlte.gestion.db.intervalos_indicadores.script')
@endsection
