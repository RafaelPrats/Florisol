@if(count($modulos) > 0)
    <div style="overflow-x: scroll">
        <table class="table-striped table-bordered" width="100%" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_listado_ciclos">
            <thead>
            <tr>
                <th class="text-center" rowspan="2"
                    style="border-color: white; color: white; background-color: #00b388; border-radius: 18px 0 0 0;">
                    Módulo
                </th>
                <th class="text-center" style="border-color: white; color: white; background-color: #00b388" colspan="8">
                    <button type="button" class="btn btn-xs btn-yura_dark pull-left" title="Activar un módulo nuevo"
                            onclick="crear_activar_modulo()">
                        <i class="fa fa-fw fa-plus"></i> Activar un módulo nuevo
                    </button>
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
            @foreach($modulos as $pos_mdl => $modulo)
                @php
                    $getLastCiclo = $modulo->getLastCiclo();
                @endphp
                <tr>
                    <td class="text-center" style="border-color: #9d9d9d">
                        {{$modulo->nombre}}
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{date('Y-m-d')}}</span>
                        <input type="date" id="ciclo_fecha_inicio_{{$modulo->id_modulo}}" name="ciclo_fecha_inicio_{{$modulo->id_modulo}}"
                               required style="width: 100%" value="{{date('Y-m-d')}}"
                               class="text-center input-yura_white">
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select name="ciclo_poda_siembra_{{$modulo->id_modulo}}" id="ciclo_poda_siembra_{{$modulo->id_modulo}}"
                                class="input-yura_white">
                            <option value="P">Poda</option>
                            <option value="S">Siembra</option>
                        </select>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden"></span>
                        <input type="text" id="ciclo_fecha_cosecha_{{$modulo->id_modulo}}" name="ciclo_fecha_cosecha_{{$modulo->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)" maxlength="3"
                               value="" class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden"></span>
                        <input type="date" id="ciclo_fecha_fin_{{$modulo->id_modulo}}" name="ciclo_fecha_fin_{{$modulo->id_modulo}}"
                               style="width: 100%" value=""
                               class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_area += $modulo->area;
                        @endphp
                        <span class="hidden">{{number_format($modulo->area, 2)}}</span>
                        <input type="number" id="ciclo_area_{{$modulo->id_modulo}}" name="ciclo_area_{{$modulo->id_modulo}}"
                               class="text-center input-yura_white" value="{{$modulo->area}}"
                               style="width: 100%" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_iniciales += $getLastCiclo != '' ? $getLastCiclo->plantas_iniciales : 0;
                        @endphp
                        <span class="hidden">{{$getLastCiclo != '' ? $getLastCiclo->plantas_iniciales : 0}}</span>
                        <input type="number" id="ciclo_plantas_iniciales_{{$modulo->id_modulo}}"
                               name="ciclo_plantas_iniciales_{{$modulo->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)"
                               value="{{$getLastCiclo != '' ? $getLastCiclo->plantas_iniciales : 0}}"
                               class="text-center input-yura_white" required>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        @php
                            $total_muertas += 0;
                        @endphp
                        <span class="hidden">0</span>
                        <input type="number" id="ciclo_plantas_muertas_{{$modulo->id_modulo}}"
                               name="ciclo_plantas_muertas_{{$modulo->id_modulo}}"
                               style="width: 100%" onkeypress="return isNumber(event)"
                               value="{{0}}"
                               class="text-center input-yura_white" required>
                    </td>

                    <td class="text-center" style="border-color: #9d9d9d">
                        <span class="hidden">{{$getLastCiclo != '' ? $getLastCiclo->conteo : ''}}</span>
                        <input type="number" id="ciclo_conteo_{{$modulo->id_modulo}}" name="ciclo_conteo_{{$modulo->id_modulo}}"
                               style="width: 100%" value="{{$getLastCiclo != '' ? $getLastCiclo->conteo : ''}}"
                               class="text-center input-yura_white" required>
                    </td>

                    <td class="text-center" style="border-color: #9d9d9d" colspan="6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary" title="Crear Ciclo"
                                    onclick="store_ciclo('{{$modulo->id_modulo}}')">
                                <i class="fa fa-fw fa-save"></i>
                            </button>
                            @if(count($modulo->ciclos->where('estado',1)) > 0)
                                <button type="button" class="btn btn-xs btn-yura_default" title="Ver Ciclos"
                                        onclick="ver_ciclos('{{$modulo->id_modulo}}')">
                                    <i class="fa fa-fw fa-eye"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tr>
                <th class="text-center" style="border-color: #9d9d9d" colspan="5">
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