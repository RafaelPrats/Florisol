<script>
    
    filtrar_graficas_rrhh()

    $("#vista_actual").val('/dashboard_personal')

    function desglose_indicador_rrhh(indicador){

        let datos = { indicador }
        let nombre_indicador ={
            'persona_ha': 'persona por hectarea',
            'horas_extras': 'horas extras',
            'costo_persona':'costo por persona'
        }

        
        get_jquery('{{url('dashboard_personal/deglose_indicador')}}', datos, function (retorno) {
            modal_view('modal-view_desglose_indicador', retorno, '<i class="fa fa-fw fa-bar-chart"></i> Indicador '+nombre_indicador[indicador], true, false, '{{isPC() ? '80%' : ''}}')
        })

    }

    function filtrar_graficas_rrhh(){

        let datos = { 
            labor : $("#filtro_predeterminado_labor").val(),
            rango:  $("#filtro_predeterminado_rango").val(),
            annos: $("#filtro_predeterminado_annos").val()
        }

        get_jquery('{{url('dashboard_personal/filtrar_graficas')}}', datos, function (retorno) {
            
            $("#div_graficas_rrhh").html(retorno)

        },'div_graficas_rrhh')

    }

</script>