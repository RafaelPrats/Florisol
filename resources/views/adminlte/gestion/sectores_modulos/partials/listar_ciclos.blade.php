@if (count($listado) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; height: 400px;">
        <table class="table-striped table-bordered" width="100%"
            style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_listado_ciclos">
            <thead>
                <tr id="tr_fija_top_0">
                    <th class="text-center th_yura_green" rowspan="2">
                        <div style="width: 100px">Módulo</div>
                    </th>
                    <th class="text-center th_yura_green" colspan="11">
                        Proyecciones
                    </th>
                    <th class="text-center bg-yura_dark" colspan="5">
                        Campo
                    </th>
                    <th class="text-center th_yura_green" rowspan="2">
                        Opciones
                    </th>
                </tr>
                <tr id="tr_fija_top_1">
                    <th class="text-center th_yura_green">
                        Inicio
                    </th>
                    <th class="text-center th_yura_green">
                        Poda/Siembra
                    </th>
                    <th class="text-center th_yura_green">
                        Dias
                    </th>
                    <th class="text-center th_yura_green">
                        1ra Flor
                    </th>
                    <th class="text-center th_yura_green">
                        Cosecha
                    </th>
                    <th class="text-center th_yura_green">
                        Final
                    </th>
                    <th class="text-center th_yura_green">
                        Área m<sup>2</sup>
                    </th>
                    <th class="text-center th_yura_green">
                        Ptas Iniciales
                    </th>
                    <th class="text-center th_yura_green">
                        Ptas muertas
                    </th>
                    <th class="text-center th_yura_green">
                        Ptas actuales
                    </th>
                    <th class="text-center th_yura_green">
                        Conteo T/P
                    </th>
                    <th class="text-center bg-yura_dark">
                        Ancho Cama
                    </th>
                    <th class="text-center bg-yura_dark">
                        Ancho Camino
                    </th>
                    <th class="text-center bg-yura_dark">
                        Metros Lineales
                    </th>
                    <th class="text-center bg-yura_dark">
                        Camas
                    </th>
                    <th class="text-center bg-yura_dark">
                        Camas m<sup>2</sup>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_area = 0;
                    $total_iniciales = 0;
                    $total_muertas = 0;
                    $total_actuales = 0;
                @endphp
                @foreach ($listado as $pos => $item)
                    @php
                        $suma = $item->ancho_cama + $item->ancho_camino;
                        $metros_lineales = $suma > 0 ? round($item->area / ($item->ancho_cama + $item->ancho_camino), 2) : 0;
                        $camas = $metros_lineales > 0 ? round($item->area / (($item->ancho_cama + $item->ancho_camino) * 30), 2) : 0;
                        $camas_m2 = ($item->ancho_cama + $item->ancho_camino) * 30;
                    @endphp
                    <tr style="background-color: {{ $pos % 2 == 0 ? '#e5e5e5' : '' }}">
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ $item->modulo_nombre }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="date" id="ciclo_fecha_inicio_{{ $item->id_ciclo }}" style="width: 100%"
                                value="{{ $item->fecha_inicio }}">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <select id="ciclo_poda_siembra_{{ $item->id_ciclo }}" style="width: 100%">
                                <option value="P" {{ $item->poda_siembra == 'P' ? 'selected' : '' }}>Poda</option>
                                <option value="S" {{ $item->poda_siembra == 'S' ? 'selected' : '' }}>Siembra
                                </option>
                            </select>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ difFechas($item->fecha_fin != '' ? $item->fecha_fin : hoy(), $item->fecha_inicio)->days }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ $item->fecha_cosecha != '' ? difFechas($item->fecha_cosecha, $item->fecha_inicio)->days : 0 }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="text" id="ciclo_fecha_cosecha_{{ $item->id_ciclo }}" style="width: 100%"
                                onkeypress="return isNumber(event)" maxlength="3" class="text-center" required
                                value="{{ $item->fecha_cosecha != '' ? difFechas($item->fecha_cosecha, $item->fecha_inicio)->days : '' }}">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="date" id="ciclo_fecha_fin_{{ $item->id_ciclo }}" style="width: 100%"
                                value="{{ $item->fecha_fin }}" class="text-center" required>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            @php
                                $total_area += $item->area;
                            @endphp
                            <input type="number" id="ciclo_area_{{ $item->id_ciclo }}" class="text-center"
                                value="{{ $item->area }}" style="width: 100%" required>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            @php
                                $total_iniciales += $item->plantas_iniciales >= 0 ? $item->plantas_iniciales : 0;
                            @endphp
                            <input type="number" id="ciclo_plantas_iniciales_{{ $item->id_ciclo }}"
                                style="width: 100%" onkeypress="return isNumber(event)"
                                value="{{ $item->plantas_iniciales > 0 ? $item->plantas_iniciales : 0 }}"
                                class="text-center" required>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            @php
                                $total_muertas += $item->plantas_muertas >= 0 ? $item->plantas_muertas : 0;
                            @endphp
                            <input type="number" id="ciclo_plantas_muertas_{{ $item->id_ciclo }}" style="width: 100%"
                                onkeypress="return isNumber(event)" value="{{ $item->plantas_muertas }}"
                                class="text-center" required>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            @php
                                $plantas_actuales = $item->plantas_iniciales;
                                if ($item->plantas_muertas > 0) {
                                    $plantas_actuales = $item->plantas_iniciales - $item->plantas_muertas;
                                }
                                
                                $total_actuales += $plantas_actuales;
                            @endphp
                            {{ number_format($plantas_actuales) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" id="ciclo_conteo_{{ $item->id_ciclo }}" style="width: 100%"
                                value="{{ $item->conteo }}" class="text-center" required>
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" id="ciclo_ancho_cama_{{ $item->id_ciclo }}" style="width: 100%"
                                value="{{ $item->ancho_cama }}" class="text-center" required min="0">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" id="ciclo_ancho_camino_{{ $item->id_ciclo }}" style="width: 100%"
                                value="{{ $item->ancho_camino }}" class="text-center" required min="0">
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($metros_lineales, 2) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($camas, 2) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            {{ number_format($camas_m2, 2) }}
                        </td>
                        <td class="text-center" style="border-color: #9d9d9d">
                            <div class="btn-group">
                                <button type="button" class="btn btn-yura_default btn-xs dropdown-toggle"
                                    data-toggle="dropdown">
                                    <i class="fa fa-fw fa-gears"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                        <a href="javascript:void(0)" onclick="update_ciclo('{{ $item->id_ciclo }}')">
                                            <i class="fa fa-fw fa-pencil"></i> Modificar Ciclo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" onclick="ver_ciclos('{{ $item->id_modulo }}')">
                                            <i class="fa fa-fw fa-eye"></i> Ver Ciclos del Módulo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)"
                                            onclick="terminar_ciclo('{{ $item->id_ciclo }}', false, '{{ $item->id_modulo }}')">
                                            <i class="fa fa-fw fa-times"></i> Terminar Ciclo
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        No hay resultados que mostrar
    </div>
@endif

<style>
    #tr_fija_top_0 {
        position: sticky;
        top: 0;
        z-index: 9
    }

    #tr_fija_top_1 {
        position: sticky;
        top: 22px;
        z-index: 9
    }
</style>
