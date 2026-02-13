<div style="overflow-y: scroll; max-height: 700px">
    <table class="table-bordered" style="border: 1px solid #9d9d9d; width: 100%" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green">
                    Fecha Ingreso
                </th>
                <th class="text-center th_yura_green">
                    Usuario
                </th>
                <th class="text-center th_yura_green">
                    Proveedor
                </th>
                <th class="text-center th_yura_green">
                    Variedad
                </th>
                <th class="text-center th_yura_green">
                    Longitud
                </th>
                <th class="text-center th_yura_green">
                    TxR
                </th>
                <th class="text-center th_yura_green">
                    Ingreso
                </th>
                <th class="text-center th_yura_green">
                    Disponibles
                </th>
                <th class="text-center th_yura_green">
                    T. Tallos
                </th>
                <th class="text-center th_yura_green">
                    Opciones
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $usuario = $item->usuario;
                @endphp
                <tr onmouseover="$(this).addClass('bg-yura_dark')" onmouseleave="$(this).removeClass('bg-yura_dark')">
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->fecha_ingreso }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $usuario != '' ? $usuario->nombre_completo : '' }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->proveedor->nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->variedad->nombre }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->longitud }}cm
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->tallos_x_ramo }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->cantidad }}
                    </th>
                    <th class="padding_lateral_5"
                        style="border-color: #9d9d9d; background-color: #eeeeee; color: black">
                        {{ $item->disponibles }}
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d">
                        {{ $item->cantidad * $item->tallos_x_ramo }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Etiqueta"
                                onclick="imprimir_etiqueta('{{ $item->id_despacho_proveedor }}')">
                                <i class="fa fa-fw fa-file-pdf-o"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar"
                                onclick="delete_model('{{ $item->id_despacho_proveedor }}')">
                                <i class="fa fa-fw fa-trash"></i>
                            </button>
                        </div>
                    </th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function imprimir_etiqueta(id) {
        $.LoadingOverlay('show');
        window.open('{{ url('despacho_proveedor/imprimir_etiqueta') }}?id=' + id, '_blank');
        $.LoadingOverlay('hide');
    }

    function delete_model(id) {
        texto =
            "<div class='alert alert-warning text-center'>Esta seguro de <b>ELIMINAR</b> el despacho?</div>";

        modal_quest('modal_delete_model', texto, 'Eliminar despacho', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            };
            post_jquery_m('{{ url('despacho_proveedor/delete_model') }}', datos, function(retorno) {
                cerrar_modals();
                listar_reporte();
            });
        })
    }
</script>
