<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="padding_lateral_5 th_yura_green">
                    Fecha
                </th>
                <th class="padding_lateral_5 th_yura_green">
                    Variedad
                </th>
                <th class="padding_lateral_5" style="width: 90px; background-color: #dddddd">
                    Ramos Ingresados
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    TxR
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Longitud
                </th>
                <th class="padding_lateral_5 bg-yura_dark" style="width: 90px">
                    Tallos Disponibles
                </th>
                <th class="text-center bg-yura_warning" style="width: 90px">
                    Botar
                </th>
                <th class="text-center bg-yura_warning mouse-hand" style="width: 90px" onclick="admin_motivos()">
                    Motivo
                    <i class="fa fa-fw fa-cogs"></i>
                </th>
                <th class="text-center bg-yura_dark" style="width: 60px">
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $ramos_pta = 0;
                    $tallos_pta = 0;
                    foreach ($item['variedades'] as $var) {
                        $ramos_pta += $var->ramos;
                        $tallos_pta += $var->disponibles;
                    }
                @endphp
                <tr style="background-color: #dddddd" class="mouse-hand"
                    onclick="$('.tr_planta_{{ $item['planta']->id_planta }}').toggleClass('hidden'); $('.tr_all_detApi_{{ $item['planta']->id_planta }}').addClass('hidden')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" colspan="2">
                        {{ $item['planta']->nombre }} <i class="fa fa-fw fa-caret-down"></i>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($ramos_pta) }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d" colspan="2">
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ number_format($tallos_pta) }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d" colspan="3">
                    </th>
                </tr>
                @foreach ($item['variedades'] as $pos_v => $var)
                    <tr onmouseover="$(this).css('background-color', 'cyan')"
                        onmouseleave="$(this).css('background-color', '')"
                        class="tr_planta_{{ $item['planta']->id_planta }} hidden">
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->fecha }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->nombre }}
                        </th>
                        <th style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center"
                                id="ramos_ingresados_{{ $var->id_inventario_recepcion }}" value="{{ $var->ramos }}"
                                readonly>
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->tallos_x_ramo }}
                        </th>
                        <th class="padding_lateral_5" style="border-color: #9d9d9d">
                            {{ $var->longitud }}
                        </th>
                        <th style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center"
                                id="tallos_disponibles_{{ $var->id_inventario_recepcion }}"
                                value="{{ $var->disponibles }}" readonly>
                        </th>
                        <th style="border-color: #9d9d9d">
                            <input type="number" style="width: 100%" class="text-center bg-yura_warning"
                                id="tallos_botar_{{ $var->id_inventario_recepcion }}" value="0">
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <select class="bg-yura_warning" style="width: 100%; height: 26px;"
                                id="motivo_botar_{{ $var->id_inventario_recepcion }}">
                                @foreach ($motivos as $motivo)
                                    <option value="{{ $motivo->id_motivo_baja }}">
                                        {{ $motivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </th>
                        <th class="text-center" style="border-color: #9d9d9d">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-yura_danger" title="Botar flor"
                                    onclick="botar_inventario('{{ $var->id_inventario_recepcion }}')">
                                    <i class="fa fa-fw fa-trash"></i>
                                </button>
                            </div>
                        </th>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function botar_inventario(id) {
        texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>BOTAR</b> la flor del inventario?</h3></div>";

        modal_quest('modal_botar_inventario', texto, 'Botar inventario', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
                botar: $('#tallos_botar_' + id).val(),
                motivo: $('#motivo_botar_' + id).val(),
                fecha: $('#fecha_filtro').val(),
            }
            if (datos['botar'] > 0 && datos['motivo'] != '')
                post_jquery_m('{{ url('botar_inventario/botar_inventario') }}', datos, function() {
                    cerrar_modals();
                    listar_reporte();
                });
        })
    }
</script>
