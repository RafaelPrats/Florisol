<table class="table table-sm table-bordered">
    <thead>
        <tr class="bg-success">
            <th style="vertical-align: middle">VARIEDAD</th>
            <th class="text-center" style="vertical-align: middle">CANTIDAD</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($invFrio as $x => $inv)
            <tr>
                <td style="vertical-align: middle;cursor:pointer" onclick="ver_inventario_veriedad('{{$x}}','{{$inv->id_planta}}',this)">
                    <b>  
                        <i class="fa fa-leaf" style="margin-right: 5px"></i> {{$inv->planta}}  <i class="fa fa-fw fa-caret-down pull-right" id="icon_planta_{{$x}}"></i>
                    </b>
                </td>
                <td style="vertical-align: middle;text-align:center"><b>{{$inv->disponibles}}</b></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="alert alert-info" style="vertical-align: middle;text-align:center">
                    No existe inventario disponible
                </td>
            </tr>
        @endforelse
    </tbody>
</table>