<table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
    <thead>
        <tr>
            <th class="text-center th_yura_green">
                Planta
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Edad
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                T. x Malla
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
                Mallas Dispo.
            </th>
            <th class="text-center th_yura_green" style="width: 60px">
            </th>
            <th class="text-center bg-yura_dark" colspan="2">
                Sacar Mallas
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($listado as $pos => $item)
            @php
                $variedad = $item->variedad;
            @endphp
            <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->planta->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ $variedad->nombre }}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    {{ difFechas($item->fecha, hoy())->d }} <sup><small><b>dias</b></small></sup>
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="tallos_x_malla_{{ $item->id_guarde }}" min="0"
                        value="{{ $item->tallos_x_malla }}" style="width: 100%; color: black" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <input type="number" id="mallas_{{ $item->id_guarde }}" min="0"
                        value="{{ $item->disponibles }}" style="width: 100%; color: black" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_warning"
                            onclick="update_guarde('{{ $item->id_guarde }}')">
                            <i class="fa fa-fw fa-save"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-yura_danger"
                            onclick="delete_guarde('{{ $item->id_guarde }}')">
                            <i class="fa fa-fw fa-trash"></i>
                        </button>
                    </div>
                </th>
                <th class="text-center" style="border-color: #9d9d9d; width: 60px;">
                    <input type="number" id="sacar_{{ $item->id_guarde }}" min="0"
                        value="" style="width: 100%; color: black" class="text-center">
                </th>
                <th class="text-center" style="border-color: #9d9d9d; width: 60px;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default"
                            onclick="sacar_guarde('{{ $item->id_guarde }}')">
                            <i class="fa fa-fw fa-check"></i>
                        </button>
                    </div>
                </th>
            </tr>
        @endforeach
    </tbody>
</table>

<script type="text/javascript">
    estructura_tabla('table_listado');

    function update_guarde(id){
        datos = {
            _token: '{{csrf_token()}}',
            id:id,
            mallas: $('#mallas_'+id).val(),
            tallos_x_malla: $('#tallos_x_malla_'+id).val(),
        }
        post_jquery_m('{{url('guarde_flor/update_guarde')}}', datos, function(){

        });
    }

    function sacar_guarde(id){
        datos = {
            _token: '{{csrf_token()}}',
            id:id,
            sacar: $('#sacar_'+id).val(),
        }
        post_jquery_m('{{url('guarde_flor/sacar_guarde')}}', datos, function(){
            listar_reporte();
        });
    }

    function delete_guarde(id){
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>ELIMINAR</b> el guarde?</div>";

        modal_quest('modal_delete_inventario', texto, 'Eliminar guarde', true, false, '40%', function() {
            datos = {
                _token: '{{csrf_token()}}',
                id:id,
            }
            post_jquery_m('{{url('guarde_flor/delete_guarde')}}', datos, function(){
                listar_reporte();
            });
        })
    }
</script>