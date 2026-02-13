<legend>Nombre: <strong>{{$dataPersonal->nombre.' '.$dataPersonal->apellido}}</strong></legend>

<table class="table table-responsive table-bordered"
       style="width: 100%; border: 2px solid #9d9d9d; border-radius: 18px 18px 0 0" id="table_content_personal">
    <tr>
        <th class="text-center th_yura_green" style="border-color: white;">Fecha de Ingreso</th>
        <th class="text-center th_yura_green" style="border-color: white;">Fecha de Egreso</th>
        <th class="text-center th_yura_green" style="border-color: white;">Causa de Desvinculacion</th>
        <th class="text-center th_yura_green" style="border-color: white;">Sueldo</th>
    </tr>
    @foreach($detalles as $det)
        <tr class="{{$det->estado == 0 ? 'error' : ''}}">
            <td class="text-center" style="border-color: #9d9d9d">{{$det->fecha_ingreso}}</td>
            <td class="text-center" style="border-color: #9d9d9d">{{$det->fecha_desvinculacion}}</td>
            <td class="text-center"
                style="border-color: #9d9d9d">{{$det->id_causa_desvinculacion != '' ? $det->causa_desvinculacion->nombre : ''}}</td>
            <td class="text-center" style="border-color: #9d9d9d">{{$det->sueldo}}</td>
        </tr>
    @endforeach
</table>
<input type="hidden" id="id_personal" value="{{$dataPersonal->id_personal}}">


