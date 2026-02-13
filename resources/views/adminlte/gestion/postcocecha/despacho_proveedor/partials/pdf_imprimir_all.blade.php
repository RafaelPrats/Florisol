@foreach ($listado as $pos => $item)
    @for ($i = 1; $i <= $item['cantidad']; $i++)
        <table
            style="position: relative; top: -40px; left: 10px; font-family: Arial, Helvetica, sans-serif; width: 230px;">
            <tr style="padding: 0">
                <th style="text-align: left; padding: 0; font-size: 0.9em">
                    {{ $item['model']->variedad->nombre }}
                </th>
                <td style="text-align: left; padding: 0;" rowspan="2">
                    {{ $item['model']->tallos_x_ramo }}<small style="font-size: 0.7em">tallos</small>
                    <br>
                    {{ $item['model']->longitud }}cm
                    <br>
                    <b style="font-size: 0.7em">
                        {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($item['model']->fecha_ingreso)))] }}{{ intVal(substr($item['model']->fecha_ingreso, 5, 2)) }}.{{ substr($item['model']->fecha_ingreso, 8, 2) }}
                    </b>
                </td>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0;">
                    {!! $barCode->getBarcode($item['model']->id_despacho_proveedor, $barCode::TYPE_CODE_128, 2) !!}
                </th>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0; font-size: 0.8em">
                    @if ($item['model']->id_proveedor > 0)
                        {{ $item['model']->proveedor->nombre }}
                    @endif
                </th>
            </tr>
        </table>

        <div style="page-break-after:always;"></div>
    @endfor
@endforeach

<style>
    div.bar div {
        margin: 0 auto;
    }

    .border {
        border: 1px solid;
    }

    table {
        border-collapse: collapse
    }
</style>
