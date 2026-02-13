<div class="row">
    @if(!$asignacionMasivaHoras)
        <div class="col-md-3 col-xs-6">
            <div class="form-group input-group">
                <span class="input-group-addon bg-yura_dark">Desde</span>
                <input type="time" class="form-control" id="desde_masivo"
                        onkeyup="set_horario_personal('desde',this)"
                        onchange="set_horario_personal('desde',this)">
            </div>
        </div>
        <div class="col-md-3 col-xs-6">
            <div class="form-group input-group">
                <span class="input-group-addon bg-yura_dark">Hasta</span>
                <input type="time" class="form-control" id="hasta_masivo"
                        onkeyup="set_horario_personal('hasta',this)"
                        onchange="set_horario_personal('hasta',this)">
            </div>
        </div>
    @endif
    <div class="col-md-3 col-xs-6">
        <button class="btn btn-md th_yura_green" onclick="store_control_asistencia()">
            <i class="fa fa-floppy-o"></i>
            Guardar asistencia
        </button>

    </div>
    <div class="col-md-3 col-xs-6 col-sm-6" id="div_iniciar_captura">
        <button class="btn btn-md th_yura_green" onclick="modal_camara()">
            <i class="fa fa-camera"></i>
            Abrir camara
        </button>
        
    </div>
    {{-- <div class="col-md-3 col-xs-6 hide" id="div_fin_captura">
        <button class="btn btn-md btn-warning" onclick="cerrar_camara()">
            <i class="fa fa-camera"></i>
            Cerrar camara
        </button>
    </div> --}}
    
</div>
<table width="100%" class="table-responsive table-bordered" id="tabla_control_personal" style="font-size: 0.8em; border-color: #9d9d9d">
    <thead>
        <tr id="th_fija_top_0">
            @if(!$asignacionMasivaHoras)
                <th class="text-center th_yura_green">
                    <input type="checkbox" name="seleccionar_todo_personal" onchange="seleccionar_todo_personal(this)" checked>
                </th>
            @endif
            <th class="text-center th_yura_green">PERSONAL</th>
            <th class="text-center th_yura_green">IDENTIFICACIÓN</th>
            <th class="text-center th_yura_green">DESDE</th>
            <th class="text-center th_yura_green">HASTA</th>
            <th class="text-center th_yura_green">LABOR</th>
            <th class="text-center th_yura_green">OPCIONES</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($personal as $p)
            <tr id="row_planta_4" style="">
                <input type="hidden" class="id_personal_detalle" value="{{ $p->id_personal_detalle }}">
                <input type="hidden" class="input_control_personal" value="{{ $p->id_control_personal }}">
                @if(!$asignacionMasivaHoras)
                    <td style="border-color: #9d9d9d" class="text-center">
                        <input type="checkbox" class="check_select_personal" checked>
                    </td>
                @endif
                <td style="border-color: #9d9d9d" class="text-center">{{$p->nombre}} {{$p->apellido}}</td>
                <td style="border-color: #9d9d9d" class="text-center">{{$p->cedula_identidad}}</td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="time" class="w-100 input-date-cd"
                            id="cp-{{$p->id_personal_detalle}}" value="{{$p->desde}}">
                </td>
                <td style="border-color: #9d9d9d" class="text-center">
                    <input type="time" class="w-100 input-date-ch"
                            id="cp-{{$p->id_personal_detalle}}" value="{{$p->hasta}}">
                </td>
                <td  style="border-color: #9d9d9d" class="text-center">
                    <select class="w-100 id_mano_obra" style="height: 22px;">
                        <option value="">Seleccione</option>
                        @foreach ($ManoObras as $mo)
                            <option value="{{$mo->id_mano_obra}}"
                                {{$mo->id_mano_obra === $p->id_mano_obra ? 'selected' : ''}}>{{$mo->nombre}}</option>
                        @endforeach
                    </select>
                </td>
                <td  style="border-color: #9d9d9d" class="text-center">
                    @isset($p->id_control_personal)
                        <button class="btn btn-xs btn-danger rounded-4" style="border-radius:18px"
                                title="Eliminar asistencia" onclick="delete_asistencia('{{$p->id_control_personal}}')">
                            <i class="fa fa-trash"></i>
                        </button>
                    @endisset
                </td>
            </tr>
        @empty
            <div  class="alert alert-info text-center">
                No se encontraron personal con los filtros seleccionados
            </div>
        @endforelse
    </tbody>
</table>


<style>

    .btn-camera{
        position: absolute;
        left: 28%;
        bottom: 26px;
        padding: 16px;
        border-radius: 100%;
        background: white;
        width: 55px;
        opacity: .8;
        border: none;
    }

    .btn-camera-flip{
        position: absolute;
        left: 66%;
        bottom: 26px;
        padding: 16px;
        border-radius: 100%;
        background: white;
        width: 55px;
        opacity: .8;
        border: none;
    }

</style>