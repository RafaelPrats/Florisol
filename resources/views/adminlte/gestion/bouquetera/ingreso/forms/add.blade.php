<div style="overflow-y: scroll; max-height: 300px">
    <table class="table-bordered" style="width: 100%; border: 1px solid #9d9d9d; border-radius: 18px 18px 0 0;"
           id="table_add_bqt">
        <tr id="tr_fija_top_0">
            <th class="text-center th_yura_green" style="border-radius: 18px 0 0 0">
                Finca
            </th>
            <th class="text-center th_yura_green">
                Variedad
            </th>
            <th class="text-center th_yura_green">
                Tipo
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Precio
            </th>
            <th class="text-center th_yura_green" style="width: 80px">
                Tallos
            </th>
            <th class="text-center th_yura_green" style="border-radius: 0 18px 0 0; width: 100px">
                Exp
                <div class="btn-group">
                    <button type="button" class="btn btn-xs btn-yura_default" onclick="add_form_bqt()" title="Agregar">
                        <i class="fa fa-fw fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-yura_warning" onclick="delete_form_bqt()" title="Reiniciar formulario">
                        <i class="fa fa-fw fa-refresh"></i>
                    </button>
                </div>
            </th>
        </tr>
        <tr id="tr_ingreso_bqt_1">
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="add_finca_1" style="width: 100%">
                    <option value="-1">Comprada</option>
                    @foreach($fincas as $f)
                        <option value="{{$f->id_configuracion_empresa}}">{{$f->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="add_planta_1" style="width: 100%"
                        onchange="select_planta($(this).val(), 'add_variedad_1', 'add_variedad_1')">
                    <option value="">Seleccione</option>
                    @foreach($plantas as $p)
                        <option value="{{$p->id_planta}}">{{$p->nombre}}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <select id="add_variedad_1" style="width: 100%">
                </select>
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="add_precio_1" style="width: 100%" class="text-center" placeholder="$">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="add_tallos_1" style="width: 100%" class="text-center" placeholder="#">
            </td>
            <td class="text-center" style="border-color: #9d9d9d">
                <input type="number" id="add_exportada_1" style="width: 100%" class="text-center" placeholder="#">
            </td>
        </tr>
    </table>
</div>

<div class="text-center" style="margin-top: 5px">
    <button type="button" class="btn btn-md btn-yura_primary" onclick="store_bqt()">
        <i class="fa fa-fw fa-save"></i> Guardar
    </button>
</div>

@include('adminlte.gestion.bouquetera.ingreso.forms.eliminar_registros')

<style>
    #table_add_bqt tr#tr_fija_top_0 th {
        position: sticky;
        top: 0;
        z-index: 9;
    }
</style>