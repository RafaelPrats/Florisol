<script>
    $('#vista_actual').val('ingreso_disponibilidad');
    listar_ingreso_disponibilidad();

    function listar_ingreso_disponibilidad() {
        datos = {
            desde: $('#filtro_predeterminado_desde').val(),
            hasta: $('#filtro_predeterminado_hasta').val(),
        };
        get_jquery('{{url('ingreso_disponibilidad/listar_ingreso_disponibilidad')}}', datos, function (retorno) {
            $('#listado_disponibilidad').html(retorno);
            //estructura_tabla('table_contenedores', false, true);
        });
    }
</script>