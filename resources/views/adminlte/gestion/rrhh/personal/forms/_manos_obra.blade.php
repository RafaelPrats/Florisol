<option value="">Seleccione</option>
@foreach($manos_obra as $mo)
    <option value="{{$mo->id_mano_obra}}">{{$mo->mano_obra->nombre}}</option>
@endforeach