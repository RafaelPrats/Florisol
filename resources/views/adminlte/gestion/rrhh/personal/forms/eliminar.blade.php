<form id="form_eliminar_persona">

    <div class="row align-self-center">
        <div class="col-md-2 align-self-center">
            <div class="form-group text-center">
                <label for="nombre">Nombre</label>
                <input autocomplete="off" disabled type="text" id="nombre" name="nombre"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->nombre}}">
            </div>
        </div>

        <div class="col-md-2 align-self-center">
            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input autocomplete="off" disabled type="text" id="apellido" name="apellido"
                       class="form-control input-yura_default" required maxlength="250"
                       value="{{$dataPersonal->apellido}}">
            </div>
        </div>

        <div class="col-md-2 align-self-center">
            <div class="form-group">
                <label for="cedula_identidad">Cédula de Identidad</label>
                <input disabled type="number" id="cedula_identidad" name="cedula_identidad"
                       class="form-control input-yura_default" required maxlength="250" autocomplete="off"
                       value="{{$dataPersonal->cedula_identidad}}">
            </div>
        </div>
    </div>

</form>
<div class="text-center">
    <button type="button" class="btn btn-yura_primary" onclick="eliminar_trabajador('{{$dataPersonal->id_personal}}')">
        <i class="fa fa-fw fa-trash"></i> Eliminar
    </button>
</div>
<input type="hidden" id="id_personal" value="{{$dataPersonal->id_personal}}">
    @foreach($detalles as $det)
<input type="hidden" id="id_personal_detalle" value="{{$det->id_personal_detalle}}">
@endforeach

<script>
 

    function eliminar_personal(id_personal) {
        modal_quest('modal_quest_del_documento', '<div class="alert alert-info text-center">¿Está seguro de eliminar esta información?</div>',
            '<i class="fa fa-fw fa-trash"></i> Eliminar información', true, false, '{{isPC() ? '35%' : ''}}', function () {
                $.LoadingOverlay('show');
                datos = {
                    _token: '{{csrf_token()}}',
                    id_personal: id_personal
                };
                post_jquery('{{url('personal/eliminar_trabajador')}}', datos, function () {
                    cerrar_modals();
                });
                $.LoadingOverlay('hide');
            });
    }


    
</script>



