<p class="text-center">
    <strong>Cosecha del día</strong>
    <button type="button" class="pull-right btn btn-xs btn-default" onclick="actualizar_cosecha_x_variedad()" id="btn_actualizar">
        <i class="fa fa-fw fa-refresh"></i>
    </button>
</p>
@if(count($listado_variedades) > 0)
    <div style="max-height: 250px; overflow-y: scroll">
        <table class="table-striped table-bordered" style="width: 100%; border: 1px solid #9d9d9d">
            @php
                $total_tallos = 0;
            @endphp
            @foreach($listado_variedades as $item)
                @php
                    $total_tallos += $item->tallos;
                @endphp
                <tr>
                    <th style="padding-left: 5px; border-color: #9d9d9d">
                        {{$item->nombre}}
                    </th>
                    <th class="text-right" style="padding-right: 5px; border-color: #9d9d9d">
                        {{number_format($item->tallos)}}
                    </th>
                </tr>
            @endforeach
        </table>
    </div>
    <div class="text-center">
        <h5 title="Cosechados" style="font-weight: bold">
            {{number_format($total_tallos)}}
        </h5>
        <button type="button" class="btn btn-link btn-xs" title="Ver Rendimiento en Cosecha"
                onclick="ver_rendimiento_cosecha('{{$cosecha != '' ? $cosecha->id_cosecha : ''}}')">
            <strong class="description-text">Totales</strong>
        </button>
    </div>
@else
    <div class="alert alert-info text-center">No se han encontrado resultados</div>
@endif