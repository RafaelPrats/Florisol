<div class="clientes-form">
    <div class="clientes-form-heading">
        <span><i class="fa fa-user-plus"></i></span>
        <div>
            <strong>{{ !empty($dataCliente) ? 'Actualizar cliente' : 'Registrar cliente' }}</strong>
            <small>Completa la información de identificación, contacto y operación.</small>
        </div>
    </div>
    <form id="form_add_cliente">
    <input type="hidden" id="id_cliente" value="{!! !empty($dataCliente) ? $dataCliente->id_cliente : '' !!}">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="250"
                    autocomplete="off" value="{!! !empty($dataCliente) ? $dataCliente->nombre : '' !!}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="tipo_identificacion">Tipo de identificación</label>
                <select class="form-control" id="tipo_identificacion" name="tipo_identificacion"
                    onchange="valida_identificacion()" required>
                    <option disabled selected>Seleccione</option>
                    @foreach ($dataTipoIdentificacion as $dTI)
                        @php
                            $selected = '';
                            if (!empty($dataCliente)) {
                                if ($dataCliente->codigo_identificacion == $dTI->codigo) {
                                    $selected = 'selected=selected';
                                }
                            }
                        @endphp
                        <option {{ $selected }} value="{{ $dTI->codigo }}">{{ $dTI->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="identificacion">Número de identificación</label>
                <input type="text" id="identificacion" name="identificacion" class="form-control"
                    {{ !empty($dataCliente) && $dataCliente->codigo_identificacion == 07 ? 'disabled' : '' }} required
                    autocomplete="off" value="{!! !empty($dataCliente) ? $dataCliente->ruc : '' !!}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="number" onkeypress="return isNumber()" id="telefono" name="telefono" class="form-control"
                    maxlength="25" autocomplete="off" value="{!! !empty($dataCliente) ? $dataCliente->telefono : '' !!}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="pais">Impuesto</label>
                <select disabled id="codigo_impuesto" name="codigo_impuesto" class="form-control" required
                    onchange="porcentaje_impuesto()">
                    <option selected disabled>Seleccione</option>
                    @foreach ($impuestos as $impuesto)
                        @php
                            $selected = '';
                            if (!empty($dataCliente)) {
                                if ($dataCliente->codigo_impuesto == $impuesto->codigo) {
                                    $selected = 'selected=selected';
                                }
                            }
                        @endphp
                        <option {{ $selected }} value="{{ $impuesto->codigo }}">{{ $impuesto->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="pais">Tipo de impuesto</label>
                <select disabled id="tipo_impuesto" name="tipo_impuesto" class="form-control" required>
                    <option selected disabled>Seleccione</option>
                    @if (isset($tipoImpuestos) && !empty($tipoImpuestos))
                        @foreach ($tipoImpuestos as $tipoImpuesto)
                            @php
                                $selected = '';
                                if (!empty($dataCliente)) {
                                    if ($dataCliente->codigo_porcentaje_impuesto == $tipoImpuesto->codigo) {
                                        $selected = 'selected=selected';
                                    }
                                }
                            @endphp
                            <option {{ $selected }} value="{{ $tipoImpuesto->codigo }}">
                                {{ $tipoImpuesto->descripcion }}</option>
                        @endforeach
                    @endif
                </select>
                <input type="hidden" value="">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="pais">País</label>
                <select id="pais" name="pais" class="form-control" required>
                    <option selected disabled>Seleccione</option>
                    @foreach ($dataPais as $pais)
                        @php
                            $selected = '';
                            if (!empty($dataCliente)) {
                                if ($dataCliente->codigo_pais === $pais->codigo) {
                                    $selected = 'selected=selected';
                                }
                            }
                        @endphp
                        <option {{ $selected }} value="{{ $pais->codigo }}">{{ $pais->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="provincia">Ciudad</label>
                <input type="text" id="provincia" name="provincia" class="form-control" maxlength="255"
                    autocomplete="off" value="{!! !empty($dataCliente) ? $dataCliente->provincia : '' !!}">
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control" maxlength="255"
                    autocomplete="off" value="{!! !empty($dataCliente) ? $dataCliente->correo : '' !!}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="puerto_entrada">Puerto de entrada</label>
                <input type="text" id="puerto_entrada" name="puerto_entrada" class="form-control"
                    value="{!! !empty($dataCliente) ? $dataCliente->puerto_entrada : '' !!}">
            </div>
        </div>
        {{-- <div class="col-md-3">
            <div class="form-group">
                <label for="tipo_credito">Tiempo de crédito</label>
                <input type="text" id="tipo_credito" name="tipo_credito" class="form-control" value="{!! !empty($dataCliente) ? $dataCliente->tipo_credito : "" !!}" required>
            </div>
        </div> --}}
        <div class="col-md-3">
            <div class="form-group">
                <label for="marca">Marca de caja</label>
                <select name="marca" id="marca" class="form-control">
                    @foreach ($marcas as $marca)
                        <option {!! !empty($dataCliente) ? ($dataCliente->id_marca === $marca->id_marca ? 'selected' : '') : '' !!} value="{{ $marca->id_marca }}">{{ $marca->nombre }} </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="segmento">Segmento</label>
                <select id="segmento" name="segmento" class="form-control" style="width: 100%">
                    @foreach ($segmentos as $segmento)
                        <option value="{{ $segmento }}" {!! !empty($dataCliente) ? ($dataCliente->segmento === $segmento ? 'selected' : '') : '' !!}>
                            {{ $segmento }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    {{-- <div class='row'>
        <div class='col-md-12'>
            <form id='form_envio_correo' name='form_envio_correo'>
                <p><label>Elija los archivos que se enviarán por correo al cliente:</label></p>
                <div class='row'>
                    <div class='col-md-2'>
                        <input type='checkbox' id='factura_cliente' name='factura_cliente' style='position:relative;top:3px'
                                {{isset($dataCliente) && $dataCliente!=""  && $dataCliente->cliente->fc ? 'checked' : '' }}>
                        <label style='font-weight:600' for='factura_cliente'>Factura del cliente</label>
                    </div>
                    <div class='col-md-2'>
                        <input type='checkbox' id='csv_etiqueta' name='csv_etiqueta' style='position:relative;top:3px'
                            {{isset($dataCliente) && $dataCliente!="" && $dataCliente->cliente->csv ? 'checked' : '' }}>
                        <label style='font-weight:600' for='csv_etiqueta'>CSV de etiquetas</label>
                    </div>
                    <div class='col-md-2'>
                        <input type='checkbox' id='packing_list' name='packing_list' style='position:relative;top:3px'
                            {{isset($dataCliente) && $dataCliente!="" && $dataCliente->cliente->le ? 'checked' : '' }}>
                        <label style='font-weight:600' for='packing_list'>Lista de empaque</label>
                    </div>
                    <div class='col-md-2'>
                        <input type='checkbox' id='dist_cajas' name='dist_cajas' style='position:relative;top:3px'
                            {{isset($dataCliente)  && $dataCliente!="" && $dataCliente->cliente->dc ? 'checked' : '' }}>
                        <label style='font-weight:600' for='dist_cajas'>Lista de distribución</label>
                        </div>
                    <div class='col-md-2'>
                        <input type='checkbox' id='factura_sri' name='factura_sri' style='position:relative;top:3px'
                            {{isset($dataCliente) && $dataCliente!="" && $dataCliente->cliente->fc_sri ? 'checked' : '' }}>
                        <label style='font-weight:600' for='factura_sri'>Factura del SRI</label>
                    </div>
                </div>
            </form>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea rows="5" id="direccion" name="direccion" class="form-control" maxlength="500" autocomplete="off">{!! !empty($dataCliente) ? $dataCliente->direccion : '' !!}</textarea>
            </div>
        </div>
    </div>
        <div class="clientes-form-actions">
            <span><i class="fa fa-info-circle"></i> Los campos marcados son obligatorios.</span>
            <button class="btn btn-clientes-form-save" type="button" id="btn_guardar_modal_add_cliente"
                onclick="store_cliente('{{ csrf_token() }}')">
                <i class="fa fa-save"></i> Guardar cliente
            </button>
        </div>
    </form>
</div>

<style>
    .clientes-form {
        --clientes-green: #00B388;
        --clientes-ink: #003729;
        --clientes-muted: #5A7177;
        --clientes-line: #dce9e4;
        color: var(--clientes-ink);
        font-size: 13px;
    }

    .clientes-form-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 12px;
        padding: 9px 11px;
        border: 1px solid var(--clientes-line);
        border-radius: 7px;
        background: #f4faf7;
    }

    .clientes-form-heading > span {
        display: grid;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        place-items: center;
        border-radius: 8px;
        background: #e4f5ee;
        color: var(--clientes-green);
    }

    .clientes-form-heading strong,
    .clientes-form-heading small {
        display: block;
    }

    .clientes-form-heading strong {
        font-size: 13px;
    }

    .clientes-form-heading small {
        margin-top: 2px;
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-form .row {
        margin-right: -7px;
        margin-left: -7px;
    }

    .clientes-form .row > [class*="col-"] {
        padding-right: 7px;
        padding-left: 7px;
    }

    .clientes-form .form-group {
        margin-bottom: 10px;
    }

    .clientes-form .form-group label {
        margin-bottom: 4px;
        color: #35574b;
        font-size: 11px;
        font-weight: 700;
    }

    .clientes-form .form-control {
        min-height: 35px;
        border-color: var(--clientes-line);
        border-radius: 5px;
        box-shadow: none;
        color: var(--clientes-ink);
        font-size: 12px;
    }

    .clientes-form .form-control:focus {
        border-color: var(--clientes-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .clientes-form textarea.form-control {
        min-height: 72px;
        resize: vertical;
    }

    .clientes-form-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 2px;
        padding-top: 10px;
        border-top: 1px solid var(--clientes-line);
    }

    .clientes-form-actions > span {
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-form-actions > span i {
        margin-right: 3px;
        color: var(--clientes-green);
    }

    .btn-clientes-form-save {
        padding: 7px 12px;
        border: 1px solid var(--clientes-green);
        border-radius: 5px;
        background: var(--clientes-green);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-clientes-form-save:hover,
    .btn-clientes-form-save:focus {
        border-color: #008f6c;
        background: #008f6c;
        color: #fff;
    }

    .btn-clientes-form-save i {
        margin-right: 4px;
    }

    @media (max-width: 480px) {
        .clientes-form-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .clientes-form-actions .btn {
            width: 100%;
        }
    }
</style>
