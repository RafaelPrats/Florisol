<div style="overflow-x: scroll; overflow-y: scroll; width: 100%; max-height: 700px;">
	<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_listado">
		<thead>
		<tr class="tr_fija_top_0">
			<th class="padding_lateral_5 th_yura_green">
				RECETA
			</th>
			<th class="text-center th_yura_green" style="width: 70px">
				LONGITUD
			</th>
			@php
				$totales = [];
			@endphp
			@foreach($fechas as $f)
				<th class="text-center th_yura_green th_fechas" data-fecha="{{$f}}" style="width: 130px;">
					{{ getDias(TP_ABREVIADO, FR_ARREGLO)[transformDiaPhp(date('w', strtotime($f)))] }}
					<br>
                    <small>{{ convertDateToText($f) }}</small>
				</th>
				@php
					$totales[] = [
						'pedidos' => 0,
						'armados' => 0,
						'disponibles' => 0,
						'despachados' => 0,
					];
				@endphp
			@endforeach
			<th class="text-center bg-yura_dark" style="width: 60px">
				DESP.
			</th>
			<th class="text-center bg-yura_dark" style="width: 70px">
				ARMADOS
			</th>
		</tr>
		</thead>
		<tbody>
			@foreach($listado as $item)
				@php
					$total_despachados_receta = 0;
					$total_armados_receta = 0;
				@endphp
				<tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
					<th class="padding_lateral_5 mouse-hand" style="border-color: #9d9d9d;" onclick="modal_receta('{{$item['receta']->id_variedad}}', '{{$item['receta']->longitud}}')">
						{{$item['receta']->nombre}}
					</th>
					<th class="text-center" style="border-color: #9d9d9d;">
						{{$item['receta']->longitud}}cm
					</th>
					@foreach($fechas as $pos_f => $fecha)
						@php
							$valor = '';
							foreach($item['valores'] as $val)
								if($val->fecha == $fecha)
									$valor = $val;
							if($valor != ''){
								$total_armados_receta += $valor != '' ? $valor->armados : 0;
								$totales[$pos_f]['pedidos'] += $valor->ramos;
								$totales[$pos_f]['armados'] += $valor->armados;
							}
						@endphp
						<td class="text-center" style="border-color: #9d9d9d; background-color: {{$pos_f % 2 == 0 ? '#dddddd' : ''}};">
							@if($valor != '')
								@php
									$despachos = $valor->despachados - $valor->armados;
									$totales[$pos_f]['despachados'] += $despachos;
									$total_despachados_receta += $despachos;
									$disponibles = getRamosDisponiblesByFecha($item['receta']->id_variedad, $item['receta']->longitud, $fecha);
									$totales[$pos_f]['disponibles'] += $disponibles;
								@endphp
								<div class="btn-group">
									<button class="btn btn-xs btn-yura_dark">
										{{$valor->ramos}}
									</button>
									<button class="btn btn-xs btn-yura_{{$valor->armados < $valor->ramos ? 'danger' : 'default'}}">
										{{$valor->ramos - $valor->armados}}
									</button>
									<button class="btn btn-xs btn-yura_info">
										{{$disponibles}}
									</button>
									@if($despachos > 0)
										<button class="btn btn-xs btn-yura_warning">
											{{$despachos}}
										</button>
									@endif
								</div>
							@endif
						</td>
					@endforeach
					<th class="text-center" style="border-color: #9d9d9d;">
						{{$total_despachados_receta > 0 ? $total_despachados_receta : 0}}
					</th>
					<th class="text-center" style="border-color: #9d9d9d;">
						{{$total_armados_receta}}
					</th>
				</tr>
			@endforeach
		</tbody>
		<tr class="tr_fija_bottom_0">
			<th class="padding_lateral_5 th_yura_green" colspan="2">
				Totales
			</th>
			@php
				$total_despachados = 0;
				$total_armados = 0;
			@endphp
			@foreach($totales as $v)
				@php
					$total_despachados += $v['despachados'];
					$total_armados += $v['armados'];
				@endphp
				<th class="text-center" style="background-color: #eeeeee; border-color: #9d9d9d;">
					<div class="btn-group">
						<button class="btn btn-xs btn-yura_dark">
							{{$v['pedidos']}}
						</button>
						<button class="btn btn-xs btn-yura_{{$v['armados'] < $v['pedidos'] ? 'danger' : 'default'}}">
							{{$v['pedidos'] - $v['armados']}}
						</button>
						<button class="btn btn-xs btn-yura_info">
							{{$v['disponibles']}}
						</button>
						@if($v['despachados'] > 0)
							<button class="btn btn-xs btn-yura_warning">
								{{$v['despachados']}}
							</button>
						@endif
					</div>
				</th>
			@endforeach
			<th class="text-center th_yura_green">
				{{$total_despachados}}
			</th>
			<th class="text-center th_yura_green">
				{{$total_armados}}
			</th>
		</tr>
	</table>
</div>

<style type="text/css">
	.tr_fija_top_1{
		position: sticky;
		top: 23px;
		z-index: 8;
	}

	.tr_fija_bottom_0{
		position: sticky;
		bottom: 0;
		z-index: 9;
	}
</style>

<script type="text/javascript">
	//estructura_tabla('table_listado')

	function modal_receta(variedad, longitud){
		fechas = [];
		th_fechas = $('.th_fechas');
		for(i=0;i<th_fechas.length;i++){
			fechas.push(th_fechas[i].getAttribute('data-fecha'));
		}
        datos = {
        	fechas: JSON.stringify(fechas),
        	variedad: variedad,
        	longitud: longitud,
        }
        get_jquery('{{ url('preproduccion/modal_receta') }}', datos, function(retorno) {
            modal_view('modal_modal_receta', retorno, '<i class="fa fa-fw fa-plus"></i> Pedidos de la Receta',
                true, false, '{{ isPC() ? '90%' : '' }}',
                function() {});
        })
	}
</script>