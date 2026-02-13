@extends('layouts.adminlte.master')

@section('titulo')
    Costos - Gestión
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('css_inicio')
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            Costos
            <small>Importar</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)" onclick="cargar_url('')"><i class="fa fa-home"></i> Inicio</a></li>
            <li>
                {{$submenu->menu->grupo_menu->nombre}}
            </li>
            <li>
                {{$submenu->menu->nombre}}
            </li>

            <li class="active">
                <a href="javascript:void(0)" onclick="cargar_url('{{$submenu->url}}')">
                    <i class="fa fa-fw fa-refresh"></i> {!! $submenu->nombre !!}
                </a>
            </li>
        </ol>
    </section>

    {{-- Importar excel con resumen de totales por semana --}}
    <section class="content">
        {{-- Importar directo desde el venture --}}
        <div class="box box-primary">
            <div class="box-header">
                <div class="box-title">
                    Importar archivo excel detallado por fechas
                    <button type="button" class="btn btn-yura_default btn-xs" title="Manual" style="margin-left: 10px"
                            onclick="help_costos_importar()">
                        <i class="fa fa-fw fa-question-circle"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <form id="form-importar_costos_details" action="{{url('costos_importar/importar_file_costos_details')}}" method="POST">
                    {!! csrf_field() !!}
                    <div class="input-group">
                        <span class="input-group-addon" style="background-color: #e9ecef">
                            Concepto
                        </span>
                        <select name="concepto_importar_details" id="concepto_importar_details" class="form-control input-group-addon">
                            <option value="I">Insumos</option>
                            <option value="M">Mano de Obra</option>
                        </select>
                        <span class="input-group-addon" style="background-color: #e9ecef">
                            Archivo
                        </span>
                        <input type="file" id="file_costos_details" name="file_costos_details" required class="form-control input-group-addon"
                               accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        <span class="input-group-addon" style="background-color: #e9ecef">
                            Criterio
                        </span>
                        <select name="criterio_importar_details" id="criterio_importar_details" class="form-control input-group-addon">
                            <option value="V">Dinero</option>
                            <option value="C">Cantidad</option>
                        </select>
                        <span class="input-group-addon" style="background-color: #e9ecef">
                            Acción
                        </span>
                        <select name="sobreescribir_importar_details" id="sobreescribir_importar_details" class="form-control input-group-addon">
                            <option value="S">Sobreescribir</option>
                            <option value="I">Sumar a lo anterior</option>
                        </select>
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-yura_dark" onclick="descargar_plantilla()">
                                <i class="fa fa-fw fa-download"></i> Plantilla
                            </button>
                            <button type="button" class="btn btn-primary" onclick="importar_file_costos_details()">
                                <i class="fa fa-fw fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    <script>
        function importar_file_costos() {
            if ($('#form-importar_costos').valid()) {
                $.LoadingOverlay('show');
                formulario = $('#form-importar_costos');
                var formData = new FormData(formulario[0]);
                formData.append('finca_actual', $('#fincas_propias').val());
                //hacemos la petición ajax
                $.ajax({
                    url: formulario.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    //necesario para subir archivos via ajax
                    cache: false,
                    contentType: false,
                    processData: false,

                    success: function (retorno2) {
                        notificar('Se ha importado un archivo', '{{url('costos_gestion')}}');
                        if (retorno2.success) {
                            $.LoadingOverlay('hide');
                            alerta_accion(retorno2.mensaje, function () {
                                //location.reload();
                            });
                        } else {
                            alerta(retorno2.mensaje);
                            $.LoadingOverlay('hide');
                        }
                    },
                    //si ha ocurrido un error
                    error: function (retorno2) {
                        console.log(retorno2);
                        alerta(retorno2.responseText);
                        alert('Hubo un problema en el envío de la información');
                        $.LoadingOverlay('hide');
                    }
                });
            }
        }

        function importar_file_costos_details() {
            $.LoadingOverlay('show');
            formulario = $('#form-importar_costos_details');
            var formData = new FormData(formulario[0]);
            formData.append('finca_actual', $('#fincas_propias').val());
            //hacemos la petición ajax
            $.ajax({
                url: formulario.attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                //necesario para subir archivos via ajax
                cache: false,
                contentType: false,
                processData: false,

                success: function (retorno2) {
                    notificar('Se ha importado un archivo', '{{url('costos_gestion')}}');
                    if (retorno2.success) {
                        $.LoadingOverlay('hide');
                        alerta_accion(retorno2.mensaje, function () {
                            //location.reload();
                        });
                    } else {
                        alerta(retorno2.mensaje);
                        $.LoadingOverlay('hide');
                    }
                },
                //si ha ocurrido un error
                error: function (retorno2) {
                    console.log(retorno2);
                    alerta(retorno2.responseText);
                    alert('Hubo un problema en el envío de la información');
                    $.LoadingOverlay('hide');
                }
            });
        }

        function descargar_plantilla() {
            $.LoadingOverlay('show');
            window.open('{{url('costos_importar/descargar_plantilla')}}?c=' + $('#concepto_importar_details').val(), '_blank');
            $.LoadingOverlay('hide');
        }

        function help_costos_importar() {
            datos = {};
            get_jquery('{{url('help_costos_importar')}}', datos, function (retorno) {
                modal_view('modal_view-help_costos_importar', retorno, '<i class="fa fa-fw fa-question-circle"></i> Manual', true, true, '95%')
            });
        }
    </script>
@endsection