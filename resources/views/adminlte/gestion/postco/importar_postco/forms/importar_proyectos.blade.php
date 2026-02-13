<div style="width: 100%; overflow-x: scroll; overflow-y: scroll; max-height: 700px;">
	<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d;">
		<tr class="tr_fija_top_0">
			<th class="padding_lateral_5 th_yura_green">
				Bouquets
			</th>
			<th class="padding_lateral_5 th_yura_green">
				Clientes
			</th>
			<th class="text-center th_yura_green" style="width: 70px;">
				Medida
			</th>
			<th class="text-center th_yura_green" style="width: 70px;">
				Cantidad
			</th>
		</tr>
		@foreach($listado as $pos => $item)
			<tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
				<th class="padding_lateral_5 {{ $item['variedad'] == '' ? 'error' : '' }}" style="border-color: #9d9d9d;">
					@if($item['variedad'] != '')
						{{$item['variedad']->nombre}}
						<input type="hidden" id="input_variedad_{{$pos}}" class="inputs_variedad" data-id_variedad="{{$item['variedad']->id_variedad}}" data-longitud="{{$item['longitud']}}" data-ramos="{{$item['ramos']}}" data-pos="{{$pos}}">
					@else
						{{$item['row']['L']}}
					@endif
				</th>
				<th class="padding_lateral_5" style="border-color: #9d9d9d;">
					@foreach($item['clientes'] as $cliente)
						<p class="{{$cliente['id_cliente'] == '' ? 'error' : ''}} clientes_{{$pos}}" data-id_cliente="{{$cliente['id_cliente']}}" data-ramos="{{$cliente['ramos']}}">
							{{$cliente['nombre']}}
						</p>
					@endforeach
				</th>
				<th class="text-center" style="border-color: #9d9d9d;">
					{{$item['longitud']}}cm
				</th>
				<th class="text-center" style="border-color: #9d9d9d;">
					{{$item['ramos']}}
				</th>
			</tr>
		@endforeach
	</table>
</div>

<div class="text-center" style="margin-top: 5px">
	@if($fallos)
		<button class="btn btn-yura_danger">
			<i class="fa fa-fw fa-times"></i> CORRIJA LOS ERRORES
		</button>
	@else
		<button class="btn btn-yura_primary" onclick="store_postco()">
			<i class="fa fa-fw fa-save"></i> GRABAR RECETAS
		</button>
	@endif
</div>

<script>
	function store_postco(){
		texto =
            "<div class='alert alert-warning text-center'><h3><i class='fa fa-fw fa-exclamation-triangle error'></i>¿Esta seguro de <b>GRABAR LOS PEDIDOS PARA LA FECHA: <span class='error'>"+$('#fecha_pedidos').val()+"</span></b>?</h3></div>";

        modal_quest('modal_store_postco', texto, 'Grabar recetas', true, false, '40%', function() {
			data = [];
			inputs_variedad = $('.inputs_variedad');
			for(i=0;i<inputs_variedad.length;i++){
				id = inputs_variedad[i].id;
				pos = $('#'+id).data('pos');
				id_variedad = $('#'+id).data('id_variedad');
				longitud = $('#'+id).data('longitud');
				ramos = $('#'+id).data('ramos');
				clientes = [];
				list_clientes = $('.clientes_'+pos);
				for(x=0;x<list_clientes.length;x++){
					clientes.push({
						id: list_clientes[x].getAttribute('data-id_cliente'),
						ramos: list_clientes[x].getAttribute('data-ramos')
					});
				}
				data.push({
					id_variedad:id_variedad,
					longitud:longitud,
					ramos:ramos,
					clientes:JSON.stringify(clientes),
				});
			}
			datos = {
				_token: '{{csrf_token()}}',
				data:JSON.stringify(data),
				fecha: $('#fecha_pedidos').val()
			}
			post_jquery_m('{{url('importar_postco/store_postco')}}', datos, function(){
				cerrar_modals();
				listar_reporte();
			});
        })
	}
</script>