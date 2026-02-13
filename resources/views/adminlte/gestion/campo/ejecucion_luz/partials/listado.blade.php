<div class="box box-success">
    {{-- ENTRADAS --}}
    <div class="box-header with-border text-center">
        <div class="alert alert-info text-center mouse-hand" onclick="$('#table_entrantes').toggleClass('hidden')">
            Ciclos <strong>ENTRANTES</strong>:
            <span class="badge">{{ count($entradas_si) }}</span> <strong>EJECUTADOS</strong> de
            <span class="badge">{{ count($entradas_si) + count($entradas_no) }}</span> <strong>TOTALES</strong>
        </div>
    </div>
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-bordered table-striped hidden" style="width: 100%; border: 1px solid #9d9d9d"
            id="table_entrantes">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Tipo
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Módulo
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Poda
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Fecha Poda
                        </div>
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Días
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Watts
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 80px">
                            Tipo Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            # Lamp.
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Día Ini. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Ini. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Días Proy.
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Días Adic. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Fin Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Sem. Fin
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Hrs. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green" colspan="2">
                        Horario
                    </th>
                    <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                        Costo
                    </th>
                    <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                        Costo/m2
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entradas_si as $luz)
                    @php
                        $ciclo = $luz->ciclo;
                        $modulo = $ciclo->modulo;
                        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
                        $dias_luz = 0;
                        if (isset($luz) && $luz->inicio_luz <= $dias_ciclo) {
                            if ($luz->dias_proy + $luz->dias_adicional >= $dias_ciclo - $luz->inicio_luz) {
                                $dias_luz = $dias_ciclo - $luz->inicio_luz;
                            } else {
                                $dias_luz = $luz->dias_proy + $luz->dias_adicional;
                            }
                        }
                        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                        $horas_luz = $dias_luz * $horas_dia;
                        //calcular costo de luz
                        $costo_luz = 0;
                        if (isset($luz)) {
                            $costo_x_tipo = $luz->tipo_luz / 1000;
                            $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                            $costo_x_lampara = $costo_x_lampara * $horas_luz;
                            $costo_luz = $costo_x_lampara * 0.1;
                        }
                        $variedad = $ciclo->variedad;
                    @endphp
                    <tr id="tr_luz_{{ $luz->id_ciclo_luz }}">
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->planta->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $modulo->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $ciclo->poda_siembra }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ convertDateToText($ciclo->fecha_inicio) }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $dias_ciclo }}
                        </th>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_lampara == 'S' ? 'Sodio' : 'Led Roja' }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->lamparas }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->inicio_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($inicio_luz) }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_proy }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_adicional }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($fin_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_sem_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ getSemanaByDate($fin_luz)->codigo }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_horas_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $horas_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            <input type="time" class="text-center" style="width: 100%"
                                value="{{ $luz->hora_ini }}">
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->hora_fin }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_luz_{{ $luz->id_ciclo_luz }}">
                            ${{ $costo_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_m2_{{ $luz->id_ciclo_luz }}">
                            ₵{{ round($costo_luz / $ciclo->area, 4) * 100 }}
                        </td>
                    </tr>
                @endforeach
                @foreach ($entradas_no as $luz)
                    @php
                        $ciclo = $luz->ciclo;
                        $modulo = $ciclo->modulo;
                        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
                        $dias_luz = 0;
                        if (isset($luz) && $luz->inicio_luz <= $dias_ciclo) {
                            if ($luz->dias_proy + $luz->dias_adicional >= $dias_ciclo - $luz->inicio_luz) {
                                $dias_luz = $dias_ciclo - $luz->inicio_luz;
                            } else {
                                $dias_luz = $luz->dias_proy + $luz->dias_adicional;
                            }
                        }
                        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                        $horas_luz = $dias_luz * $horas_dia;
                        //calcular costo de luz
                        $costo_luz = 0;
                        if (isset($luz)) {
                            $costo_x_tipo = $luz->tipo_luz / 1000;
                            $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                            $costo_x_lampara = $costo_x_lampara * $horas_luz;
                            $costo_luz = $costo_x_lampara * 0.1;
                        }
                        $variedad = $ciclo->variedad;
                    @endphp
                    <tr id="tr_luz_{{ $luz->id_ciclo_luz }}">
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->planta->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $modulo->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $ciclo->poda_siembra }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ convertDateToText($ciclo->fecha_inicio) }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $dias_ciclo }}
                        </th>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_lampara == 'S' ? 'Sodio' : 'Led Roja' }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->lamparas }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->inicio_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($inicio_luz) }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_proy }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_adicional }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($fin_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_sem_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ getSemanaByDate($fin_luz)->codigo }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_horas_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $horas_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            <input type="time" class="text-center" style="width: 100%"
                                value="{{ $luz->hora_ini }}">
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->hora_fin }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_luz_{{ $luz->id_ciclo_luz }}">
                            ${{ $costo_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_ini_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_m2_{{ $luz->id_ciclo_luz }}">
                            ₵{{ round($costo_luz / $ciclo->area, 4) * 100 }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- SALIDAS --}}
    <div class="box-header with-border text-center">
        <div class="alert alert-info text-center mouse-hand" onclick="$('#table_salientes').toggleClass('hidden')">
            Ciclos <strong>SALIENTES</strong>:
            <span class="badge">{{ count($salidas_si) }}</span> <strong>EJECUTADOS</strong> de
            <span class="badge">{{ count($salidas_si) + count($salidas_no) }}</span> <strong>TOTALES</strong>
        </div>
    </div>
    <div class="box-body" style="overflow-x: scroll">
        <table class="table-bordered table-striped hidden" style="width: 100%; border: 1px solid #9d9d9d"
            id="table_salientes">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Variedad
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Tipo
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Módulo
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Poda
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Fecha Poda
                        </div>
                    </th>
                    <th class="text-center th_yura_green" style="padding-right: 5px; padding-left: 5px">
                        Días
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Watts
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 80px">
                            Tipo Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            # Lamp.
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Día Ini. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Ini. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Sem. Ini.
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Días Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Días Proy.
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Días Adic. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 120px">
                            Fin Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green">
                        <div class="text-center" style="width: 60px">
                            Hrs. Luz
                        </div>
                    </th>
                    <th class="text-center th_yura_green" colspan="2">
                        Horario
                    </th>
                    <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                        Costo
                    </th>
                    <th class="text-center th_yura_green" style="padding-left: 5px; padding-right: 5px">
                        Costo/m2
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salidas_si as $luz)
                    @php
                        $ciclo = $luz->ciclo;
                        $modulo = $ciclo->modulo;
                        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
                        $dias_luz = 0;
                        if (isset($luz) && $luz->inicio_luz <= $dias_ciclo) {
                            if ($luz->dias_proy + $luz->dias_adicional >= $dias_ciclo - $luz->inicio_luz) {
                                $dias_luz = $dias_ciclo - $luz->inicio_luz;
                            } else {
                                $dias_luz = $luz->dias_proy + $luz->dias_adicional;
                            }
                        }
                        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                        $horas_luz = $dias_luz * $horas_dia;
                        //calcular costo de luz
                        $costo_luz = 0;
                        if (isset($luz)) {
                            $costo_x_tipo = $luz->tipo_luz / 1000;
                            $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                            $costo_x_lampara = $costo_x_lampara * $horas_luz;
                            $costo_luz = $costo_x_lampara * 0.1;
                        }
                        $variedad = $ciclo->variedad;
                    @endphp
                    <tr id="tr_luz_{{ $luz->id_ciclo_luz }}">
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->planta->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $modulo->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $ciclo->poda_siembra }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ convertDateToText($ciclo->fecha_inicio) }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $dias_ciclo }}
                        </th>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_lampara == 'S' ? 'Sodio' : 'Led Roja' }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->lamparas }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->inicio_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($inicio_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_sem_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ getSemanaByDate($inicio_luz)->codigo }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_dias_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $dias_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_proy }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_adicional }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($fin_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_horas_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $horas_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            <input type="time" class="text-center" style="width: 100%"
                                value="{{ $luz->hora_ini }}">
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->hora_fin }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_luz_{{ $luz->id_ciclo_luz }}">
                            ${{ $costo_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_m2_{{ $luz->id_ciclo_luz }}">
                            ₵{{ round($costo_luz / $ciclo->area, 4) * 100 }}
                        </td>
                    </tr>
                @endforeach
                @foreach ($salidas_no as $luz)
                    @php
                        $ciclo = $luz->ciclo;
                        $modulo = $ciclo->modulo;
                        $dias_ciclo = difFechas(hoy(), $ciclo->fecha_inicio)->days;
                        $inicio_luz = opDiasFecha('+', $luz->inicio_luz, $ciclo->fecha_inicio);
                        $fin_luz = opDiasFecha('+', $luz->inicio_luz + $luz->dias_proy + $luz->dias_adicional - 1, $ciclo->fecha_inicio);
                        $dias_luz = 0;
                        if (isset($luz) && $luz->inicio_luz <= $dias_ciclo) {
                            if ($luz->dias_proy + $luz->dias_adicional >= $dias_ciclo - $luz->inicio_luz) {
                                $dias_luz = $dias_ciclo - $luz->inicio_luz;
                            } else {
                                $dias_luz = $luz->dias_proy + $luz->dias_adicional;
                            }
                        }
                        $horas_dia = isset($luz) ? $luz->getHorasDia() : 0;
                        $horas_luz = $dias_luz * $horas_dia;
                        //calcular costo de luz
                        $costo_luz = 0;
                        if (isset($luz)) {
                            $costo_x_tipo = $luz->tipo_luz / 1000;
                            $costo_x_lampara = $costo_x_tipo * $luz->lamparas;
                            $costo_x_lampara = $costo_x_lampara * $horas_luz;
                            $costo_luz = $costo_x_lampara * 0.1;
                        }
                        $variedad = $ciclo->variedad;
                    @endphp
                    <tr id="tr_luz_{{ $luz->id_ciclo_luz }}">
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->planta->nombre }}
                        </th>
                        <th class="text-center padding_lateral_5 bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $variedad->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $modulo->nombre }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $ciclo->poda_siembra }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ convertDateToText($ciclo->fecha_inicio) }}
                        </th>
                        <th class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $dias_ciclo }}
                        </th>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->tipo_lampara == 'S' ? 'Sodio' : 'Led Roja' }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->lamparas }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->inicio_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($inicio_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_sem_inicio_luz_{{ $luz->id_ciclo_luz }}">
                            {{ getSemanaByDate($inicio_luz)->codigo }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_dias_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $dias_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_proy }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            {{ $luz->dias_adicional }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_fin_luz_{{ $luz->id_ciclo_luz }}">
                            {{ convertDateToText($fin_luz) }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_horas_luz_{{ $luz->id_ciclo_luz }}">
                            {{ $horas_luz }}
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            <input type="time" class="text-center" style="width: 100%"
                                value="{{ $luz->hora_ini }}" readonly>
                        </td>
                        <td class="text-center"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}">
                            <input type="time" class="text-center" style="width: 100%"
                                value="{{ $luz->hora_fin }}" readonly>
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_luz_{{ $luz->id_ciclo_luz }}">
                            ${{ $costo_luz }}
                        </td>
                        <td class="text-center bg_color_{{ $luz->id_ciclo_luz }}"
                            style="border-color: #9d9d9d; background-color: {{ $ciclo->ejec_fin_luz != '' ? '#b7fbaf' : '#e9ecef' }}"
                            id="td_costo_m2_{{ $luz->id_ciclo_luz }}">
                            ₵{{ round($costo_luz / $ciclo->area, 4) * 100 }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 8;
    }
</style>
