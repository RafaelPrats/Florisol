<div class="clientes-detail">
    <section class="clientes-detail-card">
        <header class="clientes-detail-heading">
            <span class="clientes-detail-icon"><i class="fa fa-user"></i></span>
            <div>
                <small>Ficha del cliente</small>
                <h2>{{ $dataCliente->nombre }}</h2>
            </div>
            <span class="clientes-detail-segment">{{ $dataCliente->segmento ?: 'Sin segmento' }}</span>
        </header>

        <div class="clientes-detail-layout">
            <div class="clientes-data-grid">
                <div class="clientes-data-item">
                    <span><i class="fa fa-id-card-o"></i> Identificación</span>
                    <strong>{{ $dataCliente->ruc ?: 'No registrada' }}</strong>
                </div>
                <div class="clientes-data-item">
                    <span><i class="fa fa-globe"></i> País</span>
                    <strong>{{ $pais ?: 'No registrado' }}</strong>
                </div>
                <div class="clientes-data-item">
                    <span><i class="fa fa-map-marker"></i> Ciudad / provincia</span>
                    <strong>{{ $dataCliente->provincia ?: 'No registrada' }}</strong>
                </div>
                <div class="clientes-data-item">
                    <span><i class="fa fa-phone"></i> Teléfono</span>
                    <strong>{{ $dataCliente->telefono ?: 'No registrado' }}</strong>
                </div>
                <div class="clientes-data-item">
                    <span><i class="fa fa-envelope-o"></i> Correo</span>
                    <strong>{{ $dataCliente->correo ?: 'No registrado' }}</strong>
                </div>
                <div class="clientes-data-item clientes-data-address">
                    <span><i class="fa fa-map-o"></i> Dirección</span>
                    <strong>{{ $dataCliente->direccion ?: 'No registrada' }}</strong>
                </div>
            </div>

            <nav class="clientes-options" aria-label="Administrar cliente">
                <div class="clientes-options-title"><i class="fa fa-sliders"></i> Administrar</div>
                <a href="javascript:void(0)" onclick="cargar_opcion('div_content_opciones','{{ $dataCliente->id_cliente }}','clientes/add','add')">
                    <i class="fa fa-pencil"></i><span>Editar datos del cliente</span><i class="fa fa-angle-right"></i>
                </a>
                <a href="javascript:void(0)" onclick="add_info('{{ $dataCliente->id_detalle_cliente }}','{{ $dataCliente->id_cliente }}')">
                    <i class="fa fa-file-text-o"></i><span>Agregar información personalizada</span><i class="fa fa-angle-right"></i>
                </a>
                @if (count(getDocumentos('detalle_cliente', $dataCliente->id_detalle_cliente)) > 0)
                    <a href="javascript:void(0)" onclick="ver_documentos('detalle_cliente','{{ $dataCliente->id_detalle_cliente }}','{{ $dataCliente->id_cliente }}')">
                        <i class="fa fa-folder-open-o"></i><span>Ver información personalizada</span><i class="fa fa-angle-right"></i>
                    </a>
                @endif
                <a href="javascript:void(0)" onclick="cargar_opcion('campos_contactos','{{ $dataCliente->id_detalle_cliente }}','clientes/ver_contactos_clientes')">
                    <i class="fa fa-address-card-o"></i><span>Contactos</span><i class="fa fa-angle-right"></i>
                </a>
                <a href="javascript:void(0)" onclick="cargar_opcion('div_consignatario','{{ $dataCliente->id_cliente }}','clientes/agregar_consignatario')">
                    <i class="fa fa-truck"></i><span>Consignatarios</span><i class="fa fa-angle-right"></i>
                </a>
                <a href="javascript:void(0)" onclick="cargar_opcion('campos_agencia_carga','','clientes/ver_agencias_carga')">
                    <i class="fa fa-cubes"></i><span>Agencias de carga</span><i class="fa fa-angle-right"></i>
                </a>
                <a href="javascript:void(0)" onclick="admin_especificaciones('{{ $dataCliente->id_cliente }}')">
                    <i class="fa fa-list-alt"></i><span>Especificaciones</span><i class="fa fa-angle-right"></i>
                </a>
            </nav>
        </div>

        @if (count(getDocumentos('detalle_cliente', $dataCliente->id_detalle_cliente)) > 0)
            <div id="content_documentos"></div>
        @endif

        <div id="div_content_opciones" class="clientes-detail-subcontent"></div>
    </section>
</div>

<div id="include_agencia_carga" class="hide">
    @include('adminlte.gestion.postcocecha.clientes.forms.add_agencias_carga')
</div>

<div id="include_contactos_cliente" class="hide">
    @include('adminlte.gestion.postcocecha.clientes.forms.add_contactos')
</div>

<style>
    .clientes-detail {
        --clientes-green: #00B388;
        --clientes-ink: #003729;
        --clientes-muted: #5A7177;
        --clientes-line: #dce9e4;
        color: var(--clientes-ink);
    }

    .clientes-detail-card {
        overflow: hidden;
        border: 1px solid var(--clientes-line);
        border-radius: 9px;
        background: #fff;
    }

    .clientes-detail-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid var(--clientes-line);
        background: linear-gradient(110deg, #f4faf7, #fff);
    }

    .clientes-detail-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border-radius: 10px;
        background: #e4f5ee;
        color: var(--clientes-green);
        font-size: 15px;
    }

    .clientes-detail-heading > div {
        min-width: 0;
        flex: 1 1 auto;
    }

    .clientes-detail-heading small {
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-detail-heading h2 {
        margin: 2px 0 0;
        overflow: hidden;
        color: var(--clientes-ink);
        font-size: 15px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clientes-detail-segment {
        padding: 5px 8px;
        border-radius: 18px;
        background: #eaf6f0;
        color: #147452;
        font-size: 10px;
        font-weight: 700;
    }

    .clientes-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(230px, 300px);
        gap: 12px;
        padding: 13px;
    }

    .clientes-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-content: start;
        gap: 7px;
    }

    .clientes-data-item {
        min-width: 0;
        padding: 9px 10px;
        border: 1px solid #e5eeea;
        border-radius: 7px;
        background: #fbfdfc;
    }

    .clientes-data-item > span {
        display: block;
        margin-bottom: 4px;
        color: var(--clientes-muted);
        font-size: 10px;
        font-weight: 600;
    }

    .clientes-data-item > span i {
        width: 14px;
        color: var(--clientes-green);
    }

    .clientes-data-item strong {
        display: block;
        overflow-wrap: anywhere;
        color: var(--clientes-ink);
        font-size: 12px;
        font-weight: 600;
    }

    .clientes-data-address {
        grid-column: 1 / -1;
    }

    .clientes-options {
        overflow: hidden;
        border: 1px solid var(--clientes-line);
        border-radius: 7px;
    }

    .clientes-options-title {
        padding: 8px 10px;
        border-bottom: 1px solid var(--clientes-line);
        background: #f4faf7;
        color: var(--clientes-ink);
        font-size: 11px;
        font-weight: 700;
    }

    .clientes-options-title i {
        margin-right: 4px;
        color: var(--clientes-green);
    }

    .clientes-options a {
        display: flex;
        min-height: 36px;
        align-items: center;
        gap: 8px;
        padding: 7px 9px;
        border-bottom: 1px solid #edf2ef;
        color: #344f46;
        font-size: 11px;
        text-decoration: none;
    }

    .clientes-options a:last-child {
        border-bottom: 0;
    }

    .clientes-options a:hover,
    .clientes-options a:focus {
        background: #f4faf7;
        color: #087a5f;
    }

    .clientes-options a > i:first-child {
        width: 15px;
        color: var(--clientes-green);
        text-align: center;
    }

    .clientes-options a > span {
        flex: 1 1 auto;
    }

    .clientes-options a > i:last-child {
        color: #93a39c;
    }

    .clientes-detail-subcontent:empty {
        display: none;
    }

    .clientes-detail-subcontent:not(:empty) {
        margin: 0 13px 13px;
        padding-top: 12px;
        border-top: 1px solid var(--clientes-line);
    }

    .clientes-detail-subcontent .box {
        overflow: hidden;
        border: 1px solid var(--clientes-line);
        border-top: 3px solid var(--clientes-green);
        border-radius: 8px;
        box-shadow: none;
    }

    .clientes-detail-subcontent .box-header {
        padding: 10px 12px;
        border-bottom: 1px solid var(--clientes-line);
        background: #f4faf7;
    }

    .clientes-detail-subcontent .box-title {
        color: var(--clientes-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .clientes-detail-subcontent table.table-bordered,
    .clientes-detail-subcontent table.table-bordered th,
    .clientes-detail-subcontent table.table-bordered td {
        border-color: var(--clientes-line) !important;
    }

    .clientes-detail-subcontent table.table-bordered th {
        padding: 8px;
        background: #eaf6f0;
        color: var(--clientes-ink);
        font-size: 10px;
        white-space: nowrap;
    }

    .clientes-detail-subcontent table.table-bordered td {
        padding: 7px 8px;
        font-size: 11px;
    }

    .clientes-detail-subcontent .form-control {
        min-height: 34px;
        border-color: var(--clientes-line);
        box-shadow: none;
        font-size: 12px;
    }

    .clientes-detail-subcontent .form-control:focus {
        border-color: var(--clientes-green);
        box-shadow: 0 0 0 2px rgba(0, 179, 136, .1);
    }

    .clientes-detail-subcontent .well {
        border-color: var(--clientes-line);
        border-radius: 7px;
        background: #f7faf8;
        box-shadow: none;
    }

    @media (max-width: 760px) {
        .clientes-detail-layout {
            grid-template-columns: 1fr;
        }

        .clientes-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .clientes-options-title {
            grid-column: 1 / -1;
        }

        .clientes-options a {
            border-right: 1px solid #edf2ef;
        }
    }

    @media (max-width: 480px) {
        .clientes-data-grid {
            grid-template-columns: 1fr;
        }

        .clientes-data-address {
            grid-column: auto;
        }

        .clientes-options {
            grid-template-columns: 1fr;
        }
    }
</style>
