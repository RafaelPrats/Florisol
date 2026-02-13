<div style="overflow-y: scroll; height: 450px">
    <select id="app_matriz_new" class="hidden">
        @foreach ($aplicaciones_matriz as $am)
            <option value="{{ $am->id_aplicacion_matriz }}">
                {{ $am->nombre }}
            </option>
        @endforeach
    </select>
    <table class="table-striped table-bordered"
        style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_aplicaciones">
        <thead>
            <tr id="tr_fija_top_0">
                <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0; width: 250px">
                    Nombre
                </th>
                <th class="text-center th_yura_green" style="width: 80px;">
                    Día Ini.
                </th>
                <th class="text-center th_yura_green" style="width: 60px;">
                    Sem. Ini.
                </th>
                <th class="text-center th_yura_green" title="Repeticiones" style="width: 60px;">
                    Rep.
                </th>
                <th class="text-center th_yura_green" style="width: 60px;">
                    Veces x Semana
                </th>
                <th class="text-center th_yura_green" style="width: 60px;" title="Frecuencia">
                    Frec.
                </th>
                <th class="text-center th_yura_green" style="width: 120px;">
                    P/S
                </th>
                @if ($tipo == 'S')
                    <th class="text-center th_yura_green" style="width: 80px;">
                        CC x Planta
                    </th>
                @endif
                <th class="text-center th_yura_green" style="width: 60px;" title="Continua">
                    Cont.
                </th>
                <th class="text-center th_yura_green" style="width: 80px;">
                    Tipo
                </th>
                <th class="text-center th_yura_green" style="width: 150px;">
                    Matriz
                </th>
                <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 30px;">
                    <div class="btn-group">
                        <button type="button" class="btn btn-xs btn-yura_default dropdown-toggle"
                            data-toggle="dropdown">
                            <i class="fa fa-fw fa-plus"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a class="dropdown-item" href="javascript:void(0)"
                                    onclick="add_aplicacion()">Aplicación</a></li>
                            {{-- <li><a class="dropdown-item" href="javascript:void(0)" onclick="add_matriz()">Labor Matriz</li> --}}
                        </ul>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listado as $item)
                @php
                    $clase_estado = $item->estado == 1 ? '' : 'error';
                @endphp
                <tr id="tr_app_{{ $item->id_aplicacion }}">
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="text" id="nombre_app_{{ $item->id_aplicacion }}" style="width: 100%"
                            class="text-center {{ $clase_estado }}" value="{{ $item->nombre }}" placeholder="Nombre">
                        <span class="hidden">{{ $item->nombre }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="dia_ini_app_{{ $item->id_aplicacion }}" style="width: 100%"
                            class="text-center mouse-hand {{ $clase_estado }}" value="{{ $item->dia_ini }}"
                            onkeyup="calcular_semana_ini('{{ $item->id_aplicacion }}')"
                            title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
                            ondblclick="parametrizar_app('dia_ini', '{{ $item->id_aplicacion }}')">
                        <span class="hidden">{{ $item->dia_ini }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="semana_ini_app_{{ $item->id_aplicacion }}" style="width: 100%"
                            class="text-center mouse-hand {{ $clase_estado }}" value="{{ $item->semana_ini }}"
                            onchange="$('#dia_ini_app_{{ $item->id_aplicacion }}').val(0)"
                            title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
                            ondblclick="parametrizar_app('semana_ini', '{{ $item->id_aplicacion }}')">
                        <span class="hidden">{{ $item->semana_ini }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="repeticiones_app_{{ $item->id_aplicacion }}" style="width: 100%;"
                            class="text-center mouse-hand {{ $clase_estado }}" value="{{ $item->repeticiones }}"
                            title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
                            min="1" ondblclick="parametrizar_app('repeticiones', '{{ $item->id_aplicacion }}')">
                        <span class="hidden">{{ $item->repeticiones }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="veces_x_semana_app_{{ $item->id_aplicacion }}" style="width: 100%"
                            class="text-center mouse-hand {{ $clase_estado }}" value="{{ $item->veces_x_semana }}"
                            title="Doble click para parametrizar" data-placement="bottom" data-toggle="tooltip"
                            min="1"
                            ondblclick="parametrizar_app('veces_x_semana', '{{ $item->id_aplicacion }}')">
                        <span class="hidden">{{ $item->veces_x_semana }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <input type="number" id="frecuencia_app_{{ $item->id_aplicacion }}" style="width: 100%"
                            class="text-center {{ $clase_estado }}" min="0" value="{{ $item->frecuencia }}">
                        <span class="hidden">{{ $item->frecuencia }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select class="{{ $clase_estado }}" id="poda_siembra_app_{{ $item->id_aplicacion }}"
                            style="width: 100%">
                            <option value="T" {{ $item->poda_siembra == 'T' ? 'selected' : '' }}>Podas y Siembras
                            </option>
                            <option value="P" {{ $item->poda_siembra == 'P' ? 'selected' : '' }}>Podas</option>
                            <option value="S" {{ $item->poda_siembra == 'S' ? 'selected' : '' }}>Siembras
                            </option>
                        </select>
                        <span class="hidden">{{ $item->poda_siembra }}</span>
                    </td>
                    @if ($tipo == 'S')
                        <td class="text-center" style="border-color: #9d9d9d">
                            <input type="number" id="litro_x_cama_app_{{ $item->id_aplicacion }}"
                                style="width: 100%" class="text-center {{ $clase_estado }}" min="1"
                                value="{{ $item->litro_x_cama }}">
                            <span class="hidden">{{ $item->litro_x_cama }}</span>
                        </td>
                    @endif
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select class="{{ $clase_estado }}" id="continua_app_{{ $item->id_aplicacion }}"
                            style="width: 100%">
                            <option value="0" {{ $item->continua == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $item->continua == '1' ? 'selected' : '' }}>Sí</option>
                        </select>
                        <span class="hidden">{{ $item->continua }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select class="{{ $clase_estado }}" id="tipo_app_{{ $item->id_aplicacion }}"
                            style="width: 100%">
                            <option value="S" {{ $item->tipo == 'S' ? 'selected' : '' }}>Sanidad</option>
                            <option value="C" {{ $item->tipo == 'C' ? 'selected' : '' }}>Cultural</option>
                        </select>
                        <span class="hidden">{{ $item->getTipo() }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <select class="{{ $clase_estado }}" id="app_matriz_{{ $item->id_aplicacion }}"
                            style="width: 100%">
                            <option value="">Seleccione...</option>
                            @php
                                $getAppMatriz = '';
                            @endphp
                            @foreach ($aplicaciones_matriz as $am)
                                <option value="{{ $am->id_aplicacion_matriz }}"
                                    {{ $am->id_aplicacion_matriz == $item->id_aplicacion_matriz ? 'selected' : '' }}>
                                    {{ $am->nombre }}
                                </option>
                                @php
                                    if ($am->id_aplicacion_matriz == $item->id_aplicacion_matriz) {
                                        $getAppMatriz = $am->nombre;
                                    }
                                @endphp
                            @endforeach
                        </select>
                        <span class="hidden">{{ $getAppMatriz }}</span>
                    </td>
                    <td class="text-center" style="border-color: #9d9d9d">
                        <div class="btn-group">
                            <button type="button" class="btn btn-xs btn-yura_primary dropdown-toggle"
                                data-toggle="dropdown">
                                <i class="fa fa-fw fa-gears"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @if ($item->estado == 1)
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)"
                                            onclick="update_app('{{ $item->id_aplicacion }}')">
                                            <i class="fa fa-fw fa-edit"></i> Editar Aplicación
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)"
                                            onclick="mezclas_app('{{ $item->id_aplicacion_matriz }}')">
                                            <i class="fa fa-fw fa-flask"></i> Mezclas de la Aplicación
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)"
                                            onclick="variedades_app('{{ $item->id_aplicacion }}')">
                                            <i class="fa fa-fw fa-leaf"></i> Variedades de la Aplicación
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="desactivar_app('{{ $item->id_aplicacion }}', '{{ $item->estado }}')">
                                        <i class="fa fa-fw fa-{{ $item->estado == 1 ? 'unlock' : 'lock' }}"></i>
                                        {{ $item->estado == 1 ? 'Desactivar' : 'Activar' }} Aplicación
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot id="table_aplicaciones_tfoot">

        </tfoot>
    </table>
</div>
<style>
    #tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function add_matriz() {
        get_jquery('{{ url('aplicaciones_campo/add_matriz') }}', {}, function(retorno) {
            modal_view('modal-view_add_matriz', retorno, '<i class="fa fa-fw fa-plus"></i> Labores matriz',
                true, false, '50%');
        });
    }
</script>
