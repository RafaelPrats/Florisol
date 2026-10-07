<div class="clientes-listado">
    @if (sizeof($listado) > 0)
        <div class="clientes-table-scroll">
            <table class="clientes-table" id="table_content_clientes">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Identificación</th>
                        <th>Segmento</th>
                        <th class="clientes-status-column">Estado</th>
                        <th class="clientes-action-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listado as $item)
                        <tr class="{{ $item->estado == 1 ? '' : 'is-inactive' }}"
                            id="row_clientes_{{ $item->id_cliente }}">
                            <td data-label="Cliente">
                                <div class="clientes-name-cell">
                                    <span class="clientes-avatar"><i class="fa fa-user"></i></span>
                                    <span>
                                        <strong>{{ $item->nombre }}</strong>
                                        <small>{{ $item->correo ?: 'Correo no registrado' }}</small>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Identificación">
                                <span class="clientes-identifier">{{ $item->ruc ?: '—' }}</span>
                            </td>
                            <td data-label="Segmento">
                                @if (!empty($item->segmento))
                                    <span class="clientes-segment">{{ $item->segmento }}</span>
                                @else
                                    <span class="clientes-no-data">Sin segmento</span>
                                @endif
                            </td>
                            <td data-label="Estado" class="clientes-status-column">
                                @if ($item->estado == 1)
                                    <span class="clientes-status is-active"><i class="fa fa-check-circle"></i> Activo</span>
                                @else
                                    <span class="clientes-status is-inactive"><i class="fa fa-ban"></i> Inactivo</span>
                                @endif
                            </td>
                            <td data-label="Acciones" class="clientes-action-column">
                                <button type="button" class="btn btn-clientes-view"
                                    title="Ver detalles de {{ $item->nombre }}"
                                    aria-label="Ver detalles de {{ $item->nombre }}"
                                    onclick="detalles_cliente('{{ $item->id_cliente }}')"
                                    id="btn_view_usuario_{{ $item->id_cliente }}">
                                    <i class="fa fa-eye"></i><span>Ver ficha</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="clientes-listado-footer">
            <span><i class="fa fa-info-circle"></i> Mostrando hasta 100 clientes por página.</span>
            <div id="pagination_listado_clientes">
                {!! str_replace('/?', '?', $listado->render()) !!}
            </div>
        </div>
    @else
        <div class="clientes-empty">
            <span><i class="fa fa-user-o"></i></span>
            <strong>No se encontraron clientes</strong>
            <small>Prueba con otros filtros o registra un nuevo cliente.</small>
        </div>
    @endif
</div>

<style>
    .clientes-listado {
        --clientes-green: #00B388;
        --clientes-ink: #003729;
        --clientes-muted: #5A7177;
        --clientes-line: #dce9e4;
        color: var(--clientes-ink);
        font-size: 13px;
    }

    .clientes-table-scroll {
        max-height: min(68vh, 680px);
        overflow: auto;
    }

    .clientes-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .clientes-table th {
        position: sticky;
        z-index: 2;
        top: 0;
        padding: 9px 12px;
        border-bottom: 1px solid #00674f;
        background: #008f6c;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .025em;
        text-align: left;
        white-space: nowrap;
    }

    .clientes-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #e8efec;
        background: #fff;
        vertical-align: middle;
        font-size: 12px;
    }

    .clientes-table tbody tr:nth-child(even) td {
        background: #f7faf8;
    }

    .clientes-table tbody tr:hover td {
        background: #edf8f3;
    }

    .clientes-table tbody tr.is-inactive td {
        background: #fbf6f5;
    }

    .clientes-table tbody tr.is-inactive:hover td {
        background: #f8efed;
    }

    .clientes-name-cell {
        display: flex;
        min-width: 205px;
        align-items: center;
        gap: 9px;
    }

    .clientes-avatar {
        display: grid;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        place-items: center;
        border-radius: 9px;
        background: #e6f5ee;
        color: #15936e;
        font-size: 12px;
    }

    .clientes-name-cell strong,
    .clientes-name-cell small {
        display: block;
    }

    .clientes-name-cell strong {
        max-width: 360px;
        overflow: hidden;
        color: var(--clientes-ink);
        font-size: 13px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clientes-name-cell small {
        max-width: 360px;
        margin-top: 2px;
        overflow: hidden;
        color: var(--clientes-muted);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clientes-identifier {
        color: #334d46;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .clientes-segment {
        display: inline-block;
        max-width: 210px;
        overflow: hidden;
        padding: 4px 8px;
        border-radius: 20px;
        background: #edf5f1;
        color: #42675b;
        font-size: 10px;
        text-overflow: ellipsis;
        vertical-align: middle;
        white-space: nowrap;
    }

    .clientes-no-data {
        color: #82908b;
        font-size: 11px;
    }

    .clientes-status-column,
    .clientes-action-column {
        width: 110px;
        text-align: center !important;
    }

    .clientes-action-column {
        width: 125px;
    }

    .clientes-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 7px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .clientes-status.is-active {
        background: #e5f6ee;
        color: #147452;
    }

    .clientes-status.is-inactive {
        background: #f1e8e6;
        color: #89645d;
    }

    .btn-clientes-view {
        padding: 5px 8px;
        border: 1px solid #cde4da;
        border-radius: 5px;
        background: #fff;
        color: #087a5f;
        font-size: 11px;
        font-weight: 600;
    }

    .btn-clientes-view:hover,
    .btn-clientes-view:focus {
        border-color: var(--clientes-green);
        background: #eaf6f0;
        color: var(--clientes-ink);
    }

    .btn-clientes-view i {
        margin-right: 3px;
    }

    .clientes-listado-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 12px;
        border-top: 1px solid var(--clientes-line);
        color: var(--clientes-muted);
        font-size: 10px;
    }

    .clientes-listado-footer > span {
        flex: 0 0 auto;
    }

    .clientes-listado-footer > span i {
        margin-right: 3px;
        color: var(--clientes-green);
    }

    .clientes-listado-footer .pagination {
        margin: 0;
    }

    .clientes-listado-footer .pagination > li > a,
    .clientes-listado-footer .pagination > li > span {
        padding: 4px 8px;
        color: #087a5f;
        font-size: 11px;
    }

    .clientes-empty {
        display: flex;
        min-height: 190px;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 5px;
        padding: 20px;
        color: var(--clientes-muted);
        text-align: center;
    }

    .clientes-empty > span {
        display: grid;
        width: 42px;
        height: 42px;
        margin-bottom: 3px;
        place-items: center;
        border-radius: 12px;
        background: #e6f5ee;
        color: var(--clientes-green);
        font-size: 17px;
    }

    .clientes-empty strong {
        color: var(--clientes-ink);
        font-size: 14px;
    }

    .clientes-empty small {
        font-size: 11px;
    }

    @media (max-width: 640px) {
        .clientes-table-scroll {
            overflow: visible;
            max-height: none;
        }

        .clientes-table,
        .clientes-table tbody {
            display: block;
            width: 100%;
        }

        .clientes-table thead {
            display: none;
        }

        .clientes-table tbody tr {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0 10px;
            padding: 8px 10px;
            border-bottom: 1px solid var(--clientes-line);
        }

        .clientes-table td {
            display: block;
            padding: 5px 0;
            border: 0;
            background: transparent !important;
        }

        .clientes-table td:first-child {
            grid-column: 1 / -1;
        }

        .clientes-table td:nth-child(2),
        .clientes-table td:nth-child(3) {
            grid-column: 1;
        }

        .clientes-table td:nth-child(2)::before,
        .clientes-table td:nth-child(3)::before {
            display: inline-block;
            min-width: 82px;
            color: var(--clientes-muted);
            content: attr(data-label) ":";
            font-size: 10px;
        }

        .clientes-table td.clientes-status-column {
            grid-column: 2;
            grid-row: 2;
            align-self: center;
        }

        .clientes-table td.clientes-action-column {
            grid-column: 2;
            grid-row: 3;
            align-self: center;
        }

        .clientes-listado-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
