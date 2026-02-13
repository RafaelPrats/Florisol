<script>

$(function(){
    $("#vista_actual").val(location.pathname)
    $("#fecha_search_control_diario").attr("max", new Date().toISOString().split("T")[0])
    obtener_control_diario(true)
})

function obtener_control_diario(buscar_actividad=false){

    let data= {
        id_area: $("#id_search_area").val(),
        id_actividad: $("#id_search_actividad").val(),
        id_mano_obra: $("#id_search_mano_obra").val(),
        fecha: $("#fecha_search_control_diario").val()
    }

    $.LoadingOverlay('show');

    $.get('{{url('control_diario/buscar_control_diario')}}', data, function (retorno) {

        $('#div_control_diario').html(retorno)
        $.LoadingOverlay('hide');
        buscar_actividad && obtener_atividad_mano_obra('actividad')

    }).fail(function (retorno) {
        console.log(retorno);
        alerta_errores(retorno.responseText);
    }).always(function () {
        $.LoadingOverlay("hide");
    })

}

function obtener_atividad_mano_obra(tipo) {

    let data = {
        tipo,
        id_search_area: $('#id_search_area').val(),
        id_search_actividad: $('#id_search_actividad').val(),
    }

    get_jquery('{{ url('control_diario/obtener_actividad_mano_obra') }}', data, retorno => {

        let html = `<option value="">Todas</option>`

        if(tipo == 'actividad'){

            retorno.forEach(e => {
                html += `<option value="${e.id_actividad}">${e.nombre}</option>`
            })
            $('#id_search_actividad').html(html)

        }else if(tipo == 'mano_obra'){

            retorno.forEach(e => {
                html += `<option value="${e.id_mano_obra}">${e.mano_obra.nombre}</option>`
            })
            $('#id_search_mano_obra').html(html)

        }
    },  tipo == 'actividad' ? 'id_search_actividad' : 'id_search_mano_obra' )

}

function add_control_personal(identificacion =null){
   
    let data= {
        fecha: $("#fecha_search_control_diario").val(),
        identificacion,
        hora_desde: $("#desde_masivo").val(),
        hora_hasta: $("#hasta_masivo").val(),
    }

    get_jquery('{{ url('control_diario/add_control_personal') }}', data, retorno => {
        console.log('retorno', retorno)

        if($("#tabla_control_personal tbody tr").length){
            $("#tabla_control_personal tbody tr:first").before(retorno)
        }else{
            $("#tabla_control_personal tbody").append(retorno)
        }
        
    }, 'div_control_diario' )

}

function seleccionar_personal(select){

    let identificacion = $(select).children('option:selected').data('identificacion')
    let id_personal_detalle = $(select).children('option:selected').data('id-personal-detalle')

    $(select).parent().next().attr('id', 'persona_detalle_' + identificacion)
    $(select).parent().parent().find('td#persona_detalle_' + identificacion).text(identificacion)
    $(select).parent().parent().find('input.id_personal_detalle').val(id_personal_detalle)

}

function seleccionar_todo_personal(check){

    let check_personal = $("input.check_select_personal")
    $(check).is(':checked') ? check_personal.prop('checked', true) : check_personal.prop('checked', false)

}

function set_horario_personal(tipo, input){

    let check_personal = $("input.check_select_personal")

    if(tipo == 'desde'){

        $.each(check_personal,(i,j)=>{
            $(j).is(':checked') && $(j).parent().next().next().next().find('input.input-date-cd').val($(input).val())
        })

    }else{

        $.each(check_personal,(i,j)=>{
            $(j).is(':checked') && $(j).parent().next().next().next().next().find('input.input-date-ch').val($(input).val())
        })

    }

}

function store_control_asistencia(){

    let datos=[]

    $.each($("input.input-date-cd"),function(i,j){
        console.log($(j).parent().parent().find('select.id_mano_obra'))
        datos.push({
            id_control_personal: $(j).parent().parent().find('input.input_control_personal').val(),
            id_personal_detalle: $(j).parent().parent().find('input.id_personal_detalle').val(),
            desde: $(j).val(),
            hasta: $(j).parent().next().find('input.input-date-ch').val(),
            id_mano_obra: $(j).parent().parent().find('select.id_mano_obra').val()
        })

    })

    let data= {
        _token: '{{csrf_token()}}',
        fecha: $("#fecha_search_control_diario").val(),
        datos
    }
    post_jquery_m('{{url('control_diario/store_control_personal')}}', data, () => {
        obtener_control_diario()
    })

}

function delete_asistencia(id_control_personal){

    let data= {
        _token: '{{csrf_token()}}',
        id_control_personal
    }
    post_jquery_m('{{url('control_diario/delete_control_personal')}}', data, function () {
       obtener_control_diario()
    })

}

$("#busqueda_personal").on("keyup", function() {

    let value = $(this).val().toLowerCase()

    $("table#tabla_control_personal tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    })

})

function modal_camara(){

    $.LoadingOverlay('show');

    $.get('control_diario/modal_foto', {}, function (retorno) {
        modal_view('modal_view_foto_asistencia', retorno, '<i class="fa fa-fw fa-picture-o"></i> Foto de asistencia', true, false, '{{isPC() ? '75%' : ''}}');
    });
    $.LoadingOverlay('hide');
}
</script>
