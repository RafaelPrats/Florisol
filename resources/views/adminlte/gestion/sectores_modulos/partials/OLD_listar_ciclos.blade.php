@if(count($listado) > 0)
    <div style="overflow-x: scroll; overflow-y: scroll; height: 400px;">
        <table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_listado_ciclos">
            <thead>
            <tr>
                <th class="text-center" rowspan="2"
                    style="border-color: white; color: white; background-color: #00b388; border-radius: 18px 0 0 0;">
                    <div style="width: 100px">Módulo</div>
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388" colspan="12">
                    Ciclos
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388; border-radius: 0 18px 0 0"
                    rowspan="2">
                    Opciones
                </th>
            </tr>
            <tr>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Inicio
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Poda/Siembra
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Dias
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    1ra Flor
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Tallos Cosechados
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Cosecha
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Final
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Área m<sup>2</sup>
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Ptas Iniciales
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Ptas muertas
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Ptas actuales
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388">
                    Conteo T/P
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
            @foreach($listado as $pos_i => $item)
                @php
                    $modulo = getModuloById($item->id_modulo);
                    $getLastCiclo = $modulo->getLastCiclo();
                @endphp
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$item->modulo_nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{$item->fecha_inicio}}</span>
                        <input type="date" id="ciclo_fecha_inicio_{{$item->id_modulo}}" name="ciclo_fecha_inicio_{{$item->id_modulo}}"
                               required style="width: 100%" value="{{$item->fecha_inicio}}"
                               class="text-center input-yura_white">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$item->num_poda_siembra != '' ? $item->num_poda_siembra : $modulo->getPodaSiembraActual()}}
                        <select name="ciclo_poda_siembra_{{$item->id_modulo}}" id="ciclo_poda_siembra_{{$item->id_modulo}}"
                                class="input-yura_white">
                            <option value="P" {{$item->poda_siembra == 'P' ? 'selected' : ''}}>Poda</option>
                            <option value="S" {{$item->poda_siembra == 'S' ? 'selected' : ''}}>Siembra</option>
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{difFechas($item->fecha_fin != '' ? $item->fecha_fin : date('Y-m-d'), $item->fecha_inicio)->days}}
                    </td>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($item->fecha_cosecha != '')
                            {{difFechas($item->fecha_cosecha, $item->fecha_inicio)->days}}
                        @else
                            0
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{number_format($item->tallos_cosechados)}}
                    </th>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{$item->fecha_cosecha}}</span>
                        <input type="text" id="ciclo_fecha_cosecha_{{$item->id_modulo}}" name="ciclo_fecha_cosecha_{{$item->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)" maxlength="3"
                               value="{{$item->fecha_cosecha != '' ? difFechas($item->fecha_cosecha, $item->fecha_inicio)->days : ''}}"
                               class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{$item->fecha_fin}}</span>
                        <input type="date" id="ciclo_fecha_fin_{{$item->id_modulo}}" name="ciclo_fecha_fin_{{$item->id_modulo}}"
                               style="width: 100%" value="{{$item->fecha_fin}}"
                               class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_area += $item->area;
                        @endphp
                        <span class="hidden">{{number_format($item->area, 2)}}</span>
                        <input type="number" id="ciclo_area_{{$item->id_modulo}}" name="ciclo_area_{{$item->id_modulo}}"
                               class="text-center input-yura_white" value="{{$item->area}}"
                               style="width: 100%" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_iniciales += $item->plantas_iniciales >= 0 ? $item->plantas_iniciales : 0;
                        @endphp
                        <span class="hidden">{{$item->plantas_iniciales}}</span>
                        <input type="number" id="ciclo_plantas_iniciales_{{$item->id_modulo}}"
                               name="ciclo_plantas_iniciales_{{$item->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)"
                               value="{{$item->plantas_iniciales > 0 ? $item->plantas_iniciales : ($getLastCiclo != '' ? $getLastCiclo->plantas_iniciales : 0)}}"
                               class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_muertas += $item->plantas_muertas >= 0 ? $item->plantas_muertas : 0;
                        @endphp
                        <span class="hidden">{{$item->plantas_muertas}}</span>
                        <input type="number" id="ciclo_plantas_muertas_{{$item->id_modulo}}"
                               name="ciclo_plantas_muertas_{{$item->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)"
                               value="{{$item->plantas_muertas}}"
                               class="text-center input-yura_white" required>
                    </td>

                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            if ($item->plantas_iniciales > 0)
                                if ($item->plantas_muertas > 0)
                                    $plantas_actuales = $item->plantas_iniciales - $item->plantas_muertas;
                                else
                                    $plantas_actuales = $item->plantas_iniciales;
                            $plantas_actuales = 0;

                            $total_actuales += $plantas_actuales;
                        @endphp
                        {{$plantas_actuales}}
                    </td>

                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{$item->conteo}}</span>
                        <input type="number" id="ciclo_conteo_{{$item->id_modulo}}" name="ciclo_conteo_{{$item->id_modulo}}"
                               style="width: 100%" value="{{$item->conteo}}"
                               class="text-center input-yura_white" required>
                    </td>

                    <td class="text-center" style="border-color: #9d9d9d" colspan="6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Terminar Ciclo"
                                    onclick="terminar_ciclo('{{$item->id_modulo}}')">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Editar Ciclo"
                                    onclick="update_ciclo('{{$item->id_ciclo}}', '{{$item->id_modulo}}')">
                                <i class="fa fa-fw fa-pencil"></i>
                            </button>
                            @if($item->cantidad_ciclos > 0)
                                <button type="button" class="btn btn-xs btn-yura_default" title="Ver Ciclos"
                                        onclick="ver_ciclos('{{$item->id_modulo}}')">
                                    <i class="fa fa-fw fa-eye"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tr>
                <th class="text-center" style="border-color: #9d9d9d" colspan="{{8}}">
                    Total
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{number_format($total_area, 2)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{$total_iniciales > 0 ? number_format($total_iniciales, 2) : ''}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{$total_muertas > 0 ? number_format($total_muertas, 2) : ''}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{number_format($total_actuales, 2)}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                </th>
            </tr>
        </table>
    </div>
@else
    <div class="alert alert-info text-center">
        No hay resultados que mostrar
    </div>
@endif