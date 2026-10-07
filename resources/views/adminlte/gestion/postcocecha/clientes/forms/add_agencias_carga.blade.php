<div id="div_select_agencia_carga" class="clientes-agencias">
    <div class="row">
        <div class="col-md-12">
            <div class="clientes-subform-heading">
                <span><i class="fa fa-cubes"></i></span>
                <div>
                    <strong>Agencias de carga</strong>
                    <small>Asocia agencias y administra sus contactos para este cliente.</small>
                </div>
            </div>
            <form id="form_add_user_agencia_carga">
                <table width="100%" class="table table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d">
                    <thead>
                    <tr class="table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}">
                        <th class="text-center" style="border-color: #9d9d9d">Seleccione una opción</th>
                        <th class="text-center" style="border-color: #9d9d9d;width: 10%;">
                            <button type="button" class="btn btn-xs btn-default" title="Añadir campo" onclick="cargar_opcion('campos_agencia_carga','','clientes/ver_agencias_carga','add')">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-default" title="Crear agencia de carga" onclick="create_agencia_carga('','{{csrf_token()}}','{{$dataCliente->id_cliente}}')">
                                <i class="fa fa-cubes" aria-hidden="true"></i>
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="campos_agencia_carga">
                    @if(isset($dataClienteAgenciasCarga) && count($dataClienteAgenciasCarga) > 0)
                        @foreach($dataClienteAgenciasCarga as $key => $clienteAgenciasCarga)
                            <tr id="tr_select_agencias_carga_{{$key+1}}">
                                <td>
                                    <div class="">
                                        <select id="select_agencia_carga_{{$key+1}}" {!! $clienteAgenciasCarga->estado == 0 ? 'disabled' : '' !!} name="select_agencia_carga_{{$key+1}}" class="form-control" required>
                                            <option disabled selected>Seleccione1</option>
                                            @foreach($dataAgenciaCarga as $agenciaCarga)
                                                @php
                                                $selected = '';
                                                    if($agenciaCarga->id_agencia_carga === $clienteAgenciasCarga->id_agencia_carga){
                                                        $selected ='selected=selected';
                                                    }
                                                @endphp
                                                <option {{$selected}} value="{{$agenciaCarga->id_agencia_carga}}">{{$agenciaCarga->nombre}}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="id_select_agencia_carga_{{$key+1}}" value="{{$clienteAgenciasCarga->id_cliente_agencia_carga}}">
                                    </div>

                                    <style>
                                        .clientes-agencias {
                                            --clientes-green: #00B388;
                                            --clientes-ink: #003729;
                                            --clientes-muted: #5A7177;
                                            --clientes-line: #dce9e4;
                                            color: var(--clientes-ink);
                                        }

                                        .clientes-agencias .clientes-subform-heading {
                                            display: flex;
                                            align-items: center;
                                            gap: 9px;
                                            margin-bottom: 9px;
                                            padding: 9px 11px;
                                            border: 1px solid var(--clientes-line);
                                            border-radius: 7px;
                                            background: #f4faf7;
                                        }

                                        .clientes-agencias .clientes-subform-heading > span {
                                            display: grid;
                                            width: 32px;
                                            height: 32px;
                                            flex: 0 0 32px;
                                            place-items: center;
                                            border-radius: 8px;
                                            background: #e4f5ee;
                                            color: var(--clientes-green);
                                        }

                                        .clientes-agencias .clientes-subform-heading strong,
                                        .clientes-agencias .clientes-subform-heading small {
                                            display: block;
                                        }

                                        .clientes-agencias .clientes-subform-heading strong {
                                            font-size: 13px;
                                        }

                                        .clientes-agencias .clientes-subform-heading small {
                                            margin-top: 2px;
                                            color: var(--clientes-muted);
                                            font-size: 10px;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga > table {
                                            border-color: var(--clientes-line);
                                            font-size: 12px !important;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga > table thead tr {
                                            background: #eaf6f0;
                                            color: var(--clientes-ink);
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga > table th,
                                        .clientes-agencias #form_add_user_agencia_carga > table td {
                                            padding: 8px;
                                            border-color: var(--clientes-line) !important;
                                            vertical-align: top;
                                        }

                                        .clientes-agencias .clientes-agency-row > td {
                                            padding: 8px !important;
                                            border-color: var(--clientes-line) !important;
                                            vertical-align: top;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga label {
                                            margin: 7px 0 3px;
                                            color: #42675b;
                                            font-size: 10px;
                                            font-weight: 700;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga .form-control {
                                            min-height: 34px;
                                            border-color: var(--clientes-line);
                                            box-shadow: none;
                                            font-size: 12px;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga .row {
                                            margin-right: -5px;
                                            margin-left: -5px;
                                        }

                                        .clientes-agencias #form_add_user_agencia_carga .row > [class*="col-"] {
                                            padding-right: 5px;
                                            padding-left: 5px;
                                        }

                                        .clientes-agencias .contacto_agencia_carga_1,
                                        .clientes-agencias [class*="contacto_agencia_carga_"] {
                                            padding: 5px 8px;
                                            border: 1px solid #e7efeb;
                                            border-radius: 6px;
                                            background: #fbfdfc;
                                        }

                                        .clientes-agencias .text-center > .btn.btn-success {
                                            margin-top: 9px;
                                            padding: 7px 12px;
                                            border: 1px solid var(--clientes-green);
                                            border-radius: 5px;
                                            background: var(--clientes-green);
                                            font-size: 12px;
                                            font-weight: 700;
                                        }

                                        @media (max-width: 600px) {
                                            .clientes-agencias #form_add_user_agencia_carga > table {
                                                display: block;
                                                overflow-x: auto;
                                            }

                                            .clientes-agencias #form_add_user_agencia_carga > table tbody tr {
                                                display: block;
                                                margin-bottom: 8px;
                                                border: 1px solid var(--clientes-line);
                                                border-radius: 7px;
                                            }

                                            .clientes-agencias #form_add_user_agencia_carga > table tbody tr > td {
                                                display: block;
                                                width: 100%;
                                            }
                                        }
                                    </style>
                                    <div class="row contacto_agencia_carga_{{$key+1}}">
                                        <div class="col-md-12"><div class="row">
                                                <div class="col-md-4">
                                                    <label> Contacto</label>
                                                    <input type="text"  class="form-control contacto_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[0]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[0]->contacto : ""}}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Correo</label>
                                                    <input type="email" class="form-control correo_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[0]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[0]->correo : ""}}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Dirección</label>
                                                    <input type="text" class="form-control direccion_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[0]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[0]->direccion : ""}}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label> Contacto</label>
                                                    <input type="text" class="form-control contacto_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[1]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[1]->contacto : ""}}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Correo</label>
                                                    <input type="email" class="form-control correo_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[1]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[1]->correo : ""}}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Dirección</label>
                                                    <input type="text" class="form-control direccion_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[1]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[1]->direccion : ""}}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label> Contacto</label>
                                                    <input type="text"  class="form-control contacto_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[2]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[2]->contacto : ""}}" >
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Correo</label>
                                                    <input type="email"  class="form-control correo_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[2]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[2]->correo : ""}}" >
                                                </div>
                                                <div class="col-md-4">
                                                    <label> Dirección</label>
                                                    <input type="text"
                                                           class="form-control direccion_cliente_agencia_carga"
                                                           value="{{isset($clienteAgenciasCarga->contacto_cliente_agencia_carga[2]) ? $clienteAgenciasCarga->contacto_cliente_agencia_carga[2]->direccion : ""}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="vertical-align: middle">
                                    <button type="button" class="btn btn-xs btn-danger" title="{!! $clienteAgenciasCarga->estado == 1 ? 'Desactivar' : 'Activar' !!} campo"
                                            onclick="deleteClienteAgenciaCarga('{{$clienteAgenciasCarga->id_cliente_agencia_carga}}','{!! $clienteAgenciasCarga->estado !!}','{{$dataCliente->id_cliente}}')">
                                        <i class="fa fa-trash " aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr id="tr_select_agencias_carga_1">
                            <td>
                                <div class="">
                                    <select id="select_agencia_carga_1" name="select_agencia_carga_1" class="form-control" required>
                                        <option disabled selected>Seleccione</option>
                                        @foreach($dataAgenciaCarga as $agenciaCarga)
                                            <option value="{{$agenciaCarga->id_agencia_carga}}">{{$agenciaCarga->nombre}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="id_select_agencia_carga_1" value="">
                                </div>
                                <div class="row contacto_agencia_carga_1">
                                    <div class="col-md-12"><div class="row">
                                            <div class="col-md-4">
                                                <label> Contacto</label>
                                                <input type="text"  class="form-control contacto_cliente_agencia_carga" value="" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label> Correo</label>
                                                <input type="email" class="form-control correo_cliente_agencia_carga" value="" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label> Dirección</label>
                                                <input type="text" class="form-control direccion_cliente_agencia_carga" value="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label> Contacto</label>
                                                <input type="text" class="form-control contacto_cliente_agencia_carga" value="" >
                                            </div>
                                            <div class="col-md-4">
                                                <label> Correo</label>
                                                <input type="email" class="form-control correo_cliente_agencia_carga" value="" >
                                            </div>
                                            <div class="col-md-4">
                                                <label> Dirección</label>
                                                <input type="text" class="form-control direccion_cliente_agencia_carga" value="">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label> Contacto</label>
                                                <input type="text"  class="form-control contacto_cliente_agencia_carga" value="" >
                                            </div>
                                            <div class="col-md-4">
                                                <label> Correo</label>
                                                <input type="email"  class="form-control correo_cliente_agencia_carga" value="" >
                                            </div>
                                            <div class="col-md-4">
                                                <label> Dirección</label>
                                                <input type="text"
                                                       class="form-control direccion_cliente_agencia_carga" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </form>
            <div class="text-center">
                <button class="btn btn-success" type="button" id="btn_guardar_modal_add_cliente" onclick="store_agencias('{{$dataCliente->id_cliente}}')"><span class="bootstrap-dialog-button-icon fa fa-fw fa-save"></span>Guardar</button>
            </div>
        </div>
    </div>
</div>
