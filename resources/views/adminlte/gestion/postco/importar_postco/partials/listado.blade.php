<div style="width: 100%; overflow-y: scroll; max-height: 700px;">
	<table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%;" id="table_listado">
		<thead>
		<tr class="tr_fija_top_0">
			<th class="padding_lateral_5 th_yura_green">
				CLIENTES
			</th>
			<th class="padding_lateral_5 th_yura_green">
				RECETA
			</th>
			<th class="text-center th_yura_green" style="width: 70px">
				LONGITUD
			</th>
			<th class="padding_lateral_5 th_yura_green" style="width: 70px">
				PEDIDOS
			</th>
			<th class="padding_lateral_5 th_yura_green" style="width: 70px">
				ARMADOS
			</th>
			<th class="padding_lateral_5 th_yura_green" style="width: 90px">
				OPCIONES
			</th>
		</tr>
		</thead>
		<tbody>
			@foreach($listado as $item)
				<tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
					<td class="padding_lateral_5" style="border-color: #9d9d9d;">
						@foreach($item->clientes as $cli)
							<p>- {{$cli->cliente->detalle()->nombre}}</p>
						@endforeach
					</td>
					<td class="padding_lateral_5" style="border-color: #9d9d9d;">
						{{$item->variedad->nombre}}
					</td>
					<td class="text-center" style="border-color: #9d9d9d;">
						{{$item->longitud}}cm
					</td>
					<td class="text-center" style="border-color: #9d9d9d;">
						{{$item->ramos}}
					</td>
					<td class="text-center" style="border-color: #9d9d9d;">
						{{$item->armados}}
					</td>
					<td class="text-center" style="border-color: #9d9d9d;">
						<div class="btn-group">
							<button class="btn btn-xs btn-yura_danger" title="Eliminar" onclick="delete_postco('{{$item->id_postco}}')">
								<i class="fa fa-fw fa-trash"></i> Eliminar
							</button>
						</div>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>

<script type="text/javascript">
	estructura_tabla('table_listado');

    function delete_postco(id){
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>ELIMINAR</b> la receta?</div>";

        modal_quest('modal_delete_inventario', texto, 'Eliminar receta', true, false, '40%', function() {
            datos = {
                _token: '{{csrf_token()}}',
                id:id,
            }
            post_jquery_m('{{url('importar_postco/delete_postco')}}', datos, function(){
                listar_reporte();
            });
        })
    }
</script>