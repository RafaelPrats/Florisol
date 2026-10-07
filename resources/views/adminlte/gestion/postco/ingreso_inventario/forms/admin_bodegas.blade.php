<div class="ingreso-bodegas">
    <div class="ingreso-bodegas-intro">
        <span><i class="fa fa-cogs"></i></span>
        <div>
            <strong>Destino de recepción por segmento</strong>
            <p>Define a qué bodega se asignarán por defecto las flores recibidas de cada segmento.</p>
        </div>
    </div>

    <div class="ingreso-bodegas-table-wrap">
        <table class="ingreso-bodegas-table">
            <thead>
                <tr>
                    <th>Segmento</th>
                    <th style="width: 190px">Bodega de destino</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($segmentos as $segmento)
                    <tr>
                        <td>
                            <i class="fa fa-tag ingreso-segment-icon"></i>
                            {{ $segmento->nombre }}
                        </td>
                        <td>
                            <select class="form-control"
                                aria-label="Bodega de {{ $segmento->nombre }}"
                                onchange="update_bodega('{{ $segmento->id_segmento }}', $(this).val())">
                                <option value="V" {{ $segmento->bodega == 'V' ? 'selected' : '' }}>Ventas</option>
                                <option value="P" {{ $segmento->bodega == 'P' ? 'selected' : '' }}>Producción</option>
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="ingreso-bodegas-empty">
                            <i class="fa fa-info-circle"></i> No hay segmentos para configurar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .ingreso-bodegas {
        color: #003729;
        font-size: 13px;
    }

    .ingreso-bodegas-intro {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 11px;
        padding: 11px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
    }

    .ingreso-bodegas-intro > span {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: 9px;
        background: #e4f5ee;
        color: #00B388;
        font-size: 15px;
    }

    .ingreso-bodegas-intro strong {
        font-size: 13px;
    }

    .ingreso-bodegas-intro p {
        margin: 3px 0 0;
        color: #5A7177;
        font-size: 11px;
    }

    .ingreso-bodegas-table-wrap {
        max-height: min(60vh, 520px);
        overflow: auto;
        border: 1px solid #dce9e4;
        border-radius: 7px;
    }

    .ingreso-bodegas-table {
        width: 100%;
        min-width: 360px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .ingreso-bodegas-table th,
    .ingreso-bodegas-table td {
        padding: 6px 9px;
        border-right: 1px solid #e4eee9;
        border-bottom: 1px solid #e4eee9;
        vertical-align: middle;
    }

    .ingreso-bodegas-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #003729;
        color: #fff;
        font-size: 12px;
    }

    .ingreso-bodegas-table tbody tr:nth-child(even) {
        background: #f6faf8;
    }

    .ingreso-bodegas-table .form-control {
        height: 32px;
        border-color: #dce9e4;
        font-size: 12px;
    }

    .ingreso-segment-icon {
        margin-right: 5px;
        color: #00B388;
    }

    .ingreso-bodegas-empty {
        padding: 20px !important;
        color: #5A7177;
        text-align: center;
    }
</style>

<script>
    function update_bodega(id_segmento, bodega) {
        var datos = {
            _token: '{{ csrf_token() }}',
            id_segmento: id_segmento,
            bodega: bodega,
        };
        post_jquery_m('{{ url('ingreso_inventario/update_bodega') }}', datos, function() {});
    }
</script>
