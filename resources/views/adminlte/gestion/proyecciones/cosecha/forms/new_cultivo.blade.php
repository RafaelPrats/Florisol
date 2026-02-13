<legend class="text-center" style="font-size: 1em">
    Crear nuevo cultivo para la variedad <strong>{{$variedad->nombre}}</strong> en la semana <strong>{{$semana->codigo}}</strong>
</legend>

<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Sector
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <select id="new_sector" style="width: 100%">
                @foreach($sectores as $s)
                    <option value="{{$s->id_sector}}">{{$s->nombre}}</option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Fecha inicio
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="date" id="new_fecha_inicio" class="text-center" style="width: 100%" value="{{$semana->fecha_inicial}}"
                   min="{{$semana->fecha_inicial}}" max="{{$semana->fecha_final}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Curva
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="text" id="new_curva" class="text-center" style="width: 100%" placeholder="20-40-40-20" value="{{$semana->curva}}">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Semana cosecha
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_semana_cosecha" class="text-center" style="width: 100%" value="14" min="5">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            % Desecho
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_desecho" class="text-center" style="width: 100%" value="{{$semana->desecho}}" min="0">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Plantas iniciales
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_plantas_iniciales" class="text-center" style="width: 100%" value="0" min="0">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Densidad m<sup>2</sup>
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_densidad" class="text-center" style="width: 100%" value="0" min="0" onkeyup="calcular_area()"
                   onchange="calcular_area()">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Área
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_area" class="text-center" style="width: 100%" value="0" min="0">
        </td>
    </tr>
    <tr>
        <th class="text-center" style="background-color: #e9ecef; border-color: #9d9d9d">
            Conteo tallos x pta
        </th>
        <td class="text-center" style="border-color: #9d9d9d">
            <input type="number" id="new_conteo" class="text-center" style="width: 100%" value="{{$semana->tallos_planta_siembra}}" min="0">
        </td>
    </tr>
</table>

<div class="text-center" style="margin-top: 10px">
    <button type="button" class="btn btn-yura_primary" onclick="store_new_cultivo()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

<script>
    function store_new_cultivo() {
        datos = {
            _token: '{{csrf_token()}}',
            variedad: '{{$variedad->id_variedad}}',
            siglas_variedad: '{{$variedad->siglas}}',
            semana: '{{$semana->codigo}}',
            sector: $('#new_sector').val(),
            fecha_inicio: $('#new_fecha_inicio').val(),
            curva: $('#new_curva').val(),
            semana_cosecha: $('#new_semana_cosecha').val(),
            desecho: $('#new_desecho').val(),
            area: $('#new_area').val(),
            plantas_iniciales: $('#new_plantas_iniciales').val(),
            conteo: $('#new_conteo').val(),
        };
        post_jquery('{{url('proy_cosecha/store_new_cultivo')}}', datos, function (retorno) {
            listar_proyecciones_cosecha();
            cerrar_modals();
        });
    }

    function calcular_area() {
        ptas_iniciales = parseInt($('#new_plantas_iniciales').val());
        densidad = parseFloat($('#new_densidad').val());
        area = ptas_iniciales / densidad;
        $('#new_area').val(Math.round(area * 100) / 100);
    }
</script>