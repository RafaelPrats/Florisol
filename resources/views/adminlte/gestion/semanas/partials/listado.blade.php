@if(sizeof($semanas)>0)
    <table width="100%" class="table-responsive table-bordered" style="font-size: 0.8em; border-color: #9d9d9d; border-radius: 18px 18px 0 0"
           id="table_content_semanas">
        <thead>
        <tr style="background-color: #dd4b39; color: white; border-radius: 18px 0 0 0">
            <th class="text-center th_yura_green" style="border-color: #9d9d9d"
                rowspan="3">
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d"
                rowspan="3">
                SEMANA
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d; width: 100px"
                rowspan="3">
                INICIO
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d; width: 100px"
                rowspan="3">
                FIN
            </th>
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248" colspan="4">
                Proyección Exportación
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                CURVA
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d"
                rowspan="3">
                DESECHOS %
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                INICIO COSECHA PODA
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                INICIO COSECHA SIEMBRA
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                Ptas INICIALES
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                DENSIDAD
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                % Bqt.
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d" rowspan="3">
                % Exp.
            </th>
            <th class="text-center th_yura_green" style="border-color: #9d9d9d; border-radius: 0 18px 0 0"
                rowspan="3">
                OPCIONES
            </th>
        </tr>
        <tr style="background-color: #dd4b39; color: white">
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248" colspan="2">
                Tallos Planta
            </th>
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248" colspan="2">
                Tallos Ramo
            </th>
        </tr>
        <tr style="background-color: #dd4b39; color: white">
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248">
                Siembra
            </th>
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248">
                Poda
            </th>
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248">
                Siembra
            </th>
            <th class="text-center th_yura_green"
                style="border-color: #9d9d9d; background-color: #0b3248">
                Poda
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($semanas as $item)
            @php
                $se = $item->getSemanaEmpresa($empresa);
            @endphp
            <tr onmouseover="$(this).css('background-color','#add8e6')" onmouseleave="$(this).css('background-color','')"
                id="row_semanas_{{$item->id_semana}}">
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="checkbox" id="check_{{$item->id_semana}}" class="pull-left check_week">
                    <input type="hidden" class="ids_semana" value="{{$item->id_semana}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">{{$item->codigo}}</td>
                <td style="border-color: #9d9d9d" class="text-center">{{$item->fecha_inicial}}</td>
                <td style="border-color: #9d9d9d" class="text-center">{{$item->fecha_final}}</td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_tallos_planta_siembra-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="tallos_planta_siembra_{{$item->id_semana}}"
                               id="tallos_planta_siembra_{{$item->id_semana}}" style="width: 100%"
                               required value="{{$item->tallos_planta_siembra}}" min="0" max="99">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_tallos_planta_poda-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="tallos_planta_poda_{{$item->id_semana}}"
                               id="tallos_planta_poda_{{$item->id_semana}}" style="width: 100%"
                               required value="{{$item->tallos_planta_poda}}" min="0" max="99">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_tallos_ramo_siembra-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="tallos_ramo_siembra_{{$item->id_semana}}"
                               id="tallos_ramo_siembra_{{$item->id_semana}}" style="width: 100%"
                               required value="{{$item->tallos_ramo_siembra}}" min="0" max="99">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_tallos_ramo_poda-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="tallos_ramo_poda_{{$item->id_semana}}"
                               id="tallos_ramo_poda_{{$item->id_semana}}" style="width: 100%"
                               required value="{{$item->tallos_ramo_poda}}" min="0" max="99">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_curva-{{$item->id_semana}}">
                        <input type="text" class="text-center" name="curva_{{$item->id_semana}}" id="curva_{{$item->id_semana}}"
                               value="{{$item->curva}}" maxlength="250" required placeholder="10-20-40-30" style="width: 100%">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_desecho-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="desecho_{{$item->id_semana}}" id="desecho_{{$item->id_semana}}"
                               required value="{{$item->desecho}}" maxlength="2" min="0" max="99" style="width: 100%">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_poda-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="semana_poda_{{$item->id_semana}}"
                               id="semana_poda_{{$item->id_semana}}" required value="{{$item->semana_poda}}" maxlength="2" min="1"
                               max="{{count($semanas)}}" style="width: 100%">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <form id="form-semana_siembra-{{$item->id_semana}}">
                        <input type="number" class="text-center" name="semana_siembra_{{$item->id_semana}}"
                               id="semana_siembra_{{$item->id_semana}}" required value="{{$item->semana_siembra}}" maxlength="2" min="1"
                               max="{{count($semanas)}}" style="width: 100%">
                    </form>
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="number" style="width: 100%" id="plantas_iniciales_{{$item->id_semana}}" min="0" class="text-center"
                           value="{{isset($se) ? $se->plantas_iniciales : 0}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="number" style="width: 100%" class="text-center" id="densidad_{{$item->id_semana}}" min="0"
                           value="{{isset($se) ? $se->densidad : 0}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="number" style="width: 100%" id="porcent_bouquetera_{{$item->id_semana}}" min="0" class="text-center"
                           value="{{$item->porcent_bouquetera}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="number" style="width: 100%" class="text-center" id="porcent_exportada_{{$item->id_semana}}" min="0"
                           value="{{$item->porcent_exportada}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <button type="button" class="btn btn-yura_dark btn-xs" title="Guardar semana {{$item->codigo}}"
                            onclick="save_semana('{{$item->id_semana}}')">
                        <i class="fa fa-fw fa-save"></i>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tr>
            <th colspan="16" style="border-color: #9d9d9d"></th>
            <th class="text-center" style="border-color: #9d9d9d">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="update_semanas()">
                    <i class="fa fa-fw fa-save"></i>
                </button>
            </th>
        </tr>
    </table>

    <div class="input-group">
        <div class="input-group-btn">
            <button type="button" class="btn btn-default" onclick="select_all()">
                <i class="fa fa-fw fa-long-arrow-up"></i> Seleccionar todos
            </button>
        </div>
        <select name="all_options" id="all_options" class="form-control" onchange="select_all_options($(this).val())">
            <option value="">¿Qué desea hacer para los marcados?</option>
            <option value="1">Igualar todos los datos</option>
            <optgroup label="Igualar por separado"></optgroup>
            <option value="6">Igualar solamente los tallos por planta siembra</option>
            <option value="7">Igualar solamente los tallos por planta poda</option>
            <option value="8">Igualar solamente los tallos por ramo siembra</option>
            <option value="9">Igualar solamente los tallos por ramo poda</option>
            <option value="2">Igualar solamente la curva</option>
            <option value="3">Igualar solamente el porcentaje de desechos</option>
            <option value="4">Igualar solamente la semana de inicio de poda</option>
            <option value="5">Igualar solamente la semana de inicio de siembra</option>
            <option value="11">Igualar solamente las plantas iniciales</option>
            <option value="12">Igualar solamente la densidad</option>
            <option value="13">Igualar solamente el porcentaje bqt.</option>
            <option value="14">Igualar solamente el porcentaje export.</option>
            <optgroup label="Otras opciones"></optgroup>
            <option value="10">Actualizar CICLOS (semana de cosecha, tallos x ramo, curva y desecho) según las SEMANAS</option>
            <option value="15">Actualizar SIEMBRAS según las SEMANAS</option>
        </select>
    </div>
@else
    <div class="alert alert-info text-center">No se han programado semanas para esta variedad en el año indicado</div>
@endif

<script>
    //estructura_tabla();
</script>