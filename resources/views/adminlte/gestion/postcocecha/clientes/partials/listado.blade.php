<div id="table_clientes" style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    @if (sizeof($listado) > 0)
        <table width="100%" class="table-responsive table-bordered" style="border-color: #9d9d9d"
            id="table_content_clientes">
            <thead>
                <tr class="tr_fija_top_0">
                    <th class="text-center th_yura_green">
                        NOMBRE COMPLETO
                    </th>
                    <th class="text-center th_yura_green">
                        IDENTIFICACION
                    </th>
                    <th class="text-center th_yura_green">
                        SEGMENTO
                    </th>
                    <th class="text-center th_yura_green" style="width: 90px">
                        OPCIONES
                    </th>
                </tr>
            </thead>
            @foreach ($listado as $item)
                <tr onmouseover="$(this).css('background-color','#add8e6')"
                    onmouseleave="$(this).css('background-color','')" class="{{ $item->estado == 1 ? '' : 'error' }}"
                    id="row_clientes_{{ $item->id_cliente }}">
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->nombre }}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->ruc }}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        {{ $item->segmento }}
                    </td>
                    <td style="border-color: #9d9d9d" class="text-center">
                        <a href="javascript:void(0)" class="btn btn-yura_default btn-xs" title="Ver detalles"
                            onclick="detalles_cliente('{{ $item->id_cliente }}')"
                            id="btn_view_usuario_{{ $item->id_cliente }}">
                            <i class="fa fa-fw fa-eye" style="color: black"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
        <div id="pagination_listado_clientes">
            {!! str_replace('/?', '?', $listado->render()) !!}
        </div>
    @else
        <div class="alert alert-info text-center">No se han encontrado coincidencias</div>
    @endif
</div>
