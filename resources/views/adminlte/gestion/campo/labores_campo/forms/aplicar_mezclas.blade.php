<legend style="font-size: 1em" class="text-center">Seleccione la MEZCLA que desea aplicar</legend>
<table class="table-bordered table-striped" style="width: 100%; border: 1px solid #9d9d9d">
    <tr>
        <th class="text-center th_yura_green">
            Aplicación
        </th>
        <th class="text-center th_yura_green">
            Mezcla
        </th>
        <th class="text-center th_yura_green">
            Tipo
        </th>
        <th class="text-center th_yura_green">
            CC por defecto
        </th>
        <th class="text-center th_yura_green">
            Repeticiones
        </th>
        <th class="text-center th_yura_green">
            CC x Repeticiones
        </th>
        <th class="text-center th_yura_green">
            Mezcla
        </th>
    </tr>
    @foreach($labor->mezclas as $pos => $mezcla)
        <tr>
            <th class="text-center" style="border-color: #9d9d9d" rowspan="2">
                {{$labor->nombre}}
            </th>
            <th class="text-center" style="border-color: #9d9d9d" rowspan="2">
                {{$mezcla->nombre}}
            </th>
            <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                SIEMBRAS
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="mezcla_litros_x_cama_{{$mezcla->id_aplicacion_mezcla}}" style="width: 100%"
                       value="{{$mezcla->litro_x_cama}}" class="text-center">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{$mezcla->repeticiones}}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{$mezcla->litros_x_repeticiones}}
            </th>
            <th class="text-center" style="border-color: #9d9d9d" rowspan="2">
                <button type="button" class="btn btn-xs btn-yura_primary" onclick="seleccionar_mezcla('{{$mezcla->id_aplicacion_mezcla}}')">
                    <i class="fa fa-fw fa-check"></i> Seleccionar
                </button>
            </th>
        </tr>
        <tr>
            <th class="text-center bg-yura_dark" style="border-color: #9d9d9d">
                PODAS
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="mezcla_litros_x_cama_poda_{{$mezcla->id_aplicacion_mezcla}}" style="width: 100%"
                       value="{{$mezcla->litro_x_cama_poda}}" class="text-center">
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{$mezcla->repeticiones_poda}}
            </th>
            <th class="text-center" style="border-color: #9d9d9d">
                {{$mezcla->litros_x_repeticiones_poda}}
            </th>
        </tr>
        @if($pos == 0)
            <script>
                setTimeout(seleccionar_mezcla('{{$mezcla->id_aplicacion_mezcla}}'), 1000);
            </script>
        @endif
    @endforeach
</table>

<div style="margin-top: 10px" id="div_aplicar_mezcla"></div>