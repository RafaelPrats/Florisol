<div class="ingreso-move">
    <div class="ingreso-move-summary">
        <div class="ingreso-move-icon"><i class="fa fa-exchange"></i></div>
        <div class="ingreso-move-variety">
            <small>Movimiento de inventario</small>
            <strong>{{ $model->variedad->nombre }}</strong>
            <span>{{ $model->variedad->planta->nombre }} · {{ $model->longitud }} cm</span>
        </div>
        <div class="ingreso-move-stock">
            <span>Disponible en origen</span>
            <strong>{{ number_format($model->disponibles) }}</strong>
            <small>tallos</small>
        </div>
    </div>

    <div class="ingreso-move-flow">
        <section class="ingreso-move-step">
            <span class="ingreso-move-step-label"><i class="fa fa-arrow-up"></i> Origen</span>
            <label>Fecha del lote
                <input type="date" class="form-control" id="original_fecha" value="{{ $model->fecha }}" readonly>
            </label>
            <label>Bodega actual
                <select id="original_bodega" class="form-control" disabled>
                    <option value="V" {{ $model->bodega == 'V' ? 'selected' : '' }}>Ventas</option>
                    <option value="P" {{ $model->bodega == 'P' ? 'selected' : '' }}>Producción</option>
                </select>
            </label>
        </section>

        <div class="ingreso-move-arrow" aria-hidden="true"><i class="fa fa-long-arrow-right"></i></div>

        <section class="ingreso-move-step ingreso-move-destination">
            <span class="ingreso-move-step-label"><i class="fa fa-arrow-down"></i> Destino</span>
            <label>Fecha del movimiento
                <input type="date" class="form-control" value="{{ hoy() }}" id="mover_fecha">
            </label>
            <label>Bodega destino
                <select id="mover_bodega" class="form-control" disabled>
                    <option value="V" {{ $model->bodega == 'P' ? 'selected' : '' }}>Ventas</option>
                    <option value="P" {{ $model->bodega == 'V' ? 'selected' : '' }}>Producción</option>
                </select>
            </label>
        </section>
    </div>

    <div class="ingreso-move-quantity">
        <label for="mover_tallos"><i class="fa fa-cubes"></i> Tallos a mover</label>
        <div class="input-group">
            <input type="number" min="1" max="{{ $model->disponibles }}" step="1" inputmode="numeric"
                id="mover_tallos" class="form-control" value="0">
            <span class="input-group-addon">tallos</span>
        </div>
        <small>Máximo disponible: {{ number_format($model->disponibles) }} tallos</small>
    </div>

    <input type="hidden" id="inventario_mover" value="{{ $model->id_inventario_recepcion }}">

    <div class="ingreso-move-footer">
        <span><i class="fa fa-info-circle"></i> Se registrará una salida de la bodega actual y una entrada en la bodega
            destino.</span>
        <button type="button" class="btn btn-yura_primary" onclick="store_movimiento()">
            <i class="fa fa-exchange"></i> Confirmar movimiento
        </button>
    </div>
</div>

<style>
    .ingreso-move {
        --move-green: #00B388;
        --move-ink: #003729;
        --move-muted: #5A7177;
        --move-line: #dce9e4;
        color: var(--move-ink);
        font-size: 13px;
    }

    .ingreso-move-summary {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 12px;
        border: 1px solid var(--move-line);
        border-radius: 9px;
        background: #f4faf7;
    }

    .ingreso-move-icon {
        display: grid;
        width: 39px;
        height: 39px;
        flex: 0 0 39px;
        place-items: center;
        border-radius: 11px;
        background: #e3f5ee;
        color: var(--move-green);
        font-size: 16px;
    }

    .ingreso-move-variety {
        display: flex;
        min-width: 0;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 2px;
    }

    .ingreso-move-variety small,
    .ingreso-move-variety span {
        color: var(--move-muted);
        font-size: 11px;
    }

    .ingreso-move-variety strong {
        overflow: hidden;
        font-size: 15px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ingreso-move-stock {
        flex: 0 0 auto;
        text-align: right;
    }

    .ingreso-move-stock span,
    .ingreso-move-stock small {
        display: block;
        color: var(--move-muted);
        font-size: 10px;
    }

    .ingreso-move-stock strong {
        color: var(--move-ink);
        font-size: 19px;
        line-height: 1.2;
    }

    .ingreso-move-flow {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 30px minmax(0, 1fr);
        align-items: center;
        gap: 9px;
        margin-top: 12px;
    }

    .ingreso-move-step {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px;
        padding: 11px;
        border: 1px solid var(--move-line);
        border-radius: 8px;
        background: #fff;
    }

    .ingreso-move-step-label {
        grid-column: 1 / -1;
        color: var(--move-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .ingreso-move-step-label i {
        margin-right: 3px;
        color: var(--move-green);
    }

    .ingreso-move-step label,
    .ingreso-move-quantity label {
        display: block;
        min-width: 0;
        margin: 0;
        color: var(--move-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .ingreso-move .form-control {
        height: 34px;
        margin-top: 4px;
        border-color: var(--move-line);
        border-radius: 5px;
        box-shadow: none;
        color: var(--move-ink);
        font-size: 12px;
    }

    .ingreso-move .form-control:disabled,
    .ingreso-move .form-control[readonly] {
        background: #f2f6f4;
        opacity: 1;
    }

    .ingreso-move-arrow {
        color: var(--move-green);
        font-size: 18px;
        text-align: center;
    }

    .ingreso-move-quantity {
        max-width: 340px;
        margin: 13px auto 0;
    }

    .ingreso-move-quantity label {
        margin-bottom: 5px;
        color: var(--move-ink);
        font-size: 12px;
    }

    .ingreso-move-quantity label i {
        color: var(--move-green);
    }

    .ingreso-move-quantity .form-control {
        height: 39px;
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        text-align: center;
    }

    .ingreso-move-quantity .input-group-addon {
        border-color: var(--move-line);
        background: #f4faf7;
        color: var(--move-muted);
    }

    .ingreso-move-quantity > small {
        display: block;
        margin-top: 4px;
        color: var(--move-muted);
        font-size: 10px;
        text-align: right;
    }

    .ingreso-move-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 13px;
        padding-top: 10px;
        border-top: 1px solid var(--move-line);
    }

    .ingreso-move-footer span {
        color: var(--move-muted);
        font-size: 10px;
    }

    .ingreso-move-footer span i {
        color: var(--move-green);
    }

    .ingreso-move-footer .btn {
        flex: 0 0 auto;
        padding: 8px 11px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    .ingreso-confirm {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #dce9e4;
        border-radius: 8px;
        background: #f4faf7;
        color: #003729;
    }

    .ingreso-confirm-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 9px;
        background: #e4f5ee;
        color: #00B388;
    }

    .ingreso-confirm strong {
        display: block;
        margin-bottom: 3px;
        font-size: 13px;
    }

    .ingreso-confirm p {
        margin: 0;
        color: #5A7177;
        font-size: 11px;
    }

    @media (max-width: 650px) {
        .ingreso-move-flow {
            grid-template-columns: 1fr;
        }

        .ingreso-move-arrow {
            transform: rotate(90deg);
        }

        .ingreso-move-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .ingreso-move-footer .btn {
            width: 100%;
        }
    }

    @media (max-width: 400px) {
        .ingreso-move-step {
            grid-template-columns: 1fr;
        }

        .ingreso-move-step-label {
            grid-column: auto;
        }
    }
</style>

<script>
    function store_movimiento() {
        var cantidad = Number($('#mover_tallos').val());
        var maximo = Number($('#mover_tallos').attr('max'));
        if (!Number.isInteger(cantidad) || cantidad < 1 || cantidad > maximo) {
            alerta_errores("<div class='alert alert-warning text-center'>Ingresa una cantidad entera entre 1 y " +
                maximo + " tallos.</div>");
            return false;
        }

        var texto =
            "<div class='ingreso-confirm'><div class='ingreso-confirm-icon'><i class='fa fa-exchange'></i></div>" +
            "<div><strong>¿Confirmas el movimiento?</strong><p>Se moverán " + cantidad +
            " tallos a la bodega de destino.</p></div></div>";

        modal_quest('modal_store_movimiento', texto, 'Mover inventario', true, false, 'min(440px, 94vw)', function() {
            var datos = {
                _token: '{{ csrf_token() }}',
                id_inventario: $('#inventario_mover').val(),
                mover_tallos: cantidad,
                mover_bodega: $('#mover_bodega').val(),
                mover_fecha: $('#mover_fecha').val(),
            };
            post_jquery_m('{{ url('ingreso_inventario/store_movimiento') }}', datos, function() {
                cerrar_modals();
                listar_reporte();
            });
        });
    }
</script>
