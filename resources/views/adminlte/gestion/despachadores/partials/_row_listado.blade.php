<td class="text-center" style="border-color: #9d9d9d">
    <input type="text" id="nombre_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center"
           value="{{$app->nombre}}" placeholder="Nombre">
    <span class="hidden">{{$app->nombre}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <input type="number" id="dia_ini_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center mouse-hand"
           value="{{$app->dia_ini}}" onkeyup="calcular_semana_ini('{{$app->id_aplicacion}}')" title="Doble click para parametrizar"
           data-placement="bottom" data-toggle="tooltip" ondblclick="parametrizar_app('dia_ini', '{{$app->id_aplicacion}}')">
    <span class="hidden">{{$app->dia_ini}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <input type="number" id="semana_ini_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center mouse-hand"
           value="{{$app->semana_ini}}" onchange="$('#dia_ini_app_{{$app->id_aplicacion}}').val(0)" title="Doble click para parametrizar"
           data-placement="bottom" data-toggle="tooltip" ondblclick="parametrizar_app('semana_ini', '{{$app->id_aplicacion}}')">
    <span class="hidden">{{$app->semana_ini}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <input type="number" id="repeticiones_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center mouse-hand" min="1"
           value="{{$app->repeticiones}}" title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
           ondblclick="parametrizar_app('repeticiones', '{{$app->id_aplicacion}}')">
    <span class="hidden">{{$app->repeticiones}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <input type="number" id="veces_x_semana_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center mouse-hand" min="1"
           value="{{$app->veces_x_semana}}" title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
           ondblclick="parametrizar_app('veces_x_semana', '{{$app->id_aplicacion}}')">
    <span class="hidden">{{$app->veces_x_semana}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <input type="number" id="frecuencia_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center" min="0"
           value="{{$app->frecuencia}}">
    <span class="hidden">{{$app->frecuencia}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <select id="poda_siembra_app_{{$app->id_aplicacion}}" style="width: 100%">
        <option value="T" {{$app->poda_siembra == 'T' ? 'selected' : ''}}>Podas y Siembras</option>
        <option value="P" {{$app->poda_siembra == 'P' ? 'selected' : ''}}>Podas</option>
        <option value="S" {{$app->poda_siembra == 'S' ? 'selected' : ''}}>Siembras</option>
    </select>
    <span class="hidden">{{$app->poda_siembra}}</span>
</td>
@if($app->tipo == 'S')
    <td class="text-center" style="border-color: #9d9d9d">
        <input type="number" id="litro_x_cama_app_{{$app->id_aplicacion}}" style="width: 100%" class="text-center" min="1"
               value="{{$app->litro_x_cama}}">
        <span class="hidden">{{$app->litro_x_cama}}</span>
    </td>
@endif
<td class="text-center" style="border-color: #9d9d9d">
    <select id="continua_app_{{$app->id_aplicacion}}" style="width: 100%">
        <option value="0" {{$app->continua == '0' ? 'selected' : ''}}>No</option>
        <option value="1" {{$app->continua == '1' ? 'selected' : ''}}>Sí</option>
    </select>
    <span class="hidden">{{$app->continua}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <select id="tipo_app_{{$app->id_aplicacion}}" style="width: 100%">
        <option value="S" {{$app->tipo == 'S' ? 'selected' : ''}}>Sanidad</option>
        <option value="C" {{$app->tipo == 'C' ? 'selected' : ''}}>Cultural</option>
    </select>
    <span class="hidden">{{$app->getTipo()}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <select id="app_matriz_{{$app->id_aplicacion}}" style="width: 100%">
        <option value="">Seleccione...</option>
        @php
            $getAppMatriz = '';
        @endphp
        @foreach($aplicaciones_matriz as $am)
            <option value="{{$am->id_aplicacion_matriz}}"
                    {{$am->id_aplicacion_matriz == $app->id_aplicacion_matriz ? 'selected' : ''}}>
                {{$am->nombre}}
            </option>
            @php
                if($am->id_aplicacion_matriz == $app->id_aplicacion_matriz)
                    $getAppMatriz = $am->nombre;
            @endphp
        @endforeach
    </select>
    <span class="hidden">{{$getAppMatriz}}</span>
</td>
<td class="text-center" style="border-color: #9d9d9d">
    <div class="btn-group">
        <button type="button" class="btn btn-yura_primary btn-xs" onclick="update_app('{{$app->id_aplicacion}}')"
                title="Editar Aplicación">
            <i class="fa fa-fw fa-edit"></i>
        </button>
        <button type="button" class="btn btn-yura_default btn-xs" onclick="detalles_app('{{$app->id_aplicacion}}')"
                title="Detalles de la Aplicación">
            <i class="fa fa-fw fa-list-alt"></i>
        </button>
        <button type="button" class="btn btn-yura_default btn-xs" onclick="variedades_app('{{$app->id_aplicacion}}')"
                title="Variedades de la Aplicación">
            <i class="fa fa-fw fa-leaf"></i>
        </button>
        <button type="button" class="btn btn-yura_danger btn-xs" onclick="desactivar_app('{{$app->id_aplicacion}}')"
                title="{{$app->estado == 1 ? 'Desactivar' : 'Activar'}} Aplicación">
            <i class="fa fa-fw fa-{{$app->estado == 1 ? 'unlock' : 'lock'}}"></i>
        </button>
    </div>
</td>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>