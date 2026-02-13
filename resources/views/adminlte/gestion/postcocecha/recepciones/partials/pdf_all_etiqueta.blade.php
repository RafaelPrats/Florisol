@foreach ($datos['listado'] as $pos => $model)
    @for ($i = 0; $i < $model->cantidad_mallas; $i++)
        <table
            style="position: relative; top: -20px; left: 0px; font-family: Arial, Helvetica, sans-serif; max-width: 100%">
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0; " colspan="3">
                    {{ $model->variedad->nombre }}
                </th>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0;" colspan="2">
                    {!! $barCode->getBarcode($model->id_desglose_recepcion, $barCode::TYPE_CODE_128, 2) !!}
                </th>
                <th style="text-align: left;">
                    <b>{{ $model->longitud }}</b><small><sup>cm</sup></small>
                    <br>
                    {{ $model->tallos_x_malla }}<small><sup>tallos</sup></small>
                </th>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: left; font-size: 0.7em; padding: 0">
                    {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($model->fecha)))] }}{{ intVal(substr($model->fecha, 5, 2)) }}.{{ substr($model->fecha, 8, 2) }}
                </th>
                <th style="text-align: center; font-size: 10px; padding: 0" colspan="2">
                    PRODUCT of ECUADOR
                </th>
            </tr>
        </table>

        @if ($i < $model->cantidad_mallas)
            <div style="page-break-after:always;"></div>
        @endif
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
