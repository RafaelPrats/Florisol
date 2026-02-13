<div style="overflow-x: scroll; overflow-y: scroll; max-height: 450px; margin-top: 10px">
    <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_operaciones">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green padding_lateral_5 columna_fija_left_0"
                    style="z-index: 11 !important;">
                    Semanas
                </th>
                <th
                    class="text-center th_yura_green r-area padding_lateral_5 {{ in_array('r-area', $columnas) ? '' : 'hidden' }}">
                    Área m<sup>2</sup>
                </th>
                <th
                    class="text-center bg-yura_dark r-area_promedio padding_lateral_5 {{ in_array('r-area_promedio', $columnas) ? '' : 'hidden' }}">
                    Área prom.
                </th>
                @if ($planta != '')
                    <th class="text-center th_yura_green">
                        Área m<sup>2</sup> Finca
                    </th>
                    <th class="text-center bg-yura_dark">
                        Área prom. Finca
                    </th>
                @endif
                <th
                    class="text-center th_yura_green padding_lateral_5 r-tallos_cosechados col-tallos_cosechados {{ in_array('r-tallos_cosechados', $columnas) ? '' : 'hidden' }}">
                    Tallos Cosechados
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_cosechados_acum col-tallos_cosechados {{ in_array('r-tallos_cosechados_acum', $columnas) ? '' : 'hidden' }}">
                    Tallos Cos. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_m2 col-tallos_cosechados {{ in_array('r-tallos_m2', $columnas) ? '' : 'hidden' }}">
                    Tallos m<sup>2</sup>
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_m2_52_sem col-tallos_cosechados {{ in_array('r-tallos_m2_52_sem', $columnas) ? '' : 'hidden' }}">
                    Tallos m<sup>2</sup> 52 Sem.
                </th>
                <th
                    class="text-center th_yura_green padding_lateral_5 r-tallos_producidos col-tallos_producidos {{ in_array('r-tallos_producidos', $columnas) ? '' : 'hidden' }}">
                    Tallos Producidos
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_producidos_acum col-tallos_producidos {{ in_array('r-tallos_producidos_acum', $columnas) ? '' : 'hidden' }}">
                    Tallos Prod. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_exportables col-tallos_producidos {{ in_array('r-tallos_exportables', $columnas) ? '' : 'hidden' }}">
                    Expotables
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_exportables_acum col-tallos_producidos {{ in_array('r-tallos_exportables_acum', $columnas) ? '' : 'hidden' }}">
                    Exp. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_10 r-porcent_exportables col-tallos_producidos {{ in_array('r-porcent_exportables', $columnas) ? '' : 'hidden' }}">
                    %Exp.
                </th>
                <th
                    class="text-center bg-yura_dark r-tallos_bqt padding_lateral_5 col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}">
                    Bqt
                </th>
                @if ($planta == '')
                    <th
                        class="text-center bg-yura_dark r-tallos_bqt_4_sem padding_lateral_5 col-tallos_producidos {{ in_array('r-tallos_bqt_4_sem', $columnas) ? '' : 'hidden' }}">
                        Bqt (-4 sem)
                    </th>
                @endif
                <th
                    class="text-center bg-yura_dark r-tallos_bqt_acum padding_lateral_5 col-tallos_producidos {{ in_array('r-tallos_bqt_acum', $columnas) ? '' : 'hidden' }}">
                    Bqt Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_bqt padding_lateral_10 col-tallos_producidos {{ in_array('r-porcent_bqt', $columnas) ? '' : 'hidden' }}">
                    %Bqt
                </th>
                <th class="text-center th_yura_green padding_lateral_5">
                    Bqt Total
                </th>
                <th class="text-center bg-yura_dark padding_lateral_5">
                    Bqt Total Acum
                </th>
                @if ($finca == 2)
                    <th
                        class="text-center bg-yura_dark r-tallos_bqt_otras_fincas padding_lateral_5 col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}">
                        Bqt Otras Fincas
                    </th>
                    <th
                        class="text-center bg-yura_dark r-tallos_bqt_otras_fincas_acum padding_lateral_5 col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}">
                        Bqt Otras Fincas Acum.
                    </th>
                @endif
                <th
                    class="text-center th_yura_green padding_lateral_5 r-flor_comprada col-flor_comprada {{ in_array('r-flor_comprada', $columnas) ? '' : 'hidden' }}">
                    Flor Comprada
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-flor_comprada_acum col-flor_comprada {{ in_array('r-flor_comprada_acum', $columnas) ? '' : 'hidden' }}">
                    Flor Compr. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_exportables col-flor_comprada {{ in_array('r-flor_comprada_tallos_exportables', $columnas) ? '' : 'hidden' }}">
                    Compr. Exp.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-tallos_exportables_acum col-flor_comprada {{ in_array('r-flor_comprada_tallos_exportables_acum', $columnas) ? '' : 'hidden' }}">
                    Compr. Exp. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_10 r-porcent_exportables col-flor_comprada {{ in_array('r-flor_comprada_porcent_exportables', $columnas) ? '' : 'hidden' }}">
                    Compr. %Exp.
                </th>
                <th
                    class="text-center bg-yura_dark r-flor_comprada_tallos_bqt padding_lateral_5 col-flor_comprada {{ in_array('r-flor_comprada_tallos_bqt', $columnas) ? '' : 'hidden' }}">
                    Compr. Bqt
                </th>
                <th
                    class="text-center bg-yura_dark r-flor_comprada_tallos_bqt_acum padding_lateral_5 col-flor_comprada {{ in_array('r-flor_comprada_tallos_bqt_acum', $columnas) ? '' : 'hidden' }}">
                    Compr. Bqt Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_bqt padding_lateral_10 col-flor_comprada {{ in_array('r-flor_comprada_porcent_bqt', $columnas) ? '' : 'hidden' }}">
                    Compr. %Bqt
                </th>
                <th
                    class="text-center th_yura_green padding_lateral_5 r-venta_total col-ventas {{ in_array('r-venta_total', $columnas) ? '' : 'hidden' }}">
                    Venta Total
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_total_acum padding_lateral_5 col-ventas {{ in_array('r-venta_total_acum', $columnas) ? '' : 'hidden' }}">
                    Venta Total Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_normal padding_lateral_5 col-ventas {{ in_array('r-venta_normal', $columnas) ? '' : 'hidden' }}">
                    Venta Exp
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_normal_acum padding_lateral_5 col-ventas {{ in_array('r-venta_normal_acum', $columnas) ? '' : 'hidden' }}">
                    Venta Exp. Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_venta_normal padding_lateral_10 col-ventas {{ in_array('r-porcent_venta_normal', $columnas) ? '' : 'hidden' }}">
                    %Venta Exp
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_bqt padding_lateral_5 col-ventas {{ in_array('r-venta_bqt', $columnas) ? '' : 'hidden' }}">
                    Venta Bqt
                </th>
                @if ($planta == '')
                    <th
                        class="text-center bg-yura_dark r-venta_bqt_4_sem padding_lateral_5 col-ventas {{ in_array('r-venta_bqt_4_sem', $columnas) ? '' : 'hidden' }}">
                        Venta Bqt (-4 sem)
                    </th>
                @endif
                <th
                    class="text-center bg-yura_dark r-venta_bqt_acum padding_lateral_5 col-ventas {{ in_array('r-venta_bqt_acum', $columnas) ? '' : 'hidden' }}">
                    Venta Bqt Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_venta_bqt padding_lateral_10 col-ventas {{ in_array('r-porcent_venta_bqt', $columnas) ? '' : 'hidden' }}">
                    %Venta Bqt
                </th>
                <th
                    class="text-center bg-yura_dark r-precio_tallo_total padding_lateral_5 col-ventas {{ in_array('r-precio_tallo_total', $columnas) ? '' : 'hidden' }}">
                    Precio/Tallo Total Año
                </th>
                <th
                    class="text-center bg-yura_dark r-precio_tallo_normal padding_lateral_5 col-ventas {{ in_array('r-precio_tallo_normal', $columnas) ? '' : 'hidden' }}">
                    Precio/Tallo Venta Sem.
                </th>
                <th
                    class="text-center bg-yura_dark r-precio_tallo_bqt padding_lateral_5 col-ventas {{ in_array('r-precio_tallo_bqt', $columnas) ? '' : 'hidden' }}">
                    Precio/Tallo Bqt Sem.
                </th>
                <th
                    class="text-center bg-yura_dark r-precio_tallo_bqt_4_sem padding_lateral_5 col-ventas {{ in_array('r-precio_tallo_bqt_4_sem', $columnas) ? '' : 'hidden' }}">
                    Precio/Tallo Bqt (-4 sem)
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_m2 padding_lateral_5 col-ventas {{ in_array('r-venta_m2', $columnas) ? '' : 'hidden' }}">
                    Venta/m<sup>2</sup>
                </th>
                <th
                    class="text-center bg-yura_dark r-venta_m2_25_sem padding_lateral_5 col-ventas {{ in_array('r-venta_m2_25_sem', $columnas) ? '' : 'hidden' }}">
                    Venta/m<sup>2</sup> 52 Sem.
                </th>
                <th
                    class="text-center th_yura_green padding_lateral_5 r-costos_total col-costos {{ in_array('r-costos_total', $columnas) ? '' : 'hidden' }}">
                    Costos Total
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-costos_total_acum col-costos {{ in_array('r-costos_total_acum', $columnas) ? '' : 'hidden' }}">
                    Costos Total Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-mo padding_lateral_5 col-costos {{ in_array('r-mo', $columnas) ? '' : 'hidden' }}">
                    MO
                </th>
                <th
                    class="text-center bg-yura_dark r-mo_acum padding_lateral_5 col-costos {{ in_array('r-mo_acum', $columnas) ? '' : 'hidden' }}">
                    MO Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_mo padding_lateral_10 col-costos {{ in_array('r-porcent_mo', $columnas) ? '' : 'hidden' }}">
                    %MO
                </th>
                <th
                    class="text-center bg-yura_dark r-insumos padding_lateral_5 col-costos {{ in_array('r-insumos', $columnas) ? '' : 'hidden' }}">
                    Insumos
                </th>
                <th
                    class="text-center bg-yura_dark r-insumos_acum padding_lateral_5 col-costos {{ in_array('r-insumos_acum', $columnas) ? '' : 'hidden' }}">
                    Insumos Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_insumos padding_lateral_10 col-costos {{ in_array('r-porcent_insumos', $columnas) ? '' : 'hidden' }}">
                    %Insumos
                </th>
                <th
                    class="text-center bg-yura_dark r-fijos padding_lateral_5 col-costos col-costos {{ in_array('r-fijos', $columnas) ? '' : 'hidden' }}">
                    Fijos
                </th>
                <th
                    class="text-center bg-yura_dark r-fijos_acum padding_lateral_5 col-costos {{ in_array('r-fijos_acum', $columnas) ? '' : 'hidden' }}">
                    Fijos Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_fijos padding_lateral_10 col-costos {{ in_array('r-porcent_fijos', $columnas) ? '' : 'hidden' }}">
                    %Fijos
                </th>
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-regalias col-costos {{ in_array('r-regalias', $columnas) ? '' : 'hidden' }}">
                    Regalías
                </th>
                <th
                    class="text-center bg-yura_dark r-regalias_acum padding_lateral_5 col-costos {{ in_array('r-regalias_acum', $columnas) ? '' : 'hidden' }}">
                    Regalías Acum.
                </th>
                <th
                    class="text-center bg-yura_dark r-porcent_regalias padding_lateral_10 col-costos {{ in_array('r-porcent_regalias', $columnas) ? '' : 'hidden' }}">
                    %Regalías
                </th>
                @if ($planta == '')
                    <th
                        class="text-center bg-yura_dark padding_lateral_5 r-compra_flor col-costos {{ in_array('r-compra_flor', $columnas) ? '' : 'hidden' }}">
                        Compra de Flor
                    </th>
                    <th
                        class="text-center bg-yura_dark r-compra_flor_acum padding_lateral_5 col-costos {{ in_array('r-compra_flor_acum', $columnas) ? '' : 'hidden' }}">
                        Compra Flor Acum.
                    </th>
                    <th
                        class="text-center bg-yura_dark r-porcent_compra_flor padding_lateral_10 col-costos {{ in_array('r-porcent_compra_flor', $columnas) ? '' : 'hidden' }}">
                        %Compra Flor
                    </th>
                @endif
                <th
                    class="text-center bg-yura_dark r-costos_m2 padding_lateral_5 col-costos {{ in_array('r-costos_m2', $columnas) ? '' : 'hidden' }}">
                    Costos/m<sup>2</sup>
                </th>
                <th
                    class="text-center bg-yura_dark r-costos_m2_52_sem padding_lateral_5 col-costos {{ in_array('r-costos_m2_52_sem', $columnas) ? '' : 'hidden' }}">
                    Costos/m<sup>2</sup> 52 Sem.
                </th>
                @if ($planta == '')
                    <th
                        class="text-center th_yura_green padding_lateral_5 r-ebitda col-ebitda {{ in_array('r-ebitda', $columnas) ? '' : 'hidden' }}">
                        EBITDA
                    </th>
                    <th
                        class="text-center bg-yura_dark padding_lateral_5 r-ebitda_acum col-ebitda {{ in_array('r-ebitda_acum', $columnas) ? '' : 'hidden' }}">
                        EBITDA Acum.
                    </th>
                    <th
                        class="text-center bg-yura_dark padding_lateral_5 r-ebitda_m2 col-ebitda {{ in_array('r-ebitda_m2', $columnas) ? '' : 'hidden' }}">
                        EBITDA/m<sup>2</sup>
                    </th>
                @endif
                <th
                    class="text-center bg-yura_dark padding_lateral_5 r-ebitda_m2_52_sem col-ebitda {{ in_array('r-ebitda_m2_52_sem', $columnas) ? '' : 'hidden' }}">
                    EBITDA/m<sup>2</sup> 52 Sem.
                </th>
            </tr>
        </thead>
        @php
            $area_acum = 0;
            $area_acum_finca = 0;
            $venta_acum = 0;
            $ebitda_acum = 0;
            $cos_acum = 0;
            $producidos_acum = 0;
            $comprada_acum = 0;
            $exp_acum = 0;
            $bqt_acum = 0;
            $comprada_exp_acum = 0;
            $comprada_bqt_acum = 0;
            $venta_normal_acum = 0;
            $venta_bqt_acum = 0;
            $costos_acum = 0;
            $mo_acum = 0;
            $insumos_acum = 0;
            $fijos_acum = 0;
            $regalias_acum = 0;
            $compra_flor_acum = 0;
            $tallos_prod_bqt_otras_fincas_acum = 0;
            $bqt_total_acum = 0;
        @endphp
        <tbody>
            @foreach ($listado as $pos => $item)
                @php
                    $precio_tallo_bqt_4_sem = $resumen_semanal_finca[$pos]->tallos_bqt_4_sem > 0 ? number_format($resumen_semanal_finca[$pos]->ventas_bqt_4_sem / $resumen_semanal_finca[$pos]->tallos_bqt_4_sem, 2) : 0;
                    $ventas_bqt = $resumen_semanal[$pos]->venta_bouquetera;
                    $costos_operativos = $resumen_costos[$pos]->mano_obra + $resumen_costos[$pos]->insumos + $resumen_costos[$pos]->fijos + $resumen_costos[$pos]->regalias + ($item['compra_flor']->tallos + $item['compra_flor']->exportada);
                    if ($planta != '') {
                        // x PLANTA
                        $bqt_total = $item['compra_flor_finca']->tallos_bqt;
                    } else {
                        // x FINCA
                        if ($finca == 2) {
                            $bqt_total = $item['compra_flor_finca']->tallos_bqt + $item['compra_flor_otras_fincas']->tallos_bqt + $item['flor_comprada_bqt']->tallos_bqt;
                        } else {
                            // x FINCA
                            $bqt_total = $item['compra_flor_finca']->tallos_bqt + $item['flor_comprada_bqt']->tallos_bqt;
                        }
                    }
                    $venta_comprada_bqt = 0;
                    if ($planta != '') {
                        // x Planta
                        $venta_comprada_bqt = $item['flor_comprada_bqt']->tallos_bqt * $precio_tallo_bqt_4_sem;
                        $ventas_bqt = $bqt_total * $precio_tallo_bqt_4_sem;
                        $costos_operativos = $resumen_costos[$pos]->mano_obra + $resumen_costos[$pos]->insumos + $resumen_costos[$pos]->fijos + $resumen_costos[$pos]->regalias;
                    }
                    $ventas = $resumen_semanal[$pos]->venta + $ventas_bqt;
                    $venta_acum += $ventas;
                    $area_acum += $item['area'];
                    $area_acum_finca += $item['area_finca'];
                    $prom_area = $area_acum / ($pos + 1);
                    $prom_area_finca = $area_acum_finca / ($pos + 1);
                    $costos_acum += $costos_operativos;
                    if ($planta == '') {
                        // x FINCA
                        $costos_m2 = $prom_area > 0 ? $costos_acum / $prom_area : 0;
                        $venta_m2 = $prom_area > 0 ? $venta_acum / $prom_area : 0;
                    } else {
                        // x Planta
                        $costos_m2 = $prom_area_finca > 0 ? $costos_acum / $prom_area_finca : 0;
                        $venta_m2 = $prom_area > 0 ? $venta_acum / $prom_area : 0;
                    }
                    $venta_m2_52_sem = ($venta_m2 / ($pos + 1)) * 52;
                    $costos_m2_52_sem = ($costos_m2 / ($pos + 1)) * 52;
                    $ebitda_m2_52_sem = $planta != '' ? $venta_m2_52_sem - $costos_m2_52_sem : 0;
                    //dd($ebitda_m2_52_sem, $prom_area, $ventas, $costos_operativos);
                    $ebitda = $planta != '' ? $ebitda_m2_52_sem * $prom_area : $ventas - $costos_operativos;
                    $ebitda_acum += $ebitda;
                    $ebitda_m2 = $prom_area > 0 ? $ebitda_acum / $prom_area : 0;
                    if ($planta == '') {
                        // x FINCA
                        $ebitda_m2_52_sem = ($ebitda_m2 / ($pos + 1)) * 52;
                    }
                    $cos_acum += $resumen_semanal[$pos]->tallos_cosechados;
                    $producidos_acum += $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor_finca']->tallos_bqt;
                    $exp_acum += $resumen_semanal[$pos]->tallos_exportables;
                    $bqt_acum += $item['compra_flor_finca']->tallos_bqt;
                    $comprada_acum += $item['flor_comprada_exp']->tallos_exportada + $item['flor_comprada_bqt']->tallos_bqt;
                    $comprada_exp_acum += $item['flor_comprada_exp']->tallos_exportada;
                    $comprada_bqt_acum += $item['flor_comprada_bqt']->tallos_bqt;
                    $tallos_prod_bqt_otras_fincas_acum += $item['compra_flor_otras_fincas']->tallos_bqt;
                    $tallos_acum = $producidos_acum + $comprada_acum;
                    $tallos_exp_acum = $exp_acum + $comprada_exp_acum;
                    $tallos_bqt_acum = $bqt_acum + $comprada_bqt_acum;
                    $venta_normal_acum += $resumen_semanal[$pos]->venta;
                    $venta_bqt_acum += $ventas_bqt;
                    $mo_acum += $resumen_costos[$pos]->mano_obra;
                    $insumos_acum += $resumen_costos[$pos]->insumos;
                    $fijos_acum += $resumen_costos[$pos]->fijos;
                    $regalias_acum += $resumen_costos[$pos]->regalias;
                    $compra_flor_acum += $item['compra_flor']->tallos + $item['compra_flor']->exportada;
                    if ($finca == 2) {
                        $precio_total_anno = $tallos_acum + $tallos_prod_bqt_otras_fincas_acum > 0 ? number_format($venta_acum / ($tallos_acum + $tallos_prod_bqt_otras_fincas_acum), 2) : 0;
                    } else {
                        $precio_total_anno = $comprada_acum + $exp_acum > 0 ? number_format($venta_acum / ($comprada_acum + $exp_acum), 2) : 0;
                    }
                    $precio_tallo_bqt = $bqt_total > 0 ? number_format($ventas_bqt / $bqt_total, 2) : 0;
                    $bqt_total_acum += $bqt_total;
                @endphp
                <tr style="background-color: {{ $pos % 2 == 0 ? '#e9ecef' : '' }}">
                    <td class="text-center columna_fija_left_0"
                        style="border-color: #9d9d9d; background-color: {{ $pos % 2 == 0 ? '#e9ecef' : 'white' }} !important;">
                        {{ $item['semana']->codigo }}
                    </td>
                    <td class="text-center padding_lateral_5 r-area {{ in_array('r-area', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($item['area'], 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-area_promedio {{ in_array('r-area_promedio', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($prom_area, 2) }}
                    </td>
                    @if ($planta != '')
                        <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ number_format($item['area_finca'], 2) }}
                        </td>
                        <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                            {{ number_format($prom_area_finca, 2) }}
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-tallos_cosechados col-tallos_cosechados {{ in_array('r-tallos_cosechados', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($resumen_semanal[$pos]->tallos_cosechados) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_cosechados_acum col-tallos_cosechados {{ in_array('r-tallos_cosechados_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($cos_acum) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_m2 col-tallos_cosechados {{ in_array('r-tallos_m2', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        @php
                            $tallos_m2 = $prom_area > 0 ? $cos_acum / $prom_area : 0;
                        @endphp
                        {{ number_format($tallos_m2, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_m2_52_sem col-tallos_cosechados {{ in_array('r-tallos_m2_52_sem', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        @php
                            $tallos_m2_52_sem = ($tallos_m2 / ($pos + 1)) * 52;
                        @endphp
                        {{ number_format($tallos_m2_52_sem, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_producidos col-tallos_producidos {{ in_array('r-tallos_producidos', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($resumen_semanal[$pos]->tallos_exportables + $item['compra_flor_finca']->tallos_bqt) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_producidos_acum col-tallos_producidos {{ in_array('r-tallos_producidos_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($producidos_acum) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_exportables col-tallos_producidos {{ in_array('r-tallos_exportables', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($resumen_semanal[$pos]->tallos_exportables) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_exportables_acum col-tallos_producidos {{ in_array('r-tallos_exportables_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($exp_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_exportables col-tallos_producidos {{ in_array('r-porcent_exportables', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_semanal[$pos]->tallos_exportables, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor_finca']->tallos_bqt, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-tallos_bqt col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($item['compra_flor_finca']->tallos_bqt) }}
                    </td>
                    @if ($planta == '')
                        <td class="text-center padding_lateral_5 r-tallos_bqt_4_sem col-tallos_producidos {{ in_array('r-tallos_bqt_4_sem', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            {{ number_format($resumen_semanal[$pos]->tallos_bqt_4_sem) }}
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-tallos_bqt_acum col-tallos_producidos {{ in_array('r-tallos_bqt_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($bqt_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_bqt col-tallos_producidos {{ in_array('r-porcent_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($item['compra_flor_finca']->tallos_bqt, $resumen_semanal[$pos]->tallos_exportables + $item['compra_flor_finca']->tallos_bqt, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($bqt_total) }}
                    </td>
                    <td class="text-center padding_lateral_5" style="border-color: #9d9d9d">
                        {{ number_format($bqt_total_acum) }}
                    </td>
                    @if ($finca == 2)
                        <td class="text-center padding_lateral_5 r-tallos_bqt_otras_fincas col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            {{ number_format($item['compra_flor_otras_fincas']->tallos_bqt) }}
                        </td>
                        <td class="text-center padding_lateral_5 r-tallos_bqt_otras_fincas_acum col-tallos_producidos {{ in_array('r-tallos_bqt', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            {{ number_format($tallos_prod_bqt_otras_fincas_acum) }}
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-flor_comprada col-flor_comprada {{ in_array('r-flor_comprada', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($item['flor_comprada_exp']->tallos_exportada + $item['flor_comprada_bqt']->tallos_bqt) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-flor_comprada_acum col-flor_comprada {{ in_array('r-flor_comprada_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($comprada_acum) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-flor_comprada_tallos_exportables col-flor_comprada {{ in_array('r-flor_comprada_tallos_exportables', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($item['flor_comprada_exp']->tallos_exportada) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-flor_comprada_tallos_exportables_acum col-flor_comprada {{ in_array('r-flor_comprada_tallos_exportables_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($comprada_exp_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-flor_comprada_porcent_exportables col-flor_comprada {{ in_array('r-flor_comprada_porcent_exportables', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($item['flor_comprada_exp']->tallos_exportada, $item['flor_comprada_exp']->tallos_exportada + $item['flor_comprada_bqt']->tallos_bqt, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-flor_comprada_tallos_bqt col-flor_comprada {{ in_array('r-flor_comprada_tallos_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($item['flor_comprada_bqt']->tallos_bqt) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-flor_comprada_tallos_bqt_acum col-flor_comprada {{ in_array('r-flor_comprada_tallos_bqt_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($comprada_bqt_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-flor_comprada_porcent_bqt col-flor_comprada {{ in_array('r-flor_comprada_porcent_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($item['flor_comprada_bqt']->tallos_bqt, $item['flor_comprada_exp']->tallos_exportada + $item['flor_comprada_bqt']->tallos_bqt, 1) }}%
                    </td>
                    <th class="text-center padding_lateral_5 r-venta_total col-ventas {{ in_array('r-venta_total', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d; color: #00b388">
                        ${{ number_format($ventas, 2) }}
                    </th>
                    <td class="text-center padding_lateral_5 r-venta_total_acum col-ventas {{ in_array('r-venta_total_acum', $columnas) ? '' : 'hidden' }} r-venta_total_acum"
                        style="border-color: #9d9d9d">
                        ${{ number_format($venta_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-venta_normal col-ventas {{ in_array('r-venta_normal', $columnas) ? '' : 'hidden' }} r-venta_normal"
                        style="border-color: #9d9d9d">
                        ${{ number_format($resumen_semanal[$pos]->venta, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-venta_normal_acum col-ventas {{ in_array('r-venta_normal_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ number_format($venta_normal_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_venta_normal col-ventas {{ in_array('r-porcent_venta_normal', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_semanal[$pos]->venta, $ventas, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-venta_bqt col-ventas {{ in_array('r-venta_bqt', $columnas) ? '' : 'hidden' }} r-venta_bqt"
                        style="border-color: #9d9d9d">
                        ${{ number_format($ventas_bqt, 2) }}
                    </td>
                    @if ($planta == '')
                        <td class="text-center padding_lateral_5 r-venta_bqt_4_sem col-ventas {{ in_array('r-venta_bqt_4_sem', $columnas) ? '' : 'hidden' }} r-venta_bqt_4_sem"
                            style="border-color: #9d9d9d">
                            ${{ number_format($resumen_semanal[$pos]->ventas_bqt_4_sem, 2) }}
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-venta_bqt_acum col-ventas {{ in_array('r-venta_bqt_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($venta_bqt_acum) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_venta_bqt col-ventas {{ in_array('r-porcent_venta_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($ventas_bqt, $ventas, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-precio_tallo_total col-ventas {{ in_array('r-precio_tallo_total', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ $precio_total_anno }}
                    </td>
                    <td class="text-center padding_lateral_5 r-precio_tallo_normal col-ventas {{ in_array('r-precio_tallo_normal', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ $resumen_semanal[$pos]->tallos_exportables + $item['flor_comprada_exp']->tallos_exportada > 0 ? number_format($resumen_semanal[$pos]->venta / ($resumen_semanal[$pos]->tallos_exportables + $item['flor_comprada_exp']->tallos_exportada), 2) : 0 }}
                    </td>
                    <td class="text-center padding_lateral_5 r-precio_tallo_bqt col-ventas {{ in_array('r-precio_tallo_bqt', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ $precio_tallo_bqt }}
                    </td>
                    <td class="text-center padding_lateral_5 r-precio_tallo_bqt_4_sem col-ventas {{ in_array('r-precio_tallo_bqt_4_sem', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ $precio_tallo_bqt_4_sem }}
                    </td>
                    <td class="text-center padding_lateral_5 r-venta_m2 col-ventas {{ in_array('r-venta_m2', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($venta_m2, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-venta_m2_25_sem col-ventas {{ in_array('r-venta_m2_25_sem', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($venta_m2_52_sem, 2) }}
                    </td>
                    <th class="text-center padding_lateral_5 r-costos_total col-costos {{ in_array('r-costos_total', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d; color: #d01c62">
                        ${{ number_format($costos_operativos, 2) }}
                    </th>
                    <td class="text-center padding_lateral_5 r-costos_total_acum col-costos {{ in_array('r-costos_total_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d;">
                        ${{ number_format($costos_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-mo col-costos {{ in_array('r-mo', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($resumen_costos[$pos]->mano_obra, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-mo_acum col-costos {{ in_array('r-mo_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($mo_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_mo col-costos {{ in_array('r-porcent_mo', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_costos[$pos]->mano_obra, $costos_operativos, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-insumos col-costos {{ in_array('r-insumos', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($resumen_costos[$pos]->insumos, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-insumos_acum col-costos {{ in_array('r-insumos_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($insumos_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_insumos col-costos {{ in_array('r-porcent_insumos', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_costos[$pos]->insumos, $costos_operativos, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-fijos col-costos {{ in_array('r-fijos', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($resumen_costos[$pos]->fijos, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-fijos_acum col-costos {{ in_array('r-fijos_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($fijos_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_fijos col-costos {{ in_array('r-porcent_fijos', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_costos[$pos]->fijos, $costos_operativos, 1) }}%
                    </td>
                    <td class="text-center padding_lateral_5 r-regalias col-costos {{ in_array('r-regalias', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($resumen_costos[$pos]->regalias, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-regalias_acum col-costos {{ in_array('r-regalias_acum', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($regalias_acum, 2) }}
                    </td>
                    <td class="text-center padding_lateral_10 r-porcent_regalias col-costos {{ in_array('r-porcent_regalias', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        {{ porcentaje($resumen_costos[$pos]->regalias, $costos_operativos, 1) }}%
                    </td>
                    @if ($planta == '')
                        <td class="text-center padding_lateral_5 r-compra_flor col-costos {{ in_array('r-compra_flor', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            ${{ number_format($item['compra_flor']->tallos + $item['compra_flor']->exportada, 2) }}
                        </td>
                        <td class="text-center padding_lateral_5 r-compra_flor_acum col-costos {{ in_array('r-compra_flor_acum', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            ${{ number_format($compra_flor_acum, 2) }}
                        </td>
                        <td class="text-center padding_lateral_10 r-porcent_compra_flor col-costos {{ in_array('r-porcent_compra_flor', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            {{ porcentaje($item['compra_flor']->tallos + $item['compra_flor']->exportada, $costos_operativos, 1) }}%
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-costos_m2 col-costos {{ in_array('r-costos_m2', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($costos_m2, 2) }}
                    </td>
                    <td class="text-center padding_lateral_5 r-costos_m2_52_sem col-costos {{ in_array('r-costos_m2_52_sem', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($costos_m2_52_sem, 2) }}
                    </td>
                    @if ($planta == '')
                        <th class="text-center padding_lateral_5 r-ebitda col-ebitda {{ in_array('r-ebitda', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d; color: {{ $ebitda < 0 ? '#d01c62' : '#00b388' }}">
                            ${{ number_format($ebitda, 2) }}
                        </th>
                        <td class="text-center padding_lateral_5 r-ebitda_acum col-ebitda {{ in_array('r-ebitda_acum', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            ${{ number_format($ebitda_acum, 2) }}
                        </td>
                        <td class="text-center padding_lateral_5 r-ebitda_m2 col-ebitda {{ in_array('r-ebitda_m2', $columnas) ? '' : 'hidden' }}"
                            style="border-color: #9d9d9d">
                            ${{ number_format($ebitda_m2, 2) }}
                        </td>
                    @endif
                    <td class="text-center padding_lateral_5 r-ebitda_m2_52_sem col-ebitda {{ in_array('r-ebitda_m2_52_sem', $columnas) ? '' : 'hidden' }}"
                        style="border-color: #9d9d9d">
                        ${{ number_format($ebitda_m2_52_sem, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }

    .columna_fija_left_0 {
        position: sticky;
        left: 0;
        z-index: 9 !important;
    }
</style>
