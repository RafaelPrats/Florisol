<script>
    $('#vista_actual').val('motivos_reclamos');
    listar_reporte();

    function listar_reporte() {
        datos = {
        };
        get_jquery('{{ url('motivos_reclamos/listar_reporte') }}', datos, function(retorno) {
            $('#div_listar_reporte').html(retorno);
        });
    }
</script>
