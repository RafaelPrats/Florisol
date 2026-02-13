<div style="overflow-x: scroll; overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_listado_all">
    	<thead>
        <tr class="tr_fija_top_0">
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2" style="width: 30px">
                Total:{{ count($combinaciones) }}
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                <div style="width: 130px">
                    Planta
                </div>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                <div style="width: 170px">
                    Variedad
                </div>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Venta Total
            </th>
            @foreach ($fechas as $f)
                <th class="text-center th_yura_green celda_cargando" colspan="2">
                    {{ $f }}
                </th>
            @endforeach
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Negativos Total
                <button type="button" class="btn btn-xs btn-yura_default btn_listar_reporte hidden"
                    title="Ver solo los negativos" onclick="mostrar_solo_negativos()">
                    <i class="fa fa-fw fa-filter"></i>
                </button>
            </th>
            <th class="padding_lateral_5 th_yura_green celda_cargando" rowspan="2">
                Perdida Total
                <button type="button" class="btn btn-xs btn-yura_default btn_listar_reporte hidden"
                    title="Ver solo las perdidas" onclick="mostrar_solo_perdidas()">
                    <i class="fa fa-fw fa-filter"></i>
                </button>
            </th>
        </tr>
        <tr class="tr_fija_top_1">
            @foreach ($fechas as $f)
                <th class="padding_lateral_5 text-center bg-yura_dark">
                    Saldo
                </th>
                <th class="padding_lateral_5 text-center bg-yura_dark">
                    Perdida
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach ($listado as $pos_i => $item)
		    <tr
		        class="tr_listado {{ $item['total_negativos'] < 0 ? 'tr_negativo' : '' }} {{ $item['total_perdidas'] > 0 ? 'tr_perdida' : '' }}">
		        <th class="padding_lateral_5" style="border-color: #9d9d9d">
		            {{ $pos_i + 1 }}
		        </th>
		        <th class="padding_lateral_5" style="border-color: #9d9d9d">
		            {{ $item['variedad']->planta_nombre }}
		        </th>
		        <th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d" onmouseover="$(this).css('background-color', 'cyan')" onmouseleave="$(this).css('background-color', '')" onclick="seleccionar_variedad('{{$item['variedad']->id_variedad}}', '{{$item['variedad']->id_planta}}')">
		            {{ $item['variedad']->variedad_nombre }}
		        </th>
		        <th class="padding_lateral_5 bg-yura_dark" style="border-right: 2px solid black">
		            {{ number_format($item['total_ventas']) }}
		        </th>
		        @foreach ($item['list_saldos'] as $pos_s => $s)
		            <th class="padding_lateral_5 text-center"
		                style="border-color: #9d9d9d; background-color: {{ $pos_s % 2 == 0 ? '#eeeeee' : '' }}; color: {{ $s < 0 ? 'red' : 'black' }}">
		                {{ $s }}
		            </th>
		            <th class="padding_lateral_5 text-center"
		                style="border-color: #9d9d9d; border-right: 2px solid black; background-color: {{ $pos_s % 2 == 0 ? '#eeeeee' : '' }}; color: black">
		                {{ $item['list_perdidas'][$pos_s] }}
		            </th>
		        @endforeach
		        <th class="padding_lateral_5 bg-yura_dark">
		            @if ($item['total_negativos'] < 0)
		                {{ number_format($item['total_negativos']) }}
		            @endif
		        </th>
		        <th class="padding_lateral_5" style="background-color: #eeeeee; color: {{ $item['total_perdidas'] > 0 ? 'red' : '' }}; border-color: #9d9d9d">
		            @if ($item['total_perdidas'] > 0)
		                {{ number_format($item['total_perdidas']) }}
		            @endif
		        </th>
		    </tr>
		@endforeach
		</tbody>
    </table>
</div>

<script type="text/javascript">
	estructura_tabla('table_listado_all');

    function seleccionar_variedad(variedad, planta){
        $('#planta_filtro').val(planta);
        select_planta_global(planta, 'variedad_filtro', 'div_filtro_variedad', '<option value=>Todas las Varidades</option>');
        setTimeout(function(){
            $('#variedad_filtro').val(variedad);
            listar_reporte();
        },2000);
    }
</script>