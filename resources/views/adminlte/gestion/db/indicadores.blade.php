@extends('layouts.adminlte.master')

@section('titulo')
    DB - Indicadores
@endsection

@section('script_inicio')
    <script>
    </script>
@endsection

@section('contenido')
    <section class="content-header">
        <h1>
            DB
            <small>Indicadores</small>
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

    <section class="content">
        <div class="box box-primary">
            <div class="box-body">
                <div id="div_listado_indicadores" style="overflow-x: scroll">
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script_final')
    <script>
        $('#vista_actual').val('db_indicadores');
        listar_indicadores();

        function listar_indicadores() {
            datos = {
                finca_actual: $('#fincas_propias').val(),
            };
            get_jquery('{{url('db_indicadores/listar_indicadores')}}', datos, function (retorno) {
                $('#div_listado_indicadores').html(retorno);
            });
        }

        function add_indicador() {
            $('#btn_add_indicador').hide();
            $('#db_tbl_indicadores').append('<tr>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<select style="width: 100%" id="new_finca">' + $('#fincas_propias').html() + '</select>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="text" class="text-center" id="new_nombre" style="width: 100%" placeholder="P1" max="4">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="text" class="text-center" id="new_descripcion" style="width: 100%" max="250">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="number" class="text-center" id="new_valor" style="width: 100%" value="0">' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<input type="checkbox" id="new_estado" style="width: 100%" checked>' +
                '</td>' +
                '<td class="text-center" style="border-color: #9d9d9d">' +
                '<button type="button" class="btn btn-xs btn-success" title="Guardar" onclick="store_inidcador()">' +
                '<i class="fa fa-fw fa-save"></i>' +
                '</button>' +
                '</td>' +
                '</tr>');
            $('#new_nombre').focus();
        }

        function store_inidcador() {
            datos = {
                _token: '{{csrf_token()}}',
                nombre: $('#new_nombre').val(),
                descripcion: $('#new_descripcion').val(),
                valor: $('#new_valor').val(),
                estado: $('#new_estado').prop('checked'),
                finca_actual: $('#new_finca').val(),
            };
            post_jquery('{{url('db_indicadores/store_indicador')}}', datos, function (retorno) {
                listar_indicadores();
            });
        }

        function update_indicador(id) {
            datos = {
                _token: '{{csrf_token()}}',
                id: id,
                nombre: $('#nombre_' + id).val(),
                descripcion: $('#descripcion_' + id).val(),
                valor: $('#valor_' + id).val(),
                estado: $('#estado_' + id).prop('checked'),
            };
            post_jquery('{{url('db_indicadores/update_indicador')}}', datos, function (retorno) {
                listar_indicadores();
            });
        }

        function ejecutar_comando(id) {
            datos = {
                _token: '{{csrf_token()}}',
                indicador: $('#nombre_' + id).val(),
                cola: 0,
                comando: 4
            };

            $.LoadingOverlay('show');
            $.post('{{url('db_jobs/send_queue_job')}}', datos, function (retorno) {
                listar_indicadores();
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $.LoadingOverlay('hide');
            });
        }

        function copiar_indicador(id) {
            datos = {
                _token: '{{csrf_token()}}',
                id: id
            };

            $.LoadingOverlay('show');
            $.post('{{url('db_indicadores/copiar_indicador')}}', datos, function (retorno) {
                alerta(retorno.mensaje);
                if (retorno.success)
                    listar_indicadores();
            }, 'json').fail(function (retorno) {
                console.log(retorno);
                alerta_errores(retorno.responseText);
            }).always(function () {
                $.LoadingOverlay('hide');
            });
        }
    </script>
@endsection
