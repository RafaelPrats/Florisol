<div id="div_contactos_cliente" class="clientes-contactos">
    <div class="row">
        <div class="col-md-12">
            <div class="clientes-subform-heading">
                <span><i class="fa fa-address-card-o"></i></span>
                <div>
                    <strong>Contactos del cliente</strong>
                    <small>Registra personas de contacto y sus datos directos.</small>
                </div>
            </div>
            <form id="form_add_user_contactos">
                <table width="100%" class="table table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d">
                    <thead>
                    <tr class="table-{{getUsuario(Session::get('id_usuario'))->configuracion->skin}}">
                        <th class="text-center" style="border-color: #9d9d9d">Ingrese los datos del contacto</th>
                        <th class="text-center" style="border-color: #9d9d9d;width: 10%;">
                            <button type="button" class="btn btn-xs btn-default" title="Añadir campo" onclick="cargar_opcion('campos_contactos','','clientes/ver_contactos_clientes','add')">
                                <i class="fa fa-fw fa-plus"></i>
                            </button>
                        </th>
                    </tr>
                    </thead>
                        <tbody id="campos_contactos">
                            @if(isset($dataContacto) && count($dataContacto) > 0)
                                @foreach($dataContacto as $key => $contacto)
                                    @php
                                        $contacto->estado == 0 ? $disabled='disabled=disabled' :  $disabled='';
                                    @endphp
                                    <tr id="tr_select_agencias_carga_{{$key+1}}">
                                        <td>
                                            <div class="form-group col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-addon" style="background-color: #e9ecef">Nombre</span>
                                                    <input type="text" id="nombre_contacto_{{$key+1}}" name="nombre_contacto_{{$key+1}}" required=""
                                                           value="{{$contacto->nombre}}" {{$disabled}} class="form-control" minlength="1" maxlength="100">
                                                </div>

                                                <style>
                                                    .clientes-contactos {
                                                        --clientes-green: #00B388;
                                                        --clientes-ink: #003729;
                                                        --clientes-muted: #5A7177;
                                                        --clientes-line: #dce9e4;
                                                        color: var(--clientes-ink);
                                                    }

                                                    .clientes-contactos .clientes-subform-heading {
                                                        display: flex;
                                                        align-items: center;
                                                        gap: 9px;
                                                        margin-bottom: 9px;
                                                        padding: 9px 11px;
                                                        border: 1px solid var(--clientes-line);
                                                        border-radius: 7px;
                                                        background: #f4faf7;
                                                    }

                                                    .clientes-contactos .clientes-subform-heading > span {
                                                        display: grid;
                                                        width: 32px;
                                                        height: 32px;
                                                        flex: 0 0 32px;
                                                        place-items: center;
                                                        border-radius: 8px;
                                                        background: #e4f5ee;
                                                        color: var(--clientes-green);
                                                    }

                                                    .clientes-contactos .clientes-subform-heading strong,
                                                    .clientes-contactos .clientes-subform-heading small {
                                                        display: block;
                                                    }

                                                    .clientes-contactos .clientes-subform-heading strong {
                                                        font-size: 13px;
                                                    }

                                                    .clientes-contactos .clientes-subform-heading small {
                                                        margin-top: 2px;
                                                        color: var(--clientes-muted);
                                                        font-size: 10px;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos > table {
                                                        border-color: var(--clientes-line);
                                                        font-size: 12px !important;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos > table thead tr {
                                                        background: #eaf6f0;
                                                        color: var(--clientes-ink);
                                                    }

                                                    .clientes-contactos #form_add_user_contactos > table th {
                                                        padding: 7px 9px;
                                                        border-color: var(--clientes-line) !important;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos > table td {
                                                        padding: 8px;
                                                        border-color: var(--clientes-line) !important;
                                                        vertical-align: middle;
                                                    }

                                                    .clientes-contactos .clientes-contact-row td {
                                                        padding: 8px !important;
                                                        border-color: var(--clientes-line) !important;
                                                        vertical-align: top;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos .form-group {
                                                        margin-bottom: 7px;
                                                        padding-right: 5px;
                                                        padding-left: 5px;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos .input-group {
                                                        width: 100%;
                                                    }

                                                    .clientes-contactos #form_add_user_contactos .input-group-addon {
                                                        min-width: 76px;
                                                        border-color: var(--clientes-line);
                                                        background: #f4faf7 !important;
                                                        color: #42675b;
                                                        font-size: 10px;
                                                        font-weight: 600;
                                                    }

                                                    .clientes-contactos .form-control {
                                                        min-height: 34px;
                                                        border-color: var(--clientes-line);
                                                        box-shadow: none;
                                                        font-size: 12px;
                                                    }

                                                    .clientes-contactos textarea.form-control {
                                                        min-height: 52px;
                                                        resize: vertical;
                                                    }

                                                    .clientes-contactos #btn_guardar_modal_add_cliente {
                                                        margin-top: 9px;
                                                        padding: 7px 12px;
                                                        border-radius: 5px;
                                                        background: var(--clientes-green);
                                                        font-size: 12px;
                                                        font-weight: 700;
                                                    }

                                                    @media (max-width: 600px) {
                                                        .clientes-contactos #form_add_user_contactos > table,
                                                        .clientes-contactos #form_add_user_contactos > table tbody,
                                                        .clientes-contactos #form_add_user_contactos > table tr,
                                                        .clientes-contactos #form_add_user_contactos > table td {
                                                            display: block;
                                                            width: 100%;
                                                        }

                                                        .clientes-contactos #form_add_user_contactos > table thead {
                                                            display: none;
                                                        }

                                                        .clientes-contactos #form_add_user_contactos > table tbody tr {
                                                            margin-bottom: 8px;
                                                            border: 1px solid var(--clientes-line);
                                                            border-radius: 7px;
                                                        }

                                                        .clientes-contactos #form_add_user_contactos > table td:last-child {
                                                            text-align: right;
                                                        }
                                                    }
                                                </style>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-addon" style="background-color: #e9ecef">Correo</span>
                                                    <input type="email" id="correo_{{$key+1}}" name="correo_{{$key+1}}" required=""
                                                           value="{{$contacto->correo}}" {{$disabled}} class="form-control" minlength="1" maxlength="100">
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-addon" style="background-color: #e9ecef">teléfono</span>
                                                    <input type="text" id="telefono_{{$key+1}}" name="telefono_{{$key+1}}" required=""
                                                           value="{{$contacto->telefono}}" {{$disabled}} class="form-control" minlength="1" maxlength="15">
                                                </div>
                                            </div>
                                            <div class="form-group col-md-12">
                                                <div class="input-group" >
                                                    <span class="input-group-addon" style="background-color: #e9ecef">Dirección</span>
                                                    <textarea id="direccion_{{$key+1}}" name="direccion_{{$key+1}}" required=""
                                                               class="form-control" cols="3" {{$disabled}} minlength="1" maxlength="500">{{$contacto->direccion}}</textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" id="id_inputs_contacto_{{$key+1}}" value="{{$contacto->id_contacto}}">
                                        </td>
                                        <td style="vertical-align: middle;text-align: center;">
                                            <button type="button" class="btn btn-xs btn-warning" title="Actualizar estado del contacto" onclick="actualizarContacto('{{$contacto->id_contacto}}','{{$contacto->estado}}','{{$dataCliente->id_cliente}}')">
                                                <i class="fa fa-ban" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr id="tr_select_agencias_carga_1">
                                    <td>
                                        <div class="form-group col-md-4">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background-color: #e9ecef">Nombre</span>
                                                <input type="text" id="nombre_contacto_1" name="nombre_contacto_1" required="" class="form-control" minlength="1" maxlength="100">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background-color: #e9ecef">Correo</span>
                                                <input type="email" id="correo_1" name="correo_1" required="" class="form-control" minlength="1" maxlength="100">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <div class="input-group">
                                                <span class="input-group-addon" style="background-color: #e9ecef">teléfono</span>
                                                <input type="text" id="telefono_1" name="telefono_1" required="" class="form-control" minlength="1" maxlength="15">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <div class="input-group" >
                                                <span class="input-group-addon" style="background-color: #e9ecef">Dirección</span>
                                                <textarea id="direccion_1" name="direccion_1" required="" class="form-control" cols="3" minlength="1" maxlength="500"></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" id="id_inputs_contacto_1" value="">
                                    </td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                </table>
            </form>
            <div class="text-center">
                <button class="btn btn-success" type="button" id="btn_guardar_modal_add_cliente" onclick="store_contactos('{{$dataCliente->id_cliente}}','{{$dataCliente->id_detalle_cliente}}')"><span class="bootstrap-dialog-button-icon fa fa-fw fa-save"></span>Guardar</button>
            </div>
        </div>
    </div>
</div>
