<style>
    .copiar-pedido {
        --copiar-green: #176b52;
        --copiar-accent: #00a77d;
        --copiar-ink: #30483e;
        --copiar-muted: #74857c;
        --copiar-border: #e1ebe5;
        color: var(--copiar-ink);
        padding: 4px;
    }

    .copiar-pedido .copiar-hero {
        position: relative;
        overflow: hidden;
        margin-bottom: 16px;
        padding: 18px;
        border: 1px solid #dbece1;
        border-radius: 12px;
        background: linear-gradient(120deg, #edf8f1 0%, #f9fcfa 65%, #fff 100%);
    }

    .copiar-pedido .copiar-hero:after {
        position: absolute;
        top: -38px;
        right: -30px;
        width: 150px;
        height: 150px;
        border: 22px solid rgba(0, 167, 125, .06);
        border-radius: 50%;
        content: '';
        pointer-events: none;
    }

    .copiar-pedido .copiar-hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-start;
        gap: 13px;
    }

    .copiar-pedido .copiar-hero-icon {
        display: inline-flex;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: var(--copiar-green);
        color: #fff;
        font-size: 18px;
    }

    .copiar-pedido .copiar-hero h4 {
        margin: 1px 0 5px;
        color: var(--copiar-ink);
        font-size: 16px;
        font-weight: 700;
    }

    .copiar-pedido .copiar-hero p {
        margin: 0;
        color: var(--copiar-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .copiar-pedido .copiar-client-name {
        color: var(--copiar-green);
        font-weight: 700;
    }

    .copiar-pedido .copiar-source {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
    }

    .copiar-pedido .copiar-source-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border: 1px solid #e0ece4;
        border-radius: 7px;
        background: rgba(255, 255, 255, .78);
        color: #50665a;
        font-size: 11px;
    }

    .copiar-pedido .copiar-source-item i {
        color: var(--copiar-accent);
    }

    .copiar-pedido .copiar-dates-panel {
        overflow: hidden;
        border: 1px solid var(--copiar-border);
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(30, 66, 47, .04);
    }

    .copiar-pedido .copiar-dates-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 15px;
        border-bottom: 1px solid #edf2ef;
        background: #fcfdfc;
    }

    .copiar-pedido .copiar-dates-heading h5 {
        margin: 0;
        color: var(--copiar-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .copiar-pedido .copiar-dates-heading small {
        display: block;
        margin-top: 4px;
        color: var(--copiar-muted);
        font-size: 11px;
    }

    .copiar-pedido .copiar-dates-count {
        flex: 0 0 auto;
        padding: 5px 9px;
        border-radius: 20px;
        background: #eaf6ef;
        color: var(--copiar-green);
        font-size: 11px;
        font-weight: 700;
    }

    .copiar-pedido .copiar-dates-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        max-height: 300px;
        overflow: auto;
        padding: 13px;
    }

    .copiar-pedido .copiar-date-card {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 10px;
        border: 1px solid #e4ece7;
        border-radius: 9px;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .copiar-pedido .copiar-date-card:focus-within {
        border-color: #78bda2;
        box-shadow: 0 0 0 3px rgba(0, 167, 125, .09);
    }

    .copiar-pedido .copiar-date-number {
        display: inline-flex;
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #edf7f1;
        color: var(--copiar-green);
        font-size: 12px;
        font-weight: 700;
    }

    .copiar-pedido .copiar-date-input-wrap {
        min-width: 0;
        flex: 1 1 auto;
    }

    .copiar-pedido .copiar-date-input-wrap label {
        display: block;
        margin-bottom: 4px;
        color: var(--copiar-muted);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .copiar-pedido .copiar-date-input {
        width: 100%;
        min-width: 0;
        height: 34px;
        padding: 5px 7px;
        border: 1px solid #dce7e1;
        border-radius: 6px;
        background: #fff;
        color: var(--copiar-ink);
        font-size: 12px;
    }

    .copiar-pedido .copiar-date-input:focus {
        border-color: #66b59a;
        outline: 0;
    }

    .copiar-pedido .copiar-remove-date {
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        padding: 0;
        border: 1px solid #f0dddd;
        border-radius: 7px;
        background: #fff8f8;
        color: #b55757;
    }

    .copiar-pedido .copiar-remove-date:hover {
        border-color: #d58a8a;
        background: #b55757;
        color: #fff;
    }

    .copiar-pedido .copiar-add-date {
        display: flex;
        width: calc(100% - 26px);
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin: 0 13px 13px;
        border: 1px dashed #9fcab3;
        border-radius: 8px;
        background: #f8fcf9;
        color: var(--copiar-green);
        font-size: 12px;
        font-weight: 700;
        transition: background .15s ease, border-color .15s ease;
    }

    .copiar-pedido .copiar-add-date:hover {
        border-color: var(--copiar-accent);
        background: #eef8f3;
    }

    .copiar-pedido .copiar-error {
        margin: 12px 0 0;
        padding: 9px 11px;
        border: 1px solid #f1d6d6;
        border-radius: 7px;
        background: #fff7f7;
        color: #a84646;
        font-size: 12px;
    }

    .copiar-pedido .copiar-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #edf2ef;
    }

    .copiar-pedido .copiar-footnote {
        max-width: 65%;
        margin: 0;
        color: var(--copiar-muted);
        font-size: 10px;
        line-height: 1.45;
    }

    .copiar-pedido .copiar-submit {
        min-height: 39px;
        padding: 8px 16px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 600px) {
        .copiar-pedido .copiar-dates-list {
            grid-template-columns: minmax(0, 1fr);
            max-height: 250px;
            padding: 10px;
        }

        .copiar-pedido .copiar-hero {
            padding: 13px;
        }

        .copiar-pedido .copiar-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .copiar-pedido .copiar-footnote {
            max-width: 100%;
        }

        .copiar-pedido .copiar-submit {
            width: 100%;
        }
    }
</style>

<div class="copiar-pedido">
    <section class="copiar-hero">
        <div class="copiar-hero-content">
            <span class="copiar-hero-icon"><i class="fa fa-fw fa-files-o"></i></span>
            <div>
                <h4>Repite este pedido en otras fechas</h4>
                <p>
                    Se crearán pedidos Open Market para
                    <span class="copiar-client-name">{{ $pedido->cliente->detalle()->nombre }}</span>
                    con la misma configuración de cajas y variedades.
                </p>
            </div>
        </div>
        <div class="copiar-source">
            <span class="copiar-source-item">
                <i class="fa fa-calendar"></i> Pedido original: {{ $pedido->fecha }}
            </span>
            <span class="copiar-source-item">
                <i class="fa fa-cubes"></i> {{ $pedido->cajas->count() }} {{ $pedido->cajas->count() == 1 ? 'configuración de caja' : 'configuraciones de caja' }}
            </span>
        </div>
    </section>

    <section class="copiar-dates-panel">
        <div class="copiar-dates-heading">
            <div>
                <h5><i class="fa fa-calendar-plus-o"></i> Fechas de los nuevos pedidos</h5>
                <small>Agrega una fecha por cada pedido que quieras crear.</small>
            </div>
            <span class="copiar-dates-count" id="copiar_cantidad_fechas">1 fecha</span>
        </div>
        <div class="copiar-dates-list" id="lista_fechas_copiar">
            <div class="copiar-date-card" id="tr_copiar_fecha_1">
                <span class="copiar-date-number">1</span>
                <div class="copiar-date-input-wrap">
                    <label for="copiar_fecha_1">Fecha de entrega</label>
                    <input type="date" class="copiar-date-input copiar-fecha-input" id="copiar_fecha_1"
                        min="{{ hoy() }}" required>
                </div>
                <button type="button" class="copiar-remove-date" onclick="eliminar_fecha(1)"
                    title="Quitar fecha" aria-label="Quitar fecha 1">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        <button type="button" class="copiar-add-date" onclick="add_fecha()">
            <i class="fa fa-plus-circle"></i> Agregar otra fecha
        </button>
    </section>

    <div class="copiar-error hidden" id="copiar_pedido_error" role="alert"></div>

    <div class="copiar-actions">
        <p class="copiar-footnote">
            Cada fecha seleccionada generará un pedido independiente. Evita repetir fechas en esta lista.
        </p>
        <button type="button" class="btn btn-yura_primary copiar-submit"
            onclick="store_copiar_pedido('{{ $pedido->id_proyecto }}')">
            <i class="fa fa-fw fa-copy"></i> Crear pedidos
            <span class="badge" id="copiar_boton_cantidad">1</span>
        </button>
    </div>
</div>

<script>
    var cantidad_fechas = 1;

    function actualizar_cantidad_fechas() {
        var $fechas = $('.copiar-fecha-input');
        var cantidad = $fechas.length;
        $('#copiar_cantidad_fechas').text(cantidad + (cantidad === 1 ? ' fecha' : ' fechas'));
        $('#copiar_boton_cantidad').text(cantidad);
        $fechas.each(function(indice) {
            $(this).closest('.copiar-date-card').find('.copiar-date-number').text(indice + 1);
        });
    }

    $('#lista_fechas_copiar').on('change', '.copiar-fecha-input', function() {
        $('#copiar_pedido_error').addClass('hidden').text('');
    });

    function add_fecha() {
        cantidad_fechas++;
        var numero = cantidad_fechas;
        $('#lista_fechas_copiar').append(
            '<div class="copiar-date-card" id="tr_copiar_fecha_' + numero + '">' +
            '<span class="copiar-date-number">' + numero + '</span>' +
            '<div class="copiar-date-input-wrap">' +
            '<label for="copiar_fecha_' + numero + '">Fecha de entrega</label>' +
            '<input type="date" class="copiar-date-input copiar-fecha-input" id="copiar_fecha_' + numero +
            '" min="{{ hoy() }}" required>' +
            '</div>' +
            '<button type="button" class="copiar-remove-date" onclick="eliminar_fecha(' + numero +
            ')" title="Quitar fecha" aria-label="Quitar fecha ' + numero + '">' +
            '<i class="fa fa-trash"></i>' +
            '</button>' +
            '</div>'
        );
        actualizar_cantidad_fechas();
        $('#copiar_fecha_' + numero).trigger('focus');
    }

    function eliminar_fecha(numero) {
        $('#tr_copiar_fecha_' + numero).remove();
        actualizar_cantidad_fechas();
    }

    function store_copiar_pedido(id_ped) {
        var fechas = [];
        var fechasVistas = {};
        var error = '';
        $('.copiar-fecha-input').each(function() {
            var fecha = $(this).val();
            if (!fecha) {
                error = 'Completa todas las fechas antes de crear los pedidos.';
                return false;
            }
            if (fecha < $(this).attr('min')) {
                error = 'Selecciona fechas iguales o posteriores a hoy.';
                return false;
            }
            if (fechasVistas[fecha]) {
                error = 'Hay una fecha repetida. Cada pedido debe tener una fecha distinta.';
                return false;
            }
            fechasVistas[fecha] = true;
            fechas.push(fecha);
        });

        if (!error && fechas.length === 0)
            error = 'Agrega al menos una fecha para copiar el pedido.';

        if (error) {
            $('#copiar_pedido_error').text(error).removeClass('hidden');
            return;
        }

        $('#copiar_pedido_error').addClass('hidden').text('');
        datos = {
            _token: '{{ csrf_token() }}',
            data: JSON.stringify(fechas),
            id_ped: id_ped
        }
        post_jquery_m('{{ url('proyectos/store_copiar_pedido') }}', datos, function() {
            listar_reporte();
            cerrar_modals();
        })
    }
</script>
