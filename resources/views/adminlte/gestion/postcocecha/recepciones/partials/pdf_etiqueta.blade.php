@if ($datos['model'] != '')
    @for ($i = 0; $i < $datos['model']->cantidad_mallas; $i++)
        <table
            style="position: relative; top: -40px; left: 0px; font-family: Arial, Helvetica, sans-serif; max-width: 100%">
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0; font-size: 0.85em" colspan="3">
                    {{ $datos['model']->variedad->nombre }}
                </th>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: center; padding: 0;" colspan="2">
                    {!! $barCode->getBarcode(
                        str_pad($datos['model']->id_desglose_recepcion, 24, '0', STR_PAD_LEFT),
                        $barCode::TYPE_CODE_128,
                        1,
                    ) !!}
                </th>
                <th style="text-align: left;">
                    <b>{{ $datos['model']->longitud }}</b><small><sup>cm</sup></small>
                    <br>
                    {{ $datos['model']->tallos_x_malla }}<small><sup>tallos</sup></small>
                </th>
            </tr>
            <tr style="padding: 0">
                <th style="text-align: left; font-size: 0.7em; padding: 0">
                    {{ getDias(TP_LETRA)[transformDiaPhp(date('w', strtotime($datos['model']->fecha)))] }}{{ intVal(substr($datos['model']->fecha, 5, 2)) }}.{{ substr($datos['model']->fecha, 8, 2) }}
                </th>
                <th style="text-align: center; font-size: 10px; padding: 0" colspan="2">
                    PRODUCT of ECUADOR
                </th>
            </tr>
            {{-- 
            <tr>
                <th style="text-align: center; font-size: 15px; padding: 0" colspan="3">
                    <br> ----------------------------
                    <br>
                    {{ $datos['model']->id_desglose_recepcion }}
                    <br>
                    {{ str_pad($datos['model']->id_desglose_recepcion, 24, '0', STR_PAD_LEFT) }}
                    <br>
                    @php
                        $bin = str_pad(decbin($datos['model']->id_desglose_recepcion), 24, '0', STR_PAD_LEFT);
                    @endphp
                    {{ $bin }}
                </th>
            </tr>
            --}}
        </table>

        @if ($i < $datos['model']->cantidad_mallas - 1)
            <div style="page-break-after:always;"></div>
        @endif
    @endfor
@else
    NO EXISTE EL REGISTRO
@endif

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
