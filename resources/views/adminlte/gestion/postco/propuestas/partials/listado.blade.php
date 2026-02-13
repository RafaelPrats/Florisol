<div style="overflow-y: scroll; overflow-x: scroll; max-height: 700px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d" id="table_listado">
        <thead>
            <tr class="tr_fija_top_0">
                <th class="text-center th_yura_green" style="width: 60px">
                    CODIGO
                </th>
                <th class="text-center th_yura_green">
                    FECHA
                </th>
                <th class="text-center th_yura_green">
                    IMAGEN
                </th>
                <th class="text-center th_yura_green">
                    <div style="min-width: 190px">
                        PLANTA
                    </div>
                </th>
                <th class="text-center th_yura_green">
                    <div style="min-width: 250px">
                        VARIEDAD
                    </div>
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    UNIDADES
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    PRECIO
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    TOTAL TALLOS
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    PRECIO RAMO
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    MO
                </th>
                <th class="text-center th_yura_green" style="width: 70px">
                    LONGITUD
                </th>
                <th class="text-center th_yura_green">
                    COLORES
                </th>
                <th class="text-center th_yura_green">
                    SEASON
                </th>
                <th class="text-center th_yura_green">
                    CLIENTE
                </th>
                <th class="text-center th_yura_green">
                    CAJA
                </th>
                <th class="text-center th_yura_green" style="width: 60px">
                    PACKING
                </th>
                <th class="text-center th_yura_green" style="width: 90px">
                    OPCIONES
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $detalles = $item->detalles;
                @endphp
                <tr>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->nombre }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ convertDateToText($item->fecha) }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <img src="{{ asset('images/propuesta/' . $item->imagen) }}" alt=""
                            class="sombra_pequeña mouse-hand imagen_listado"
                            onclick="abrirGaleria('{{ $item->id_propuesta }}')"
                            style="width: 150px; border: 1px dotted #ccc; border-radius: 14px">
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($detalles as $det)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $det->variedad->planta->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($detalles as $det)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $det->variedad->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5 text-right" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($detalles as $det)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $det->unidades }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5 text-right" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($detalles as $det)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                ${{ $det->precio }}
                            </p>
                        @endforeach
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->getTotalTallos() }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        ${{ round($item->getPrecio(), 2) }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        ${{ $item->costo_mano_obra }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->longitud }}cm
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($item->colores as $col)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $col->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($item->seasons as $col)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $col->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($item->clientes as $col)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $col->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="padding_lateral_5" style="border-color: #9d9d9d; vertical-align: top">
                        @foreach ($item->cajas as $col)
                            <p style="padding: 0px; margin: 0px; border-bottom: 1px solid #9d9d9d">
                                {{ $col->nombre }}
                            </p>
                        @endforeach
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        {{ $item->packing }}
                    </th>
                    <th class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_default" title="Ver Ramo"
                                onclick="editar_propuesta('{{ $item->id_propuesta }}')">
                                <i class="fa fa-fw fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-yura_danger" title="Eliminar Ramo"
                                onclick="delete_propuesta('{{ $item->id_propuesta }}')">
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
    estructura_tabla('table_listado');

    function delete_propuesta(id) {
        texto =
            "<div class='alert alert-warning text-center'>¿Esta seguro de <b>ELIMINAR</b> este ramo?</div>";

        modal_quest('modal_delete_propuesta', texto, 'Eliminar propuesta', true, false, '40%', function() {
            datos = {
                _token: '{{ csrf_token() }}',
                id: id,
            }
            post_jquery_m('{{ url('propuestas/delete_propuesta') }}', datos, function() {
                listar_reporte();
            });
        })
    }

    function editar_propuesta(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('propuestas/editar_propuesta') }}', datos, function(retorno) {
            modal_view('modal_editar_propuesta', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Prouesta',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }

    function abrirGaleria(id) {
        datos = {
            id: id
        }
        get_jquery('{{ url('propuestas/abrirGaleria') }}', datos, function(retorno) {
            modal_view('modal_abrirGaleria', retorno, '<i class="fa fa-fw fa-eye"></i> Imagen',
                true, false, '{{ isPC() ? '95%' : '' }}',
                function() {});
        })
    }
</script>
