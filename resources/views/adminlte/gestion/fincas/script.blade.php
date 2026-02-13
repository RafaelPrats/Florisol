<script>
    listar_fincas();
    listar_super_fincas();

    function listar_super_fincas() {
        datos = {};
        get_jquery('{{url('fincas/listar_super_fincas')}}', datos, function (retorno) {
            $('#div_listado_super_fincas').html(retorno);
        });
    }

    function listar_fincas() {
        datos = {};
        get_jquery('{{url('fincas/listar_fincas')}}', datos, function (retorno) {
            $('#div_listado_fincas').html(retorno);
        });
    }
</script>