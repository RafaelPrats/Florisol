<div class="dropdown pull-left">
    <button class="btn btn-xs btn-yura_default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-fw fa-gears"></i>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        @foreach($fechas as $f)
            <li>
                <a href="javascript:void(0)" onclick="actualizar_fecha('{{$d['variedad']->id_variedad}}', '{{$f->fecha}}')">
                    Actualizar el día: "{{$f->fecha}}"
                </a>
            </li>
        @endforeach
        @if(count($fechas) > 0)
            <li role="separator" class="divider"></li>
            <li>
                <a href="javascript:void(0)" title="Puede causar problemas de rendimiento por tiempo de demora"
                   onclick="actualizar_fecha('{{$d['variedad']->id_variedad}}', '{{$fechas[0]->fecha}}', '{{$fechas[count($fechas) - 1]->fecha}}')">
                    Actualizar todos los dias
                    <i class="fa fa-fw fa-exclamation-triangle error"></i>
                </a>
            </li>
        @endif
    </ul>
</div>