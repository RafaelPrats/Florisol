<option value="">Seleccione</option>
@foreach($actividades as $act)
    <option value="{{$act->id_actividad}}">{{$act->nombre}}</option>
@endforeach