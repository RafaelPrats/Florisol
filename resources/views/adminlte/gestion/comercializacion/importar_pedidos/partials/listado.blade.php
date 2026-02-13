@if (count($listado) > 0)
    <div style="overflow-y: scroll; max-height: 500px">
        <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Pedido
                </th>
                <th class="text-center th_yura_green">
                    Codigo Rainbow
                </th>
                <th class="text-center th_yura_green">
                    Variedad
                </th>
                <th class="text-center th_yura_green">
                    Longitud
                </th>
                <th class="text-center th_yura_green">
                    Ramos
                </th>
                <th class="text-center th_yura_green padding_lateral_5" style="width: 60px">
                    Ramos/OT
                </th>
                <th class="text-center th_yura_green">
                    Total Ramos
                </th>
                <th class="text-center th_yura_green">
                </th>
            </tr>
            @php
                $resumen_variedades = [];
            @endphp
            @foreach ($listado as $pos_p => $ped)
                @php
                    $ped_getTotales = $ped->getTotales()->ramos;
                @endphp
                @foreach ($ped->detalles as $pos_det => $det)
                    @php
                        $variedad = $det->variedad;
                        $pos_en_resumen = -1;
                        foreach ($resumen_variedades as $pos => $r) {
                            if ($r['variedad']->id_variedad == $det->id_variedad && $r['longitud'] == $det->longitud) {
                                $pos_en_resumen = $pos;
                            }
                        }
                        if ($pos_en_resumen != -1) {
                            $resumen_variedades[$pos_en_resumen]['ramos'] += $det->ramos * $det->caja;
                        } else {
                            $resumen_variedades[] = [
                                'variedad' => $variedad,
                                'longitud' => $det->longitud,
                                'ramos' => $det->ramos * $det->caja,
                            ];
                        }
                    @endphp
                    <tr class="tr_pedido_{{ $ped->id_import_pedido }}"
                        onmouseover="$('.tr_pedido_{{ $ped->id_import_pedido }}').addClass('bg-yura_dark')"
                        onmouseleave="$('.tr_pedido_{{ $ped->id_import_pedido }}').removeClass('bg-yura_dark')">
                        @if ($pos_det == 0)
                            <th class="text-center" style="border-color: #9d9d9d" rowspan="{{ count($ped->detalles) }}">
                                #{{ $ped->codigo }}
                                <br>
                                {{ $ped->cliente->detalle()->nombre }}
                                <br>
                                {{ convertDateToText($ped->fecha) }}

                                @if ($det->ejecutado == 1)
                                    <br>
                                    <span class="badge bg-yura_primary">
                                        <i class="fa fa-fw fa-check"></i> Despachado
                                    </span>
                                @endif
                            </th>
                        @endif
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $variedad->siglas }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $variedad->nombre }}
                        </th>
                        <td class="text-right padding_lateral_5" style="border-color: #9d9d9d">
                            <b>{{ $det->longitud }}</b><sup>cm</sup>
                        </td>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->ramos }}
                            <button type="button" class="btn btn-xs btn-yura_default pull-right"
                                title="Distribuir Receta"
                                onclick="admin_receta('{{ $det->id_detalle_import_pedido }}')">
                                <i class="fa fa-fw fa-gift"></i>
                            </button>
                        </th>
                        <th class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $det->getCantidadRamosOT() }}
                        </th>
                        @if ($pos_det == 0)
                            <th class="text-center" style="border-color: #9d9d9d"
                                rowspan="{{ count($ped->detalles) }}">
                                {{ number_format($ped_getTotales) }}
                            </th>
                        @endif
                        @if ($pos_det == 0)
                            <th class="text-center" style="border-color: #9d9d9d"
                                rowspan="{{ count($ped->detalles) }}">
                                <div class="btn-group">
                                    @if ($det->ejecutado == 0)
                                        <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                                            data-toggle="dropdown">
                                            <i class="fa fa-fw fa-gears"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right sombra_pequeña" role="menu"
                                            style="z-index: 10 !important">
                                            <li>
                                                <a href="javascript:void(0)"
                                                    onclick="mover_fecha_pedido('{{ $ped->id_import_pedido }}')">
                                                    <i class="fa fa-fw fa-calendar"></i> Mover Fecha del Pedido
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" title="Eliminar"
                                                    onclick="eliminar_pedido('{{ $ped->id_import_pedido }}')">
                                                    <i class="fa fa-fw fa-trash"></i> Eliminar Pedido
                                                </a>
                                            </li>
                                        </ul>
                                    @endif
                                </div>
                            </th>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-4" style="overflow-y: scroll; max-height: 250px; margin-top: 5px;">
            <table class="table-bordered pull-right" style="width: 100%; border: 1px solid #9d9d9d"
                id="table_resumen_variedades">
                <thead>
                    <tr class="tr_fija_top_0">
                        <th class="text-center th_yura_green">
                            Codigo Rainbow
                        </th>
                        <th class="text-center th_yura_green">
                            Variedad
                        </th>
                        <th class="text-center th_yura_green">
                            Longitud
                        </th>
                        <th class="text-center th_yura_green">
                            Ramos
                        </th>
                    </tr>
                </thead>
                @php
                    $total_ramos = 0;
                @endphp
                <tbody>
                    @foreach ($resumen_variedades as $r)
                        @php
                            $total_ramos += $r['ramos'];
                        @endphp
                        <tr onmouseover="$(this).addClass('bg-yura_dark')"
                            onmouseleave="$(this).removeClass('bg-yura_dark')">
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->siglas }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['variedad']->nombre }}
                            </th>
                            <th class="text-center" style="border-color: #9d9d9d">
                                {{ $r['longitud'] }} <sup><b>cm</b></sup>
                            </th>
                            <td class="text-center" style="border-color: #9d9d9d">
                                {{ number_format($r['ramos']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tr class="tr_fija_bottom_0">
                    <th class="text-center th_yura_green" colspan="3">
                        TOTALES
                    </th>
                    <th class="text-center th_yura_green">
                        {{ number_format($total_ramos) }}
                    </th>
                </tr>
            </table>
        </div>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif

<script>
    estructura_tabla('table_resumen_variedades');
    $('#table_resumen_variedades_filter').addClass('hidden');

    function eliminar_pedido(ped) {
        texto =
            "<div class='alert alert-warning text-center' style='margin-bottom: 0; font-size: 16px'>¡Esta a punto de <b>ELIMINAR</b> el pedido!</div>" +
            "<legend style='font-size: 1.2em; margin-top: 10px' class='text-center'>¿Desea guardarlo como cambio?</legend>" +
            "<div style='display: flex;align-items: center;justify-content: space-evenly; margin-top: 0'>" +
            "<div>" +
            "<input type='radio' name='check_guarda_cambio' value='NO' style='width: 18px;height: 18px;' id=no_guarda_cambio'>" +
            "<label style='font-size: 18px;position: relative;bottom: 4px;' for='no_guarda_cambio'><b>NO</b></label>" +
            "</div>" +
            "<div>" +
            "<input type='radio' name='check_guarda_cambio' value='SI' style='width: 18px;height: 18px;' id='si_guarda_cambio' checked=''>" +
            "<label style='font-size: 18px;position: relative;bottom: 4px;' for='si_guarda_cambio'><b>SI</b></label>" +
            "</div>" +
            "</div>";

        modal_quest('modal_eliminar_pedido', texto, 'Eliminar pedido', true, false, '40%', function() {
            if ($('#si_guarda_cambio').prop('checked') == true)
                guardar_cambio = 1;
            else
                guardar_cambio = 0;

            datos = {
                _token: '{{ csrf_token() }}',
                id_pedido: ped,
                guardar_cambio: guardar_cambio,
            };
            post_jquery_m('importar_pedidos/eliminar_pedido', datos, function() {
                cerrar_modals();
                listar_reporte();
            });

        })
    }

    function admin_receta(det_ped) {
        datos = {
            det_ped: det_ped,
        };
        get_jquery('{{ url('importar_pedidos/admin_receta') }}', datos, function(retorno) {
            modal_view('modal_admin_receta', retorno, '<i class="fa fa-fw fa-gift"></i> Administrar receta',
                true, false, '{{ isPC() ? '95%' : '' }}');
        });
    }

    function mover_fecha_pedido(ped) {
        datos = {
            ped: ped,
        };
        get_jquery('{{ url('importar_pedidos/mover_fecha_pedido') }}', datos, function(retorno) {
            modal_view('modal_mover_fecha_pedido', retorno,
                '<i class="fa fa-fw fa-calendar"></i> Mover fecha del Pedido',
                true, false, '{{ isPC() ? '50%' : '' }}');
        });
    }
</script>
