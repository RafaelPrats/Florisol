<div style="width: 100%; overflow-y: scroll; max-height: 450px">
    <table style="width: 100%; border: 1px solid #9d9d9d" class="table-bordered table-striped">
        <tr>
            <th class="text-center th_yura_green" colspan="4">
                Reportes disponibles
            </th>
        </tr>
        @foreach($reportes as $r)
            <tr id="tr_reporte_{{$r->id_envio_reporte}}" class="tr_reporte">
                <th class="text-left" style="border-color: #9d9d9d; padding-left: 10px">
                    {{$r->nombre_reporte}}
                </th>
                <th class="text-center" style="border-color: #9d9d9d">
                    <select id="dia_semana_{{$r->id_envio_reporte}}" style="width: 100%; color: black !important;">
                        <option value="1" {{$r->dia_semana == 1 ? 'selected' : ''}}>Lunes</option>
                        <option value="2" {{$r->dia_semana == 2 ? 'selected' : ''}}>Martes</option>
                        <option value="3" {{$r->dia_semana == 3 ? 'selected' : ''}}>Miércoles</option>
                        <option value="4" {{$r->dia_semana == 4 ? 'selected' : ''}}>Jueves</option>
                        <option value="5" {{$r->dia_semana == 5 ? 'selected' : ''}}>Viernes</option>
                        <option value="6" {{$r->dia_semana == 6 ? 'selected' : ''}}>Sábado</option>
                        <option value="0" {{$r->dia_semana == 0 ? 'selected' : ''}}>Domingo</option>
                    </select>
                </th>
                <th class="text-center" style="border-color: #9d9d9d;">
                    <input type="time" id="hora_reporte_{{$r->id_envio_reporte}}" value="{{$r->hora}}" required
                           style="width: 100%; color: black !important;">
                </th>
                <th class="text-center" style="border-color: #9d9d9d; padding-left: 10px">
                    <button type="button" class="btn btn-xs btn-yura_primary" onclick="seleccionar_reporte({{$r->id_envio_reporte}})">
                        <i class="fa fa-fw fa-arrow-right"></i>
                    </button>
                </th>
            </tr>
        @endforeach
    </table>
</div>
