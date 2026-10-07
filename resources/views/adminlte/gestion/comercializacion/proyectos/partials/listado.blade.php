<div class="proyectos-listado-scroll">
    <table class="table-bordered proyectos-listado-table" style="width: 100%; border: 1px solid #9d9d9d">
        <thead>
            <tr class="proyectos-listado-header" style="font-size: 0.8em">
                <th class="text-center th_yura_green">
                    <input type="checkbox" onchange="$('.check_proyecto').prop('checked', $(this).prop('checked'))">
                </th>
                <th class="text-center th_yura_green">
                    PACKING
                </th>
                <th class="text-center th_yura_green">
                    CLIENTE
                </th>
                <th class="text-center th_yura_green">
                    TIPO
                </th>
                <th class="text-center th_yura_green">
                    FECHA
                </th>
                <th class="text-center th_yura_green">
                    CAJAS
                </th>
                <th class="text-center th_yura_green">
                    BQT
                </th>
                <th class="text-center th_yura_green" colspan="2">
                    BUNCHES
                </th>
                <th class="text-center th_yura_green" colspan="2">
                    STEMS
                </th>
                <th class="text-center th_yura_green">
                    PRECIO
                </th>
                <th class="text-center th_yura_green">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody>
        @php
            $resumen_variedad_longitud = [];
            $resumen_piezas = [];
        @endphp
        @foreach ($proyectos as $proyecto)
            @php
                $cant_rows = 0;
                $cajas = $proyecto->cajas;
                $ramos_proy = 0;
                $tallos_proy = 0;
                $monto_proy = 0;
                $cajas_proy = 0;
                foreach ($cajas as $c) {
                    $cajas_proy += $c->cantidad;
                    foreach ($c->detalles as $d) {
                        $cant_rows++;
                        $ramos_proy += $d->ramos_x_caja * $c->cantidad;
                        $tallos_proy += $d->tallos_x_ramo * $d->ramos_x_caja * $c->cantidad;
                        $monto_proy += $d->precio * $d->ramos_x_caja * $c->cantidad;
                    }
                }
            @endphp
            @foreach ($cajas as $pos_c => $caja)
                @php
                    $detalles = $caja->detalles;

                    //RESUMEN PIEZAS
                    $pos_en_resumen = -1;
                    foreach ($resumen_piezas as $pos => $r) {
                        if ($r['tipo_caja'] == $caja->tipo_caja) {
                            $pos_en_resumen = $pos;
                        }
                    }
                    if ($pos_en_resumen != -1) {
                        $resumen_piezas[$pos_en_resumen]['cantidad'] += $caja->cantidad;
                    } else {
                        $resumen_piezas[] = [
                            'tipo_caja' => $caja->tipo_caja,
                            'cantidad' => $caja->cantidad,
                        ];
                    }
                @endphp
                @foreach ($detalles as $pos_d => $detalle)
                    @php
                        $variedad = $detalle->variedad;
                        $tallos = $caja->cantidad * $detalle->ramos_x_caja * $detalle->tallos_x_ramo;
                        $ramos = $caja->cantidad * $detalle->ramos_x_caja;
                        $venta = $caja->cantidad * $detalle->ramos_x_caja * $detalle->precio;

                        //RESUMEN VARIEDAD/LONGITUD
                        $pos_en_resumen = -1;
                        foreach ($resumen_variedad_longitud as $pos => $r) {
                            if (
                                $r['id_variedad'] == $detalle->id_variedad &&
                                $r['longitud'] == $detalle->longitud_ramo
                            ) {
                                $pos_en_resumen = $pos;
                            }
                        }
                        if ($pos_en_resumen != -1) {
                            $resumen_variedad_longitud[$pos_en_resumen]['tallos'] += $tallos;
                            $resumen_variedad_longitud[$pos_en_resumen]['ramos'] += $ramos;
                            $resumen_variedad_longitud[$pos_en_resumen]['venta'] += $venta;
                        } else {
                            $resumen_variedad_longitud[] = [
                                'id_variedad' => $detalle->id_variedad,
                                'longitud' => $detalle->longitud_ramo,
                                'nombre_planta' => $variedad->planta->nombre,
                                'nombre_variedad' => $variedad->nombre,
                                'tallos' => $tallos,
                                'ramos' => $ramos,
                                'venta' => $venta,
                            ];
                        }
                    @endphp
                    <tr class="tr_pedido_{{ $proyecto->id_proyecto }}" style="font-size: 0.9em"
                        onmouseover="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', 'cyan')"
                        onmouseleave="$('.tr_pedido_{{ $proyecto->id_proyecto }}').css('background-color', '')">
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <input type="checkbox" id="check_proyecto_{{ $proyecto->id_proyecto }}"
                                    class="check_proyecto">
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <small>{{ $proyecto->segmento }}</small>
                                @if ($proyecto->packing != '')
                                    <br>
                                    {{ $proyecto->packing }}
                                @endif
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->cliente->detalle()->nombre }}
                                <br>
                                <small>Ventas: ${{ $monto_proy }}</small>
                                <br>
                                <small>Cajas: {{ $cajas_proy }}</small>
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->tipo }}
                                @if ($proyecto->tipo == 'SO')
                                    <br>
                                    #{{ $proyecto->orden_fija }}
                                @endif
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $proyecto->fecha }}
                            </th>
                        @endif
                        @if ($pos_d == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($detalles) }}">
                                {{ $caja->cantidad }} <small>{{ $caja->tipo_caja }}</small>
                                <br>
                                @foreach ($caja->marcaciones as $marcacion)
                                    <small><em>{{ $marcacion->valor }}</em></small>
                                @endforeach
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->ramos_x_caja }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $ramos_proy }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            {{ $detalle->tallos_x_ramo }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                {{ $tallos_proy }}
                            </th>
                        @endif
                        <th class="text-center" style="border-color: #9d9d9d">
                            ${{ $detalle->precio }}
                        </th>
                        @if ($pos_d == 0 && $pos_c == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ $cant_rows }}">
                                <div class="input-group-btn">
                                    <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle btn-block"
                                        data-toggle="dropdown" aria-expanded="true">
                                        Acciones <span class="fa fa-caret-down"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right sombra_pequeña"
                                        style="background-color: #c8c8c8;">
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="editar_proyecto('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-fw fa-pencil"></i>
                                                Editar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" style="color: black"
                                                onclick="copiar_pedido('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-files-o fa-fw" aria-hidden="true"></i>
                                                Copiar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link_opciones_{{ $proyecto->id_proyecto }}"
                                                href="javascript:void(0)" id="edit_pedidos" style="color: black"
                                                onclick="delete_pedido('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-fw fa-trash" aria-hidden="true"></i>
                                                Cancelar pedido
                                            </a>
                                        </li>
                                        <li>
                                            <a style="color: black" href="javascript:void(0)"
                                                onclick="descargar_despachos('{{ $proyecto->id_proyecto }}')">
                                                <i class="fa fa-cubes fa-fw"></i>
                                                Despachos
                                            </a>
                                        </li>
                                        @if ($proyecto->id_empresa == 2)
                                            <li>
                                                <a style="color: black" href="javascript:void(0)"
                                                    onclick="enviar_notificacion('{{ $proyecto->id_proyecto }}')">
                                                    <i class="fa fa-send fa-fw"></i>
                                                    Notificar a fincas
                                                </a>
                                            </li>
                                            <li>
                                                <a style="color: black" href="javascript:void(0)"
                                                    onclick="modal_confirmaciones('{{ $proyecto->id_proyecto }}')">
                                                    <i class="fa fa-gift fa-fw"></i>
                                                    Ver Confirmaciones
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        @endforeach
        </tbody>
    </table>
</div>

<style>
    .proyectos-listado-scroll {
        position: relative;
        max-height: 700px;
        overflow: auto;
    }

    .proyectos-listado-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .proyectos-listado-header th {
        position: sticky;
        top: 0;
        z-index: 5;
        box-shadow: 0 1px 0 rgba(0, 55, 41, .2);
    }

    .proyectos-resumen {
        --resumen-green: #176b52;
        --resumen-accent: #00a77d;
        --resumen-ink: #30483e;
        --resumen-muted: #77877e;
        --resumen-border: #e1ebe5;
        margin-top: 14px;
    }

    .proyectos-resumen .resumen-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 10px;
        color: var(--resumen-ink);
        font-size: 15px;
        font-weight: 700;
    }

    .proyectos-resumen .resumen-heading-icon {
        display: inline-flex;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #eaf6ef;
        color: var(--resumen-green);
    }

    .proyectos-resumen .resumen-heading small {
        display: block;
        margin-top: 3px;
        color: var(--resumen-muted);
        font-size: 10px;
        font-weight: 400;
    }

    .proyectos-resumen .resumen-grid {
        display: grid;
        grid-template-columns: minmax(0, 2.2fr) minmax(240px, 1fr);
        gap: 12px;
        align-items: start;
    }

    .proyectos-resumen .resumen-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--resumen-border);
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(30, 66, 47, .045);
    }

    .proyectos-resumen .resumen-card-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #edf2ef;
        background: #fcfdfc;
    }

    .proyectos-resumen .resumen-card-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: var(--resumen-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .proyectos-resumen .resumen-card-title i {
        color: var(--resumen-accent);
        font-size: 14px;
    }

    .proyectos-resumen .resumen-count {
        flex: 0 0 auto;
        padding: 4px 8px;
        border-radius: 20px;
        background: #edf7f1;
        color: var(--resumen-green);
        font-size: 10px;
        font-weight: 700;
    }

    .proyectos-resumen .resumen-table-wrap {
        max-height: 300px;
        overflow: auto;
    }

    .proyectos-resumen .resumen-table {
        width: 100%;
        margin: 0;
        border: 0;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 11px;
    }

    .proyectos-resumen .resumen-table th,
    .proyectos-resumen .resumen-table td {
        padding: 9px 10px;
        border: 0;
        border-bottom: 1px solid #edf1ee;
        vertical-align: middle;
    }

    .proyectos-resumen .resumen-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f0f7f2;
        color: #597065;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .45px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .proyectos-resumen .resumen-table thead th.resumen-sortable {
        cursor: pointer;
        user-select: none;
    }

    .proyectos-resumen .resumen-table thead th.resumen-sortable:hover {
        background: #e5f1e9;
        color: var(--resumen-green);
    }

    .proyectos-resumen .resumen-sort-icon {
        margin-left: 5px;
        color: #99aa9f;
        font-size: 9px;
    }

    .proyectos-resumen .resumen-sortable[aria-sort="ascending"] .resumen-sort-icon,
    .proyectos-resumen .resumen-sortable[aria-sort="descending"] .resumen-sort-icon {
        color: var(--resumen-green);
    }

    .proyectos-resumen .resumen-table tbody tr {
        transition: background .12s ease;
    }

    .proyectos-resumen .resumen-table tbody tr:hover {
        background: #f6faf7;
    }

    .proyectos-resumen .resumen-table .resumen-plant {
        color: #52675b;
        font-size: 10px;
        font-weight: 600;
    }

    .proyectos-resumen .resumen-variedad {
        display: block;
        color: var(--resumen-ink);
        font-weight: 700;
    }

    .proyectos-resumen .resumen-longitud {
        display: inline-block;
        margin-top: 3px;
        padding: 2px 6px;
        border-radius: 10px;
        background: #f0f4f1;
        color: var(--resumen-muted);
        font-size: 9px;
        font-weight: 600;
    }

    .proyectos-resumen .resumen-number {
        color: #3d5548;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        text-align: right;
        white-space: nowrap;
    }

    .proyectos-resumen .resumen-money {
        color: var(--resumen-green);
        font-weight: 700;
    }

    .proyectos-resumen .resumen-total th,
    .proyectos-resumen .resumen-total td {
        position: sticky;
        bottom: 0;
        z-index: 1;
        border-bottom: 0;
        background: #eaf5ed;
        color: var(--resumen-green);
        font-weight: 700;
    }

    .proyectos-resumen .resumen-total-label {
        font-size: 9px;
        letter-spacing: .45px;
        text-transform: uppercase;
    }

    .proyectos-resumen .resumen-caja-type {
        color: var(--resumen-ink);
        font-size: 12px;
        font-weight: 700;
    }

    .proyectos-resumen .resumen-caja-type i {
        margin-right: 6px;
        color: #7bab8c;
        font-size: 10px;
    }

    .proyectos-resumen .resumen-caja-count {
        color: var(--resumen-ink);
        font-size: 13px;
        font-weight: 700;
    }

    .proyectos-resumen .resumen-pieces-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        background: linear-gradient(110deg, #eaf5ed, #f7fbf8);
        color: var(--resumen-green);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .proyectos-resumen .resumen-pieces-total strong {
        font-size: 20px;
        letter-spacing: 0;
    }

    @media (max-width: 767px) {
        .proyectos-resumen .resumen-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .proyectos-resumen .resumen-table-wrap {
            max-height: 260px;
        }
    }
</style>

<section class="proyectos-resumen">
    <h3 class="resumen-heading">
        <span class="resumen-heading-icon"><i class="fa fa-fw fa-bar-chart"></i></span>
        <span>Resumen de pedidos<small>Totales agrupados por variedad y tipo de caja</small></span>
    </h3>
    <div class="resumen-grid">
        <section class="resumen-card">
            <div class="resumen-card-heading">
                <div class="resumen-card-title">
                    <i class="fa fa-leaf"></i> Producción y ventas por variedad
                </div>
                <span class="resumen-count">
                    {{ count($resumen_variedad_longitud) }}
                    {{ count($resumen_variedad_longitud) == 1 ? 'combinación' : 'combinaciones' }}
                </span>
            </div>
            <div class="resumen-table-wrap">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th colspan="2">Variedad y longitud</th>
                            <th class="text-right">Tallos</th>
                            <th class="text-right">Ramos</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $total_tallos = 0;
                        $total_ramos = 0;
                        $total_monto = 0;
                    @endphp
                    @foreach ($resumen_variedad_longitud as $item)
                        @php
                            $total_tallos += $item['tallos'];
                            $total_ramos += $item['ramos'];
                            $total_monto += $item['venta'];
                        @endphp
                        <tr>
                            <td class="resumen-plant">
                                {{ $item['nombre_planta'] }}
                            </td>
                            <td>
                                <span class="resumen-variedad">{{ $item['nombre_variedad'] }}</span>
                                <span class="resumen-longitud">{{ $item['longitud'] }} cm</span>
                            </td>
                            <td class="resumen-number">
                                {{ $item['tallos'] }}
                            </td>
                            <td class="resumen-number">
                                {{ $item['ramos'] }}
                            </td>
                            <td class="resumen-number resumen-money">
                                ${{ round($item['venta'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="resumen-total">
                        <th class="resumen-total-label" colspan="2">
                            Totales
                        </th>
                        <td class="resumen-number">
                            {{ $total_tallos }}
                        </td>
                        <td class="resumen-number">
                            {{ $total_ramos }}
                        </td>
                        <td class="resumen-number">
                            ${{ round($total_monto, 2) }}
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </section>
        <section class="resumen-card">
            <div class="resumen-card-heading">
                <div class="resumen-card-title">
                    <i class="fa fa-cubes"></i> Composición de cajas
                </div>
                <span class="resumen-count">{{ count($resumen_piezas) }} tipos</span>
            </div>
            <div class="resumen-table-wrap">
                <table class="resumen-table">
                    <thead>
                        <tr>
                            <th>Tipo de caja</th>
                            <th class="text-right">Piezas</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php
                        $total_piezas = 0;
                    @endphp
                    @foreach ($resumen_piezas as $item)
                        @php
                            $total_piezas += $item['cantidad'];
                        @endphp
                        <tr>
                            <td class="resumen-caja-type">
                                <i class="fa fa-cube"></i>{{ $item['tipo_caja'] }}
                            </td>
                            <td class="resumen-number resumen-caja-count">
                                {{ $item['cantidad'] }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="resumen-pieces-total">
                <span>Total de piezas</span>
                <strong>{{ $total_piezas }}</strong>
            </div>
        </section>
    </div>
</section>

<script>
    (function() {
        var $resumenTables = $('.proyectos-resumen .resumen-table');

        $resumenTables.find('thead th').each(function() {
            $(this)
                .addClass('resumen-sortable')
                .attr({
                    'aria-sort': 'none',
                    'title': 'Ordenar por esta columna'
                })
                .append(' <i class="fa fa-sort resumen-sort-icon" aria-hidden="true"></i>');
        });

        function valor_orden(texto) {
            var limpio = texto.replace(/\s/g, '').replace(/\$/g, '').replace(/,/g, '').replace(/cm$/i, '');
            if (/^-?\d+(\.\d+)?$/.test(limpio)) {
                return {
                    numerico: true,
                    valor: parseFloat(limpio)
                };
            }
            return {
                numerico: false,
                valor: texto.trim().toLocaleLowerCase()
            };
        }

        $resumenTables.on('click', 'thead th.resumen-sortable', function() {
            var $th = $(this);
            var $table = $th.closest('table');
            var inicio = 0;
            $th.prevAll().each(function() {
                inicio += parseInt($(this).attr('colspan') || 1, 10);
            });
            var columnas = parseInt($th.attr('colspan') || 1, 10);
            var ascendente = $th.attr('aria-sort') !== 'ascending';
            var filas = $table.find('tbody tr').get();

            filas.sort(function(filaA, filaB) {
                var celdasA = $(filaA).children('td, th');
                var celdasB = $(filaB).children('td, th');
                var textoA = '';
                var textoB = '';

                for (var columna = inicio; columna < inicio + columnas; columna++) {
                    textoA += ' ' + celdasA.eq(columna).text();
                    textoB += ' ' + celdasB.eq(columna).text();
                }

                var valorA = valor_orden(textoA);
                var valorB = valor_orden(textoB);
                var comparacion;
                if (valorA.numerico && valorB.numerico) {
                    comparacion = valorA.valor - valorB.valor;
                } else {
                    comparacion = valorA.valor.localeCompare(valorB.valor, 'es', {
                        numeric: true,
                        sensitivity: 'base'
                    });
                }
                return ascendente ? comparacion : -comparacion;
            });

            $.each(filas, function(_, fila) {
                $table.children('tbody').append(fila);
            });

            $th.closest('tr').find('th.resumen-sortable')
                .attr('aria-sort', 'none')
                .find('.resumen-sort-icon')
                .removeClass('fa-sort-asc fa-sort-desc')
                .addClass('fa-sort');
            $th.attr('aria-sort', ascendente ? 'ascending' : 'descending')
                .find('.resumen-sort-icon')
                .removeClass('fa-sort')
                .addClass(ascendente ? 'fa-sort-asc' : 'fa-sort-desc');
        });
    })();

    function editar_proyecto(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/editar_proyecto') }}', datos, function(retorno) {
            modal_view('modal_editar_proyecto', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Pedido',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }

    function copiar_pedido(pedido) {
        datos = {
            pedido: pedido
        }
        get_jquery('{{ url('proyectos/copiar_pedido') }}', datos, function(retorno) {
            modal_view('modal_copiar_pedido', retorno, '<i class="fa fa-fw fa-calendar"></i> Copiar Pedido',
                true, false, '{{ isPC() ? '50%' : '' }}',
                function() {});
        });
    }

    function delete_pedido(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Cancelar Pedido',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>CANCELAR</b> este pedido?</div>',
        };
        modal_quest('modal_delete_pedido', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proyectos/delete_pedido') }}', datos, function() {
                    listar_reporte();
                });
            });
    }

    function enviar_notificacion(id) {
        mensaje = {
            title: '<i class="fa fa-fw fa-save"></i> Notificar Pedido',
            mensaje: '<div class="alert alert-warning text-center"><i class="fa fa-fw fa-exclamation-triangle"></i> ¿Está seguro de <b>NOTIFICAR</b> a las fincas sobre este pedido?</div>',
        };
        modal_quest('modal_enviar_notificacion', mensaje['mensaje'], mensaje['title'], true, false,
            '{{ isPC() ? '35%' : '' }}',
            function() {
                datos = {
                    _token: '{{ csrf_token() }}',
                    id: id
                };
                post_jquery_m('{{ url('proyectos/enviar_notificacion') }}', datos, function() {
                    listar_reporte();
                });
            });
    }

    function descargar_despachos(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('proyectos/descargar_despachos') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function modal_confirmaciones(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('proyectos/modal_confirmaciones') }}', datos, function(retorno) {
            modal_view('modal_modal_confirmaciones', retorno,
                '<i class="fa fa-fw fa-plus"></i> Flor del Pedido',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
    }
</script>
