<script>

    trabajador()

    function trabajador() {
        datos = {
            busqueda_personal: $('#busqueda_personal').val(),
            estado: $('#estado').val(),
        };

            $.LoadingOverlay('show');
            $.get('{{url('personal/trabajadores')}}', datos, function (retorno) {
                $('#div_listado_personal').html(retorno);
            }).always(function () {
                $.LoadingOverlay('hide');
            });
        }


    function add_personal() {
        get_jquery('{{url('personal/add')}}', {}, function (retorno) {
            modal_view('modal-view_add_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Añadir Personal', true, false, '90%');
        });
    }



    function ver_personal(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/update_personal')}}', datos, function (retorno) {
            modal_view('modal-view_update_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }

    function ficha_personal(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/ficha_personal')}}', datos, function (retorno) {
            modal_view('modal-view_ficha_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Editar Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }


    function ver_desincorporar_personal(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/view_desincorporar_personal')}}', datos, function (retorno) {
            modal_view('modal-view_desincorporar_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Desincorporar éste Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }

    function ver_incorporar_personal(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/view_incorporar_personal')}}', datos, function (retorno) {
            modal_view('modal-view_incorporar_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Incorporar éste Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }

    function incorporar_personal(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/view_incorporar_personal')}}', datos, function (retorno) {
            modal_view('modal-view_incorporar_personal', retorno, '<i class="fa fa-fw fa-plus"></i> Incorporar Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }
    function ver_historico(id_personal) {
        //dd($request->all());
        $.LoadingOverlay('show');
        datos = {
            id_personal: id_personal
        };
        get_jquery('{{url('personal/view_historico')}}', datos, function (retorno) {
            modal_view('modal-view_historico', retorno, '<i class="fa fa-fw fa-plus"></i> Histórico del Personal', true, false, '80%');
            //store_personal();
        });
        $.LoadingOverlay('hide');
    }

    function exportar_personal() {
        $.LoadingOverlay('show');
        window.open('{{url('personal/excel')}}' + '?busqueda=' + $('#busqueda_personal').val().trim()+ '&estado='+$('#estado').val().trim(), '_blank');
        $.LoadingOverlay('hide');
    }

    function seleccionar_area() {
        datos = {
            id_area: $('#id_area').val(),
        }
        get_jquery('{{ url('personal/seleccionar_area') }}', datos, function(retorno) {
            $('#id_actividad').html(retorno);
        })
    }



</script>
