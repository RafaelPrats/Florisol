<div style="position: relative; top: 0px; left: 0px; width: 100%">
    <table class="text-center" style="width: 100%; font-size: 0.7em">
        <tr>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                OT
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
            <th style="vertical-align: top; text-align: center" class="border-1px">
                OBSERVACION
            </th>
        </tr>
        @php
            $orden_trabajo = $datos['model'];
            $despachador = $orden_trabajo->despachador;
            $postco = $orden_trabajo->postco;
            $cliente = $orden_trabajo->cliente;
            $total_tallos = 0;
            $total_tallos_ramo = 0;
            foreach ($orden_trabajo->detalles as $det) {
                $total_tallos += $det->unidades * $orden_trabajo->ramos;
                $total_tallos_ramo += $det->unidades;
            }
        @endphp
        @foreach ($orden_trabajo->detalles as $pos_d => $det)
            <tr>
                @if ($pos_d == 0)
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        #{{ $orden_trabajo->id_orden_trabajo }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $cliente->detalle()->nombre }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ convertDateToText($postco->fecha) }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $postco->variedad->nombre }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $orden_trabajo->longitud }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $orden_trabajo->ramos }}
                    </td>
                @endif
                <td style="text-align: center" class="border-1px">
                    {{ $det->item->nombre }}
                </td>
                <td style="text-align: center" class="border-1px">
                    {{ $det->unidades * $orden_trabajo->ramos }}
                </td>
                <td style="text-align: center" class="border-1px">
                    {{ $det->unidades }}
                </td>
                @if ($pos_d == 0)
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $despachador != '' ? $despachador->nombre : '' }}
                    </td>
                    <td style="text-align: center" class="border-1px" rowspan="{{ count($orden_trabajo->detalles) }}">
                        {{ $orden_trabajo->observacion }}
                    </td>
                @endif
            </tr>
        @endforeach
        <tr>
            <th style="vertical-align: top; text-align: center" class="border-1px" colspan="7">
                TOTAL
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                {{ $total_tallos }}
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px">
                {{ $total_tallos_ramo }}
            </th>
            <th style="vertical-align: top; text-align: center" class="border-1px"  colspan="2">
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
