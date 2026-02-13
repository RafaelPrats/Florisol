<div style="position: relative; top: 0px; left: 0px; width: 100%">
    <table class="text-center" style="width: 100%; font-size: 0.7em">
        <tr>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                OT
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                PEDIDO
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                CLIENTE
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                FECHA
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                RECETA
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                MEDIDA
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                RAMOS
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                VARIEDAD
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                TALLOS
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                UNIDADES
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                RESPONSABLE
            </th>
        </tr>
        @php
            $orden_trabajo = $datos['model'];
            $despachador = $orden_trabajo->despachador;
            $detalle_pedido = $orden_trabajo->detalle_import_pedido;
            $pedido = $detalle_pedido->pedido;
            $total_tallos = 0;
            $total_tallos_ramo = 0;
            foreach ($orden_trabajo->detalles as $det) {
                $total_tallos += $det->tallos;
                $total_tallos_ramo += $det->tallos / $orden_trabajo->ramos;
            }
        @endphp
        @foreach ($orden_trabajo->detalles as $pos_d => $det)
            <tr>
                @if ($pos_d == 0)
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        #{{ $orden_trabajo->id_orden_trabajo }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        #{{ $pedido->codigo }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $pedido->cliente->detalle()->nombre }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ convertDateToText($pedido->fecha) }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $detalle_pedido->variedad->nombre }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $orden_trabajo->longitud }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $orden_trabajo->ramos }}
                    </td>
                @endif
                <td style="text-align: center" class="border-1px">
                    {{ $det->variedad->nombre }}
                </td>
                <td style="text-align: center" class="border-1px">
                    {{ $det->tallos }}
                </td>
                <td style="text-align: center" class="border-1px">
                    {{ $det->tallos / $orden_trabajo->ramos }}
                </td>
                @if ($pos_d == 0)
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $despachador != '' ? $despachador->nombre : '' }}
                    </td>
                @endif
            </tr>
        @endforeach
        <tr>
            <th style="vertical-align: top; text-align: center" class="border-1px" colspan="8">
                TOTAL
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                {{ $total_tallos }}
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                {{ $total_tallos_ramo }}
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
            </th>
        </tr>
    </table>
</div>

<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
    }

    .border-1px {
        border: 1px solid black;
    }

    .text-center {
        text-align: center;
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
    }

    td,
    th {
        padding: 0;
        margin: 0;
    }
</style>
