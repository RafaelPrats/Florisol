@if(count($modulo->ciclos) > 0)
    <div style="overflow-x: scroll">
        <table class="table-striped table-bordered table-responsive" width="100%" style="border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0"
               id="table_ver_ciclos">
            <thead>
            <tr>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d; border-radius: 18px 0 0 0">
                    Inicio
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                    Variedad
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                    Área m<sup>2</sup>
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                    Dias
                </th>
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                    Tallos Cosechados
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d;">
                    Final
                </th>
                <th class="text-center th_yura_default" style="border-color: #9d9d9d; border-radius: 0 18px 0 0">
                    Opciones
                </th>
            </tr>
            </thead>

            <tbody>
            @foreach($modulo->ciclos->where('estado',1)->sortByDesc('fecha_inicio') as $pos_ciclo => $ciclo)
                <input type="hidden" id="activo_ciclo_modal_{{$ciclo->id_ciclo}}" value="{{$ciclo->activo}}">
                <tr class="{{$ciclo->activo == 1 ? 'background-color_yura' : ''}} {{$ciclo->estado == 0 ? 'error' : ''}}"
                    title="{{$ciclo->activo == 1 ? 'Activo' : ''}}">
                    <th class="text-center" style="border-color: #9d9d9d">
                        <span class="elemento_view_{{$ciclo->id_ciclo}}">{{$ciclo->fecha_inicio}}</span>
                        <input type="date" id="fecha_inicio_ciclo_modal_{{$ciclo->id_ciclo}}" value="{{$ciclo->fecha_inicio}}"
                               class="elemento_input_{{$ciclo->id_ciclo}} text-center input-yura_white {{$ciclo->activo == 1 ? 'background-color_yura' : ''}}"
                               style="width: 100%; display: none"
                               required>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <span class="elemento_view_{{$ciclo->id_ciclo}}">{{$ciclo->variedad->siglas}}</span>
                        <select id="variedad_ciclo_modal_{{$ciclo->id_ciclo}}" class="elemento_input_{{$ciclo->id_ciclo}}
                        {{$ciclo->activo == 1 ? 'background-color_yura' : ''}} input-yura_white" style="width: 100%; display: none">
                            @foreach(getVariedades() as $item)
                                <option value="{{$item->id_variedad}}" {{$item->id_variedad == $ciclo->id_variedad ? 'selected' : ''}}>
                                    {{$item->siglas}}
                                </option>
                            @endforeach
                        </select>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <span class="elemento_view_{{$ciclo->id_ciclo}}">{{$ciclo->area}}m<sup>2</sup></span>
                        <input type="number" id="area_ciclo_modal_{{$ciclo->id_ciclo}}" value="{{$ciclo->area}}"
                               class="elemento_input_{{$ciclo->id_ciclo}} text-center input-yura_white {{$ciclo->activo == 1 ? 'background-color_yura' : ''}}"
                               style="width: 100%; display: none" required>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        @if($ciclo->fecha_fin != '')
                            {{difFechas($ciclo->fecha_fin, $ciclo->fecha_inicio)->days}}
                        @else
                            {{difFechas(date('Y-m-d'), $ciclo->fecha_inicio)->days}}
                        @endif
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{number_format($ciclo->getTallosCosechados())}}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <span class="elemento_view_{{$ciclo->id_ciclo}}">
                            {{$ciclo->fecha_fin}}
                        </span>
                        <input type="date" id="fecha_fin_ciclo_modal_{{$ciclo->id_ciclo}}" value="{{$ciclo->fecha_fin}}"
                               class="elemento_input_{{$ciclo->id_ciclo}} text-center input-yura_white {{$ciclo->activo == 1 ? 'background-color_yura' : ''}}"
                               style="width: 100%; display: none" required>
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Ver cosechas"
                                    onclick="ver_cosechas('{{$ciclo->id_ciclo}}')">
                                <i class="fa fa-fw fa-leaf"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar ciclo"
                                    onclick="eliminar_ciclo('{{$ciclo->id_ciclo}}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <input type="hidden" id="id_modulo" value="{{$modulo->id_modulo}}">

    <script>
        estructura_tabla('table_ver_ciclos', false, true);
        $('#table_ver_ciclos_length label').addClass('text-color_yura');
        $('#table_ver_ciclos_length label select').addClass('input-yura_white');
        $('#table_ver_ciclos_filter label').addClass('text-color_yura');
        $('#table_ver_ciclos_filter label input').addClass('input-yura_white');
    </script>
@else
    <div class="alert alert-info text-center">
        No se han encontrado resultados
    </div>
@endif

<script>
    function ver_cosechas(ciclo) {
        datos = {
            ciclo: ciclo,
        };
        get_jquery('{{url('sectores_modulos/ver_cosechas')}}', datos, function (retorno) {
            modal_view('modal-view_ver_cosechas', retorno, '<i class="fa fa-fw fa-le"></i> Cosechas', true, false, '{{isPC() ? '95%' : ''}}');
        });
    }

    function eliminar_ciclo(ciclo) {
        datos = {
            _token: '{{csrf_token()}}',
            ciclo: ciclo,
        };
        modal_quest('modal-quest_eliminar_ciclo', '<div class="alert alert-info text-center">¿Está seguro de <strong>ELIMINAR</strong> este ciclo?</div>',
            '<i class="fa fa-fw fa-exclamation-triangle"></i> Confirmar acción', true, false, '{{isPC() ? '35%' : ''}}', function () {
                post_jquery('{{url('sectores_modulos/eliminar_ciclo')}}', datos, function () {
                    listar_ciclos_sect_mod_perennes();
                    cerrar_modals();
                });
                cerrar_modals();
            });
    }
</script>